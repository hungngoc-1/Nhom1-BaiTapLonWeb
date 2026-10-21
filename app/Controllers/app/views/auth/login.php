<div class="container mt-5">
    <h2>Đăng Nhập Quản Lý Nhà Trọ</h2>
    
    <!-- Hiển thị thông báo lỗi từ Server -->
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['flash_error'], ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <form action="index.php?route=login" method="POST">
        <!-- Mã hóa dữ liệu ẩn để chống CSRF -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        
        <div class="mb-3">
            <label>Tên đăng nhập (*)</label>
            <!-- Giữ lại dữ liệu cũ và mã hóa đầu ra chống XSS -->
            <input type="text" name="username" class="form-control" 
                   value="<?= htmlspecialchars($_SESSION['old_username'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        
        <div class="mb-3">
            <label>Mật khẩu (*)</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        
        <button type="submit" class="btn btn-primary">Đăng nhập</button>
    </form>
</div>