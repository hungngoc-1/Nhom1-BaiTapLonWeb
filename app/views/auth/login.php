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
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { min-height: 100vh; font-family: 'Be Vietnam Pro', sans-serif; background-color: #F4F9F9; display: flex; overflow-x: hidden; }
        .login-wrapper { display: flex; width: 100%; min-height: 100vh; }
        .left-panel { width: 53%; position: relative; background: linear-gradient(155deg, #2A7F79 0%, #287A74 45%, #1D5E59 100%); color: #ffffff; padding: 48px 56px; display: flex; flex-direction: column; justify-content: space-between; overflow: hidden; }
        .left-content { position: relative; z-index: 3; max-width: 470px; }
        .brand-box { display: flex; align-items: center; gap: 14px; margin-bottom: 28px; }
        .brand-name { font-size: 2.1rem; font-weight: 800; color: #ffffff; line-height: 1.1; }
        .brand-name span { color: #98D8D1; }
        .brand-sub { font-size: 0.9rem; color: rgba(255, 255, 255, 0.9); }
        .hero-heading { font-size: 2.25rem; font-weight: 800; line-height: 1.25; margin-bottom: 14px; }
        .hero-heading span { color: #A8E4DD; }
        .hero-desc { font-size: 0.9rem; line-height: 1.6; color: rgba(255, 255, 255, 0.88); margin-bottom: 24px; }
        .feature-list { display: flex; flex-direction: column; gap: 14px; }
        .feature-item { display: flex; align-items: center; gap: 14px; }
        .feature-icon { width: 40px; height: 40px; border-radius: 50%; background: rgba(172, 236, 213, 0.25); border: 1px solid rgba(172, 236, 213, 0.4); display: flex; align-items: center; justify-content: center; font-size: 1.1rem; color: #ffffff; flex-shrink: 0; }
        .feature-info h4 { font-size: 0.92rem; font-weight: 700; margin: 0 0 1px; color: #ffffff; }
        .feature-info p { font-size: 0.78rem; margin: 0; color: rgba(255, 255, 255, 0.82); }
        .handwritten-quote { margin-top: 20px; margin-left: 20px; font-family: 'Caveat', cursive; font-size: 1.45rem; color: rgba(255, 255, 255, 0.94); transform: rotate(-8deg); }
        .right-panel { width: 47%; background-color: #F3F8F8; display: flex; align-items: center; justify-content: center; padding: 36px; position: relative; }
        .login-card { width: 100%; max-width: 450px; background: #ffffff; border-radius: 24px; padding: 40px 40px 30px; box-shadow: 0 20px 55px rgba(24, 78, 74, 0.08); z-index: 2; }
        .login-title { font-size: 1.75rem; font-weight: 800; color: #15454B; margin-bottom: 6px; }
        .login-subtitle { color: #5E878B; font-size: 0.85rem; margin-bottom: 22px; }
        .field-group { margin-bottom: 16px; }
        .field-label { display: block; font-size: 0.85rem; font-weight: 600; color: #184A50; margin-bottom: 6px; }
        .input-box { display: flex; align-items: center; background-color: #FAFDFD; border: 1.5px solid #DFECEC; border-radius: 12px; }
        .input-icon-wrap { width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; color: #1C5258; font-size: 1.1rem; background: rgba(235, 245, 245, 0.65); border-radius: 10px 0 0 10px; }
        .input-box input { width: 100%; border: none; background: transparent; padding: 10px 14px; font-size: 0.88rem; color: #164246; outline: none; }
        .options-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; font-size: 0.82rem; }
        .custom-check { display: flex; align-items: center; gap: 8px; color: #215156; font-weight: 500; }
        .forgot-link { color: #287A74; text-decoration: none; font-weight: 600; }
        .btn-login { width: 100%; background: linear-gradient(90deg, #32857F 0%, #206761 100%); color: #ffffff; border: none; border-radius: 12px; padding: 12px; font-size: 0.95rem; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; }
        .card-footer-text { margin-top: 24px; padding-top: 16px; border-top: 1px solid #ECF3F3; text-align: center; font-size: 0.76rem; color: #699196; }
        @media (max-width: 992px) { .left-panel { display: none; } .right-panel { width: 100%; } }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="left-panel">
            <div class="left-content">
                <div class="brand-box">
                    <div>
                        <div class="brand-name">Stay<span>Mate</span></div>
                        <div class="brand-sub">Hệ thống quản lý phòng trọ</div>
                    </div>
                </div>
                <h2 class="hero-heading">Quản lý phòng trọ<br><span>dễ dàng, hiệu quả</span></h2>
                <p class="hero-desc">Giải pháp quản lý phòng trọ hiện đại, giúp bạn quản lý thông tin phòng, khách thuê, hợp đồng và thanh toán nhanh chóng.</p>
                <div class="feature-list">
                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-house-door-fill"></i></div>
                        <div class="feature-info"><h4>Quản lý phòng trọ</h4><p>Theo dõi tình trạng phòng</p></div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class="bi bi-file-earmark-text-fill"></i></div>
                        <div class="feature-info"><h4>Hợp đồng & khách thuê</h4><p>Lưu trữ, tra cứu dễ dàng</p></div>
                    </div>
                </div>
                <div class="handwritten-quote">An tâm quản lý - Vững vàng kinh doanh</div>
            </div>
        </div>
        <div class="right-panel">
            <div class="login-card">
                <h1 class="login-title">Đăng nhập</h1>
                <p class="login-subtitle">Vui lòng đăng nhập để tiếp tục sử dụng hệ thống.</p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small rounded-3 mb-3">
                        <i class="bi bi-exclamation-circle me-1"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php?route=auth/process">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                    <div class="field-group">
                        <label class="field-label">Tên đăng nhập</label>
                        <div class="input-box">
                            <span class="input-icon-wrap"><i class="bi bi-person"></i></span>
                            <input type="text" name="username" placeholder="Nhập tên đăng nhập hoặc email" required>
                        </div>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Mật khẩu</label>
                        <div class="input-box">
                            <span class="input-icon-wrap"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" placeholder="Nhập mật khẩu" required>
                        </div>
                    </div>
                    <div class="options-row">
                        <label class="custom-check"><input type="checkbox" name="remember" checked> Ghi nhớ đăng nhập</label>
                        <a href="#" class="forgot-link">Quên mật khẩu?</a>
                    </div>
                    <button type="submit" class="btn-login">Đăng nhập <i class="bi bi-arrow-right"></i></button>
                </form>
                <div class="card-footer-text">© 2025 StayMate. Hệ thống quản lý phòng trọ.</div>
            </div>
        </div>
    </div>
</body>
</html>