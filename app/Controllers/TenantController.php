<?php
require_once __DIR__ . '/../Models/TenantModel.php';
require_once __DIR__ . '/../Models/RoomModel.php';

class TenantController {
    private TenantModel $model;
    private RoomModel $roomModel;

    public function __construct() {
        $this->model = new TenantModel();
        $this->roomModel = new RoomModel();
    }

    public function index(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!hash_equals($_SESSION['csrf_token'], $token)) {
                $_SESSION['tenant_flash_error'] = 'Phiên làm việc không hợp lệ (CSRF). Vui lòng thử lại!';
                header("Location: index.php?route=tenants");
                exit;
            }

            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $fullName         = trim($_POST['full_name'] ?? '');
                $cccd             = trim($_POST['cccd'] ?? '');
                $phone            = trim($_POST['phone'] ?? '');
                $birthday         = trim($_POST['birthday'] ?? '');
                $gender           = trim($_POST['gender'] ?? 'Nam');
                $email            = trim($_POST['email'] ?? '');
                $occupation       = trim($_POST['occupation'] ?? '');
                $hometown         = trim($_POST['hometown'] ?? '');
                $emergencyContact = trim($_POST['emergency_contact'] ?? '');
                $status           = trim($_POST['status'] ?? 'Đang thuê');
                $roomId           = (int)($_POST['room_id'] ?? 0);
                $roommateName     = trim($_POST['roommate_name'] ?? '');
                $roommatePhone    = trim($_POST['roommate_phone'] ?? '');

                if ($fullName === '' || $phone === '') {
                    $_SESSION['tenant_flash_error'] = 'Vui lòng nhập đầy đủ Họ và tên và Số điện thoại khách thuê!';
                } else {
                    try {
                        $newTenantId = $this->model->createTenantFull(
                            $fullName,
                            $cccd,
                            $phone,
                            $birthday ?: null,
                            $gender,
                            $email,
                            $occupation,
                            $hometown,
                            $emergencyContact,
                            $status,
                            $roommateName ?: null,
                            $roommatePhone ?: null
                        );

                        if ($roomId > 0) {
                            $this->model->assignRoomToTenant($newTenantId, $roomId, $roommateName, $roommatePhone);
                            $_SESSION['tenant_flash_success'] = "Đã thêm khách thuê '{$fullName}' và gán vào phòng thành công! Tài khoản đăng nhập: {$phone} (Mật khẩu: 123)";
                        } else {
                            $_SESSION['tenant_flash_success'] = "Đã thêm khách thuê '{$fullName}' thành công! Tài khoản đăng nhập: {$phone} (Mật khẩu: 123)";
                        }
                    } catch (PDOException $e) {
                        $_SESSION['tenant_flash_error'] = 'Lỗi lưu thông tin: ' . $e->getMessage();
                    }
                }
                header("Location: index.php?route=tenants");
                exit;
            } elseif ($action === 'update') {
                $id               = (int)($_POST['id'] ?? 0);
                $fullName         = trim($_POST['full_name'] ?? '');
                $cccd             = trim($_POST['cccd'] ?? '');
                $phone            = trim($_POST['phone'] ?? '');
                $birthday         = trim($_POST['birthday'] ?? '');
                $gender           = trim($_POST['gender'] ?? 'Nam');
                $email            = trim($_POST['email'] ?? '');
                $occupation       = trim($_POST['occupation'] ?? '');
                $hometown         = trim($_POST['hometown'] ?? '');
                $emergencyContact = trim($_POST['emergency_contact'] ?? '');
                $status           = trim($_POST['status'] ?? 'Đang thuê');
                $roomId           = (int)($_POST['room_id'] ?? 0);
                $roommateName     = trim($_POST['roommate_name'] ?? '');
                $roommatePhone    = trim($_POST['roommate_phone'] ?? '');

                if ($id <= 0 || $fullName === '' || $phone === '') {
                    $_SESSION['tenant_flash_error'] = 'Vui lòng nhập đầy đủ thông tin khách thuê!';
                } else {
                    try {
                        $this->model->updateTenantFull(
                            $id,
                            $fullName,
                            $cccd,
                            $phone,
                            $birthday ?: null,
                            $gender,
                            $email,
                            $occupation,
                            $hometown,
                            $emergencyContact,
                            $status,
                            $roommateName ?: null,
                            $roommatePhone ?: null
                        );

                        if ($roomId > 0) {
                            $this->model->assignRoomToTenant($id, $roomId, $roommateName, $roommatePhone);
                        }

                        $_SESSION['tenant_flash_success'] = "Đã cập nhật thông tin khách thuê '{$fullName}' thành công! (Tài khoản: {$phone}, Mật khẩu: 123)";
                    } catch (PDOException $e) {
                        $_SESSION['tenant_flash_error'] = 'Không thể cập nhật: ' . $e->getMessage();
                    }
                }
                header("Location: index.php?route=tenants");
                exit;
            } elseif ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    try {
                        $this->model->deleteTenant($id);
                        $_SESSION['tenant_flash_success'] = 'Đã xóa khách thuê khỏi hệ thống!';
                    } catch (PDOException $e) {
                        $_SESSION['tenant_flash_error'] = 'Không thể xóa: Khách thuê này đang có hợp đồng thuê phòng trong CSDL!';
                    }
                }
                header("Location: index.php?route=tenants");
                exit;
            }
        }

        $flash_success = $_SESSION['tenant_flash_success'] ?? '';
        $flash_error   = $_SESSION['tenant_flash_error'] ?? '';
        unset($_SESSION['tenant_flash_success'], $_SESSION['tenant_flash_error']);

        $keyword    = trim($_GET['keyword'] ?? '');
        $stayStatus = trim($_GET['stay_status'] ?? '');
        $selectedRoomId = (int)($_GET['room_id'] ?? 0);

        $stats    = $this->model->getTenantStats();
        $tenants  = $this->model->getTenants($keyword, $stayStatus);
        $allRooms = $this->roomModel->getRooms();

        require_once __DIR__ . '/../views/tenants/index.php';
    }
}
