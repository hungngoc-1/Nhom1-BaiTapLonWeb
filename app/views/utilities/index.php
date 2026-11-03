<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/../../Config/Database.php';

$msg = '';
$msgType = '';

try {
    $conn = Database::getConnection();

    // =========================================================================
    // 1. LẤY ĐƠN GIÁ DỊCH VỤ TỪ BẢNG `services`
    // =========================================================================
    $servicesList = $conn->query("SELECT * FROM services ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    $elecPrice = 3500;
    $elecServiceId = null;
    $waterPrice = 20000;
    $waterServiceId = null;
    $otherServices = [];
    $otherServicesTotal = 0;

    foreach ($servicesList as $srv) {
        $nameLower = mb_strtolower($srv['service_name'], 'UTF-8');
        if (strpos($nameLower, 'điện') !== false || strpos($nameLower, 'dien') !== false) {
            $elecPrice = (float)$srv['unit_price'];
            $elecServiceId = (int)$srv['id'];
        } elseif (strpos($nameLower, 'nước') !== false || strpos($nameLower, 'nuoc') !== false) {
            $waterPrice = (float)$srv['unit_price'];
            $waterServiceId = (int)$srv['id'];
        } else {
            $otherServices[] = $srv;
            $otherServicesTotal += (float)$srv['unit_price'];
        }
    }

    // Hàm chuẩn hóa kỳ chốt về định dạng MM-YYYY (VD: 10-2023)
    $normalizePeriod = function (string $raw): string {
        $raw = trim($raw);
        if (preg_match('/^(\d{4})-(\d{2})$/', $raw, $m)) {
            return $m[2] . '-' . $m[1];
        }
        if (preg_match('/^(\d{1,2})[\/\-](\d{4})$/', $raw, $m)) {
            return str_pad($m[1], 2, '0', STR_PAD_LEFT) . '-' . $m[2];
        }
        return $raw;
    };

    // Hàm tự động lập hóa đơn từ bản ghi điện nước
    $generateInvoiceFromReading = function (int $readingId) use ($conn, $elecPrice, $waterPrice, $elecServiceId, $waterServiceId, $otherServices, $otherServicesTotal): array {
        $stmt = $conn->prepare("
            SELECT u.*, r.room_number, r.price AS room_price
            FROM utilities_readings u
            JOIN rooms r ON u.room_id = r.id
            WHERE u.id = ?
        ");
        $stmt->execute([$readingId]);
        $reading = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$reading) {
            return ['ok' => false, 'msg' => 'Không tìm thấy bản ghi chỉ số điện nước.'];
        }

        // Kiểm tra đã có hóa đơn kỳ này chưa
        $chkInv = $conn->prepare("SELECT id FROM invoices WHERE room_id = ? AND month_year = ?");
        $chkInv->execute([$reading['room_id'], $reading['month_year']]);
        if ($chkInv->fetch()) {
            return ['ok' => false, 'msg' => "Phòng {$reading['room_number']} đã có hóa đơn kỳ {$reading['month_year']}!"];
        }

        // Tìm hợp đồng hiệu lực (hoặc gần nhất) của phòng
        $cStmt = $conn->prepare("
            SELECT id FROM contracts 
            WHERE room_id = ? 
            ORDER BY CASE WHEN status = 'active' THEN 0 ELSE 1 END, id DESC 
            LIMIT 1
        ");
        $cStmt->execute([$reading['room_id']]);
        $contract = $cStmt->fetch(PDO::FETCH_ASSOC);
        if (!$contract) {
            return ['ok' => false, 'msg' => "Phòng {$reading['room_number']} chưa có hợp đồng thuê nào để lập hóa đơn."];
        }

        $elecUsed = max(0, (int)$reading['electric_new'] - (int)$reading['electric_old']);
        $waterUsed = max(0, (int)$reading['water_new'] - (int)$reading['water_old']);
        $elecCost = $elecUsed * $elecPrice;
        $waterCost = $waterUsed * $waterPrice;
        $roomPrice = (float)$reading['room_price'];
        $totalAmount = $roomPrice + $elecCost + $waterCost + $otherServicesTotal;

        $conn->beginTransaction();
        try {
            $insInv = $conn->prepare("
                INSERT INTO invoices (room_id, contract_id, month_year, total_amount, status)
                VALUES (?, ?, ?, ?, 'unpaid')
            ");
            $insInv->execute([$reading['room_id'], $contract['id'], $reading['month_year'], $totalAmount]);
            $invoiceId = (int)$conn->lastInsertId();

            // Lưu chi tiết vào invoice_details
            $insDet = $conn->prepare("
                INSERT INTO invoice_details (invoice_id, service_id, quantity, applied_price, subtotal)
                VALUES (?, ?, ?, ?, ?)
            ");
            // Tiền phòng (service_id = NULL)
            $insDet->execute([$invoiceId, null, 1, $roomPrice, $roomPrice]);
            // Tiền điện
            if ($elecServiceId) {
                $insDet->execute([$invoiceId, $elecServiceId, $elecUsed, $elecPrice, $elecCost]);
            }
            // Tiền nước
            if ($waterServiceId) {
                $insDet->execute([$invoiceId, $waterServiceId, $waterUsed, $waterPrice, $waterCost]);
            }
            // Dịch vụ khác (Rác, Internet...)
            foreach ($otherServices as $os) {
                $uPrice = (float)$os['unit_price'];
                $insDet->execute([$invoiceId, (int)$os['id'], 1, $uPrice, $uPrice]);
            }

            $conn->commit();
            return [
                'ok' => true,
                'msg' => "Đã lập hóa đơn kỳ {$reading['month_year']} cho phòng {$reading['room_number']} (Tổng: " . number_format($totalAmount, 0, ',', '.') . " đ)!"
            ];
        } catch (Exception $ex) {
            $conn->rollBack();
            return ['ok' => false, 'msg' => 'Lỗi khi tạo hóa đơn: ' . $ex->getMessage()];
        }
    };

    // =========================================================================
    // 2. XỬ LÝ CÁC THAO TÁC CRUD & NGHIỆP VỤ ĐIỆN NƯỚC
    // =========================================================================

    // A. THÊM CHỈ SỐ MỚI (1 PHÒNG)
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $room_id = (int)($_POST['room_id'] ?? 0);
        $month_year = $normalizePeriod($_POST['month_year'] ?? '');
        $electric_old = (int)($_POST['electric_old'] ?? -1);
        $electric_new = (int)($_POST['electric_new'] ?? -1);
        $water_old = (int)($_POST['water_old'] ?? -1);
        $water_new = (int)($_POST['water_new'] ?? -1);
        $autoInvoice = !empty($_POST['auto_create_invoice']);

        if ($room_id <= 0 || empty($month_year)) {
            $msg = "Vui lòng chọn phòng và nhập kỳ chốt hợp lệ!";
            $msgType = "danger";
        } elseif ($electric_old < 0 || $electric_new < 0 || $water_old < 0 || $water_new < 0) {
            $msg = "Chỉ số điện nước phải là số nguyên lớn hơn hoặc bằng 0!";
            $msgType = "warning";
        } elseif ($electric_new < $electric_old || $water_new < $water_old) {
            $msg = "Lỗi nghiệp vụ: Chỉ số MỚI không được nhỏ hơn chỉ số CŨ!";
            $msgType = "warning";
        } else {
            $check = $conn->prepare("SELECT id FROM utilities_readings WHERE room_id = ? AND month_year = ?");
            $check->execute([$room_id, $month_year]);
            if ($check->rowCount() > 0) {
                $msg = "Phòng này đã được chốt chỉ số điện nước trong kỳ $month_year! Không được nhập trùng kỳ.";
                $msgType = "danger";
            } else {
                $stmt = $conn->prepare("
                    INSERT INTO utilities_readings (room_id, month_year, electric_old, electric_new, water_old, water_new)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$room_id, $month_year, $electric_old, $electric_new, $water_old, $water_new]);
                $newReadingId = (int)$conn->lastInsertId();

                if ($autoInvoice && $newReadingId > 0) {
                    $invRes = $generateInvoiceFromReading($newReadingId);
                    $msg = "Đã chốt chỉ số thành công! " . $invRes['msg'];
                    $msgType = $invRes['ok'] ? "success" : "warning";
                } else {
                    $msg = "Đã chốt chỉ số điện nước kỳ $month_year thành công!";
                    $msgType = "success";
                }
            }
        }
    }

    // B. CHỐT NHANH HÀNG LOẠT NHIỀU PHÒNG (BATCH ADD)
    if (isset($_POST['action']) && $_POST['action'] === 'batch_add') {
        $batchPeriod = $normalizePeriod($_POST['batch_month_year'] ?? date('m-Y'));
        $batchRooms = $_POST['batch'] ?? [];
        $savedCount = 0;
        $errors = [];

        foreach ($batchRooms as $rId => $vals) {
            $rId = (int)$rId;
            if (empty($vals['enable']) || $vals['electric_new'] === '' || $vals['water_new'] === '') {
                continue;
            }
            $eOld = (int)($vals['electric_old'] ?? 0);
            $eNew = (int)$vals['electric_new'];
            $wOld = (int)($vals['water_old'] ?? 0);
            $wNew = (int)$vals['water_new'];

            if ($eNew < $eOld || $wNew < $wOld) {
                $errors[] = "Phòng ID #$rId có chỉ số mới nhỏ hơn chỉ số cũ.";
                continue;
            }

            $chk = $conn->prepare("SELECT id FROM utilities_readings WHERE room_id = ? AND month_year = ?");
            $chk->execute([$rId, $batchPeriod]);
            if ($chk->rowCount() > 0) {
                continue;
            }

            $ins = $conn->prepare("
                INSERT INTO utilities_readings (room_id, month_year, electric_old, electric_new, water_old, water_new)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $ins->execute([$rId, $batchPeriod, $eOld, $eNew, $wOld, $wNew]);
            $savedCount++;
        }

        if ($savedCount > 0) {
            $msg = "Đã chốt nhanh thành công chỉ số cho $savedCount phòng trong kỳ $batchPeriod!";
            if (!empty($errors)) {
                $msg .= " (" . implode(' ', $errors) . ")";
            }
            $msgType = "success";
        } else {
            $msg = !empty($errors) ? implode(' ', $errors) : "Chưa có phòng nào được chọn hoặc nhập đủ chỉ số mới.";
            $msgType = "warning";
        }
    }

    // C. CẬP NHẬT CHỈ SỐ (UPDATE)
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $electric_old = (int)($_POST['electric_old'] ?? 0);
        $electric_new = (int)($_POST['electric_new'] ?? 0);
        $water_old = (int)($_POST['water_old'] ?? 0);
        $water_new = (int)($_POST['water_new'] ?? 0);

        if ($electric_new < $electric_old || $water_new < $water_old) {
            $msg = "Cập nhật thất bại: Chỉ số mới không được nhỏ hơn chỉ số cũ!";
            $msgType = "warning";
        } else {
            $stmt = $conn->prepare("
                UPDATE utilities_readings 
                SET electric_old = ?, electric_new = ?, water_old = ?, water_new = ? 
                WHERE id = ?
            ");
            $stmt->execute([$electric_old, $electric_new, $water_old, $water_new, $id]);

            // Tự động đồng bộ sang hóa đơn tương ứng của phòng trong kỳ (nếu chưa thanh toán)
            $rStmt = $conn->prepare("SELECT room_id, month_year FROM utilities_readings WHERE id = ?");
            $rStmt->execute([$id]);
            $readingRow = $rStmt->fetch(PDO::FETCH_ASSOC);
            if ($readingRow) {
                $chkInv = $conn->prepare("SELECT id, status FROM invoices WHERE room_id = ? AND month_year = ?");
                $chkInv->execute([$readingRow['room_id'], $readingRow['month_year']]);
                $inv = $chkInv->fetch(PDO::FETCH_ASSOC);
                if ($inv && $inv['status'] !== 'paid') {
                    $invId = (int)$inv['id'];
                    $eUsed = max(0, $electric_new - $electric_old);
                    $wUsed = max(0, $water_new - $water_old);
                    $eCost = $eUsed * $elecPrice;
                    $wCost = $wUsed * $waterPrice;

                    if ($elecServiceId) {
                        $conn->prepare("UPDATE invoice_details SET quantity = ?, subtotal = ? WHERE invoice_id = ? AND service_id = ?")
                             ->execute([$eUsed, $eCost, $invId, $elecServiceId]);
                    }
                    if ($waterServiceId) {
                        $conn->prepare("UPDATE invoice_details SET quantity = ?, subtotal = ? WHERE invoice_id = ? AND service_id = ?")
                             ->execute([$wUsed, $wCost, $invId, $waterServiceId]);
                    }
                    $totStmt = $conn->prepare("SELECT SUM(subtotal) FROM invoice_details WHERE invoice_id = ?");
                    $totStmt->execute([$invId]);
                    $newTotal = (float)$totStmt->fetchColumn();
                    $conn->prepare("UPDATE invoices SET total_amount = ? WHERE id = ?")->execute([$newTotal, $invId]);
                }
            }

            $msg = "Đã cập nhật chỉ số điện nước thành công!";
            $msgType = "success";
        }
    }

    // D. XÓA CHỈ SỐ (DELETE)
    if ((isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) ||
        (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id']))) {
        $delId = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM utilities_readings WHERE id = ?");
        $stmt->execute([$delId]);
        $msg = "Đã xóa bản ghi chỉ số điện nước thành công!";
        $msgType = "success";
    }

    // E. LẬP HÓA ĐƠN NHANH TỪ BẢN GHI ĐIỆN NƯỚC
    if (isset($_POST['action']) && $_POST['action'] === 'create_invoice') {
        $readingId = (int)($_POST['reading_id'] ?? 0);
        $res = $generateInvoiceFromReading($readingId);
        $msg = $res['msg'];
        $msgType = $res['ok'] ? 'success' : 'warning';
    }

    // F. CẬP NHẬT ĐƠN GIÁ ĐIỆN NƯỚC & DỊCH VỤ
    if (isset($_POST['action']) && $_POST['action'] === 'update_services') {
        $prices = $_POST['service_price'] ?? [];
        $updStmt = $conn->prepare("UPDATE services SET unit_price = ? WHERE id = ?");
        foreach ($prices as $sId => $priceVal) {
            if (is_numeric($priceVal) && (float)$priceVal >= 0) {
                $updStmt->execute([(float)$priceVal, (int)$sId]);
            }
        }
        // Nếu có thêm dịch vụ mới
        $newSrvName = trim($_POST['new_service_name'] ?? '');
        $newSrvPrice = $_POST['new_service_price'] ?? '';
        $newSrvUnit = trim($_POST['new_service_unit'] ?? 'Tháng');
        if ($newSrvName !== '' && is_numeric($newSrvPrice) && (float)$newSrvPrice >= 0) {
            $insSrv = $conn->prepare("INSERT INTO services (service_name, unit_price, unit) VALUES (?, ?, ?)");
            $insSrv->execute([$newSrvName, (float)$newSrvPrice, $newSrvUnit ?: 'Tháng']);
        }

        // Tải lại danh sách dịch vụ sau khi cập nhật
        $servicesList = $conn->query("SELECT * FROM services ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
        $otherServices = [];
        $otherServicesTotal = 0;
        foreach ($servicesList as $srv) {
            $nameLower = mb_strtolower($srv['service_name'], 'UTF-8');
            if (strpos($nameLower, 'điện') !== false || strpos($nameLower, 'dien') !== false) {
                $elecPrice = (float)$srv['unit_price'];
            } elseif (strpos($nameLower, 'nước') !== false || strpos($nameLower, 'nuoc') !== false) {
                $waterPrice = (float)$srv['unit_price'];
            } else {
                $otherServices[] = $srv;
                $otherServicesTotal += (float)$srv['unit_price'];
            }
        }
        $msg = "Đã cập nhật bảng đơn giá Điện Nước & Dịch vụ thành công!";
        $msgType = "success";
    }

    // =========================================================================
    // 3. TRUY VẤN DỮ LIỆU HIỂN THỊ & THỐNG KÊ
    // =========================================================================

    // Danh sách tất cả các kỳ có trong CSDL
    $periodsStmt = $conn->query("
        SELECT DISTINCT month_year 
        FROM utilities_readings 
        ORDER BY SUBSTRING(month_year, 4, 4) DESC, SUBSTRING(month_year, 1, 2) DESC
    ");
    $availablePeriods = $periodsStmt->fetchAll(PDO::FETCH_COLUMN);
    $currentMonthYear = date('m-Y');
    if (!in_array($currentMonthYear, $availablePeriods, true)) {
        array_unshift($availablePeriods, $currentMonthYear);
    }

    // Kỳ đang được chọn để xem tiến độ & thống kê
    $latestDataPeriod = $conn->query("
        SELECT month_year FROM utilities_readings 
        ORDER BY SUBSTRING(month_year, 4, 4) DESC, SUBSTRING(month_year, 1, 2) DESC 
        LIMIT 1
    ")->fetchColumn() ?: $currentMonthYear;

    $selectedPeriod = isset($_GET['period']) ? trim($_GET['period']) : $latestDataPeriod;
    $selectedRoomFilter = isset($_GET['room_id']) ? trim($_GET['room_id']) : '';
    $selectedInvStatus = isset($_GET['invoice_status']) ? trim($_GET['invoice_status']) : '';

    // Danh sách phòng kèm chỉ số mới nhất (để tự động điền chỉ số cũ khi chọn phòng)
    $allRooms = $conn->query("
        SELECT 
            r.id, 
            r.room_number, 
            r.price, 
            r.status,
            (
                SELECT usr.full_name 
                FROM contracts c 
                JOIN users usr ON c.tenant_id = usr.id 
                WHERE c.room_id = r.id AND c.status = 'active' 
                ORDER BY c.id DESC LIMIT 1
            ) AS tenant_name,
            (
                SELECT u2.electric_new 
                FROM utilities_readings u2 
                WHERE u2.room_id = r.id 
                ORDER BY SUBSTRING(u2.month_year, 4, 4) DESC, SUBSTRING(u2.month_year, 1, 2) DESC, u2.id DESC 
                LIMIT 1
            ) AS last_electric,
            (
                SELECT u2.water_new 
                FROM utilities_readings u2 
                WHERE u2.room_id = r.id 
                ORDER BY SUBSTRING(u2.month_year, 4, 4) DESC, SUBSTRING(u2.month_year, 1, 2) DESC, u2.id DESC 
                LIMIT 1
            ) AS last_water,
            (
                SELECT u2.month_year 
                FROM utilities_readings u2 
                WHERE u2.room_id = r.id 
                ORDER BY SUBSTRING(u2.month_year, 4, 4) DESC, SUBSTRING(u2.month_year, 1, 2) DESC, u2.id DESC 
                LIMIT 1
            ) AS last_period
        FROM rooms r
        ORDER BY r.room_number ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    $rentedRooms = array_values(array_filter($allRooms, fn($r) => $r['status'] === 'rented'));

    // Truy vấn danh sách bản ghi chỉ số điện nước
    $sqlReadings = "
        SELECT 
            u.id,
            u.room_id,
            u.month_year,
            u.electric_old,
            u.electric_new,
            u.water_old,
            u.water_new,
            u.created_at,
            r.room_number,
            r.price AS room_price,
            (
                SELECT usr.full_name 
                FROM contracts c 
                JOIN users usr ON c.tenant_id = usr.id 
                WHERE c.room_id = r.id AND c.status = 'active' 
                ORDER BY c.id DESC LIMIT 1
            ) AS tenant_name,
            inv.id AS invoice_id,
            inv.status AS invoice_status,
            inv.total_amount AS invoice_total
        FROM utilities_readings u
        JOIN rooms r ON u.room_id = r.id
        LEFT JOIN invoices inv ON inv.room_id = u.room_id AND inv.month_year = u.month_year
        WHERE 1=1
    ";
    $paramsReadings = [];

    if ($selectedPeriod !== '' && $selectedPeriod !== 'all') {
        $sqlReadings .= " AND u.month_year = ?";
        $paramsReadings[] = $selectedPeriod;
    }
    if ($selectedRoomFilter !== '' && is_numeric($selectedRoomFilter)) {
        $sqlReadings .= " AND u.room_id = ?";
        $paramsReadings[] = (int)$selectedRoomFilter;
    }
    if ($selectedInvStatus === 'created') {
        $sqlReadings .= " AND inv.id IS NOT NULL";
    } elseif ($selectedInvStatus === 'not_created') {
        $sqlReadings .= " AND inv.id IS NULL";
    }

    $sqlReadings .= " ORDER BY SUBSTRING(u.month_year, 4, 4) DESC, SUBSTRING(u.month_year, 1, 2) DESC, r.room_number ASC";
    $stmtReadings = $conn->prepare($sqlReadings);
    $stmtReadings->execute($paramsReadings);
    $readings = $stmtReadings->fetchAll(PDO::FETCH_ASSOC);

    // Tính toán số liệu cho 4 Thẻ Thống Kê (Overview Cards)
    $activeCheckPeriod = ($selectedPeriod !== '' && $selectedPeriod !== 'all') ? $selectedPeriod : $latestDataPeriod;
    $stmtPeriodRooms = $conn->prepare("
        SELECT u.room_id, u.electric_old, u.electric_new, u.water_old, u.water_new,
               inv.id AS invoice_id, inv.status AS invoice_status, inv.total_amount AS invoice_total
        FROM utilities_readings u
        LEFT JOIN invoices inv ON inv.room_id = u.room_id AND inv.month_year = u.month_year
        WHERE u.month_year = ?
    ");
    $stmtPeriodRooms->execute([$activeCheckPeriod]);
    $periodReadingsMap = [];
    foreach ($stmtPeriodRooms->fetchAll(PDO::FETCH_ASSOC) as $pr) {
        $periodReadingsMap[(int)$pr['room_id']] = $pr;
    }

    $totalRentedCount = count($rentedRooms);
    $recordedRentedCount = 0;
    $unrecordedRooms = [];
    foreach ($rentedRooms as $rr) {
        if (isset($periodReadingsMap[(int)$rr['id']])) {
            $recordedRentedCount++;
        } else {
            $unrecordedRooms[] = $rr;
        }
    }
    $progressPercent = $totalRentedCount > 0 ? round(($recordedRentedCount / $totalRentedCount) * 100) : 0;

    $totalElecKwh = 0;
    $totalWaterM3 = 0;
    $totalElecMoney = 0;
    $totalWaterMoney = 0;
    $invoicesCreatedCount = 0;
    $invoicesPendingCount = 0;

    foreach ($readings as $rRow) {
        $eUsed = max(0, (int)$rRow['electric_new'] - (int)$rRow['electric_old']);
        $wUsed = max(0, (int)$rRow['water_new'] - (int)$rRow['water_old']);
        $totalElecKwh += $eUsed;
        $totalWaterM3 += $wUsed;
        $totalElecMoney += $eUsed * $elecPrice;
        $totalWaterMoney += $wUsed * $waterPrice;
        if (!empty($rRow['invoice_id'])) {
            $invoicesCreatedCount++;
        } else {
            $invoicesPendingCount++;
        }
    }
    $totalUtilityMoney = $totalElecMoney + $totalWaterMoney;

} catch (PDOException $e) {
    $msg = "Lỗi kết nối CSDL: " . $e->getMessage();
    $msgType = "danger";
    $servicesList = [];
    $otherServices = [];
    $otherServicesTotal = 0;
    $elecPrice = 3500;
    $waterPrice = 20000;
    $availablePeriods = [date('m-Y')];
    $selectedPeriod = date('m-Y');
    $activeCheckPeriod = date('m-Y');
    $selectedRoomFilter = '';
    $selectedInvStatus = '';
    $allRooms = [];
    $rentedRooms = [];
    $readings = [];
    $periodReadingsMap = [];
    $unrecordedRooms = [];
    $totalRentedCount = 0;
    $recordedRentedCount = 0;
    $progressPercent = 0;
    $totalElecKwh = 0;
    $totalWaterM3 = 0;
    $totalElecMoney = 0;
    $totalWaterMoney = 0;
    $totalUtilityMoney = 0;
    $invoicesCreatedCount = 0;
    $invoicesPendingCount = 0;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayMate - Quản lý Chỉ số Điện Nước</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --teal-main: #308f86;
            --teal-hover: #25756d;
            --bg-page: #f8fafc;
        }

        body { 
            background-color: var(--bg-page);
            font-family: 'Nunito', sans-serif;
            font-size: 0.95rem;
            color: #334155;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-image: 
                url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23e6f4f1" fill-opacity="0.8" d="M0,160L48,170.7C96,181,192,203,288,197.3C384,192,480,160,576,149.3C672,139,768,149,864,170.7C960,192,1056,224,1152,213.3C1248,203,1344,149,1392,122.7L1440,96L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0C192,0,96,0,48,0L0,0Z"></path></svg>'),
                url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 300"><path d="M0,150 Q50,100 100,250" stroke="%2334d399" stroke-width="4" fill="none"/><path d="M10,120 Q40,90 60,110 Q30,140 10,120" fill="%236ee7b7"/><path d="M30,180 Q80,140 90,170 Q50,200 30,180" fill="%2310b981"/><path d="M60,220 Q100,190 110,220 Q70,250 60,220" fill="%23059669"/></svg>'),
                url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 300"><path d="M200,150 Q150,100 100,250" stroke="%2334d399" stroke-width="4" fill="none"/><path d="M190,120 Q160,90 140,110 Q170,140 190,120" fill="%236ee7b7"/><path d="M170,180 Q120,140 110,170 Q150,200 170,180" fill="%2310b981"/><path d="M140,220 Q100,190 90,220 Q130,250 140,220" fill="%23059669"/></svg>');
            background-position: top center, left -35px bottom 60px, right -35px bottom 40px;
            background-repeat: no-repeat;
            background-size: 100% auto, 120px, 120px;
            background-attachment: fixed;
        }

        /* ================= NAVBAR CHUẨN TRANG TỔNG QUAN ================= */
        .navbar-custom { background: #ffffff; padding: 12px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04); z-index: 100; border-bottom: 1px solid #f1f5f9; }
        .brand-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .brand-logo i { color: var(--teal-main); font-size: 2.2rem; }
        .brand-text { color: var(--teal-main); font-weight: 900; font-size: 1.4rem; line-height: 1; letter-spacing: -0.5px; }
        .brand-sub { color: #64748b; font-size: 0.75rem; font-weight: 600; }
        .text-teal-main { color: var(--teal-main) !important; }

        .nav-pills { gap: 6px; flex-wrap: nowrap !important; }
        .nav-pills .nav-link { 
            color: #475569; font-weight: 800; font-size: 0.88rem; 
            padding: 8px 14px; border-radius: 50px; transition: all 0.2s; white-space: nowrap; 
        }
        .nav-pills .nav-link i { margin-right: 6px; }
        .nav-pills .nav-link:hover { color: var(--teal-main); background: #f0fdfa; }
        .nav-pills .nav-link.active { background-color: var(--teal-main); color: white; box-shadow: 0 6px 15px rgba(48, 143, 134, 0.35); }

        /* Nút 3 gạch (Hamburger) trên điện thoại */
        .btn-hamburger {
            width: 44px; height: 44px;
            border-radius: 14px;
            background: #f0fdfa;
            border: 2px solid #99f6e4;
            color: var(--teal-main);
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.25rem;
            transition: all 0.2s;
        }
        .btn-hamburger:hover, .btn-hamburger:focus {
            background: var(--teal-main);
            color: #ffffff;
            outline: none;
        }
        @media (max-width: 991.98px) {
            .mobile-nav-collapse {
                background: #ffffff;
                border-radius: 22px;
                padding: 14px;
                margin-top: 12px;
                border: 2px solid #ccfbf1;
                box-shadow: 0 12px 30px rgba(0,0,0,0.08);
            }
            .mobile-nav-collapse .nav-pills {
                flex-direction: column;
                gap: 6px;
            }
            .mobile-nav-collapse .nav-link {
                border-radius: 16px;
                padding: 11px 16px;
            }
        }

        /* Dropdown chọn Kỳ đang xem gọn gàng */
        .period-dropdown-btn {
            background: #ffffff;
            border: 2.5px solid #99f6e4;
            border-radius: 50px;
            padding: 8px 18px;
            font-weight: 800;
            font-size: 0.88rem;
            color: #0f172a;
            box-shadow: 0 6px 18px rgba(48, 143, 134, 0.12);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s;
        }
        .period-dropdown-btn:hover, .period-dropdown-btn:focus {
            border-color: var(--teal-main);
            background: #f0fdfa;
        }
        .period-dropdown-badge {
            background: var(--teal-main);
            color: #ffffff;
            padding: 4px 12px;
            border-radius: 50px;
            font-weight: 900;
            font-size: 0.82rem;
        }
        .period-dropdown-menu {
            border-radius: 22px;
            border: 2px solid #ccfbf1;
            padding: 8px;
            min-width: 220px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.12);
        }
        .period-dropdown-menu .dropdown-item {
            border-radius: 14px;
            font-weight: 800;
            font-size: 0.86rem;
            padding: 9px 14px;
            color: #334155;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        .period-dropdown-menu .dropdown-item:hover {
            background: #f0fdfa;
            color: var(--teal-main);
        }
        .period-dropdown-menu .dropdown-item.active {
            background: var(--teal-main);
            color: #ffffff;
        }

        .avatar-circle {
            width: 46px; height: 46px;
            background-color: var(--teal-main);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 900; font-size: 1rem;
            border: 3px solid #ffffff; 
            box-shadow: 0 6px 15px rgba(48, 143, 134, 0.4);
            transition: 0.2s;
        }

        /* ================= NÚT QUAY LẠI & HEADER TÒA NHÀ ================= */
        .btn-back-content { 
            background: white; border: 3px solid #ffffff; color: #334155; font-weight: 800; font-size: 0.9rem; 
            padding: 10px 26px; border-radius: 50px; transition: 0.2s; text-decoration: none; 
            display: inline-flex; align-items: center; box-shadow: 0 8px 20px rgba(0,0,0,0.08); margin-bottom: 22px;
            position: relative; z-index: 2;
        }
        .btn-back-content:hover { background: #f8fafc; border-color: var(--teal-main); color: var(--teal-main); transform: translateY(-3px); box-shadow: 0 12px 25px rgba(0,0,0,0.12); }

        .header-title-wrapper { position: relative; z-index: 2; display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; flex-wrap: wrap; gap: 16px; }
        .main-title {  font-weight: 800; color: #0f172a; font-size: 1.45rem; letter-spacing: -0.3px; display: inline-flex; align-items: center; flex-wrap: wrap; }
        .bld-highlight { color: var(--teal-main); margin-left: 8px; position: relative; }
        .title-sparkle { position: absolute; background-color: #fde047; border-radius: 2px; }
        .ts-1 { width: 4px; height: 12px; top: -10px; right: -15px; transform: rotate(20deg); }
        .ts-2 { width: 12px; height: 4px; top: 15px; right: -25px; transform: rotate(15deg); }
        .bld-address-txt { font-size: 0.92rem; font-weight: 700; color: #64748b; margin-top: 6px; }

        .date-badge { background: white; border-radius: 50px; padding: 12px 26px; font-weight: 800; color: #475569; font-size: 0.9rem; box-shadow: 0 8px 20px rgba(0,0,0,0.08); border: 3px solid #ffffff; }

        .header-house-graphic {
            position: absolute; right: 0; top: -50px; width: 300px; height: 200px; z-index: 1; pointer-events: none;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 200"><path d="M0,180 Q150,150 300,180 L300,200 L0,200 Z" fill="%23dcfce7"/><rect x="120" y="80" width="80" height="100" fill="%23e0f2fe"/><polygon points="100,80 160,30 220,80" fill="%230284c7"/><polygon points="160,30 220,80 250,80 190,30" fill="%2338bdf8"/><rect x="140" y="100" width="15" height="20" fill="%23ffffff"/><rect x="165" y="100" width="15" height="20" fill="%23ffffff"/><rect x="140" y="140" width="15" height="40" fill="%23ffffff"/><circle cx="50" cy="140" r="30" fill="%234ade80"/><circle cx="80" cy="150" r="40" fill="%2322c55e"/><circle cx="240" cy="140" r="35" fill="%2322c55e"/><circle cx="270" cy="150" r="25" fill="%2316a34a"/></svg>');
            background-size: contain; background-repeat: no-repeat; background-position: bottom right;
        }

                .overview-card {
            border-radius: 18px;
            padding: 18px 22px; position: relative; overflow: hidden;
            box-shadow: 0 4px 16px rgba(0,0,0,0.03);
            border: 1px solid #e2e8f0;
            background: #ffffff;
            height: 100%; transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .overview-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.06); }
        .overview-title {  font-size: 0.85rem; font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; gap: 6px; text-transform: uppercase; color: #475569; position: relative; z-index: 2; }
        .overview-val {  font-size: 1.55rem; font-weight: 800; line-height: 1.2; letter-spacing: -0.3px; position: relative; z-index: 2; color: #0f172a; }
        .overview-icon-bg {
            position: absolute; right: 18px; top: 18px;
            width: 38px; height: 38px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1rem;
            z-index: 2;
        }
        .card-bg-pattern { display: none !important; }

        /* ================= KHUNG SECTION BO GÓC 30PX GIỐNG TRANG TỔNG QUAN ================= */
        .section-box {
            background: rgba(255, 255, 255, 0.96);
            border-radius: 30px;
            border: 4px solid #ffffff;
            padding: 24px 28px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
            position: relative;
            z-index: 2;
            margin-bottom: 24px;
        }

        /* Nút bấm chuẩn StayMate */
        .btn-teal-custom {
            background: var(--teal-main);
            border-radius: 50px;
            font-weight: 800;
            color: white;
            padding: 10px 24px;
            border: 2px solid var(--teal-hover);
            box-shadow: 0 8px 20px rgba(48, 143, 134, 0.35);
            font-size: 0.9rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .btn-teal-custom:hover { background: var(--teal-hover); color: white; transform: translateY(-2px); box-shadow: 0 12px 24px rgba(48, 143, 134, 0.45); }

        .btn-pill-white {
            background: #ffffff;
            border-radius: 50px;
            font-weight: 800;
            color: #334155;
            padding: 10px 20px;
            border: 2px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
            font-size: 0.86rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }
        .btn-pill-white:hover { border-color: var(--teal-main); color: var(--teal-main); background: #f0fdfa; transform: translateY(-2px); }

        .btn-pill-amber {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            border-radius: 50px;
            font-weight: 800;
            color: #92400e;
            padding: 10px 22px;
            border: 2px solid #ffffff;
            box-shadow: 0 6px 16px rgba(245, 158, 11, 0.25);
            font-size: 0.88rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-pill-amber:hover { background: #f59e0b; color: white; transform: translateY(-2px); }

        /* ================= LƯỚI TRẠNG THÁI PHÒNG TRONG KỲ ================= */
        .room-status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 14px;
        }
        .room-mini-card {
            border-radius: 22px;
            padding: 14px 16px;
            border: 2.5px solid #e2e8f0;
            background: #ffffff;
            transition: all 0.22s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 6px 15px rgba(0,0,0,0.03);
        }
        .room-mini-card:hover { transform: translateY(-3px); box-shadow: 0 10px 22px rgba(0,0,0,0.07); }
        .room-mini-card.done {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border-color: #86efac;
        }
        .btn-outline-teal {
            color: var(--teal-main);
            border: 2px solid var(--teal-main);
            background: #ffffff;
            font-weight: 800;
            transition: all 0.2s;
        }
        .btn-outline-teal:hover {
            background: var(--teal-main);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(48, 143, 134, 0.25);
        }
        .table-row-highlight {
            animation: flashRow 2.5s ease-out;
        }
        @keyframes flashRow {
            0% { background-color: #fef08a !important; }
            50% { background-color: #fef9c3 !important; }
            100% { background-color: transparent !important; }
        }
        .room-mini-card.done {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border-color: #86efac;
        }
        .room-mini-card.pending {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border-color: #fcd34d;
        }

        /* ================= THANH LỌC & TÌM KIẾM ================= */
        .filter-control {
            border-radius: 50px;
            border: 2px solid #e2e8f0;
            padding: 9px 18px;
            font-weight: 700;
            font-size: 0.88rem;
            color: #334155;
            background-color: #f8fafc;
            transition: all 0.2s;
        }
        .filter-control:focus {
            background-color: #ffffff;
            border-color: var(--teal-main);
            box-shadow: 0 0 0 4px rgba(48, 143, 134, 0.15);
            outline: none;
        }

        /* ================= BẢNG DỮ LIỆU BO GÓC SÂU ================= */
        .table-rounded-wrap {
            border-radius: 24px;
            border: 2px solid #e2e8f0;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 6px 20px rgba(0,0,0,0.03);
        }
        .table-staymate { margin-bottom: 0; }
        .table-staymate thead th { 
            background: #f0fdfa;
            color: var(--teal-main);
            font-weight: 900;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 16px 18px;
            border-bottom: 2px solid #ccfbf1;
            white-space: nowrap;
        }
        .table-staymate tbody td {
            padding: 16px 18px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            font-weight: 600;
            color: #1e293b;
        }
        .table-staymate tbody tr { transition: background-color 0.2s; }
        .table-staymate tbody tr:hover { background-color: #f8fafc; }

        /* Badges */
        .badge-room-pill {
            font-size: 0.88rem;
            box-shadow: 0 4px 10px rgba(48, 143, 134, 0.25);
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .badge-period-pill {
            font-size: 0.86rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        
            white-space: nowrap !important;
            flex-shrink: 0 !important;
        }
        .meter-box {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 16px;
            padding: 8px 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-width: 108px;
            white-space: nowrap;
        }
        .usage-pill-elec, .usage-pill-water {
            font-size: 0.84rem;
            white-space: nowrap;
        }
        .usage-pill-elec {
            background: #fef3c7;
            color: #b45309;
            border: 1.5px solid #fde68a;
        }
        .usage-pill-water {
            background: #e0f2fe;
            color: #0369a1;
            border: 1.5px solid #bae6fd;
        }
        .table-staymate td, .table-staymate th { white-space: nowrap; }
        .table-staymate thead th {  padding: 14px 10px; font-size: 0.82rem; }
        .table-staymate tbody td { padding: 14px 10px; }
        .table-staymate thead th:first-child, .table-staymate tbody td:first-child { padding-left: 16px !important; }
        .table-staymate td .tenant-name { white-space: normal; min-width: 110px; max-width: 150px; line-height: 1.25; }
        .table-staymate .col-period { width: 135px !important; min-width: 125px !important; white-space: nowrap !important; }
        .table-staymate .meter-box { min-width: 96px; padding: 7px 10px; gap: 6px; }
        .table-staymate .usage-pill-elec, .table-staymate .usage-pill-water { min-width: 76px; padding: 4px 8px; }
        .table-staymate .badge-period-pill {
            font-size: 0.86rem; 
            white-space: nowrap !important;
            flex-shrink: 0 !important;
        }
        .table-staymate .badge-room-pill {
            font-size: 0.88rem; }
        /* Cột thao tác luôn hiển thị (ghim bên phải) */
        .table-staymate thead th:last-child, .table-staymate tbody td:last-child {
            position: sticky; right: 0; z-index: 2;
            padding-right: 14px !important; padding-left: 12px !important;
            box-shadow: -8px 0 12px -8px rgba(0,0,0,0.08);
        }
        .table-staymate thead th:last-child { background: #f0fdfa; }
        .table-staymate tbody td:last-child { background: #ffffff; }
        .table-staymate tbody tr:hover td:last-child { background: #f8fafc; }
        .btn-icon-circle { width: 34px !important; height: 34px !important; }

        /* Action Buttons */
        .btn-icon-circle {
            width: 38px; height: 38px;
            border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            border: 2px solid transparent;
            transition: all 0.2s;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-icon-view { background: #f0fdfa; color: var(--teal-main); border-color: #99f6e4; }
        .btn-icon-view:hover { background: var(--teal-main); color: white; transform: translateY(-2px); }
        .btn-icon-edit { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; }
        .btn-icon-edit:hover { background: #2563eb; color: white; transform: translateY(-2px); }
        .btn-icon-del { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
        .btn-icon-del:hover { background: #dc2626; color: white; transform: translateY(-2px); }

        .btn-create-inv {
            background: #dcfce7;
            color: #15803d;
            border: 1.5px solid #86efac;
            border-radius: 50px;
            padding: 6px 14px;
            font-weight: 800;
            font-size: 0.78rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-create-inv:hover { background: #16a34a; color: white; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3); }

        /* Modals */
        .modal-staymate .modal-content {
            border-radius: 30px;
            border: 4px solid #ffffff;
            box-shadow: 0 25px 60px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .modal-staymate .modal-header {
            background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%);
            border-bottom: 2px solid #99f6e4;
            padding: 18px 26px;
        }
        .meter-input-card {
            border-radius: 22px;
            padding: 18px;
            border: 2px solid #e2e8f0;
        }
        .meter-input-card.elec { background: linear-gradient(135deg, #fffbeb 0%, #fef9c3 100%); border-color: #fde047; }
        .meter-input-card.water { background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-color: #bae6fd; }

        /* Bảng Nhập & Tính Tiền Nhanh */
        .table-quick-calc th { background: #f8fafc; font-size: 0.76rem; text-transform: uppercase; color: #64748b; font-weight: 800; }
        .table-quick-calc td { vertical-align: middle; }
        .calc-result-box {
            background: linear-gradient(135deg, #f0fdfa 0%, #e6fffa 100%);
            border: 2px solid #99f6e4;
            border-radius: 18px;
            padding: 10px 14px;
            transition: all 0.3s ease;
        }
        .calc-result-box.highlight {
            border-color: var(--teal-main);
            box-shadow: 0 0 0 3px rgba(48,143,134,0.2);
        }

        .footer-custom { background: #ffffff; padding: 20px 0; color: #64748b; font-size: 0.85rem; font-weight: 700; margin-top: auto; border-top: 1px solid #f1f5f9; position: relative; z-index: 10; }

        @media print {
            .navbar-custom, .btn-back-content, .no-print, .footer-custom { display: none !important; }
            body { background: #fff !important; }
            .section-box { box-shadow: none !important; border: 1px solid #ccc !important; }
        }
    </style>
</head>
<body>

    <!-- ================= NAVBAR CHUẨN STAYMATE (CÓ MENU 3 GẠCH TRÊN ĐIỆN THOẠI) ================= -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container-fluid px-3 px-xl-5">
            <a class="brand-logo me-3" href="index.php?route=dashboard">
                <i class="fa-solid fa-house-chimney text-teal-main"></i>
                <div>
                    <div class="brand-text">StayMate</div>
                    <div class="brand-sub">Quản Lý Nhà Trọ</div>
                </div>
            </a>

            <!-- Nhóm Chuông + Avatar + Nút 3 gạch khi ở màn hình điện thoại -->
            <div class="d-flex align-items-center gap-3 order-lg-3">
                <div class="position-relative" style="color: #64748b; cursor: pointer;" title="Phòng chưa chốt kỳ <?= htmlspecialchars($activeCheckPeriod) ?>">
                    <i class="fa-solid fa-bell fs-4"></i>
                    <?php if (count($unrecordedRooms) > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white">
                            <?= count($unrecordedRooms) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="dropdown">
                    <div class="d-flex align-items-center gap-2 cursor-pointer" data-bs-toggle="dropdown" style="cursor: pointer;">
                        <div class="avatar-circle">AS</div>
                        <div class="text-start d-none d-sm-block lh-sm">
                            <div class="fw-bold text-dark" style="font-size: 0.9rem;"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Quản trị viên Hệ thống') ?> <i class="fa-solid fa-angle-down ms-1 text-muted fs-6"></i></div>
                            <div class="text-muted text-capitalize" style="font-size: 0.84rem;"><?= htmlspecialchars($_SESSION['role'] ?? 'admin') ?></div>
                        </div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 mt-2 p-2">
                        <li>
                            <button type="button" class="dropdown-item rounded-3 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#profileModal">
                                <i class="fa-solid fa-id-card me-2 text-teal-main" style="color:#308f86;"></i> Thông tin tài khoản
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item rounded-3 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                <i class="fa-solid fa-key me-2 text-warning"></i> Đổi mật khẩu
                            </button>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item rounded-3 py-2 text-danger fw-bold" href="index.php?route=auth/logout"><i class="fa-solid fa-right-from-bracket me-2"></i> Đăng xuất</a></li>
                    </ul>
                </div>

                <!-- Nút 3 gạch (Hamburger) chỉ hiện trên điện thoại / màn hình nhỏ -->
                <button class="btn-hamburger d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#staymateMobileNav" aria-controls="staymateMobileNav" aria-expanded="false" aria-label="Mở danh mục menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>

            <!-- Danh mục Menu (Ngang trên máy tính, Xổ dọc trên điện thoại) -->
            <div class="collapse navbar-collapse mobile-nav-collapse order-lg-2" id="staymateMobileNav">
                <ul class="nav nav-pills flex-nowrap me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php?route=overview"><i class="fa-solid fa-border-all"></i> Tổng quan</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=rooms"><i class="fa-solid fa-bed"></i> Phòng trọ</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=tenants"><i class="fa-solid fa-users"></i> Khách thuê</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=contracts"><i class="fa-solid fa-file-signature"></i> Hợp đồng</a></li>
                <li class="nav-item"><a class="nav-link active" href="index.php?route=utilities"><i class="fa-solid fa-bolt"></i> Điện Nước</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=invoices"><i class="fa-solid fa-file-invoice-dollar"></i> Hóa đơn</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=showcase" target="_blank"><i class="fa-regular fa-eye"></i> Xem phòng</a></li>
            </ul>
            </div>
        </div>
    </nav>

    <!-- ================= MAIN CONTAINER ================= -->
    <div class="container-fluid px-4 px-xl-5 py-4 position-relative">

        <div class="d-flex flex-wrap justify-content-end align-items-center mb-2 no-print">
            <!-- Danh mục chọn Kỳ đang xem (Thu gọn thành Dropdown bấm vào mới xổ ra) -->
            <div class="dropdown">
                <button class="period-dropdown-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span><i class="fa-regular fa-calendar-check me-1 text-teal-main"></i> Kỳ đang xem:</span>
                    <span class="period-dropdown-badge">
                        <?= $selectedPeriod === 'all' ? 'Tất cả các kỳ' : 'Kỳ ' . htmlspecialchars($selectedPeriod) ?>
                    </span>
                    <i class="fa-solid fa-chevron-down text-muted small"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end period-dropdown-menu">
                    <li>
                        <a class="dropdown-item <?= $selectedPeriod === 'all' ? 'active' : '' ?>" href="index.php?route=utilities&period=all">
                            <span><i class="fa-solid fa-layer-group me-2"></i>Tất cả các kỳ</span>
                            <?php if ($selectedPeriod === 'all'): ?><i class="fa-solid fa-check"></i><?php endif; ?>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <?php foreach ($availablePeriods as $per): ?>
                        <li>
                            <a class="dropdown-item <?= $selectedPeriod === $per ? 'active' : '' ?>" href="index.php?route=utilities&period=<?= urlencode($per) ?>">
                                <span><i class="fa-regular fa-calendar me-2"></i>Kỳ <?= htmlspecialchars($per) ?></span>
                                <?php if ($selectedPeriod === $per): ?><i class="fa-solid fa-check"></i><?php endif; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- HEADER GIỐNG TRANG TỔNG QUAN -->
        <div class="header-title-wrapper">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1" style="font-size: 0.85rem; font-weight: 700;">
                        <li class="breadcrumb-item"><a href="index.php?route=dashboard" class="text-teal-main text-decoration-none"><i class="fa-solid fa-house"></i> Hệ thống Quản lý</a></li>
                        <li class="breadcrumb-item text-muted" aria-current="page">Quản lý Điện Nước &amp; Dịch vụ</li>
                    </ol>
                </nav>
                <div class="main-title">
                    Quản lý Chỉ số Điện Nước -
                    <span class="bld-highlight">
                        <?= $selectedPeriod === 'all' ? 'Tất cả các Kỳ' : 'Kỳ ' . htmlspecialchars($selectedPeriod) ?>
                        
                    </span>
                </div>
                <div class="bld-address-txt">
                    <i class="fa-solid fa-bolt text-warning me-1"></i> Đơn giá Điện: <strong class="text-dark"><?= number_format($elecPrice, 0, ',', '.') ?> đ/kWh</strong>
                    <span class="mx-2">•</span>
                    <i class="fa-solid fa-droplet text-info me-1"></i> Đơn giá Nước: <strong class="text-dark"><?= number_format($waterPrice, 0, ',', '.') ?> đ/Khối</strong>
                    <?php if ($otherServicesTotal > 0): ?>
                        <span class="mx-2">•</span>
                        <i class="fa-solid fa-wifi text-teal-main me-1"></i> Phí dịch vụ cố định: <strong class="text-dark"><?= number_format($otherServicesTotal, 0, ',', '.') ?> đ/phòng</strong>
                    <?php endif; ?>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2 position-relative z-2 no-print">
                <button type="button" class="btn-pill-white" data-bs-toggle="modal" data-bs-target="#servicePriceModal">
                    <i class="fa-solid fa-sliders text-teal-main"></i> Bảng giá Dịch vụ
                </button>
                <button type="button" class="btn-pill-amber" data-bs-toggle="modal" data-bs-target="#batchUtilityModal">
                    <i class="fa-solid fa-table-list"></i> Chốt Nhanh Hàng Loạt
                </button>
                <button type="button" class="btn-teal-custom" onclick="openAddModal()">
                    <i class="fa-solid fa-plus"></i> Chốt Chỉ Số Mới
                </button>
            </div>

            
        </div>

                <!-- THÔNG BÁO FLASH / CRUD -->
        <?php if ($msg): ?>
            <div class="alert alert-<?= htmlspecialchars($msgType) ?> alert-dismissible fade show rounded-pill px-4 py-3 border-2 shadow-sm fw-bold position-relative z-2 mb-4" role="alert">
                <i class="fa-solid <?= $msgType === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation' ?> me-2 fs-5 align-middle"></i>
                <?= htmlspecialchars($msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- ================= 3 THẺ OVERVIEW CARDS (CÂN ĐỐI, ĐỒNG ĐỀU FONT) ================= -->
        <div class="row g-3 mb-4 position-relative z-2">
            <!-- Card 1: Tổng Điện tiêu thụ -->
            <div class="col-12 col-md-4">
                <div class="overview-card" style="border-top: 3px solid #f59e0b;">
                    <div class="overview-title">
                        <i class="fa-solid fa-bolt text-warning me-1"></i> TỔNG ĐIỆN TIÊU THỤ
                    </div>
                    <div class="d-flex align-items-baseline my-1">
                        <span class="overview-val me-2"><?= number_format($totalElecKwh, 0, ',', '.') ?></span>
                        <span class="fw-semibold text-muted small">kWh (số)</span>
                    </div>
                    <div class="text-muted" style="font-size: 0.88rem;">
                        Thành tiền: <strong class="text-dark"><?= number_format($totalElecMoney, 0, ',', '.') ?> đ</strong>
                    </div>
                </div>
            </div>

            <!-- Card 2: Tổng Nước tiêu thụ -->
            <div class="col-12 col-md-4">
                <div class="overview-card" style="border-top: 3px solid #0284c7;">
                    <div class="overview-title">
                        <i class="fa-solid fa-droplet text-info me-1"></i> TỔNG NƯỚC TIÊU THỤ
                    </div>
                    <div class="d-flex align-items-baseline my-1">
                        <span class="overview-val me-2"><?= number_format($totalWaterM3, 0, ',', '.') ?></span>
                        <span class="fw-semibold text-muted small">Khối (m³)</span>
                    </div>
                    <div class="text-muted" style="font-size: 0.88rem;">
                        Thành tiền: <strong class="text-dark"><?= number_format($totalWaterMoney, 0, ',', '.') ?> đ</strong>
                    </div>
                </div>
            </div>

            <!-- Card 3: Tổng tiền Điện Nước -->
            <div class="col-12 col-md-4">
                <div class="overview-card" style="border-top: 3px solid #308f86;">
                    <div class="overview-title">
                        <i class="fa-solid fa-coins text-teal-main me-1"></i> TỔNG THU ĐIỆN NƯỚC
                    </div>
                    <div class="my-1">
                        <span class="overview-val text-teal-main"><?= number_format($totalUtilityMoney, 0, ',', '.') ?> đ</span>
                    </div>
                    <div class="d-flex justify-content-between text-muted" style="font-size: 0.88rem;">
                        <span>Đã lập: <strong class="text-dark"><?= $invoicesCreatedCount ?></strong> HĐ</span>
                        <?php if ($invoicesPendingCount > 0): ?>
                            <span class="text-danger fw-semibold">Chờ lập: <?= $invoicesPendingCount ?> HĐ</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= KHU VỰC 1: SƠ ĐỒ TRẠNG THÁI CHỐT SỐ CÁC PHÒNG ĐANG THUÊ (ĐẨY LÊN TRÊN) ================= -->
        <div class="section-box no-print mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 pb-2 border-bottom border-light">
                <div>
                    <h6 class="fw-black mb-0 text-dark" style="font-weight: 900; font-size: 1.08rem;">
                        <i class="fa-solid fa-grip text-teal-main me-2"></i>
                        Sơ đồ Chốt Chỉ số Các Phòng Đang Thuê — <span class="text-teal-main">Kỳ <?= htmlspecialchars($activeCheckPeriod) ?></span>
                    </h6>
                    <div class="text-muted small fw-semibold">Phòng đã chốt có màu xanh, phòng chưa chốt có màu vàng. Bấm "Kiểm tra" ở từng phòng để xem chi tiết bên dưới.</div>
                </div>
                <div class="d-flex align-items-center gap-3 small fw-bold">
                    <span><span class="badge rounded-pill bg-success me-1">&nbsp;</span> Đã chốt (<?= $recordedRentedCount ?>)</span>
                    <span><span class="badge rounded-pill bg-warning text-dark me-1">&nbsp;</span> Chưa chốt (<?= count($unrecordedRooms) ?>)</span>
                </div>
            </div>

            <div class="room-status-grid">
                <?php foreach ($rentedRooms as $rm): 
                    $rId = (int)$rm['id'];
                    $hasRec = isset($periodReadingsMap[$rId]);
                    $recData = $periodReadingsMap[$rId] ?? null;
                ?>
                    <div class="room-mini-card <?= $hasRec ? 'done' : 'pending' ?>" id="room_card_<?= htmlspecialchars($rm['room_number']) ?>">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge-room-pill">
                                <i class="fa-solid fa-door-closed"></i> <?= htmlspecialchars($rm['room_number']) ?>
                            </span>
                            <?php if ($hasRec): ?>
                                <?php if (!empty($recData['invoice_id']) && $recData['invoice_status'] === 'paid'): ?>
                                    <span class="badge rounded-pill bg-success text-white border border-success fw-bold px-2 py-1" style="font-size: 0.8rem;">
                                        <i class="fa-solid fa-circle-check me-1"></i> Đã thanh toán
                                    </span>
                                <?php else: ?>
                                    <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle fw-bold px-2 py-1" style="font-size: 0.8rem;">
                                        <i class="fa-solid fa-check-circle me-1"></i> Đã chốt
                                    </span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning fw-bold px-2 py-1" style="font-size: 0.8rem;">
                                    <i class="fa-regular fa-clock me-1"></i> Chưa chốt
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="small fw-bold text-secondary mb-2 text-truncate" style="font-size: 0.9rem;">
                            <i class="fa-regular fa-user me-1 text-muted"></i> <?= htmlspecialchars($rm['tenant_name'] ?? 'Đang thuê') ?>
                        </div>

                        <?php if ($hasRec): 
                            $eU = max(0, (int)$recData['electric_new'] - (int)$recData['electric_old']);
                            $wU = max(0, (int)$recData['water_new'] - (int)$recData['water_old']);
                            $roomTotalUtility = ($eU * $elecPrice) + ($wU * $waterPrice);
                        ?>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top border-success-subtle small fw-bold">
                                <span class="text-warning-emphasis"><i class="fa-solid fa-bolt text-warning"></i> +<?= $eU ?> kWh</span>
                                <span class="text-info-emphasis"><i class="fa-solid fa-droplet text-info"></i> +<?= $wU ?> m³</span>
                                <span class="text-teal-main"><?= number_format($roomTotalUtility, 0, ',', '.') ?>đ</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-teal w-100 mt-2 py-1 fw-bold rounded-pill" onclick="checkRoomDetails('<?= htmlspecialchars($rm['room_number']) ?>')">
                                <i class="fa-solid fa-magnifying-glass me-1"></i> Kiểm tra
                            </button>
                        <?php else: ?>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top border-warning-subtle">
                                <span class="text-muted" style="font-size: 0.78rem; font-weight: 700;">
                                    Số cũ: Đ <?= (int)($rm['last_electric'] ?? 0) ?> | N <?= (int)($rm['last_water'] ?? 0) ?>
                                </span>
                            </div>
                            <div class="d-flex gap-2 mt-2">
                                <button type="button" class="btn btn-sm rounded-pill fw-bold px-3 py-1 flex-grow-1" 
                                        style="background: #f59e0b; color: white; font-size: 0.8rem;"
                                        onclick="openAddModalForRoom(<?= $rId ?>, '<?= htmlspecialchars($activeCheckPeriod) ?>')">
                                    <i class="fa-solid fa-pen-nib me-1"></i> Ghi số
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-teal rounded-pill fw-bold py-1 px-3" 
                                        title="Kiểm tra lịch sử phòng <?= htmlspecialchars($rm['room_number']) ?>" 
                                        onclick="checkRoomDetails('<?= htmlspecialchars($rm['room_number']) ?>')">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ================= KHU VỰC 2: BỘ LỌC, TÌM KIẾM LIVE API & BẢNG DỮ LIỆU CẢI TIẾN ================= -->
        <div class="section-box" id="utilitiesTableSection">
            <!-- Thanh công cụ lọc -->
            <div class="row g-3 align-items-end mb-4 pb-3 border-bottom border-light no-print">
                <div class="col-12 col-lg-4">
                    <label class="form-label fw-bold text-teal-main text-uppercase mb-1" style="font-size: 0.78rem; letter-spacing: 0.5px;">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Tìm kiếm tự động (Live Search API)
                    </label>
                    <div class="position-relative">
                        <input type="text" id="searchInput" class="form-control filter-control ps-4 pe-5" 
                               placeholder="Gõ số phòng (A101, 101), tên khách hoặc kỳ...">
                        <button type="button" id="clearSearchBtn" onclick="clearSearch()" 
                                style="display: none; position: absolute; right: 38px; top: 50%; transform: translateY(-50%); border: none; background: transparent; color: #94a3b8; font-size: 0.9rem;" title="Xóa tìm kiếm">
                            <i class="fa-solid fa-circle-xmark"></i>
                        </button>
                        <span id="loadingIndicator" style="display: none; position: absolute; right: 16px; top: 50%; transform: translateY(-50%);" class="text-teal-main">
                            <i class="fa-solid fa-spinner fa-spin"></i>
                        </span>
                    </div>
                    <div id="errorMsg" style="display: none;" class="text-danger mt-1 fw-bold small"></div>
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <label class="form-label fw-bold text-muted text-uppercase mb-1" style="font-size: 0.84rem;">Kỳ chốt</label>
                    <select id="periodFilter" class="form-select filter-control" onchange="applyFilters()">
                        <option value="all" <?= $selectedPeriod === 'all' ? 'selected' : '' ?>>-- Tất cả các kỳ --</option>
                        <?php foreach ($availablePeriods as $per): ?>
                            <option value="<?= htmlspecialchars($per) ?>" <?= $selectedPeriod === $per ? 'selected' : '' ?>>
                                Kỳ <?= htmlspecialchars($per) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <label class="form-label fw-bold text-muted text-uppercase mb-1" style="font-size: 0.84rem;">Phòng trọ</label>
                    <select id="roomFilter" class="form-select filter-control" onchange="applyFilters()">
                        <option value="">-- Tất cả phòng --</option>
                        <?php foreach ($allRooms as $rm): ?>
                            <option value="<?= (int)$rm['id'] ?>" <?= (string)$selectedRoomFilter === (string)$rm['id'] ? 'selected' : '' ?>>
                                Phòng <?= htmlspecialchars($rm['room_number']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <label class="form-label fw-bold text-muted text-uppercase mb-1" style="font-size: 0.84rem;">Trạng thái HĐ</label>
                    <select id="invoiceFilter" class="form-select filter-control" onchange="applyFilters()">
                        <option value="" <?= $selectedInvStatus === '' ? 'selected' : '' ?>>-- Tất cả --</option>
                        <option value="created" <?= $selectedInvStatus === 'created' ? 'selected' : '' ?>>Đã lập hóa đơn</option>
                        <option value="not_created" <?= $selectedInvStatus === 'not_created' ? 'selected' : '' ?>>Chưa lập hóa đơn</option>
                    </select>
                </div>

                <div class="col-6 col-md-3 col-lg-2 text-end d-flex gap-2 justify-content-end">
                    <button type="button" class="btn-pill-white w-100 justify-content-center" onclick="exportTableToCSV()" title="Xuất file Excel CSV">
                        <i class="fa-solid fa-file-csv text-success"></i> Xuất CSV
                    </button>
                    <button type="button" class="btn-pill-white" onclick="window.print()" title="In bảng chốt chỉ số">
                        <i class="fa-solid fa-print text-teal-main"></i>
                    </button>
                </div>
            </div>

            <!-- Tiêu đề bảng -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="fw-black text-dark" style="font-weight: 900; font-size: 1.08rem;">
                    <i class="fa-solid fa-list-check me-2 text-teal-main"></i>
                    Bảng Chi Tiết Chỉ Số &amp; Tính Tiền Điện Nước (<span id="recordCount"><?= count($readings) ?></span> bản ghi)
                </div>
                <div class="text-muted small fw-bold">
                    Đơn giá: Điện <strong class="text-dark"><?= number_format($elecPrice, 0, ',', '.') ?>đ</strong> • Nước <strong class="text-dark"><?= number_format($waterPrice, 0, ',', '.') ?>đ</strong>
                </div>
            </div>

            <!-- Bảng dữ liệu cải tiến (4 cột thoáng, xếp tầng Điện - Nước gọn gàng) -->
            <div class="table-rounded-wrap table-responsive">
                <table class="table table-staymate align-middle mb-0" id="utilitiesTable">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width: 25%;">PHÒNG &amp; KHÁCH THUÊ</th>
                            <th style="width: 38%;">CHỈ SỐ TIÊU THỤ (ĐIỆN &amp; NƯỚC)</th>
                            <th style="width: 22%;">TỔNG TIỀN &amp; HÓA ĐƠN</th>
                            <th class="text-center pe-4 no-print" style="width: 15%;">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <?php if (count($readings) > 0): ?>
                            <?php foreach ($readings as $row):
                                $dienTieuThu = max(0, (int)$row['electric_new'] - (int)$row['electric_old']);
                                $nuocTieuThu = max(0, (int)$row['water_new'] - (int)$row['water_old']);
                                $tienDien = $dienTieuThu * $elecPrice;
                                $tienNuoc = $nuocTieuThu * $waterPrice;
                                $tongDienNuoc = $tienDien + $tienNuoc;
                                $roomNumber = $row['room_number'];
                                $tenantName = $row['tenant_name'] ?? 'Khách thuê phòng';
                                $periodVal = $row['month_year'];
                            ?>
                            <tr id="row_reading_<?= (int)$row['id'] ?>" 
                                data-room="<?= htmlspecialchars($roomNumber) ?>" 
                                data-tenant="<?= htmlspecialchars($tenantName) ?>" 
                                data-period="<?= htmlspecialchars($periodVal) ?>">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge-room-pill"><i class="fa-solid fa-door-closed"></i> <?= htmlspecialchars($roomNumber) ?></span>
                                        <span class="badge-period-pill"><i class="fa-regular fa-calendar text-teal-main me-1"></i><?= htmlspecialchars($periodVal) ?></span>
                                    </div>
                                    <div class="fw-bold text-dark tenant-name" style="font-size: 0.96rem;">
                                        <i class="fa-regular fa-user text-muted me-1"></i><?= htmlspecialchars($tenantName) ?>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.85rem;">
                                        Giá phòng: <strong class="text-dark"><?= number_format((float)($row['room_price'] ?? 0), 0, ',', '.') ?> đ</strong>
                                    </div>
                                </td>

                                <td>
                                    <div class="d-flex flex-column gap-2 py-1">
                                        <!-- Hàng Điện -->
                                        <div class="d-flex flex-wrap align-items-center justify-content-between p-2 rounded-3 bg-light border border-warning-subtle">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-warning text-dark fw-bold px-2 py-1" style="font-size: 0.82rem;">
                                                    <i class="fa-solid fa-bolt me-1"></i>Điện
                                                </span>
                                                <div class="meter-box py-1 px-2" style="font-size: 0.88rem;">
                                                    <span class="text-muted fw-bold"><?= (int)$row['electric_old'] ?></span>
                                                    <i class="fa-solid fa-arrow-right text-warning small mx-1"></i>
                                                    <span class="fw-black text-dark"><?= (int)$row['electric_new'] ?></span>
                                                </div>
                                                <span class="usage-pill-elec py-1 px-2 fw-bold" style="font-size: 0.84rem;">
                                                    +<?= $dienTieuThu ?> kWh
                                                </span>
                                                <?php if ($dienTieuThu >= 200): ?>
                                                    <span class="badge rounded-pill bg-danger-subtle text-danger small"><i class="fa-solid fa-fire"></i> Cao</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-end">
                                                <span class="text-muted small">Tiền điện:</span>
                                                <strong class="text-warning-emphasis ms-1" style="font-size: 0.95rem;"><?= number_format($tienDien, 0, ',', '.') ?> đ</strong>
                                            </div>
                                        </div>

                                        <!-- Hàng Nước -->
                                        <div class="d-flex flex-wrap align-items-center justify-content-between p-2 rounded-3 bg-light border border-info-subtle">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-info text-white fw-bold px-2 py-1" style="font-size: 0.82rem;">
                                                    <i class="fa-solid fa-droplet me-1"></i>Nước
                                                </span>
                                                <div class="meter-box py-1 px-2" style="font-size: 0.88rem;">
                                                    <span class="text-muted fw-bold"><?= (int)$row['water_old'] ?></span>
                                                    <i class="fa-solid fa-arrow-right text-info small mx-1"></i>
                                                    <span class="fw-black text-dark"><?= (int)$row['water_new'] ?></span>
                                                </div>
                                                <span class="usage-pill-water py-1 px-2 fw-bold" style="font-size: 0.84rem;">
                                                    +<?= $nuocTieuThu ?> m³
                                                </span>
                                            </div>
                                            <div class="text-end">
                                                <span class="text-muted small">Tiền nước:</span>
                                                <strong class="text-info-emphasis ms-1" style="font-size: 0.95rem;"><?= number_format($tienNuoc, 0, ',', '.') ?> đ</strong>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <div>
                                            <span class="text-muted small">Điện + Nước:</span>
                                            <div class="fw-black text-teal-main" style="font-size: 1.15rem; font-weight: 900;">
                                                <?= number_format($tongDienNuoc, 0, ',', '.') ?> đ
                                            </div>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.82rem;">
                                            Tạm tính cả phòng: <strong><?= number_format((float)$row['room_price'] + $tongDienNuoc + $otherServicesTotal, 0, ',', '.') ?> đ</strong>
                                        </div>
                                        <div class="mt-1">
                                            <?php if (!empty($row['invoice_id'])): ?>
                                                <?php if ($row['invoice_status'] === 'paid'): ?>
                                                    <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-1 fw-bold" style="font-size: 0.84rem;">
                                                        <i class="fa-solid fa-circle-check me-1"></i> Đã thanh toán
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning px-3 py-1 fw-bold" style="font-size: 0.84rem;">
                                                        <i class="fa-solid fa-file-invoice-dollar me-1"></i> Đã lập HĐ (Chờ thu)
                                                    </span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <form method="POST" action="index.php?route=utilities&period=<?= urlencode($selectedPeriod) ?>" class="d-inline">
                                                    <input type="hidden" name="action" value="create_invoice">
                                                    <input type="hidden" name="reading_id" value="<?= (int)$row['id'] ?>">
                                                    <button type="submit" class="btn-create-inv" title="Tự động lập hóa đơn kỳ này cho phòng <?= htmlspecialchars($row['room_number']) ?>">
                                                        <i class="fa-solid fa-wand-magic-sparkles"></i> Lập Hóa Đơn
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>

                                <td class="text-center pe-4 no-print">
                                    <div class="d-inline-flex align-items-center gap-2">
                                        <button type="button" class="btn-icon-circle btn-icon-view" title="Xem phiếu tính tiền chi tiết"
                                            onclick="openReceiptModal('<?= htmlspecialchars($row['room_number']) ?>', '<?= htmlspecialchars($tenantName) ?>', '<?= htmlspecialchars($periodVal) ?>', <?= (int)$row['electric_old'] ?>, <?= (int)$row['electric_new'] ?>, <?= (int)$row['water_old'] ?>, <?= (int)$row['water_new'] ?>, <?= (float)($row['room_price'] ?? 0) ?>)">
                                            <i class="fa-solid fa-receipt"></i>
                                        </button>
                                        <button type="button" class="btn-icon-circle btn-icon-edit" title="Chỉnh sửa chỉ số"
                                            onclick="openEditModal(<?= (int)$row['id'] ?>, '<?= htmlspecialchars($row['room_number']) ?>', '<?= htmlspecialchars($periodVal) ?>', <?= (int)$row['electric_old'] ?>, <?= (int)$row['electric_new'] ?>, <?= (int)$row['water_old'] ?>, <?= (int)$row['water_new'] ?>)">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <a href="index.php?route=utilities&period=<?= urlencode($selectedPeriod) ?>&action=delete&id=<?= (int)$row['id'] ?>" 
                                           class="btn-icon-circle btn-icon-del" title="Xóa bản ghi"
                                           onclick="return confirm('Bạn có chắc chắn muốn xóa chỉ số phòng <?= htmlspecialchars($row['room_number']) ?> kỳ <?= htmlspecialchars($periodVal) ?> không?');">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr id="emptyTableInitialRow">
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-folder-open fs-1 text-teal-main opacity-25 mb-3 d-block"></i>
                                    <p class="fw-bold mb-2">Chưa có dữ liệu chỉ số điện nước trong kỳ đang chọn.</p>
                                    <button type="button" class="btn-teal-custom btn-sm" onclick="openAddModal()">
                                        <i class="fa-solid fa-plus"></i> Chốt chỉ số ngay
                                    </button>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr id="noResultsRow" style="display: none;">
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-magnifying-glass fs-1 text-teal-main opacity-25 mb-3 d-block"></i>
                                <h6 class="fw-bold text-dark">Không tìm thấy phòng hoặc khách thuê phù hợp với từ khóa.</h6>
                                <p class="small mb-0">Thử tìm kiếm với số phòng khác hoặc ấn nút "Xóa tìm kiếm".</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    <!-- ================= MODAL 1: CHỐT CHỈ SỐ MỚI (TỰ ĐỘNG LẤY SỐ CŨ + TÍNH TIỀN TRỰC TIẾP) ================= -->
    <div class="modal fade modal-staymate" id="addUtilityModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-black text-teal-main mb-0" style="font-weight: 900;">
                            <i class="fa-solid fa-bolt me-2"></i> Chốt Chỉ Số Điện Nước Mới
                        </h5>
                        <div class="text-muted small fw-bold">Tự động kế thừa chỉ số cũ kỳ trước &amp; tính nhẩm thành tiền trực tiếp</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="index.php?route=utilities">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                        <div class="row g-3 mb-3">
                            <div class="col-md-7">
                                <label class="form-label fw-bold text-uppercase small text-muted">Chọn Phòng Trọ <span class="text-danger">*</span></label>
                                <select class="form-select filter-control rounded-4 py-2" name="room_id" id="add_room_id" required onchange="onRoomSelectChange()">
                                    <option value="">-- Chọn phòng cần ghi số --</option>
                                    <?php foreach ($allRooms as $r): ?>
                                        <option value="<?= (int)$r['id'] ?>"
                                                data-room-number="<?= htmlspecialchars($r['room_number']) ?>"
                                                data-room-price="<?= (float)$r['price'] ?>"
                                                data-tenant="<?= htmlspecialchars($r['tenant_name'] ?? 'Chưa có khách') ?>"
                                                data-last-elec="<?= (int)($r['last_electric'] ?? 0) ?>"
                                                data-last-water="<?= (int)($r['last_water'] ?? 0) ?>"
                                                data-last-period="<?= htmlspecialchars($r['last_period'] ?? 'Chưa có') ?>">
                                            Phòng <?= htmlspecialchars($r['room_number']) ?> — <?= htmlspecialchars($r['tenant_name'] ?? ($r['status'] === 'rented' ? 'Đang thuê' : 'Phòng trống')) ?> (<?= number_format((float)$r['price'], 0, ',', '.') ?>đ)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div id="add_room_hint" class="small fw-bold text-teal-main mt-1"></div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-bold text-uppercase small text-muted">Kỳ chốt (MM-YYYY) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control filter-control rounded-4 py-2" name="month_year" id="add_month_year" 
                                       value="<?= htmlspecialchars($activeCheckPeriod) ?>" placeholder="VD: 10-2023" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <!-- Khung Điện -->
                            <div class="col-md-6">
                                <div class="meter-input-card elec h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-black mb-0" style="color: #b45309; font-weight: 900;">
                                            <i class="fa-solid fa-bolt text-warning me-1"></i> Chỉ số Điện (kWh)
                                        </h6>
                                        <span class="badge rounded-pill bg-white text-dark border fw-bold"><?= number_format($elecPrice, 0, ',', '.') ?> đ/kWh</span>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="form-label small fw-bold text-muted mb-1">Chỉ số CŨ</label>
                                            <input type="number" class="form-control rounded-3 fw-bold" name="electric_old" id="add_elec_old" 
                                                   required min="0" value="0" oninput="calcLivePreview('add')">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small fw-bold text-dark mb-1">Chỉ số MỚI <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control rounded-3 fw-black border-warning border-2" name="electric_new" id="add_elec_new" 
                                                   required min="0" placeholder="Nhập số mới..." oninput="calcLivePreview('add')">
                                        </div>
                                    </div>
                                    <div class="mt-2 pt-2 border-top border-warning-subtle d-flex justify-content-between small fw-bold">
                                        <span>Tiêu thụ: <strong id="add_elec_used" class="text-dark">0 kWh</strong></span>
                                        <span>Thành tiền: <strong id="add_elec_cost" class="text-danger">0 đ</strong></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Khung Nước -->
                            <div class="col-md-6">
                                <div class="meter-input-card water h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-black mb-0 text-primary" style="font-weight: 900;">
                                            <i class="fa-solid fa-droplet text-info me-1"></i> Chỉ số Nước (Khối)
                                        </h6>
                                        <span class="badge rounded-pill bg-white text-dark border fw-bold"><?= number_format($waterPrice, 0, ',', '.') ?> đ/m³</span>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="form-label small fw-bold text-muted mb-1">Chỉ số CŨ</label>
                                            <input type="number" class="form-control rounded-3 fw-bold" name="water_old" id="add_water_old" 
                                                   required min="0" value="0" oninput="calcLivePreview('add')">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small fw-bold text-dark mb-1">Chỉ số MỚI <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control rounded-3 fw-black border-info border-2" name="water_new" id="add_water_new" 
                                                   required min="0" placeholder="Nhập số mới..." oninput="calcLivePreview('add')">
                                        </div>
                                    </div>
                                    <div class="mt-2 pt-2 border-top border-info-subtle d-flex justify-content-between small fw-bold">
                                        <span>Tiêu thụ: <strong id="add_water_used" class="text-dark">0 m³</strong></span>
                                        <span>Thành tiền: <strong id="add_water_cost" class="text-primary">0 đ</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cảnh báo nhập lùi / tiêu thụ bất thường -->
                        <div id="add_live_warning" class="alert alert-danger rounded-4 py-2 px-3 small fw-bold mb-3" style="display: none;"></div>

                        <!-- Tổng kết tiền & Tùy chọn tự động tạo hóa đơn -->
                        <div class="p-3 rounded-4 mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2" style="background: #f0fdfa; border: 2px solid #99f6e4;">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="autoCreateInvoice" name="auto_create_invoice" value="1" checked>
                                <label class="form-check-label fw-bold text-dark" for="autoCreateInvoice">
                                    Tự động lập luôn Hóa đơn tháng cho phòng này sau khi lưu
                                </label>
                            </div>
                            <div class="text-end">
                                <span class="small fw-bold text-muted me-1">Tổng Điện + Nước:</span>
                                <span id="add_total_utility" class="fs-5 fw-black text-teal-main" style="font-weight: 900;">0 đ</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn-pill-white px-4" data-bs-dismiss="modal">Hủy bỏ</button>
                            <button type="submit" id="add_submit_btn" class="btn-teal-custom px-4">
                                <i class="fa-solid fa-floppy-disk"></i> Lưu Chỉ Số Điện Nước
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= MODAL 2: CẬP NHẬT CHỈ SỐ ================= -->
    <div class="modal fade modal-staymate" id="editUtilityModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-black text-teal-main mb-0" style="font-weight: 900;">
                        <i class="fa-solid fa-pen-to-square me-2"></i> Cập Nhật Chỉ Số Điện Nước
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="index.php?route=utilities&period=<?= urlencode($selectedPeriod) ?>">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">

                        <div class="mb-3 text-center">
                            <span class="badge-room-pill fs-6 px-4 py-2" id="edit_title">Phòng -- Kỳ --</span>
                        </div>

                        <div class="meter-input-card elec mb-3">
                            <h6 class="fw-black mb-2" style="color: #b45309; font-weight: 900;"><i class="fa-solid fa-bolt text-warning me-1"></i> Chỉ số Điện (kWh)</h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-muted mb-1">Chỉ số CŨ</label>
                                    <input type="number" class="form-control rounded-3 fw-bold" name="electric_old" id="edit_electric_old" required min="0" oninput="calcLivePreview('edit')">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-dark mb-1">Chỉ số MỚI</label>
                                    <input type="number" class="form-control rounded-3 fw-black border-warning border-2" name="electric_new" id="edit_electric_new" required min="0" oninput="calcLivePreview('edit')">
                                </div>
                            </div>
                            <div class="mt-2 pt-2 border-top border-warning-subtle d-flex justify-content-between small fw-bold">
                                <span>Tiêu thụ: <strong id="edit_elec_used">0 kWh</strong></span>
                                <span>Tiền điện: <strong id="edit_elec_cost" class="text-danger">0 đ</strong></span>
                            </div>
                        </div>

                        <div class="meter-input-card water mb-3">
                            <h6 class="fw-black mb-2 text-primary" style="font-weight: 900;"><i class="fa-solid fa-droplet text-info me-1"></i> Chỉ số Nước (m³)</h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-muted mb-1">Chỉ số CŨ</label>
                                    <input type="number" class="form-control rounded-3 fw-bold" name="water_old" id="edit_water_old" required min="0" oninput="calcLivePreview('edit')">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-dark mb-1">Chỉ số MỚI</label>
                                    <input type="number" class="form-control rounded-3 fw-black border-info border-2" name="water_new" id="edit_water_new" required min="0" oninput="calcLivePreview('edit')">
                                </div>
                            </div>
                            <div class="mt-2 pt-2 border-top border-info-subtle d-flex justify-content-between small fw-bold">
                                <span>Tiêu thụ: <strong id="edit_water_used">0 m³</strong></span>
                                <span>Tiền nước: <strong id="edit_water_cost" class="text-primary">0 đ</strong></span>
                            </div>
                        </div>

                        <div id="edit_live_warning" class="alert alert-danger rounded-4 py-2 px-3 small fw-bold mb-3" style="display: none;"></div>

                        <div class="d-flex justify-content-between align-items-center mb-3 px-2">
                            <span class="fw-bold text-muted">Tổng tiền Điện + Nước:</span>
                            <span id="edit_total_utility" class="fs-5 fw-black text-teal-main" style="font-weight: 900;">0 đ</span>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn-pill-white px-4" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" id="edit_submit_btn" class="btn-teal-custom px-4">
                                <i class="fa-solid fa-check"></i> Lưu Thay Đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= MODAL 3: CHỐT NHANH HÀNG LOẠT THEO KỲ ================= -->
    <div class="modal fade modal-staymate" id="batchUtilityModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-black text-teal-main mb-0" style="font-weight: 900;">
                            <i class="fa-solid fa-table-list me-2"></i> Bảng Chốt Nhanh Điện Nước Hàng Loạt
                        </h5>
                        <div class="text-muted small fw-bold">Nhập chỉ số mới cho nhiều phòng cùng lúc mà không cần mở từng phòng</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="index.php?route=utilities">
                        <input type="hidden" name="action" value="batch_add">
                        <div class="row align-items-center mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase text-muted mb-1">Kỳ chốt hàng loạt (MM-YYYY)</label>
                                <input type="text" name="batch_month_year" class="form-control filter-control" value="<?= htmlspecialchars($activeCheckPeriod) ?>" required>
                            </div>
                            <div class="col-md-8 text-end small fw-bold text-muted pt-3">
                                Chỉ số Cũ được lấy tự động từ kỳ gần nhất của từng phòng.
                            </div>
                        </div>

                        <div class="table-rounded-wrap table-responsive mb-3">
                            <table class="table table-staymate align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-3">CHỌN</th>
                                        <th>PHÒNG</th>
                                        <th>ĐIỆN CŨ</th>
                                        <th>ĐIỆN MỚI (kWh)</th>
                                        <th>NƯỚC CŨ</th>
                                        <th>NƯỚC MỚI (m³)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($unrecordedRooms) > 0): ?>
                                        <?php foreach ($unrecordedRooms as $urm): 
                                            $uId = (int)$urm['id'];
                                            $lastE = (int)($urm['last_electric'] ?? 0);
                                            $lastW = (int)($urm['last_water'] ?? 0);
                                        ?>
                                            <tr>
                                                <td class="ps-3">
                                                    <input type="checkbox" class="form-check-input" name="batch[<?= $uId ?>][enable]" value="1" checked>
                                                </td>
                                                <td>
                                                    <span class="badge-room-pill"><?= htmlspecialchars($urm['room_number']) ?></span>
                                                    <div class="small text-muted fw-bold mt-1"><?= htmlspecialchars($urm['tenant_name'] ?? 'Đang thuê') ?></div>
                                                </td>
                                                <td style="width: 140px;">
                                                    <input type="number" class="form-control rounded-3 fw-bold bg-light" name="batch[<?= $uId ?>][electric_old]" value="<?= $lastE ?>" min="0">
                                                </td>
                                                <td style="width: 170px;">
                                                    <input type="number" class="form-control rounded-3 fw-black border-warning border-2" name="batch[<?= $uId ?>][electric_new]" placeholder=">= <?= $lastE ?>" min="<?= $lastE ?>">
                                                </td>
                                                <td style="width: 140px;">
                                                    <input type="number" class="form-control rounded-3 fw-bold bg-light" name="batch[<?= $uId ?>][water_old]" value="<?= $lastW ?>" min="0">
                                                </td>
                                                <td style="width: 170px;">
                                                    <input type="number" class="form-control rounded-3 fw-black border-info border-2" name="batch[<?= $uId ?>][water_new]" placeholder=">= <?= $lastW ?>" min="<?= $lastW ?>">
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-success fw-bold">
                                                <i class="fa-solid fa-circle-check me-2 fs-5"></i> Tất cả các phòng đang thuê đều đã được chốt chỉ số trong kỳ <?= htmlspecialchars($activeCheckPeriod) ?>!
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn-pill-white px-4" data-bs-dismiss="modal">Đóng</button>
                            <?php if (count($unrecordedRooms) > 0): ?>
                                <button type="submit" class="btn-teal-custom px-4">
                                    <i class="fa-solid fa-check-double"></i> Lưu Tất Cả Phòng Đã Nhập
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= MODAL 4: CẤU HÌNH ĐƠN GIÁ ĐIỆN NƯỚC & DỊCH VỤ ================= -->
    <div class="modal fade modal-staymate" id="servicePriceModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-black text-teal-main mb-0" style="font-weight: 900;">
                            <i class="fa-solid fa-sliders me-2"></i> Cấu Hình Đơn Giá Điện Nước &amp; Dịch Vụ
                        </h5>
                        <div class="text-muted small fw-bold">Bảng giá dùng để tự động nhân thành tiền khi chốt số &amp; lập hóa đơn</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="index.php?route=utilities&period=<?= urlencode($selectedPeriod) ?>">
                        <input type="hidden" name="action" value="update_services">

                        <div class="mb-3">
                            <?php foreach ($servicesList as $srv): ?>
                                <div class="d-flex align-items-center justify-content-between p-3 mb-2 rounded-4 border bg-light">
                                    <div>
                                        <div class="fw-black text-dark" style="font-weight: 800;"><?= htmlspecialchars($srv['service_name']) ?></div>
                                        <div class="text-muted small fw-semibold">Đơn vị tính: <?= htmlspecialchars($srv['unit']) ?></div>
                                    </div>
                                    <div class="input-group" style="width: 200px;">
                                        <input type="number" class="form-control fw-black text-end rounded-start-4" 
                                               name="service_price[<?= (int)$srv['id'] ?>]" 
                                               value="<?= (float)$srv['unit_price'] ?>" min="0" step="100" required>
                                        <span class="input-group-text bg-white fw-bold rounded-end-4">đ</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="p-3 rounded-4 border border-dashed mb-3" style="background: #f8fafc;">
                            <div class="fw-bold small text-teal-main mb-2"><i class="fa-solid fa-plus-circle me-1"></i> Thêm dịch vụ phụ thu mới (Tùy chọn)</div>
                            <div class="row g-2">
                                <div class="col-5">
                                    <input type="text" name="new_service_name" class="form-control form-control-sm rounded-3" placeholder="Tên DV (VD: Gửi xe)">
                                </div>
                                <div class="col-4">
                                    <input type="number" name="new_service_price" class="form-control form-control-sm rounded-3" placeholder="Đơn giá (đ)" min="0">
                                </div>
                                <div class="col-3">
                                    <input type="text" name="new_service_unit" class="form-control form-control-sm rounded-3" placeholder="Đơn vị" value="Tháng">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn-pill-white px-4" data-bs-dismiss="modal">Đóng</button>
                            <button type="submit" class="btn-teal-custom px-4">
                                <i class="fa-solid fa-floppy-disk"></i> Lưu Bảng Giá
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= MODAL 5: PHIẾU BÁO CHỈ SỐ & TÍNH TIỀN CHI TIẾT ================= -->
    <div class="modal fade modal-staymate" id="receiptModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-black text-teal-main mb-0" style="font-weight: 900;">
                        <i class="fa-solid fa-receipt me-2"></i> Phiếu Báo Chỉ Số Điện Nước &amp; Tạm Tính
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="receiptModalBody">
                    <!-- Nội dung phiếu được render bằng JS -->
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn-pill-white" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn-teal-custom" onclick="window.print()">
                        <i class="fa-solid fa-print"></i> In Phiếu Báo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= FOOTER CHUẨN TRANG TỔNG QUAN ================= -->
    <footer class="footer-custom container-fluid px-4 px-xl-5 d-flex flex-wrap justify-content-between align-items-center">
        <div><i class="fa-solid fa-building me-1" style="color:var(--teal-main);"></i> © 2026 StayMate - Quản lý Nhà trọ Chuyên nghiệp</div>
        <div class="d-flex gap-4">
            <span><i class="fa-solid fa-shield-halved me-1" style="color:var(--teal-main);"></i> WAMP MVC Architecture</span>
            <span><i class="fa-solid fa-bolt me-1 text-warning"></i> Tự động hóa Điện, Nước &amp; Hóa đơn</span>
        </div>
    </footer>

        <!-- ================= JAVASCRIPT XỬ LÝ TƯƠNG TÁC & LIVE API ================= -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const ELEC_PRICE = <?= json_encode((float)$elecPrice) ?>;
        const WATER_PRICE = <?= json_encode((float)$waterPrice) ?>;
        const OTHER_SERVICES_TOTAL = <?= json_encode((float)$otherServicesTotal) ?>;

        function formatVND(num) {
            return Math.round(num).toLocaleString('vi-VN') + ' đ';
        }

        // ===============================================================
        // 1. XỬ LÝ MODAL CHỐT CHỈ SỐ MỚI (TỰ ĐỘNG ĐIỀN CŨ & TÍNH TIỀN LIVE)
        // ===============================================================
        function onRoomSelectChange() {
            const selectEl = document.getElementById('add_room_id');
            const opt = selectEl.options[selectEl.selectedIndex];
            const hint = document.getElementById('add_room_hint');

            if (!opt || !opt.value) {
                if (hint) hint.innerHTML = '';
                document.getElementById('add_elec_old').value = 0;
                document.getElementById('add_water_old').value = 0;
                document.getElementById('add_elec_new').value = '';
                document.getElementById('add_water_new').value = '';
                calcLivePreview('add');
                return;
            }

            const lastElec = parseInt(opt.getAttribute('data-last-elec') || '0', 10);
            const lastWater = parseInt(opt.getAttribute('data-last-water') || '0', 10);
            const lastPeriod = opt.getAttribute('data-last-period') || '';
            const tenant = opt.getAttribute('data-tenant') || '';
            const price = parseFloat(opt.getAttribute('data-room-price') || '0');

            document.getElementById('add_elec_old').value = lastElec;
            document.getElementById('add_water_old').value = lastWater;
            document.getElementById('add_elec_new').value = '';
            document.getElementById('add_water_new').value = '';
            document.getElementById('add_elec_new').placeholder = '>= ' + lastElec;
            document.getElementById('add_water_new').placeholder = '>= ' + lastWater;

            if (hint) {
                hint.innerHTML = `<span class="text-teal-main fw-bold"><i class="fa-solid fa-user me-1"></i>${tenant}</span> • Giá phòng: <strong>${formatVND(price)}</strong>` + (lastPeriod ? ` • Kế thừa số cũ kỳ ${lastPeriod}` : '');
            }

            calcLivePreview('add');
            setTimeout(() => {
                document.getElementById('add_elec_new').focus();
            }, 150);
        }

        function calcLivePreview(type) {
            const isEdit = (type === 'edit');
            const prefix = isEdit ? 'edit_' : 'add_';

            const eOldInput = document.getElementById(prefix + 'elec_old') || document.getElementById(isEdit ? 'edit_electric_old' : 'add_elec_old');
            const eNewInput = document.getElementById(prefix + 'elec_new') || document.getElementById(isEdit ? 'edit_electric_new' : 'add_elec_new');
            const wOldInput = document.getElementById(prefix + 'water_old') || document.getElementById(isEdit ? 'edit_water_old' : 'add_water_old');
            const wNewInput = document.getElementById(prefix + 'water_new') || document.getElementById(isEdit ? 'edit_water_new' : 'add_water_new');

            const eOld = parseInt(eOldInput?.value || '0', 10);
            const eNewRaw = eNewInput?.value ?? '';
            const wOld = parseInt(wOldInput?.value || '0', 10);
            const wNewRaw = wNewInput?.value ?? '';

            const hasENew = (eNewRaw !== '' && !isNaN(eNewRaw));
            const hasWNew = (wNewRaw !== '' && !isNaN(wNewRaw));

            const eNew = hasENew ? parseInt(eNewRaw, 10) : eOld;
            const wNew = hasWNew ? parseInt(wNewRaw, 10) : wOld;

            const eUsed = Math.max(0, eNew - eOld);
            const wUsed = Math.max(0, wNew - wOld);
            const eCost = eUsed * ELEC_PRICE;
            const wCost = wUsed * WATER_PRICE;
            const totalUtility = eCost + wCost;

            const eUsedEl = document.getElementById(prefix + 'elec_used');
            const eCostEl = document.getElementById(prefix + 'elec_cost');
            const wUsedEl = document.getElementById(prefix + 'water_used');
            const wCostEl = document.getElementById(prefix + 'water_cost');
            const totalEl = document.getElementById(prefix + 'total_utility');
            const warnEl = document.getElementById(prefix + 'live_warning');
            const btnSubmit = document.getElementById(prefix + 'submit_btn');

            if (eUsedEl) eUsedEl.textContent = (hasENew ? eUsed : 0) + ' kWh';
            if (eCostEl) eCostEl.textContent = formatVND(hasENew ? eCost : 0);
            if (wUsedEl) wUsedEl.textContent = (hasWNew ? wUsed : 0) + ' m³';
            if (wCostEl) wCostEl.textContent = formatVND(hasWNew ? wCost : 0);
            if (totalEl) totalEl.textContent = formatVND(totalUtility);

            let hasError = false;
            let errMsg = '';
            if (hasENew && eNew < eOld) {
                hasError = true;
                errMsg = `Lỗi: Chỉ số điện mới (${eNew}) không được nhỏ hơn số cũ (${eOld})!`;
            } else if (hasWNew && wNew < wOld) {
                hasError = true;
                errMsg = `Lỗi: Chỉ số nước mới (${wNew}) không được nhỏ hơn số cũ (${wOld})!`;
            }

            if (warnEl) {
                if (hasError) {
                    warnEl.style.display = 'block';
                    warnEl.textContent = errMsg;
                } else {
                    warnEl.style.display = 'none';
                }
            }
            if (btnSubmit) {
                btnSubmit.disabled = hasError;
            }
        }

        // Mở Modal Thêm mới chung
        function openAddModal() {
            const modalEl = document.getElementById('addUtilityModal');
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
            calcLivePreview('add');
        }

        // Mở Modal Thêm mới khi bấm "Ghi số" ở Sơ đồ phòng
        function openAddModalForRoom(roomId, periodStr) {
            const sel = document.getElementById('add_room_id');
            if (sel) {
                sel.value = String(roomId);
                if (periodStr && periodStr !== 'all') {
                    const my = document.getElementById('add_month_year');
                    if (my) my.value = periodStr;
                }
                onRoomSelectChange();
            }
            const modalEl = document.getElementById('addUtilityModal');
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }

        // ===============================================================
        // 2. MỞ MODAL SỬA & PHIẾU BÁO CHI TIẾT
        // ===============================================================
        function openEditModal(id, room, month, e_old, e_new, w_old, w_new) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_title').textContent = 'Phòng ' + room + ' — Kỳ ' + month;
            document.getElementById('edit_electric_old').value = e_old;
            document.getElementById('edit_electric_new').value = e_new;
            document.getElementById('edit_water_old').value = w_old;
            document.getElementById('edit_water_new').value = w_new;
            calcLivePreview('edit');
            bootstrap.Modal.getOrCreateInstance(document.getElementById('editUtilityModal')).show();
        }

        function openReceiptModal(room, tenant, month, eOld, eNew, wOld, wNew, roomPrice) {
            const eUsed = Math.max(0, eNew - eOld);
            const wUsed = Math.max(0, wNew - wOld);
            const eCost = eUsed * ELEC_PRICE;
            const wCost = wUsed * WATER_PRICE;
            const totalAll = roomPrice + eCost + wCost + OTHER_SERVICES_TOTAL;

            document.getElementById('receiptModalBody').innerHTML = `
                <div class="p-3 rounded-4 border bg-light mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge-room-pill fs-6 px-3 py-1">Phòng ${room}</span>
                        <span class="badge-period-pill">Kỳ ${month}</span>
                    </div>
                    <div class="small fw-bold text-secondary">Khách thuê: <strong class="text-dark">${tenant}</strong></div>
                </div>
                <table class="table table-bordered rounded-4 overflow-hidden align-middle small fw-bold mb-3">
                    <thead class="table-light">
                        <tr>
                            <th>Khoản mục</th>
                            <th class="text-center">Chỉ số / SL</th>
                            <th class="text-end">Đơn giá</th>
                            <th class="text-end">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><i class="fa-solid fa-bed text-teal-main me-1"></i> Tiền thuê phòng</td>
                            <td class="text-center">01 tháng</td>
                            <td class="text-end">${formatVND(roomPrice)}</td>
                            <td class="text-end">${formatVND(roomPrice)}</td>
                        </tr>
                        <tr>
                            <td><i class="fa-solid fa-bolt text-warning me-1"></i> Tiền Điện</td>
                            <td class="text-center">${eOld} → ${eNew} (+${eUsed} kWh)</td>
                            <td class="text-end">${formatVND(ELEC_PRICE)}</td>
                            <td class="text-end">${formatVND(eCost)}</td>
                        </tr>
                        <tr>
                            <td><i class="fa-solid fa-droplet text-info me-1"></i> Tiền Nước</td>
                            <td class="text-center">${wOld} → ${wNew} (+${wUsed} m³)</td>
                            <td class="text-end">${formatVND(WATER_PRICE)}</td>
                            <td class="text-end">${formatVND(wCost)}</td>
                        </tr>
                        <tr>
                            <td><i class="fa-solid fa-wifi text-secondary me-1"></i> Dịch vụ cố định (Rác, Wifi...)</td>
                            <td class="text-center">Trọn gói</td>
                            <td class="text-end">${formatVND(OTHER_SERVICES_TOTAL)}</td>
                            <td class="text-end">${formatVND(OTHER_SERVICES_TOTAL)}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="table-success">
                            <th colspan="3" class="text-end fs-6">TỔNG CỘNG DỰ KIẾN:</th>
                            <th class="text-end fs-6 text-teal-main">${formatVND(totalAll)}</th>
                        </tr>
                    </tfoot>
                </table>
            `;
            bootstrap.Modal.getOrCreateInstance(document.getElementById('receiptModal')).show();
        }

        // ===============================================================
        // 3. NÚT "KIỂM TRA" PHÒNG: CUỘN XUỐNG VÀ LỌC TỨC THÌ
        // ===============================================================
        function checkRoomDetails(roomNumber) {
            const searchInp = document.getElementById('searchInput');
            if (searchInp) {
                searchInp.value = roomNumber;
                doFilterTable(roomNumber);
                const clearBtn = document.getElementById('clearSearchBtn');
                if (clearBtn) clearBtn.style.display = 'block';
            }

            const tableSec = document.getElementById('utilitiesTableSection');
            if (tableSec) {
                tableSec.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

            setTimeout(() => {
                const rows = document.querySelectorAll('#tableBody tr[data-room]');
                rows.forEach(r => {
                    const rRoom = r.getAttribute('data-room') || '';
                    if (rRoom.toUpperCase() === roomNumber.toUpperCase()) {
                        r.classList.add('table-row-highlight');
                        setTimeout(() => r.classList.remove('table-row-highlight'), 3000);
                    }
                });
            }, 350);
        }

        function clearSearch() {
            const searchInp = document.getElementById('searchInput');
            if (searchInp) {
                searchInp.value = '';
                doFilterTable('');
                const clearBtn = document.getElementById('clearSearchBtn');
                if (clearBtn) clearBtn.style.display = 'none';
                searchInp.focus();
            }
        }

        // ===============================================================
        // 4. BỘ LỌC TỨC THÌ CLIENT-SIDE (0MS) + LIVE SEARCH API ĐỒNG BỘ
        // ===============================================================
        function doFilterTable(keyword) {
            const kw = (keyword || '').trim().toLowerCase();
            const rows = document.querySelectorAll('#tableBody tr[data-room]');
            let visibleCount = 0;

            const cleanKw = kw.replace(/^[pP]/, '');

            rows.forEach(tr => {
                if (!kw) {
                    tr.style.display = '';
                    visibleCount++;
                    return;
                }
                const room = (tr.getAttribute('data-room') || '').toLowerCase();
                const tenant = (tr.getAttribute('data-tenant') || '').toLowerCase();
                const period = (tr.getAttribute('data-period') || '').toLowerCase();
                const text = tr.innerText.toLowerCase();

                const match = room.includes(kw) || room.includes(cleanKw) || tenant.includes(kw) || period.includes(kw) || text.includes(kw);
                if (match) {
                    tr.style.display = '';
                    visibleCount++;
                } else {
                    tr.style.display = 'none';
                }
            });

            const noResRow = document.getElementById('noResultsRow');
            if (noResRow) {
                noResRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
            }
            const countEl = document.getElementById('recordCount');
            if (countEl) countEl.textContent = visibleCount;
        }

        // Render cấu trúc 4 cột mới khi API trả về kết quả
        function renderTableFromData(items) {
            const tbody = document.getElementById('tableBody');
            const noResRow = document.getElementById('noResultsRow');
            const countEl = document.getElementById('recordCount');

            if (!items || items.length === 0) {
                tbody.querySelectorAll('tr[data-room]').forEach(r => r.remove());
                if (noResRow) noResRow.style.display = '';
                if (countEl) countEl.textContent = 0;
                return;
            }

            if (noResRow) noResRow.style.display = 'none';
            tbody.querySelectorAll('tr[data-room]').forEach(r => r.remove());
            if (countEl) countEl.textContent = items.length;

            items.forEach(item => {
                const tr = document.createElement('tr');
                const eOld = Number(item.electric_old);
                const eNew = Number(item.electric_new);
                const wOld = Number(item.water_old);
                const wNew = Number(item.water_new);
                const eUsed = Math.max(0, eNew - eOld);
                const wUsed = Math.max(0, wNew - wOld);
                const eCost = eUsed * ELEC_PRICE;
                const wCost = wUsed * WATER_PRICE;
                const totalUtil = eCost + wCost;
                const roomPrice = parseFloat(item.room_price || 0);
                const roomNum = item.room_number || '';
                const tenantName = item.tenant_name || 'Khách thuê phòng';
                const period = item.month_year || '';

                tr.id = 'row_reading_' + item.id;
                tr.setAttribute('data-room', roomNum);
                tr.setAttribute('data-tenant', tenantName);
                tr.setAttribute('data-period', period);

                let invHtml = '';
                if (item.invoice_id) {
                    if (item.invoice_status === 'paid') {
                        invHtml = '<span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-1 fw-bold" style="font-size: 0.84rem;"><i class="fa-solid fa-circle-check me-1"></i> Đã thanh toán</span>';
                    } else {
                        invHtml = '<span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning px-3 py-1 fw-bold" style="font-size: 0.84rem;"><i class="fa-solid fa-file-invoice-dollar me-1"></i> Đã lập HĐ (Chờ thu)</span>';
                    }
                } else {
                    invHtml = `
                        <form method="POST" action="index.php?route=utilities" class="d-inline">
                            <input type="hidden" name="action" value="create_invoice">
                            <input type="hidden" name="reading_id" value="${Number(item.id)}">
                            <button type="submit" class="btn-create-inv" title="Lập hóa đơn">
                                <i class="fa-solid fa-wand-magic-sparkles"></i> Lập Hóa Đơn
                            </button>
                        </form>
                    `;
                }

                tr.innerHTML = `
                    <td class="ps-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge-room-pill"><i class="fa-solid fa-door-closed"></i> ${escapeHtml(roomNum)}</span>
                            <span class="badge-period-pill"><i class="fa-regular fa-calendar text-teal-main me-1"></i>${escapeHtml(period)}</span>
                        </div>
                        <div class="fw-bold text-dark tenant-name" style="font-size: 0.96rem;">
                            <i class="fa-regular fa-user text-muted me-1"></i>${escapeHtml(tenantName)}
                        </div>
                        <div class="text-muted" style="font-size: 0.85rem;">
                            Giá phòng: <strong class="text-dark">${formatVND(roomPrice)}</strong>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column gap-2 py-1">
                            <div class="d-flex flex-wrap align-items-center justify-content-between p-2 rounded-3 bg-light border border-warning-subtle">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-warning text-dark fw-bold px-2 py-1" style="font-size: 0.82rem;"><i class="fa-solid fa-bolt me-1"></i>Điện</span>
                                    <div class="meter-box py-1 px-2" style="font-size: 0.88rem;">
                                        <span class="text-muted fw-bold">${eOld}</span>
                                        <i class="fa-solid fa-arrow-right text-warning small mx-1"></i>
                                        <span class="fw-black text-dark">${eNew}</span>
                                    </div>
                                    <span class="usage-pill-elec py-1 px-2 fw-bold" style="font-size: 0.84rem;">+${eUsed} kWh</span>
                                    ${eUsed >= 200 ? '<span class="badge rounded-pill bg-danger-subtle text-danger small"><i class="fa-solid fa-fire"></i> Cao</span>' : ''}
                                </div>
                                <div class="text-end">
                                    <span class="text-muted small">Tiền điện:</span>
                                    <strong class="text-warning-emphasis ms-1" style="font-size: 0.95rem;">${formatVND(eCost)}</strong>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap align-items-center justify-content-between p-2 rounded-3 bg-light border border-info-subtle">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-info text-white fw-bold px-2 py-1" style="font-size: 0.82rem;"><i class="fa-solid fa-droplet me-1"></i>Nước</span>
                                    <div class="meter-box py-1 px-2" style="font-size: 0.88rem;">
                                        <span class="text-muted fw-bold">${wOld}</span>
                                        <i class="fa-solid fa-arrow-right text-info small mx-1"></i>
                                        <span class="fw-black text-dark">${wNew}</span>
                                    </div>
                                    <span class="usage-pill-water py-1 px-2 fw-bold" style="font-size: 0.84rem;">+${wUsed} m³</span>
                                </div>
                                <div class="text-end">
                                    <span class="text-muted small">Tiền nước:</span>
                                    <strong class="text-info-emphasis ms-1" style="font-size: 0.95rem;">${formatVND(wCost)}</strong>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column gap-1">
                            <div>
                                <span class="text-muted small">Điện + Nước:</span>
                                <div class="fw-black text-teal-main" style="font-size: 1.15rem; font-weight: 900;">${formatVND(totalUtil)}</div>
                            </div>
                            <div class="text-muted" style="font-size: 0.82rem;">
                                Tạm tính cả phòng: <strong>${formatVND(roomPrice + totalUtil + OTHER_SERVICES_TOTAL)}</strong>
                            </div>
                            <div class="mt-1">${invHtml}</div>
                        </div>
                    </td>
                    <td class="text-center pe-4 no-print">
                        <div class="d-inline-flex align-items-center gap-2">
                            <button type="button" class="btn-icon-circle btn-icon-view" title="Xem phiếu tính tiền chi tiết"
                                onclick="openReceiptModal('${escapeJs(roomNum)}', '${escapeJs(tenantName)}', '${escapeJs(period)}', ${eOld}, ${eNew}, ${wOld}, ${wNew}, ${roomPrice})">
                                <i class="fa-solid fa-receipt"></i>
                            </button>
                            <button type="button" class="btn-icon-circle btn-icon-edit" title="Chỉnh sửa chỉ số"
                                onclick="openEditModal(${Number(item.id)}, '${escapeJs(roomNum)}', '${escapeJs(period)}', ${eOld}, ${eNew}, ${wOld}, ${wNew})">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <a href="index.php?route=utilities&action=delete&id=${Number(item.id)}" 
                               class="btn-icon-circle btn-icon-del" title="Xóa bản ghi"
                               onclick="return confirm('Bạn có chắc chắn muốn xóa bản ghi chỉ số phòng ${escapeJs(roomNum)} không?');">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </div>
                    </td>
                `;

                tbody.insertBefore(tr, noResRow);
            });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text || '';
            return div.innerHTML;
        }

        function escapeJs(str) {
            return (str || '').replace(/'/g, "\\'").replace(/"/g, '\\"');
        }

        // Lắng nghe sự kiện gõ tìm kiếm: Vừa lọc tức thì 0ms, vừa gọi API
        let searchDebounceTimer;
        const searchInput = document.getElementById('searchInput');
        const loadingIndicator = document.getElementById('loadingIndicator');
        const clearBtn = document.getElementById('clearSearchBtn');

        if (searchInput) {
            searchInput.addEventListener('input', () => {
                const keyword = searchInput.value;
                if (clearBtn) clearBtn.style.display = keyword ? 'block' : 'none';

                // 1. Phản hồi tức thì 0ms trên giao diện client
                doFilterTable(keyword);

                // 2. Gọi Live Search API (debounce 250ms) để tải thêm dữ liệu chuẩn xác
                clearTimeout(searchDebounceTimer);
                if (loadingIndicator) loadingIndicator.style.display = 'inline-block';

                searchDebounceTimer = setTimeout(async () => {
                    const period = document.getElementById('periodFilter')?.value || '';
                    const periodParam = period === 'all' ? '' : period;

                    try {
                        const res = await fetch(`index.php?route=api/utilities/search&q=${encodeURIComponent(keyword)}&month_year=${encodeURIComponent(periodParam)}`);
                        if (loadingIndicator) loadingIndicator.style.display = 'none';
                        if (!res.ok) return;

                        const json = await res.json();
                        if (json && json.ok && Array.isArray(json.data)) {
                            renderTableFromData(json.data);
                        }
                    } catch (e) {
                        if (loadingIndicator) loadingIndicator.style.display = 'none';
                        // Giữ nguyên client filter đã lọc tức thì
                    }
                }, 250);
            });
        }

        // ===============================================================
        // 5. CÁC HÀM TIỆN ÍCH LỌC KỲ, XUẤT CSV
        // ===============================================================
        function applyFilters() {
            const period = document.getElementById('periodFilter').value;
            const roomId = document.getElementById('roomFilter').value;
            const invStatus = document.getElementById('invoiceFilter').value;
            const url = new URL(window.location.href);
            url.searchParams.set('route', 'utilities');
            url.searchParams.set('period', period);
            if (roomId) url.searchParams.set('room_id', roomId); else url.searchParams.delete('room_id');
            if (invStatus) url.searchParams.set('invoice_status', invStatus); else url.searchParams.delete('invoice_status');
            window.location.href = url.toString();
        }

        function exportTableToCSV() {
            const rows = [['Phong', 'Khach Thue', 'Ky Chot', 'Dien Cu', 'Dien Moi', 'Dien Tieu Thu (kWh)', 'Nuoc Cu', 'Nuoc Moi', 'Nuoc Tieu Thu (m3)', 'Tong Tien Dien Nuoc']];
            document.querySelectorAll('#tableBody tr[data-room]').forEach(tr => {
                if (tr.style.display === 'none') return;
                const room = tr.getAttribute('data-room') || '';
                const tenant = tr.getAttribute('data-tenant') || '';
                const period = tr.getAttribute('data-period') || '';
                const meterBoxes = tr.querySelectorAll('.meter-box');
                const eOld = meterBoxes[0]?.querySelectorAll('span')[0]?.textContent.trim() || '0';
                const eNew = meterBoxes[0]?.querySelectorAll('span')[1]?.textContent.trim() || '0';
                const eUsed = String(Math.max(0, parseInt(eNew) - parseInt(eOld)));
                const wOld = meterBoxes[1]?.querySelectorAll('span')[0]?.textContent.trim() || '0';
                const wNew = meterBoxes[1]?.querySelectorAll('span')[1]?.textContent.trim() || '0';
                const wUsed = String(Math.max(0, parseInt(wNew) - parseInt(wOld)));
                const total = tr.querySelector('.fw-black.text-teal-main')?.textContent.trim() || '';
                rows.push([room, tenant, period, eOld, eNew, eUsed, wOld, wNew, wUsed, total]);
            });
            const csvContent = '\uFEFF' + rows.map(r => r.map(c => `"${String(c).replace(/"/g, '""')}"`).join(',')).join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'Bang_Chot_Dien_Nuoc_StayMate.csv';
            link.click();
        }

    <!-- MODAL DOI MAT KHAU -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light rounded-top-4">
                    <h5 class="modal-title fw-bold text-teal-main"><i class="fa-solid fa-key me-2 text-warning"></i> Đổi Mật Khẩu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="index.php?route=dashboard">
                    <input type="hidden" name="action" value="change_password">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Mật khẩu hiện tại</label>
                            <input type="password" name="old_password" class="form-control rounded-3" placeholder="Nhập mật khẩu hiện tại" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Mật khẩu mới</label>
                            <input type="password" name="new_password" class="form-control rounded-3" placeholder="Nhập mật khẩu mới" required minlength="4">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Xác nhận mật khẩu mới</label>
                            <input type="password" name="confirm_password" class="form-control rounded-3" placeholder="Nhập lại mật khẩu mới" required minlength="4">
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-teal-custom px-4" style="background:#308f86;color:#fff;border-radius:50px;"><i class="fa-solid fa-check me-1"></i> Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL THONG TIN TAI KHOAN -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light rounded-top-4">
                    <h5 class="modal-title fw-bold text-teal-main"><i class="fa-solid fa-user-shield me-2"></i> Thông tin Tài khoản</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold fs-4" style="width:60px; height:60px; background:#308f86;">AS</div>
                        <div>
                            <h5 class="fw-bold mb-0"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Quản trị viên Hệ thống') ?></h5>
                            <span class="badge bg-success rounded-pill text-uppercase"><?= htmlspecialchars($_SESSION['role'] ?? 'admin') ?></span>
                        </div>
                    </div>
                    <ul class="list-group list-group-flush border rounded-3">
                        <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Tên đăng nhập:</span> <strong><?= htmlspecialchars($_SESSION['username'] ?? 'admin01') ?></strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Email:</span> <strong><?= htmlspecialchars($_SESSION['email'] ?? 'admin@nhatro.com') ?></strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Số điện thoại:</span> <strong><?= htmlspecialchars($_SESSION['phone'] ?? '0901234567') ?></strong></li>
                    </ul>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                    <a href="index.php?route=auth/logout" class="btn btn-danger rounded-pill px-4"><i class="fa-solid fa-right-from-bracket me-1"></i> Đăng xuất</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
