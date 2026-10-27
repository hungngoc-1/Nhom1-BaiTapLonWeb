<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - StayMate Hệ thống quản lý phòng trọ</title>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&family=Caveat:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --teal-dark: #287A74;
            --teal-darker: #18524E;
            --teal-mid: #4FA69F;
            --mint-light: #ACECD5;
            --yellow-pastel: #FDF8B8;
            --text-dark: #164246;
            --text-muted: #5C8387;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            font-family: 'Be Vietnam Pro', sans-serif;
            background-color: #F4F9F9;
            overflow-x: hidden;
            display: flex;
        }

        .login-wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* ================= CỘT TRÁI: BANNER STAYMATE ================= */
        .left-panel {
            width: 53%;
            position: relative;
            background: linear-gradient(155deg, #2A7F79 0%, #287A74 45%, #1D5E59 100%);
            color: #ffffff;
            padding: 52px 64px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            /* Nếu Huệ có cắt riêng ảnh bên trái thành background.png thì có thể bật dòng dưới: */
            /* background: url('background.png') no-repeat center center / cover; */
        }

        /* Mảng trang trí góc trên trái */
        .left-top-decor {
            position: absolute;
            top: -45px;
            left: -45px;
            width: 130px;
            height: 130px;
            background-color: rgba(172, 236, 213, 0.75);
            border-radius: 50%;
            pointer-events: none;
        }

        /* Nội dung chữ bên trái */
        .left-content {
            position: relative;
            z-index: 3;
            max-width: 470px;
        }

        /* Cụm Logo StayMate */
        .brand-box {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 36px;
        }

        .brand-icon {
            width: 56px;
            height: 56px;
            flex-shrink: 0;
        }

        .brand-name {
            font-size: 2.15rem;
            font-weight: 800;
            line-height: 1.05;
            letter-spacing: -0.5px;
            color: #ffffff;
        }

        .brand-name span {
            color: #98D8D1;
        }

        .brand-sub {
            font-size: 0.92rem;
            color: rgba(255, 255, 255, 0.9);
            margin-top: 4px;
            font-weight: 400;
        }

        /* Tiêu đề lớn */
        .hero-heading {
            font-size: 2.35rem;
            font-weight: 800;
            line-height: 1.25;
            margin-bottom: 16px;
            letter-spacing: -0.5px;
        }

        .hero-heading span {
            color: #A8E4DD;
        }

        .hero-desc {
            font-size: 0.92rem;
            line-height: 1.65;
            color: rgba(255, 255, 255, 0.88);
            margin-bottom: 30px;
        }

        /* Danh sách 4 tính năng */
        .feature-list {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .feature-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(172, 236, 213, 0.25);
            border: 1px solid rgba(172, 236, 213, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #ffffff;
            flex-shrink: 0;
        }

        .feature-info h4 {
            font-size: 0.96rem;
            font-weight: 700;
            margin: 0 0 2px;
            color: #ffffff;
        }

        .feature-info p {
            font-size: 0.8rem;
            margin: 0;
            color: rgba(255, 255, 255, 0.82);
        }

        /* Chữ ký viết tay bên dưới */
        .handwritten-quote {
            position: relative;
            z-index: 3;
            margin-top: 28px;
            margin-left: 24px;
            font-family: 'Caveat', cursive;
            font-size: 1.55rem;
            line-height: 1.2;
            color: rgba(255, 255, 255, 0.94);
            transform: rotate(-8deg);
            transform-origin: left center;
            width: fit-content;
        }

        .handwritten-quote .quote-line2 {
            display: block;
            margin-left: 26px;
        }

        .quote-underline {
            display: block;
            width: 90px;
            height: 3px;
            background: #FDF8B8;
            border-radius: 4px;
            margin-top: 4px;
            margin-left: 65px;
        }

        /* Minh họa tòa nhà & đồi góc dưới bên trái */
        .building-art {
            position: absolute;
            right: -10px;
            bottom: 0;
            width: 62%;
            max-width: 490px;
            z-index: 2;
            pointer-events: none;
        }

        .bottom-hills {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            z-index: 1;
            pointer-events: none;
        }

        /* ================= CỘT PHẢI: KHUNG ĐĂNG NHẬP ================= */
        .right-panel {
            width: 47%;
            position: relative;
            background-color: #F3F8F8;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 36px;
            overflow: hidden;
        }

        /* Hoạ tiết trang trí góc trên phải & dưới phải */
        .decor-top-right-yellow {
            position: absolute;
            top: -55px;
            right: 55px;
            width: 170px;
            height: 170px;
            border-radius: 50%;
            background-color: #FDF4B4;
            z-index: 0;
        }

        .decor-top-right-mint {
            position: absolute;
            top: -45px;
            right: -45px;
            width: 190px;
            height: 190px;
            border-radius: 50%;
            background-color: #AEE5DC;
            z-index: 0;
        }

        .decor-dots {
            position: absolute;
            top: 22px;
            right: 26px;
            display: grid;
            grid-template-columns: repeat(3, 5px);
            gap: 11px;
            z-index: 1;
        }

        .decor-dots span {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background-color: #55A39D;
            opacity: 0.65;
        }

        .decor-bottom-right-yellow {
            position: absolute;
            bottom: -75px;
            right: -65px;
            width: 240px;
            height: 240px;
            border-radius: 50%;
            background-color: #FDF3AE;
            z-index: 0;
        }

        /* Thẻ Card Đăng nhập màu trắng */
        .login-card {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 470px;
            background: #ffffff;
            border-radius: 26px;
            padding: 46px 46px 34px;
            box-shadow: 0 20px 55px rgba(24, 78, 74, 0.08);
        }

        /* Logo trong Card */
        .card-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 28px;
        }

        .card-brand svg {
            width: 48px;
            height: 48px;
            flex-shrink: 0;
        }

        .card-brand-title {
            font-size: 1.7rem;
            font-weight: 800;
            line-height: 1.05;
            color: #164B51;
            letter-spacing: -0.4px;
        }

        .card-brand-title span {
            color: #4FA69F;
        }

        .card-brand-sub {
            font-size: 0.8rem;
            font-weight: 500;
            color: #2B5E63;
            margin-top: 3px;
        }

        /* Tiêu đề Đăng nhập */
        .login-title {
            font-size: 1.85rem;
            font-weight: 800;
            color: #15454B;
            margin-bottom: 6px;
            letter-spacing: -0.4px;
        }

        .login-subtitle {
            color: #5E878B;
            font-size: 0.88rem;
            margin-bottom: 26px;
        }

        /* Nhãn & Ô Input */
        .field-group {
            margin-bottom: 18px;
        }

        .field-label {
            display: block;
            font-size: 0.88rem;
            font-weight: 600;
            color: #184A50;
            margin-bottom: 8px;
        }

        .input-box {
            position: relative;
            display: flex;
            align-items: center;
            background-color: #FAFDFD;
            border: 1.5px solid #DFECEC;
            border-radius: 12px;
            transition: all 0.2s ease;
        }

        .input-box:focus-within {
            background-color: #ffffff;
            border-color: #4FA69F;
            box-shadow: 0 0 0 3.5px rgba(79, 166, 159, 0.14);
        }

        .input-icon-wrap {
            width: 46px;
            height: 46px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1C5258;
            font-size: 1.15rem;
            flex-shrink: 0;
            background: rgba(235, 245, 245, 0.65);
            border-radius: 10px 0 0 10px;
        }

        .input-box input {
            width: 100%;
            border: none;
            background: transparent;
            padding: 12px 42px 12px 14px;
            font-size: 0.9rem;
            color: #164246;
            outline: none;
        }

        .input-box input::placeholder {
            color: #8CAEB2;
            font-weight: 400;
        }

        .eye-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #36696E;
            font-size: 1.12rem;
            cursor: pointer;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .eye-icon:hover {
            opacity: 1;
        }

        /* Hàng Ghi nhớ đăng nhập & Quên mật khẩu */
        .options-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 4px;
            margin-bottom: 24px;
            font-size: 0.84rem;
        }

        .custom-check {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #215156;
            font-weight: 500;
            cursor: pointer;
            user-select: none;
        }

        .custom-check input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #287A74;
            border-radius: 5px;
            cursor: pointer;
        }

        .forgot-link {
            color: #287A74;
            text-decoration: none;
            font-weight: 600;
        }

        .forgot-link:hover {
            color: #18524E;
            text-decoration: underline;
        }

        /* Nút Đăng nhập */
        .btn-login {
            width: 100%;
            background: linear-gradient(90deg, #32857F 0%, #206761 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 13.5px;
            font-size: 0.98rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 8px 20px rgba(40, 122, 116, 0.25);
            transition: all 0.25s ease;
        }

        .btn-login:hover {
            background: linear-gradient(90deg, #287A74 0%, #17504B 100%);
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(40, 122, 116, 0.33);
        }

        /* Dòng bản quyền dưới cùng Card */
        .card-footer-text {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ECF3F3;
            text-align: center;
            font-size: 0.78rem;
            color: #699196;
        }

        /* Responsive trên điện thoại / máy tính bảng */
        @media (max-width: 992px) {
            .left-panel {
                display: none;
            }
            .right-panel {
                width: 100%;
                padding: 20px;
            }
            .login-card {
                padding: 34px 26px 28px;
            }
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <!-- ================= CỘT TRÁI (BANNER GIỚI THIỆU) ================= -->
        <div class="left-panel">
            <div class="left-top-decor"></div>

            <div class="left-content">
                <!-- Logo StayMate -->
                <div class="brand-box">
                    <svg class="brand-icon" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 28L32 12L52 28V50C52 52.2091 50.2091 54 48 54H16C13.7909 54 12 52.2091 12 50V28Z" fill="#236B65"/>
                        <path d="M10 29L32 11L54 29" stroke="#FDF8B8" stroke-width="5.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M13 27V50C13 52.2091 14.7909 54 17 54H47C49.2091 54 51 52.2091 51 50V27" stroke="#ACECD5" stroke-width="5" stroke-linecap="round"/>
                        <rect x="25" y="30" width="5.5" height="5.5" rx="1" fill="#FDF8B8"/>
                        <rect x="33.5" y="30" width="5.5" height="5.5" rx="1" fill="#FDF8B8"/>
                        <rect x="25" y="38.5" width="5.5" height="5.5" rx="1" fill="#FDF8B8"/>
                        <rect x="33.5" y="38.5" width="5.5" height="5.5" rx="1" fill="#FDF8B8"/>
                    </svg>
                    <div>
                        <div class="brand-name">Stay<span>Mate</span></div>
                        <div class="brand-sub">Hệ thống quản lý phòng trọ</div>
                    </div>
                </div>

                <!-- Tiêu đề lớn -->
                <h2 class="hero-heading">
                    Quản lý phòng trọ<br>
                    <span>dễ dàng, hiệu quả</span>
                </h2>
                <p class="hero-desc">
                    Giải pháp quản lý phòng trọ hiện đại, giúp bạn quản lý thông tin phòng, khách thuê, hợp đồng, hoá đơn và thanh toán một cách nhanh chóng, chính xác và tiện lợi.
                </p>

                <!-- 4 mục tính năng -->
                <div class="feature-list">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-house-door-fill"></i>
                        </div>
                        <div class="feature-info">
                            <h4>Quản lý phòng trọ</h4>
                            <p>Theo dõi tình trạng phòng, công suất thuê</p>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-file-earmark-text-fill"></i>
                        </div>
                        <div class="feature-info">
                            <h4>Hợp đồng & khách thuê</h4>
                            <p>Lưu trữ, tra cứu dễ dàng</p>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-receipt"></i>
                        </div>
                        <div class="feature-info">
                            <h4>Hóa đơn & thanh toán</h4>
                            <p>Điện, nước, internet, chi phí phát sinh</p>
                        </div>
                    </div>

                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="bi bi-bar-chart-fill"></i>
                        </div>
                        <div class="feature-info">
                            <h4>Báo cáo thống kê</h4>
                            <p>Doanh thu, công nợ, hiệu suất phòng</p>
                        </div>
                    </div>
                </div>

                <!-- Chữ viết tay -->
                <div class="handwritten-quote">
                    <span>An tâm quản lý</span>
                    <span class="quote-line2">- Vững vàng kinh doanh</span>
                    <span class="quote-underline"></span>
                </div>
            </div>

            <!-- Đồi & lá trang trí góc dưới trái -->
            <svg class="bottom-hills" viewBox="0 0 800 220" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 110C140 120 260 175 370 220H0V110Z" fill="#FDF8B8" fill-opacity="0.9"/>
                <path d="M0 140C110 145 210 185 280 220H0V140Z" fill="#ACECD5"/>
                <path d="M18 220C30 185 65 155 95 145C75 175 60 198 48 220H18Z" fill="#18524E"/>
                <path d="M5 220C10 175 35 135 62 120C50 155 40 190 32 220H5Z" fill="#287A74"/>
            </svg>

            <!-- Hình minh họa tòa nhà StayMate bên phải cột xanh -->
            <svg class="building-art" viewBox="0 0 420 460" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Nền tòa nhà 3 tầng -->
                <path d="M120 120L380 40V430H120V120Z" fill="#E9F6F4"/>
                <path d="M120 120L85 135V430H120V120Z" fill="#C4E3DF"/>
                <!-- Các tầng ban công -->
                <rect x="120" y="145" width="260" height="10" fill="#4FA69F"/>
                <rect x="120" y="245" width="260" height="10" fill="#4FA69F"/>
                <rect x="120" y="335" width="260" height="10" fill="#287A74"/>
                <!-- Cửa sổ các tầng -->
                <rect x="155" y="168" width="55" height="65" rx="4" fill="#3B8D88"/>
                <rect x="255" y="160" width="65" height="72" rx="4" fill="#3B8D88"/>
                <rect x="155" y="265" width="55" height="62" rx="4" fill="#3B8D88"/>
                <rect x="255" y="260" width="65" height="66" rx="4" fill="#3B8D88"/>
                <!-- Lan can -->
                <line x1="120" y1="220" x2="380" y2="210" stroke="#287A74" stroke-width="3"/>
                <line x1="120" y1="312" x2="380" y2="306" stroke="#287A74" stroke-width="3"/>
                <!-- Biển hiệu StayMate tầng 1 -->
                <rect x="140" y="322" width="125" height="36" rx="4" fill="#287A74"/>
                <text x="155" y="346" fill="#FFFFFF" font-family="Be Vietnam Pro, sans-serif" font-weight="700" font-size="18">StayMate</text>
                <!-- Cổng vào -->
                <rect x="165" y="362" width="90" height="68" fill="#1D5954"/>
                <!-- Cây xanh trang trí -->
                <circle cx="95" cy="370" r="35" fill="#4FA69F"/>
                <circle cx="75" cy="390" r="28" fill="#287A74"/>
                <circle cx="365" cy="355" r="42" fill="#287A74"/>
                <circle cx="345" cy="385" r="30" fill="#4FA69F"/>
            </svg>
        </div>

        <!-- ================= CỘT PHẢI (FORM ĐĂNG NHẬP) ================= -->
        <div class="right-panel">
            <!-- Các khối tròn trang trí góc phải -->
            <div class="decor-top-right-yellow"></div>
            <div class="decor-top-right-mint"></div>
            <div class="decor-dots">
                <span></span><span></span><span></span>
                <span></span><span></span><span></span>
                <span></span><span></span><span></span>
            </div>
            <div class="decor-bottom-right-yellow"></div>

            <!-- Thẻ Đăng nhập -->
            <div class="login-card">
                <!-- Logo StayMate nhỏ trong thẻ -->
                <div class="card-brand">
                    <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 29L32 11L54 29" stroke="#287A74" stroke-width="5.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M14 26V50C14 52.2091 15.7909 54 18 54H46C48.2091 54 50 52.2091 50 50V26" stroke="#4FA69F" stroke-width="5" stroke-linecap="round"/>
                        <rect x="25" y="30" width="5.5" height="5.5" rx="1" fill="#E3C567"/>
                        <rect x="33.5" y="30" width="5.5" height="5.5" rx="1" fill="#E3C567"/>
                        <rect x="25" y="38.5" width="5.5" height="5.5" rx="1" fill="#E3C567"/>
                        <rect x="33.5" y="38.5" width="5.5" height="5.5" rx="1" fill="#E3C567"/>
                    </svg>
                    <div>
                        <div class="card-brand-title">Stay<span>Mate</span></div>
                        <div class="card-brand-sub">Hệ thống quản lý phòng trọ</div>
                    </div>
                </div>

                <h1 class="login-title">Đăng nhập</h1>
                <p class="login-subtitle">Vui lòng đăng nhập để tiếp tục sử dụng hệ thống.</p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small rounded-3 mb-3">
                        <i class="bi bi-exclamation-circle me-1"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php?route=auth/login">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                    <div class="field-group">
                        <label class="field-label">Tên đăng nhập</label>
                        <div class="input-box">
                            <span class="input-icon-wrap">
                                <i class="bi bi-person"></i>
                            </span>
                            <input type="text" name="username" placeholder="Nhập tên đăng nhập hoặc email" required>
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Mật khẩu</label>
                        <div class="input-box">
                            <span class="input-icon-wrap">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" name="password" id="passwordInput" placeholder="Nhập mật khẩu" required>
                            <i class="bi bi-eye-slash eye-icon" id="togglePassword"></i>
                        </div>
                    </div>

                    <div class="options-row">
                        <label class="custom-check">
                            <input type="checkbox" name="remember" checked>
                            <span>Ghi nhớ đăng nhập</span>
                        </label>
                        <a href="#" class="forgot-link">Quên mật khẩu?</a>
                    </div>

                    <button type="submit" class="btn-login">
                        <span>Đăng nhập</span>
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </form>

                <div class="card-footer-text">
                    © 2025 StayMate. Hệ thống quản lý phòng trọ.
                </div>
            </div>
        </div>
    </div>

    <script>
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