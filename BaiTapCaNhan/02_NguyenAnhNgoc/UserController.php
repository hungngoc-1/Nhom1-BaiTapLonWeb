<?php
require_once __DIR__ . '/UserRepository.php';

class UserController {
    private $repository;

    public function __construct() {
        $this->repository = new UserRepository();
    }

    public function index() {
        $message = '';
        
        // Xử lý Thêm, Sửa, Xóa (POST)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $username = trim($_POST['username'] ?? '');
                $password = trim($_POST['password'] ?? '');
                $full_name = trim($_POST['full_name'] ?? '');
                $role = $_POST['role'] ?? 'tenant';

                if (!empty($username) && !empty($password) && !empty($full_name)) {
                    try {
                        $this->repository->createUser($username, $password, $full_name, $role);
                        header("Location: UserController.php?msg=created"); exit;
                    } catch (Exception $e) { $message = "Lỗi: Tài khoản đã tồn tại!"; }
                } else { $message = "Vui lòng nhập đủ thông tin!"; }
            } 
            elseif ($action === 'update') {
                $id = $_POST['id'] ?? 0;
                $full_name = trim($_POST['full_name'] ?? '');
                $role = $_POST['role'] ?? 'tenant';
                if ($id > 0 && !empty($full_name)) {
                    $this->repository->updateUser($id, $full_name, $role);
                    header("Location: UserController.php?msg=updated"); exit;
                }
            }
            elseif ($action === 'delete') {
                $id = $_POST['id'] ?? 0;
                if ($id > 0) $this->repository->deleteUser($id);
                header("Location: UserController.php?msg=deleted"); exit;
            }
        }

        // Lấy thông tin User nếu đang bấm nút Sửa
        $edit_user = null;
        if (isset($_GET['edit_id'])) {
            $edit_user = $this->repository->getUserById($_GET['edit_id']);
        }

        // Lấy danh sách (có tìm kiếm)
        $keyword = trim($_GET['keyword'] ?? '');
        $users = $this->repository->getUsers($keyword);
        
        // Gọi file View
        require_once __DIR__ . '/views/index.php';
    }
}

// Chạy trực tiếp file này
$controller = new UserController();
$controller->index();
?>