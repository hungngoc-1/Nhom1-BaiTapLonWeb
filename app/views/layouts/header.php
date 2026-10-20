<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Nhà Trọ & Hóa Đơn Điện Nước - Nhóm 10</title>
    <!-- Bootstrap 5 CSS & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { min-height: 100vh; width: 260px; background: #1e293b; color: #fff; transition: all 0.3s; }
        .sidebar .nav-link { color: #cbd5e1; padding: 12px 20px; border-radius: 8px; margin: 4px 12px; font-weight: 500; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #3b82f6; color: #fff; }
        .sidebar .nav-link i { margin-right: 10px; font-size: 1.1rem; }
        .stat-card { border: none; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-3px); }
        @media (max-width: 991.98px) {
            .sidebar { position: fixed; z-index: 1040; left: -260px; }
            .sidebar.show { left: 0; }
        }
    </style>
</head>
<body>
<div class="d-flex">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="flex-grow-1 d-flex flex-column min-vh-100">
        <!-- Navbar trên cùng -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm px-4 py-2">
            <button class="btn btn-outline-secondary d-lg-none me-2" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>

            <!-- Ô tìm kiếm chung -->
            <form class="d-none d-md-flex me-auto" method="GET" action="index.php" style="width: 320px;">
                <input type="hidden" name="route" value="dashboard">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="keyword" class="form-control bg-light border-start-0" 
                           placeholder="Tìm nhanh mã phòng, người thuê..." 
                           value="<?= htmlspecialchars($_GET['keyword'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </form>

            <ul class="navbar-nav ms-auto align-items-center">
                <!-- Chuông thông báo -->
                <li class="nav-item dropdown me-3">
                    <a class="nav-link position-relative" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-bell fs-5 text-secondary"></i>
                        <span class="position-absolute top-1 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= isset($notifications) ? count($notifications) : 3 ?>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="width: 320px;">
                        <li class="dropdown-header fw-bold text-dark border-bottom pb-2">Thông báo hệ thống</li>
                        <?php if (!empty($notifications)): ?>
                            <?php foreach ($notifications as $note): ?>
                                <li>
                                    <a class="dropdown-item py-2 text-wrap border-bottom" href="#">
                                        <span class="badge bg-<?= htmlspecialchars($note['type']) ?> me-1">●</span>
                                        <small class="fw-semibold"><?= htmlspecialchars($note['title'], ENT_QUOTES, 'UTF-8') ?></small>
                                        <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($note['time']) ?></div>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </li>

                <!-- Thông tin User & Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div class="d-none d-sm-block text-start">
                            <div class="fw-semibold lh-1" style="font-size: 0.9rem;">Nguyễn Kim Huệ</div>
                            <small class="text-muted" style="font-size: 0.75rem;">Admin / Người 1</small>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li><a class="dropdown-item" href="?route=login"><i class="bi bi-box-arrow-in-right me-2"></i>Xem giao diện Đăng nhập</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="?route=login"><i class="bi bi-power me-2"></i>Đăng xuất</a></li>
                    </ul>
                </li>
            </ul>
        </nav>