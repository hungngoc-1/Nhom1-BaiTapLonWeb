<?php
require_once __DIR__ . '/../Models/AccountModel.php';
require_once __DIR__ . '/../Models/ReportModel.php';

class AccountController {
    private AccountModel $accountModel;
    private ReportModel $reportModel;

    public function __construct() {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $this->accountModel = new AccountModel();
        $this->reportModel  = new ReportModel();
    }

    public function index(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Bắt buộc đăng nhập
        if (empty($_SESSION['username'])) {
            header("Location: index.php?route=auth/login");
            exit;
        }

        $userRole = $_SESSION['role'] ?? 'admin';
        $currentUserId = (int)($_SESSION['user_id'] ?? 0);

        // Khách thuê không có quyền truy cập trang quản lý tài khoản
        if ($userRole === 'tenant') {
            header("Location: index.php?route=showcase");
            exit;
        }

        // Xác định tòa nhà phụ trách của Quản lý
        $allowedBlds = $this->reportModel->getAllowedBuildingIds();
        $currentBuilding = reset($allowedBlds) ?: 'A';

        // Xử lý các thao tác POST (Thêm, Sửa, Đổi MK, Xóa)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $username = trim($_POST['username'] ?? '');
                $password = trim($_POST['password'] ?? '123456');
                $fullName = trim($_POST['full_name'] ?? '');
                $phone    = trim($_POST['phone'] ?? '');
                $email    = trim($_POST['email'] ?? '');
                $role     = trim($_POST['role'] ?? 'tenant');

                // Nếu là Quản lý, chỉ được phép tạo tài khoản khách thuê
                if ($userRole === 'landlord') {
                    $role = 'tenant';
                }

                if ($username === '' || $fullName === '' || $phone === '' || $email === '') {
                    $_SESSION['acc_flash_error'] = "Vui lòng điền đầy đủ Tên đăng nhập, Họ tên, SĐT và Email!";
                } elseif (strlen($password) < 4) {
                    $_SESSION['acc_flash_error'] = "Mật khẩu phải từ 4 ký tự trở lên!";
                } else {
                    try {
                        $this->accountModel->createAccount($username, $password, $fullName, $phone, $email, $role);
                        $roleName = ($role === 'landlord') ? 'Quản lý' : 'Khách thuê';
                        $this->reportModel->logActivity("Đã tạo tài khoản {$roleName}: '{$username}' ({$fullName})", 'success');
                        $_SESSION['acc_flash_success'] = "Đã thêm tài khoản {$roleName} '{$username}' thành công!";
                    } catch (Exception $e) {
                        $_SESSION['acc_flash_error'] = $e->getMessage();
                    }
                }
                header("Location: index.php?route=accounts");
                exit;

            } elseif ($action === 'update') {
                $id       = (int)($_POST['id'] ?? 0);
                $fullName = trim($_POST['full_name'] ?? '');
                $phone    = trim($_POST['phone'] ?? '');
                $email    = trim($_POST['email'] ?? '');
                $role     = trim($_POST['role'] ?? '');

                $target = $this->accountModel->getAccountById($id);
                if (!$target) {
                    $_SESSION['acc_flash_error'] = "Không tìm thấy tài khoản cần chỉnh sửa!";
                } elseif ($userRole === 'landlord' && $target['role'] !== 'tenant') {
                    $_SESSION['acc_flash_error'] = "Bạn chỉ có quyền quản lý tài khoản khách thuê!";
                } else {
                    try {
                        $newRole = ($userRole === 'admin' && in_array($role, ['admin', 'landlord', 'tenant'])) ? $role : null;
                        $this->accountModel->updateAccount($id, $fullName, $phone, $email, $newRole);
                        $this->reportModel->logActivity("Đã cập nhật thông tin tài khoản: '{$target['username']}'", 'info');
                        $_SESSION['acc_flash_success'] = "Đã cập nhật tài khoản '{$target['username']}' thành công!";
                    } catch (Exception $e) {
                        $_SESSION['acc_flash_error'] = $e->getMessage();
                    }
                }
                header("Location: index.php?route=accounts");
                exit;

            } elseif ($action === 'reset_password') {
                $id       = (int)($_POST['id'] ?? 0);
                $newPass  = trim($_POST['new_password'] ?? '');
                $target   = $this->accountModel->getAccountById($id);

                if (!$target) {
                    $_SESSION['acc_flash_error'] = "Không tìm thấy tài khoản!";
                } elseif ($userRole === 'landlord' && $target['role'] !== 'tenant') {
                    $_SESSION['acc_flash_error'] = "Bạn chỉ có quyền đổi mật khẩu cho tài khoản khách thuê!";
                } elseif (strlen($newPass) < 4) {
                    $_SESSION['acc_flash_error'] = "Mật khẩu mới phải từ 4 ký tự trở lên!";
                } else {
                    try {
                        $this->accountModel->resetPassword($id, $newPass);
                        $this->reportModel->logActivity("Đã đổi mật khẩu tài khoản: '{$target['username']}'", 'warning');
                        $_SESSION['acc_flash_success'] = "Đã đặt lại mật khẩu cho tài khoản '{$target['username']}' thành công!";
                    } catch (Exception $e) {
                        $_SESSION['acc_flash_error'] = $e->getMessage();
                    }
                }
                header("Location: index.php?route=accounts");
                exit;

            } elseif ($action === 'delete') {
                $id     = (int)($_POST['id'] ?? 0);
                $target = $this->accountModel->getAccountById($id);

                if (!$target) {
                    $_SESSION['acc_flash_error'] = "Không tìm thấy tài khoản cần xóa!";
                } elseif ($id === $currentUserId) {
                    $_SESSION['acc_flash_error'] = "Bạn không thể tự xóa tài khoản của chính mình!";
                } elseif ($target['role'] === 'admin') {
                    $_SESSION['acc_flash_error'] = "Không thể xóa tài khoản Quản trị viên tối cao!";
                } elseif ($userRole === 'landlord' && $target['role'] !== 'tenant') {
                    $_SESSION['acc_flash_error'] = "Bạn chỉ có quyền xóa tài khoản khách thuê!";
                } else {
                    try {
                        $this->accountModel->deleteAccount($id);
                        $this->reportModel->logActivity("Đã xóa tài khoản: '{$target['username']}'", 'danger');
                        $_SESSION['acc_flash_success'] = "Đã xóa tài khoản '{$target['username']}' khỏi hệ thống!";
                    } catch (Exception $e) {
                        $_SESSION['acc_flash_error'] = $e->getMessage();
                    }
                }
                header("Location: index.php?route=accounts");
                exit;
            }
        }

        // Lọc và Tìm kiếm (GET)
        $roleFilter = trim($_GET['role'] ?? '');
        $keyword    = trim($_GET['keyword'] ?? '');

        $accounts = $this->accountModel->getAccounts($roleFilter, $keyword, $userRole, $currentBuilding);
        $stats    = $this->accountModel->getAccountStats($userRole, $currentBuilding);

        $flashSuccess = $_SESSION['acc_flash_success'] ?? '';
        $flashError   = $_SESSION['acc_flash_error'] ?? '';
        unset($_SESSION['acc_flash_success'], $_SESSION['acc_flash_error']);

        $buildingsConfig = $this->reportModel->getBuildingsConfig();
        $buildingName = $buildingsConfig[$currentBuilding]['name'] ?? ('Tòa ' . $currentBuilding);

        require __DIR__ . '/../views/accounts/index.php';
    }
}
