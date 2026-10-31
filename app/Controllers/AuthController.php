<?php
class AuthController {
    // Hàm hiển thị form đăng nhập
    public function showLoginForm() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $error = $_SESSION['flash_error'] ?? '';
        unset($_SESSION['flash_error']);
        require_once __DIR__ . '/../views/auth/login.php';
    }

    // Hàm xử lý đăng nhập
    public function login() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $_SESSION['flash_error'] = "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.";
            header("Location: index.php?route=auth/login");
            exit;
        }

        require_once __DIR__ . '/../Config/Database.php';
        
        try {
            // ĐÃ SỬA LỖI Ở ĐÂY: Khởi tạo đối tượng Database trước khi gọi getConnection()
            $database = new Database();
            $db = $database->getConnection();
            
            $stmt = $db->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();

            if ($user && ($password === $user['password'] || password_verify($password, $user['password']))) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];

                $_SESSION['flash_success'] = "Đăng nhập thành công!";
                header("Location: index.php?route=dashboard");
                exit;
            } else {
                $_SESSION['flash_error'] = "Tên đăng nhập hoặc mật khẩu không chính xác.";
                header("Location: index.php?route=auth/login");
                exit;
            }
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = "Lỗi hệ thống cơ sở dữ liệu.";
            header("Location: index.php?route=auth/login");
            exit;
        }
    }

    // Hàm xử lý đăng xuất
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_destroy();
        header("Location: index.php?route=auth/login");
        exit;
    }
}
?>