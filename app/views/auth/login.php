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

        .brand-box { display: flex; align-items: center; gap: 14px; margin-bottom: 28px; cursor: pointer; text-decoration: none; }
        
        .brand-name { font-size: 2.4rem; font-weight: 800; line-height: 1; letter-spacing: -0.3px; }
        .brand-name .part-1 { color: #ffffff; }
        .brand-name .part-2 { color: #A8E4DD; }
        
        .brand-sub { font-size: 0.92rem; color: rgba(255, 255, 255, 0.95); margin-top: 5px; font-weight: 500;}
        
        .hero-heading { font-size: 2.6rem; font-weight: 800; line-height: 1.25; margin-bottom: 16px; letter-spacing: -0.3px; }
        .hero-heading span { color: #A8E4DD; font-weight: 700; }
        
        .hero-desc { font-size: 1rem; line-height: 1.6; color: rgba(255, 255, 255, 0.92); margin-bottom: 30px; font-weight: 400;}
        
        .feature-list { display: flex; flex-direction: column; gap: 16px; }
        .feature-item { 
            display: flex; align-items: center; gap: 16px; 
            padding: 10px 14px; border-radius: 16px; cursor: pointer;
            transition: all 0.25s ease; border: 1px solid transparent;
        }
        .feature-item:hover {
            background: rgba(255, 255, 255, 0.14);
            border-color: rgba(255, 255, 255, 0.25);
            transform: translateX(6px);
        }
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
            border-radius: 36px; padding: 42px 44px; 
            border: 2px solid #287A74;
            box-shadow: 0 22px 50px rgba(40, 122, 116, 0.12), 0 2px 6px rgba(0, 0, 0, 0.03); 
            z-index: 3; position: relative;
        }

        .card-brand { display: flex; align-items: center; gap: 14px; margin-bottom: 24px; text-decoration: none; }
        
        .card-brand .brand-name { font-size: 1.8rem; font-weight: 800; line-height: 1; }
        .card-brand .brand-name .part-1 { color: #113437; }
        .card-brand .brand-name .part-2 { color: #287A74; }
        
        .card-brand .brand-sub { color: #6B8B88; font-size: 0.85rem; font-weight: 500; margin-top: 4px;}

        .login-title { font-size: 1.9rem; font-weight: 800; color: #113437; margin-bottom: 6px; }
        .login-subtitle { color: #6B8B88; font-size: 0.95rem; margin-bottom: 22px; }
        
        .alert-error {
            background-color: #FEEFEF; color: #D93025; padding: 14px 16px;
            border-radius: 12px; font-size: 0.9rem; margin-bottom: 18px;
            display: flex; align-items: center; gap: 10px; font-weight: 500;
            border: 1px solid #FAD2CF;
        }
        .alert-success {
            background-color: #E6F4EA; color: #137333; padding: 14px 16px;
            border-radius: 12px; font-size: 0.9rem; margin-bottom: 18px;
            display: flex; align-items: center; gap: 10px; font-weight: 500;
            border: 1px solid #CEEAD6;
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
        
        .options-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; font-size: 0.92rem; }
        
        .custom-check { display: flex; align-items: center; gap: 10px; color: #355C58; font-weight: 500; cursor: pointer; user-select: none; }
        .custom-check input[type="checkbox"] {
            appearance: none; width: 20px; height: 20px; 
            background: #E2EAEA; border-radius: 5px; 
            display: flex; align-items: center; justify-content: center; cursor: pointer;
            transition: 0.2s;
        }
        .custom-check input[type="checkbox"]:checked {
            background: #287A74;
        }
        .custom-check input[type="checkbox"]:checked::after {
            content: '✓'; color: white; font-size: 13px; font-weight: bold;
        }
        
        .forgot-link { color: #287A74; text-decoration: none; font-weight: 600; cursor: pointer; background: none; border: none; font-family: inherit; font-size: inherit; }
        .forgot-link:hover { text-decoration: underline; }
        
        .btn-login { 
            width: 100%; background-color: #287A74; color: #ffffff; 
            border: none; border-radius: 14px; padding: 16px; font-size: 1.05rem; 
            font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 10px; 
            cursor: pointer; transition: 0.3s;
            box-shadow: 0 6px 16px rgba(40, 122, 116, 0.2);
        }
        .btn-login:hover { background-color: #1F615D; transform: translateY(-1px); }

        /* Khung gợi ý tài khoản SQL nhanh */
        .quick-accounts {
            margin-top: 18px;
            padding: 12px 14px;
            background: #F4F9F9;
            border: 1px dashed #A8E4DD;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }
        .quick-accounts-label {
            font-size: 0.8rem;
            color: #355C58;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .btn-quick-fill {
            background: #ffffff;
            color: #287A74;
            border: 1.5px solid #287A74;
            border-radius: 20px;
            padding: 5px 12px;
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn-quick-fill:hover {
            background: #287A74;
            color: #ffffff;
        }
        
        .card-footer-text { margin-top: 20px; text-align: center; font-size: 0.85rem; color: #8BA8A5; }

        /* Modal Quên mật khẩu & Danh sách tài khoản SQL */
        .modal-backdrop-custom {
            position: fixed; inset: 0;
            background: rgba(17, 52, 55, 0.55);
            backdrop-filter: blur(4px);
            display: none; align-items: center; justify-content: center;
            z-index: 1000; padding: 20px;
        }
        .modal-backdrop-custom.show { display: flex; }
        .modal-box {
            background: #ffffff;
            width: 100%; max-width: 520px;
            border-radius: 28px;
            border: 2px solid #287A74;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            animation: popIn 0.2s ease-out;
        }
        @keyframes popIn {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .modal-head {
            background: #287A74;
            color: #ffffff;
            padding: 18px 24px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .modal-head h3 { font-size: 1.15rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 8px; }
        .modal-close {
            background: rgba(255,255,255,0.2); border: none; color: #fff;
            width: 32px; height: 32px; border-radius: 50%; cursor: pointer;
            font-size: 1.1rem; display: flex; align-items: center; justify-content: center;
        }
        .modal-body-custom { padding: 22px 24px; max-height: 80vh; overflow-y: auto; }
        .acc-badge {
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 12px; border-radius: 12px; background: #F4F9F9;
            border: 1px solid #E2EAEA; margin-bottom: 8px; font-size: 0.85rem;
        }

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
                <a href="index.php?route=auth/login" class="brand-box">
                    <svg width="50" height="50" viewBox="0 0 24 24" fill="none">
                        <path d="M3 9L12 2L21 9V20C21 20.5523 20.5523 21 20 21H4C3.44772 21 3 20.5523 3 20V9Z" fill="#A8E4DD" stroke="#A8E4DD" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9 22V12H15V22" stroke="#166E66" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="#166E66"/>
                    </svg>
                    <div>
                        <div class="brand-name"><span class="part-1">Stay</span><span class="part-2">Mate</span></div>
                        <div class="brand-sub">Hệ thống quản lý phòng trọ</div>
                    </div>
                </a>
                
                <h2 class="hero-heading">Quản lý phòng trọ<br><span>dễ dàng, hiệu quả</span></h2>
                <p class="hero-desc">Giải pháp quản lý phòng trọ hiện đại, giúp bạn quản lý thông tin phòng, khách thuê, hợp đồng, hoá đơn và thanh toán một cách nhanh chóng, chính xác và tiện lợi.</p>
                
                <div class="feature-list">
                    <div class="feature-item" onclick="fillAndFocus('admin01', 'admin01', 'Quản lý phòng trọ: Theo dõi 6 phòng (P101 - P302) theo từng Tòa nhà.')">
                        <div class="feature-icon"><i class="bi bi-house-door-fill"></i></div>
                        <div class="feature-info"><h4>Quản lý phòng trọ</h4><p>Theo dõi tình trạng phòng, công suất thuê</p></div>
                    </div>
                    <div class="feature-item" onclick="fillAndFocus('admin01', 'admin01', 'Hợp đồng & khách thuê: Quản lý 5 hợp đồng và thông tin khách thuê từ CSDL.')">
                        <div class="feature-icon"><i class="bi bi-file-earmark-text-fill"></i></div>
                        <div class="feature-info"><h4>Hợp đồng & khách thuê</h4><p>Lưu trữ, tra cứu dễ dàng</p></div>
                    </div>
                    <div class="feature-item" onclick="fillAndFocus('admin01', 'admin01', 'Hóa đơn & thanh toán: Tự động tính điện, nước, dịch vụ và theo dõi 23 hóa đơn.')">
                        <div class="feature-icon"><i class="bi bi-receipt-cutoff"></i></div>
                        <div class="feature-info"><h4>Hóa đơn & thanh toán</h4><p>Điện, nước, internet, chi phí phát sinh</p></div>
                    </div>
                    <div class="feature-item" onclick="fillAndFocus('admin01', 'admin01', 'Báo cáo thống kê: Thống kê doanh thu thực thu & công nợ trực tiếp từ MySQL.')">
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
                <a href="index.php?route=auth/login" class="card-brand">
                    <svg width="42" height="42" viewBox="0 0 24 24" fill="none">
                        <path d="M3 9L12 2L21 9V20C21 20.5523 20.5523 21 20 21H4C3.44772 21 3 20.5523 3 20V9Z" fill="#287A74" stroke="#287A74" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9 22V12H15V22" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="#ffffff"/>
                    </svg>
                    <div>
                        <div class="brand-name"><span class="part-1">Stay</span><span class="part-2">Mate</span></div>
                        <div class="brand-sub">Hệ thống quản lý phòng trọ</div>
                    </div>
                </a>

                <h1 class="login-title">Đăng nhập</h1>
                <p class="login-subtitle" id="loginSubtitle">Vui lòng đăng nhập để tiếp tục sử dụng hệ thống.</p>

                <?php if (!empty($error)): ?>
                    <div class="alert-error">
                        <i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert-success">
                        <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php?route=auth/process" id="loginForm">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action" value="login">

                    <div class="field-group">
                        <label class="field-label">Tên đăng nhập</label>
                        <div class="input-box">
                            <i class="bi bi-person"></i>
                            <input type="text" name="username" id="usrInput" value="<?= htmlspecialchars($old_username ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Nhập tên đăng nhập hoặc email" required autocomplete="username">
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
                        <button type="button" class="forgot-link" onclick="openForgotModal()">Quên mật khẩu?</button>
                    </div>
                    
                    <button type="submit" class="btn-login">Đăng nhập <i class="bi bi-arrow-right"></i></button>
                </form>

                <div style="margin-top: 18px; padding: 14px; background: #F0FBF9; border-radius: 16px; border: 1.5px dashed #A8E4DD;">
                    <!-- KHỐI 1: KHÁCH THUÊ PHÒNG (CHỈ VÀO CỔNG KHÁCH THUÊ) -->
                    <div style="margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px dashed #A8E4DD;">
                        <div style="font-size: 0.84rem; font-weight: 800; color: #166E66; margin-bottom: 4px; display: flex; align-items: center; justify-content: space-between;">
                            <span><i class="bi bi-person-badge-fill text-success"></i> 1. Đăng nhập Khách Thuê (Cư Dân):</span>
                            <span class="badge" style="background: #e6f4f2; color: #166E66; font-size: 0.68rem; padding: 2px 6px; border-radius: 6px;">Chỉ vào Cổng Khách Thuê</span>
                        </div>
                        <div style="font-size: 0.73rem; color: #475569; margin-bottom: 6px;">
                            <i class="bi bi-shield-check text-success"></i> Khách thuê chỉ xem hóa đơn, tiền phòng & hợp đồng của mình, <strong>không xem được trang quản lý</strong>.
                        </div>
                        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                            <button type="button" class="btn-quick-fill" style="padding: 6px 12px; font-size: 0.78rem; font-weight: 800; background: #166E66; color: #fff; border: none; border-radius: 8px;" onclick="fillLogin('kh_a101', 'hash'); document.getElementById('loginForm').submit();" title="Khách thuê Nguyễn Tiến Đạt - Phòng A101 (Tòa A)">
                                <i class="bi bi-door-open-fill"></i> P.A101 - Nguyễn Tiến Đạt
                            </button>
                            <button type="button" class="btn-quick-fill" style="padding: 6px 12px; font-size: 0.78rem; font-weight: 800; background: #166E66; color: #fff; border: none; border-radius: 8px;" onclick="fillLogin('kh_b101', 'hash'); document.getElementById('loginForm').submit();" title="Khách thuê Mai Thùy Dung - Phòng B101 (Tòa B)">
                                <i class="bi bi-door-open-fill"></i> P.B101 - Mai Thùy Dung
                            </button>
                            <button type="button" class="btn-quick-fill" style="padding: 6px 12px; font-size: 0.78rem; font-weight: 800; background: #166E66; color: #fff; border: none; border-radius: 8px;" onclick="fillLogin('kh_c101', 'hash'); document.getElementById('loginForm').submit();" title="Khách thuê Hoàng Thị Ngọc Bích - Phòng C101 (Tòa C)">
                                <i class="bi bi-door-open-fill"></i> P.C101 - Hoàng Bích
                            </button>
                        </div>
                    </div>

                    <!-- KHỐI 2: BAN QUẢN LÝ (VÀO TRANG TỔNG QUAN / QUẢN TRỊ) -->
                    <div>
                        <div style="font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 4px; display: flex; align-items: center; justify-content: space-between;">
                            <span><i class="bi bi-shield-lock-fill text-warning"></i> 2. Đăng nhập Ban Quản Lý & Admin:</span>
                            <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 0.68rem; padding: 2px 6px; border-radius: 6px;">Vào trang Tổng quan</span>
                        </div>
                        <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                            <button type="button" class="btn-quick-fill" style="padding: 4px 8px; font-size: 0.74rem; background: #113437; color: #fff;" onclick="fillLogin('admin', 'hash'); document.getElementById('loginForm').submit();" title="Quản trị viên toàn hệ thống (Nguyễn Tuấn Dũng)">
                                <i class="bi bi-shield-shaded"></i> Admin Tổng
                            </button>
                            <button type="button" class="btn-quick-fill" style="padding: 4px 8px; font-size: 0.74rem; background: #475569; color: #fff;" onclick="fillLogin('quanly_a', 'hash'); document.getElementById('loginForm').submit();" title="Quản lý Tòa A (Nguyễn Văn Quân)">
                                <i class="bi bi-building"></i> QL Tòa A (Quân)
                            </button>
                            <button type="button" class="btn-quick-fill" style="padding: 4px 8px; font-size: 0.74rem; background: #475569; color: #fff;" onclick="fillLogin('quanly_b', 'hash'); document.getElementById('loginForm').submit();" title="Quản lý Tòa B (Trần Thị Mai Lan)">
                                <i class="bi bi-building"></i> QL Tòa B (Lan)
                            </button>
                            <button type="button" class="btn-quick-fill" style="padding: 4px 8px; font-size: 0.74rem; background: #475569; color: #fff;" onclick="fillLogin('quanly_c', 'hash'); document.getElementById('loginForm').submit();" title="Quản lý Tòa C (Lê Hoàng Nam)">
                                <i class="bi bi-building"></i> QL Tòa C (Nam)
                            </button>
                        </div>
                    </div>
                </div>

                
                
                <div class="card-footer-text">© <?= date('Y') ?> StayMate. Hệ thống quản lý phòng trọ.</div>
            </div>
        </div>
    </div>

    <!-- MODAL QUÊN MẬT KHẨU & TRA CỨU TÀI KHOẢN SQL -->
    <div class="modal-backdrop-custom" id="forgotModal" onclick="if(event.target===this) closeForgotModal()">
        <div class="modal-box">
            <div class="modal-head">
                <h3><i class="bi bi-shield-lock-fill"></i> Khôi phục mật khẩu & Tài khoản SQL</h3>
                <button type="button" class="modal-close" onclick="closeForgotModal()">&times;</button>
            </div>
            <div class="modal-body-custom">
                <p style="font-size:0.88rem; color:#475569; margin-bottom:14px;">
                    Nhập <strong>Tên đăng nhập</strong>, <strong>Email</strong> hoặc <strong>Số điện thoại</strong> trong CSDL để đặt lại mật khẩu mới:
                </p>
                <form method="POST" action="index.php?route=auth/process" style="margin-bottom: 20px;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action" value="forgot_password">
                    <div class="field-group" style="margin-bottom:12px;">
                        <label class="field-label">Tài khoản / Email / SĐT</label>
                        <div class="input-box">
                            <i class="bi bi-person-check"></i>
                            <input type="text" name="identifier" id="resetIdentifier" placeholder="VD: admin01 hoặc admin@nhatro.com" required>
                        </div>
                    </div>
                    <div class="field-group" style="margin-bottom:14px;">
                        <label class="field-label">Mật khẩu mới</label>
                        <div class="input-box">
                            <i class="bi bi-key"></i>
                            <input type="text" name="new_password" value="admin01" placeholder="Nhập mật khẩu mới" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-login" style="padding:12px; font-size:0.95rem;">
                        <i class="bi bi-check2-circle"></i> Cập nhật mật khẩu vào SQL
                    </button>
                </form>

                <div style="margin-top: 18px; padding: 12px; background: #F0FBF9; border-radius: 16px; border: 1.5px dashed #A8E4DD;">
                    <div style="font-size: 0.8rem; font-weight: 700; color: #166E66; margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between;">
                        <span><i class="bi bi-shield-lock"></i> Đăng nhập nhanh Quản trị & Quản lý tòa:</span>
                        <span style="font-size: 0.7rem; color: #64748b;">(MK: <code>123456</code> hoặc <code>hash</code>)</span>
                    </div>
                    <div style="display: flex; gap: 5px; flex-wrap: wrap; margin-bottom: 10px;">
                        <button type="button" class="btn-quick-fill" style="padding: 4px 9px; font-size: 0.76rem; background: #113437; color: #fff;" onclick="fillLogin('admin', 'hash'); document.getElementById('loginForm').submit();" title="Quản trị viên (Toàn quyền tất cả các tòa)">
                            <i class="bi bi-shield-shaded"></i> Admin
                        </button>
                        <button type="button" class="btn-quick-fill" style="padding: 4px 9px; font-size: 0.76rem; background: #287A74; color: #fff;" onclick="fillLogin('quanly_a', 'hash'); document.getElementById('loginForm').submit();" title="Quản lý Tòa A (Nguyễn Văn Quân - Chỉ xem Tòa A)">
                            <i class="bi bi-building"></i> QL Tòa A (quanly_a)
                        </button>
                        <button type="button" class="btn-quick-fill" style="padding: 4px 9px; font-size: 0.76rem; background: #1d72b8; color: #fff;" onclick="fillLogin('quanly_b', 'hash'); document.getElementById('loginForm').submit();" title="Quản lý Tòa B (Trần Thị Mai Lan - Chỉ xem Tòa B)">
                            <i class="bi bi-building"></i> QL Tòa B (quanly_b)
                        </button>
                        <button type="button" class="btn-quick-fill" style="padding: 4px 9px; font-size: 0.76rem; background: #b8860b; color: #fff;" onclick="fillLogin('quanly_c', 'hash'); document.getElementById('loginForm').submit();" title="Quản lý Tòa C (Lê Hoàng Nam - Chỉ xem Tòa C)">
                            <i class="bi bi-building"></i> QL Tòa C (quanly_c)
                        </button>
                    </div>

                    <div style="font-size: 0.78rem; font-weight: 700; color: #166E66; margin-bottom: 6px;">
                        <i class="bi bi-person-badge"></i> Đăng nhập nhanh Khách Thuê:
                    </div>
                    <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                        <button type="button" class="btn-quick-fill" style="padding: 4px 9px; font-size: 0.76rem;" onclick="fillLogin('kh_a101', 'hash'); document.getElementById('loginForm').submit();" title="Nguyễn Tiến Đạt (P.A101 - Tòa A)">
                            <i class="bi bi-door-open"></i> P.A101
                        </button>
                        <button type="button" class="btn-quick-fill" style="padding: 4px 9px; font-size: 0.76rem;" onclick="fillLogin('kh_b101', 'hash'); document.getElementById('loginForm').submit();" title="Mai Thùy Dung (P.B101 - Tòa B)">
                            <i class="bi bi-door-open"></i> P.B101
                        </button>
                        <button type="button" class="btn-quick-fill" style="padding: 4px 9px; font-size: 0.76rem;" onclick="fillLogin('kh_c101', 'hash'); document.getElementById('loginForm').submit();" title="Hoàng Thị Ngọc Bích (P.C101 - Tòa C)">
                            <i class="bi bi-door-open"></i> P.C101
                        </button>
                    </div>
                </div>

                <div style="border-top:1px solid #E2EAEA; padding-top:14px;">
                    <div style="font-size:0.85rem; font-weight:700; color:#113437; margin-bottom:10px;">
                        <i class="bi bi-people-fill" style="color:#287A74;"></i> Danh sách tài khoản hiện có trong CSDL (`users`):
                    </div>
                    <?php if (!empty($demoUsers)): ?>
                        <?php foreach ($demoUsers as $u): ?>
                            <div class="acc-badge">
                                <div>
                                    <div style="font-weight:700; color:#113437;">
                                        <?= htmlspecialchars($u['full_name']) ?>
                                        <span style="color:#287A74;">(<?= htmlspecialchars($u['username']) ?>)</span>
                                    </div>
                                    <div style="font-size:0.78rem; color:#6B8B88;">
                                        Vai trò: <strong><?= htmlspecialchars($u['role']) ?></strong> • SĐT: <?= htmlspecialchars($u['phone']) ?>
                                    </div>
                                </div>
                                <button type="button" class="btn-quick-fill" onclick="fillLogin('<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>', '<?= htmlspecialchars($u['username'] === 'admin01' ? 'admin01' : $u['password'], ENT_QUOTES) ?>'); closeForgotModal();">
                                    Chọn đăng nhập
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="acc-badge">
                            <div><strong>Quản Trị Viên (admin01)</strong> • Mật khẩu: <code>admin01</code></div>
                            <button type="button" class="btn-quick-fill" onclick="fillLogin('admin01', 'admin01'); closeForgotModal();">Chọn</button>
                        </div>
                    <?php endif; ?>
                </div>
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

        function fillLogin(user, pass) {
            document.getElementById('usrInput').value = user;
            document.getElementById('pwdInput').value = pass;
            document.getElementById('usrInput').focus();
        }

        function fillAndFocus(user, pass, msg) {
            fillLogin(user, pass);
            const sub = document.getElementById('loginSubtitle');
            if (sub && msg) {
                sub.textContent = msg;
                sub.style.color = '#287A74';
                sub.style.fontWeight = '600';
            }
        }

        function openForgotModal() {
            document.getElementById('forgotModal').classList.add('show');
        }

        function closeForgotModal() {
            document.getElementById('forgotModal').classList.remove('show');
        }
    </script>
<?php require_once dirname(__DIR__) . '/components/toast.php'; ?>
</body>
</html>
