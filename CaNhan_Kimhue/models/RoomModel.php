<?php
// CaNhan_Kimhue/models/RoomModel.php

class RoomModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // 1. Lấy danh sách tất cả phòng trọ
    public function getAllRooms() {
        $stmt = $this->pdo->prepare("SELECT * FROM rooms ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // 2. Lấy thông tin 1 phòng theo ID
    public function getRoomById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // 3. Thêm phòng mới (Khớp cột: room_number, price, status, description)
    public function addRoom($room_number, $price, $status, $description) {
        $stmt = $this->pdo->prepare("INSERT INTO rooms (room_number, price, status, description) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$room_number, $price, $status, $description]);
    }

    // 4. Cập nhật thông tin phòng
    public function updateRoom($id, $room_number, $price, $status, $description) {
        $stmt = $this->pdo->prepare("UPDATE rooms SET room_number = ?, price = ?, status = ?, description = ? WHERE id = ?");
        return $stmt->execute([$room_number, $price, $status, $description, $id]);
    }

    // 5. Xóa phòng
    public function deleteRoom($id) {
        $stmt = $this->pdo->prepare("DELETE FROM rooms WHERE id = ?");
        return $stmt->execute([$id]);
    }
    // 6. Tìm kiếm phòng theo số phòng hoặc mô tả
    public function searchRooms($keyword) {
        $stmt = $this->pdo->prepare("SELECT * FROM rooms WHERE room_number LIKE ? OR description LIKE ? ORDER BY id DESC");
        $searchTerm = "%" . $keyword . "%";
        $stmt->execute([$searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
}
?>