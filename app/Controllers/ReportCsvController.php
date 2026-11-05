<?php
require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../Models/ReportModel.php';

class ReportCsvController {
    private PDO $db;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = Database::getConnection();
    }

    /**
     * Phân quyền: Cả Admin và Quản lý tòa nhà (Manager) đều được xem báo cáo.
     * Admin xem toàn bộ 3 tòa, Quản lý chỉ xem tòa nhà mình phụ trách.
     */
    private function checkAuth(): void {
        $role = $_SESSION['role'] ?? '';
        if (!in_array($role, ['admin', 'manager', 'landlord'], true)) {
            $_SESSION['flash_error'] = 'Bạn không có quyền truy cập Báo cáo Doanh thu & Công nợ!';
            header('Location: index.php?route=overview');
            exit;
        }
    }

    /**
     * Trang xem báo cáo tổng thể, chỉ số hoạt động tòa nhà, chi tiết 3 tab và xuất CSV
     */
    public function index(): void {
        $this->checkAuth();

        $userRole = $_SESSION['role'] ?? 'admin';
        $isAdmin  = ($userRole === 'admin');

        // Lấy thông tin tòa nhà nếu người đăng nhập là Quản lý
        $mgrInfo = ReportModel::getManagerBuildingInfo();
        $isManager = (!$isAdmin && $mgrInfo !== null);
        $managerBuilding = $isManager ? $mgrInfo : null;

        if ($isManager) {
            $buildingId = (string)$mgrInfo['prefix']; // 'A', 'B' hoặc 'C'
            $mgrBId     = (int)$mgrInfo['id'];
            $mgrPrefix  = (string)$mgrInfo['prefix'];
        } else {
            $buildingId = trim($_GET['building_id'] ?? 'all');
            $mgrBId     = 0;
            $mgrPrefix  = '';
        }

        $monthYear  = trim($_GET['month_year'] ?? 'all');
        $status     = trim($_GET['status'] ?? 'all');
        $keyword    = trim($_GET['keyword'] ?? '');
        $export     = trim($_GET['export'] ?? '');

        // 1. Danh sách tòa nhà: Admin xem cả 3 tòa, Manager chỉ xem tòa mình quản lý
        if ($isManager) {
            $stmtB = $this->db->prepare("SELECT id, name, address FROM buildings WHERE id = :bid");
            $stmtB->execute([':bid' => $mgrBId]);
            $buildings = $stmtB->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $buildings = $this->db->query("SELECT id, name, address FROM buildings ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
        }

        // 2. Danh sách kỳ thu (tháng-năm)
        if ($isManager) {
            $stmtP = $this->db->prepare("
                SELECT DISTINCT i.month_year 
                FROM invoices i
                JOIN rooms r ON r.id = i.room_id
                WHERE r.building_id = :bid OR r.room_number LIKE :pfx
                ORDER BY i.month_year DESC
            ");
            $stmtP->execute([':bid' => $mgrBId, ':pfx' => $mgrPrefix . '%']);
            $periods = $stmtP->fetchAll(PDO::FETCH_COLUMN);
        } else {
            $periods = $this->db->query("SELECT DISTINCT month_year FROM invoices ORDER BY month_year DESC")->fetchAll(PDO::FETCH_COLUMN);
        }

        // 3. TÍNH TOÁN BẢNG MA TRẬN SO SÁNH / CHỈ SỐ HOẠT ĐỘNG
        $matrix = [];
        $systemSummary = [
            'total_rooms'       => 0,
            'rented_rooms'      => 0,
            'available_rooms'   => 0,
            'maintenance_rooms' => 0,
            'occupancy_rate'    => 0,
            'tenant_count'      => 0,
            'renewed_count'     => 0,
            'renewal_rate'      => 0,
            'deposit_held'      => 0,
            'total_invoiced'    => 0,
            'paid_amount'       => 0,
            'unpaid_amount'     => 0,
            'paid_count'        => 0,
            'unpaid_count'      => 0,
            'total_invoices'    => 0,
            'collection_rate'   => 0
        ];

        foreach ($buildings as $b) {
            $bId = (int)$b['id'];
            $bPrefix = ($bId === 1) ? 'A' : (($bId === 2) ? 'B' : 'C');

            // Số lượng phòng theo trạng thái
            $stmtRoom = $this->db->prepare("
                SELECT 
                    COUNT(*) AS total_rooms,
                    SUM(CASE WHEN status = 'rented' THEN 1 ELSE 0 END) AS rented_rooms,
                    SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) AS available_rooms,
                    SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) AS maintenance_rooms
                FROM rooms 
                WHERE building_id = :bId OR room_number LIKE :pfx
            ");
            $stmtRoom->execute([':bId' => $bId, ':pfx' => $bPrefix . '%']);
            $roomStats = $stmtRoom->fetch(PDO::FETCH_ASSOC);

            $totalR = (int)($roomStats['total_rooms'] ?? 0);
            $rentedR = (int)($roomStats['rented_rooms'] ?? 0);
            $availR = (int)($roomStats['available_rooms'] ?? 0);
            $maintR = (int)($roomStats['maintenance_rooms'] ?? 0);
            $occRate = $totalR > 0 ? round(($rentedR / $totalR) * 100, 1) : 0;

            // Số khách thuê đang ở
            $stmtTenants = $this->db->prepare("
                SELECT COUNT(DISTINCT c.tenant_id)
                FROM contracts c
                JOIN rooms r ON r.id = c.room_id
                WHERE (r.building_id = :bId OR r.room_number LIKE :pfx)
                  AND c.status = 'active'
            ");
            $stmtTenants->execute([':bId' => $bId, ':pfx' => $bPrefix . '%']);
            $tenantCount = (int)$stmtTenants->fetchColumn();

            // Số hợp đồng đã gia hạn (tái ký)
            $stmtRenewed = $this->db->prepare("
                SELECT COUNT(*)
                FROM contracts c
                JOIN rooms r ON r.id = c.room_id
                WHERE (r.building_id = :bId OR r.room_number LIKE :pfx)
                  AND c.status = 'active'
                  AND c.renewal_status = 'renewed'
            ");
            $stmtRenewed->execute([':bId' => $bId, ':pfx' => $bPrefix . '%']);
            $renewedCount = (int)$stmtRenewed->fetchColumn();
            $renewalRate = $tenantCount > 0 ? round(($renewedCount / $tenantCount) * 100, 1) : 0;

            // Tiền cọc đang giữ
            $stmtDeposit = $this->db->prepare("
                SELECT COALESCE(SUM(c.deposit_amount), 0)
                FROM contracts c
                JOIN rooms r ON r.id = c.room_id
                WHERE (r.building_id = :bId OR r.room_number LIKE :pfx)
                  AND c.status = 'active'
            ");
            $stmtDeposit->execute([':bId' => $bId, ':pfx' => $bPrefix . '%']);
            $depositHeld = (float)$stmtDeposit->fetchColumn();

            // Doanh thu thực thu & Công nợ
            $stmtInv = $this->db->prepare("
                SELECT 
                    COUNT(*) AS total_inv,
                    COALESCE(SUM(i.total_amount), 0) AS total_invoiced,
                    COALESCE(SUM(CASE WHEN i.status = 'paid' THEN i.total_amount ELSE 0 END), 0) AS paid_amount,
                    COALESCE(SUM(CASE WHEN i.status = 'unpaid' THEN i.total_amount ELSE 0 END), 0) AS unpaid_amount,
                    SUM(CASE WHEN i.status = 'paid' THEN 1 ELSE 0 END) AS paid_count,
                    SUM(CASE WHEN i.status = 'unpaid' THEN 1 ELSE 0 END) AS unpaid_count
                FROM invoices i
                JOIN rooms r ON r.id = i.room_id
                WHERE (r.building_id = :bId OR r.room_number LIKE :pfx)
            ");
            $stmtInv->execute([':bId' => $bId, ':pfx' => $bPrefix . '%']);
            $invStats = $stmtInv->fetch(PDO::FETCH_ASSOC);

            $invoiced = (float)($invStats['total_invoiced'] ?? 0);
            $paid = (float)($invStats['paid_amount'] ?? 0);
            $unpaid = (float)($invStats['unpaid_amount'] ?? 0);
            $paidCnt = (int)($invStats['paid_count'] ?? 0);
            $unpaidCnt = (int)($invStats['unpaid_count'] ?? 0);
            $totalInv = (int)($invStats['total_inv'] ?? 0);
            $collRate = $invoiced > 0 ? round(($paid / $invoiced) * 100, 1) : 0;

            $matrix[$bId] = [
                'id'                => $bId,
                'name'              => $b['name'],
                'prefix'            => $bPrefix,
                'total_rooms'       => $totalR,
                'rented_rooms'      => $rentedR,
                'available_rooms'   => $availR,
                'maintenance_rooms' => $maintR,
                'occupancy_rate'    => $occRate,
                'tenant_count'      => $tenantCount,
                'renewed_count'     => $renewedCount,
                'renewal_rate'      => $renewalRate,
                'deposit_held'      => $depositHeld,
                'total_invoices'    => $totalInv,
                'total_invoiced'    => $invoiced,
                'paid_amount'       => $paid,
                'unpaid_amount'     => $unpaid,
                'paid_count'        => $paidCnt,
                'unpaid_count'      => $unpaidCnt,
                'collection_rate'   => $collRate
            ];

            // Tích lũy toàn hệ thống / tòa nhà phụ trách
            $systemSummary['total_rooms']       += $totalR;
            $systemSummary['rented_rooms']      += $rentedR;
            $systemSummary['available_rooms']   += $availR;
            $systemSummary['maintenance_rooms'] += $maintR;
            $systemSummary['tenant_count']      += $tenantCount;
            $systemSummary['renewed_count']     += $renewedCount;
            $systemSummary['deposit_held']      += $depositHeld;
            $systemSummary['total_invoiced']    += $invoiced;
            $systemSummary['paid_amount']       += $paid;
            $systemSummary['unpaid_amount']     += $unpaid;
            $systemSummary['paid_count']        += $paidCnt;
            $systemSummary['unpaid_count']      += $unpaidCnt;
            $systemSummary['total_invoices']    += $totalInv;
        }

        if ($systemSummary['total_rooms'] > 0) {
            $systemSummary['occupancy_rate'] = round(($systemSummary['rented_rooms'] / $systemSummary['total_rooms']) * 100, 1);
        }
        if ($systemSummary['total_invoiced'] > 0) {
            $systemSummary['collection_rate'] = round(($systemSummary['paid_amount'] / $systemSummary['total_invoiced']) * 100, 1);
        }
        if ($systemSummary['tenant_count'] > 0) {
            $systemSummary['renewal_rate'] = round(($systemSummary['renewed_count'] / $systemSummary['tenant_count']) * 100, 1);
        }

        // 4. LẤY DỮ LIỆU TAB 1: DANH SÁCH HÓA ĐƠN CHI TIẾT
        $sqlInvoices = "
            SELECT 
                i.id,
                i.month_year,
                i.room_price,
                i.electric_amount,
                i.water_amount,
                i.service_fee,
                i.total_amount,
                i.status,
                i.paid_at,
                i.due_date,
                i.created_at,
                i.payment_method,
                r.room_number,
                r.price AS default_room_price,
                r.building_id,
                b.name AS building_name,
                u.full_name AS tenant_name,
                u.phone AS tenant_phone,
                u.email AS tenant_email
            FROM invoices i
            JOIN rooms r ON r.id = i.room_id
            LEFT JOIN buildings b ON b.id = r.building_id
            LEFT JOIN contracts c ON c.id = i.contract_id
            LEFT JOIN users u ON u.id = c.tenant_id
            WHERE 1=1
        ";
        $invParams = [];

        // Ràng buộc nghiêm ngặt với Quản lý
        if ($isManager) {
            $sqlInvoices .= " AND (r.building_id = :mgrBId OR r.room_number LIKE :mgrPfx)";
            $invParams[':mgrBId'] = $mgrBId;
            $invParams[':mgrPfx'] = $mgrPrefix . '%';
        }

        // Nếu người dùng nhấn Xuất CSV thì áp dụng lọc SQL để tải file đúng tiêu chí
        if ($export === 'csv') {
            if (!$isManager && $buildingId !== 'all' && $buildingId !== '') {
                $prefix = strtoupper($buildingId);
                $sqlInvoices .= " AND (r.building_id = :bId OR r.room_number LIKE :pfx)";
                $bIdNum = $prefix === 'A' ? 1 : ($prefix === 'B' ? 2 : ($prefix === 'C' ? 3 : (int)$buildingId));
                $invParams[':bId'] = $bIdNum;
                $invParams[':pfx'] = $prefix . '%';
            }

            if ($monthYear !== 'all' && $monthYear !== '') {
                $sqlInvoices .= " AND i.month_year = :period";
                $invParams[':period'] = $monthYear;
            }

            if ($status === 'paid' || $status === 'unpaid') {
                $sqlInvoices .= " AND i.status = :status";
                $invParams[':status'] = $status;
            }

            if ($keyword !== '') {
                $sqlInvoices .= " AND (r.room_number LIKE :kw OR u.full_name LIKE :kw OR u.phone LIKE :kw OR CONCAT('HD-', i.id) LIKE :kw)";
                $invParams[':kw'] = '%' . $keyword . '%';
            }
        }

        $sqlInvoices .= " ORDER BY r.room_number ASC, i.id DESC";
        $stmtInvoices = $this->db->prepare($sqlInvoices);
        $stmtInvoices->execute($invParams);
        $invoiceRows = $stmtInvoices->fetchAll(PDO::FETCH_ASSOC);

        // Tổng kết cho danh sách hóa đơn sau khi lọc
        $filterSummary = [
            'total_invoices' => count($invoiceRows),
            'total_amount'   => 0,
            'paid_amount'    => 0,
            'unpaid_amount'  => 0,
            'paid_count'     => 0,
            'unpaid_count'   => 0,
            'room_total'     => 0,
            'utility_total'  => 0,
        ];

        foreach ($invoiceRows as &$ir) {
            $amt = (float)$ir['total_amount'];
            $roomAmt = (float)$ir['room_price'];
            $elAmt = (float)$ir['electric_amount'];
            $wtAmt = (float)$ir['water_amount'];
            $svAmt = (float)$ir['service_fee'];
            $utilAmt = $elAmt + $wtAmt + $svAmt;

            // Nếu dữ liệu cũ chưa có breakdown, lấy giá phòng niêm yết
            if ($roomAmt <= 0 && $amt > 0) {
                $roomAmt = (float)($ir['default_room_price'] ?? 0);
                $utilAmt = max(0, $amt - $roomAmt);
            }

            $ir['computed_room'] = $roomAmt;
            $ir['computed_util'] = $utilAmt;

            $filterSummary['total_amount'] += $amt;
            $filterSummary['room_total'] += $roomAmt;
            $filterSummary['utility_total'] += $utilAmt;
            if ($ir['status'] === 'paid') {
                $filterSummary['paid_amount'] += $amt;
                $filterSummary['paid_count']++;
            } else {
                $filterSummary['unpaid_amount'] += $amt;
                $filterSummary['unpaid_count']++;
            }
        }
        unset($ir);

        // 5. LẤY DỮ LIỆU TAB 2: HIỆN TRẠNG PHÒNG & TIỀN CỌC GIỮ CHỖ
        $sqlRooms = "
            SELECT 
                r.id,
                r.room_number,
                r.building_id,
                b.name AS building_name,
                r.price,
                r.status,
                r.description,
                c.id AS contract_id,
                c.contract_code,
                c.contract_type,
                c.deposit_amount,
                c.deposit_status,
                c.start_date,
                c.end_date,
                u.full_name AS tenant_name,
                u.phone AS tenant_phone
            FROM rooms r
            LEFT JOIN buildings b ON b.id = r.building_id
            LEFT JOIN contracts c ON c.id = (
                SELECT c2.id FROM contracts c2
                WHERE c2.room_id = r.id AND c2.status = 'active'
                ORDER BY c2.id DESC LIMIT 1
            )
            LEFT JOIN users u ON u.id = c.tenant_id
        ";
        if ($isManager) {
            $sqlRooms .= " WHERE (r.building_id = :mgrBId OR r.room_number LIKE :mgrPfx)";
            $sqlRooms .= " ORDER BY r.room_number ASC";
            $stmtRooms = $this->db->prepare($sqlRooms);
            $stmtRooms->execute([':mgrBId' => $mgrBId, ':mgrPfx' => $mgrPrefix . '%']);
            $roomRows = $stmtRooms->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $sqlRooms .= " ORDER BY r.room_number ASC";
            $roomRows = $this->db->query($sqlRooms)->fetchAll(PDO::FETCH_ASSOC);
        }

        // 6. LẤY DỮ LIỆU TAB 3: KHÁCH THUÊ & HỢP ĐỒNG CƯ TRÚ (ACTIVE)
        $sqlTenants = "
            SELECT 
                u.id AS tenant_id,
                u.full_name,
                u.phone,
                u.email,
                u.cccd,
                u.roommate_name,
                u.roommate_phone,
                c.id AS contract_id,
                c.contract_code,
                c.renewal_status,
                c.start_date,
                c.end_date,
                c.monthly_rent,
                c.deposit_amount,
                r.room_number,
                r.building_id,
                b.name AS building_name,
                DATEDIFF(c.end_date, CURDATE()) AS days_left
            FROM users u
            JOIN contracts c ON c.tenant_id = u.id AND c.status = 'active'
            JOIN rooms r ON r.id = c.room_id
            LEFT JOIN buildings b ON b.id = r.building_id
        ";
        if ($isManager) {
            $sqlTenants .= " WHERE (r.building_id = :mgrBId OR r.room_number LIKE :mgrPfx)";
            $sqlTenants .= " ORDER BY r.room_number ASC";
            $stmtTenants = $this->db->prepare($sqlTenants);
            $stmtTenants->execute([':mgrBId' => $mgrBId, ':mgrPfx' => $mgrPrefix . '%']);
            $tenantRows = $stmtTenants->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $sqlTenants .= " ORDER BY r.room_number ASC";
            $tenantRows = $this->db->query($sqlTenants)->fetchAll(PDO::FETCH_ASSOC);
        }

        // 7. XỬ LÝ XUẤT CSV NẾU CÓ THAM SỐ EXPORT
        if ($export === 'csv') {
            $this->exportMasterCsv($matrix, $systemSummary, $invoiceRows, $filterSummary, $roomRows, $tenantRows, $buildingId, $monthYear, $isManager, $managerBuilding);
            return;
        }

        $userFullName = $_SESSION['full_name'] ?? ($isManager ? ('Quản lý ' . $managerBuilding['name']) : 'Quản trị viên Hệ thống');
        $brandHome    = 'index.php?route=dashboard';
        $pageTitle    = $isManager ? ('Báo Cáo Doanh Thu, Công Nợ & Tổng Quan - ' . $managerBuilding['name']) : 'Báo Cáo Doanh Thu, Công Nợ & Tổng Quan Hệ Thống';

        require_once __DIR__ . '/../views/reports/csv.php';
    }

    /**
     * Xuất Báo Cáo Tổng Hợp Master CSV Chuẩn UTF-8 Có BOM Cho Excel
     */
    private function exportMasterCsv(
        array $matrix,
        array $systemSummary,
        array $invoiceRows,
        array $filterSummary,
        array $roomRows,
        array $tenantRows,
        string $buildingId,
        string $monthYear,
        bool $isManager = false,
        ?array $managerBuilding = null
    ): void {
        $timestamp = date('Ymd_His');
        if ($isManager && $managerBuilding) {
            $filename = "Bao_Cao_StayMate_" . $managerBuilding['prefix'] . "_" . $timestamp . ".csv";
        } else {
            $filename = "Bao_Cao_Doanh_Thu_StayMate_" . ($buildingId !== 'all' ? "Toa{$buildingId}_" : "ToanHeThong_") . $timestamp . ".csv";
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');

        // Ghi UTF-8 BOM để Excel tiếng Việt hiển thị hoàn hảo không lỗi font
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // ==========================================
        // MỤC 1: TIÊU ĐỀ BÁO CÁO & THÔNG TIN CHUNG
        // ==========================================
        $reportTitle = $isManager && $managerBuilding 
            ? ('HỆ THỐNG QUẢN LÝ NHÀ TRỌ STAYMATE - BÁO CÁO HOẠT ĐỘNG ' . mb_strtoupper($managerBuilding['name'], 'UTF-8'))
            : 'HỆ THỐNG QUẢN LÝ NHÀ TRỌ STAYMATE - BÁO CÁO DOANH THU & CÔNG NỢ TOÀN HỆ THỐNG';

        $scopeText = $isManager && $managerBuilding 
            ? ($managerBuilding['name'] . ' (Tòa phân công quản lý)')
            : ($buildingId === 'all' ? 'Toàn bộ 3 tòa nhà (Tòa A, B, C)' : ('Tòa ' . $buildingId));

        $userLabel = $_SESSION['full_name'] ?? ($isManager ? 'Quản Lý Tòa Nhà' : 'Quản Trị Viên (Admin)');

        fputcsv($out, [$reportTitle]);
        fputcsv($out, ['Thời gian xuất:', date('d/m/Y H:i:s'), 'Người xuất:', $userLabel]);
        fputcsv($out, [
            'Phạm vi hiển thị:', $scopeText,
            'Kỳ lọc hóa đơn:', $monthYear === 'all' ? 'Tất cả các kỳ' : ('Kỳ ' . $monthYear)
        ]);
        fputcsv($out, []);

        // ==========================================
        // MỤC 2: CHỈ SỐ TỔNG QUAN TÀI CHÍNH & VẬN HÀNH
        // ==========================================
        $overviewHeading = $isManager && $managerBuilding 
            ? ('=== 1. CHỈ SỐ TỔNG QUAN ' . mb_strtoupper($managerBuilding['name'], 'UTF-8') . ' ===')
            : '=== 1. CHỈ SỐ TỔNG QUAN HỆ THỐNG (TOÀN BỘ 3 TÒA NHÀ) ===';

        fputcsv($out, [$overviewHeading]);
        fputcsv($out, ['Chỉ Tiêu', 'Giá Trị', 'Đơn Vị Tính', 'Ghi Chú']);
        fputcsv($out, ['Tổng số phòng', $systemSummary['total_rooms'], 'Phòng', $scopeText]);
        fputcsv($out, ['Số phòng đang cho thuê', $systemSummary['rented_rooms'], 'Phòng', 'Có hợp đồng thuê hiệu lực']);
        fputcsv($out, ['Số phòng trống sẵn sàng', $systemSummary['available_rooms'], 'Phòng', 'Có thể dọn vào ở ngay']);
        fputcsv($out, ['Số phòng đang bảo trì', $systemSummary['maintenance_rooms'], 'Phòng', 'Đang sửa chữa định kỳ']);
        fputcsv($out, ['Tỷ lệ lấp đầy', $systemSummary['occupancy_rate'] . '%', '%', 'Hiệu suất khai thác phòng']);
        fputcsv($out, ['Tổng số khách cư trú', $systemSummary['tenant_count'], 'Người', 'Số khách thuê chính thức']);
        fputcsv($out, ['Hợp đồng đã tái ký / gia hạn', $systemSummary['renewed_count'], 'Hợp đồng', 'Khách ở lâu năm gia hạn HĐ']);
        fputcsv($out, ['Tỷ lệ gia hạn hợp đồng', $systemSummary['renewal_rate'] . '%', '%', 'Số HĐ gia hạn / Tổng khách thuê']);
        fputcsv($out, ['Tổng tiền đặt cọc đang giữ', number_format($systemSummary['deposit_held'], 0, ',', '.') . ' đ', 'VNĐ', 'Tiền bảo đảm hợp đồng']);
        fputcsv($out, ['Tổng tiền lập hóa đơn phát sinh', number_format($systemSummary['total_invoiced'], 0, ',', '.') . ' đ', 'VNĐ', 'Tổng ' . $systemSummary['total_invoices'] . ' hóa đơn']);
        fputcsv($out, ['Thực thu (Doanh thu đã thu)', number_format($systemSummary['paid_amount'], 0, ',', '.') . ' đ', 'VNĐ', 'Đã thanh toán (' . $systemSummary['paid_count'] . ' hóa đơn)']);
        fputcsv($out, ['Công nợ chưa thu (Còn nợ)', number_format($systemSummary['unpaid_amount'], 0, ',', '.') . ' đ', 'VNĐ', 'Còn nợ cần thu (' . $systemSummary['unpaid_count'] . ' hóa đơn)']);
        fputcsv($out, ['Tỷ lệ thu hồi công nợ', $systemSummary['collection_rate'] . '%', '%', 'Thực thu / Tổng hóa đơn phát sinh']);
        fputcsv($out, []);

        // ==========================================
        // MỤC 3: BẢNG CHỈ SỐ HOẠT ĐỘNG / MA TRẬN
        // ==========================================
        $matrixHeading = $isManager && $managerBuilding 
            ? ('=== 2. CHỈ SỐ HOẠT ĐỘNG & HIỆU SUẤT ' . mb_strtoupper($managerBuilding['name'], 'UTF-8') . ' ===')
            : '=== 2. MA TRẬN SO SÁNH HIỆU QUẢ 3 TÒA NHÀ ===';

        fputcsv($out, [$matrixHeading]);
        fputcsv($out, [
            'Tòa Nhà',
            'Tổng Phòng',
            'Đang Thuê',
            'Phòng Trống',
            'Bảo Trì',
            'Tỷ Lệ Lấp Đầy (%)',
            'Khách Ở',
            'HĐ Tái Ký (Gia hạn)',
            'Tỷ Lệ Gia Hạn (%)',
            'Tiền Cọc Giữ (VNĐ)',
            'Hóa Đơn Phát Sinh (VNĐ)',
            'Thực Thu (VNĐ)',
            'Công Nợ Còn Lại (VNĐ)',
            'Tỷ Lệ Thu Hồi (%)'
        ]);

        foreach ($matrix as $row) {
            fputcsv($out, [
                $row['name'],
                $row['total_rooms'],
                $row['rented_rooms'],
                $row['available_rooms'],
                $row['maintenance_rooms'],
                $row['occupancy_rate'] . '%',
                $row['tenant_count'],
                $row['renewed_count'],
                $row['renewal_rate'] . '%',
                number_format($row['deposit_held'], 0, ',', '.') . ' đ',
                number_format($row['total_invoiced'], 0, ',', '.') . ' đ',
                number_format($row['paid_amount'], 0, ',', '.') . ' đ',
                number_format($row['unpaid_amount'], 0, ',', '.') . ' đ',
                $row['collection_rate'] . '%'
            ]);
        }

        if (!$isManager) {
            fputcsv($out, [
                'TOÀN HỆ THỐNG (TỔNG HỢP)',
                $systemSummary['total_rooms'],
                $systemSummary['rented_rooms'],
                $systemSummary['available_rooms'],
                $systemSummary['maintenance_rooms'],
                $systemSummary['occupancy_rate'] . '%',
                $systemSummary['tenant_count'],
                $systemSummary['renewed_count'],
                $systemSummary['renewal_rate'] . '%',
                number_format($systemSummary['deposit_held'], 0, ',', '.') . ' đ',
                number_format($systemSummary['total_invoiced'], 0, ',', '.') . ' đ',
                number_format($systemSummary['paid_amount'], 0, ',', '.') . ' đ',
                number_format($systemSummary['unpaid_amount'], 0, ',', '.') . ' đ',
                $systemSummary['collection_rate'] . '%'
            ]);
        }
        fputcsv($out, []);

        // ==========================================
        // MỤC 4: CHI TIẾT DOANH THU & CÔNG NỢ HÓA ĐƠN
        // ==========================================
        fputcsv($out, ['=== 3. CHI TIẾT DOANH THU & CÔNG NỢ TỪNG HÓA ĐƠN ===']);
        fputcsv($out, [
            'STT',
            'Mã Hóa Đơn',
            'Tòa Nhà',
            'Số Phòng',
            'Khách Thuê',
            'Số Điện Thoại',
            'Kỳ Hóa Đơn',
            'Tiền Phòng',
            'Điện Nước & DV',
            'Tổng Hóa Đơn',
            'Đã Thu (Doanh Thu)',
            'Còn Nợ (Công Nợ)',
            'Trạng Thái',
            'Hạn Thanh Toán / Ngày Thu'
        ]);

        $stt = 1;
        foreach ($invoiceRows as $inv) {
            $isPaid = ($inv['status'] === 'paid');
            $totAmt = (float)$inv['total_amount'];
            $roomAmt = (float)($inv['computed_room'] ?? 0);
            $utilAmt = (float)($inv['computed_util'] ?? 0);
            $paidAmt = $isPaid ? $totAmt : 0;
            $unpaidAmt = $isPaid ? 0 : $totAmt;

            $dateNote = '';
            if ($isPaid && !empty($inv['paid_at'])) {
                $dateNote = 'Đã thu: ' . date('d/m/Y', strtotime($inv['paid_at']));
            } elseif (!$isPaid && !empty($inv['due_date'])) {
                $dateNote = 'Hạn: ' . date('d/m/Y', strtotime($inv['due_date']));
            }

            fputcsv($out, [
                $stt++,
                'HD-' . str_pad((string)$inv['id'], 4, '0', STR_PAD_LEFT),
                $inv['building_name'] ?? ('Tòa ' . substr($inv['room_number'], 0, 1)),
                $inv['room_number'],
                $inv['tenant_name'] ?? 'Chưa cập nhật',
                $inv['tenant_phone'] ?? '',
                $inv['month_year'],
                number_format($roomAmt, 0, ',', '.') . ' đ',
                number_format($utilAmt, 0, ',', '.') . ' đ',
                number_format($totAmt, 0, ',', '.') . ' đ',
                number_format($paidAmt, 0, ',', '.') . ' đ',
                number_format($unpaidAmt, 0, ',', '.') . ' đ',
                $isPaid ? 'Đã thu' : 'Chưa thu',
                $dateNote
            ]);
        }
        fputcsv($out, []);

        // ==========================================
        // MỤC 5: HIỆN TRẠNG PHÒNG & TIỀN CỌC GIỮ CHỖ
        // ==========================================
        $roomHeading = $isManager && $managerBuilding 
            ? ('=== 4. HIỆN TRẠNG PHÒNG & TIỀN CỌC ' . mb_strtoupper($managerBuilding['name'], 'UTF-8') . ' ===')
            : '=== 4. HIỆN TRẠNG PHÒNG & TIỀN CỌC TOÀN HỆ THỐNG ===';

        fputcsv($out, [$roomHeading]);
        fputcsv($out, [
            'STT',
            'Số Phòng',
            'Tòa Nhà',
            'Giá Thuê Niêm Yết',
            'Trạng Thái Phòng',
            'Khách Đang Thuê',
            'Số Điện Thoại',
            'Mã Hợp Đồng',
            'Loại HĐ',
            'Tiền Cọc Đang Giữ',
            'Trạng Thái Cọc',
            'Thời Hạn Thuê'
        ]);

        $rIdx = 1;
        foreach ($roomRows as $r) {
            $statusLabel = 'Trống';
            if ($r['status'] === 'rented') {
                $statusLabel = 'Đang thuê';
            } elseif ($r['status'] === 'maintenance') {
                $statusLabel = 'Bảo trì';
            }

            $cTypeLabel = '-';
            if (!empty($r['contract_type'])) {
                $cTypeLabel = ($r['contract_type'] === 'deposit_hold') ? 'Cọc giữ chỗ 5N' : 'Chính thức';
            }

            $dateRange = '-';
            if (!empty($r['start_date']) && !empty($r['end_date'])) {
                $dateRange = date('d/m/Y', strtotime($r['start_date'])) . ' - ' . date('d/m/Y', strtotime($r['end_date']));
            }

            fputcsv($out, [
                $rIdx++,
                $r['room_number'],
                $r['building_name'] ?? ('Tòa ' . substr($r['room_number'], 0, 1)),
                number_format((float)$r['price'], 0, ',', '.') . ' đ',
                $statusLabel,
                $r['tenant_name'] ?? '-',
                $r['tenant_phone'] ?? '-',
                $r['contract_code'] ?? '-',
                $cTypeLabel,
                !empty($r['deposit_amount']) ? number_format((float)$r['deposit_amount'], 0, ',', '.') . ' đ' : '0 đ',
                $r['deposit_status'] ?? '-',
                $dateRange
            ]);
        }
        fputcsv($out, []);

        // ==========================================
        // MỤC 6: DANH SÁCH KHÁCH THUÊ & HỢP ĐỒNG CƯ TRÚ
        // ==========================================
        $tenantHeading = $isManager && $managerBuilding 
            ? ('=== 5. DANH SÁCH KHÁCH THUÊ & HỢP ĐỒNG ' . mb_strtoupper($managerBuilding['name'], 'UTF-8') . ' ===')
            : '=== 5. DANH SÁCH KHÁCH THUÊ & HỢP ĐỒNG CƯ TRÚ ===';

        fputcsv($out, [$tenantHeading]);
        fputcsv($out, [
            'STT',
            'Khách Thuê',
            'Số Điện Thoại',
            'Số CCCD',
            'Email',
            'Số Phòng',
            'Tòa Nhà',
            'Mã Hợp Đồng',
            'Tình Trạng Tái Ký',
            'Thời Hạn HĐ',
            'Thời Gian Còn Lại (Ngày)',
            'Tiền Thuê / Tháng',
            'Tiền Cọc Giữ',
            'Người Ở Ghép'
        ]);

        $tIdx = 1;
        foreach ($tenantRows as $tn) {
            $isRenewed = ($tn['renewal_status'] ?? '') === 'renewed';
            $roommateInfo = !empty($tn['roommate_name']) 
                ? ($tn['roommate_name'] . (!empty($tn['roommate_phone']) ? " ({$tn['roommate_phone']})" : ''))
                : 'Ở 1 mình';

            fputcsv($out, [
                $tIdx++,
                $tn['full_name'],
                $tn['phone'],
                $tn['cccd'] ?? '-',
                $tn['email'] ?? '-',
                $tn['room_number'],
                $tn['building_name'] ?? ('Tòa ' . substr($tn['room_number'], 0, 1)),
                $tn['contract_code'],
                $isRenewed ? 'Đã gia hạn (Tái ký)' : 'Hợp đồng ban đầu',
                date('d/m/Y', strtotime($tn['start_date'])) . ' - ' . date('d/m/Y', strtotime($tn['end_date'])),
                (int)$tn['days_left'] . ' ngày',
                number_format((float)$tn['monthly_rent'], 0, ',', '.') . ' đ',
                number_format((float)$tn['deposit_amount'], 0, ',', '.') . ' đ',
                $roommateInfo
            ]);
        }

        fclose($out);
        exit;
    }
}