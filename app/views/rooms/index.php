<?php
if (!function_exists('h')) {
    function h($s): string {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('money')) {
    function money($n): string {
        return number_format((float)$n, 0, ',', '.') . ' đ';
    }
}

$csrf          = $_SESSION['csrf_token'] ?? '';
$currentStatus = $filters['status'] ?? '';
$currentFloor  = $filters['floor'] ?? '';
$currentPrice  = $filters['price_range'] ?? '';
$keyword       = $filters['keyword'] ?? '';
$hasFilter     = ($keyword !== '' || $currentStatus !== '' || $currentFloor !== '' || $currentPrice !== '');

$totalRooms    = (int)($stats['total'] ?? 0);
$rentedRooms   = (int)($stats['rented'] ?? 0);
$availRooms    = (int)($stats['available'] ?? 0);

$adminName = $_SESSION['full_name'] ?? 'Quản trị viên Hệ thống';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayMate - Quản lý Phòng trọ</title>
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
                url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23e6f4f1" fill-opacity="0.8" d="M0,160L48,170.7C96,181,192,203,288,197.3C384,192,480,160,576,149.3C672,139,768,149,864,170.7C960,192,1056,224,1152,213.3C1248,203,1344,149,1392,122.7L1440,96L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0C192,0,96,0,48,0L0,0Z"></path></svg>'),
                url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 300"><path d="M0,150 Q50,100 100,250" stroke="%2334d399" stroke-width="4" fill="none"/><path d="M10,120 Q40,90 60,110 Q30,140 10,120" fill="%236ee7b7"/><path d="M30,180 Q80,140 90,170 Q50,200 30,180" fill="%2310b981"/><path d="M60,220 Q100,190 110,220 Q70,250 60,220" fill="%23059669"/></svg>'),
                url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 300"><path d="M200,150 Q150,100 100,250" stroke="%2334d399" stroke-width="4" fill="none"/><path d="M190,120 Q160,90 140,110 Q170,140 190,120" fill="%236ee7b7"/><path d="M170,180 Q120,140 110,170 Q150,200 170,180" fill="%2310b981"/><path d="M140,220 Q100,190 90,220 Q130,250 140,220" fill="%23059669"/></svg>');
            background-position: top center, left 20%, right bottom;
            background-repeat: no-repeat;
            background-size: 100% auto, 150px, 150px;
            background-attachment: fixed;
        }

        .text-teal-main { color: var(--teal-main) !important; }

        @keyframes fadeUpIn {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulseDot {
            0%, 100% { transform: scale(1); opacity: 1; }
            50%      { transform: scale(1.35); opacity: 0.65; }
        }

        .anim-fade-up {
            animation: fadeUpIn 0.45s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        /* Navbar */
        .navbar-custom {
            background: #ffffff;
            padding: 12px 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            z-index: 100;
            border-bottom: 1px solid #f1f5f9;
        }
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
        .nav-pills .nav-link:hover { color: var(--teal-main); background: #f0fdfa; }
        .nav-pills .nav-link.active {
            background-color: var(--teal-main);
            color: #ffffff;
            box-shadow: 0 6px 15px rgba(48, 143, 134, 0.35);
        }

        .avatar-circle {
            width: 46px;
            height: 46px;
            background-color: var(--teal-main);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 900;
            font-size: 1rem;
            border: 3px solid #ffffff;
            box-shadow: 0 6px 15px rgba(48, 143, 134, 0.4);
        }

        .btn-hamburger {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: #f0fdfa;
            border: 2px solid #99f6e4;
            color: var(--teal-main);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        /* Section Box */
        .section-box {
            background: rgba(255, 255, 255, 0.96);
            border-radius: 30px;
            border: 4px solid #ffffff;
            padding: 26px 28px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
            position: relative;
            z-index: 2;
            margin-bottom: 24px;
        }

        .btn-teal-custom {
            background: var(--teal-main);
            border-radius: 50px;
            font-weight: 800;
            color: white;
            padding: 10px 24px;
            border: 2px solid var(--teal-hover);
            box-shadow: 0 8px 20px rgba(48, 143, 134, 0.35);
            font-size: 0.9rem;
            transition: all 0.22s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-teal-custom:hover {
            background: var(--teal-hover);
            color: white;
            transform: translateY(-2px);
        }

        .btn-pill-white {
            background: #ffffff;
            border-radius: 50px;
            font-weight: 800;
            color: #334155;
            padding: 9px 20px;
            border: 2px solid #e2e8f0;
            font-size: 0.88rem;
            transition: all 0.22s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }
        .btn-pill-white:hover {
            border-color: var(--teal-main);
            color: var(--teal-main);
            background: #f0fdfa;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 20px;
            border-radius: 50px;
            font-size: 0.88rem;
            font-weight: 800;
            text-decoration: none;
            transition: all 0.22s ease;
            border: 2px solid transparent;
        }
        .status-pill:hover { transform: translateY(-2px); }
        .pill-all    { background: #f1f5f9; color: #334155; border-color: #cbd5e1; }
        .pill-empty  { background: #f0fdfa; color: var(--teal-main); border-color: #99f6e4; }
        .pill-rented { background: #dcfce7; color: #15803d; border-color: #86efac; }

        .status-pill.active-filter {
            background: var(--teal-main);
            color: #ffffff;
            border-color: var(--teal-hover);
            box-shadow: 0 6px 16px rgba(48, 143, 134, 0.32);
        }

        .filter-control {
            border-radius: 50px;
            border: 2px solid #e2e8f0;
            padding: 9px 18px;
            font-weight: 700;
            font-size: 0.88rem;
            color: #334155;
            background-color: #f8fafc;
            transition: all 0.2s;
        }
        select.filter-control {
            padding-right: 36px !important;
        }
        .filter-control:focus {
            background-color: #ffffff;
            border-color: var(--teal-main);
            box-shadow: 0 0 0 4px rgba(48, 143, 134, 0.15);
            outline: none;
        }

        /* Room Card */
        .room-card {
            background: #ffffff;
            border-radius: 26px;
            border: 2.5px solid #e2e8f0;
            padding: 22px;
            transition: all 0.3s cubic-bezier(0.22, 1, 0.36, 1);
            position: relative;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.05);
            overflow: hidden;
        }
        .room-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 5px;
            background: linear-gradient(90deg, var(--teal-main), #34d399);
            opacity: 0.85;
        }
        .room-card.is-available::before {
            background: linear-gradient(90deg, #0284c7, #38bdf8);
        }
        .room-card:hover {
            transform: translateY(-7px);
            box-shadow: 0 22px 45px rgba(48, 143, 134, 0.16);
            border-color: var(--teal-main);
        }

        .room-code-box {
            width: 52px; height: 52px; border-radius: 18px;
            background: linear-gradient(135deg, var(--teal-main), var(--teal-hover));
            color: #ffffff; font-weight: 900; font-size: 0.95rem;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; box-shadow: 0 6px 15px rgba(48, 143, 134, 0.3);
            border: 2px solid #ffffff;
        }
        .room-code-box.is-available {
            background: linear-gradient(135deg, #0284c7, #38bdf8);
            box-shadow: 0 6px 15px rgba(2, 132, 199, 0.28);
        }

        .badge-status-room {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 13px; border-radius: 50px; font-size: 0.76rem;
            font-weight: 800; white-space: nowrap;
        }
        .badge-status-rented { background: #dcfce7; color: #15803d; border: 1.5px solid #86efac; }
        .badge-status-empty  { background: #e0f2fe; color: #0369a1; border: 1.5px solid #7dd3fc; }
        .status-dot-live { width: 7px; height: 7px; border-radius: 50%; display: inline-block; animation: pulseDot 2s infinite; }

        .price-banner {
            background: linear-gradient(135deg, #f0fdfa 0%, #ecfdf5 100%);
            border: 1.5px solid #ccfbf1; border-radius: 18px; padding: 12px 16px;
            display: flex; justify-content: space-between; align-items: center; margin: 16px 0 12px 0;
        }
        .price-val { font-weight: 900; font-size: 1.2rem; color: var(--teal-main); letter-spacing: -0.3px; }

        .desc-box {
            color: #475569; font-weight: 600; font-size: 0.85rem; margin-bottom: 14px;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
            overflow: hidden; min-height: 2.5em;
        }

        .tenant-box {
            background: linear-gradient(135deg, #f8fafc 0%, #f0fdf4 100%);
            border: 1.5px solid #bbf7d0; border-radius: 18px; padding: 12px 15px; margin-bottom: 14px;
        }
        .empty-room-box {
            background: linear-gradient(135deg, #f8fafc 0%, #f0f9ff 100%);
            border: 1.5px dashed #7dd3fc; border-radius: 18px; padding: 12px 15px; margin-bottom: 14px;
        }

        .code-link-pill {
            font-weight: 800; color: var(--teal-main); background: #ffffff;
            border: 1.5px solid #99f6e4; border-radius: 50px; padding: 2px 10px; font-size: 0.76rem;
            text-decoration: none; transition: all 0.2s; display: inline-flex; align-items: center; gap: 4px;
        }
        .code-link-pill:hover { background: var(--teal-main); color: #ffffff; }

        .quick-link-chip {
            width: 36px; height: 36px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
            text-decoration: none; font-size: 0.85rem; border: 1.5px solid #e2e8f0; background: #f8fafc; color: #475569; transition: all 0.2s ease;
        }
        .quick-link-chip:hover { background: #f0fdfa; color: var(--teal-main); border-color: #99f6e4; transform: translateY(-2px); }

        .btn-edit-card {
            border: 2px solid #cbd5e1; border-radius: 50px; color: #334155; font-weight: 800; font-size: 0.84rem;
            padding: 7px 14px; background: #ffffff; transition: all 0.2s ease; flex-grow: 1; display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        }
        .btn-edit-card:hover { border-color: var(--teal-main); color: #ffffff; background: var(--teal-main); box-shadow: 0 6px 14px rgba(48, 143, 134, 0.28); }

        .btn-del-card {
            width: 38px; height: 38px; border-radius: 50%; border: 1.5px solid #fecaca; color: #dc2626; background: #fef2f2;
            display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s ease; flex-shrink: 0;
        }
        .btn-del-card:hover { background: #dc2626; color: #ffffff; border-color: #dc2626; transform: translateY(-2px); }

        /* Modal style matching user design */
        .modal-content {
            border-radius: 20px !important;
            border: 0 !important;
            box-shadow: 0 25px 60px rgba(0,0,0,0.22) !important;
            overflow: hidden;
        }

        .footer-custom {
            background: #ffffff; padding: 20px 0; color: #64748b; font-size: 0.85rem; font-weight: 700;
            margin-top: auto; border-top: 1px solid #f1f5f9; position: relative; z-index: 10;
        }
    </style>
</head>
<body>

    <!-- ================= NAVBAR ================= -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container-fluid px-3 px-xl-5">
            <a class="brand-logo me-4" href="index.php?route=dashboard">
                <i class="fa-solid fa-house-chimney text-teal-main"></i>
                <div>
                    <div class="brand-text">StayMate</div>
                    <div class="brand-sub">Quản Lý Nhà Trọ</div>
                </div>
            </a>

            <div class="d-flex align-items-center gap-3 order-lg-3">
                <a href="index.php?route=invoices&status=unpaid" class="position-relative text-decoration-none" style="color: #64748b;" title="Hóa đơn chưa thu">
                    <i class="fa-solid fa-bell fs-4"></i>
                </a>

                <div class="dropdown">
                    <div class="d-flex align-items-center gap-2 cursor-pointer" data-bs-toggle="dropdown" style="cursor: pointer;">
                        <div class="avatar-circle">AS</div>
                        <div class="text-start d-none d-sm-block lh-sm">
                            <div class="fw-bold text-dark" style="font-size: 0.9rem;"><?= htmlspecialchars($adminName) ?> <i class="fa-solid fa-angle-down ms-1 text-muted fs-6"></i></div>
                            <div class="text-muted text-capitalize" style="font-size: 0.75rem;"><?= htmlspecialchars($_SESSION['role'] ?? 'admin') ?></div>
                        </div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 mt-2 p-2">
                        <li>
                            <button type="button" class="dropdown-item rounded-3 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#profileModal">
                                <i class="fa-solid fa-id-card me-2 text-teal-main" style="color:#308f86;"></i> Thông tin tài khoản
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

                <button class="btn-hamburger d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#staymateMobileNav">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>

            <div class="collapse navbar-collapse mobile-nav-collapse order-lg-2" id="staymateMobileNav">
                <ul class="nav nav-pills flex-nowrap me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php?route=overview"><i class="fa-solid fa-border-all"></i> Tổng quan</a></li>
                    <li class="nav-item"><a class="nav-link active" href="index.php?route=rooms"><i class="fa-solid fa-bed"></i> Phòng trọ</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?route=tenants"><i class="fa-solid fa-users"></i> Khách thuê</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?route=contracts"><i class="fa-solid fa-file-signature"></i> Hợp đồng</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?route=utilities"><i class="fa-solid fa-bolt"></i> Điện Nước</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?route=showcase" target="_blank"><i class="fa-regular fa-eye"></i> Xem phòng khách</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ================= NỘI DUNG CHÍNH ================= -->
    <div class="container-fluid px-4 px-xl-5 py-4 position-relative">

        <!-- Dòng Quản lý Phòng trọ & Nút Thêm phòng trọ (Đúng yêu cầu hiển thị tối giản) -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <h2 class="fw-black text-dark mb-0" style="font-weight: 900; font-size: 1.85rem;">Quản lý Phòng trọ - Danh sách &amp; Trạng thái phòng</h2>
            <button type="button" class="btn-teal-custom" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                <i class="fa-solid fa-plus"></i> Thêm phòng trọ
            </button>
        </div>

        <!-- Thông báo Flash -->
        <?php if (!empty($flash_success)): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 py-3 shadow-sm fw-bold border-0 mb-4 position-relative z-2" style="background: #dcfce7; color: #166534;">
                <i class="fa-solid fa-circle-check me-2"></i><?= h($flash_success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($flash_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-pill px-4 py-3 shadow-sm fw-bold border-0 mb-4 position-relative z-2" style="background: #fee2e2; color: #991b1b;">
                <i class="fa-solid fa-triangle-exclamation me-2"></i><?= h($flash_error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- ================= KHUNG BỘ LỌC & LƯỚI PHÒNG TRỌ ================= -->
        <div class="section-box anim-fade-up">
            <!-- Hàng nút Pill lọc nhanh trạng thái -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pb-3 mb-4 border-bottom border-light">
                <div class="d-flex flex-wrap gap-2">
                    <a href="index.php?route=rooms" class="status-pill pill-all <?= $currentStatus === '' ? 'active-filter' : '' ?>">
                        <i class="fa-solid fa-border-all"></i> Tất cả phòng (<?= $totalRooms ?>)
                    </a>
                    <a href="index.php?route=rooms&status=available" class="status-pill pill-empty <?= $currentStatus === 'available' ? 'active-filter' : '' ?>">
                        <i class="fa-solid fa-door-open"></i> Phòng trống (<?= $availRooms ?>)
                    </a>
                    <a href="index.php?route=rooms&status=rented" class="status-pill pill-rented <?= $currentStatus === 'rented' ? 'active-filter' : '' ?>">
                        <i class="fa-solid fa-user-check"></i> Đang thuê (<?= $rentedRooms ?>)
                    </a>
                </div>
            </div>

            <!-- Thanh tìm kiếm & lọc chi tiết -->
            <form method="GET" action="index.php" class="row g-3 align-items-end mb-4">
                <input type="hidden" name="route" value="rooms">
                <div class="col-12 col-lg-4">
                    <label class="form-label fw-bold text-teal-main text-uppercase mb-1" style="font-size: 0.75rem;">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Từ khóa tìm kiếm
                    </label>
                    <input type="text" name="keyword" class="form-control filter-control"
                           placeholder="Tìm số phòng (P101), mô tả, tên hoặc SĐT khách..."
                           value="<?= h($keyword) ?>">
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label fw-bold text-muted text-uppercase mb-1" style="font-size: 0.75rem;">Khu vực tầng</label>
                    <select name="floor" class="form-select filter-control">
                        <option value="">-- Tất cả tầng --</option>
                        <?php foreach ($floors as $fl): ?>
                            <option value="<?= (int)$fl ?>" <?= $currentFloor === (string)$fl ? 'selected' : '' ?>>
                                Tầng <?= (int)$fl ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label fw-bold text-muted text-uppercase mb-1" style="font-size: 0.75rem;">Mức giá thuê</label>
                    <select name="price_range" class="form-select filter-control">
                        <option value="">-- Mọi mức giá --</option>
                        <option value="under3m" <?= $currentPrice === 'under3m' ? 'selected' : '' ?>>Đến 3.000.000 đ</option>
                        <option value="3m_4m" <?= $currentPrice === '3m_4m' ? 'selected' : '' ?>>3.000.000 - 4.000.000 đ</option>
                        <option value="over4m" <?= $currentPrice === 'over4m' ? 'selected' : '' ?>>Trên 4.000.000 đ</option>
                    </select>
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label fw-bold text-muted text-uppercase mb-1" style="font-size: 0.75rem;">Trạng thái</label>
                    <select name="status" class="form-select filter-control">
                        <option value="">-- Tất cả --</option>
                        <option value="available" <?= $currentStatus === 'available' ? 'selected' : '' ?>>Phòng trống</option>
                        <option value="rented" <?= $currentStatus === 'rented' ? 'selected' : '' ?>>Đang thuê</option>
                    </select>
                </div>

                <div class="col-6 col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn-teal-custom flex-grow-1 justify-content-center py-2 px-3">
                        <i class="fa-solid fa-filter"></i> Lọc
                    </button>
                    <?php if ($hasFilter): ?>
                        <a href="index.php?route=rooms" class="btn-pill-white py-2 px-3" title="Xóa bộ lọc">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Lưới Thẻ Phòng Trọ -->
            <div class="row g-4">
                <?php if (!empty($rooms)): ?>
                    <?php foreach ($rooms as $idx => $room):
                        $isAvailable = ($room['status'] ?? 'available') === 'available';
                        $roomId      = (int)$room['id'];
                        $unpaidCnt   = (int)($room['unpaid_count'] ?? 0);
                        $delayStyle  = 'animation-delay: ' . min(0.35, $idx * 0.05) . 's;';
                    ?>
                        <div class="col-xxl-4 col-xl-4 col-lg-6 col-md-6 anim-fade-up" style="<?= $delayStyle ?>">
                            <div class="room-card <?= $isAvailable ? 'is-available' : '' ?>">
                                <div>
                                    <!-- Đầu thẻ: Mã phòng + Tầng + Trạng thái -->
                                    <div class="d-flex align-items-start justify-content-between gap-2">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="room-code-box <?= $isAvailable ? 'is-available' : '' ?>">
                                                <?= h($room['room_number']) ?>
                                            </div>
                                            <div>
                                                <h5 class="fw-black mb-1 text-dark" style="font-weight: 900; font-size: 1.12rem;">
                                                    Phòng <?= h($room['room_number']) ?>
                                                </h5>
                                                <div class="text-muted fw-bold" style="font-size: 0.8rem;">
                                                    <i class="fa-solid fa-layer-group text-teal-main me-1"></i><?= h($room['floor_label']) ?>
                                                    &bull; ID #<?= $roomId ?>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if ($isAvailable): ?>
                                            <span class="badge-status-room badge-status-empty">
                                                <span class="status-dot-live" style="background: #0284c7;"></span> Phòng trống
                                            </span>
                                        <?php else: ?>
                                            <span class="badge-status-room badge-status-rented">
                                                <span class="status-dot-live" style="background: #16a34a;"></span> Đang thuê
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Giá thuê tháng -->
                                    <div class="price-banner">
                                        <span class="fw-bold text-muted small">
                                            <i class="fa-solid fa-tag text-teal-main me-1"></i> Giá thuê niêm yết
                                        </span>
                                        <div>
                                            <span class="price-val"><?= money($room['price']) ?></span>
                                            <span class="text-muted fw-bold small">/tháng</span>
                                        </div>
                                    </div>

                                    <!-- Mô tả phòng từ CSDL -->
                                    <div class="desc-box" title="<?= h($room['description'] ?? '') ?>">
                                        <i class="fa-solid fa-sparkles text-warning me-1"></i>
                                        <?= h(!empty($room['description']) ? $room['description'] : 'Phòng tiêu chuẩn đầy đủ tiện nghi cơ bản') ?>
                                    </div>

                                    <!-- Thông tin Khách thuê & Hợp đồng thực tế từ CSDL -->
                                    <?php if (!$isAvailable): ?>
                                        <div class="tenant-box">
                                            <?php if (!empty($room['contract_id'])): ?>
                                                <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                                                    <a href="index.php?route=tenants&keyword=<?= urlencode($room['tenant_phone'] ?? $room['tenant_name'] ?? '') ?>"
                                                       class="fw-black text-decoration-none text-dark text-truncate"
                                                       style="font-weight: 800; font-size: 0.9rem;">
                                                        <i class="fa-solid fa-user-check text-success me-1"></i><?= h($room['tenant_name']) ?>
                                                    </a>
                                                    <a href="index.php?route=contracts/detail&id=<?= (int)$room['contract_id'] ?>"
                                                       class="code-link-pill" title="Xem chi tiết hợp đồng">
                                                        <i class="fa-solid fa-file-contract"></i> <?= h($room['contract_code']) ?>
                                                    </a>
                                                </div>
                                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-1 text-muted fw-bold" style="font-size: 0.78rem;">
                                                    <span><i class="fa-solid fa-phone me-1 text-teal-main"></i><?= h($room['tenant_phone']) ?></span>
                                                    <span>
                                                        <i class="fa-regular fa-calendar me-1"></i>
                                                        <?= date('d/m/Y', strtotime($room['contract_start'])) ?> → <?= date('d/m/Y', strtotime($room['contract_end'])) ?>
                                                    </span>
                                                </div>
                                                <?php if (!empty($room['roommate_name'])): ?>
                                                    <div class="mt-1 pt-1 border-top border-light small text-dark fw-bold">
                                                        <i class="fa-solid fa-user-group text-teal-main me-1"></i>Ở cùng: <?= h($room['roommate_name']) ?>
                                                        <?php if (!empty($room['roommate_phone'])): ?>
                                                            <span class="text-muted">(<?= h($room['roommate_phone']) ?>)</span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($unpaidCnt > 0): ?>
                                                    <div class="mt-2 pt-2 border-top border-success-subtle d-flex justify-content-between align-items-center" style="font-size: 0.78rem;">
                                                        <span class="text-danger fw-bold">
                                                            <i class="fa-solid fa-circle-exclamation me-1"></i>Nợ <?= $unpaidCnt ?> hóa đơn (<?= money($room['unpaid_amount']) ?>)
                                                        </span>
                                                        <a href="index.php?route=invoices&room_id=<?= $roomId ?>&period=all" class="fw-bold text-danger text-decoration-none">
                                                            Thu ngay →
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <div class="fw-bold text-dark" style="font-size: 0.88rem;">
                                                            <i class="fa-solid fa-user-clock text-warning me-1"></i> Đang thuê (Chưa gắn HĐ active)
                                                        </div>
                                                        <div class="text-muted fw-semibold" style="font-size: 0.78rem;">
                                                            Tạo hợp đồng để đồng bộ khách thuê &amp; hóa đơn
                                                        </div>
                                                    </div>
                                                    <a href="index.php?route=contracts" class="code-link-pill">
                                                        <i class="fa-solid fa-plus"></i> Lập HĐ
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="empty-room-box d-flex justify-content-between align-items-center gap-2">
                                            <div>
                                                <div class="fw-bold" style="color: #0369a1; font-size: 0.88rem;">
                                                    <i class="fa-solid fa-circle-check me-1"></i> Phòng trống sẵn sàng đón khách
                                                </div>
                                                <div class="text-muted fw-semibold" style="font-size: 0.78rem;">
                                                    Đang hiển thị công khai trên trang Xem phòng
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column gap-1 align-items-end">
                                                <a href="index.php?route=tenants&room_id=<?= $roomId ?>" class="btn-teal-custom py-1 px-3 text-decoration-none shadow-sm" style="font-size: 0.8rem; font-weight: 800;">
                                                    <i class="fa-solid fa-user-plus me-1"></i> Thêm khách
                                                </a>
                                                <a href="index.php?route=contracts" class="code-link-pill" title="Lập hợp đồng cho phòng này">
                                                    <i class="fa-solid fa-file-signature"></i> Tạo HĐ
                                                </a>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Chân thẻ: Liên kết nhanh Điện nước / Hóa đơn + Sửa + Xóa -->
                                <div class="d-flex align-items-center gap-2 pt-3 border-top border-light">
                                    <a href="index.php?route=utilities&room_id=<?= $roomId ?>&period=all"
                                       class="quick-link-chip" title="Xem chỉ số Điện Nước phòng <?= h($room['room_number']) ?>">
                                        <i class="fa-solid fa-bolt text-warning"></i>
                                    </a>
                                    <a href="index.php?route=invoices&room_id=<?= $roomId ?>&period=all"
                                       class="quick-link-chip" title="Xem Hóa đơn phòng <?= h($room['room_number']) ?>">
                                        <i class="fa-solid fa-file-invoice-dollar text-teal-main"></i>
                                    </a>

                                    <button type="button" class="btn-edit-card" data-bs-toggle="modal" data-bs-target="#editRoomModal<?= $roomId ?>">
                                        <i class="fa-solid fa-pen-to-square"></i> Chỉnh sửa
                                    </button>

                                    <form method="POST" action="index.php?route=rooms" class="m-0"
                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa Phòng <?= h($room['room_number']) ?> khỏi CSDL?');">
                                        <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $roomId ?>">
                                        <button type="submit" class="btn-del-card" title="Xóa phòng <?= h($room['room_number']) ?>">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <h5 class="fw-black text-dark mb-1" style="font-weight: 900;">Không tìm thấy phòng trọ phù hợp</h5>
                        <p class="text-muted fw-semibold mb-3">Thử thay đổi điều kiện lọc hoặc thêm phòng trọ mới vào hệ thống.</p>
                        <div class="d-flex justify-content-center gap-2">
                            <?php if ($hasFilter): ?>
                                <a href="index.php?route=rooms" class="btn-pill-white">
                                    <i class="fa-solid fa-rotate-left"></i> Xem tất cả phòng
                                </a>
                            <?php endif; ?>
                            <button type="button" class="btn-teal-custom" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                                <i class="fa-solid fa-plus"></i> Thêm Phòng Mới
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ================= MODAL THÊM PHÒNG TRỌ MỚI (ĐÚNG THIẾT KẾ ẢNH 2) ================= -->
    <div class="modal fade" id="addRoomModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form method="POST" action="index.php?route=rooms" class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 pb-0 px-4 pt-4">
                    <h5 class="modal-title fw-bold text-dark" style="font-size: 1.25rem;">Thêm Phòng trọ mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body px-4 py-3">
                    <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                    <input type="hidden" name="action" value="create">

                    <div class="row g-3">
                        <!-- Tòa nhà & Loại phòng -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Tòa nhà <span class="text-danger">*</span></label>
                            <select name="building" class="form-select rounded-3 py-2" required>
                                <option value="Tòa A - Smart Stay Sunrise" selected>Tòa A - Smart Stay Sunrise</option>
                                <option value="Tòa B - StayMate Central">Tòa B - StayMate Central</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Loại phòng <span class="text-danger">*</span></label>
                            <select name="room_type" class="form-select rounded-3 py-2" required>
                                <option value="Phòng Tiêu chuẩn (Standard) (2.600.000đ)" selected>Phòng Tiêu chuẩn (Standard) (2.600.000đ)</option>
                                <option value="Phòng Cao cấp (VIP) (3.500.000đ)">Phòng Cao cấp (VIP) (3.500.000đ)</option>
                                <option value="Phòng Studio (4.000.000đ)">Phòng Studio (4.000.000đ)</option>
                            </select>
                        </div>

                        <!-- Mã phòng & Tầng -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Mã phòng <span class="text-danger">*</span></label>
                            <input type="text" name="room_number" class="form-control rounded-3 py-2" placeholder="VD: P104" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Tầng <span class="text-danger">*</span></label>
                            <input type="number" name="floor" class="form-control rounded-3 py-2" value="1" required>
                        </div>

                        <!-- Diện tích & Số người tối đa -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Diện tích (m²) <span class="text-danger">*</span></label>
                            <input type="number" step="0.1" name="area" class="form-control rounded-3 py-2" value="22" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Số người tối đa <span class="text-danger">*</span></label>
                            <input type="number" name="max_occupants" class="form-control rounded-3 py-2" value="2" required>
                        </div>

                        <!-- Giá thuê & Trạng thái -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Giá thuê (VNĐ/tháng) <span class="text-danger">*</span></label>
                            <input type="number" step="1000" name="price" class="form-control rounded-3 py-2" value="2800000" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Trạng thái</label>
                            <select name="status" class="form-select rounded-3 py-2">
                                <option value="available" selected>Phòng trống</option>
                                <option value="rented">Đang thuê</option>
                            </select>
                        </div>

                        <!-- Tiện nghi trong phòng -->
                        <div class="col-12">
                            <label class="form-label fw-bold small text-dark">Tiện nghi trong phòng</label>
                            <input type="text" name="amenities" class="form-control rounded-3 py-2" value="Máy lạnh, Nước nóng, Wifi, Kệ bếp, WC riêng">
                        </div>

                        <!-- Ghi chú / Mô tả -->
                        <div class="col-12">
                            <label class="form-label fw-bold small text-dark">Ghi chú / Mô tả</label>
                            <textarea name="description" class="form-control rounded-3 py-2" rows="3" placeholder=""></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-1 d-flex justify-content-end gap-2">
                    <button type="button" class="btn text-white px-4 py-2" style="background: #64748b; border-radius: 10px; font-weight: 700; font-size: 0.95rem;" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn text-white px-4 py-2" style="background: #5b4bf7; border-radius: 10px; font-weight: 700; font-size: 0.95rem;">Lưu thông tin</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= TẤT CẢ MODAL CẬP NHẬT PHÒNG (ĐẶT Ở ROOT TRÁNH LỖI ĐEN MÀN HÌNH / STACKING CONTEXT) ================= -->
    <?php if (!empty($rooms)): ?>
        <?php foreach ($rooms as $r):
            $rId = (int)$r['id'];
            $rAvail = ($r['status'] === 'available');
            $d = $r['details'] ?? RoomModel::parseRoomDetails($r);
            $rServices = $r['services'] ?? [];
        ?>
        <div class="modal fade" id="editRoomModal<?= $rId ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <form method="POST" action="index.php?route=rooms" class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                    <div class="modal-header border-0 pb-0 px-4 pt-4">
                        <h5 class="modal-title fw-bold text-dark" style="font-size: 1.25rem;">
                            Cập nhật Phòng <?= h($r['room_number']) ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                    </div>
                    <div class="modal-body px-4 py-3">
                        <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $rId ?>">

                        <div class="row g-3">
                            <!-- Tòa nhà & Loại phòng -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark">Tòa nhà <span class="text-danger">*</span></label>
                                <select name="building" class="form-select rounded-3 py-2" required>
                                    <option value="Tòa A - Smart Stay Sunrise" <?= $d['building'] === 'Tòa A - Smart Stay Sunrise' ? 'selected' : '' ?>>Tòa A - Smart Stay Sunrise</option>
                                    <option value="Tòa B - StayMate Central" <?= $d['building'] === 'Tòa B - StayMate Central' ? 'selected' : '' ?>>Tòa B - StayMate Central</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark">Loại phòng <span class="text-danger">*</span></label>
                                <select name="room_type" class="form-select rounded-3 py-2" required>
                                    <option value="Phòng Tiêu chuẩn (Standard) (2.600.000đ)" <?= str_contains($d['room_type'], 'Tiêu chuẩn') ? 'selected' : '' ?>>Phòng Tiêu chuẩn (Standard) (2.600.000đ)</option>
                                    <option value="Phòng Cao cấp (VIP) (3.500.000đ)" <?= str_contains($d['room_type'], 'Cao cấp') ? 'selected' : '' ?>>Phòng Cao cấp (VIP) (3.500.000đ)</option>
                                    <option value="Phòng Studio (4.000.000đ)" <?= str_contains($d['room_type'], 'Studio') ? 'selected' : '' ?>>Phòng Studio (4.000.000đ)</option>
                                </select>
                            </div>

                            <!-- Mã phòng & Tầng -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark">Mã phòng <span class="text-danger">*</span></label>
                                <input type="text" name="room_number" class="form-control rounded-3 py-2" value="<?= h($r['room_number']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark">Tầng <span class="text-danger">*</span></label>
                                <input type="number" name="floor" class="form-control rounded-3 py-2" value="<?= (int)$d['floor'] ?>" required>
                            </div>

                            <!-- Diện tích & Số người tối đa -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark">Diện tích (m²) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="area" class="form-control rounded-3 py-2" value="<?= number_format($d['area'], 2, '.', '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark">Số người tối đa <span class="text-danger">*</span></label>
                                <input type="number" name="max_occupants" class="form-control rounded-3 py-2" value="<?= (int)$d['max_occupants'] ?>" required>
                            </div>

                            <!-- Giá thuê & Trạng thái -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark">Giá thuê (VNĐ/tháng) <span class="text-danger">*</span></label>
                                <input type="number" step="1000" name="price" class="form-control rounded-3 py-2" value="<?= number_format($r['price'], 2, '.', '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark">Trạng thái</label>
                                <select name="status" class="form-select rounded-3 py-2">
                                    <option value="available" <?= $rAvail ? 'selected' : '' ?>>Phòng trống</option>
                                    <option value="rented" <?= !$rAvail ? 'selected' : '' ?>>Đang thuê</option>
                                </select>
                            </div>

                            <!-- Tiện nghi trong phòng -->
                            <div class="col-12">
                                <label class="form-label fw-bold small text-dark">Tiện nghi trong phòng</label>
                                <input type="text" name="amenities" class="form-control rounded-3 py-2" value="<?= h($d['amenities']) ?>">
                            </div>

                            <!-- Ghi chú / Mô tả -->
                            <div class="col-12">
                                <label class="form-label fw-bold small text-dark">Ghi chú / Mô tả</label>
                                <textarea name="description" class="form-control rounded-3 py-2" rows="3"><?= h($d['note']) ?></textarea>
                            </div>

                            <!-- Dịch vụ khác của phòng (Dấu cộng để thêm các dịch vụ khác và lưu lại) -->
                            <div class="col-12 mt-3 pt-2 border-top">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold small text-dark mb-0 d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-layer-group text-teal-main"></i> Dịch vụ khác của phòng
                                    </label>
                                    <button type="button" class="btn btn-sm text-white px-3 py-1" style="background:#5b4bf7; border-radius:8px; font-weight:700; font-size:0.82rem;" onclick="addRoomServiceRow(<?= $rId ?>)">
                                        <i class="fa-solid fa-plus me-1"></i> Thêm dịch vụ
                                    </button>
                                </div>
                                <div id="roomServicesContainer_<?= $rId ?>" class="d-flex flex-column gap-2">
                                    <?php if (!empty($rServices)): ?>
                                        <?php foreach ($rServices as $s): ?>
                                            <div class="service-row d-flex align-items-center gap-2 p-2 rounded-3" style="background:#f8fafc; border:1px solid #e2e8f0;">
                                                <input type="text" name="service_names[]" class="form-control rounded-3 py-1" style="font-size:0.85rem;" placeholder="Tên dịch vụ (VD: Gửi xe, Vệ sinh...)" value="<?= h($s['service_name']) ?>" required>
                                                <div class="input-group" style="max-width: 170px;">
                                                    <input type="number" step="1000" name="service_prices[]" class="form-control rounded-start-3 py-1" style="font-size:0.85rem;" placeholder="Đơn giá" value="<?= (float)$s['unit_price'] ?>" required>
                                                    <span class="input-group-text py-1 px-2 small">đ</span>
                                                </div>
                                                <input type="text" name="service_units[]" class="form-control rounded-3 py-1" style="max-width: 110px; font-size:0.85rem;" placeholder="Đơn vị" value="<?= h($s['unit']) ?>">
                                                <button type="button" class="btn btn-outline-danger btn-sm rounded-3 px-2 py-1" onclick="this.closest('.service-row').remove()" title="Xóa dịch vụ">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-1 d-flex justify-content-end gap-2">
                        <button type="button" class="btn text-white px-4 py-2" style="background: #64748b; border-radius: 10px; font-weight: 700; font-size: 0.95rem;" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn text-white px-4 py-2" style="background: #5b4bf7; border-radius: 10px; font-weight: 700; font-size: 0.95rem;">Lưu thông tin</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- ================= FOOTER ================= -->
    <footer class="footer-custom container-fluid px-4 px-xl-5 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div><i class="fa-solid fa-building me-1" style="color:var(--teal-main);"></i> © 2026 StayMate - Quản lý Nhà trọ Chuyên nghiệp</div>
        <div class="d-flex flex-wrap gap-4">
            <span><i class="fa-solid fa-shield-halved me-1" style="color:var(--teal-main);"></i> WAMP MVC Architecture</span>
            <span><i class="fa-solid fa-bolt me-1 text-warning"></i> Tự động hóa Điện, Nước &amp; Hóa đơn</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- MODAL DOI MAT KHAU -->
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
                        <button type="submit" class="btn btn-teal-custom px-4" style="background:#308f86;color:#fff;border-radius:50px;"><i class="fa-solid fa-check me-1"></i> Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL THONG TIN TAI KHOAN -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light rounded-top-4">
                    <h5 class="modal-title fw-bold text-teal-main"><i class="fa-solid fa-user-shield me-2"></i> Thông tin Tài khoản</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold fs-4" style="width:60px; height:60px; background:#308f86;">AS</div>
                        <div>
                            <h5 class="fw-bold mb-0"><?= htmlspecialchars($adminName) ?></h5>
                            <span class="badge bg-success rounded-pill text-uppercase"><?= htmlspecialchars($_SESSION['role'] ?? 'admin') ?></span>
                        </div>
                    </div>
                    <ul class="list-group list-group-flush border rounded-3">
                        <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Tên đăng nhập:</span> <strong><?= htmlspecialchars($_SESSION['username'] ?? 'admin01') ?></strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Email:</span> <strong><?= htmlspecialchars($_SESSION['email'] ?? 'admin@nhatro.com') ?></strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Số điện thoại:</span> <strong><?= htmlspecialchars($_SESSION['phone'] ?? '0901234567') ?></strong></li>
                    </ul>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                    <a href="index.php?route=auth/logout" class="btn btn-danger rounded-pill px-4"><i class="fa-solid fa-right-from-bracket me-1"></i> Đăng xuất</a>
                </div>
            </div>
        </div>
    </div>

    <script>
    function addRoomServiceRow(roomId) {
        const container = document.getElementById('roomServicesContainer_' + roomId);
        if (!container) return;
        const div = document.createElement('div');
        div.className = 'service-row d-flex align-items-center gap-2 p-2 rounded-3';
        div.style.background = '#f8fafc';
        div.style.border = '1px solid #e2e8f0';
        div.innerHTML = `
            <input type="text" name="service_names[]" class="form-control rounded-3 py-1" style="font-size:0.85rem;" placeholder="Tên dịch vụ (VD: Gửi xe, Vệ sinh...)" required>
            <div class="input-group" style="max-width: 170px;">
                <input type="number" step="1000" name="service_prices[]" class="form-control rounded-start-3 py-1" style="font-size:0.85rem;" placeholder="Đơn giá" value="50000" required>
                <span class="input-group-text py-1 px-2 small">đ</span>
            </div>
            <input type="text" name="service_units[]" class="form-control rounded-3 py-1" style="max-width: 110px; font-size:0.85rem;" placeholder="Đơn vị" value="Tháng">
            <button type="button" class="btn btn-outline-danger btn-sm rounded-3 px-2 py-1" onclick="this.closest('.service-row').remove()" title="Xóa dịch vụ">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        `;
        container.appendChild(div);
    }
    </script>
</body>
</html>
