<?php
if (!function_exists('h')) {
    function h($s): string {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}
$csrf = $_SESSION['csrf_token'] ?? '';
$adminName = $_SESSION['full_name'] ?? 'Quản trị viên Hệ thống';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayMate - Quản lý Khách thuê</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --teal-main: #308f86; --teal-hover: #25756d; --bg-page: #f8fafc; }
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
        .navbar-custom { background: #ffffff; padding: 12px 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); z-index: 100; border-bottom: 1px solid #f1f5f9; }
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
        .nav-pills .nav-link.active { background-color: var(--teal-main); color: #ffffff; box-shadow: 0 6px 15px rgba(48, 143, 134, 0.35); }
        .avatar-circle { width: 46px; height: 46px; background: var(--teal-main); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 900; border: 3px solid #fff; box-shadow: 0 6px 15px rgba(48, 143, 134, 0.4); }
        .btn-hamburger { width: 44px; height: 44px; border-radius: 14px; background: #f0fdfa; border: 2px solid #99f6e4; color: var(--teal-main); display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        @media (max-width: 991.98px) {
            .mobile-nav-collapse { background: #fff; border-radius: 22px; padding: 14px; margin-top: 12px; border: 2px solid #ccfbf1; box-shadow: 0 12px 30px rgba(0,0,0,.08); }
            .mobile-nav-collapse .nav-pills { flex-direction: column; gap: 6px; }
            .mobile-nav-collapse .nav-link { border-radius: 16px; padding: 11px 16px; }
        }
        .btn-back-content { background: #fff; border: 3px solid #fff; color: #334155; font-weight: 800; padding: 10px 26px; border-radius: 50px; transition: .2s; text-decoration: none; display: inline-flex; align-items: center; box-shadow: 0 8px 20px rgba(0,0,0,.08); margin-bottom: 22px; position: relative; z-index: 2; }
        .btn-back-content:hover { border-color: var(--teal-main); color: var(--teal-main); transform: translateY(-3px); }
        .header-title-wrapper { position: relative; z-index: 2; display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 26px; flex-wrap: wrap; gap: 16px; }
        .main-title { font-weight: 900; color: #0f172a; font-size: 2rem; letter-spacing: -0.5px; display: inline-flex; align-items: center; flex-wrap: wrap; }
        .bld-highlight { color: var(--teal-main); margin-left: 8px; position: relative; }
        .title-sparkle { position: absolute; background: #fde047; border-radius: 2px; }
        .ts-1 { width: 4px; height: 12px; top: -10px; right: -15px; transform: rotate(20deg); }
        .ts-2 { width: 12px; height: 4px; top: 15px; right: -25px; transform: rotate(15deg); }
        .header-house-graphic {
            position: absolute; right: 0; top: -50px; width: 300px; height: 200px; z-index: 1; pointer-events: none;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 200"><path d="M0,180 Q150,150 300,180 L300,200 L0,200 Z" fill="%23dcfce7"/><rect x="120" y="80" width="80" height="100" fill="%23e0f2fe"/><polygon points="100,80 160,30 220,80" fill="%230284c7"/><polygon points="160,30 220,80 250,80 190,30" fill="%2338bdf8"/><rect x="140" y="100" width="15" height="20" fill="%23ffffff"/><rect x="165" y="100" width="15" height="20" fill="%23ffffff"/><rect x="140" y="140" width="15" height="40" fill="%23ffffff"/><circle cx="50" cy="140" r="30" fill="%234ade80"/><circle cx="80" cy="150" r="40" fill="%2322c55e"/><circle cx="240" cy="140" r="35" fill="%2322c55e"/><circle cx="270" cy="150" r="25" fill="%2316a34a"/></svg>');
            background-size: contain; background-repeat: no-repeat; background-position: bottom right;
        }
        .overview-card { border-radius: 30px; padding: 24px; position: relative; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,.12); border: 4px solid #fff; height: 100%; transition: .3s; }
        .overview-card:hover { transform: translateY(-7px); box-shadow: 0 25px 45px rgba(0,0,0,.17); }
        .card-green { background: linear-gradient(135deg, #dcfce7, #bbf7d0); }
        .card-blue { background: linear-gradient(135deg, #e0f2fe, #bae6fd); }
        .card-yellow { background: linear-gradient(135deg, #fef9c3, #fef08a); }
        .card-purple { background: linear-gradient(135deg, #f3e8ff, #e9d5ff); }
        .overview-title { font-size: .8rem; font-weight: 900; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; text-transform: uppercase; }
        .overview-val { font-size: 2.2rem; font-weight: 900; line-height: 1; letter-spacing: -1px; }
        .section-box { background: rgba(255,255,255,.96); border-radius: 30px; border: 4px solid #fff; padding: 24px 28px; box-shadow: 0 15px 40px rgba(0,0,0,.08); position: relative; z-index: 2; margin-bottom: 24px; }
        .btn-teal-custom { background: var(--teal-main); border-radius: 50px; font-weight: 800; color: #fff; padding: 10px 24px; border: 2px solid var(--teal-hover); box-shadow: 0 8px 20px rgba(48,143,134,.35); font-size: .9rem; transition: .2s; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; cursor: pointer; }
        .btn-teal-custom:hover { background: var(--teal-hover); color: #fff; transform: translateY(-2px); }
        .btn-pill-white { background: #fff; border-radius: 50px; font-weight: 800; color: #334155; padding: 10px 20px; border: 2px solid #e2e8f0; font-size: .88rem; transition: .2s; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; }
        .btn-pill-white:hover { border-color: var(--teal-main); color: var(--teal-main); background: #f0fdfa; }
        .filter-control { border-radius: 50px; border: 2px solid #e2e8f0; padding: 9px 18px; font-weight: 700; font-size: .88rem; color: #334155; background-color: #f8fafc; }
        select.filter-control { padding-right: 36px !important; }
        .filter-control:focus { background-color: #fff; border-color: var(--teal-main); box-shadow: 0 0 0 4px rgba(48,143,134,.15); outline: none; }
        .table-rounded-wrap { border-radius: 24px; border: 2px solid #e2e8f0; overflow: hidden; background: #fff; }
        .table-staymate thead th { background: #f0fdfa; color: var(--teal-main); font-weight: 900; font-size: .78rem; text-transform: uppercase; padding: 15px 18px; border-bottom: 2px solid #ccfbf1; }
        .table-staymate tbody td { padding: 15px 18px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 600; }
        .table-staymate tbody tr:hover { background: #f8fafc; }
        .badge-room-pill { background: var(--teal-main); color: #fff; padding: 5px 14px; border-radius: 50px; font-weight: 800; font-size: .8rem; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
        .badge-room-pill:hover { background: var(--teal-hover); color: #fff; }
        .btn-icon-circle { width: 36px; height: 36px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; border: 1.5px solid transparent; transition: .2s; background: none; }
        .btn-icon-edit { background: #f0fdfa; color: var(--teal-main); border-color: #99f6e4; }
        .btn-icon-edit:hover { background: var(--teal-main); color: #fff; }
        .btn-icon-del { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
        .btn-icon-del:hover { background: #dc2626; color: #fff; }
        .modal-staymate .modal-content { border-radius: 30px; border: 4px solid #fff; box-shadow: 0 25px 60px rgba(0,0,0,.2); overflow: hidden; }
        .modal-staymate .modal-header { background: linear-gradient(135deg, #f0fdfa, #ccfbf1); border-bottom: 2px solid #99f6e4; padding: 18px 26px; }
        .modal-staymate .form-control { border-radius: 16px; border: 2px solid #e2e8f0; padding: 10px 15px; font-weight: 700; }
        .modal { z-index: 1060 !important; }
        .modal-backdrop { z-index: 1050 !important; }
        .footer-custom { background: #ffffff; padding: 20px 0; color: #64748b; font-size: .85rem; font-weight: 700; margin-top: auto; border-top: 1px solid #f1f5f9; position: relative; z-index: 10; }
    </style>
</head>
<body>

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
                <div class="dropdown">
                    <div class="d-flex align-items-center gap-2 cursor-pointer" data-bs-toggle="dropdown" style="cursor: pointer;">
                        <div class="avatar-circle">AS</div>
                        <div class="text-start d-none d-sm-block lh-sm">
                            <div class="fw-bold text-dark" style="font-size: 0.9rem;"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Quản trị viên Hệ thống') ?> <i class="fa-solid fa-angle-down ms-1 text-muted fs-6"></i></div>
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
                <li class="nav-item"><a class="nav-link" href="index.php?route=rooms"><i class="fa-solid fa-bed"></i> Phòng trọ</a></li>
                <li class="nav-item"><a class="nav-link active" href="index.php?route=tenants"><i class="fa-solid fa-users"></i> Khách thuê</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=contracts"><i class="fa-solid fa-file-signature"></i> Hợp đồng</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=utilities"><i class="fa-solid fa-bolt"></i> Điện Nước</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=showcase" target="_blank"><i class="fa-regular fa-eye"></i> Xem phòng khách</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=tenant/portal" target="_blank"><i class="fa-solid fa-user-shield text-teal-main"></i> Cổng khách thuê</a></li>
            </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 px-xl-5 py-4 position-relative">
        <!-- Dòng Quản lý Khách thuê & Nút Thêm Khách thuê -->
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <h2 class="fw-black text-dark mb-0" style="font-weight: 900; font-size: 1.85rem;">Quản lý Khách thuê - Hồ sơ & Tài khoản lưu trú</h2>
            <button type="button" class="btn-teal-custom" data-bs-toggle="modal" data-bs-target="#addTenantModal">
                <i class="fa-solid fa-user-plus"></i> Thêm Khách thuê
            </button>
        </div>

        <?php if (!empty($flash_success)): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 py-3 fw-bold border-0 shadow-sm mb-4 position-relative z-2">
                <i class="fa-solid fa-circle-check me-2"></i><?= h($flash_success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($flash_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-pill px-4 py-3 fw-bold border-0 shadow-sm mb-4 position-relative z-2">
                <i class="fa-solid fa-triangle-exclamation me-2"></i><?= h($flash_error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="section-box">
            <form method="GET" action="index.php" class="row g-3 align-items-end mb-4 pb-3 border-bottom border-light">
                <input type="hidden" name="route" value="tenants">
                <div class="col-12 col-md-5">
                    <label class="form-label fw-bold text-teal-main text-uppercase mb-1" style="font-size: .75rem;">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Tìm kiếm khách thuê
                    </label>
                    <input type="text" name="keyword" class="form-control filter-control"
                           placeholder="Tìm họ tên, SĐT, email, username hoặc mã phòng..."
                           value="<?= h($keyword ?? '') ?>">
                </div>
                <div class="col-6 col-md-4">
                    <label class="form-label fw-bold text-muted text-uppercase mb-1" style="font-size: .75rem;">Trạng thái lưu trú</label>
                    <select name="stay_status" class="form-select filter-control">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="staying" <?= ($stayStatus ?? '') === 'staying' ? 'selected' : '' ?>>Đang lưu trú (Có HĐ active)</option>
                        <option value="unassigned" <?= ($stayStatus ?? '') === 'unassigned' ? 'selected' : '' ?>>Chưa xếp phòng / Đã trả phòng</option>
                    </select>
                </div>
                <div class="col-6 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn-teal-custom flex-grow-1 justify-content-center py-2">
                        <i class="fa-solid fa-filter"></i> Lọc
                    </button>
                    <?php if (!empty($keyword) || !empty($stayStatus)): ?>
                        <a href="index.php?route=tenants" class="btn-pill-white py-2 px-3" title="Xóa bộ lọc"><i class="fa-solid fa-rotate-left"></i></a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="table-rounded-wrap table-responsive">
                <table class="table table-staymate mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>HỌ VÀ TÊN &amp; CCCD</th>
                            <th>ĐIỆN THOẠI &amp; TÀI KHOẢN</th>
                            <th>EMAIL &amp; THƯỜNG TRÚ</th>
                            <th>PHÒNG &amp; BẠN CÙNG PHÒNG</th>
                            <th>TRẠNG THÁI</th>
                            <th class="text-end">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($tenants)): ?>
                            <?php foreach ($tenants as $t):
                                $tId = (int)$t['id'];
                                $isStaying = !empty($t['contract_id']);
                                $tStatus = !empty($t['status']) ? $t['status'] : ($isStaying ? 'Đang thuê' : 'Chưa xếp phòng');
                            ?>
                                <tr>
                                    <td class="text-muted fw-bold">#<?= $tId ?></td>
                                    <td>
                                        <div class="fw-black text-dark" style="font-weight: 800; font-size: 0.95rem;"><?= h($t['full_name']) ?></div>
                                        <?php if (!empty($t['cccd'])): ?>
                                            <div class="text-muted small"><i class="fa-regular fa-id-card me-1 text-teal-main"></i>CCCD: <strong><?= h($t['cccd']) ?></strong></div>
                                        <?php endif; ?>
                                        <?php if (!empty($t['occupation'])): ?>
                                            <div class="text-muted small"><i class="fa-solid fa-briefcase me-1"></i><?= h($t['occupation']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-teal-main"><i class="fa-solid fa-phone me-1"></i><?= h($t['phone']) ?></div>
                                        <div class="text-muted small">
                                            <i class="fa-solid fa-key text-warning me-1"></i>TK: <strong><?= h($t['username']) ?></strong> <span class="badge bg-light text-secondary border">MK: 123</span>
                                        </div>
                                    </td>
                                    <td class="text-muted">
                                        <div><i class="fa-regular fa-envelope me-1"></i><?= h($t['email'] ?: 'Chưa cập nhật') ?></div>
                                        <?php if (!empty($t['hometown'])): ?>
                                            <div class="small"><i class="fa-solid fa-location-dot me-1 text-danger"></i><?= h($t['hometown']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($isStaying): ?>
                                            <a href="index.php?route=rooms&keyword=<?= urlencode($t['room_number']) ?>" class="badge-room-pill">
                                                <i class="fa-solid fa-bed"></i> Phòng <?= h($t['room_number']) ?>
                                            </a>
                                            <?php if (!empty($t['roommate_name'])): ?>
                                                <div class="small fw-bold text-dark mt-1">
                                                    <i class="fa-solid fa-user-group text-teal-main me-1"></i>Ở cùng: <?= h($t['roommate_name']) ?>
                                                    <?php if (!empty($t['roommate_phone'])): ?>
                                                        <span class="text-muted">(<?= h($t['roommate_phone']) ?>)</span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="text-muted small mt-1">
                                                <?= date('d/m/Y', strtotime($t['start_date'])) ?> → <?= date('d/m/Y', strtotime($t['end_date'])) ?>
                                                &bull; <a href="index.php?route=contracts/detail&id=<?= (int)$t['contract_id'] ?>" class="text-teal-main fw-bold text-decoration-none">Xem HĐ</a>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge rounded-pill px-2 py-1 bg-light text-muted border">Chưa gắn phòng</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($tStatus === 'Đang thuê' || $isStaying): ?>
                                            <span class="badge rounded-pill px-3 py-2" style="background:#dcfce7;color:#15803d;font-weight:800;">● Đang thuê</span>
                                        <?php elseif ($tStatus === 'Chờ nhận phòng'): ?>
                                            <span class="badge rounded-pill px-3 py-2" style="background:#fef9c3;color:#854d0e;font-weight:800;">Chờ nhận phòng</span>
                                        <?php else: ?>
                                            <span class="badge rounded-pill px-3 py-2" style="background:#f1f5f9;color:#475569;font-weight:800;"><?= h($tStatus) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            <button type="button" class="btn-icon-circle btn-icon-edit" data-bs-toggle="modal" data-bs-target="#editTenantModal<?= $tId ?>" title="Chỉnh sửa">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <form method="POST" action="index.php?route=tenants" class="m-0" onsubmit="return confirm('Bạn có chắc chắn muốn xóa khách thuê <?= h($t['full_name']) ?> không?');">
                                                <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $tId ?>">
                                                <button type="submit" class="btn-icon-circle btn-icon-del" title="Xóa"><i class="fa-solid fa-trash-can"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted fw-bold">Chưa tìm thấy khách thuê nào phù hợp.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ================= MODAL THÊM KHÁCH THUÊ MỚI (CHUẨN FORM ẢNH) ================= -->
    <div class="modal fade modal-staymate" id="addTenantModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form method="POST" action="index.php?route=tenants" class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 pb-0 px-4 pt-4">
                    <h5 class="modal-title fw-bold text-dark" style="font-size: 1.25rem;">Thêm Khách thuê mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body px-4 py-3">
                    <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                    <input type="hidden" name="action" value="create">

                    <div class="row g-3">
                        <!-- Hàng 1: Họ tên & CCCD -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control rounded-3 py-2" placeholder="VD: Đinh Thảo Nguyên" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Số CCCD / CMND (9-12 số) <span class="text-danger">*</span></label>
                            <input type="text" name="cccd" class="form-control rounded-3 py-2" placeholder="VD: 037201091234" maxlength="20" required>
                        </div>

                        <!-- Hàng 2: SĐT, Ngày sinh, Giới tính -->
                        <div class="col-md-5">
                            <label class="form-label fw-bold small text-dark">Số điện thoại <span class="text-danger">*</span> <span class="text-teal-main fw-normal" style="font-size:0.75rem;">(Là tên tài khoản, MK: 123)</span></label>
                            <input type="text" name="phone" class="form-control rounded-3 py-2" placeholder="VD: 0937001122" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-dark">Ngày sinh</label>
                            <input type="date" name="birthday" class="form-control rounded-3 py-2" value="VD: 2001-06-15">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-dark">Giới tính</label>
                            <select name="gender" class="form-select rounded-3 py-2">
                                <option value="Nữ" selected>Nữ</option>
                                <option value="Nam">Nam</option>
                                <option value="Khác">Khác</option>
                            </select>
                        </div>

                        <!-- Hàng 3: Email & Nghề nghiệp -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Email</label>
                            <input type="email" name="email" class="form-control rounded-3 py-2" placeholder="VD: nguyendt@gmail.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark">Nghề nghiệp / Nơi công tác</label>
                            <input type="text" name="occupation" class="form-control rounded-3 py-2" placeholder="VD: Nhân viên Nhân sự (HR)">
                        </div>

                        <!-- Hàng 4: Quê quán, Liên hệ khẩn cấp, Trạng thái -->
                        <div class="col-md-5">
                            <label class="form-label fw-bold small text-dark">Quê quán / Địa chỉ thường trú</label>
                            <input type="text" name="hometown" class="form-control rounded-3 py-2" placeholder="VD: Ninh Bình">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-dark">Liên hệ khẩn cấp</label>
                            <input type="text" name="emergency_contact" class="form-control rounded-3 py-2" placeholder="VD: 0904000111">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-dark">Trạng thái</label>
                            <select name="status" class="form-select rounded-3 py-2">
                                <option value="Đang thuê" selected>Đang thuê</option>
                                <option value="Chờ nhận phòng">Chờ nhận phòng</option>
                                <option value="Đã chuyển đi">Đã chuyển đi</option>
                            </select>
                        </div>

                        <!-- Chọn phòng thuê liên kết -->
                        <div class="col-12 mt-3 pt-2 border-top">
                            <label class="form-label fw-bold small text-dark d-flex align-items-center gap-2 mb-1">
                                <i class="fa-solid fa-door-open text-teal-main"></i> Chọn phòng thuê liên kết
                            </label>
                            <select name="room_id" class="form-select rounded-3 py-2" id="tenantRoomSelect_new" onchange="handleTenantRoomChange(this, 'new')">
                                <option value="" data-max-occupants="1">-- Chọn phòng thuê (Chưa xếp phòng) --</option>
                                <?php 
                                $hasAvailable = false;
                                if (!empty($allRooms)): 
                                    foreach ($allRooms as $r):
                                        $isCurSelected = ($selectedRoomId > 0 && $selectedRoomId === (int)$r['id']);
                                        // Chỉ hiện những phòng trống (status = 'available'), ẩn hoàn toàn phòng đã thuê
                                        if ($r['status'] !== 'available' && !$isCurSelected) {
                                            continue;
                                        }
                                        $hasAvailable = true;
                                        $rMax = (int)($r['details']['max_occupants'] ?? 2);
                                ?>
                                        <option value="<?= $r['id'] ?>" 
                                                data-max-occupants="<?= $rMax ?>"
                                                <?= $isCurSelected ? 'selected' : '' ?>>
                                            Phòng <?= h($r['room_number']) ?> &bull; <?= number_format($r['price'], 0, ',', '.') ?> đ/tháng (Tối đa <?= $rMax ?> người) [Phòng trống]
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <?php if (!$hasAvailable): ?>
                                    <option value="" disabled>-- Hiện không có phòng trống --</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Khối thông tin bạn cùng phòng: Tự động hiển thị khi phòng tối đa >= 2 người -->
                        <div class="col-12 roommate-block" id="roommateBlock_new" style="display: none;">
                            <div class="p-3 rounded-4" style="background: #f0fdfa; border: 1.5px dashed #5eead4;">
                                <div class="fw-bold text-teal-main mb-2 small d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-user-group"></i> 
                                    <span>Thông tin bạn cùng phòng (Phòng tối đa <span class="max-occ-num">2</span> người)</span>
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-dark">Họ và tên bạn cùng phòng</label>
                                        <input type="text" name="roommate_name" class="form-control rounded-3 py-2" placeholder="VD: Trần Thu Hà">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-dark">Số điện thoại bạn cùng phòng</label>
                                        <input type="text" name="roommate_phone" class="form-control rounded-3 py-2" placeholder="VD: 0987654321">
                                    </div>
                                </div>
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

    <!-- ================= TẤT CẢ MODAL CẬP NHẬT KHÁCH THUÊ (ĐẶT Ở ROOT TRÁNH LỖI STACKING CONTEXT) ================= -->
    <?php if (!empty($tenants)): ?>
        <?php foreach ($tenants as $t):
            $tId = (int)$t['id'];
            $curRoomId = (int)($t['room_id'] ?? 0);
            $tGender = $t['gender'] ?? 'Nữ';
            $tStatus = $t['status'] ?? 'Đang thuê';
        ?>
        <div class="modal fade modal-staymate" id="editTenantModal<?= $tId ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <form method="POST" action="index.php?route=tenants" class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                    <div class="modal-header border-0 pb-0 px-4 pt-4">
                        <h5 class="modal-title fw-bold text-dark" style="font-size: 1.25rem;">
                            Cập nhật Khách thuê: <?= h($t['full_name']) ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                    </div>
                    <div class="modal-body px-4 py-3">
                        <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $tId ?>">

                        <div class="row g-3">
                            <!-- Hàng 1: Họ tên & CCCD -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" name="full_name" class="form-control rounded-3 py-2" value="<?= h($t['full_name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark">Số CCCD / CMND (9-12 số) <span class="text-danger">*</span></label>
                                <input type="text" name="cccd" class="form-control rounded-3 py-2" value="<?= h($t['cccd'] ?? '037201091234') ?>" required>
                            </div>

                            <!-- Hàng 2: SĐT, Ngày sinh, Giới tính -->
                            <div class="col-md-5">
                                <label class="form-label fw-bold small text-dark">Số điện thoại <span class="text-danger">*</span> <span class="text-teal-main fw-normal" style="font-size:0.75rem;">(Tài khoản đăng nhập, MK: 123)</span></label>
                                <input type="text" name="phone" class="form-control rounded-3 py-2" value="<?= h($t['phone']) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-dark">Ngày sinh</label>
                                <input type="date" name="birthday" class="form-control rounded-3 py-2" value="<?= h($t['birthday'] ?? '2001-06-15') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small text-dark">Giới tính</label>
                                <select name="gender" class="form-select rounded-3 py-2">
                                    <option value="Nữ" <?= $tGender === 'Nữ' ? 'selected' : '' ?>>Nữ</option>
                                    <option value="Nam" <?= $tGender === 'Nam' ? 'selected' : '' ?>>Nam</option>
                                    <option value="Khác" <?= $tGender === 'Khác' ? 'selected' : '' ?>>Khác</option>
                                </select>
                            </div>

                            <!-- Hàng 3: Email & Nghề nghiệp -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark">Email</label>
                                <input type="email" name="email" class="form-control rounded-3 py-2" value="<?= h($t['email']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark">Nghề nghiệp / Nơi công tác</label>
                                <input type="text" name="occupation" class="form-control rounded-3 py-2" value="<?= h($t['occupation'] ?? 'Nhân viên Nhân sự (HR)') ?>">
                            </div>

                            <!-- Hàng 4: Quê quán, Liên hệ khẩn cấp, Trạng thái -->
                            <div class="col-md-5">
                                <label class="form-label fw-bold small text-dark">Quê quán / Địa chỉ thường trú</label>
                                <input type="text" name="hometown" class="form-control rounded-3 py-2" value="<?= h($t['hometown'] ?? 'Ninh Bình') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-dark">Liên hệ khẩn cấp</label>
                                <input type="text" name="emergency_contact" class="form-control rounded-3 py-2" value="<?= h($t['emergency_contact'] ?? '0904000111') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small text-dark">Trạng thái</label>
                                <select name="status" class="form-select rounded-3 py-2">
                                    <option value="Đang thuê" <?= $tStatus === 'Đang thuê' ? 'selected' : '' ?>>Đang thuê</option>
                                    <option value="Chờ nhận phòng" <?= $tStatus === 'Chờ nhận phòng' ? 'selected' : '' ?>>Chờ nhận phòng</option>
                                    <option value="Đã chuyển đi" <?= $tStatus === 'Đã chuyển đi' ? 'selected' : '' ?>>Đã chuyển đi</option>
                                </select>
                            </div>

                            <!-- Chọn phòng thuê liên kết -->
                            <div class="col-12 mt-3 pt-2 border-top">
                                <label class="form-label fw-bold small text-dark d-flex align-items-center gap-2 mb-1">
                                    <i class="fa-solid fa-door-open text-teal-main"></i> Phòng thuê liên kết
                                </label>
                                <select name="room_id" class="form-select rounded-3 py-2" onchange="handleTenantRoomChange(this, '<?= $tId ?>')">
                                    <option value="" data-max-occupants="1">-- Chưa xếp phòng --</option>
                                    <?php if (!empty($allRooms)): ?>
                                        <?php foreach ($allRooms as $r):
                                            $isThisRoom = ($curRoomId === (int)$r['id']);
                                            // Chỉ hiện phòng trống hoặc chính phòng khách này đang thuê
                                            if ($r['status'] !== 'available' && !$isThisRoom) {
                                                continue;
                                            }
                                            $rMax = (int)($r['details']['max_occupants'] ?? 2);
                                            $stLabel = ($r['status'] === 'available') ? 'Phòng trống' : 'Đang thuê';
                                        ?>
                                            <option value="<?= $r['id'] ?>" 
                                                    data-max-occupants="<?= $rMax ?>"
                                                    <?= $isThisRoom ? 'selected' : '' ?>>
                                                Phòng <?= h($r['room_number']) ?> &bull; <?= number_format($r['price'], 0, ',', '.') ?> đ/tháng (Tối đa <?= $rMax ?> người) [<?= $stLabel ?>]
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <!-- Khối bạn cùng phòng -->
                            <?php 
                            $hasRoommate = !empty($t['roommate_name']) || !empty($t['roommate_phone']);
                            ?>
                            <div class="col-12 roommate-block" id="roommateBlock_<?= $tId ?>" style="display: <?= $hasRoommate ? 'block' : 'none' ?>;">
                                <div class="p-3 rounded-4" style="background: #f0fdfa; border: 1.5px dashed #5eead4;">
                                    <div class="fw-bold text-teal-main mb-2 small d-flex align-items-center gap-2">
                                        <i class="fa-solid fa-user-group"></i> 
                                        <span>Thông tin bạn cùng phòng</span>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-dark">Họ và tên bạn cùng phòng</label>
                                            <input type="text" name="roommate_name" class="form-control rounded-3 py-2" value="<?= h($t['roommate_name'] ?? '') ?>" placeholder="VD: Trần Thu Hà">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-dark">Số điện thoại bạn cùng phòng</label>
                                            <input type="text" name="roommate_phone" class="form-control rounded-3 py-2" value="<?= h($t['roommate_phone'] ?? '') ?>" placeholder="VD: 0987654321">
                                        </div>
                                    </div>
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
                            <h5 class="fw-bold mb-0"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Quản trị viên Hệ thống') ?></h5>
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
    function handleTenantRoomChange(selectEl, prefix) {
        const selectedOpt = selectEl.options[selectEl.selectedIndex];
        const maxOcc = parseInt(selectedOpt.getAttribute('data-max-occupants') || '1', 10);
        const block = document.getElementById('roommateBlock_' + prefix);
        if (!block) return;

        if (maxOcc >= 2) {
            block.style.display = 'block';
            const numSpan = block.querySelector('.max-occ-num');
            if (numSpan) numSpan.textContent = maxOcc;
        } else {
            block.style.display = 'none';
        }
    }

    // Auto check on page load for add modal if pre-selected
    document.addEventListener('DOMContentLoaded', function() {
        const selectNew = document.getElementById('tenantRoomSelect_new');
        if (selectNew && selectNew.value !== '') {
            handleTenantRoomChange(selectNew, 'new');
            // Auto open modal if route has room_id parameter
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('room_id')) {
                const addModal = new bootstrap.Modal(document.getElementById('addTenantModal'));
                addModal.show();
            }
        }
    });
    </script>
</body>
</html>
