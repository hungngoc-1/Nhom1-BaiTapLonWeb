<?php
class AuthController {
    // Hàm hiển thị giao diện đăng nhập 2 cột StayMate
    public function showLoginForm() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        // Tạo CSRF Token nếu chưa có
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Lấy thông báo lỗi nếu có
        $error = $_SESSION['flash_error'] ?? '';
        unset($_SESSION['flash_error']);

        // Gọi đúng đường dẫn file view
        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function login() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // 1. Kiểm tra CSRF Token
        if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['flash_error'] = "Lỗi bảo mật: CSRF Token không hợp lệ.";
            header("Location: index.php?route=auth/login");
            exit;
        }

        // 2. Lấy và chuẩn hóa dữ liệu
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // 3. Validation phía Server
        if (empty($username) || empty($password)) {
            $_SESSION['flash_error'] = "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.";
            header("Location: index.php?route=auth/login");
            exit;
        }

        if (strlen($username) < 3 || strlen($username) > 50) {
            $_SESSION['flash_error'] = "Tên đăng nhập phải từ 3 đến 50 ký tự.";
            header("Location: index.php?route=auth/login");
            exit;
        }

        // 4. Nếu hợp lệ -> Đăng nhập thành công, chuyển hướng về dashboard
        $_SESSION['flash_success'] = "Đăng nhập thành công!";
        header("Location: index.php?route=dashboard");
        exit;
    }
}
?>