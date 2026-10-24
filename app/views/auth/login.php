<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Hệ thống quản lý phòng trọ</title>
    <!-- Font chữ Serif cho tiêu đề "Đăng nhập" và Sans-serif cho nội dung giống hệt ảnh -->
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            /* Gọi ảnh background.png nằm ở thư mục gốc Nhom1-BaiTapLonWeb */
            background: url('background.png') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: flex-end; /* Đẩy khung đăng nhập sang bên phải như ảnh mẫu */
            padding-right: 7%;
            font-family: 'Be Vietnam Pro', sans-serif;
        }

        /* Khung thẻ đăng nhập màu băng tuyết mờ */
        .login-card {
            width: 100%;
            max-width: 470px;
            background: rgba(226, 240, 249, 0.88); /* Sắc xanh băng trắng mờ giống ảnh */
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-radius: 26px;
            padding: 44px 42px 38px;
            box-shadow: 0 20px 50px rgba(21, 32, 38, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.65);
        }

        /* Icon ngôi nhà trên đỉnh */
        .home-logo {
            width: 58px;
            height: 58px;
            margin: 0 auto 6px;
            display: block;
        }

        /* Chữ "Đăng nhập" có chân đậm màu Deep Ocean */
        .login-title {
            font-family: 'Playfair Display', Georgia, serif;
            font-weight: 800;
            font-size: 2.5rem;
            color: #1B3D59;
            margin-bottom: 6px;
            letter-spacing: -0.5px;
        }

        .login-subtitle {
            color: #2c4c66;
            font-size: 0.95rem;
            margin-bottom: 28px;
        }

        /* Ô nhập Tên đăng nhập & Mật khẩu */
        .input-box {
            position: relative;
            margin-bottom: 18px;
        }

        .input-box input {
            width: 100%;
            background-color: rgba(179, 213, 241, 0.45); /* Màu Melting Ice (#B3D5F1) pha trong suốt */
            border: 1px solid rgba(106, 151, 192, 0.25);
            border-radius: 14px;
            padding: 15px 46px 15px 48px;
            font-size: 0.96rem;
            color: #152026;
            outline: none;
            transition: all 0.2s ease;
        }

        .input-box input::placeholder {
            color: #6A97C0; /* Màu Windstorm */
            font-weight: 400;
        }

        .input-box input:focus {
            background-color: rgba(212, 238, 248, 0.85);
            border-color: #1B3D59;
            box-shadow: 0 0 0 3px rgba(27, 61, 89, 0.12);
        }

        /* Icon bên trái ô input */
        .input-box .left-icon {
            position: absolute;
            left: 17px;
            top: 50%;
            transform: translateY(-50%);
            color: #1B3D59;
            font-size: 1.25rem;
            pointer-events: none;
        }

        /* Icon con mắt bên phải ô mật khẩu */
        .input-box .eye-icon {
            position: absolute;
            right: 17px;
            top: 50%;
            transform: translateY(-50%);
            color: #1B3D59;
            font-size: 1.2rem;
            cursor: pointer;
            opacity: 0.85;
        }

        .input-box .eye-icon:hover {
            opacity: 1;
        }

        /* Hàng Ghi nhớ đăng nhập & Quên mật khẩu */
        .options-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 4px;
            margin-bottom: 24px;
            font-size: 0.88rem;
        }

        .custom-check {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #1B3D59;
            cursor: pointer;
            user-select: none;
        }

        .custom-check input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #1B3D59;
            border-radius: 4px;
            cursor: pointer;
        }

        .forgot-link {
            color: #4a80b0;
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-link:hover {
            color: #1B3D59;
            text-decoration: underline;
        }

        /* Nút Đăng nhập màu Deep Ocean (#1B3D59) */
        .btn-login {
            width: 100%;
            background-color: #1B3D59;
            color: #ffffff;
            border: none;
            border-radius: 14px;
            padding: 15px;
            font-size: 1.15rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: background-color 0.25s ease, transform 0.15s ease;
        }

        .btn-login:hover {
            background-color: #152026; /* Màu Black Pine */
            transform: translateY(-1px);
        }

        /* Đường kẻ ngang có chữ "Hoặc" ở giữa */
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 26px 0 22px;
            color: #6A97C0;
            font-size: 0.88rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid rgba(106, 151, 192, 0.35);
        }

        .divider span {
            padding: 0 14px;
        }

        /* Dòng chữ Hệ thống quản lý phòng trọ ở dưới cùng */
        .footer-brand {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            color: #1B3D59;
            text-decoration: none;
            font-size: 0.92rem;
            font-weight: 500;
        }

        .footer-brand:hover {
            color: #152026;
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            body {
                justify-content: center;
                padding: 16px;
            }
            .login-card {
                padding: 32px 24px;
            }
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="text-center">
            <!-- Biểu tượng ngôi nhà có ống khói và 4 ô cửa sổ giống hệt ảnh mẫu -->
            <svg class="home-logo" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M8 30L32 10L56 30" stroke="#1B3D59" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M44 14V20L50 25V14H44Z" fill="#1B3D59"/>
                <path d="M14 27V52C14 53.1046 14.8954 54 16 54H48C49.1046 54 50 53.1046 50 52V27" stroke="#1B3D59" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"/>
                <!-- 4 ô cửa sổ ở giữa ngôi nhà -->
                <rect x="25" y="31" width="6" height="6" rx="1" fill="#1B3D59"/>
                <rect x="33" y="31" width="6" height="6" rx="1" fill="#1B3D59"/>
                <rect x="25" y="39" width="6" height="6" rx="1" fill="#1B3D59"/>
                <rect x="33" y="39" width="6" height="6" rx="1" fill="#1B3D59"/>
            </svg>

            <h1 class="login-title">Đăng nhập</h1>
            <p class="login-subtitle">Vui lòng đăng nhập để tiếp tục</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small rounded-3 mb-3">
                <i class="bi bi-exclamation-circle me-1"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?route=auth/login">
            <!-- Giữ nguyên CSRF token ngầm bên dưới để đúng chuẩn bảo mật của môn học -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

            <!-- Ô Tên đăng nhập -->
            <div class="input-box">
                <i class="bi bi-person left-icon"></i>
                <input type="text" name="username" placeholder="Tên đăng nhập" required>
            </div>

            <!-- Ô Mật khẩu có con mắt bật/tắt -->
            <div class="input-box">
                <i class="bi bi-lock left-icon"></i>
                <input type="password" name="password" id="passwordInput" placeholder="Mật khẩu" required>
                <i class="bi bi-eye eye-icon" id="togglePassword"></i>
            </div>

            <!-- Ghi nhớ đăng nhập & Quên mật khẩu -->
            <div class="options-row">
                <label class="custom-check">
                    <input type="checkbox" name="remember" checked>
                    <span>Ghi nhớ đăng nhập</span>
                </label>
                <a href="#" class="forgot-link">Quên mật khẩu?</a>
            </div>

            <!-- Nút Đăng nhập -->
            <button type="submit" class="btn-login">
                <span>Đăng nhập</span>
                <i class="bi bi-arrow-right"></i>
            </button>

            <!-- Đường kẻ chữ Hoặc -->
            <div class="divider">
                <span>Hoặc</span>
            </div>

            <!-- Dòng cuối cùng: Hệ thống quản lý phòng trọ (bấm vào sẽ sang Dashboard) -->
            <div class="text-center">
                <a href="?route=dashboard" class="footer-brand">
                    <i class="bi bi-house-door"></i>
                    <span>Hệ thống quản lý phòng trọ</span>
                </a>
            </div>
        </form>
    </div>

    <script>
        // Chức năng bấm vào con mắt để xem/ẩn mật khẩu
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('passwordInput');

        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });
    </script>
</body>
</html>