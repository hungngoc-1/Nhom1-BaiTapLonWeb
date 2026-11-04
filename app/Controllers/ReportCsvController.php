<?php
require_once __DIR__ . '/../Config/Database.php';

class ReportCsvController {
    private PDO $db;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = Database::getConnection();
    }

    /**
     * Phân quyền nghiêm ngặt: CHỈ CÓ QUẢN TRỊ VIÊN (ADMIN) MỚI ĐƯỢC XEM BÁO CÁO NÀY
     */
    private function checkAdminAuth(): void {
        $role = $_SESSION['role'] ?? '';
        if ($role !== 'admin') {
            $_SESSION['flash_error'] = 'Chỉ Quản trị viên (Admin) mới có quyền truy cập Báo cáo Doanh thu & Công nợ!';
            header('Location: index.php?route=overview');
            exit;
        }
    }

    /**
     * Trang xem báo cáo tổng thể, ma trận 3 tòa nhà, chi tiết 3 tab và xuất CSV
     */
    public function index(): void {
        $this->checkAdminAuth();

        $buildingId = trim($_GET['building_id'] ?? 'all');
        $monthYear  = trim($_GET['month_year'] ?? 'all');
        $status     = trim($_GET['status'] ?? 'all');
        $keyword    = trim($_GET['keyword'] ?? '');
        $export     = trim($_GET['export'] ?? '');

        // 1. Danh sách 3 tòa nhà
        $buildings = $this->db->query("SELECT id, name, address FROM buildings ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

        // 2. Danh sách kỳ thu (tháng-năm)
        $periods = $this->db->query("SELECT DISTINCT month_year FROM invoices ORDER BY month_year DESC")->fetchAll(PDO::FETCH_COLUMN);

        // 3. TÍNH TOÁN BẢNG MA TRẬN SO SÁNH 3 TÒA NHÀ & TOÀN HỆ THỐNG
        $matrix = [];
        $systemSummary = [
            'total_rooms'       => 0,
            'rented_rooms'      => 0,
            'available_rooms'   => 0,
            'maintenance_rooms' => 0,
            'occupancy_rate'    => 0,
            'tenant_count'      => 0,
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
                'deposit_held'      => $depositHeld,
                'total_invoices'    => $totalInv,
                'total_invoiced'    => $invoiced,
                'paid_amount'       => $paid,
                'unpaid_amount'     => $unpaid,
                'paid_count'        => $paidCnt,
                'unpaid_count'      => $unpaidCnt,
                'collection_rate'   => $collRate
            ];

            // Tích lũy toàn hệ thống
            $systemSummary['total_rooms']       += $totalR;
            $systemSummary['rented_rooms']      += $rentedR;
            $systemSummary['available_rooms']   += $availR;
            $systemSummary['maintenance_rooms'] += $maintR;
            $systemSummary['tenant_count']      += $tenantCount;
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

        // 4. LẤY DỮ LIỆU TAB 1: DANH SÁCH HÓA ĐƠN CHI TIẾT (KÈM BỘ LỌC)
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

        if ($buildingId !== 'all' && $buildingId !== '') {
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

        $sqlInvoices .= " ORDER BY r.room_number ASC, i.id DESC";
        $stmtInvoices = $this->db->prepare($sqlInvoices);
        $stmtInvoices->execute($invParams);
        $invoiceRows = $stmtInvoices->fetchAll(PDO::FETCH_ASSOC);

        // Tổng kết cho danh sách hóa đơn sau khi lọc
        $filterSummary = [
            'total_invoices' => count($invoiceRows),
            'total_amount'   => 0,
            'paid_amount'     => 0,
            'unpaid_amount'   => 0,
            'paid_count'      => 0,
            'unpaid_count'    => 0,
            'room_total'      => 0,
            'utility_total'   => 0
        ];

        foreach ($invoiceRows as &$ir) {
            // Đảm bảo hạn thanh toán hợp lệ (ngày 10 tháng kế tiếp của kỳ)
            if (empty($ir['due_date'])) {
                if (preg_match('/^(\d{2})-(\d{4})$/', $ir['month_year'], $m)) {
                    $ir['due_date'] = date('Y-m-d', strtotime("{$m[2]}-{$m[1]}-01 +1 month +9 days"));
                } else {
                    $ir['due_date'] = date('Y-m-d');
                }
            }

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

        // 5. LẤY DỮ LIỆU TAB 2: HIỆN TRẠNG 60 PHÒNG & TIỀN CỌC GIỮ CHỖ
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
            ORDER BY r.room_number ASC
        ";
        $roomRows = $this->db->query($sqlRooms)->fetchAll(PDO::FETCH_ASSOC);

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
                c.start_date,
                c.end_date,
                c.monthly_rent,
                c.deposit_amount,
                r.room_number,
                b.name AS building_name,
                DATEDIFF(c.end_date, CURDATE()) AS days_left
            FROM users u
            JOIN contracts c ON c.tenant_id = u.id AND c.status = 'active'
            JOIN rooms r ON r.id = c.room_id
            LEFT JOIN buildings b ON b.id = r.building_id
            ORDER BY r.room_number ASC
        ";
        $tenantRows = $this->db->query($sqlTenants)->fetchAll(PDO::FETCH_ASSOC);

        // 7. XỬ LÝ XUẤT CSV NẾU CÓ THAM SỐ EXPORT
        if ($export === 'csv') {
            $this->exportMasterCsv($matrix, $systemSummary, $invoiceRows, $filterSummary, $roomRows, $tenantRows, $buildingId, $monthYear);
            return;
        }

        $userRole     = $_SESSION['role'] ?? 'admin';
        $userFullName = $_SESSION['full_name'] ?? 'Quản trị viên Hệ thống';
        $brandHome    = 'index.php?route=dashboard';
        $pageTitle    = 'Báo Cáo Doanh Thu & Công Nợ';

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
        string $monthYear
    ): void {
        $timestamp = date('Ymd_His');
        $filename = "Bao_Cao_Doanh_Thu_StayMate_" . ($buildingId !== 'all' ? "Toa{$buildingId}_" : "ToanHeThong_") . $timestamp . ".csv";

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
        fputcsv($out, ['HỆ THỐNG QUẢN LÝ NHÀ TRỌ STAYMATE - BÁO CÁO DOANH THU & CÔNG NỢ TOÀN HỆ THỐNG']);
        fputcsv($out, ['Thời gian xuất:', date('d/m/Y H:i:s'), 'Người xuất:', $_SESSION['full_name'] ?? 'Quản Trị Viên (Admin)']);
        fputcsv($out, [
            'Phạm vi hiển thị:', $buildingId === 'all' ? 'Toàn bộ 3 tòa nhà (Tòa A, B, C)' : ('Tòa ' . $buildingId),
            'Kỳ lọc hóa đơn:', $monthYear === 'all' ? 'Tất cả các kỳ' : ('Kỳ ' . $monthYear)
        ]);
        fputcsv($out, []);

        // ==========================================
        // MỤC 2: CHỈ SỐ TỔNG QUAN TÀI CHÍNH & VẬN HÀNH
        // ==========================================
        fputcsv($out, ['=== 1. CHỈ SỐ TỔNG QUAN HỆ THỐNG (TOÀN BỘ 3 TÒA NHÀ) ===']);
        fputcsv($out, ['Chỉ Tiêu', 'Giá Trị', 'Đơn Vị Tính', 'Ghi Chú']);
        fputcsv($out, ['Tổng số phòng', $systemSummary['total_rooms'], 'Phòng', 'Cả 3 tòa nhà (Sunrise, Emerald, Horizon)']);
        fputcsv($out, ['Số phòng đang cho thuê', $systemSummary['rented_rooms'], 'Phòng', 'Có hợp đồng thuê hiệu lực']);
        fputcsv($out, ['Số phòng trống sẵn sàng', $systemSummary['available_rooms'], 'Phòng', 'Có thể dọn vào ở ngay']);
        fputcsv($out, ['Số phòng đang bảo trì', $systemSummary['maintenance_rooms'], 'Phòng', 'Đang sửa chữa định kỳ']);
        fputcsv($out, ['Tỷ lệ lấp đầy hệ thống', $systemSummary['occupancy_rate'] . '%', '%', 'Hiệu suất khai thác phòng']);
        fputcsv($out, ['Tổng số khách cư trú', $systemSummary['tenant_count'], 'Người', 'Số khách thuê chính thức']);
        fputcsv($out, ['Tổng tiền đặt cọc đang giữ', number_format($systemSummary['deposit_held'], 0, ',', '.') . ' đ', 'VNĐ', 'Tiền bảo đảm hợp đồng']);
        fputcsv($out, ['Tổng tiền lập hóa đơn phát sinh', number_format($systemSummary['total_invoiced'], 0, ',', '.') . ' đ', 'VNĐ', 'Tổng ' . $systemSummary['total_invoices'] . ' hóa đơn']);
        fputcsv($out, ['Thực thu (Doanh thu đã thu)', number_format($systemSummary['paid_amount'], 0, ',', '.') . ' đ', 'VNĐ', 'Đã thanh toán (' . $systemSummary['paid_count'] . ' hóa đơn)']);
        fputcsv($out, ['Công nợ chưa thu (Còn nợ)', number_format($systemSummary['unpaid_amount'], 0, ',', '.') . ' đ', 'VNĐ', 'Còn nợ cần thu (' . $systemSummary['unpaid_count'] . ' hóa đơn)']);
        fputcsv($out, ['Tỷ lệ thu hồi công nợ', $systemSummary['collection_rate'] . '%', '%', 'Thực thu / Tổng hóa đơn phát sinh']);
        fputcsv($out, []);

        // ==========================================
        // MỤC 3: BẢNG MA TRẬN SO SÁNH 3 TÒA NHÀ
        // ==========================================
        fputcsv($out, ['=== 2. MA TRẬN SO SÁNH HIỆU QUẢ 3 TÒA NHÀ ===']);
        fputcsv($out, [
            'Tòa Nhà',
            'Tổng Phòng',
            'Đang Thuê',
            'Phòng Trống',
            'Bảo Trì',
            'Tỷ Lệ Lấp Đầy (%)',
            'Khách Ở',
            'Tiền Cọc Đang Giữ (VNĐ)',
            'Tổng Hóa Đơn (VNĐ)',
            'Thực Thu / Doanh Thu (VNĐ)',
            'Công Nợ Chưa Thu (VNĐ)',
            'Tỷ Lệ Thu Hồi (%)'
        ]);

        foreach ($matrix as $m) {
            fputcsv($out, [
                $m['name'],
                $m['total_rooms'],
                $m['rented_rooms'],
                $m['available_rooms'],
                $m['maintenance_rooms'],
                $m['occupancy_rate'] . '%',
                $m['tenant_count'],
                number_format($m['deposit_held'], 0, '', ''),
                number_format($m['total_invoiced'], 0, '', ''),
                number_format($m['paid_amount'], 0, '', ''),
                number_format($m['unpaid_amount'], 0, '', ''),
                $m['collection_rate'] . '%'
            ]);
        }

        // Dòng tổng cộng ma trận
        fputcsv($out, [
            'TOÀN HỆ THỐNG (TỔNG CỘNG)',
            $systemSummary['total_rooms'],
            $systemSummary['rented_rooms'],
            $systemSummary['available_rooms'],
            $systemSummary['maintenance_rooms'],
            $systemSummary['occupancy_rate'] . '%',
            $systemSummary['tenant_count'],
            number_format($systemSummary['deposit_held'], 0, '', ''),
            number_format($systemSummary['total_invoiced'], 0, '', ''),
            number_format($systemSummary['paid_amount'], 0, '', ''),
            number_format($systemSummary['unpaid_amount'], 0, '', ''),
            $systemSummary['collection_rate'] . '%'
        ]);
        fputcsv($out, []);

        // ==========================================
        // MỤC 4: BẢNG KÊ CHI TIẾT HÓA ĐƠN & CÔNG NỢ
        // ==========================================
        fputcsv($out, ['=== 3. BẢNG KÊ CHI TIẾT HÓA ĐƠN DOANH THU & CÔNG NỢ ===']);
        fputcsv($out, [
            'STT',
            'Mã Hóa Đơn',
            'Tòa Nhà',
            'Số Phòng',
            'Khách Thuê',
            'Số Điện Thoại',
            'Kỳ Thu',
            'Tiền Phòng (VNĐ)',
            'Điện Nước & DV (VNĐ)',
            'Tổng Tiền HĐ (VNĐ)',
            'Đã Thu / Thực Thu (VNĐ)',
            'Còn Nợ / Công Nợ (VNĐ)',
            'Trạng Thái',
            'Phương Thức',
            'Hạn Thanh Toán / Ngày Thu'
        ]);

        $idx = 1;
        foreach ($invoiceRows as $r) {
            $isPaid = $r['status'] === 'paid';
            $paidAmt = $isPaid ? (float)$r['total_amount'] : 0;
            $debtAmt = !$isPaid ? (float)$r['total_amount'] : 0;

            $payTime = '';
            if ($isPaid && !empty($r['paid_at'])) {
                $payTime = 'Đã thu: ' . date('d/m/Y', strtotime($r['paid_at']));
            } elseif (!$isPaid) {
                $payTime = 'Hạn TT: ' . date('d/m/Y', strtotime($r['due_date']));
            } else {
                $payTime = 'Đã thu: ' . date('d/m/Y', strtotime($r['created_at']));
            }

            fputcsv($out, [
                $idx++,
                'HD-' . str_pad((string)$r['id'], 4, '0', STR_PAD_LEFT),
                $r['building_name'] ?? ('Tòa ' . substr($r['room_number'], 0, 1)),
                $r['room_number'],
                $r['tenant_name'] ?? 'Khách vãng lai',
                $r['tenant_phone'] ?? '',
                $r['month_year'],
                number_format((float)$r['computed_room'], 0, '', ''),
                number_format((float)$r['computed_util'], 0, '', ''),
                number_format((float)$r['total_amount'], 0, '', ''),
                number_format($paidAmt, 0, '', ''),
                number_format($debtAmt, 0, '', ''),
                $isPaid ? 'Đã thu (Doanh thu)' : 'Chưa thu (Công nợ)',
                $r['payment_method'] ?? ($isPaid ? 'Chuyển khoản' : ''),
                $payTime
            ]);
        }
        fputcsv($out, []);

        // ==========================================
        // MỤC 5: BẢNG KÊ HIỆN TRẠNG 60 PHÒNG & TIỀN CỌC
        // ==========================================
        fputcsv($out, ['=== 4. BẢNG KÊ HIỆN TRẠNG 60 PHÒNG & TIỀN CỌC GIỮ CHỖ ===']);
        fputcsv($out, [
            'STT',
            'Số Phòng',
            'Tòa Nhà',
            'Giá Thuê Niêm Yết (VNĐ)',
            'Trạng Thái Phòng',
            'Khách Đang Ở / Giữ Chỗ',
            'SĐT Khách',
            'Mã Hợp Đồng',
            'Tiền Cọc Đang Giữ (VNĐ)',
            'Trạng Thái Cọc'
        ]);

        $rIdx = 1;
        foreach ($roomRows as $rm) {
            $stText = 'Phòng trống';
            if ($rm['status'] === 'rented') {
                $stText = 'Đang cho thuê';
            } elseif ($rm['status'] === 'maintenance') {
                $stText = 'Đang bảo trì/sửa chữa';
            }

            fputcsv($out, [
                $rIdx++,
                $rm['room_number'],
                $rm['building_name'] ?? ('Tòa ' . substr($rm['room_number'], 0, 1)),
                number_format((float)$rm['price'], 0, '', ''),
                $stText,
                $rm['tenant_name'] ?? '-',
                $rm['tenant_phone'] ?? '-',
                $rm['contract_code'] ?? '-',
                number_format((float)($rm['deposit_amount'] ?? 0), 0, '', ''),
                !empty($rm['deposit_status']) ? ($rm['deposit_status'] === 'held' ? 'Đang giữ' : $rm['deposit_status']) : '-'
            ]);
        }
        fputcsv($out, []);

        // ==========================================
        // MỤC 6: BẢNG KÊ KHÁCH THUÊ & HỢP ĐỒNG CƯ TRÚ
        // ==========================================
        fputcsv($out, ['=== 5. DANH SÁCH KHÁCH THUÊ & HỢP ĐỒNG CƯ TRÚ ===']);
        fputcsv($out, [
            'STT',
            'Khách Thuê',
            'Số Điện Thoại',
            'Số CCCD',
            'Email',
            'Số Phòng',
            'Tòa Nhà',
            'Mã Hợp Đồng',
            'Ngày Bắt Đầu',
            'Ngày Hết Hạn',
            'Số Ngày Còn Lại',
            'Giá Thuê (VNĐ)',
            'Tiền Cọc (VNĐ)',
            'Người Ở Ghép'
        ]);

        $tIdx = 1;
        foreach ($tenantRows as $tn) {
            fputcsv($out, [
                $tIdx++,
                $tn['full_name'],
                $tn['phone'],
                $tn['cccd'] ?? '-',
                $tn['email'] ?? '-',
                $tn['room_number'],
                $tn['building_name'] ?? '',
                $tn['contract_code'],
                date('d/m/Y', strtotime($tn['start_date'])),
                date('d/m/Y', strtotime($tn['end_date'])),
                $tn['days_left'],
                number_format((float)$tn['monthly_rent'], 0, '', ''),
                number_format((float)$tn['deposit_amount'], 0, '', ''),
                $tn['roommate_name'] ?? 'Ở 1 mình'
            ]);
        }

        fclose($out);
        exit;
    }
}
