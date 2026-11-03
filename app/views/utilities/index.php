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
        SELECT room_id, electric_old, electric_new, water_old, water_new 
        FROM utilities_readings 
        WHERE month_year = ?
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
            font-size: 0.9rem;
            color: #334155;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-image: 
                url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23e6f4f1" fill-opacity="0.8" d="M0,160L48,170.7C96,181,192,203,288,197.3C384,192,480,160,576,149.3C672,139,768,149,864,170.7C960,192,1056,224,1152,213.3C1248,203,1344,149,1392,122.7L1440,96L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0C192,0,96,0,48,0L0,0Z"></path></svg>'),
                url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 300"><path d="M0,150 Q50,100 100,250" stroke="%2334d399" stroke-width="4" fill="none"/><path d="M10,120 Q40,90 60,110 Q30,140 10,120" fill="%236ee7b7"/><path d="M30,180 Q80,140 90,170 Q50,200 30,180" fill="%2310b981"/><path d="M60,220 Q100,190 110,220 Q70,250 60,220" fill="%23059669"/></svg>'),
                url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 300"><path d="M200,150 Q150,100 100,250" stroke="%2334d399" stroke-width="4" fill="none"/><path d="M190,120 Q160,90 140,110 Q170,140 190,120" fill="%236ee7b7"/><path d="M170,180 Q120,140 110,170 Q150,200 170,180" fill="%2310b981"/><path d="M140,220 Q100,190 90,220 Q130,250 140,220" fill="%23059669"/></svg>');
            background-position: top center, left 20%, right bottom;
            background-repeat: no-repeat;
            background-size: 100% auto, 150px, 150px;
            background-attachment: fixed;
        }

        /* ================= NAVBAR CHUẨN TRANG TỔNG QUAN ================= */
        .navbar-custom { background: #ffffff; padding: 12px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04); z-index: 100; border-bottom: 1px solid #f1f5f9; }
        .brand-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .brand-logo i { color: var(--teal-main); font-size: 2.2rem; }
        .brand-text { color: var(--teal-main); font-weight: 900; font-size: 1.4rem; line-height: 1; letter-spacing: -0.5px; }
        .brand-sub { color: #64748b; font-size: 0.75rem; font-weight: 600; }
        .text-teal-main { color: var(--teal-main) !important; }

        .nav-pills { gap: 8px; }
        .nav-pills .nav-link { 
            color: #475569; font-weight: 800; font-size: 0.92rem; 
            padding: 10px 18px; border-radius: 50px; transition: all 0.2s; white-space: nowrap; 
        }
        .nav-pills .nav-link i { margin-right: 6px; }
        .nav-pills .nav-link:hover { color: var(--teal-main); background: #f0fdfa; }
        .nav-pills .nav-link.active { background-color: var(--teal-main); color: white; box-shadow: 0 6px 15px rgba(48, 143, 134, 0.35); }

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
        .main-title { font-weight: 900; color: #0f172a; font-size: 1.95rem; letter-spacing: -0.5px; display: inline-flex; align-items: center; flex-wrap: wrap; }
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

        /* ================= 4 THẺ OVERVIEW BO TRÒN SÂU 30PX ================= */
        .overview-card {
            border-radius: 30px;
            padding: 24px; position: relative; overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.12);
            border: 4px solid #ffffff;
            height: 100%; transition: transform 0.3s ease, box-shadow 0.3s ease;
            backdrop-filter: blur(5px);
        }
        .overview-card:hover { transform: translateY(-7px); box-shadow: 0 25px 45px rgba(0,0,0,0.17); }

        .card-green { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); }
        .card-blue { background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); }
        .card-yellow { background: linear-gradient(135deg, #fef9c3 0%, #fef08a 100%); }
        .card-purple { background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%); }

        .card-bg-pattern { position: absolute; right: 0; bottom: 0; width: 135px; height: 135px; background-size: contain; background-repeat: no-repeat; background-position: bottom right; opacity: 0.85; pointer-events: none; }
        .pattern-house { background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect x="30" y="50" width="50" height="40" fill="%23bae6fd"/><polygon points="20,50 55,20 90,50" fill="%233b82f6"/><rect x="40" y="60" width="10" height="10" fill="%23ffffff"/><rect x="60" y="60" width="10" height="10" fill="%23ffffff"/><circle cx="15" cy="80" r="10" fill="%234ade80"/><circle cx="95" cy="75" r="12" fill="%2322c55e"/></svg>'); }
        .pattern-wave { background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path d="M0,70 Q25,40 50,70 T100,70 L100,100 L0,100 Z" fill="%23bae6fd" opacity="0.5"/><path d="M0,90 Q25,60 50,90 T100,90 L100,100 L0,100 Z" fill="%237dd3fc" opacity="0.5"/></svg>'); }
        .pattern-wave-yellow { background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path d="M0,70 Q25,40 50,70 T100,70 L100,100 L0,100 Z" fill="%23fde047" opacity="0.5"/><path d="M0,90 Q25,60 50,90 T100,90 L100,100 L0,100 Z" fill="%23facc15" opacity="0.5"/></svg>'); }
        .pattern-people { background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="30" cy="40" r="12" fill="%23c084fc" opacity="0.6"/><circle cx="50" cy="30" r="15" fill="%23a855f7" opacity="0.7"/><circle cx="70" cy="40" r="12" fill="%23c084fc" opacity="0.6"/><path d="M10,90 Q30,60 50,60 Q70,60 90,90" fill="%23a855f7" opacity="0.6"/></svg>'); }

        .overview-title { font-size: 0.8rem; font-weight: 900; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; text-transform: uppercase; position: relative; z-index: 2; }
        .overview-val { font-size: 2.2rem; font-weight: 900; line-height: 1.05; letter-spacing: -1px; position: relative; z-index: 2; }
        .overview-icon-bg {
            position: absolute; right: 20px; top: 22px;
            width: 50px; height: 50px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1.3rem;
            box-shadow: 0 8px 18px rgba(0,0,0,0.15); z-index: 2;
            border: 3px solid #ffffff;
        }
        .icon-yellow { background: linear-gradient(135deg, #f59e0b, #eab308); }
        .icon-blue { background: linear-gradient(135deg, #0284c7, #38bdf8); }
        .icon-purple { background: linear-gradient(135deg, #9333ea, #a855f7); }

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
            font-size: 0.78rem;
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
            background: var(--teal-main);
            color: white;
            padding: 6px 14px;
            border-radius: 50px;
            font-weight: 900;
            font-size: 0.85rem;
            box-shadow: 0 4px 10px rgba(48, 143, 134, 0.25);
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .badge-period-pill {
            background: #f1f5f9;
            color: #334155;
            border: 2px solid #cbd5e1;
            padding: 5px 14px;
            border-radius: 50px;
            font-weight: 800;
            font-size: 0.82rem;
        }
        .meter-box {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 16px;
            padding: 8px 12px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .usage-pill-elec {
            background: #fef3c7;
            color: #b45309;
            border: 1.5px solid #fde68a;
            padding: 4px 11px;
            border-radius: 50px;
            font-weight: 800;
            font-size: 0.76rem;
        }
        .usage-pill-water {
            background: #e0f2fe;
            color: #0369a1;
            border: 1.5px solid #bae6fd;
            padding: 4px 11px;
            border-radius: 50px;
            font-weight: 800;
            font-size: 0.76rem;
        }

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

        .footer-custom { background: #ffffff; padding: 20px 0; color: #64748b; font-size: 0.85rem; font-weight: 700; margin-top: auto; border-top: 1px solid #f1f5f9; position: relative; z-index: 10; }

        @media print {
            .navbar-custom, .btn-back-content, .no-print, .footer-custom { display: none !important; }
            body { background: #fff !important; }
            .section-box { box-shadow: none !important; border: 1px solid #ccc !important; }
        }
    </style>
</head>
<body>

    <!-- ================= NAVBAR CHUẨN STAYMATE ================= -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container-fluid px-4 px-xl-5 d-flex justify-content-between align-items-center">
            <a class="brand-logo me-4" href="index.php?route=dashboard">
                <i class="fa-solid fa-house-chimney text-teal-main"></i>
                <div>
                    <div class="brand-text">StayMate</div>
                    <div class="brand-sub">Quản Lý Nhà Trọ</div>
                </div>
            </a>

            <ul class="nav nav-pills me-auto d-none d-lg-flex">
                <li class="nav-item"><a class="nav-link" href="index.php?route=dashboard"><i class="fa-solid fa-city"></i> Tòa nhà</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=overview"><i class="fa-solid fa-border-all"></i> Tổng quan</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=rooms"><i class="fa-solid fa-bed"></i> Phòng trọ</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=contracts"><i class="fa-solid fa-file-signature"></i> Hợp đồng</a></li>
                <li class="nav-item"><a class="nav-link active" href="index.php?route=utilities"><i class="fa-solid fa-bolt"></i> Điện Nước</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=invoices"><i class="fa-solid fa-file-invoice-dollar"></i> Hóa đơn</a></li>
                <li class="nav-item"><a class="nav-link" href="TrangXemPhong/" target="_blank"><i class="fa-solid fa-eye"></i> Xem phòng</a></li>
            </ul>

            <div class="d-flex align-items-center gap-4">
                <div class="position-relative" style="color: #64748b; cursor: pointer;" title="Phòng chưa chốt kỳ <?= htmlspecialchars($activeCheckPeriod) ?>">
                    <i class="fa-solid fa-bell fs-4"></i>
                    <?php if (count($unrecordedRooms) > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white">
                            <?= count($unrecordedRooms) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="dropdown">
                    <div class="d-flex align-items-center gap-2" style="cursor: pointer;" data-bs-toggle="dropdown">
                        <div class="avatar-circle">AS</div>
                        <div class="text-start d-none d-sm-block lh-sm">
                            <div class="fw-bold text-dark" style="font-size: 0.9rem;">Quản trị viên Hệ thống <i class="fa-solid fa-angle-down ms-1 text-muted fs-6"></i></div>
                            <div class="text-muted" style="font-size: 0.75rem;">Admin</div>
                        </div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 mt-2">
                        <li><a class="dropdown-item text-danger fw-bold" href="index.php?route=auth/logout"><i class="fa-solid fa-right-from-bracket me-2"></i> Đăng xuất</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- ================= MAIN CONTAINER ================= -->
    <div class="container-fluid px-4 px-xl-5 py-4 position-relative">

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 no-print">
            <a href="index.php?route=overview" class="btn-back-content mb-2">
                <i class="fa-solid fa-arrow-left me-2"></i> Quay lại Tổng quan
            </a>

            <!-- Chọn nhanh Kỳ chốt để xem thống kê -->
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="fw-bold text-muted small"><i class="fa-regular fa-calendar-check me-1 text-teal-main"></i> Kỳ đang xem:</span>
                <?php foreach (array_slice($availablePeriods, 0, 5) as $per): ?>
                    <a href="index.php?route=utilities&period=<?= urlencode($per) ?>" 
                       class="btn btn-sm rounded-pill fw-bold px-3 <?= ($selectedPeriod === $per) ? 'btn-teal-custom py-1 px-3' : 'btn-pill-white py-1 px-3' ?>">
                        Kỳ <?= htmlspecialchars($per) ?>
                    </a>
                <?php endforeach; ?>
                <a href="index.php?route=utilities&period=all" 
                   class="btn btn-sm rounded-pill fw-bold px-3 <?= ($selectedPeriod === 'all') ? 'btn-teal-custom py-1 px-3' : 'btn-pill-white py-1 px-3' ?>">
                    Tất cả các kỳ
                </a>
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
                        <span class="title-sparkle ts-1"></span>
                        <span class="title-sparkle ts-2"></span>
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

            <div class="header-house-graphic d-none d-lg-block"></div>
        </div>

        <!-- THÔNG BÁO FLASH / CRUD -->
        <?php if ($msg): ?>
            <div class="alert alert-<?= htmlspecialchars($msgType) ?> alert-dismissible fade show rounded-pill px-4 py-3 border-2 shadow-sm fw-bold position-relative z-2 mb-4" role="alert">
                <i class="fa-solid <?= $msgType === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation' ?> me-2 fs-5 align-middle"></i>
                <?= htmlspecialchars($msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- ================= 4 THẺ OVERVIEW CARDS ĐỒNG BỘ TRANG TỔNG QUAN ================= -->
        <div class="row g-4 mb-4 position-relative z-2">
            <!-- Card 1: Tiến độ chốt số -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="overview-card card-green">
                    <div class="card-bg-pattern pattern-house"></div>
                    <div class="overview-title text-success">
                        <i class="fa-solid fa-clipboard-check fs-6"></i> TIẾN ĐỘ CHỐT KỲ <?= htmlspecialchars($activeCheckPeriod) ?>
                    </div>
                    <div class="d-flex align-items-baseline mb-2 position-relative z-2">
                        <span class="overview-val text-dark me-2"><?= $recordedRentedCount ?> / <?= $totalRentedCount ?></span>
                        <span class="fw-bold text-muted small">phòng đang thuê</span>
                    </div>
                    <div class="progress mb-2 position-relative z-2" style="height: 9px; background-color: rgba(255,255,255,0.75); border-radius: 10px;">
                        <div class="progress-bar bg-success" style="width: <?= $progressPercent ?>%; border-radius: 10px;"></div>
                    </div>
                    <div class="d-flex justify-content-between text-success position-relative z-2" style="font-size: 0.8rem; font-weight: 800;">
                        <span><i class="fa-solid fa-circle-check"></i> <?= $progressPercent ?>% hoàn tất</span>
                        <span class="<?= count($unrecordedRooms) > 0 ? 'text-danger' : 'text-muted' ?>">
                            <?= count($unrecordedRooms) ?> phòng chưa ghi
                        </span>
                    </div>
                </div>
            </div>

            <!-- Card 2: Tổng Điện tiêu thụ -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="overview-card card-yellow">
                    <div class="card-bg-pattern pattern-wave-yellow"></div>
                    <div class="overview-icon-bg icon-yellow"><i class="fa-solid fa-bolt"></i></div>
                    <div class="overview-title" style="color: #b45309;">
                        <i class="fa-solid fa-plug-circle-bolt fs-6"></i> TỔNG ĐIỆN TIÊU THỤ
                    </div>
                    <div class="d-flex align-items-baseline mt-2 position-relative z-2">
                        <span class="overview-val me-2" style="color: #b45309;"><?= number_format($totalElecKwh, 0, ',', '.') ?></span>
                        <span class="fw-bold small" style="color: #92400e;">kWh (số)</span>
                    </div>
                    <div class="mt-3 position-relative z-2" style="font-size: 0.82rem; font-weight: 800; color: #b45309;">
                        <i class="fa-solid fa-coins me-1"></i> Thành tiền: <span class="text-dark"><?= number_format($totalElecMoney, 0, ',', '.') ?> đ</span>
                    </div>
                </div>
            </div>

            <!-- Card 3: Tổng Nước tiêu thụ -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="overview-card card-blue">
                    <div class="card-bg-pattern pattern-wave"></div>
                    <div class="overview-icon-bg icon-blue"><i class="fa-solid fa-droplet"></i></div>
                    <div class="overview-title text-primary">
                        <i class="fa-solid fa-faucet-drip fs-6"></i> TỔNG NƯỚC TIÊU THỤ
                    </div>
                    <div class="d-flex align-items-baseline mt-2 position-relative z-2">
                        <span class="overview-val text-dark me-2"><?= number_format($totalWaterM3, 0, ',', '.') ?></span>
                        <span class="fw-bold text-primary small">Khối (m³)</span>
                    </div>
                    <div class="mt-3 text-primary position-relative z-2" style="font-size: 0.82rem; font-weight: 800;">
                        <i class="fa-solid fa-coins me-1"></i> Thành tiền: <span class="text-dark"><?= number_format($totalWaterMoney, 0, ',', '.') ?> đ</span>
                    </div>
                </div>
            </div>

            <!-- Card 4: Tổng tiền Điện Nước & Hóa đơn -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="overview-card card-purple">
                    <div class="card-bg-pattern pattern-people"></div>
                    <div class="overview-icon-bg icon-purple"><i class="fa-solid fa-sack-dollar"></i></div>
                    <div class="overview-title" style="color: #7e22ce;">
                        <i class="fa-solid fa-calculator fs-6"></i> TỔNG THU ĐIỆN NƯỚC
                    </div>
                    <div class="overview-val text-dark mt-2 position-relative z-2" style="font-size: 1.85rem;">
                        <?= number_format($totalUtilityMoney, 0, ',', '.') ?> đ
                    </div>
                    <div class="mt-3 position-relative z-2 d-flex justify-content-between" style="font-size: 0.8rem; font-weight: 800; color: #7e22ce;">
                        <span><i class="fa-solid fa-file-invoice me-1"></i> Đã lập: <?= $invoicesCreatedCount ?> HĐ</span>
                        <?php if ($invoicesPendingCount > 0): ?>
                            <span class="text-danger">Chờ lập: <?= $invoicesPendingCount ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= KHU VỰC 1: SƠ ĐỒ TRẠNG THÁI CHỐT SỐ CÁC PHÒNG ĐANG THUÊ ================= -->
        <div class="section-box no-print">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 pb-2 border-bottom border-light">
                <div>
                    <h6 class="fw-black mb-0 text-dark" style="font-weight: 900; font-size: 1.05rem;">
                        <i class="fa-solid fa-Grip text-teal-main me-2"></i>
                         Sơ đồ Chốt Chỉ số Các Phòng Đang Thuê — <span class="text-teal-main">Kỳ <?= htmlspecialchars($activeCheckPeriod) ?></span>
                    </h6>
                    <div class="text-muted small fw-semibold">Bấm vào phòng màu vàng để chốt nhanh chỉ số (hệ thống tự động điền chỉ số cũ kỳ trước)</div>
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
                    <div class="room-mini-card <?= $hasRec ? 'done' : 'pending' ?>">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge-room-pill">
                                <i class="fa-solid fa-door-closed"></i> <?= htmlspecialchars($rm['room_number']) ?>
                            </span>
                            <?php if ($hasRec): ?>
                                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle fw-bold px-2 py-1">
                                    <i class="fa-solid fa-check-circle me-1"></i> Đã chốt
                                </span>
                            <?php else: ?>
                                <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning fw-bold px-2 py-1">
                                    <i class="fa-regular fa-clock me-1"></i> Chưa chốt
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="small fw-bold text-secondary mb-2 text-truncate">
                            <i class="fa-regular fa-user me-1"></i> <?= htmlspecialchars($rm['tenant_name'] ?? 'Đang thuê') ?>
                        </div>

                        <?php if ($hasRec): 
                            $eU = max(0, (int)$recData['electric_new'] - (int)$recData['electric_old']);
                            $wU = max(0, (int)$recData['water_new'] - (int)$recData['water_old']);
                        ?>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top border-success-subtle small fw-bold">
                                <span class="text-warning-emphasis"><i class="fa-solid fa-bolt text-warning"></i> +<?= $eU ?> kWh</span>
                                <span class="text-info-emphasis"><i class="fa-solid fa-droplet text-info"></i> +<?= $wU ?> m³</span>
                                <span class="text-teal-main"><?= number_format(($eU * $elecPrice) + ($wU * $waterPrice), 0, ',', '.') ?>đ</span>
                            </div>
                        <?php else: ?>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top border-warning-subtle">
                                <span class="text-muted" style="font-size: 0.76rem; font-weight: 700;">
                                    Số cũ: Đ <?= (int)($rm['last_electric'] ?? 0) ?> | N <?= (int)($rm['last_water'] ?? 0) ?>
                                </span>
                                <button type="button" class="btn btn-sm rounded-pill fw-bold px-3 py-1" 
                                        style="background: #f59e0b; color: white; font-size: 0.76rem;"
                                        onclick="openAddModalForRoom(<?= $rId ?>, '<?= htmlspecialchars($activeCheckPeriod) ?>')">
                                    <i class="fa-solid fa-pen-nib me-1"></i> Ghi số
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ================= KHU VỰC 2: BỘ LỌC, TÌM KIẾM LIVE API & BẢNG DỮ LIỆU ================= -->
        <div class="section-box">
            <!-- Thanh công cụ lọc -->
            <div class="row g-3 align-items-end mb-4 pb-3 border-bottom border-light no-print">
                <div class="col-12 col-lg-4">
                    <label class="form-label fw-bold text-teal-main text-uppercase mb-1" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Tìm kiếm tự động (Live Search API)
                    </label>
                    <div class="position-relative">
                        <input type="text" id="searchInput" class="form-control filter-control ps-4" 
                               placeholder="Gõ số phòng (P101), tên khách thuê hoặc kỳ...">
                        <span id="loadingIndicator" style="display: none; position: absolute; right: 16px; top: 50%; transform: translateY(-50%);" class="text-teal-main">
                            <i class="fa-solid fa-spinner fa-spin"></i>
                        </span>
                    </div>
                    <div id="errorMsg" style="display: none;" class="text-danger mt-1 fw-bold small"></div>
                </div>

                <div class="col-6 col-md-3 col-lg-2">
                    <label class="form-label fw-bold text-muted text-uppercase mb-1" style="font-size: 0.75rem;">Kỳ chốt</label>
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
                    <label class="form-label fw-bold text-muted text-uppercase mb-1" style="font-size: 0.75rem;">Phòng trọ</label>
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
                    <label class="form-label fw-bold text-muted text-uppercase mb-1" style="font-size: 0.75rem;">Trạng thái HĐ</label>
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
                <div class="fw-black text-dark" style="font-weight: 900; font-size: 1.05rem;">
                    <i class="fa-solid fa-list-check me-2 text-teal-main"></i>
                    Bảng Chi Tiết Chỉ Số &amp; Tính Tiền Điện Nước (<span id="recordCount"><?= count($readings) ?></span> bản ghi)
                </div>
                <div class="text-muted small fw-bold">
                    Tự động tính theo đơn giá: Điện <?= number_format($elecPrice, 0, ',', '.') ?>đ • Nước <?= number_format($waterPrice, 0, ',', '.') ?>đ
                </div>
            </div>

            <!-- Bảng dữ liệu -->
            <div class="table-rounded-wrap table-responsive">
                <table class="table table-staymate align-middle" id="utilitiesTable">
                    <thead>
                        <tr>
                            <th class="ps-4">PHÒNG &amp; KHÁCH THUÊ</th>
                            <th>KỲ CHỐT</th>
                            <th>CHỈ SỐ ĐIỆN (CŨ → MỚI)</th>
                            <th>CHỈ SỐ NƯỚC (CŨ → MỚI)</th>
                            <th>TỔNG ĐIỆN + NƯỚC</th>
                            <th>TRẠNG THÁI HÓA ĐƠN</th>
                            <th class="text-end pe-4 no-print">THAO TÁC</th>
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
                                $highUsageWarn = ($dienTieuThu >= 200 || $nuocTieuThu >= 25);
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge-room-pill"><?= htmlspecialchars($row['room_number']) ?></span>
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size: 0.86rem;">
                                                <?= htmlspecialchars($row['tenant_name'] ?? 'Khách thuê phòng') ?>
                                            </div>
                                            <div class="text-muted" style="font-size: 0.74rem;">
                                                Giá phòng: <?= number_format((float)($row['room_price'] ?? 0), 0, ',', '.') ?>đ
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="badge-period-pill">
                                        <i class="fa-regular fa-calendar me-1 text-teal-main"></i><?= htmlspecialchars($row['month_year']) ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="meter-box">
                                                <span class="text-muted fw-bold"><?= (int)$row['electric_old'] ?></span>
                                                <i class="fa-solid fa-arrow-right text-warning" style="font-size: 0.75rem;"></i>
                                                <span class="fw-black text-dark"><?= (int)$row['electric_new'] ?></span>
                                            </div>
                                            <span class="usage-pill-elec">
                                                <i class="fa-solid fa-bolt me-1"></i>+<?= $dienTieuThu ?> kWh
                                            </span>
                                            <?php if ($dienTieuThu >= 200): ?>
                                                <span class="badge rounded-pill bg-danger-subtle text-danger" title="Tiêu thụ điện cao đột biến!">
                                                    <i class="fa-solid fa-fire"></i> Cao
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.78rem; font-weight: 700;">
                                            Thành tiền: <span class="text-dark fw-bold"><?= number_format($tienDien, 0, ',', '.') ?> đ</span>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="meter-box">
                                                <span class="text-muted fw-bold"><?= (int)$row['water_old'] ?></span>
                                                <i class="fa-solid fa-arrow-right text-info" style="font-size: 0.75rem;"></i>
                                                <span class="fw-black text-dark"><?= (int)$row['water_new'] ?></span>
                                            </div>
                                            <span class="usage-pill-water">
                                                <i class="fa-solid fa-droplet me-1"></i>+<?= $nuocTieuThu ?> m³
                                            </span>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.78rem; font-weight: 700;">
                                            Thành tiền: <span class="text-dark fw-bold"><?= number_format($tienNuoc, 0, ',', '.') ?> đ</span>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="fw-black text-teal-main" style="font-size: 1rem; font-weight: 900;">
                                        <?= number_format($tongDienNuoc, 0, ',', '.') ?> đ
                                    </div>
                                    <div class="text-muted" style="font-size: 0.73rem;">
                                        Tạm tính cả phòng: <strong><?= number_format((float)$row['room_price'] + $tongDienNuoc + $otherServicesTotal, 0, ',', '.') ?> đ</strong>
                                    </div>
                                </td>

                                <td>
                                    <?php if (!empty($row['invoice_id'])): ?>
                                        <?php if ($row['invoice_status'] === 'paid'): ?>
                                            <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-2 fw-bold">
                                                <i class="fa-solid fa-circle-check me-1"></i> Đã thu tiền HĐ
                                            </span>
                                        <?php else: ?>
                                            <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning px-3 py-2 fw-bold">
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
                                </td>

                                <td class="text-end pe-4 no-print">
                                    <div class="d-inline-flex align-items-center gap-2">
                                        <button type="button" class="btn-icon-circle btn-icon-view" title="Xem phiếu tính tiền chi tiết"
                                            onclick="openReceiptModal('<?= htmlspecialchars($row['room_number']) ?>', '<?= htmlspecialchars($row['tenant_name'] ?? 'Khách thuê') ?>', '<?= htmlspecialchars($row['month_year']) ?>', <?= (int)$row['electric_old'] ?>, <?= (int)$row['electric_new'] ?>, <?= (int)$row['water_old'] ?>, <?= (int)$row['water_new'] ?>, <?= (float)($row['room_price'] ?? 0) ?>)">
                                            <i class="fa-solid fa-receipt"></i>
                                        </button>
                                        <button type="button" class="btn-icon-circle btn-icon-edit" title="Chỉnh sửa chỉ số"
                                            onclick="openEditModal(<?= (int)$row['id'] ?>, '<?= htmlspecialchars($row['room_number']) ?>', '<?= htmlspecialchars($row['month_year']) ?>', <?= (int)$row['electric_old'] ?>, <?= (int)$row['electric_new'] ?>, <?= (int)$row['water_old'] ?>, <?= (int)$row['water_new'] ?>)">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <a href="index.php?route=utilities&period=<?= urlencode($selectedPeriod) ?>&action=delete&id=<?= (int)$row['id'] ?>" 
                                           class="btn-icon-circle btn-icon-del" title="Xóa bản ghi"
                                           onclick="return confirm('Bạn có chắc chắn muốn xóa chỉ số phòng <?= htmlspecialchars($row['room_number']) ?> kỳ <?= htmlspecialchars($row['month_year']) ?> không?');">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-folder-open fs-1 text-teal-main opacity-25 mb-3 d-block"></i>
                                    <p class="fw-bold mb-2">Chưa có dữ liệu chỉ số điện nước trong kỳ đang chọn.</p>
                                    <button type="button" class="btn-teal-custom btn-sm" onclick="openAddModal()">
                                        <i class="fa-solid fa-plus"></i> Chốt chỉ số ngay
                                    </button>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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

        // Mở Modal Thêm mới chung
        function openAddModal() {
            const modalEl = document.getElementById('addUtilityModal');
            new bootstrap.Modal(modalEl).show();
        }

        // Mở Modal Thêm mới và chọn sẵn phòng + tự động lấy chỉ số cũ
        function openAddModalForRoom(roomId, periodStr) {
            const selectEl = document.getElementById('add_room_id');
            selectEl.value = String(roomId);
            if (periodStr && periodStr !== 'all') {
                document.getElementById('add_month_year').value = periodStr;
            }
            onRoomSelectChange();
            new bootstrap.Modal(document.getElementById('addUtilityModal')).show();
        }

        // Khi chọn phòng trong Modal Thêm mới -> Tự động điền chỉ số cũ kỳ trước
        function onRoomSelectChange() {
            const selectEl = document.getElementById('add_room_id');
            const opt = selectEl.options[selectEl.selectedIndex];
            const hintEl = document.getElementById('add_room_hint');
            if (!opt || !opt.value) {
                hintEl.textContent = '';
                return;
            }
            const lastElec = parseInt(opt.getAttribute('data-last-elec') || '0', 10);
            const lastWater = parseInt(opt.getAttribute('data-last-water') || '0', 10);
            const lastPeriod = opt.getAttribute('data-last-period') || '';
            const tenant = opt.getAttribute('data-tenant') || '';

            document.getElementById('add_elec_old').value = lastElec;
            document.getElementById('add_water_old').value = lastWater;
            document.getElementById('add_elec_new').min = lastElec;
            document.getElementById('add_water_new').min = lastWater;

            hintEl.innerHTML = `<i class="fa-solid fa-wand-magic-sparkles me-1"></i>Khách: <strong>${tenant}</strong> • Tự động lấy chỉ số cũ từ kỳ gần nhất (${lastPeriod}): Điện <strong>${lastElec}</strong>, Nước <strong>${lastWater}</strong>`;
            calcLivePreview('add');
        }

        // Tính nhẩm trực tiếp số tiêu thụ & thành tiền trong Modal Add / Edit
        function calcLivePreview(mode) {
            const eOld = parseInt(document.getElementById(mode === 'add' ? 'add_elec_old' : 'edit_electric_old').value || '0', 10);
            const eNewRaw = document.getElementById(mode === 'add' ? 'add_elec_new' : 'edit_electric_new').value;
            const wOld = parseInt(document.getElementById(mode === 'add' ? 'add_water_old' : 'edit_water_old').value || '0', 10);
            const wNewRaw = document.getElementById(mode === 'add' ? 'add_water_new' : 'edit_water_new').value;

            const eNew = eNewRaw === '' ? eOld : parseInt(eNewRaw, 10);
            const wNew = wNewRaw === '' ? wOld : parseInt(wNewRaw, 10);

            const warnEl = document.getElementById(mode + '_live_warning');
            const submitBtn = document.getElementById(mode + '_submit_btn');

            if ((eNewRaw !== '' && eNew < eOld) || (wNewRaw !== '' && wNew < wOld)) {
                warnEl.style.display = 'block';
                warnEl.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i> Lỗi: Chỉ số MỚI không được nhỏ hơn chỉ số CŨ!';
                submitBtn.disabled = true;
            } else {
                submitBtn.disabled = false;
                const eDiff = Math.max(0, eNew - eOld);
                const wDiff = Math.max(0, wNew - wOld);
                if (eDiff >= 300 || wDiff >= 35) {
                    warnEl.style.display = 'block';
                    warnEl.className = 'alert alert-warning rounded-4 py-2 px-3 small fw-bold mb-3';
                    warnEl.innerHTML = `<i class="fa-solid fa-bell me-1"></i> Cảnh báo: Mức tiêu thụ khá cao (+${eDiff} kWh, +${wDiff} m³). Vui lòng kiểm tra kỹ tránh nhập nhầm số đồng hồ!`;
                } else {
                    warnEl.style.display = 'none';
                    warnEl.className = 'alert alert-danger rounded-4 py-2 px-3 small fw-bold mb-3';
                }
            }

            const eUsed = Math.max(0, eNew - eOld);
            const wUsed = Math.max(0, wNew - wOld);
            const eCost = eUsed * ELEC_PRICE;
            const wCost = wUsed * WATER_PRICE;

            document.getElementById(mode + '_elec_used').textContent = eUsed + ' kWh';
            document.getElementById(mode + '_elec_cost').textContent = formatVND(eCost);
            document.getElementById(mode + '_water_used').textContent = wUsed + ' m³';
            document.getElementById(mode + '_water_cost').textContent = formatVND(wCost);
            document.getElementById(mode + '_total_utility').textContent = formatVND(eCost + wCost);
        }

        // Mở Modal Sửa chỉ số
        function openEditModal(id, room, month, e_old, e_new, w_old, w_new) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_title').textContent = 'Phòng ' + room + ' — Kỳ ' + month;
            document.getElementById('edit_electric_old').value = e_old;
            document.getElementById('edit_electric_new').value = e_new;
            document.getElementById('edit_water_old').value = w_old;
            document.getElementById('edit_water_new').value = w_new;
            calcLivePreview('edit');
            new bootstrap.Modal(document.getElementById('editUtilityModal')).show();
        }

        // Mở Modal Phiếu báo chi tiết
        function openReceiptModal(room, tenant, month, eOld, eNew, wOld, wNew, roomPrice) {
            const eUsed = Math.max(0, eNew - eOld);
            const wUsed = Math.max(0, wNew - wOld);
            const eCost = eUsed * ELEC_PRICE;
            const wCost = wUsed * WATER_PRICE;
            const totalAll = roomPrice + eCost + wCost + OTHER_SERVICES_TOTAL;

            document.getElementById('receiptModalBody').innerHTML = `
                <div class="p-3 rounded-4 border bg-light mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge-room-pill">Phòng ${room}</span>
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
            new bootstrap.Modal(document.getElementById('receiptModal')).show();
        }

        // Chuyển bộ lọc
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

        // Xuất bảng ra CSV (mở được bằng Excel)
        function exportTableToCSV() {
            const rows = [['Phong', 'Khach Thue', 'Ky Chot', 'Dien Cu', 'Dien Moi', 'Dien Tieu Thu (kWh)', 'Nuoc Cu', 'Nuoc Moi', 'Nuoc Tieu Thu (m3)', 'Tong Tien Dien Nuoc']];
            document.querySelectorAll('#tableBody tr').forEach(tr => {
                const tds = tr.querySelectorAll('td');
                if (tds.length < 5) return;
                const room = tds[0].querySelector('.badge-room-pill')?.textContent.trim() || '';
                const tenant = tds[0].querySelector('.fw-bold.text-dark')?.textContent.trim() || '';
                const period = tds[1].textContent.trim();
                const elecBox = tds[2].querySelectorAll('.meter-box span');
                const eOld = elecBox[0]?.textContent.trim() || '0';
                const eNew = elecBox[1]?.textContent.trim() || '0';
                const eUsed = String(Math.max(0, parseInt(eNew) - parseInt(eOld)));
                const waterBox = tds[3].querySelectorAll('.meter-box span');
                const wOld = waterBox[0]?.textContent.trim() || '0';
                const wNew = waterBox[1]?.textContent.trim() || '0';
                const wUsed = String(Math.max(0, parseInt(wNew) - parseInt(wOld)));
                const total = tds[4].querySelector('.fw-black')?.textContent.trim() || '';
                rows.push([room, tenant, period, eOld, eNew, eUsed, wOld, wNew, wUsed, total]);
            });
            const csvContent = '\uFEFF' + rows.map(r => r.map(c => `"${String(c).replace(/"/g, '""')}"`).join(',')).join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'Bang_Chot_Dien_Nuoc_StayMate.csv';
            link.click();
        }

        // ===============================================================
        // FETCH API: LIVE SEARCH CẬP NHẬT DOM AN TOÀN
        // ===============================================================
        let typingTimer;
        const searchInput = document.getElementById('searchInput');
        const tableBody = document.getElementById('tableBody');
        const loadingIndicator = document.getElementById('loadingIndicator');
        const errorMsg = document.getElementById('errorMsg');
        const recordCount = document.getElementById('recordCount');

        searchInput.addEventListener('input', () => {
            clearTimeout(typingTimer);
            loadingIndicator.style.display = 'inline-block';
            errorMsg.style.display = 'none';

            typingTimer = setTimeout(async () => {
                const keyword = searchInput.value;
                const period = document.getElementById('periodFilter').value;
                const periodParam = period === 'all' ? '' : period;
                try {
                    const res = await fetch(`index.php?route=api/utilities/search&q=${encodeURIComponent(keyword)}&month_year=${encodeURIComponent(periodParam)}`);
                    if (!res.ok) throw new Error('HTTP Error: ' + res.status);

                    const json = await res.json();
                    tableBody.innerHTML = '';
                    loadingIndicator.style.display = 'none';
                    recordCount.textContent = json.data ? json.data.length : 0;

                    if (!json.data || json.data.length === 0) {
                        tableBody.innerHTML = '<tr><td colspan="7" class="text-center py-5 text-muted"><i class="fa-solid fa-folder-open fs-1 opacity-25 mb-3 d-block"></i>Không tìm thấy bản ghi phù hợp với từ khóa.</td></tr>';
                        return;
                    }

                    json.data.forEach(item => {
                        const tr = document.createElement('tr');
                        const dienTieuThu = Math.max(0, item.electric_new - item.electric_old);
                        const nuocTieuThu = Math.max(0, item.water_new - item.water_old);
                        const tienDien = dienTieuThu * ELEC_PRICE;
                        const tienNuoc = nuocTieuThu * WATER_PRICE;
                        const tongDienNuoc = tienDien + tienNuoc;
                        const roomPrice = parseFloat(item.room_price || 0);

                        let invHtml = '';
                        if (item.invoice_id) {
                            if (item.invoice_status === 'paid') {
                                invHtml = '<span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-2 fw-bold"><i class="fa-solid fa-circle-check me-1"></i> Đã thu tiền HĐ</span>';
                            } else {
                                invHtml = '<span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning px-3 py-2 fw-bold"><i class="fa-solid fa-file-invoice-dollar me-1"></i> Đã lập HĐ (Chờ thu)</span>';
                            }
                        } else {
                            invHtml = `
                                <form method="POST" action="index.php?route=utilities" class="d-inline">
                                    <input type="hidden" name="action" value="create_invoice">
                                    <input type="hidden" name="reading_id" value="${Number(item.id)}">
                                    <button type="submit" class="btn-create-inv">
                                        <i class="fa-solid fa-wand-magic-sparkles"></i> Lập Hóa Đơn
                                    </button>
                                </form>
                            `;
                        }

                        tr.innerHTML = `
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge-room-pill room-cell"></span>
                                    <div>
                                        <div class="fw-bold text-dark tenant-cell" style="font-size: 0.86rem;"></div>
                                        <div class="text-muted" style="font-size: 0.74rem;">Giá phòng: ${formatVND(roomPrice)}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge-period-pill"><i class="fa-regular fa-calendar me-1 text-teal-main"></i><span class="period-cell"></span></span>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="meter-box">
                                            <span class="text-muted fw-bold">${Number(item.electric_old)}</span>
                                            <i class="fa-solid fa-arrow-right text-warning" style="font-size: 0.75rem;"></i>
                                            <span class="fw-black text-dark">${Number(item.electric_new)}</span>
                                        </div>
                                        <span class="usage-pill-elec"><i class="fa-solid fa-bolt me-1"></i>+${dienTieuThu} kWh</span>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.78rem; font-weight: 700;">Thành tiền: <span class="text-dark fw-bold">${formatVND(tienDien)}</span></div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="meter-box">
                                            <span class="text-muted fw-bold">${Number(item.water_old)}</span>
                                            <i class="fa-solid fa-arrow-right text-info" style="font-size: 0.75rem;"></i>
                                            <span class="fw-black text-dark">${Number(item.water_new)}</span>
                                        </div>
                                        <span class="usage-pill-water"><i class="fa-solid fa-droplet me-1"></i>+${nuocTieuThu} m³</span>
                                    </div>
                                    <div class="text-muted" style="font-size: 0.78rem; font-weight: 700;">Thành tiền: <span class="text-dark fw-bold">${formatVND(tienNuoc)}</span></div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-black text-teal-main" style="font-size: 1rem; font-weight: 900;">${formatVND(tongDienNuoc)}</div>
                                <div class="text-muted" style="font-size: 0.73rem;">Tạm tính cả phòng: <strong>${formatVND(roomPrice + tongDienNuoc + OTHER_SERVICES_TOTAL)}</strong></div>
                            </td>
                            <td>${invHtml}</td>
                            <td class="text-end pe-4 no-print">
                                <div class="d-inline-flex align-items-center gap-2">
                                    <button type="button" class="btn-icon-circle btn-icon-edit" title="Chỉnh sửa">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    <a href="index.php?route=utilities&action=delete&id=${Number(item.id)}" class="btn-icon-circle btn-icon-del" title="Xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa bản ghi chỉ số này không?');">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        `;

                        // Gán dữ liệu chuỗi bằng textContent để chống XSS
                        tr.querySelector('.room-cell').textContent = item.room_number;
                        tr.querySelector('.tenant-cell').textContent = item.tenant_name || 'Khách thuê phòng';
                        tr.querySelector('.period-cell').textContent = item.month_year;
                        tr.querySelector('.btn-icon-edit').addEventListener('click', () => {
                            openEditModal(Number(item.id), item.room_number, item.month_year, Number(item.electric_old), Number(item.electric_new), Number(item.water_old), Number(item.water_new));
                        });

                        tableBody.appendChild(tr);
                    });

                } catch (err) {
                    loadingIndicator.style.display = 'none';
                    errorMsg.style.display = 'block';
                    errorMsg.innerHTML = '<i class="fa-solid fa-triangle-exclamation me-1"></i> Gặp lỗi khi tải dữ liệu từ máy chủ API.';
                }
            }, 250);
        });
    </script>
</body>
</html>
