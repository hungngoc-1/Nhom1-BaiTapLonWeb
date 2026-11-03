<?php
require_once __DIR__ . '/../Config/Database.php';

class ReportModel {
    private PDO $conn;

    public function __construct() {
        // Đặt múi giờ máy tính Việt Nam (GMT+7) để đảm bảo giờ hiển thị chính xác theo giờ hệ thống
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $this->conn = Database::getConnection();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->initDefaultBuildings();
        $this->initDefaultRoomTypes();
    }

    /**
     * Khởi tạo cấu hình danh sách Tòa nhà gắn kết trực tiếp với dữ liệu SQL trong bảng `rooms`
     * - Tòa A: Tầng 1 (P101, P102...)
     * - Tòa B: Tầng 2 (P201, P202...)
     * - Tòa C: Tầng 3 (P301, P302...)
     */
    
    /**
     * Khởi tạo danh mục các Loại phòng / Hạng phòng tiêu biểu trong hệ thống
     */
    public function initDefaultRoomTypes(): void {
        if (empty($_SESSION['room_types'])) {
            $_SESSION['room_types'] = [
                'RT01' => [
                    'id'          => 'RT01',
                    'name'        => 'Phòng Đơn Tiêu Chuẩn',
                    'price'       => 2800000,
                    'area'        => '18 - 22m²',
                    'amenities'   => 'Điều hòa, Nóng lạnh, Giường đệm, Tủ quần áo',
                    'badge_color' => '#0284c7'
                ],
                'RT02' => [
                    'id'          => 'RT02',
                    'name'        => 'Phòng Studio Full Nội Thất',
                    'price'       => 3800000,
                    'area'        => '25 - 28m²',
                    'amenities'   => 'Bếp riêng, Tủ lạnh, Máy giặt, Sofa, Ban công',
                    'badge_color' => '#308f86'
                ],
                'RT03' => [
                    'id'          => 'RT03',
                    'name'        => 'Phòng Duplex Gác Lửng',
                    'price'       => 4500000,
                    'area'        => '32 - 36m²',
                    'amenities'   => 'Gác lửng đúc cao cấp, Bếp từ, Tủ lạnh 2 cánh, Kính lớn',
                    'badge_color' => '#7c3aed'
                ],
                'RT04' => [
                    'id'          => 'RT04',
                    'name'        => 'Căn Hộ 1 Phòng Ngủ (1PN)',
                    'price'       => 5500000,
                    'area'        => '40 - 45m²',
                    'amenities'   => '1PN + 1 Khách, Khóa vân tay, Đầy đủ thiết bị cao cấp',
                    'badge_color' => '#d97706'
                ],
                'RT05' => [
                    'id'          => 'RT05',
                    'name'        => 'Phòng Ban Công Thoáng Mát',
                    'price'       => 3300000,
                    'area'        => '22 - 25m²',
                    'amenities'   => 'Ban công đón nắng, Điều hòa Inverter, Nóng lạnh, Bàn làm việc',
                    'badge_color' => '#059669'
                ]
            ];
        }
    }

    public function getRoomTypes(): array {
        $this->initDefaultRoomTypes();
        return $_SESSION['room_types'] ?? [];
    }

    public function addRoomType(string $name, float $price, string $area, string $amenities): string {
        $this->initDefaultRoomTypes();
        $id = 'RT' . str_pad((string)(count($_SESSION['room_types']) + 1), 2, '0', STR_PAD_LEFT);
        if (isset($_SESSION['room_types'][$id])) {
            $id = 'RT' . rand(10, 99);
        }
        $_SESSION['room_types'][$id] = [
            'id'          => $id,
            'name'        => $name,
            'price'       => $price,
            'area'        => $area !== '' ? $area : '25m²',
            'amenities'   => $amenities !== '' ? $amenities : 'Đầy đủ tiện nghi',
            'badge_color' => '#308f86'
        ];
        return $id;
    }
    private function initDefaultBuildings(): void {
        if (empty($_SESSION['buildings_config'])) {
            $_SESSION['buildings_config'] = [
                'A' => [
                    'id'          => 'A',
                    'code'        => 'BLD-A',
                    'name'        => 'Tòa A - StayMate Sunrise',
                    'address'     => 'Số 128 Cầu Giấy, Quận Cầu Giấy, TP. Hà Nội',
                    'manager'     => 'Quản lý Tòa A (Nguyễn Văn Quản)',
                    'manager_acc' => 'quanly_a',
                    'manager_phone' => '0912 345 678',
                    'manager_email' => 'quanly_a@nhatro.com',
                    'room_prefix' => 'P1',
                    'room_types'  => ['Phòng Đơn Tiêu Chuẩn', 'Phòng Ban Công Thoáng Mát'],
                    'theme'       => 'teal'
                ],
                'B' => [
                    'id'          => 'B',
                    'code'        => 'BLD-B',
                    'name'        => 'Tòa B - StayMate Emerald',
                    'address'     => 'Số 45 Nguyễn Chí Thanh, Quận Đống Đa, TP. Hà Nội',
                    'manager'     => 'Quản lý Tòa B (Trần Thị Bích)',
                    'manager_acc' => 'quanly_b',
                    'manager_phone' => '0987 654 321',
                    'manager_email' => 'quanly_b@nhatro.com',
                    'room_prefix' => 'P2',
                    'room_types'  => ['Phòng Studio Full Nội Thất', 'Phòng Ban Công Thoáng Mát'],
                    'theme'       => 'blue'
                ],
                'C' => [
                    'id'          => 'C',
                    'code'        => 'BLD-C',
                    'name'        => 'Tòa C - StayMate Horizon',
                    'address'     => 'Số 88 Nguyễn Trãi, Quận Thanh Xuân, TP. Hà Nội',
                    'manager'     => 'Quản lý Tòa C (Lê Hoàng Nam)',
                    'manager_acc' => 'quanly_c',
                    'manager_phone' => '0934 567 890',
                    'manager_email' => 'quanly_c@nhatro.com',
                    'room_prefix' => 'P3',
                    'room_types'  => ['Phòng Studio Full Nội Thất', 'Phòng Duplex Gác Lửng', 'Căn Hộ 1 Phòng Ngủ (1PN)'],
                    'theme'       => 'yellow'
                ]
            ];
        }
    }

    
    /**
     * Lấy danh sách ID các tòa nhà mà tài khoản hiện tại được phép quản lý / truy cập
     * - Admin: Được xem và quản lý tất cả các tòa (all)
     * - Quản lý (landlord): Chỉ xem và quản lý đúng tòa nhà được phân công
     * - Khách thuê (tenant): Xem tòa nhà có phòng đang thuê
     */
    public function getAllowedBuildingIds(): array {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $role     = $_SESSION['role'] ?? 'admin';
        $username = $_SESSION['username'] ?? '';
        $allBlds  = $this->getBuildingsConfig();

        if ($role === 'admin') {
            return array_keys($allBlds);
        }

        if ($role === 'landlord') {
            $allowed = [];
            foreach ($allBlds as $bId => $bInfo) {
                $acc = $bInfo['manager_acc'] ?? '';
                if (
                    strcasecmp($acc, $username) === 0 ||
                    ($bId === 'A' && in_array($username, ['quanly_a', 'nhanvien', 'quanly'])) ||
                    ($bId === 'B' && in_array($username, ['quanly_b'])) ||
                    ($bId === 'C' && in_array($username, ['quanly_c']))
                ) {
                    $allowed[] = (string)$bId;
                }
            }
            return !empty($allowed) ? $allowed : ['A'];
        }

        // Khách thuê (tenant): tìm tòa dựa vào phòng đang có hợp đồng
        if ($role === 'tenant') {
            $userId = (int)($_SESSION['user_id'] ?? 0);
            $stmt = $this->conn->prepare("SELECT r.room_number FROM contracts c JOIN rooms r ON c.room_id = r.id WHERE c.tenant_id = :uid LIMIT 1");
            $stmt->execute([':uid' => $userId]);
            $rn = (string)$stmt->fetchColumn();
            if (str_starts_with($rn, 'P1')) return ['A'];
            if (str_starts_with($rn, 'P2')) return ['B'];
            if (str_starts_with($rn, 'P3')) return ['C'];
            return ['A'];
        }

        return array_keys($allBlds);
    }

    public function getBuildingsConfig(): array {
        return $_SESSION['buildings_config'] ?? [];
    }

    public function addBuilding(string $code, string $name, string $address, string $manager, string $managerAcc, string $roomPrefix, string $theme = 'teal', array $roomTypes = [], string $managerPhone = '', string $managerEmail = ''): string {
        $id = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', str_replace('BLD-', '', $code)));
        if ($id === '' || isset($_SESSION['buildings_config'][$id])) {
            $id = 'B' . count($_SESSION['buildings_config']) . rand(10, 99);
        }
        if ($roomPrefix === '') {
            $roomPrefix = 'P' . (count($_SESSION['buildings_config']) + 1);
        }
        $_SESSION['buildings_config'][$id] = [
            'id'          => $id,
            'code'        => $code !== '' ? strtoupper($code) : ('BLD-' . $id),
            'name'        => $name,
            'address'     => $address !== '' ? $address : 'TP. Hà Nội',
            'manager'       => $manager !== '' ? $manager : 'Quản Trị Viên',
            'manager_acc'   => $managerAcc !== '' ? $managerAcc : 'admin01',
            'manager_phone' => $managerPhone !== '' ? $managerPhone : '',
            'manager_email' => $managerEmail !== '' ? $managerEmail : ($managerAcc !== '' ? $managerAcc . '@nhatro.com' : ''),
            'room_prefix' => strtoupper($roomPrefix),
            'room_types'  => !empty($roomTypes) ? $roomTypes : ['Phòng Đơn Tiêu Chuẩn'],
            'theme'       => in_array($theme, ['teal', 'blue', 'yellow'], true) ? $theme : 'teal'
        ];
        return $id;
    }

    public function updateBuilding(string $id, string $code, string $name, string $address, string $manager, string $managerAcc, string $roomPrefix = '', array $roomTypes = [], string $managerPhone = '', string $managerEmail = ''): bool {
        if (!isset($_SESSION['buildings_config'][$id])) {
            return false;
        }
        $_SESSION['buildings_config'][$id]['code']        = $code !== '' ? strtoupper($code) : $_SESSION['buildings_config'][$id]['code'];
        $_SESSION['buildings_config'][$id]['name']        = $name;
        $_SESSION['buildings_config'][$id]['address']     = $address;
        $_SESSION['buildings_config'][$id]['manager']       = $manager;
        $_SESSION['buildings_config'][$id]['manager_acc']   = $managerAcc;
        $_SESSION['buildings_config'][$id]['manager_phone'] = $managerPhone;
        $_SESSION['buildings_config'][$id]['manager_email'] = $managerEmail;
        if ($roomPrefix !== '') {
            $_SESSION['buildings_config'][$id]['room_prefix'] = strtoupper($roomPrefix);
        }
        if (!empty($roomTypes)) {
            $_SESSION['buildings_config'][$id]['room_types'] = $roomTypes;
        }
        return true;
    }

    public function deleteBuilding(string $id): bool {
        if (isset($_SESSION['buildings_config'][$id]) && count($_SESSION['buildings_config']) > 1) {
            unset($_SESSION['buildings_config'][$id]);
            return true;
        }
        return false;
    }

    public function getRoomPrefixByBuilding(string $buildingId): string {
        if ($buildingId === '' || strtolower($buildingId) === 'all') {
            return '';
        }
        $configs = $this->getBuildingsConfig();
        if (isset($configs[$buildingId])) {
            return $configs[$buildingId]['room_prefix'];
        }
        return '';
    }

    // 1. Lấy danh sách Tòa nhà kèm số liệu thực tế từ CSDL (rooms, contracts, invoices)
    public function getBuildingsWithStats(): array {
        $configs = $this->getBuildingsConfig();
        $result = [];

        foreach ($configs as $id => $bld) {
            $stats = $this->getSummaryStats($id);
            $rooms = $this->getRoomsList($id);
            $invoices = $this->getDebtList('', '', $id);

            if (empty($bld['room_types'])) {
                if ($id === 'A') $bld['room_types'] = ['Phòng Đơn Tiêu Chuẩn', 'Phòng Ban Công Thoáng Mát'];
                elseif ($id === 'B') $bld['room_types'] = ['Phòng Studio Full Nội Thất', 'Phòng Ban Công Thoáng Mát'];
                elseif ($id === 'C') $bld['room_types'] = ['Phòng Studio Full Nội Thất', 'Phòng Duplex Gác Lửng', 'Căn Hộ 1 Phòng Ngủ (1PN)'];
                else $bld['room_types'] = ['Phòng Tiêu Chuẩn'];
            }
            $bld['total_rooms']      = $stats['total_rooms'];
            $bld['rented_rooms']     = $stats['rented_rooms'];
            $bld['empty_rooms']      = $stats['empty_rooms'];
            $bld['occupancy_rate']   = $stats['occupancy_rate'];
            $bld['total_revenue']    = $stats['total_revenue'];
            $bld['total_debt']       = $stats['total_debt'];
            $bld['unpaid_invoices']  = $stats['unpaid_invoices'];
            $bld['paid_invoices']    = $stats['paid_invoices'];
            $bld['active_contracts'] = $stats['active_contracts'];
            $bld['rooms_list']       = $rooms;
            $bld['invoices_list']    = $invoices;
            $result[$id] = $bld;
        }

        return $result;
    }

    // 2. Thống kê tổng quan số lượng phòng, doanh thu, công nợ & hợp đồng trực tiếp từ SQL mới
    public function getSummaryStats(string $buildingId = 'all'): array {
        $prefix = $this->getRoomPrefixByBuilding($buildingId);
        $whereRoom = $prefix !== '' ? " WHERE r.room_number LIKE :prefix " : "";
        $params = $prefix !== '' ? [':prefix' => $prefix . '%'] : [];

        // Đếm phòng từ bảng `rooms`
        $stmt = $this->conn->prepare("
            SELECT 
                COUNT(*) AS total_rooms,
                SUM(CASE WHEN r.status = 'rented' THEN 1 ELSE 0 END) AS rented_rooms,
                SUM(CASE WHEN r.status = 'available' THEN 1 ELSE 0 END) AS empty_rooms
            FROM rooms r
            $whereRoom
        ");
        $stmt->execute($params);
        $roomRow = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $totalRooms  = (int)($roomRow['total_rooms'] ?? 0);
        $rentedRooms = (int)($roomRow['rented_rooms'] ?? 0);
        $emptyRooms  = max(0, $totalRooms - $rentedRooms);
        $occupancyRate = $totalRooms > 0 ? (int)round(($rentedRooms / $totalRooms) * 100) : 0;

        // Thống kê hóa đơn thực thu & công nợ từ bảng `invoices` JOIN `rooms`
        $stmtInv = $this->conn->prepare("
            SELECT 
                SUM(CASE WHEN i.status = 'paid' THEN i.total_amount ELSE 0 END) AS total_revenue,
                SUM(CASE WHEN i.status = 'paid' THEN 1 ELSE 0 END) AS paid_invoices,
                SUM(CASE WHEN i.status = 'unpaid' THEN i.total_amount ELSE 0 END) AS total_debt,
                SUM(CASE WHEN i.status = 'unpaid' THEN 1 ELSE 0 END) AS unpaid_invoices,
                COUNT(*) AS total_invoices
            FROM invoices i
            JOIN rooms r ON i.room_id = r.id
            $whereRoom
        ");
        $stmtInv->execute($params);
        $invRow = $stmtInv->fetch(PDO::FETCH_ASSOC) ?: [];

        // Thống kê hợp đồng từ bảng `contracts` JOIN `rooms`
        $stmtContract = $this->conn->prepare("
            SELECT 
                SUM(CASE WHEN c.status = 'active' THEN 1 ELSE 0 END) AS active_contracts,
                COUNT(*) AS total_contracts
            FROM contracts c
            JOIN rooms r ON c.room_id = r.id
            $whereRoom
        ");
        $stmtContract->execute($params);
        $cRow = $stmtContract->fetch(PDO::FETCH_ASSOC) ?: [];

        // Tổng số khách thuê trong bảng `users`
        $totalTenants = (int)$this->conn->query("SELECT COUNT(*) FROM users WHERE role = 'tenant'")->fetchColumn();

        return [
            'total_rooms'      => $totalRooms,
            'empty_rooms'      => $emptyRooms,
            'rented_rooms'     => $rentedRooms,
            'occupancy_rate'   => $occupancyRate,
            'total_revenue'    => (float)($invRow['total_revenue'] ?? 0),
            'paid_invoices'    => (int)($invRow['paid_invoices'] ?? 0),
            'unpaid_invoices'  => (int)($invRow['unpaid_invoices'] ?? 0),
            'total_invoices'   => (int)($invRow['total_invoices'] ?? 0),
            'total_debt'       => (float)($invRow['total_debt'] ?? 0),
            'active_contracts' => (int)($cRow['active_contracts'] ?? 0),
            'total_contracts'  => (int)($cRow['total_contracts'] ?? 0),
            'total_tenants'    => $totalTenants
        ];
    }

    // 3. Dữ liệu biểu đồ doanh thu & công nợ theo kỳ từ bảng `invoices`
    public function getMonthlyRevenue(string $buildingId = 'all'): array {
        $prefix = $this->getRoomPrefixByBuilding($buildingId);
        $whereRoom = $prefix !== '' ? " WHERE r.room_number LIKE :prefix " : "";
        $params = $prefix !== '' ? [':prefix' => $prefix . '%'] : [];

        $sql = "
            SELECT 
                i.month_year,
                SUM(CASE WHEN i.status = 'paid' THEN i.total_amount ELSE 0 END) AS revenue,
                SUM(CASE WHEN i.status = 'unpaid' THEN i.total_amount ELSE 0 END) AS debt,
                COUNT(*) AS invoice_count
            FROM invoices i
            JOIN rooms r ON i.room_id = r.id
            $whereRoom
            GROUP BY i.month_year
            ORDER BY RIGHT(i.month_year, 4) ASC, LEFT(i.month_year, 2) ASC
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $labels = [];
        $revenues = [];
        $debts = [];
        foreach ($rows as $r) {
            $labels[]   = 'Kỳ ' . $r['month_year'];
            $revenues[] = (float)$r['revenue'];
            $debts[]    = (float)$r['debt'];
        }

        return [
            'labels'   => $labels,
            'revenues' => $revenues,
            'debts'    => $debts,
            'rows'     => $rows
        ];
    }

    // 4. Lấy bảng giá dịch vụ từ bảng `services`
    public function getServicesMap(): array {
        $rows = $this->conn->query("SELECT * FROM services ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
        $elecPrice = 3500;
        $waterPrice = 20000;
        $otherTotal = 0;
        foreach ($rows as $srv) {
            $nameLower = mb_strtolower($srv['service_name'], 'UTF-8');
            if (strpos($nameLower, 'điện') !== false || strpos($nameLower, 'dien') !== false) {
                $elecPrice = (float)$srv['unit_price'];
            } elseif (strpos($nameLower, 'nước') !== false || strpos($nameLower, 'nuoc') !== false) {
                $waterPrice = (float)$srv['unit_price'];
            } else {
                $otherTotal += (float)$srv['unit_price'];
            }
        }
        return [
            'list'        => $rows,
            'elec_price'  => $elecPrice,
            'water_price' => $waterPrice,
            'other_total' => $otherTotal
        ];
    }

    // 5. Danh sách hóa đơn & công nợ trực tiếp từ SQL (kết hợp `invoices`, `rooms`, `contracts`, `users`, `utilities_readings`)
    public function getDebtList(string $keyword = '', string $status = '', string $buildingId = 'all', string $monthYear = ''): array {
        $srvMap = $this->getServicesMap();
        $elecPrice  = $srvMap['elec_price'];
        $waterPrice = $srvMap['water_price'];
        $otherFee   = $srvMap['other_total'];

        $prefix = $this->getRoomPrefixByBuilding($buildingId);

        $sql = "
            SELECT 
                i.id,
                i.room_id,
                i.contract_id,
                i.month_year,
                i.total_amount,
                i.status,
                i.created_at,
                r.room_number,
                r.price AS room_price,
                r.description AS room_description,
                u.full_name AS tenant_name,
                u.phone AS tenant_phone,
                u.email AS tenant_email,
                ur.electric_old,
                ur.electric_new,
                ur.water_old,
                ur.water_new
            FROM invoices i
            JOIN rooms r ON i.room_id = r.id
            LEFT JOIN contracts c ON i.contract_id = c.id
            LEFT JOIN users u ON c.tenant_id = u.id
            LEFT JOIN utilities_readings ur ON ur.room_id = i.room_id AND ur.month_year = i.month_year
            WHERE 1=1
        ";
        $params = [];

        if ($prefix !== '') {
            $sql .= " AND r.room_number LIKE :prefix";
            $params[':prefix'] = $prefix . '%';
        }
        if ($status !== '' && in_array($status, ['paid', 'unpaid'], true)) {
            $sql .= " AND i.status = :status";
            $params[':status'] = $status;
        }
        if ($monthYear !== '') {
            $sql .= " AND i.month_year = :month_year";
            $params[':month_year'] = $monthYear;
        }
        if ($keyword !== '') {
            $sql .= " AND (r.room_number LIKE :kw1 OR u.full_name LIKE :kw2 OR i.month_year LIKE :kw3 OR u.phone LIKE :kw4)";
            $params[':kw1'] = $params[':kw2'] = $params[':kw3'] = $params[':kw4'] = '%' . $keyword . '%';
        }

        $sql .= " ORDER BY (i.status = 'unpaid') DESC, RIGHT(i.month_year, 4) DESC, LEFT(i.month_year, 2) DESC, r.room_number ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $elecUsed  = isset($row['electric_new']) ? max(0, (int)$row['electric_new'] - (int)$row['electric_old']) : 0;
            $waterUsed = isset($row['water_new']) ? max(0, (int)$row['water_new'] - (int)$row['water_old']) : 0;
            $elecCost  = $elecUsed * $elecPrice;
            $waterCost = $waterUsed * $waterPrice;

            $row['invoice_code'] = 'HD-' . str_replace('-', '', $row['month_year']) . '-0' . $row['id'];
            $row['room']         = $row['room_number'];
            $row['tenant']       = $row['tenant_name'] ?? 'Khách thuê';
            $row['month']        = str_replace('-', '/', $row['month_year']);
            $row['electric_used']= $elecUsed;
            $row['water_used']   = $waterUsed;
            $row['electric']     = $elecCost;
            $row['water']        = $waterCost;
            $row['service_fee']  = $otherFee;
            $row['total']        = (float)$row['total_amount'];
        }
        unset($row);

        return $rows;
    }

    // 6. Danh sách Phòng trọ từ SQL kèm thông tin Khách đang thuê & Hợp đồng
    public function getRoomsList(string $buildingId = 'all'): array {
        $prefix = $this->getRoomPrefixByBuilding($buildingId);
        $whereRoom = $prefix !== '' ? " WHERE r.room_number LIKE :prefix " : "";
        $params = $prefix !== '' ? [':prefix' => $prefix . '%'] : [];

        $sql = "
            SELECT 
                r.*,
                c.id AS active_contract_id,
                c.start_date,
                c.end_date,
                c.deposit_amount,
                u.id AS tenant_id,
                u.full_name AS tenant_name,
                u.phone AS tenant_phone,
                u.email AS tenant_email
            FROM rooms r
            LEFT JOIN contracts c ON c.room_id = r.id AND c.status = 'active'
            LEFT JOIN users u ON c.tenant_id = u.id
            $whereRoom
            ORDER BY r.room_number ASC
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 7. Danh sách Hợp đồng từ SQL
    public function getContractsList(string $buildingId = 'all'): array {
        $prefix = $this->getRoomPrefixByBuilding($buildingId);
        $whereRoom = $prefix !== '' ? " WHERE r.room_number LIKE :prefix " : "";
        $params = $prefix !== '' ? [':prefix' => $prefix . '%'] : [];

        $sql = "
            SELECT 
                c.*,
                r.room_number,
                r.price AS room_price,
                u.full_name AS tenant_name,
                u.phone AS tenant_phone,
                u.email AS tenant_email
            FROM contracts c
            JOIN rooms r ON c.room_id = r.id
            JOIN users u ON c.tenant_id = u.id
            $whereRoom
            ORDER BY (c.status = 'active') DESC, c.id DESC
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 8. Ghi nhận & Lấy danh sách Nhật ký Hoạt động (chỉ ghi khi có thay đổi dữ liệu, ghi rõ tài khoản thực hiện)
    public function logActivity(string $title, string $type = 'info'): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['activity_logs']) || !is_array($_SESSION['activity_logs'])) {
            $_SESSION['activity_logs'] = [];
        }
        $userFullName = $_SESSION['full_name'] ?? 'Quản trị viên';
        $userRole     = $_SESSION['role'] ?? 'admin';
        $userName     = $_SESSION['username'] ?? 'admin';

        array_unshift($_SESSION['activity_logs'], [
            'id'       => uniqid('act_'),
            'user'     => $userFullName,
            'username' => $userName,
            'role'     => $userRole,
            'title'    => $title,
            'time'     => date('H:i:s - d/m/Y'),
            'type'     => $type
        ]);
        // Giữ tối đa 50 thao tác gần nhất trong session
        $_SESSION['activity_logs'] = array_slice($_SESSION['activity_logs'], 0, 50);
    }

    public function getNotifications(string $buildingId = 'all'): array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['activity_logs']) || !is_array($_SESSION['activity_logs'])) {
            $_SESSION['activity_logs'] = [];
        }
        return $_SESSION['activity_logs'];
    }

    // 9. Xác nhận Thu tiền Hóa đơn trên SQL (`status = 'paid'`)
    public function markInvoiceStatus(int $invoiceId, string $status = 'paid'): bool {
        if (!in_array($status, ['paid', 'unpaid'], true)) {
            return false;
        }
        $stmt = $this->conn->prepare("UPDATE invoices SET status = :status WHERE id = :id");
        return $stmt->execute([':status' => $status, ':id' => $invoiceId]);
    }

    // 10. Xóa Hóa đơn trên SQL
    public function deleteInvoice(int $invoiceId): bool {
        $stmt = $this->conn->prepare("DELETE FROM invoices WHERE id = :id");
        return $stmt->execute([':id' => $invoiceId]);
    }

    // 11. Lập Hóa đơn mới vào SQL (`invoices`, `utilities_readings`, `invoice_details`)
    public function createNewInvoice(int $roomId, string $monthYear, int $elecOld, int $elecNew, int $waterOld, int $waterNew): array {
        // Chuẩn hóa kỳ dạng MM-YYYY (VD: 11-2023)
        $monthYear = trim($monthYear);
        if (preg_match('/^(\d{4})-(\d{2})$/', $monthYear, $m)) {
            $monthYear = $m[2] . '-' . $m[1];
        } elseif (preg_match('/^(\d{1,2})[\/\-](\d{4})$/', $monthYear, $m)) {
            $monthYear = str_pad($m[1], 2, '0', STR_PAD_LEFT) . '-' . $m[2];
        }

        if ($elecNew < $elecOld || $waterNew < $waterOld) {
            return ['ok' => false, 'msg' => 'Chỉ số điện/nước mới không được nhỏ hơn chỉ số cũ!'];
        }

        // Kiểm tra phòng
        $stmtRoom = $this->conn->prepare("SELECT * FROM rooms WHERE id = :id LIMIT 1");
        $stmtRoom->execute([':id' => $roomId]);
        $room = $stmtRoom->fetch(PDO::FETCH_ASSOC);
        if (!$room) {
            return ['ok' => false, 'msg' => 'Không tìm thấy phòng trọ đã chọn.'];
        }

        // Kiểm tra trùng hóa đơn kỳ này
        $chk = $this->conn->prepare("SELECT id FROM invoices WHERE room_id = :room_id AND month_year = :my LIMIT 1");
        $chk->execute([':room_id' => $roomId, ':my' => $monthYear]);
        if ($chk->fetch()) {
            return ['ok' => false, 'msg' => "Phòng {$room['room_number']} đã có hóa đơn kỳ {$monthYear} trong CSDL!"];
        }

        // Tìm hợp đồng hiệu lực hoặc gần nhất của phòng
        $stmtCon = $this->conn->prepare("
            SELECT id FROM contracts 
            WHERE room_id = :room_id 
            ORDER BY (status = 'active') DESC, id DESC LIMIT 1
        ");
        $stmtCon->execute([':room_id' => $roomId]);
        $contract = $stmtCon->fetch(PDO::FETCH_ASSOC);

        // Nếu phòng chưa có hợp đồng nào, tự tạo hợp đồng cho khách mặc định để đảm bảo khóa ngoại SQL
        if (!$contract) {
            $tenantId = (int)$this->conn->query("SELECT id FROM users WHERE role = 'tenant' ORDER BY id ASC LIMIT 1")->fetchColumn();
            if ($tenantId <= 0) {
                $tenantId = 1;
            }
            $insCon = $this->conn->prepare("
                INSERT INTO contracts (room_id, tenant_id, start_date, end_date, deposit_amount, status)
                VALUES (:room_id, :tenant_id, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 12 MONTH), :deposit, 'active')
            ");
            $insCon->execute([
                ':room_id'   => $roomId,
                ':tenant_id' => $tenantId,
                ':deposit'   => $room['price']
            ]);
            $contractId = (int)$this->conn->lastInsertId();
            $this->conn->prepare("UPDATE rooms SET status = 'rented' WHERE id = :id")->execute([':id' => $roomId]);
        } else {
            $contractId = (int)$contract['id'];
        }

        // Tính tổng tiền theo bảng `services`
        $srvMap     = $this->getServicesMap();
        $elecUsed   = $elecNew - $elecOld;
        $waterUsed  = $waterNew - $waterOld;
        $elecCost   = $elecUsed * $srvMap['elec_price'];
        $waterCost  = $waterUsed * $srvMap['water_price'];
        $roomPrice  = (float)$room['price'];
        $totalAmount= $roomPrice + $elecCost + $waterCost + $srvMap['other_total'];

        try {
            $this->conn->beginTransaction();

            // Lưu / cập nhật chỉ số điện nước vào `utilities_readings`
            $chkUr = $this->conn->prepare("SELECT id FROM utilities_readings WHERE room_id = :room_id AND month_year = :my LIMIT 1");
            $chkUr->execute([':room_id' => $roomId, ':my' => $monthYear]);
            $existingUr = $chkUr->fetch(PDO::FETCH_ASSOC);

            if ($existingUr) {
                $updUr = $this->conn->prepare("
                    UPDATE utilities_readings 
                    SET electric_old = :eo, electric_new = :en, water_old = :wo, water_new = :wn
                    WHERE id = :id
                ");
                $updUr->execute([
                    ':eo' => $elecOld, ':en' => $elecNew,
                    ':wo' => $waterOld, ':wn' => $waterNew,
                    ':id' => $existingUr['id']
                ]);
            } else {
                $insUr = $this->conn->prepare("
                    INSERT INTO utilities_readings (room_id, month_year, electric_old, electric_new, water_old, water_new)
                    VALUES (:room_id, :my, :eo, :en, :wo, :wn)
                ");
                $insUr->execute([
                    ':room_id' => $roomId, ':my' => $monthYear,
                    ':eo' => $elecOld, ':en' => $elecNew,
                    ':wo' => $waterOld, ':wn' => $waterNew
                ]);
            }

            // Lưu hóa đơn vào `invoices`
            $insInv = $this->conn->prepare("
                INSERT INTO invoices (room_id, contract_id, month_year, total_amount, status)
                VALUES (:room_id, :contract_id, :my, :total, 'unpaid')
            ");
            $insInv->execute([
                ':room_id'     => $roomId,
                ':contract_id' => $contractId,
                ':my'          => $monthYear,
                ':total'       => $totalAmount
            ]);
            $invoiceId = (int)$this->conn->lastInsertId();

            // Lưu chi tiết vào `invoice_details`
            $insDet = $this->conn->prepare("
                INSERT INTO invoice_details (invoice_id, service_id, quantity, applied_price, subtotal)
                VALUES (:inv_id, :srv_id, :qty, :price, :sub)
            ");
            // Tiền phòng
            $insDet->execute([
                ':inv_id' => $invoiceId, ':srv_id' => null,
                ':qty' => 1, ':price' => $roomPrice, ':sub' => $roomPrice
            ]);
            // Các dịch vụ trong bảng `services`
            foreach ($srvMap['list'] as $srv) {
                $nameLower = mb_strtolower($srv['service_name'], 'UTF-8');
                $uPrice = (float)$srv['unit_price'];
                if (strpos($nameLower, 'điện') !== false || strpos($nameLower, 'dien') !== false) {
                    $insDet->execute([':inv_id' => $invoiceId, ':srv_id' => $srv['id'], ':qty' => $elecUsed, ':price' => $uPrice, ':sub' => $elecCost]);
                } elseif (strpos($nameLower, 'nước') !== false || strpos($nameLower, 'nuoc') !== false) {
                    $insDet->execute([':inv_id' => $invoiceId, ':srv_id' => $srv['id'], ':qty' => $waterUsed, ':price' => $uPrice, ':sub' => $waterCost]);
                } else {
                    $insDet->execute([':inv_id' => $invoiceId, ':srv_id' => $srv['id'], ':qty' => 1, ':price' => $uPrice, ':sub' => $uPrice]);
                }
            }

            $this->conn->commit();
            return [
                'ok'  => true,
                'msg' => "Đã lập hóa đơn kỳ {$monthYear} cho phòng {$room['room_number']} (Tổng: " . number_format($totalAmount, 0, ',', '.') . " đ) thành công!"
            ];
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return ['ok' => false, 'msg' => 'Lỗi CSDL khi lập hóa đơn: ' . $e->getMessage()];
        }
    }
}
