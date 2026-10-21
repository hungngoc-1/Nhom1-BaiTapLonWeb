<?php
class AuthController {
    public function login() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // 1. Kiểm tra CSRF Token
        if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['flash_error'] = "Lỗi bảo mật: CSRF Token không hợp lệ.";
            header("Location: index.php?route=login");
            exit;
        }

        // 2. Lấy và chuẩn hóa dữ liệu
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Giữ lại dữ liệu hợp lệ khi có lỗi
        $_SESSION['old_username'] = $username;

        // 3. Validation phía Server
        if (empty($username) || empty($password)) {
            $_SESSION['flash_error'] = "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.";
            header("Location: index.php?route=login");
            exit;
        }

        if (strlen($username) < 3 || strlen($username) > 50) {
            $_SESSION['flash_error'] = "Tên đăng nhập phải từ 3 đến 50 ký tự.";
            header("Location: index.php?route=login");
            exit;
        }

        // 4. Nếu validation qua hết -> Xử lý đăng nhập (Giả lập thành công)
        unset($_SESSION['old_username']);
        $_SESSION['flash_success'] = "Đăng nhập thành công!";
        header("Location: index.php?route=dashboard");
        exit;
    }
}
?>