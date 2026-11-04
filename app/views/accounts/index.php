<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

$userRole     = $_SESSION['role'] ?? 'admin';
$userFullName = $_SESSION['full_name'] ?? 'Quản trị viên Hệ thống';
$userName     = $_SESSION['username'] ?? 'admin01';
$userEmail    = $_SESSION['email'] ?? 'admin@nhatro.com';
$userPhone    = $_SESSION['phone'] ?? '0901234567';

$brandHome = ($userRole === 'admin') ? 'index.php?route=dashboard' : ('index.php?route=overview&building_id=' . urlencode($_SESSION['selected_building_id'] ?? 'A'));
$pageTitle = ($userRole === 'admin') ? 'Quản lý Tài khoản (Quản lý & Khách thuê)' : ('Quản lý Tài khoản Khách thuê - ' . ($buildingName ?? 'Tòa A'));
$menuLabel = ($userRole === 'admin') ? 'Tài khoản' : 'Tài khoản khách';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayMate - <?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --teal-main: #308f86;
            --teal-hover: #25756d;
            --bg-page: #f8fafc;
        }

        body {
            background-color: var(--bg-page);
            font-family: 'Nunito', sans-serif;
            font-size: 0.9rem;
            color: #334155;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-image:
                url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23e6f4f1" fill-opacity="0.8" d="M0,160L48,170.7C96,181,192,203,288,197.3C384,192,480,160,576,149.3C672,139,768,149,864,170.7C960,192,1056,224,1152,213.3C1248,203,1344,149,1392,122.7L1440,96L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0C192,0,96,0,48,0L0,0Z"></path></svg>');
            background-position: top center;
            background-repeat: no-repeat;
            background-size: 100% auto;
            background-attachment: fixed;
        }

        .text-teal-main { color: var(--teal-main) !important; }
        .cursor-pointer { cursor: pointer; }

        /* Navbar */
        .navbar-custom { background: #ffffff; padding: 12px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04); z-index: 100; border-bottom: 1px solid #f1f5f9; }
        .brand-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .brand-logo i { color: var(--teal-main); font-size: 2.2rem; }
        .brand-text { color: var(--teal-main); font-weight: 900; font-size: 1.4rem; line-height: 1; letter-spacing: -0.5px; }
        .brand-sub { color: #64748b; font-size: 0.75rem; font-weight: 600; }

        .nav-pills { gap: 6px; flex-wrap: nowrap !important; }
        .nav-pills .nav-link { 
            color: #475569; font-weight: 800; font-size: 0.88rem; 
            padding: 8px 14px; border-radius: 50px; transition: all 0.2s; white-space: nowrap; 
        }
        .nav-pills .nav-link i { margin-right: 6px; }
        .nav-pills .nav-link:hover { color: var(--teal-main); background: #f0fdfa; transform: translateY(-1px); }
        .nav-pills .nav-link.active { background-color: var(--teal-main); color: #ffffff; box-shadow: 0 6px 15px rgba(48, 143, 134, 0.35); }

        .avatar-circle { width: 44px; height: 44px; background-color: var(--teal-main); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 900; font-size: 1rem; border: 3px solid #ffffff; box-shadow: 0 6px 15px rgba(48, 143, 134, 0.4); }

        /* Welcome Banner */
        .welcome-banner {
            background-color: #ffffff;
            border-radius: 28px;
            padding: 24px 30px;
            box-shadow: 0 10px 30px rgba(48, 143, 134, 0.1);
            border: 3px solid #ffffff;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .welcome-icon-box {
            width: 54px; height: 54px;
            background: linear-gradient(135deg, var(--teal-main), var(--teal-hover));
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1.5rem;
            box-shadow: 0 8px 20px rgba(48, 143, 134, 0.35);
        }

        /* Stat cards */
        .stat-card {
            background: #ffffff;
            border-radius: 22px;
            border: 3px solid #ffffff;
            padding: 20px 24px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            transition: transform 0.25s ease;
            position: relative;
            overflow: hidden;
        }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08); }
        .stat-title { font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-num { font-size: 2.2rem; font-weight: 900; line-height: 1; }

        /* Box & Table */
        .section-box {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 28px;
            border: 3px solid #ffffff;
            padding: 24px 28px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.06);
        }
        .btn-teal-custom {
            background: var(--teal-main); border-radius: 50px; font-weight: 800;
            color: white; padding: 10px 24px; border: none;
            box-shadow: 0 6px 18px rgba(48, 143, 134, 0.35); transition: 0.2s;
        }
        .btn-teal-custom:hover { background: var(--teal-hover); color: white; transform: translateY(-2px); }

        .filter-tab {
            padding: 8px 18px; border-radius: 50px; font-weight: 800; font-size: 0.83rem;
            text-decoration: none; border: 1.5px solid #e2e8f0; color: #475569; background: #ffffff; transition: 0.2s;
        }
        .filter-tab:hover { border-color: var(--teal-main); color: var(--teal-main); }
        .filter-tab.active { background: var(--teal-main); border-color: var(--teal-main); color: #ffffff; }

        .table-custom thead th {
            background-color: #f0fdfa; color: var(--teal-main); font-size: 0.8rem; font-weight: 900;
            text-transform: uppercase; padding: 14px 16px; border-bottom: 2px solid #ccfbf1;
        }
        .table-custom tbody td { padding: 14px 16px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 600; }

        .user-avatar-sm {
            width: 42px; height: 42px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 0.95rem; color: white;
            flex-shrink: 0;
        }

        .btn-action-icon {
            width: 36px; height: 36px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            border: 1.5px solid #e2e8f0; background: #ffffff; color: #64748b;
            transition: all 0.2s ease;
        }
        .btn-action-icon:hover { transform: translateY(-2px); }
        .btn-action-icon.btn-edit:hover { background: #fef3c7; color: #d97706; border-color: #fde68a; }
        .btn-action-icon.btn-key:hover { background: #e0f2fe; color: #0284c7; border-color: #bae6fd; }
        .btn-action-icon.btn-del:hover { background: #fee2e2; color: #dc2626; border-color: #fecaca; }

        .btn-hamburger { width: 42px; height: 42px; border-radius: 12px; background: #f0fdfa; border: 2px solid #99f6e4; color: var(--teal-main); display: inline-flex; align-items: center; justify-content: center; font-size: 1.2rem; }
    </style>
</head>
<body>

    <!-- ================= NAVBAR ================= -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container-fluid px-3 px-xl-5">
            <a class="brand-logo me-4" href="<?= $brandHome ?>">
                <i class="fa-solid fa-house-chimney text-teal-main"></i>
                <div>
                    <div class="brand-text">StayMate</div>
                    <div class="brand-sub">Quản Lý Nhà Trọ</div>
                </div>
            </a>

            <!-- Cụm Chuông + Avatar + Nút menu di động -->
            <div class="d-flex align-items-center gap-3 order-lg-3">
                <div class="text-secondary cursor-pointer" title="Thông báo">
                    <i class="fa-solid fa-bell fs-5"></i>
                </div>

                <div class="dropdown">
                    <div class="d-flex align-items-center gap-2" style="cursor: pointer;" data-bs-toggle="dropdown">
                        <div class="avatar-circle">AS</div>
                        <div class="text-start d-none d-sm-block lh-sm">
                            <div class="fw-bold text-dark" style="font-size: 0.9rem;"><?= htmlspecialchars($userFullName) ?> <i class="fa-solid fa-angle-down ms-1 text-muted fs-6"></i></div>
                            <div class="text-muted text-capitalize" style="font-size: 0.75rem;"><?= htmlspecialchars($userRole) ?></div>
                        </div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 mt-2 p-2">
                        <li>
                            <button type="button" class="dropdown-item rounded-3 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#profileModal">
                                <i class="fa-solid fa-id-card me-2 text-teal-main"></i> Thông tin tài khoản
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item rounded-3 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                <i class="fa-solid fa-key me-2 text-warning"></i> Đổi mật khẩu
                            </button>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item rounded-3 py-2 text-danger fw-bold" href="index.php?route=auth/logout"><i class="fa-solid fa-right-from-bracket me-2"></i> Đăng xuất</a></li>
                    </ul>
                </div>

                <button class="btn-hamburger d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#staymateNav">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>

            <!-- Menu chính -->
            <div class="collapse navbar-collapse order-lg-2" id="staymateNav">
                <ul class="nav nav-pills flex-nowrap me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php?route=overview"><i class="fa-solid fa-border-all"></i> Tổng quan</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=rooms"><i class="fa-solid fa-bed"></i> Phòng trọ</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=tenants"><i class="fa-solid fa-users"></i> Khách thuê</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=contracts"><i class="fa-solid fa-file-signature"></i> Hợp đồng</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=utilities"><i class="fa-solid fa-bolt"></i> Điện Nước</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=invoices"><i class="fa-solid fa-file-invoice-dollar"></i> Hóa đơn</a></li>
                <li class="nav-item"><a class="nav-link active" href="index.php?route=accounts"><i class="fa-solid fa-user-gear"></i> Tài khoản</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=reports_csv"><i class="fa-solid fa-chart-pie"></i> Báo cáo</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=showcase" target="_blank"><i class="fa-regular fa-eye"></i> Xem phòng khách</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=tenant/portal" target="_blank"><i class="fa-solid fa-user-shield text-teal-main"></i> Cổng khách thuê</a></li>
            </ul>
            </div>
        </div>
    </nav>

    <!-- ================= MAIN CONTENT ================= -->
    <div class="container-fluid px-3 px-xl-5 py-4">

        <!-- Flash Alert -->
        <?php if (!empty($flashSuccess)): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 fw-bold mb-3" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> <?= htmlspecialchars($flashSuccess) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($flashError)): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 fw-bold mb-3" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= htmlspecialchars($flashError) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Welcome Banner -->
        <div class="welcome-banner mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="welcome-icon-box">
                    <i class="fa-solid fa-users-gear"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-1" style="font-size: 1.45rem; color: #0f172a;"><?= htmlspecialchars($pageTitle) ?></h3>
                    <div class="text-muted" style="font-size: 0.85rem; font-weight: 600;">
                        <?php if ($userRole === 'admin'): ?>
                            Quản trị toàn quyền: Cấp phát tài khoản, phân quyền quản lý tòa nhà và khách thuê trên toàn hệ thống.
                        <?php else: ?>
                            Quản lý riêng: Quản lý danh sách tài khoản đăng nhập cho khách thuê phòng thuộc <?= htmlspecialchars($buildingName) ?>.
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="d-none d-md-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill fw-bold">
                    <i class="fa-regular fa-calendar me-1 text-teal-main"></i> <?= date('d/m/Y') ?>
                </span>
            </div>
        </div>

        <!-- 4 Stats Cards (Admin) hoặc 3 Stats Cards (Quản lý) -->
        <div class="row g-3 mb-4">
            <?php if ($userRole === 'admin'): ?>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stat-card" style="border-left: 5px solid #308f86;">
                        <div class="stat-title text-teal-main"><i class="fa-solid fa-users me-1"></i> TỔNG TÀI KHOẢN</div>
                        <div class="stat-num text-dark mt-2"><?= $stats['total'] ?></div>
                        <div class="small text-muted mt-1 fw-bold">Đã kích hoạt trong CSDL</div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stat-card" style="border-left: 5px solid #ef4444;">
                        <div class="stat-title text-danger"><i class="fa-solid fa-shield-halved me-1"></i> QUẢN TRỊ VIÊN (ADMIN)</div>
                        <div class="stat-num text-dark mt-2"><?= $stats['admins'] ?></div>
                        <div class="small text-muted mt-1 fw-bold">Quyền hạn tối cao</div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stat-card" style="border-left: 5px solid #3b82f6;">
                        <div class="stat-title text-primary"><i class="fa-solid fa-user-tie me-1"></i> QUẢN LÝ TÒA NHÀ</div>
                        <div class="stat-num text-dark mt-2"><?= $stats['managers'] ?></div>
                        <div class="small text-muted mt-1 fw-bold">Phụ trách Tòa A, B, C</div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="stat-card" style="border-left: 5px solid #10b981;">
                        <div class="stat-title text-success"><i class="fa-solid fa-user-group me-1"></i> KHÁCH THUÊ TRỌ</div>
                        <div class="stat-num text-dark mt-2"><?= $stats['tenants'] ?></div>
                        <div class="small text-muted mt-1 fw-bold">Tài khoản khách thuê</div>
                    </div>
                </div>
            <?php else: ?>
                <div class="col-12 col-sm-4">
                    <div class="stat-card" style="border-left: 5px solid #308f86;">
                        <div class="stat-title text-teal-main"><i class="fa-solid fa-users me-1"></i> TỔNG TÀI KHOẢN KHÁCH</div>
                        <div class="stat-num text-dark mt-2"><?= $stats['total'] ?></div>
                        <div class="small text-muted mt-1 fw-bold">Khách thuê <?= htmlspecialchars($buildingName) ?></div>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <div class="stat-card" style="border-left: 5px solid #10b981;">
                        <div class="stat-title text-success"><i class="fa-solid fa-house-user me-1"></i> ĐANG THUÊ PHÒNG</div>
                        <div class="stat-num text-dark mt-2"><?= $stats['renting'] ?></div>
                        <div class="small text-muted mt-1 fw-bold">Có hợp đồng hiệu lực</div>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <div class="stat-card" style="border-left: 5px solid #f59e0b;">
                        <div class="stat-title text-warning" style="color:#d97706 !important;"><i class="fa-regular fa-clock me-1"></i> CHƯA GÁN PHÒNG</div>
                        <div class="stat-num text-dark mt-2"><?= $stats['waiting'] ?></div>
                        <div class="small text-muted mt-1 fw-bold">Khách mới tạo tài khoản</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Section Box Danh Sách Tài Khoản -->
        <div class="section-box">
            <!-- Toolbar -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <!-- Bộ lọc theo Role (Chỉ Admin) -->
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <?php if ($userRole === 'admin'): ?>
                        <a href="index.php?route=accounts" class="filter-tab <?= empty($roleFilter) ? 'active' : '' ?>">Tất cả (<?= $stats['total'] ?>)</a>
                        <a href="index.php?route=accounts&role=manager" class="filter-tab <?= ($roleFilter === 'manager' || $roleFilter === 'landlord') ? 'active' : '' ?>">Quản lý Tòa nhà (<?= $stats['managers'] ?>)</a>
                        <a href="index.php?route=accounts&role=tenant" class="filter-tab <?= ($roleFilter === 'tenant') ? 'active' : '' ?>">Khách thuê (<?= $stats['tenants'] ?>)</a>
                    <?php else: ?>
                        <span class="fw-bold text-dark fs-6"><i class="fa-solid fa-list-check me-2 text-teal-main"></i> Danh sách khách thuê của <?= htmlspecialchars($buildingName) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Tìm kiếm & Nút Thêm mới -->
                <div class="d-flex align-items-center gap-2">
                    <form method="GET" action="index.php" class="d-flex align-items-center">
                        <input type="hidden" name="route" value="accounts">
                        <?php if (!empty($roleFilter)): ?>
                            <input type="hidden" name="role" value="<?= htmlspecialchars($roleFilter) ?>">
                        <?php endif; ?>
                        <div class="input-group" style="width: 260px;">
                            <input type="text" name="keyword" class="form-control rounded-start-pill ps-3" placeholder="Tìm tên, username, SĐT..." value="<?= htmlspecialchars($keyword) ?>" style="font-size:0.88rem;">
                            <button class="btn btn-outline-secondary rounded-end-pill px-3" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                        </div>
                    </form>

                    <button type="button" class="btn btn-teal-custom" data-bs-toggle="modal" data-bs-target="#createAccountModal">
                        <i class="fa-solid fa-plus me-1"></i> Thêm Tài Khoản
                    </button>
                </div>
            </div>

            <!-- Bảng tài khoản -->
            <div class="table-responsive">
                <table class="table table-custom align-middle">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>Tài khoản & Họ tên</th>
                            <th>Vai trò hệ thống</th>
                            <th>Khu vực / Phòng trọ</th>
                            <th>Liên hệ</th>
                            <th>Ngày tạo</th>
                            <th class="text-end" style="width: 140px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($accounts)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fa-regular fa-folder-open fs-2 d-block mb-2 text-secondary"></i>
                                    Không tìm thấy tài khoản nào phù hợp với điều kiện lọc!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($accounts as $acc): ?>
                                <?php
                                    $role = $acc['role'] ?? 'tenant';
                                    $isAdm = ($role === 'admin');
                                    $isMng = ($role === 'landlord' || $role === 'manager');
                                    $isTnt = ($role === 'tenant');

                                    // Màu avatar
                                    $avatarBg = $isAdm ? '#ef4444' : ($isMng ? '#308f86' : '#10b981');
                                    $initials = mb_substr($acc['full_name'] ?? 'U', 0, 1, 'UTF-8');
                                ?>
                                <tr>
                                    <td class="text-muted fw-bold">#<?= $acc['id'] ?></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="user-avatar-sm" style="background: <?= $avatarBg ?>;">
                                                <?= htmlspecialchars($initials) ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark fs-6"><?= htmlspecialchars($acc['full_name']) ?></div>
                                                <div class="text-muted small">
                                                    <i class="fa-solid fa-user me-1 text-teal-main"></i><?= htmlspecialchars($acc['username']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($isAdm): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 fw-bold">
                                                <i class="fa-solid fa-shield-halved me-1"></i> Quản trị viên (Admin)
                                            </span>
                                        <?php elseif ($isMng): ?>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-bold">
                                                <i class="fa-solid fa-user-tie me-1"></i> Quản lý Tòa nhà
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold">
                                                <i class="fa-solid fa-user me-1"></i> Khách thuê trọ
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($isAdm): ?>
                                            <span class="text-muted small"><i class="fa-solid fa-layer-group me-1"></i>Toàn hệ thống (3 Tòa)</span>
                                        <?php elseif ($isMng): ?>
                                            <?php
                                                $assignedBld = 'Tòa A';
                                                if ($acc['username'] === 'quanly_b') $assignedBld = 'Tòa B (Emerald)';
                                                elseif ($acc['username'] === 'quanly_c') $assignedBld = 'Tòa C (Horizon)';
                                                else $assignedBld = 'Tòa A (Sunrise)';
                                            ?>
                                            <span class="badge bg-info-subtle text-info-emphasis border rounded-pill px-2 py-1 fw-bold">
                                                <i class="fa-solid fa-building me-1"></i> <?= $assignedBld ?>
                                            </span>
                                        <?php else: ?>
                                            <?php if (!empty($acc['room_number'])): ?>
                                                <span class="badge bg-light text-dark border rounded-pill px-2 py-1 fw-bold">
                                                    <i class="fa-solid fa-door-open me-1 text-teal-main"></i> Phòng <?= htmlspecialchars($acc['room_number']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-warning-subtle text-warning-emphasis border rounded-pill px-2 py-1 fw-bold">
                                                    Chưa gán phòng
                                                </span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <div><i class="fa-solid fa-phone me-1 text-muted"></i> <?= htmlspecialchars($acc['phone'] ?: 'Chưa có') ?></div>
                                            <div class="text-muted"><i class="fa-regular fa-envelope me-1"></i> <?= htmlspecialchars($acc['email'] ?: 'Chưa có') ?></div>
                                        </div>
                                    </td>
                                    <td class="text-muted small">
                                        <?= !empty($acc['created_at']) ? date('d/m/Y', strtotime($acc['created_at'])) : '---' ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <!-- Nút Sửa -->
                                            <button type="button" class="btn-action-icon btn-edit" title="Sửa thông tin"
                                                    onclick="openEditAccountModal(<?= htmlspecialchars(json_encode($acc), ENT_QUOTES, 'UTF-8') ?>)">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>

                                            <!-- Nút Đổi MK -->
                                            <button type="button" class="btn-action-icon btn-key" title="Đặt lại mật khẩu"
                                                    onclick="openResetPassModal(<?= $acc['id'] ?>, '<?= htmlspecialchars($acc['username'], ENT_QUOTES) ?>')">
                                                <i class="fa-solid fa-key"></i>
                                            </button>

                                            <!-- Nút Xóa (Không cho xóa admin hoặc chính mình) -->
                                            <?php if (!$isAdm && $acc['id'] !== $currentUserId): ?>
                                                <form method="POST" action="index.php?route=accounts" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa tài khoản <?= htmlspecialchars($acc['username']) ?>?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $acc['id'] ?>">
                                                    <button type="submit" class="btn-action-icon btn-del" title="Xóa tài khoản">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- ================= MODAL TẠO TÀI KHOẢN MỚI ================= -->
    <div class="modal fade" id="createAccountModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light rounded-top-4">
                    <h5 class="modal-title fw-bold text-teal-main"><i class="fa-solid fa-user-plus me-2"></i> Thêm Tài Khoản Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="index.php?route=accounts">
                    <input type="hidden" name="action" value="create">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Vai trò tài khoản <span class="text-danger">*</span></label>
                            <?php if ($userRole === 'admin'): ?>
                                <select name="role" class="form-select rounded-3" required>
                                    <option value="tenant" selected>Khách thuê trọ (tenant)</option>
                                    <option value="manager">Quản lý tòa nhà (manager)</option>
                                </select>
                            <?php else: ?>
                                <input type="text" class="form-control rounded-3 bg-light" value="Khách thuê trọ (tenant)" readonly>
                                <input type="hidden" name="role" value="tenant">
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control rounded-3" placeholder="Ví dụ: Nguyễn Văn A" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">Tên đăng nhập <span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control rounded-3" placeholder="khach06 hoặc quanly_x" required minlength="3">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">Mật khẩu ban đầu <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control rounded-3" placeholder="Mặc định: 123456" value="123456" required minlength="4">
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">Số điện thoại <span class="text-danger">*</span></label>
                                <input type="tel" name="phone" class="form-control rounded-3" placeholder="0987654321 (đúng 10 số)" pattern="^0[0-9]{9}$" maxlength="10" minlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" title="Số điện thoại phải gồm đúng 10 số và bắt đầu bằng số 0" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control rounded-3" placeholder="khach@gmail.com" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-teal-custom px-4"><i class="fa-solid fa-check me-1"></i> Tạo tài khoản</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ================= MODAL CHỈNH SỬA TÀI KHOẢN ================= -->
    <div class="modal fade" id="editAccountModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light rounded-top-4">
                    <h5 class="modal-title fw-bold text-teal-main"><i class="fa-solid fa-user-pen me-2"></i> Chỉnh Sửa Tài Khoản</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="index.php?route=accounts">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="editAccId">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Tên đăng nhập</label>
                            <input type="text" id="editAccUsername" class="form-control rounded-3 bg-light" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" id="editAccFullName" class="form-control rounded-3" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">Số điện thoại <span class="text-danger">*</span></label>
                                <input type="tel" name="phone" id="editAccPhone" class="form-control rounded-3" pattern="^0[0-9]{9}$" maxlength="10" minlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" title="Số điện thoại phải gồm đúng 10 số và bắt đầu bằng số 0" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="editAccEmail" class="form-control rounded-3" required>
                            </div>
                        </div>
                        <?php if ($userRole === 'admin'): ?>
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">Vai trò</label>
                                <select name="role" id="editAccRole" class="form-select rounded-3">
                                    <option value="admin">Quản trị viên (admin)</option>
                                    <option value="manager">Quản lý tòa nhà (manager)</option>
                                    <option value="tenant">Khách thuê trọ (tenant)</option>
                                </select>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-teal-custom px-4"><i class="fa-solid fa-check me-1"></i> Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ================= MODAL ĐẶT LẠI MẬT KHẨU CHO USER ================= -->
    <div class="modal fade" id="resetPassModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light rounded-top-4">
                    <h5 class="modal-title fw-bold text-teal-main"><i class="fa-solid fa-key me-2 text-warning"></i> Đặt Lại Mật Khẩu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="index.php?route=accounts">
                    <input type="hidden" name="action" value="reset_password">
                    <input type="hidden" name="id" id="resetPassId">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Tài khoản</label>
                            <input type="text" id="resetPassUsername" class="form-control rounded-3 bg-light" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Mật khẩu mới <span class="text-danger">*</span></label>
                            <input type="password" name="new_password" class="form-control rounded-3" placeholder="Nhập mật khẩu mới" required minlength="4">
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-teal-custom px-4"><i class="fa-solid fa-check me-1"></i> Xác nhận đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ================= MODAL ĐỔI MẬT KHẨU HIỆN TẠI (DROPDOWN) ================= -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light rounded-top-4">
                    <h5 class="modal-title fw-bold text-teal-main"><i class="fa-solid fa-key me-2 text-warning"></i> Đổi Mật Khẩu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="index.php?route=dashboard">
                    <input type="hidden" name="action" value="change_password">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Mật khẩu hiện tại</label>
                            <input type="password" name="old_password" class="form-control rounded-3" placeholder="Nhập mật khẩu hiện tại" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Mật khẩu mới</label>
                            <input type="password" name="new_password" class="form-control rounded-3" placeholder="Nhập mật khẩu mới" required minlength="4">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Xác nhận mật khẩu mới</label>
                            <input type="password" name="confirm_password" class="form-control rounded-3" placeholder="Nhập lại mật khẩu mới" required minlength="4">
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-teal-custom px-4"><i class="fa-solid fa-check me-1"></i> Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ================= MODAL THÔNG TIN TÀI KHOẢN HIỆN TẠI ================= -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light rounded-top-4">
                    <h5 class="modal-title fw-bold text-teal-main"><i class="fa-solid fa-user-shield me-2"></i> Thông tin Tài khoản</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold fs-4" style="width:60px; height:60px; background:var(--teal-main);">AS</div>
                        <div>
                            <h5 class="fw-bold mb-0"><?= htmlspecialchars($userFullName) ?></h5>
                            <span class="badge bg-success rounded-pill text-uppercase"><?= htmlspecialchars($userRole) ?></span>
                        </div>
                    </div>
                    <ul class="list-group list-group-flush border rounded-3">
                        <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Tên đăng nhập:</span> <strong><?= htmlspecialchars($userName) ?></strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Email:</span> <strong><?= htmlspecialchars($userEmail) ?></strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Số điện thoại:</span> <strong><?= htmlspecialchars($userPhone) ?></strong></li>
                    </ul>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                    <a href="index.php?route=auth/logout" class="btn btn-danger rounded-pill px-4"><i class="fa-solid fa-right-from-bracket me-1"></i> Đăng xuất</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openEditAccountModal(acc) {
            document.getElementById('editAccId').value = acc.id || '';
            document.getElementById('editAccUsername').value = acc.username || '';
            document.getElementById('editAccFullName').value = acc.full_name || '';
            document.getElementById('editAccPhone').value = acc.phone || '';
            document.getElementById('editAccEmail').value = acc.email || '';
            const roleEl = document.getElementById('editAccRole');
            if (roleEl && acc.role) {
                roleEl.value = acc.role;
            }
            new bootstrap.Modal(document.getElementById('editAccountModal')).show();
        }

        function openResetPassModal(id, username) {
            document.getElementById('resetPassId').value = id;
            document.getElementById('resetPassUsername').value = username;
            new bootstrap.Modal(document.getElementById('resetPassModal')).show();
        }
    </script>
<?php require_once dirname(__DIR__) . '/components/toast.php'; ?>
</body>
</html>
