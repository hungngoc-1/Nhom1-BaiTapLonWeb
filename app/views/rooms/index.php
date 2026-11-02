<?php
// Tính toán nhanh số lượng theo trạng thái để hiển thị lên các nút lọc (như ảnh mẫu)
$totalCount = count($rooms ?? []);
$availableCount = 0;
$rentedCount = 0;
$maintenanceCount = 0;

if (!empty($rooms)) {
    foreach ($rooms as $r) {
        $st = $r['status'] ?? 'available';
        if ($st === 'available' || $st === 'trong') $availableCount++;
        elseif ($st === 'rented' || $st === 'dang_thue') $rentedCount++;
        else $maintenanceCount++;
    }
}

$currentStatus = $_GET['status'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Phòng trọ & Loại phòng - Smart Stay</title>
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --teal-dark: #287A74;       /* Màu chủ đạo 1 (Ảnh 4) */
            --teal-medium: #58A8A1;     /* Màu chủ đạo 2 (Ảnh 4) */
            --mint-light: #B2F0E8;      /* Màu chủ đạo 3 (Ảnh 4) */
            --yellow-pastel: #FFF9AE;   /* Màu chủ đạo 4 (Ảnh 4) */
            --bg-body: #F0F7F6;
        }

        body {
            background: linear-gradient(135deg, #E8F5F3 0%, #F4FAF9 50%, #FFFDF2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #2C3E50;
        }

        /* ================= THANH MENU TOPBAR ĐẦY ĐỦ ================= */
        .topbar-staymate {
            background: #ffffff;
            border-radius: 16px;
            padding: 10px 20px;
            box-shadow: 0 4px 20px rgba(40, 122, 116, 0.08);
            margin: 16px 20px;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .brand-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--teal-dark), var(--teal-medium));
            color: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            box-shadow: 0 4px 10px rgba(40, 122, 116, 0.25);
        }

        .nav-menu-list {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: wrap;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .nav-menu-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 10px;
            color: #5A6A7E;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
        }

        .nav-menu-link:hover {
            background-color: rgba(178, 240, 232, 0.35);
            color: var(--teal-dark);
            transform: translateY(-1px);
        }

        .nav-menu-link.active {
            background-color: var(--teal-dark);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(40, 122, 116, 0.3);
        }

        /* Chuông thông báo & Profile */
        .notif-btn {
            position: relative;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #F0F7F6;
            color: var(--teal-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: 0.2s;
        }
        .notif-btn:hover { background: var(--mint-light); }
        .notif-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #E74C3C;
            color: white;
            font-size: 0.68rem;
            font-weight: bold;
            padding: 2px 5px;
            border-radius: 10px;
        }

        .avatar-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--teal-dark);
            color: white;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ================= NÚT PILL LỌC TRẠNG THÁI ================= */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: 30px;
            font-size: 0.88rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.25s ease;
            border: 1.5px solid transparent;
        }
        .status-pill:hover { transform: translateY(-2px); }

        .pill-all { background: #1E293B; color: #fff; }
        .pill-empty { background: #E3F9F6; color: var(--teal-dark); border-color: var(--teal-medium); }
        .pill-rented { background: #E6F4EA; color: #1E7E34; border-color: #A8DAB5; }
        .pill-maint { background: var(--yellow-pastel); color: #856404; border-color: #F5E77E; }

        .status-pill.active-filter {
            box-shadow: 0 4px 12px rgba(40, 122, 116, 0.25);
            transform: scale(1.03);
        }

        .btn-teal-primary {
            background: var(--teal-dark);
            color: white;
            border-radius: 30px;
            padding: 8px 20px;
            font-weight: 600;
            border: none;
            transition: all 0.25s;
        }
        .btn-teal-primary:hover {
            background: #1E5E59;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 122, 116, 0.3);
        }

        .btn-teal-outline {
            background: #E3F9F6;
            color: var(--teal-dark);
            border: 1.5px solid var(--teal-medium);
            border-radius: 30px;
            padding: 7px 18px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-teal-outline:hover {
            background: var(--mint-light);
            color: var(--teal-dark);
        }

        /* ================= THANH TÌM KIẾM ================= */
        .search-bar-card {
            background: white;
            border-radius: 16px;
            padding: 14px 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            border: 1px solid #E2F1EF;
        }

        /* ================= THẺ PHÒNG TRỌ (ROOM CARD) ================= */
        .room-card {
            background: white;
            border-radius: 18px;
            border: 1.5px solid #D5ECE9;
            padding: 18px;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .room-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 28px rgba(40, 122, 116, 0.16);
            border-color: var(--teal-medium);
        }

        .room-code-box {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: #1E293B;
            color: white;
            font-weight: 700;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .room-code-box.is-empty {
            background: var(--teal-dark);
        }

        .price-banner {
            background: #FFF9F5;
            border-radius: 10px;
            padding: 10px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 14px 0 10px 0;
        }

        .tenant-box {
            background: #F0FAF8;
            border: 1px solid var(--mint-light);
            border-radius: 12px;
            padding: 10px 14px;
            margin-bottom: 14px;
        }

        .empty-room-box {
            background: #F8FAFC;
            border: 1px dashed #CBD5E1;
            border-radius: 12px;
            padding: 10px 14px;
            margin-bottom: 14px;
        }

        .btn-edit-card {
            border: 1px solid #CBD5E1;
            border-radius: 30px;
            color: #334155;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 7px 12px;
            background: white;
            transition: 0.2s;
            flex-grow: 1;
        }
        .btn-edit-card:hover {
            border-color: var(--teal-dark);
            color: var(--teal-dark);
            background: #F0FAF8;
        }

        .btn-del-card {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 1px solid #FCA5A5;
            color: #EF4444;
            background: #FEF2F2;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
        }
        .btn-del-card:hover {
            background: #EF4444;
            color: white;
        }

        /* Modal Custom */
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>

<!-- ==================== 1. THANH MENU ĐẦY ĐỦ LIÊN KẾT CÁC TRANG ==================== -->
<header class="topbar-staymate d-flex align-items-center justify-content-between flex-wrap gap-3">
    <!-- Logo -->
    <a href="index.php?route=dashboard" class="brand-logo">
        <div class="brand-icon"><i class="bi bi-buildings-fill"></i></div>
        <div>
            <div class="fw-bold" style="color: var(--teal-dark); font-size: 1.05rem; letter-spacing: 0.5px;">SMART STAY</div>
            <div class="text-muted" style="font-size: 0.72rem;">Quản Lý Nhà Trọ</div>
        </div>
    </a>

    <!-- Danh sách 9 mục Menu liên kết trực tiếp qua Router -->
    <ul class="nav-menu-list">
        <li>
            <a href="index.php?route=overview" class="nav-menu-link <?= ($_GET['route'] ?? '') === 'overview' ? 'active' : '' ?>">
                <i class="bi bi-building"></i> Tòa nhà
            </a>
        </li>
        <li>
            <a href="index.php?route=dashboard" class="nav-menu-link <?= ($_GET['route'] ?? '') === 'dashboard' ? 'active' : '' ?>">
                <i class="bi bi-grid-1x2-fill"></i> Tổng quan
            </a>
        </li>
        <li>
            <a href="index.php?route=rooms" class="nav-menu-link <?= ($_GET['route'] ?? 'rooms') === 'rooms' ? 'active' : '' ?>">
                <i class="bi bi-door-open-fill"></i> Phòng trọ
            </a>
        </li>
        <li>
            <a href="index.php?route=tenants" class="nav-menu-link <?= ($_GET['route'] ?? '') === 'tenants' ? 'active' : '' ?>">
                <i class="bi bi-people-fill"></i> Khách thuê
            </a>
        </li>
        <li>
            <a href="index.php?route=contracts" class="nav-menu-link <?= ($_GET['route'] ?? '') === 'contracts' ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-text-fill"></i> Hợp đồng
            </a>
        </li>
        <li>
            <a href="index.php?route=utilities" class="nav-menu-link <?= ($_GET['route'] ?? '') === 'utilities' ? 'active' : '' ?>">
                <i class="bi bi-lightning-charge-fill"></i> Điện Nước
            </a>
        </li>
        <li>
            <a href="index.php?route=invoices" class="nav-menu-link <?= ($_GET['route'] ?? '') === 'invoices' ? 'active' : '' ?>">
                <i class="bi bi-receipt"></i> Hóa đơn & Thu tiền
            </a>
        </li>
        <li>
            <a href="index.php?route=reports" class="nav-menu-link <?= ($_GET['route'] ?? '') === 'reports' ? 'active' : '' ?>">
                <i class="bi bi-bar-chart-line-fill"></i> Báo cáo
            </a>
        </li>
        <li>
            <a href="index.php?route=web360" class="nav-menu-link <?= ($_GET['route'] ?? '') === 'web360' ? 'active' : '' ?>">
                <i class="bi bi-globe2 text-danger"></i> Web 360°
            </a>
        </li>
    </ul>

    <!-- Cụm Thông báo & Tài khoản Admin bên phải -->
    <div class="d-flex align-items-center gap-3">
        <a href="index.php?route=dashboard" class="notif-btn" title="Thông báo">
            <i class="bi bi-bell-fill"></i>
            <span class="notif-badge">8</span>
        </a>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center gap-2 text-decoration-none text-dark dropdown-toggle" data-bs-toggle="dropdown">
                <div class="avatar-circle">Q</div>
                <div class="d-none d-xl-block text-start">
                    <div class="fw-bold" style="font-size: 0.85rem; line-height: 1.1;">Quản trị viên Hệ thố...</div>
                    <small class="text-muted" style="font-size: 0.72rem;">Quản trị viên (Admin)</small>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2">
                <li><a class="dropdown-item" href="index.php?route=dashboard"><i class="bi bi-speedometer2 me-2"></i>Trang Tổng quan</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="index.php?route=auth/logout"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</a></li>
            </ul>
        </div>
    </div>
</header>

<!-- ==================== 2. NỘI DUNG CHÍNH TRANG PHÒNG TRỌ ==================== -->
<div class="container-fluid px-4 pb-5">

    <!-- Thông báo kết quả Thêm / Sửa / Xóa -->
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-3" role="alert" style="background-color: var(--mint-light); color: var(--teal-dark);">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?php
                if ($_GET['msg'] === 'created') echo 'Đã thêm phòng trọ mới thành công!';
                elseif ($_GET['msg'] === 'updated') echo 'Cập nhật thông tin phòng thành công!';
                elseif ($_GET['msg'] === 'deleted') echo 'Đã xóa phòng trọ khỏi hệ thống!';
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Hàng nút Pill lọc trạng thái & Nút Thêm Phòng -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div class="d-flex flex-wrap gap-2">
            <a href="index.php?route=rooms" class="status-pill pill-all <?= $currentStatus === '' ? 'active-filter' : '' ?>">
                <i class="bi bi-grid-fill"></i> Tất cả (<?= $totalCount ?>)
            </a>
            <a href="index.php?route=rooms&status=available" class="status-pill pill-empty <?= $currentStatus === 'available' ? 'active-filter' : '' ?>">
                <span>●</span> Phòng trống (<?= $availableCount ?>)
            </a>
            <a href="index.php?route=rooms&status=rented" class="status-pill pill-rented <?= $currentStatus === 'rented' ? 'active-filter' : '' ?>">
                <span>●</span> Đang thuê (<?= $rentedCount ?>)
            </a>
            <a href="index.php?route=rooms&status=maintenance" class="status-pill pill-maint <?= $currentStatus === 'maintenance' ? 'active-filter' : '' ?>">
                <span>●</span> Bảo trì (<?= $maintenanceCount ?>)
            </a>
        </div>

        <div class="d-flex gap-2">
            <button type="button" class="btn-teal-outline" data-bs-toggle="modal" data-bs-target="#roomTypesModal">
                <i class="bi bi-tags-fill me-1"></i> Loại phòng (4)
            </button>
            <button type="button" class="btn-teal-primary" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                <i class="bi bi-plus-circle-fill me-1"></i> Thêm Phòng
            </button>
        </div>
    </div>

    <!-- Thanh công cụ Tìm kiếm & Lọc chi tiết -->
    <div class="search-bar-card mb-4">
        <form method="GET" action="index.php" class="row g-2 align-items-center">
            <input type="hidden" name="route" value="rooms">
            <div class="col-lg-4 col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="keyword" class="form-control border-start-0" placeholder="Tìm mã phòng, tiện nghi, tên khách thuê..." value="<?= htmlspecialchars($keyword ?? '') ?>">
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <select name="room_type" class="form-select">
                    <option value="">-- Mọi loại phòng --</option>
                    <option value="standard">Phòng Tiêu chuẩn (Standard)</option>
                    <option value="duplex">Phòng Gác lửng (Duplex)</option>
                    <option value="studio">Căn hộ Studio Full Nội thất</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <select name="floor" class="form-select">
                    <option value="">-- Tất cả tầng --</option>
                    <option value="1">Tầng 1</option>
                    <option value="2">Tầng 2</option>
                    <option value="3">Tầng 3</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <select name="status" class="form-select">
                    <option value="">-- Mọi trạng thái --</option>
                    <option value="available" <?= $currentStatus === 'available' ? 'selected' : '' ?>>Phòng trống</option>
                    <option value="rented" <?= $currentStatus === 'rented' ? 'selected' : '' ?>>Đang thuê</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-teal-primary w-100 rounded-3 py-2"><i class="bi bi-search me-1"></i> Lọc</button>
                <a href="index.php?route=rooms" class="btn btn-outline-secondary rounded-3 py-2 px-3">Xóa</a>
            </div>
        </form>
    </div>

    <!-- ==================== 3. LƯỚI THẺ PHÒNG TRỌ (4 CỘT NHƯ ẢNH 1) ==================== -->
    <div class="row g-4">
        <?php if (!empty($rooms)): ?>
            <?php foreach ($rooms as $room): 
                $isAvailable = ($room['status'] ?? 'available') === 'available';
            ?>
                <div class="col-xxl-3 col-xl-3 col-lg-4 col-md-6">
                    <div class="room-card">
                        <div>
                            <!-- Phần đầu thẻ: Mã phòng + Tên phòng + Badge trạng thái -->
                            <div class="d-flex align-items-start justify-content-between gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="room-code-box <?= $isAvailable ? 'is-empty' : '' ?>">
                                        <?= htmlspecialchars($room['room_number']) ?>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="fw-bold" style="font-size: 1rem;">Phòng <?= htmlspecialchars($room['room_number']) ?></span>
                                            <span class="badge bg-light text-secondary border" style="font-size: 0.68rem;">BLD-A</span>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.78rem;">
                                            Tầng 1 &bull; 22m² &bull; <span style="color: var(--teal-dark); font-weight: 600;">Phòng Tiêu chuẩn (Standard)</span>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($isAvailable): ?>
                                    <span class="badge rounded-pill" style="background: #E3F9F6; color: var(--teal-dark); font-size: 0.72rem;">● Trống</span>
                                <?php else: ?>
                                    <span class="badge rounded-pill" style="background: #E6F4EA; color: #1E7E34; font-size: 0.72rem;">● Đang thuê</span>
                                <?php endif; ?>
                            </div>

                            <!-- Khung Giá thuê -->
                            <div class="price-banner">
                                <span class="text-muted small">Giá thuê:</span>
                                <div>
                                    <span class="fw-bold text-danger" style="font-size: 1.08rem;"><?= number_format($room['price'] ?? 0, 0, ',', '.') ?> đ</span>
                                    <span class="text-muted small">/tháng</span>
                                </div>
                            </div>

                            <!-- Dòng tiện nghi -->
                            <div class="text-muted small mb-3 text-truncate" title="<?= htmlspecialchars($room['description'] ?? '') ?>">
                                <i class="bi bi-stars text-warning me-1"></i>
                                <?= htmlspecialchars(!empty($room['description']) ? $room['description'] : 'Máy lạnh, Nước nóng, Wifi, Gác lửng, WC riêng') ?>
                            </div>

                            <!-- Khung thông tin khách thuê hoặc Phòng trống -->
                            <?php if (!$isAvailable): ?>
                                <div class="tenant-box">
                                    <div class="fw-bold small" style="color: var(--teal-dark);">
                                        <i class="bi bi-person-check-fill me-1"></i> Khách đang lưu trú
                                    </div>
                                    <div class="text-muted" style="font-size: 0.78rem;">
                                        <i class="bi bi-telephone me-1"></i> 0912345001 &bull; <span class="text-primary fw-semibold">HD-2026-00<?= $room['id'] ?></span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="empty-room-box">
                                    <div class="fw-bold small" style="color: var(--teal-dark);">
                                        <i class="bi bi-check2-circle me-1"></i> Phòng trống sẵn sàng
                                    </div>
                                    <div class="text-muted" style="font-size: 0.78rem;">Phòng tiêu chuẩn tiết kiệm</div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Chân thẻ: Nút Chỉnh sửa & Nút Xóa -->
                        <div class="d-flex align-items-center gap-2 pt-2 border-top">
                            <button type="button" class="btn-edit-card" data-bs-toggle="modal" data-bs-target="#editRoomModal<?= $room['id'] ?>">
                                <i class="bi bi-pencil-square me-1"></i> Chỉnh sửa
                            </button>
                            <form method="POST" action="index.php?route=rooms" class="m-0" onsubmit="return confirm('Bạn có chắc chắn muốn xóa Phòng <?= htmlspecialchars($room['room_number']) ?>?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $room['id'] ?>">
                                <button type="submit" class="btn-del-card" title="Xóa phòng">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- ==================== MODAL CẬP NHẬT PHÒNG (GIỐNG ẢNH 2) ==================== -->
                <div class="modal fade" id="editRoomModal<?= $room['id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <form method="POST" action="index.php?route=rooms" class="modal-content">
                            <div class="modal-header border-bottom px-4 py-3">
                                <h5 class="modal-title fw-bold">Cập nhật Phòng <?= htmlspecialchars($room['room_number']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?= $room['id'] ?>">

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Tòa nhà <span class="text-danger">*</span></label>
                                        <select class="form-select">
                                            <option selected>Tòa A - Smart Stay Sunrise</option>
                                            <option>Tòa B - Smart Stay Riverside</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Loại phòng <span class="text-danger">*</span></label>
                                        <select class="form-select">
                                            <option selected>Phòng Tiêu chuẩn (Standard) (2.600.000đ)</option>
                                            <option>Phòng Gác lửng (Duplex) (3.200.000đ)</option>
                                            <option>Căn hộ Studio Full Nội thất (3.800.000đ)</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Mã phòng <span class="text-danger">*</span></label>
                                        <input type="text" name="room_number" class="form-control" value="<?= htmlspecialchars($room['room_number']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Tầng <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" value="1" min="1">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Diện tích (m²) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" class="form-control" value="22.00">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Số người tối đa <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" value="2" min="1">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Giá thuê (VNĐ/tháng) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" name="price" class="form-control" value="<?= htmlspecialchars($room['price']) ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold small">Trạng thái</label>
                                        <select name="status" class="form-select">
                                            <option value="available" <?= ($room['status'] ?? '') === 'available' ? 'selected' : '' ?>>Phòng trống</option>
                                            <option value="rented" <?= ($room['status'] ?? '') === 'rented' ? 'selected' : '' ?>>Đang thuê</option>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold small">Tiện nghi trong phòng</label>
                                        <input type="text" name="description" class="form-control" value="<?= htmlspecialchars(!empty($room['description']) ? $room['description'] : 'Máy lạnh, Nước nóng, Wifi, Gác lửng, WC riêng') ?>">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold small">Ghi chú / Mô tả</label>
                                        <textarea class="form-control" rows="2">Phòng tầng trệt thoáng mát, gần cổng chính</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0 px-4 pb-4 pt-0">
                                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                                <button type="submit" class="btn btn-teal-primary rounded-pill px-4">Lưu thông tin</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="bg-white rounded-4 p-5 text-center shadow-sm border">
                    <i class="bi bi-door-closed display-4 text-muted"></i>
                    <h5 class="mt-3 fw-bold">Chưa có phòng trọ nào</h5>
                    <p class="text-muted mb-3">Hãy bấm nút "Thêm Phòng" ở góc phải bên trên để tạo phòng trọ đầu tiên.</p>
                    <button class="btn btn-teal-primary" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                        <i class="bi bi-plus-lg me-1"></i> Thêm Phòng Ngay
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ==================== MODAL THÊM PHÒNG TRỌ MỚI (GIỐNG ẢNH 3) ==================== -->
<div class="modal fade" id="addRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="POST" action="index.php?route=rooms" class="modal-content">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold">Thêm Phòng trọ mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="action" value="create">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Tòa nhà <span class="text-danger">*</span></label>
                        <select class="form-select">
                            <option selected>Tòa A - Smart Stay Sunrise</option>
                            <option>Tòa B - Smart Stay Riverside</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Loại phòng <span class="text-danger">*</span></label>
                        <select class="form-select">
                            <option selected>Phòng Tiêu chuẩn (Standard) (2.600.000đ)</option>
                            <option>Phòng Gác lửng (Duplex) (3.200.000đ)</option>
                            <option>Căn hộ Studio Full Nội thất (3.800.000đ)</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Mã phòng <span class="text-danger">*</span></label>
                        <input type="text" name="room_number" class="form-control" placeholder="VD: P104" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Tầng <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" value="1" min="1">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Diện tích (m²) <span class="text-danger">*</span></label>
                        <input type="number" step="0.1" class="form-control" value="22">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Số người tối đa <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" value="2" min="1">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Giá thuê (VNĐ/tháng) <span class="text-danger">*</span></label>
                        <input type="number" name="price" class="form-control" value="2800000" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="available" selected>Phòng trống</option>
                            <option value="rented">Đang thuê</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold small">Tiện nghi trong phòng</label>
                        <input type="text" name="description" class="form-control" value="Máy lạnh, Nước nóng, Wifi, Kệ bếp, WC riêng">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold small">Ghi chú / Mô tả</label>
                        <textarea class="form-control" rows="2" placeholder="Nhập ghi chú thêm về phòng..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-0">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-teal-primary rounded-pill px-4">Lưu thông tin</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>