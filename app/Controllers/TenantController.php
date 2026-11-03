<?php
require_once __DIR__ . '/../Models/TenantModel.php';

class TenantController {
    private TenantModel $model;

    public function __construct() {
        $this->model = new TenantModel();
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
                $username = trim($_POST['username'] ?? '');
                $password = trim($_POST['password'] ?? '123456');
                $fullName = trim($_POST['full_name'] ?? '');
                $phone    = trim($_POST['phone'] ?? '');
                $email    = trim($_POST['email'] ?? '');

                if ($username === '' || $fullName === '' || $phone === '' || $email === '') {
                    $_SESSION['tenant_flash_error'] = 'Vui lòng nhập đầy đủ Họ tên, Tài khoản, SĐT và Email!';
                } else {
                    try {
                        $this->model->createTenant($username, $password !== '' ? $password : '123456', $fullName, $phone, $email);
                        $_SESSION['tenant_flash_success'] = "Đã thêm khách thuê '{$fullName}' vào hệ thống!";
                    } catch (PDOException $e) {
                        $_SESSION['tenant_flash_error'] = 'Không thể thêm: Tên đăng nhập hoặc Email đã tồn tại trong CSDL!';
                    }
                }
                header("Location: index.php?route=tenants");
                exit;
            } elseif ($action === 'update') {
                $id       = (int)($_POST['id'] ?? 0);
                $fullName = trim($_POST['full_name'] ?? '');
                $phone    = trim($_POST['phone'] ?? '');
                $email    = trim($_POST['email'] ?? '');

                if ($id <= 0 || $fullName === '' || $phone === '' || $email === '') {
                    $_SESSION['tenant_flash_error'] = 'Vui lòng nhập đầy đủ thông tin khách thuê!';
                } else {
                    try {
                        $this->model->updateTenant($id, $fullName, $phone, $email);
                        $_SESSION['tenant_flash_success'] = "Đã cập nhật thông tin khách thuê '{$fullName}' thành công!";
                    } catch (PDOException $e) {
                        $_SESSION['tenant_flash_error'] = 'Không thể cập nhật: Email đã bị trùng với tài khoản khác!';
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

        $stats   = $this->model->getTenantStats();
        $tenants = $this->model->getTenants($keyword, $stayStatus);

        require_once __DIR__ . '/../views/tenants/index.php';
    }
}
?>
