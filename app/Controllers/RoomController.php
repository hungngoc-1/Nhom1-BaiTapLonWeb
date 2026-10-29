<?php
require_once __DIR__ . '/../Models/RoomModel.php';

class RoomController {
    private $model;

    public function __construct() {
        $this->model = new RoomModel();
    }

    public function index() {
        $message = '';
        
        // Xử lý Thêm, Sửa, Xóa (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $room_number = trim($_POST['room_number'] ?? '');
                $price = trim($_POST['price'] ?? 0);
                $status = $_POST['status'] ?? 'available';
                $description = trim($_POST['description'] ?? '');

                if (!empty($room_number) && !empty($price)) {
                    try {
                        $this->model->createRoom($room_number, $price, $status, $description);
                        header("Location: index.php?route=rooms&msg=created"); exit;
                    } catch (Exception $e) { $message = "Lỗi: Số phòng đã tồn tại!"; }
                } else { $message = "Vui lòng nhập đủ thông tin bắt buộc!"; }
            } 
            elseif ($action === 'update') {
                $id = $_POST['id'] ?? 0;
                $room_number = trim($_POST['room_number'] ?? '');
                $price = trim($_POST['price'] ?? 0);
                $status = $_POST['status'] ?? 'available';
                $description = trim($_POST['description'] ?? '');

                if ($id > 0 && !empty($room_number)) {
                    $this->model->updateRoom($id, $room_number, $price, $status, $description);
                    header("Location: index.php?route=rooms&msg=updated"); exit;
                }
            }
            elseif ($action === 'delete') {
                $id = $_POST['id'] ?? 0;
                if ($id > 0) $this->model->deleteRoom($id);
                header("Location: index.php?route=rooms&msg=deleted"); exit;
            }
        }

        // Kiểm tra xem có đang bấm nút Sửa không
        $edit_room = null;
        if (isset($_GET['edit_id'])) {
            $edit_room = $this->model->getRoomById($_GET['edit_id']);
        }

        // Xử lý Tìm kiếm
        $keyword = trim($_GET['keyword'] ?? '');
        $rooms = $this->model->getRooms($keyword);
        
        // Gọi View hiển thị
        require_once __DIR__ . '/../views/rooms/index.php';
    }
}
?>