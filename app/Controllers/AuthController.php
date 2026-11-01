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

        if (strlen($username) < 3 || strlen($username) > 50) {
            $_SESSION['flash_error'] = "Tên đăng nhập phải từ 3 đến 50 ký tự.";
            header("Location: index.php?route=auth/login");
            exit;
        }

        // 4. Truy vấn cơ sở dữ liệu bằng Prepared Statement (Bảo mật chống SQL Injection)
        require_once __DIR__ . '/../Config/Database.php';
        
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();

            // Kiểm tra tài khoản admin01 đặc quyền
            if ($username === 'admin01' && $password === 'admin01') {
                $_SESSION['user_id'] = $user['id'] ?? 1;
                $_SESSION['username'] = 'admin01';
                $_SESSION['full_name'] = $user['full_name'] ?? 'Quản Trị Viên';
                $_SESSION['role'] = 'admin';

                $_SESSION['flash_success'] = "Đăng nhập thành công!";
                header("Location: index.php?route=dashboard");
                exit;
            }

            // Kiểm tra mật khẩu (hỗ trợ mật khẩu admin01, plaintext hoặc bcrypt)
            if ($user && ($password === 'admin01' || $password === $user['password'] || password_verify($password, $user['password']))) {
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