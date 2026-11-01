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
                $status = $this->validStatus($_POST['status'] ?? 'available');
                $description = trim($_POST['description'] ?? '');

                if ($room_number !== '' && is_numeric($price) && $price > 0) {
                    try {
                        $this->model->createRoom($room_number, $price, $status, $description);
                        header("Location: index.php?route=rooms&msg=created"); exit;
                    } catch (PDOException $e) { $message = "Lỗi: Số phòng đã tồn tại!"; }
                } else { $message = "Vui lòng nhập đủ thông tin bắt buộc (giá thuê phải là số lớn hơn 0)!"; }
            } 
            elseif ($action === 'update') {
                $id = (int)($_POST['id'] ?? 0);
                $room_number = trim($_POST['room_number'] ?? '');
                $price = trim($_POST['price'] ?? 0);
                $status = $this->validStatus($_POST['status'] ?? 'available');
                $description = trim($_POST['description'] ?? '');

                if ($id > 0 && $room_number !== '' && is_numeric($price) && $price > 0) {
                    try {
                        $this->model->updateRoom($id, $room_number, $price, $status, $description);
                        header("Location: index.php?route=rooms&msg=updated"); exit;
                    } catch (PDOException $e) { $message = "Lỗi: Số phòng đã tồn tại!"; }
                } else { $message = "Vui lòng nhập đủ thông tin bắt buộc (giá thuê phải là số lớn hơn 0)!"; }
            }
            elseif ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    try {
                        $this->model->deleteRoom($id);
                        header("Location: index.php?route=rooms&msg=deleted"); exit;
                    } catch (PDOException $e) {
                        // Phòng đang có hợp đồng / hóa đơn / chỉ số điện nước (ràng buộc khóa ngoại)
                        $message = "Không thể xóa: phòng này đang có hợp đồng, hóa đơn hoặc chỉ số điện nước liên quan!";
                    }
                }
            }
        }

        // Kiểm tra xem có đang bấm nút Sửa không
        $edit_room = null;
        if (isset($_GET['edit_id'])) {
            $edit_room = $this->model->getRoomById((int)$_GET['edit_id']);
        }

        // Xử lý Tìm kiếm
        $keyword = trim($_GET['keyword'] ?? '');
        $rooms = $this->model->getRooms($keyword);
        
        // Gọi View hiển thị
        require_once __DIR__ . '/../views/rooms/index.php';
    }

    // Chỉ chấp nhận trạng thái có trong CSDL (ENUM 'available', 'rented')
    private function validStatus($status) {
        return in_array($status, ['available', 'rented'], true) ? $status : 'available';
    }
}
?>
