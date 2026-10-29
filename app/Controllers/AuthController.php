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

        // 4. Truy vấn cơ sở dữ liệu bằng Prepared Statement (Bảo mật chống SQL Injection)
        require_once __DIR__ . '/../Models/Database.php';
        
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();

            // Kiểm tra tài khoản khớp với CSDL (seed.sql)
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

    public function logout() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_destroy();
        header("Location: index.php?route=auth/login");
        exit;
    }
}
?>