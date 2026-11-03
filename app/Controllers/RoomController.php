<?php
require_once __DIR__ . '/../Models/RoomModel.php';

class RoomController {
    private RoomModel $model;

    public function __construct() {
        $this->model = new RoomModel();
    }

    public function index(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Xử lý Thêm, Sửa, Xóa (POST) có kiểm tra CSRF token
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!hash_equals($_SESSION['csrf_token'], $token)) {
                $_SESSION['room_flash_error'] = 'Phiên làm việc không hợp lệ (CSRF). Vui lòng thử lại!';
                header("Location: index.php?route=rooms");
                exit;
            }

            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $room_number = strtoupper(trim($_POST['room_number'] ?? ''));
                $priceRaw = str_replace([',', '.', ' '], '', trim($_POST['price'] ?? '0'));
                // Nếu form gửi dạng số chuẩn (vd: 3000000 hoặc 3000000.00)
                $price = is_numeric($_POST['price'] ?? '') ? (float)$_POST['price'] : (float)$priceRaw;
                $status = $this->validStatus($_POST['status'] ?? 'available');
                $description = trim($_POST['description'] ?? '');

                if ($room_number === '' || mb_strlen($room_number) > 20) {
                    $_SESSION['room_flash_error'] = 'Vui lòng nhập mã phòng hợp lệ (tối đa 20 ký tự)!';
                } elseif ($price <= 0) {
                    $_SESSION['room_flash_error'] = 'Giá thuê phòng phải là số lớn hơn 0!';
                } else {
                    try {
                        $this->model->createRoom($room_number, $price, $status, $description);
                        $_SESSION['room_flash_success'] = "Đã thêm phòng {$room_number} vào hệ thống thành công!";
                    } catch (PDOException $e) {
                        $_SESSION['room_flash_error'] = "Không thể thêm: Mã phòng '{$room_number}' đã tồn tại trong CSDL!";
                    }
                }
                header("Location: index.php?route=rooms");
                exit;
            } elseif ($action === 'update') {
                $id = (int)($_POST['id'] ?? 0);
                $room_number = strtoupper(trim($_POST['room_number'] ?? ''));
                $price = is_numeric($_POST['price'] ?? '') ? (float)$_POST['price'] : 0;
                $status = $this->validStatus($_POST['status'] ?? 'available');
                $description = trim($_POST['description'] ?? '');

                if ($id <= 0 || $room_number === '' || mb_strlen($room_number) > 20) {
                    $_SESSION['room_flash_error'] = 'Thông tin mã phòng không hợp lệ!';
                } elseif ($price <= 0) {
                    $_SESSION['room_flash_error'] = 'Giá thuê phòng phải là số lớn hơn 0!';
                } else {
                    try {
                        $this->model->updateRoom($id, $room_number, $price, $status, $description);
                        $_SESSION['room_flash_success'] = "Đã cập nhật thông tin phòng {$room_number} thành công!";
                    } catch (PDOException $e) {
                        $_SESSION['room_flash_error'] = "Không thể cập nhật: Mã phòng '{$room_number}' đã bị trùng với phòng khác!";
                    }
                }
                header("Location: index.php?route=rooms");
                exit;
            } elseif ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $roomInfo = $this->model->getRoomById($id);
                    $roomLabel = $roomInfo['room_number'] ?? ('#' . $id);
                    try {
                        $this->model->deleteRoom($id);
                        $_SESSION['room_flash_success'] = "Đã xóa phòng {$roomLabel} khỏi hệ thống!";
                    } catch (PDOException $e) {
                        $_SESSION['room_flash_error'] = "Không thể xóa phòng {$roomLabel}: Phòng đang có hợp đồng, chỉ số điện nước hoặc hóa đơn liên quan trong CSDL!";
                    }
                }
                header("Location: index.php?route=rooms");
                exit;
            }
        }

        // Lấy thông báo flash (nếu có)
        $flash_success = $_SESSION['room_flash_success'] ?? '';
        $flash_error = $_SESSION['room_flash_error'] ?? '';
        unset($_SESSION['room_flash_success'], $_SESSION['room_flash_error']);

        // Hỗ trợ tương thích tham số msg cũ nếu có link bên ngoài gọi tới
        if ($flash_success === '' && isset($_GET['msg'])) {
            if ($_GET['msg'] === 'created') $flash_success = 'Đã thêm phòng trọ mới thành công!';
            elseif ($_GET['msg'] === 'updated') $flash_success = 'Cập nhật thông tin phòng thành công!';
            elseif ($_GET['msg'] === 'deleted') $flash_success = 'Đã xóa phòng trọ khỏi hệ thống!';
        }

        // Bộ lọc tìm kiếm
        $filters = [
            'keyword'     => trim($_GET['keyword'] ?? ''),
            'status'      => trim($_GET['status'] ?? ''),
            'floor'       => trim($_GET['floor'] ?? ''),
            'price_range' => trim($_GET['price_range'] ?? ''),
        ];

        $stats    = $this->model->getRoomStats();
        $services = $this->model->getServices();
        $floors   = $this->model->getAvailableFloors();
        $rooms    = $this->model->getRooms(
            $filters['keyword'],
            $filters['status'],
            $filters['floor'],
            $filters['price_range']
        );

        require_once __DIR__ . '/../views/rooms/index.php';
    }

    private function validStatus(string $status): string {
        return in_array($status, ['available', 'rented'], true) ? $status : 'available';
    }
}
?>
