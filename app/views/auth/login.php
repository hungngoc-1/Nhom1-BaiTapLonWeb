<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - StayMate</title>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            min-height: 100vh; 
            font-family: 'Be Vietnam Pro', sans-serif; 
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            display: flex; 
            overflow-y: auto; 
            background-color: #F4F9F9; 
        }

        .login-wrapper { display: flex; width: 100%; min-height: 100vh; position: relative; overflow: hidden; }
        
        /* ================= CỘT TRÁI ================= */
        .left-panel { 
            width: 50%; 
            position: relative;
            color: #ffffff; 
            padding: 55px 70px; 
            display: flex; flex-direction: column; 
            justify-content: center;
            overflow: hidden;
        }
        
        .left-panel-bg {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?ixlib=rb-4.0.3&auto=format&fit=crop&w=1400&q=85');
            background-size: cover; background-position: center;
            z-index: -1;
        }
        
        .left-panel-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(180deg, rgba(20, 110, 102, 0.72) 0%, rgba(12, 68, 63, 0.82) 100%);
            z-index: -1;
        }

        .left-content {
            display: flex; flex-direction: column;
            position: relative; z-index: 2;
            max-width: 520px;
        }

        .brand-box { display: flex; align-items: center; gap: 14px; margin-bottom: 28px; }
        
        .brand-name { font-size: 2.4rem; font-weight: 800; line-height: 1; letter-spacing: -0.3px; }
        .brand-name .part-1 { color: #ffffff; }
        .brand-name .part-2 { color: #A8E4DD; }
        
        .brand-sub { font-size: 0.92rem; color: rgba(255, 255, 255, 0.95); margin-top: 5px; font-weight: 500;}
        
        .hero-heading { font-size: 2.6rem; font-weight: 800; line-height: 1.25; margin-bottom: 16px; letter-spacing: -0.3px; }
        .hero-heading span { color: #A8E4DD; font-weight: 700; }
        
        .hero-desc { font-size: 1rem; line-height: 1.6; color: rgba(255, 255, 255, 0.92); margin-bottom: 30px; font-weight: 400;}
        
        .feature-list { display: flex; flex-direction: column; gap: 18px; }
        .feature-item { display: flex; align-items: center; gap: 16px; }
        .feature-icon { 
            width: 44px; height: 44px; 
            background: rgba(255, 255, 255, 0.25); 
            border-radius: 50%; 
            display: flex; align-items: center; justify-content: center; 
            font-size: 1.25rem; color: #ffffff; flex-shrink: 0; 
            backdrop-filter: blur(4px);
        }
        .feature-info h4 { font-size: 0.98rem; font-weight: 700; margin: 0 0 2px; color: #ffffff; }
        .feature-info p { font-size: 0.86rem; margin: 0; color: rgba(255, 255, 255, 0.85); }

        /* ================= CỘT PHẢI ================= */
        .right-panel { 
            width: 50%; 
            display: flex; align-items: center; justify-content: center; 
            position: relative; overflow: hidden; padding: 20px;
            background-color: #ffffff;
        }

        .decor-top-right-shape {
            position: absolute; top: 0; right: 0; 
            width: 240px; height: 240px; 
            background: #AEE6DE; 
            border-bottom-left-radius: 100%; 
            z-index: 1;
        }
        .decor-top-right-yellow {
            position: absolute; top: -60px; right: -60px; 
            width: 260px; height: 260px; 
            background: #FEF7E2; 
            border-radius: 50%; 
            z-index: 0;
        }
        .decor-dots {
            position: absolute; top: 35px; right: 35px; 
            width: 100px; height: 100px;
            background-image: radial-gradient(#6CAE9F 2.2px, transparent 2.2px);
            background-size: 18px 18px; 
            z-index: 2;
        }
        
        .decor-bottom-left {
            position: absolute; bottom: -100px; left: -80px; 
            width: 350px; height: 350px; background: #FEF7E2; 
            border-radius: 50%; z-index: 0;
        }

        /* Khung đăng nhập với viền dày hơn và màu xanh rõ nét */
        .login-card { 
            width: 100%; max-width: 520px; background: #ffffff; 
            border-radius: 36px; padding: 48px 46px; 
            border: 2px solid #287A74; /* Viền dày 2px và mang màu xanh chủ đạo rõ nét */
            box-shadow: 0 22px 50px rgba(40, 122, 116, 0.12), 0 2px 6px rgba(0, 0, 0, 0.03); 
            z-index: 3; position: relative;
        }

        .card-brand { display: flex; align-items: center; gap: 14px; margin-bottom: 28px; }
        
        .card-brand .brand-name { font-size: 1.8rem; font-weight: 800; line-height: 1; }
        .card-brand .brand-name .part-1 { color: #113437; }
        .card-brand .brand-name .part-2 { color: #287A74; }
        
        .card-brand .brand-sub { color: #6B8B88; font-size: 0.85rem; font-weight: 500; margin-top: 4px;}

        .login-title { font-size: 1.9rem; font-weight: 800; color: #113437; margin-bottom: 6px; }
        .login-subtitle { color: #6B8B88; font-size: 0.95rem; margin-bottom: 28px; }
        
        .alert-error {
            background-color: #FEEFEF; color: #D93025; padding: 14px 16px;
            border-radius: 12px; font-size: 0.9rem; margin-bottom: 20px;
            display: flex; align-items: center; gap: 10px; font-weight: 500;
        }

        .field-group { margin-bottom: 18px; }
        .field-label { display: block; font-size: 0.9rem; font-weight: 600; color: #113437; margin-bottom: 8px; }
        .input-box { 
            display: flex; align-items: center; 
            border: 1.5px solid #E2EAEA; border-radius: 14px; 
            padding: 0 18px; transition: 0.3s; background: #fff;
        }
        .input-box:focus-within { border-color: #287A74; box-shadow: 0 0 0 4px rgba(40,122,116,0.12); }
        .input-box i { color: #8BA8A5; font-size: 1.25rem; }
        .input-box input { 
            width: 100%; border: none; background: transparent; 
            padding: 14px 12px; font-size: 1rem; color: #113437; outline: none; 
        }
        .input-box input::placeholder { color: #A0B6B4; }
        .pwd-toggle { cursor: pointer; color: #8BA8A5; transition: 0.2s; }
        .pwd-toggle:hover { color: #287A74; }
        
        .options-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; font-size: 0.92rem; }
        
        .custom-check { display: flex; align-items: center; gap: 10px; color: #355C58; font-weight: 500; cursor: pointer; }
        .custom-check input[type="checkbox"] {
            appearance: none; width: 20px; height: 20px; 
            background: #287A74; border-radius: 5px; 
            display: flex; align-items: center; justify-content: center; cursor: pointer;
        }
        .custom-check input[type="checkbox"]::after {
            content: '✓'; color: white; font-size: 13px; font-weight: bold;
        }
        
        .forgot-link { color: #287A74; text-decoration: none; font-weight: 600; }
        .forgot-link:hover { text-decoration: underline; }
        
        .btn-login { 
            width: 100%; background-color: #287A74; color: #ffffff; 
            border: none; border-radius: 14px; padding: 16px; font-size: 1.05rem; 
            font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 10px; 
            cursor: pointer; transition: 0.3s;
            box-shadow: 0 6px 16px rgba(40, 122, 116, 0.2);
        }
        .btn-login:hover { background-color: #1F615D; }
        
        .card-footer-text { margin-top: 28px; text-align: center; font-size: 0.85rem; color: #8BA8A5; }

        @media (max-width: 992px) { .left-panel { display: none; } .right-panel { width: 100%; } }
    </style>
</head>
<body>
    <div class="login-wrapper">
        
        <!-- CỘT TRÁI -->
        <div class="left-panel">
            <div class="left-panel-bg"></div>
            <div class="left-panel-overlay"></div>
            <div class="left-content">
                <div class="brand-box">
                    <svg width="50" height="50" viewBox="0 0 24 24" fill="none">
                        <path d="M3 9L12 2L21 9V20C21 20.5523 20.5523 21 20 21H4C3.44772 21 3 20.5523 3 20V9Z" fill="#A8E4DD" stroke="#A8E4DD" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9 22V12H15V22" stroke="#166E66" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="#166E66"/>
                    </svg>
                    <div>
                        <div class="brand-name"><span class="part-1">Stay</span><span class="part-2">Mate</span></div>
                        <div class="brand-sub">Hệ thống quản lý phòng trọ</div>
                    </div>
                </div>
                
                <h2 class="hero-heading">Quản lý phòng trọ<br><span>dễ dàng, hiệu quả</span></h2>
                <p class="hero-desc">Giải pháp quản lý phòng trọ hiện đại, giúp bạn quản lý thông tin phòng, khách thuê, hợp đồng, hoá đơn và thanh toán một cách nhanh chóng, chính xác và tiện lợi.</p>
                
                <div class="feature-list">
                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-house-door-fill"></i></div>
                        <div class="feature-info"><h4>Quản lý phòng trọ</h4><p>Theo dõi tình trạng phòng, công suất thuê</p></div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-file-earmark-text-fill"></i></div>
                        <div class="feature-info"><h4>Hợp đồng & khách thuê</h4><p>Lưu trữ, tra cứu dễ dàng</p></div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-receipt-cutoff"></i></div>
                        <div class="feature-info"><h4>Hóa đơn & thanh toán</h4><p>Điện, nước, internet, chi phí phát sinh</p></div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-bar-chart-fill"></i></div>
                        <div class="feature-info"><h4>Báo cáo thống kê</h4><p>Doanh thu, công nợ, hiệu suất phòng</p></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CỘT PHẢI -->
        <div class="right-panel">
            <div class="decor-top-right-yellow"></div>
            <div class="decor-top-right-shape"></div>
            <div class="decor-dots"></div>
            <div class="decor-bottom-left"></div>

            <div class="login-card">
                <div class="card-brand">
                    <svg width="42" height="42" viewBox="0 0 24 24" fill="none">
                        <path d="M3 9L12 2L21 9V20C21 20.5523 20.5523 21 20 21H4C3.44772 21 3 20.5523 3 20V9Z" fill="#287A74" stroke="#287A74" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9 22V12H15V22" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="#ffffff"/>
                    </svg>
                    <div>
                        <div class="brand-name"><span class="part-1">Stay</span><span class="part-2">Mate</span></div>
                        <div class="brand-sub">Hệ thống quản lý phòng trọ</div>
                    </div>
                </div>

                <h1 class="login-title">Đăng nhập</h1>
                <p class="login-subtitle">Vui lòng đăng nhập để tiếp tục sử dụng hệ thống.</p>

                <?php if (!empty($error)): ?>
                    <div class="alert-error">
                        <i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php?route=auth/process">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                    <div class="field-group">
                        <label class="field-label">Tên đăng nhập</label>
                        <div class="input-box">
                            <i class="bi bi-person"></i>
                            <input type="text" name="username" placeholder="Nhập tên đăng nhập hoặc email" required autocomplete="username">
                        </div>
                    </div>
                    
                    <div class="field-group">
                        <label class="field-label">Mật khẩu</label>
                        <div class="input-box">
                            <i class="bi bi-lock"></i>
                            <input type="password" name="password" id="pwdInput" placeholder="Nhập mật khẩu" required autocomplete="current-password">
                            <i class="bi bi-eye-slash pwd-toggle" onclick="togglePassword()" title="Hiện mật khẩu"></i>
                        </div>
                    </div>
                    
                    <div class="options-row">
                        <label class="custom-check">
                            <input type="checkbox" name="remember" checked> Ghi nhớ đăng nhập
                        </label>
                        <a href="#" class="forgot-link">Quên mật khẩu?</a>
                    </div>
                    
                    <button type="submit" class="btn-login">Đăng nhập <i class="bi bi-arrow-right"></i></button>
                </form>
                
                <div class="card-footer-text">© <?= date('Y') ?> StayMate. Hệ thống quản lý phòng trọ.</div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const pwdInput = document.getElementById('pwdInput');
            const icon = document.querySelector('.pwd-toggle');
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
                icon.title = "Ẩn mật khẩu";
            } else {
                pwdInput.type = 'password';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
                icon.title = "Hiện mật khẩu";
            }
        }
    </script>
</body>
</html>