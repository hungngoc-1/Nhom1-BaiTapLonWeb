<?php
require_once __DIR__ . '/../Config/Database.php';

class RoomModel {
    private PDO $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    /**
     * Thống kê tổng quan phòng trọ trực tiếp từ CSDL chung (quan_ly_nha_tro)
     */
    public function getRoomStats(): array {
        $total = (int)$this->conn->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
        $rented = (int)$this->conn->query("SELECT COUNT(*) FROM rooms WHERE status = 'rented'")->fetchColumn();
        $available = max(0, $total - $rented);
        $occupancyRate = $total > 0 ? (int)round(($rented / $total) * 100) : 0;

        $monthlyPotential = (float)$this->conn->query("SELECT COALESCE(SUM(price), 0) FROM rooms WHERE status = 'rented'")->fetchColumn();
        $avgPrice = (float)$this->conn->query("SELECT COALESCE(AVG(price), 0) FROM rooms")->fetchColumn();
        $activeContracts = (int)$this->conn->query("SELECT COUNT(*) FROM contracts WHERE status = 'active'")->fetchColumn();
        $unpaidInvoices = (int)$this->conn->query("SELECT COUNT(*) FROM invoices WHERE status = 'unpaid'")->fetchColumn();
        $unpaidRooms = (int)$this->conn->query("SELECT COUNT(DISTINCT room_id) FROM invoices WHERE status = 'unpaid'")->fetchColumn();

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
     * Lấy danh sách các tầng hiện có từ mã phòng (VD: P101 -> 1, P201 -> 2, P301 -> 3)
     */
    public function getAvailableFloors(): array {
        $rows = $this->conn->query("SELECT room_number FROM rooms ORDER BY room_number ASC")->fetchAll(PDO::FETCH_COLUMN);
        $floors = [];
        foreach ($rows as $rn) {
            if (preg_match('/^[A-Za-z]*(\d)\d{2}$/', trim((string)$rn), $m)) {
                $floors[(int)$m[1]] = (int)$m[1];
            }
        }
        if (empty($floors)) {
            $floors = [1 => 1, 2 => 2, 3 => 3];
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
            $sql .= " AND (r.room_number LIKE :fl1 OR r.room_number LIKE :fl2 OR r.description LIKE :fl3)";
            $params[':fl1'] = "P{$floor}%";
            $params[':fl2'] = "{$floor}%";
            $params[':fl3'] = "%tầng {$floor}%";
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

            // Tự động nhận diện số tầng từ mã phòng (VD: P101 -> Tầng 1, P201 -> Tầng 2)
            if (preg_match('/^[A-Za-z]*(\d)\d{2}$/', trim((string)$row['room_number']), $m)) {
                $row['floor_label'] = 'Tầng ' . $m[1];
            } elseif (preg_match('/tầng\s*(\d+)/iu', (string)($row['description'] ?? ''), $m2)) {
                $row['floor_label'] = 'Tầng ' . $m2[1];
            } else {
                $row['floor_label'] = 'Khu chính';
            }
        }
        unset($row);

        return $list;
    }

    public function getRoomById(int $id): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM rooms WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }

    public function createRoom(string $room_number, float $price, string $status, string $description): bool {
        $sql = "INSERT INTO rooms (room_number, price, status, description) VALUES (:room_number, :price, :status, :description)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':room_number' => $room_number,
            ':price'       => $price,
            ':status'      => $status,
            ':description' => $description,
        ]);
    }

    public function updateRoom(int $id, string $room_number, float $price, string $status, string $description): bool {
        $sql = "UPDATE rooms SET room_number = :room_number, price = :price, status = :status, description = :description WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':room_number' => $room_number,
            ':price'       => $price,
            ':status'      => $status,
            ':description' => $description,
            ':id'          => $id,
        ]);
    }

    public function deleteRoom(int $id): bool {
        $stmt = $this->conn->prepare("DELETE FROM rooms WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
?>
