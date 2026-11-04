<?php
require_once __DIR__ . '/../Config/Database.php';

class RoomModel {
    private PDO $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    /**
     * Thống kê tổng quan phòng trọ trực tiếp từ CSDL chung (quan_ly_nha_tro)
     * Tự động lọc theo tòa nhà được phân công nếu tài khoản đang đăng nhập là Quản lý
     */
    public function getRoomStats(): array {
        require_once __DIR__ . '/ReportModel.php';
        $mgr = ReportModel::getManagerBuildingInfo();
        $whereRoom = "WHERE 1=1";
        $params = [];
        if ($mgr) {
            $whereRoom .= " AND (building_id = :mgrBId OR room_number LIKE :mgrPfx)";
            $params[':mgrBId'] = $mgr['id'];
            $params[':mgrPfx'] = $mgr['prefix'] . '%';
        }

        $stmtTotal = $this->conn->prepare("SELECT COUNT(*) FROM rooms " . $whereRoom);
        $stmtTotal->execute($params);
        $total = (int)$stmtTotal->fetchColumn();

        $stmtRented = $this->conn->prepare("SELECT COUNT(*) FROM rooms " . $whereRoom . " AND status = 'rented'");
        $stmtRented->execute($params);
        $rented = (int)$stmtRented->fetchColumn();

        $available = max(0, $total - $rented);
        $occupancyRate = $total > 0 ? (int)round(($rented / $total) * 100) : 0;

        $stmtPot = $this->conn->prepare("SELECT COALESCE(SUM(price), 0) FROM rooms " . $whereRoom . " AND status = 'rented'");
        $stmtPot->execute($params);
        $monthlyPotential = (float)$stmtPot->fetchColumn();

        $stmtAvg = $this->conn->prepare("SELECT COALESCE(AVG(price), 0) FROM rooms " . $whereRoom);
        $stmtAvg->execute($params);
        $avgPrice = (float)$stmtAvg->fetchColumn();

        // Đếm hợp đồng active
        $whereContract = "WHERE c.status = 'active'";
        if ($mgr) {
            $whereContract .= " AND (r.building_id = :mgrBId OR r.room_number LIKE :mgrPfx)";
        }
        $stmtAct = $this->conn->prepare("SELECT COUNT(*) FROM contracts c JOIN rooms r ON r.id = c.room_id " . $whereContract);
        $stmtAct->execute($params);
        $activeContracts = (int)$stmtAct->fetchColumn();

        // Đếm hóa đơn nợ
        $whereInv = "WHERE i.status = 'unpaid'";
        if ($mgr) {
            $whereInv .= " AND (r.building_id = :mgrBId OR r.room_number LIKE :mgrPfx)";
        }
        $stmtUnp = $this->conn->prepare("SELECT COUNT(*) FROM invoices i JOIN rooms r ON r.id = i.room_id " . $whereInv);
        $stmtUnp->execute($params);
        $unpaidInvoices = (int)$stmtUnp->fetchColumn();

        $stmtUnpRooms = $this->conn->prepare("SELECT COUNT(DISTINCT i.room_id) FROM invoices i JOIN rooms r ON r.id = i.room_id " . $whereInv);
        $stmtUnpRooms->execute($params);
        $unpaidRooms = (int)$stmtUnpRooms->fetchColumn();

        return [
            'total'             => $total,
            'rented'            => $rented,
            'available'         => $available,
            'occupancy_rate'    => $occupancyRate,
            'monthly_potential' => $monthlyPotential,
            'avg_price'         => $avgPrice,
            'active_contracts'  => $activeContracts,
            'unpaid_invoices'   => $unpaidInvoices,
            'unpaid_rooms'      => $unpaidRooms,
        ];
    }

    /**
     * Lấy danh sách dịch vụ chung từ bảng services
     */
    public function getServices(): array {
        return $this->conn->query("SELECT * FROM services ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách các tầng hiện có từ mã phòng (VD: A101 -> Tầng 1, A201 -> Tầng 2, P101 -> Tầng 1)
     */
    public function getAvailableFloors(): array {
        require_once __DIR__ . '/ReportModel.php';
        $mgr = ReportModel::getManagerBuildingInfo();
        if ($mgr) {
            $stmt = $this->conn->prepare("SELECT room_number FROM rooms WHERE building_id = :bId OR room_number LIKE :pfx ORDER BY room_number ASC");
            $stmt->execute([':bId' => $mgr['id'], ':pfx' => $mgr['prefix'] . '%']);
            $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } else {
            $rows = $this->conn->query("SELECT room_number FROM rooms ORDER BY room_number ASC")->fetchAll(PDO::FETCH_COLUMN);
        }
        $floors = [];
        foreach ($rows as $rn) {
            if (preg_match('/^[A-Za-z]*(\d)\d{2}$/', trim((string)$rn), $m)) {
                $floors[(int)$m[1]] = (int)$m[1];
            }
        }
        if (empty($floors)) {
            $floors = [1 => 1, 2 => 2, 3 => 3, 4 => 4];
        }
        ksort($floors);
        return array_values($floors);
    }

    /**
     * Lấy danh sách phòng kèm thông tin hợp đồng hiệu lực, khách thuê và hóa đơn nợ
     */
    public function getRooms(string $keyword = '', string $status = '', string $floor = '', string $priceRange = ''): array {
        $sql = "SELECT r.*,
                       c.id AS contract_id,
                       c.start_date AS contract_start,
                       c.end_date AS contract_end,
                       c.deposit_amount,
                       u.id AS tenant_id,
                       u.full_name AS tenant_name,
                       u.phone AS tenant_phone,
                       u.email AS tenant_email,
                       u.roommate_name,
                       u.roommate_phone,
                       (SELECT COUNT(*) FROM invoices i WHERE i.room_id = r.id AND i.status = 'unpaid') AS unpaid_count,
                       (SELECT COALESCE(SUM(i2.total_amount), 0) FROM invoices i2 WHERE i2.room_id = r.id AND i2.status = 'unpaid') AS unpaid_amount
                FROM rooms r
                LEFT JOIN contracts c ON c.id = (
                    SELECT c2.id FROM contracts c2
                    WHERE c2.room_id = r.id AND c2.status = 'active'
                    ORDER BY c2.id DESC LIMIT 1
                )
                LEFT JOIN users u ON u.id = c.tenant_id
                WHERE 1=1";

        $params = [];
        require_once __DIR__ . '/ReportModel.php';
        $mgr = ReportModel::getManagerBuildingInfo();
        if ($mgr) {
            $sql .= " AND (r.building_id = :mgrBId OR r.room_number LIKE :mgrPfx)";
            $params[':mgrBId'] = $mgr['id'];
            $params[':mgrPfx'] = $mgr['prefix'] . '%';
        }

        if ($keyword !== '') {
            $sql .= " AND (r.room_number LIKE :kw1 OR r.description LIKE :kw2 OR u.full_name LIKE :kw3 OR u.phone LIKE :kw4)";
            $kw = "%$keyword%";
            $params[':kw1'] = $kw;
            $params[':kw2'] = $kw;
            $params[':kw3'] = $kw;
            $params[':kw4'] = $kw;
        }

        if ($status !== '' && in_array($status, ['available', 'rented'], true)) {
            $sql .= " AND r.status = :status";
            $params[':status'] = $status;
        }

        if ($floor !== '' && ctype_digit($floor)) {
            $sql .= " AND (r.room_number REGEXP :flReg OR r.description LIKE :flDesc)";
            $params[':flReg'] = "[A-Za-z]?" . $floor . "[0-9]{2}";
            $params[':flDesc'] = "%tầng {$floor}%";
        }

        if ($priceRange === 'under3m') {
            $sql .= " AND r.price <= 3000000";
        } elseif ($priceRange === '3m_4m') {
            $sql .= " AND r.price > 3000000 AND r.price <= 4000000";
        } elseif ($priceRange === 'over4m') {
            $sql .= " AND r.price > 4000000";
        }

        $sql .= " ORDER BY r.room_number ASC, r.id ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $list = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($list as &$row) {
            if (!empty($row['contract_id'])) {
                $year = !empty($row['contract_start']) ? date('Y', strtotime($row['contract_start'])) : date('Y');
                $row['contract_code'] = 'HD-' . $year . '-' . str_pad((string)$row['contract_id'], 3, '0', STR_PAD_LEFT);
            } else {
                $row['contract_code'] = null;
            }

            // Tự động nhận diện số tầng từ mã phòng (VD: A101 -> Tầng 1, P201 -> Tầng 2)
            if (preg_match('/^[A-Za-z]*(\d)\d{2}$/', trim((string)$row['room_number']), $m)) {
                $row['floor_label'] = 'Tầng ' . $m[1];
            } elseif (preg_match('/tầng\s*(\d+)/iu', (string)($row['description'] ?? ''), $m2)) {
                $row['floor_label'] = 'Tầng ' . $m2[1];
            } else {
                $row['floor_label'] = 'Khu chính';
            }

            $row['details'] = self::parseRoomDetails($row);
            $row['services'] = $this->getRoomServices((int)$row['id']);
        }
        unset($row);

        return $list;
    }

    public static function parseRoomDetails(array $room): array {
        $desc = (string)($room['description'] ?? '');
        $roomNumber = (string)($room['room_number'] ?? '');

        // Tầng mặc định từ mã phòng (VD: P101 -> 1, P201 -> 2)
        $floor = 1;
        if (preg_match('/^[A-Za-z]*(\d)\d{2}$/', $roomNumber, $m)) {
            $floor = (int)$m[1];
        } elseif (preg_match('/tầng\s*(\d+)/iu', $desc, $m)) {
            $floor = (int)$m[1];
        }

        // Tòa nhà
        $building = 'Tòa A - Smart Stay Sunrise';
        if (preg_match('/Tòa:\s*([^|]+)/iu', $desc, $m)) {
            $building = trim($m[1]);
        }

        // Loại phòng
        $roomType = 'Phòng Tiêu chuẩn (Standard) (2.600.000đ)';
        if (preg_match('/Loại:\s*([^|]+)/iu', $desc, $m)) {
            $roomType = trim($m[1]);
        } elseif ((float)($room['price'] ?? 0) >= 4000000) {
            $roomType = 'Phòng Studio (4.000.000đ)';
        } elseif ((float)($room['price'] ?? 0) >= 3500000) {
            $roomType = 'Phòng Cao cấp (VIP) (3.500.000đ)';
        }

        // Diện tích
        $area = 22.00;
        if (preg_match('/Diện tích:\s*([\d\.]+)/iu', $desc, $m)) {
            $area = (float)$m[1];
        }

        // Số người tối đa
        $maxOccupants = 2;
        if (preg_match('/Tối đa:\s*(\d+)/iu', $desc, $m)) {
            $maxOccupants = (int)$m[1];
        }

        // Tiện nghi
        $amenities = 'Máy lạnh, Nước nóng, Wifi, Gác lửng, WC riêng';
        if (preg_match('/Tiện nghi:\s*([^|]+)/iu', $desc, $m)) {
            $amenities = trim($m[1]);
        }

        // Hình ảnh phòng
        $image = 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=900&q=85';
        if (preg_match('/Ảnh:\s*([^|\s]+)/iu', $desc, $m)) {
            $image = trim($m[1]);
        }

        // Ghi chú / mô tả còn lại
        $note = $desc;
        if (preg_match('/Ghi chú:\s*([^|]+)/iu', $desc, $m)) {
            $note = trim($m[1]);
        } else {
            $clean = preg_replace('/(Tòa:[^|]*\|?|Loại:[^|]*\|?|Tầng\s*\d+\|?|Diện tích:[^|]*\|?|Tối đa:[^|]*\|?|Tiện nghi:[^|]*\|?|Dịch vụ:[^|]*\|?|Ảnh:[^|]*\|?)/iu', '', $desc);
            $clean = trim(trim((string)$clean), '|');
            if ($clean !== '') {
                $note = trim($clean);
            }
        }

        return [
            'building'      => $building,
            'room_type'     => $roomType,
            'floor'         => $floor,
            'area'          => $area,
            'max_occupants' => $maxOccupants,
            'amenities'     => $amenities,
            'image'         => $image,
            'note'          => $note,
        ];
    }

    public function getRoomServices(int $roomId): array {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM room_services WHERE room_id = :room_id ORDER BY id ASC");
            $stmt->execute([':room_id' => $roomId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function saveRoomServices(int $roomId, array $services): void {
        $stmt = $this->conn->prepare("DELETE FROM room_services WHERE room_id = :room_id");
        $stmt->execute([':room_id' => $roomId]);

        if (empty($services)) return;

        $ins = $this->conn->prepare("INSERT INTO room_services (room_id, service_name, unit_price, unit) VALUES (:room_id, :name, :price, :unit)");
        foreach ($services as $s) {
            $name = trim($s['service_name'] ?? '');
            if ($name === '') continue;
            $price = (float)($s['unit_price'] ?? 0);
            $unit = trim($s['unit'] ?? 'Tháng') ?: 'Tháng';
            $ins->execute([
                ':room_id' => $roomId,
                ':name'    => $name,
                ':price'   => $price,
                ':unit'    => $unit,
            ]);
        }
    }

    public function getRoomById(int $id): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM rooms WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public function createRoom(string $room_number, float $price, string $status, string $description, ?int $building_id = null): int {
        $sql = "INSERT INTO rooms (room_number, price, status, description, building_id) VALUES (:room_number, :price, :status, :description, :building_id)";
        $stmt = $this->conn->prepare($sql);
        $res = $stmt->execute([
            ':room_number'  => $room_number,
            ':price'        => $price,
            ':status'       => $status,
            ':description'  => $description,
            ':building_id'  => $building_id ?: 1,
        ]);
        return $res ? (int)$this->conn->lastInsertId() : 0;
    }

    public function updateRoom(int $id, string $room_number, float $price, string $status, string $description, ?int $building_id = null): bool {
        $sql = "UPDATE rooms SET room_number = :room_number, price = :price, status = :status, description = :description, building_id = :building_id WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':room_number'  => $room_number,
            ':price'        => $price,
            ':status'       => $status,
            ':description'  => $description,
            ':building_id'  => $building_id ?: 1,
            ':id'           => $id,
        ]);
    }

    public function deleteRoom(int $id): bool {
        try {
            $this->conn->prepare("DELETE FROM room_services WHERE room_id = :id")->execute([':id' => $id]);
        } catch (PDOException $e) {}
        $stmt = $this->conn->prepare("DELETE FROM rooms WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
?>