<?php
class ContractModel {
    private $conn;

    public function __construct() {
        $host = 'localhost';
        $dbname = 'quan_ly_nha_tro';
        $username = 'root';
        $password = ''; 
        
        try {
            $this->conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Lỗi kết nối CSDL: " . $e->getMessage());
        }
    }

    public function getAllContracts($keyword = '', $status = '') {
        $sql = "SELECT c.*, r.room_number, u.full_name, u.phone 
                FROM contracts c
                LEFT JOIN rooms r ON c.room_id = r.id
                LEFT JOIN users u ON c.tenant_id = u.id
                WHERE 1=1";
        
        $params = [];
        if (!empty($keyword)) {
            $sql .= " AND (r.room_number LIKE :keyword OR u.full_name LIKE :keyword OR c.id LIKE :keyword)";
            $params[':keyword'] = "%$keyword%";
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

    // Hàm Thêm hợp đồng mới
    public function createContract($data) {
        $sql = "INSERT INTO contracts (room_id, tenant_id, start_date, end_date, deposit_amount, status) 
                VALUES (:room_id, :tenant_id, :start_date, :end_date, :deposit_amount, :status)";
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