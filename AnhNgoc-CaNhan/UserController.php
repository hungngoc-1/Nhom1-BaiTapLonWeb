<?php
require_once 'UserModel.php';

class UserController {
    private $model;

    public function __construct() {
        $this->model = new UserModel();
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
                        $this->model->createUser($username, $password, $full_name, $role);
                        header("Location: UserController.php?msg=created"); exit;
                    } catch (Exception $e) { $message = "Lỗi: Tài khoản đã tồn tại!"; }
                } else { $message = "Vui lòng nhập đủ thông tin!"; }
            } 
            elseif ($action === 'update') {
                $id = $_POST['id'] ?? 0;
                $full_name = trim($_POST['full_name'] ?? '');
                $role = $_POST['role'] ?? 'tenant';
                if ($id > 0 && !empty($full_name)) {
                    $this->model->updateUser($id, $full_name, $role);
                    header("Location: UserController.php?msg=updated"); exit;
                }
            }
            elseif ($action === 'delete') {
                $id = $_POST['id'] ?? 0;
                if ($id > 0) $this->model->deleteUser($id);
                header("Location: UserController.php?msg=deleted"); exit;
            }
        }

        // Kiểm tra xem có đang bấm nút Sửa không
        $edit_user = null;
        if (isset($_GET['edit_id'])) {
            $edit_user = $this->model->getUserById($_GET['edit_id']);
        }

        // Xử lý Tìm kiếm
        $keyword = trim($_GET['keyword'] ?? '');
        $users = $this->model->getUsers($keyword);
        
        require_once 'index.php';
    }
}

$controller = new UserController();
$controller->index();
?>