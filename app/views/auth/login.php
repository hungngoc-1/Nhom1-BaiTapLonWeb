<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Hệ thống Quản lý Nhà trọ Nhóm 10</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #1e293b 0%, #3b82f6 100%); min-height: 100vh; }
        .login-card { max-width: 420px; width: 100%; border-radius: 16px; border: none; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center p-3">
    <div class="card login-card shadow-lg p-4">
        <div class="text-center mb-4">
            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px;">
                <i class="bi bi-buildings-fill fs-2"></i>
            </div>
            <h4 class="fw-bold mb-1">QUẢN LÝ NHÀ TRỌ</h4>
            <p class="text-muted small mb-0">Đăng nhập hệ thống (Nguyễn Kim Huệ)</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small">
                <i class="bi bi-exclamation-circle me-1"></i> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?route=auth/login">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

            <div class="mb-3">
                <label class="form-label small fw-semibold">Email hoặc Tên đăng nhập</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="admin@nhatro.vn" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Mật khẩu</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Vai trò đăng nhập (Demo)</label>
                <select name="role" class="form-select form-select-sm">
                    <option value="landlord">Chủ nhà trọ / Quản trị viên</option>
                    <option value="tenant">Người thuê trọ</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-100 fw-semibold py-2 mb-3">
                <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập hệ thống
            </button>

            <div class="text-center">
                <a href="?route=dashboard" class="text-decoration-none small text-secondary">
                    <i class="bi bi-arrow-left"></i> Quay lại trang Dashboard Demo
                </a>
            </div>
        </form>
    </div>
</body>
</html>