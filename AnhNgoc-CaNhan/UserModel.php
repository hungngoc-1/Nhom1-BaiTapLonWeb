<?php
class UserModel {
    private $conn;

    public function __construct() {
        try {
            $this->conn = new PDO("mysql:host=localhost;dbname=quan_ly_nha_tro;charset=utf8mb4", "root", "");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Lỗi kết nối CSDL: " . $e->getMessage());
        }
    }

    public function getUsers($keyword = '') {
        $sql = "SELECT * FROM users";
        if (!empty($keyword)) {
            $sql .= " WHERE full_name LIKE :keyword OR username LIKE :keyword";
        }
        $sql .= " ORDER BY id DESC";
        $stmt = $this->conn->prepare($sql);
        if (!empty($keyword)) $stmt->bindValue(':keyword', "%$keyword%");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createUser($username, $password, $full_name, $role) {
        $sql = "INSERT INTO users (username, password, full_name, role) VALUES (:username, :password, :full_name, :role)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':username' => $username, ':password' => $password, ':full_name' => $full_name, ':role' => $role]);
    }

    public function updateUser($id, $full_name, $role) {
        $sql = "UPDATE users SET full_name = :full_name, role = :role WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':full_name' => $full_name, ':role' => $role, ':id' => $id]);
    }

    public function deleteUser($id) {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
?>