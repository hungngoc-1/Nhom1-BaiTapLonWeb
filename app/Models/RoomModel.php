<?php
class RoomModel {
    private $conn;

    public function __construct() {
        try {
            $this->conn = new PDO("mysql:host=localhost;dbname=quan_ly_nha_tro;charset=utf8mb4", "root", "");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Lỗi kết nối CSDL: " . $e->getMessage());
        }
    }

    public function getRooms($keyword = '') {
        $sql = "SELECT * FROM rooms";
        if (!empty($keyword)) {
            $sql .= " WHERE room_number LIKE :keyword OR description LIKE :keyword";
        }
        $sql .= " ORDER BY id DESC";
        $stmt = $this->conn->prepare($sql);
        if (!empty($keyword)) $stmt->bindValue(':keyword', "%$keyword%");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRoomById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM rooms WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createRoom($room_number, $price, $status, $description) {
        $sql = "INSERT INTO rooms (room_number, price, status, description) VALUES (:room_number, :price, :status, :description)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':room_number' => $room_number,
            ':price' => $price,
            ':status' => $status,
            ':description' => $description
        ]);
    }

    public function updateRoom($id, $room_number, $price, $status, $description) {
        $sql = "UPDATE rooms SET room_number = :room_number, price = :price, status = :status, description = :description WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':room_number' => $room_number,
            ':price' => $price,
            ':status' => $status,
            ':description' => $description,
            ':id' => $id
        ]);
    }

    public function deleteRoom($id) {
        $stmt = $this->conn->prepare("DELETE FROM rooms WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
?>