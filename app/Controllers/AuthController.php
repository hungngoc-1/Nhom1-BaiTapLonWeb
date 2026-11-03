<?php
require_once __DIR__ . '/../Config/Database.php';

class AuthController {
    // Hàm hiển thị form đăng nhập
    public function showLoginForm() {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        $error = $_SESSION['flash_error'] ?? '';
        $success = $_SESSION['flash_success'] ?? '';
        $old_username = $_SESSION['old_username'] ?? '';
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        // Lấy danh sách tài khoản từ SQL mới (database/seed.sql) để hỗ trợ tra cứu / đăng nhập thử
        $demoUsers = [];
        try {
            $db = Database::getConnection();
            $stmt = $db->query("SELECT id, username, password, full_name, phone, email, role FROM users ORDER BY id ASC");
            $demoUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $demoUsers = [];
        }

        require_once __DIR__ . '/../views/auth/login.php';
    }

    // Hàm xử lý đăng nhập & khôi phục mật khẩu
    public function login() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?route=auth/login");
            exit;
        }

        // Kiểm tra CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!empty($_SESSION['csrf_token']) && !empty($csrfToken) && !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
            $_SESSION['flash_error'] = "Phiên làm việc không hợp lệ, vui lòng thử lại.";
            header("Location: index.php?route=auth/login");
            exit;
        }

        $action = $_POST['action'] ?? 'login';

        // Xử lý chức năng Quên mật khẩu / Đặt lại mật khẩu trực tiếp trên SQL
        if ($action === 'forgot_password') {
            $identifier = trim($_POST['identifier'] ?? '');
            $newPassword = $_POST['new_password'] ?? '';

            if ($identifier === '' || $newPassword === '') {
                $_SESSION['flash_error'] = "Vui lòng nhập tên đăng nhập/email/SĐT và mật khẩu mới.";
                header("Location: index.php?route=auth/login");
                exit;
            }

            try {
                $db = Database::getConnection();
                $stmt = $db->prepare("SELECT * FROM users WHERE username = :id1 OR email = :id2 OR phone = :id3 LIMIT 1");
                $stmt->execute(['id1' => $identifier, 'id2' => $identifier, 'id3' => $identifier]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    $_SESSION['flash_error'] = "Không tìm thấy tài khoản khớp với thông tin: " . $identifier;
                    header("Location: index.php?route=auth/login");
                    exit;
                }

                $upd = $db->prepare("UPDATE users SET password = :pwd WHERE id = :id");
                $upd->execute(['pwd' => $newPassword, 'id' => $user['id']]);

                $_SESSION['old_username'] = $user['username'];
                $_SESSION['flash_success'] = "Đã đặt lại mật khẩu cho tài khoản '{$user['username']}' thành công! Bạn có thể đăng nhập ngay.";
                header("Location: index.php?route=auth/login");
                exit;
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = "Lỗi hệ thống cơ sở dữ liệu.";
                header("Location: index.php?route=auth/login");
                exit;
            }
        }

        // Xử lý Đăng nhập chính
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $_SESSION['old_username'] = $username;

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

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM users WHERE username = :u1 OR email = :u2 OR phone = :u3 LIMIT 1");
            $stmt->execute(['u1' => $username, 'u2' => $username, 'u3' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Kiểm tra tài khoản từ bảng users trong SQL mới (hỗ trợ password trong seed.sql, admin01, 123456 hoặc bcrypt)
            $isValid = false;
            if ($user) {
                if (
                    $password === $user['password'] ||
                    password_verify($password, $user['password']) ||
                    $password === 'admin01' ||
                    $password === '123456'
                ) {
                    $isValid = true;
                }
            } elseif ($username === 'admin01' && ($password === 'admin01' || $password === 'hashed_pass_1' || $password === '123456')) {
                $user = [
                    'id' => 1,
                    'username' => 'admin01',
                    'full_name' => 'Quản Trị Viên',
                    'phone' => '0901234567',
                    'email' => 'admin@nhatro.com',
                    'role' => 'admin'
                ];
                $isValid = true;
            }

            if ($isValid && $user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['phone'] = $user['phone'] ?? '';
                $_SESSION['email'] = $user['email'] ?? '';
                $_SESSION['role'] = $user['role'];
                unset($_SESSION['old_username']);

                $_SESSION['flash_success'] = "Đăng nhập thành công! Xin chào " . $user['full_name'] . ".";
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
