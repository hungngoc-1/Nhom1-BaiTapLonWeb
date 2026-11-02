<?php
require_once __DIR__ . '/../Config/Database.php';

class RoomModel {
    private $conn;

    public function __construct() {
        // Dùng kết nối chung trong app/Config/Database.php
        $this->conn = Database::getConnection();
    }

    public function getRooms($keyword = '', $status = '', $room_type = '', $floor = '') {
    $sql = "SELECT * FROM rooms WHERE 1=1";
    $params = [];

    // Lọc theo từ khóa (Mã phòng hoặc Mô tả)
    if (!empty($keyword)) {
        $sql .= " AND (room_number LIKE :keyword OR description LIKE :keyword)";
        $params[':keyword'] = "%$keyword%";
    }
    
    // Lọc theo trạng thái
    if (!empty($status)) {
        $sql .= " AND status = :status";
        $params[':status'] = $status;
    }

    // Lọc theo loại phòng (Dựa vào từ khóa trong description)
    if (!empty($room_type)) {
        if ($room_type === 'standard') {
            $sql .= " AND (description LIKE '%Tiêu chuẩn%' OR description LIKE '%Standard%')";
        } elseif ($room_type === 'duplex') {
            $sql .= " AND (description LIKE '%Gác lửng%' OR description LIKE '%Duplex%')";
        } elseif ($room_type === 'studio') {
            $sql .= " AND (description LIKE '%Studio%')";
        }
    }

    // Lọc theo tầng (Dựa vào số đầu tiên của mã phòng. VD: P102 -> Tầng 1)
    if (!empty($floor)) {
        $sql .= " AND room_number LIKE :floor";
        $params[':floor'] = "_$floor%"; // Bỏ qua ký tự đầu (P), khớp số tầng
    }

    $sql .= " ORDER BY id DESC";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute($params);
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
