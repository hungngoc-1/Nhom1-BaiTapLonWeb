<?php
require_once __DIR__ . '/../Models/RoomModel.php';

class RoomController {
    private RoomModel $roomModel;

    public function __construct() {
        $this->roomModel = new RoomModel();
    }

    public function index(): void {
        // Lấy từ khóa tìm kiếm (nếu có)
        $keyword = trim($_GET['keyword'] ?? '');
        
        // Gọi Model lấy danh sách phòng
        $rooms = $this->roomModel->getAllRooms($keyword);
        
        // Gọi file giao diện Phòng trọ
        require_once __DIR__ . '/../views/rooms/index.php';
    }
}
?>