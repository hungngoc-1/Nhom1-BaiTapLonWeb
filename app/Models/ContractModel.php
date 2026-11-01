<?php
require_once __DIR__ . '/../Config/Database.php';

class ContractModel {
    private $conn;

    public function __construct() {
        // Dùng kết nối chung trong app/Config/Database.php
        $this->conn = Database::getConnection();
    }

    public function getAllContracts($keyword = '', $status = '') {
        $sql = "SELECT c.*, r.room_number, u.full_name, u.phone 
                FROM contracts c
                LEFT JOIN rooms r ON c.room_id = r.id
                LEFT JOIN users u ON c.tenant_id = u.id
                WHERE 1=1";
        
        $params = [];
        if (!empty($keyword)) {
            $sql .= " AND (r.room_number LIKE :kw1 OR u.full_name LIKE :kw2 OR c.id LIKE :kw3)";
            $params[':kw1'] = $params[':kw2'] = $params[':kw3'] = "%$keyword%";
        }
        if (!empty($status)) {
            $sql .= " AND c.status = :status";
            $params[':status'] = $status;
        }
        $sql .= " ORDER BY c.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy 1 hợp đồng theo id (dùng cho form Sửa)
    public function getContractById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM contracts WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Danh sách phòng & khách thuê để chọn trong form
    public function getRooms() {
        return $this->conn->query("SELECT id, room_number, status FROM rooms ORDER BY room_number")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTenants() {
        return $this->conn->query("SELECT id, full_name, phone FROM users WHERE role = 'tenant' ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);
    }

    // Nghiệp vụ: kiểm tra hợp đồng đang hiệu lực của phòng có bị chồng lấn thời gian không
    public function hasOverlap($room_id, $start_date, $end_date, $exclude_id = 0) {
        $sql = "SELECT COUNT(*) FROM contracts
                WHERE room_id = :room_id AND status = 'active' AND id <> :exclude_id
                  AND start_date <= :end_date AND end_date >= :start_date";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':room_id' => $room_id,
            ':exclude_id' => $exclude_id,
            ':start_date' => $start_date,
            ':end_date' => $end_date
        ]);
        return $stmt->fetchColumn() > 0;
    }

    // Hàm Thêm hợp đồng mới
    public function createContract($data) {
        $sql = "INSERT INTO contracts (room_id, tenant_id, start_date, end_date, deposit_amount, status) 
                VALUES (:room_id, :tenant_id, :start_date, :end_date, :deposit_amount, :status)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($data);
    }

    // Hàm Cập nhật hợp đồng
    public function updateContract($id, $data) {
        $sql = "UPDATE contracts SET room_id = :room_id, tenant_id = :tenant_id, start_date = :start_date,
                       end_date = :end_date, deposit_amount = :deposit_amount, status = :status
                WHERE id = :id";
        $data['id'] = $id;
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($data);
    }

    // Hàm Xóa hợp đồng
    public function deleteContract($id) {
        $sql = "DELETE FROM contracts WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
?>
