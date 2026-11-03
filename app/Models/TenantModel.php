<?php
class TenantModel {
    private $conn;

    public function __construct() {
        try {
            $this->conn = new PDO("mysql:host=localhost;dbname=quan_ly_nha_tro;charset=utf8mb4", "root", "");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Lỗi kết nối CSDL: " . $e->getMessage());
        }
    }

    public function getTenants($keyword = '') {
        $sql = "SELECT * FROM tenants";
        if (!empty($keyword)) {
            $sql .= " WHERE full_name LIKE :keyword OR phone LIKE :keyword OR cccd LIKE :keyword";
        }
        $sql .= " ORDER BY id DESC";
        $stmt = $this->conn->prepare($sql);
        if (!empty($keyword)) {
            $stmt->bindValue(':keyword', "%$keyword%");
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>