<?php
if (!function_exists('h')) {
    function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('money')) {
    function money($n) { return number_format((float)$n, 0, ',', '.') . ' đ'; }
}

$periodLabel = ($filters['period'] === 'all') ? 'Tất cả các kỳ' : 'Kỳ ' . $filters['period'];
$returnQuery = http_build_query(array_filter([
    'period' => $filters['period'], 'status' => $filters['status'],
    'room_id' => $filters['room_id'], 'keyword' => $filters['keyword'],
]));
$exportQuery = $returnQuery;
$csrf = $_SESSION['csrf_token'] ?? '';

// Bản ghi điện nước chưa lập hóa đơn + tính trước số tiền
$pendingView = [];
foreach ($pending as $pr) {
    $eU = max(0, (int)$pr['electric_new'] - (int)$pr['electric_old']);
    $wU = max(0, (int)$pr['water_new'] - (int)$pr['water_old']);
    $pr['e_used'] = $eU;
    $pr['w_used'] = $wU;
    $pr['preview_total'] = (float)$pr['room_price'] + $eU * $rates['elec'] + $wU * $rates['water'] + $rates['others_total'];
    $pendingView[] = $pr;
}
$pendingInPeriod = array_values(array_filter($pendingView, fn($x) => $filters['period'] === 'all' || $x['month_year'] === $filters['period']));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayMate - Hóa đơn &amp; Thu tiền</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --teal-main: #308f86; --teal-hover: #25756d; --bg-page: #f8fafc; }
        body { 
            background-color: var(--bg-page); font-family: 'Nunito', sans-serif; font-size: 0.95rem; color: #334155;
            min-height: 100vh; display: flex; flex-direction: column;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23e6f4f1" fill-opacity="0.8" d="M0,160L48,170.7C96,181,192,203,288,197.3C384,192,480,160,576,149.3C672,139,768,149,864,170.7C960,192,1056,224,1152,213.3C1248,203,1344,149,1392,122.7L1440,96L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0C192,0,96,0,48,0L0,0Z"></path></svg>');
            background-position: top center; background-repeat: no-repeat; background-size: 100% auto; background-attachment: fixed;
        }
        .text-teal-main { color: var(--teal-main) !important; }
        .navbar-custom { background: #fff; padding: 12px 20px; box-shadow: 0 4px 20px rgba(0,0,0,.04); z-index: 100; border-bottom: 1px solid #f1f5f9; }
        .brand-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .brand-logo i { color: var(--teal-main); font-size: 2.2rem; }
        .brand-text { color: var(--teal-main); font-weight: 900; font-size: 1.4rem; line-height: 1; letter-spacing: -.5px; }
        .brand-sub { color: #64748b; font-size: .75rem; font-weight: 600; }
        .nav-pills { gap: 6px; flex-wrap: nowrap !important; }
        .nav-pills .nav-link { 
            color: #475569; font-weight: 800; font-size: 0.88rem; 
            padding: 8px 14px; border-radius: 50px; transition: all 0.2s; white-space: nowrap; 
        }
        .nav-pills .nav-link i { margin-right: 6px; }
        .nav-pills .nav-link:hover { color: var(--teal-main); background: #f0fdfa; }
        .nav-pills .nav-link.active { background: var(--teal-main); color: #fff; box-shadow: 0 6px 15px rgba(48,143,134,.35); }
        .avatar-circle { width: 46px; height: 46px; background: var(--teal-main); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 900; border: 3px solid #fff; box-shadow: 0 6px 15px rgba(48,143,134,.4); }

        .btn-hamburger { width: 44px; height: 44px; border-radius: 14px; background: #f0fdfa; border: 2px solid #99f6e4; color: var(--teal-main); display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem; transition: .2s; }
        .btn-hamburger:hover, .btn-hamburger:focus { background: var(--teal-main); color: #fff; outline: none; }
        @media (max-width: 991.98px) {
            .mobile-nav-collapse { background: #fff; border-radius: 22px; padding: 14px; margin-top: 12px; border: 2px solid #ccfbf1; box-shadow: 0 12px 30px rgba(0,0,0,.08); }
            .mobile-nav-collapse .nav-pills { flex-direction: column; gap: 6px; }
            .mobile-nav-collapse .nav-link { border-radius: 16px; padding: 11px 16px; }
        }
        .period-dropdown-btn { background: #fff; border: 2.5px solid #99f6e4; border-radius: 50px; padding: 8px 18px; font-weight: 800; font-size: .88rem; color: #0f172a; box-shadow: 0 6px 18px rgba(48,143,134,.12); display: inline-flex; align-items: center; gap: 10px; transition: .2s; }
        .period-dropdown-btn:hover, .period-dropdown-btn:focus { border-color: var(--teal-main); background: #f0fdfa; }
        .period-dropdown-badge { background: var(--teal-main); color: #fff; padding: 4px 12px; border-radius: 50px; font-weight: 900; font-size: .82rem; }
        .period-dropdown-menu { border-radius: 22px; border: 2px solid #ccfbf1; padding: 8px; min-width: 220px; box-shadow: 0 15px 35px rgba(0,0,0,.12); }
        .period-dropdown-menu .dropdown-item { border-radius: 14px; font-weight: 800; font-size: .86rem; padding: 9px 14px; color: #334155; display: flex; align-items: center; justify-content: space-between; margin-bottom: 2px; }
        .period-dropdown-menu .dropdown-item:hover { background: #f0fdfa; color: var(--teal-main); }
        .period-dropdown-menu .dropdown-item.active { background: var(--teal-main); color: #fff; }

        .btn-back-content { background: #fff; border: 3px solid #fff; color: #334155; font-weight: 800; padding: 10px 26px; border-radius: 50px; transition: .2s; text-decoration: none; display: inline-flex; align-items: center; box-shadow: 0 8px 20px rgba(0,0,0,.08); }
        .btn-back-content:hover { border-color: var(--teal-main); color: var(--teal-main); transform: translateY(-3px); }
        .header-title-wrapper { position: relative; z-index: 2; display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; flex-wrap: wrap; gap: 16px; }
        .main-title {  font-weight: 800; color: #0f172a; font-size: 1.45rem; letter-spacing: -0.3px; display: inline-flex; align-items: center; flex-wrap: wrap; }
        .bld-highlight { color: var(--teal-main); margin-left: 8px; position: relative; }
        .title-sparkle { position: absolute; background: #fde047; border-radius: 2px; }
        .ts-1 { width: 4px; height: 12px; top: -10px; right: -15px; transform: rotate(20deg); }
        .ts-2 { width: 12px; height: 4px; top: 15px; right: -25px; transform: rotate(15deg); }
        .bld-address-txt { font-size: .92rem; font-weight: 700; color: #64748b; margin-top: 6px; }

                .overview-card {
            border-radius: 18px;
            padding: 18px 20px; position: relative; overflow: hidden;
            box-shadow: 0 4px 16px rgba(0,0,0,0.03);
            border: 1px solid #e2e8f0;
            background: #ffffff;
            height: 100%; transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .overview-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.06); }
        .overview-title {  font-size: 0.85rem; font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; gap: 6px; text-transform: uppercase; color: #475569; position: relative; z-index: 2; }
        .overview-val {  font-size: 1.55rem; font-weight: 800; line-height: 1.2; letter-spacing: -0.3px; position: relative; z-index: 2; color: #0f172a; }
        .overview-icon-bg {
            position: absolute; right: 18px; top: 18px;
            width: 38px; height: 38px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1rem;
            z-index: 2;
        }

        .section-box { background: rgba(255,255,255,.96); border-radius: 30px; border: 4px solid #fff; padding: 24px 28px; box-shadow: 0 15px 40px rgba(0,0,0,.08); position: relative; z-index: 2; margin-bottom: 24px; }
        .btn-teal-custom { background: var(--teal-main); border-radius: 50px; font-weight: 800; color: #fff; padding: 10px 24px; border: 2px solid var(--teal-hover); box-shadow: 0 8px 20px rgba(48,143,134,.35); font-size: .9rem; transition: .2s; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; cursor: pointer; }
        .btn-teal-custom:hover { background: var(--teal-hover); color: #fff; transform: translateY(-2px); }
        .btn-pill-white { background: #fff; border-radius: 50px; font-weight: 800; color: #334155; padding: 10px 20px; border: 2px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,.04); font-size: .86rem; transition: .2s; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; cursor: pointer; }
        .btn-pill-white:hover { border-color: var(--teal-main); color: var(--teal-main); background: #f0fdfa; transform: translateY(-2px); }
        .btn-pill-amber { background: linear-gradient(135deg,#fef3c7,#fde68a); border-radius: 50px; font-weight: 800; color: #92400e; padding: 10px 22px; border: 2px solid #fff; box-shadow: 0 6px 16px rgba(245,158,11,.25); font-size: .88rem; transition: .2s; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; }
        .btn-pill-amber:hover { background: #f59e0b; color: #fff; transform: translateY(-2px); }
        .filter-control { border-radius: 50px; border: 2px solid #e2e8f0; padding: 9px 18px; font-weight: 700; font-size: .88rem; color: #334155; background-color: #f8fafc; transition: .2s; }
        .filter-control:focus { background-color: #fff; border-color: var(--teal-main); box-shadow: 0 0 0 4px rgba(48,143,134,.15); outline: none; }

        .rate-chip { display: inline-flex; align-items: center; gap: 6px; background: #fff; border: 2px solid #e2e8f0; border-radius: 50px; padding: 6px 14px; font-weight: 800; font-size: .8rem; color: #334155; }

        .table-rounded-wrap { border-radius: 24px; border: 2px solid #e2e8f0; overflow: hidden; background: #fff; box-shadow: 0 6px 20px rgba(0,0,0,.03); }
        .table-staymate { margin-bottom: 0; }
        .table-staymate thead th { background: #f0fdfa; color: var(--teal-main); font-weight: 900; font-size: .72rem; text-transform: uppercase; letter-spacing: .4px; padding: 14px 10px; border-bottom: 2px solid #ccfbf1; white-space: nowrap; }
        .table-staymate tbody td { padding: 14px 10px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 600; color: #1e293b; white-space: nowrap; }
        .table-staymate thead th:first-child, .table-staymate tbody td:first-child { padding-left: 18px; }
        .table-staymate tbody tr:hover { background: #f8fafc; }
        .table-staymate thead th:last-child, .table-staymate tbody td:last-child { position: sticky; right: 0; z-index: 2; padding-right: 16px; box-shadow: -8px 0 12px -8px rgba(0,0,0,.08); }
        .table-staymate thead th:last-child { background: #f0fdfa; }
        .table-staymate tbody td:last-child { background: #fff; }
        .table-staymate tbody tr:hover td:last-child { background: #f8fafc; }
        .table-staymate .tenant-name { white-space: normal; min-width: 110px; max-width: 150px; line-height: 1.25; }
        .sub-note { font-size: 0.82rem; color: #64748b; font-weight: 700; }

        .badge-room-pill {
            font-size: 0.88rem; box-shadow: 0 4px 10px rgba(48,143,134,.25); display: inline-flex; }
        .badge-period-pill {
            font-size: 0.86rem; display: inline-flex; gap: 6px; align-items: center; white-space: nowrap; }
        .code-pill {
            font-size: 0.88rem; text-decoration: none; }
        .code-pill:hover { background: var(--teal-main); color: #fff; }
        .st-pill {
            font-size: 0.85rem; white-space: nowrap; border: 1.5px solid; }
        .st-paid { background: #dcfce7; color: #15803d; border-color: #86efac; }
        .st-unpaid { background: #fef3c7; color: #b45309; border-color: #fde68a; }
        .st-overdue { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }

        .btn-icon-circle { width: 34px; height: 34px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; border: 2px solid transparent; transition: .2s; text-decoration: none; cursor: pointer; background: none; }
        .btn-icon-view { background: #f0fdfa; color: var(--teal-main); border-color: #99f6e4; }
        .btn-icon-view:hover { background: var(--teal-main); color: #fff; }
        .btn-icon-pay { background: #dcfce7; color: #15803d; border-color: #86efac; }
        .btn-icon-pay:hover { background: #16a34a; color: #fff; }
        .btn-icon-del { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
        .btn-icon-del:hover { background: #dc2626; color: #fff; }
        .btn-icon-lock { background: #f1f5f9; color: #94a3b8; border-color: #e2e8f0; cursor: not-allowed; }

        .modal-staymate .modal-content { border-radius: 30px; border: 4px solid #fff; box-shadow: 0 25px 60px rgba(0,0,0,.2); overflow: hidden; }
        .modal-staymate .modal-header { background: linear-gradient(135deg,#f0fdfa,#ccfbf1); border-bottom: 2px solid #99f6e4; padding: 18px 26px; }
        .method-card { cursor: pointer; border: 2px solid #e2e8f0; border-radius: 20px; padding: 14px 10px; text-align: center; font-weight: 800; font-size: .82rem; transition: .2s; background: #fff; display: block; height: 100%; }
        .method-card i { font-size: 1.5rem; display: block; margin-bottom: 6px; color: #94a3b8; }
        input[type=radio]:checked + .method-card { border-color: var(--teal-main); background: #f0fdfa; color: var(--teal-main); box-shadow: 0 6px 16px rgba(48,143,134,.2); }
        input[type=radio]:checked + .method-card i { color: var(--teal-main); }
        .footer-custom { background: #fff; padding: 20px 0; color: #64748b; font-size: .85rem; font-weight: 700; margin-top: auto; border-top: 1px solid #f1f5f9; position: relative; z-index: 10; }
        @media print { .navbar-custom, .no-print, .footer-custom, .btn-back-content { display: none !important; } body { background: #fff !important; } .section-box { box-shadow: none !important; } }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
    <div class="container-fluid px-3 px-xl-5">
        <a class="brand-logo me-3" href="index.php?route=dashboard">
            <i class="fa-solid fa-house-chimney"></i>
            <div><div class="brand-text">StayMate</div><div class="brand-sub">Quản Lý Nhà Trọ</div></div>
        </a>
        <div class="d-flex align-items-center gap-3 order-lg-3">
            <div class="position-relative" style="color:#64748b" title="Hóa đơn quá hạn">
                <i class="fa-solid fa-bell fs-4"></i>
                <?php if ($stats['overdue_count'] > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white"><?= (int)$stats['overdue_count'] ?></span>
                <?php endif; ?>
            </div>
            <div class="dropdown">
                    <div class="d-flex align-items-center gap-2 cursor-pointer" data-bs-toggle="dropdown" style="cursor: pointer;">
                        <div class="avatar-circle">AS</div>
                        <div class="text-start d-none d-sm-block lh-sm">
                            <div class="fw-bold text-dark" style="font-size: 0.9rem;"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Quản trị viên Hệ thống') ?> <i class="fa-solid fa-angle-down ms-1 text-muted fs-6"></i></div>
                            <div class="text-muted text-capitalize" style="font-size: 0.82rem;"><?= htmlspecialchars($_SESSION['role'] ?? 'admin') ?></div>
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
            <button class="btn-hamburger d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#staymateMobileNav" aria-controls="staymateMobileNav" aria-expanded="false" aria-label="Mở danh mục menu">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
        <div class="collapse navbar-collapse mobile-nav-collapse order-lg-2" id="staymateMobileNav">
            <ul class="nav nav-pills flex-nowrap me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php?route=overview"><i class="fa-solid fa-border-all"></i> Tổng quan</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=rooms"><i class="fa-solid fa-bed"></i> Phòng trọ</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=tenants"><i class="fa-solid fa-users"></i> Khách thuê</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=contracts"><i class="fa-solid fa-file-signature"></i> Hợp đồng</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=utilities"><i class="fa-solid fa-bolt"></i> Điện Nước</a></li>
                <li class="nav-item"><a class="nav-link active" href="index.php?route=invoices"><i class="fa-solid fa-file-invoice-dollar"></i> Hóa đơn</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=showcase" target="_blank"><i class="fa-regular fa-eye"></i> Xem phòng khách</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=tenant/portal" target="_blank"><i class="fa-solid fa-user-shield text-teal-main"></i> Cổng khách thuê</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container-fluid px-4 px-xl-5 py-4 position-relative">

    <div class="d-flex flex-wrap justify-content-end align-items-center mb-2 no-print">
        <div class="dropdown">
            <button class="period-dropdown-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span><i class="fa-regular fa-calendar-check me-1 text-teal-main"></i> Kỳ đang xem:</span>
                <span class="period-dropdown-badge"><?= $filters['period'] === 'all' ? 'Tất cả các kỳ' : 'Kỳ ' . h($filters['period']) ?></span>
                <i class="fa-solid fa-chevron-down text-muted small"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end period-dropdown-menu">
                <li>
                    <a class="dropdown-item <?= $filters['period'] === 'all' ? 'active' : '' ?>" href="index.php?route=invoices&period=all">
                        <span><i class="fa-solid fa-layer-group me-2"></i>Tất cả các kỳ</span>
                        <?php if ($filters['period'] === 'all'): ?><i class="fa-solid fa-check"></i><?php endif; ?>
                    </a>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <?php foreach ($periods as $per): ?>
                    <li>
                        <a class="dropdown-item <?= $filters['period'] === $per ? 'active' : '' ?>" href="index.php?route=invoices&period=<?= urlencode($per) ?>">
                            <span><i class="fa-regular fa-calendar me-2"></i>Kỳ <?= h($per) ?></span>
                            <?php if ($filters['period'] === $per): ?><i class="fa-solid fa-check"></i><?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="header-title-wrapper">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1" style="font-size:.85rem;font-weight:700">
                    <li class="breadcrumb-item"><a href="index.php?route=dashboard" class="text-teal-main text-decoration-none"><i class="fa-solid fa-house"></i> Hệ thống Quản lý</a></li>
                    <li class="breadcrumb-item text-muted">Hóa đơn &amp; Thu tiền</li>
                </ol>
            </nav>
            <div class="main-title">
                Hóa đơn &amp; Thu tiền -
                <span class="bld-highlight"><?= h($periodLabel) ?></span>
            </div>
            <div class="bld-address-txt d-flex flex-wrap align-items-center gap-2 mt-2">
                <span class="rate-chip"><i class="fa-solid fa-bolt text-warning"></i> Điện: <?= money($rates['elec']) ?>/kWh</span>
                <span class="rate-chip"><i class="fa-solid fa-droplet text-info"></i> Nước: <?= money($rates['water']) ?>/Khối</span>
                <span class="rate-chip"><i class="fa-solid fa-layer-group text-teal-main"></i> Dịch vụ cố định: <?= money($rates['others_total']) ?>/phòng</span>
                <a href="index.php?route=utilities" class="rate-chip text-decoration-none text-teal-main no-print"><i class="fa-solid fa-sliders"></i> Sửa bảng giá</a>
            </div>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2 no-print">
            <a class="btn-pill-white" href="index.php?route=invoices/export<?= $exportQuery ? '&' . $exportQuery : '' ?>"><i class="fa-solid fa-file-csv text-success"></i> Xuất CSV</a>
            <button type="button" class="btn-pill-white" onclick="window.print()"><i class="fa-solid fa-print text-teal-main"></i> In danh sách</button>
            <button type="button" class="btn-teal-custom" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fa-solid fa-plus"></i> Lập Hóa Đơn
                <?php if (count($pendingView) > 0): ?><span class="badge rounded-pill bg-warning text-dark"><?= count($pendingView) ?></span><?php endif; ?>
            </button>
        </div>
    </div>

    <?php if ($flash_success): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 py-3 shadow-sm fw-bold position-relative" style="z-index:2"><i class="fa-solid fa-circle-check me-2"></i><?= h($flash_success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($flash_error): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-pill px-4 py-3 shadow-sm fw-bold position-relative" style="z-index:2"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= h($flash_error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <?php if (count($pendingInPeriod) > 0): ?>
        <div class="section-box no-print py-3 d-flex flex-wrap justify-content-between align-items-center gap-2" style="background:#fffbeb;border-color:#fff">
            <div class="fw-bold" style="color:#92400e"><i class="fa-solid fa-circle-exclamation me-2"></i>Có <strong><?= count($pendingInPeriod) ?></strong> bản ghi điện nước đã chốt nhưng <strong>chưa lập hóa đơn</strong><?= $filters['period'] !== 'all' ? ' trong kỳ ' . h($filters['period']) : '' ?>.</div>
            <button type="button" class="btn-pill-amber" data-bs-toggle="modal" data-bs-target="#createModal"><i class="fa-solid fa-wand-magic-sparkles"></i> Lập ngay</button>
        </div>
    <?php endif; ?>

    <?php if (!empty($refundNotifications)): foreach ($refundNotifications as $rf): ?>
        <?php if ($rf['termination_verification_case'] === 'expired_no_renew'): ?>
            <div class="alert alert-success rounded-4 p-3 shadow-sm d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3 border-2 border-success-subtle position-relative z-2">
                <div class="fw-bold text-success-emphasis">
                    <i class="fa-solid fa-circle-check fs-5 me-2 text-success"></i>
                    <strong>XÁC NHẬN TỪ HỢP ĐỒNG:</strong> Phòng <strong><?= h($rf['room_number']) ?></strong> (Khách: <strong><?= h($rf['tenant_name']) ?></strong>) đã được xác nhận <em>Hết thời gian thuê và không muốn gia hạn</em> &rarr; Trạng thái cọc đã chuyển thành <strong>ĐÃ HOÀN CỌC</strong> (<?= money($rf['deposit_amount']) ?>).
                </div>
                <form method="POST" action="index.php?route=invoices/confirm-refund" class="m-0">
                    <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                    <input type="hidden" name="contract_id" value="<?= (int)$rf['id'] ?>">
                    <input type="hidden" name="subtab" value="<?= h($filters['subtab'] ?? 'deposit') ?>">
                    <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 py-2 fw-bold shadow-sm">
                        <i class="fa-solid fa-check-double me-1"></i> Xác nhận đã hoàn cọc cho khách
                    </button>
                </form>
            </div>
        <?php elseif ($rf['termination_verification_case'] === 'early_move_out'): ?>
            <div class="alert alert-danger rounded-4 p-3 shadow-sm d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3 border-2 border-danger-subtle position-relative z-2">
                <div class="fw-bold text-danger-emphasis">
                    <i class="fa-solid fa-ban fs-5 me-2 text-danger"></i>
                    <strong>XÁC NHẬN TỪ HỢP ĐỒNG:</strong> Phòng <strong><?= h($rf['room_number']) ?></strong> (Khách: <strong><?= h($rf['tenant_name']) ?></strong>) đã được xác nhận <em>Bỏ phòng trước khi hết hợp đồng</em> &rarr; <strong>TỊCH THU TIỀN CỌC (KHÔNG ĐƯỢC HOÀN CỌC)</strong> theo điều khoản hợp đồng.
                </div>
                <form method="POST" action="index.php?route=invoices/confirm-refund" class="m-0">
                    <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                    <input type="hidden" name="contract_id" value="<?= (int)$rf['id'] ?>">
                    <input type="hidden" name="subtab" value="<?= h($filters['subtab'] ?? 'deposit') ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-2 fw-bold">
                        <i class="fa-solid fa-check me-1"></i> Đã đối soát &amp; Đóng thông báo
                    </button>
                </form>
            </div>
        <?php endif; ?>
    <?php endforeach; endif; ?>

    <!-- ================= BẢNG 4 THẺ THỐNG KÊ TÀI CHÍNH & HÓA ĐƠN GỌN GÀNG ================= -->
    <div class="row g-3 mb-4 position-relative" style="z-index: 2;">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="p-3 rounded-4 bg-white border shadow-sm h-100 d-flex flex-column justify-content-between" style="border-top: 3.5px solid #16a34a !important;">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-success text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.3px;">
                            <i class="fa-solid fa-file-invoice-dollar me-1"></i> TỔNG PHẢI THU
                        </span>
                        <span class="badge rounded-pill bg-success-subtle text-success fw-bold" style="font-size: 0.78rem;">Doanh thu</span>
                    </div>
                    <div class="fw-black text-dark my-1" style="font-size: 1.55rem; font-weight: 900;">
                        <?= money($stats['total']) ?>
                    </div>
                </div>
                <div class="mt-2 pt-2 border-top border-light d-flex justify-content-between text-muted" style="font-size: 0.84rem;">
                    <span>Tổng <strong><?= (int)$stats['count'] ?></strong> hóa đơn</span>
                    <span class="text-teal-main fw-bold"><?= h($periodLabel) ?></span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="p-3 rounded-4 bg-white border shadow-sm h-100 d-flex flex-column justify-content-between" style="border-top: 3.5px solid #0284c7 !important;">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-primary text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.3px;">
                            <i class="fa-solid fa-circle-check me-1"></i> ĐÃ THU TỪ KHÁCH
                        </span>
                        <span class="badge rounded-pill bg-primary-subtle text-primary fw-bold" style="font-size: 0.78rem;"><?= (int)$stats['rate'] ?>% đã thu</span>
                    </div>
                    <div class="fw-black text-primary my-1" style="font-size: 1.55rem; font-weight: 900;">
                        <?= money($stats['paid']) ?>
                    </div>
                </div>
                <div class="mt-2 pt-2 border-top border-light d-flex justify-content-between text-muted" style="font-size: 0.84rem;">
                    <span>Hoàn tất <strong><?= (int)$stats['paid_count'] ?></strong> HĐ</span>
                    <span class="text-success fw-bold">Thu đúng hạn</span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="p-3 rounded-4 bg-white border shadow-sm h-100 d-flex flex-column justify-content-between" style="border-top: 3.5px solid #f59e0b !important;">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-warning-emphasis text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.3px;">
                            <i class="fa-regular fa-clock me-1"></i> CHƯA THU / NỢ ĐỌNG
                        </span>
                        <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis fw-bold" style="font-size: 0.78rem;">Tồn đọng</span>
                    </div>
                    <div class="fw-black my-1" style="font-size: 1.55rem; font-weight: 900; color: #b45309;">
                        <?= money($stats['unpaid']) ?>
                    </div>
                </div>
                <div class="mt-2 pt-2 border-top border-light d-flex justify-content-between text-muted" style="font-size: 0.84rem;">
                    <span>Tồn <strong><?= (int)$stats['unpaid_count'] ?></strong> HĐ</span>
                    <span>Quá hạn: <strong class="text-danger"><?= money($stats['overdue']) ?></strong></span>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="p-3 rounded-4 bg-white border shadow-sm h-100 d-flex flex-column justify-content-between" style="border-top: 3.5px solid #308f86 !important;">
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-teal-main text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.3px;">
                            <i class="fa-solid fa-chart-line me-1"></i> LỢI NHUẬN RÒNG (THU - CHI)
                        </span>
                        <span class="badge rounded-pill text-white fw-bold" style="background: var(--teal-main); font-size: 0.75rem;">Lãi ròng</span>
                    </div>
                    <div class="fw-black text-teal-main my-1" style="font-size: 1.55rem; font-weight: 900;">
                        <?= money($stats['total'] - $thirdPartyFinance['total_cost']) ?>
                    </div>
                </div>
                <div class="mt-2 pt-2 border-top border-light d-flex justify-content-between text-muted" style="font-size: 0.84rem;">
                    <span>Chi bên thứ 3: <strong><?= money($thirdPartyFinance['total_cost']) ?></strong></span>
                    <span class="text-teal-main fw-bold">Lãi DV: +<?= money($thirdPartyFinance['service_profit']) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= TAB CHUYỂN ĐỔI: KHOẢN THU (HÓA ĐƠN) & KHOẢN CHI (BÊN THỨ 3) ================= -->
    <ul class="nav nav-pills p-1 bg-white rounded-pill border shadow-sm mb-4 no-print position-relative z-2" id="mainInvoiceTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active py-2 px-4 rounded-pill fw-bold" id="tab-revenue-btn" data-bs-toggle="pill" data-bs-target="#tab-revenue" type="button">
                <i class="fa-solid fa-file-invoice-dollar me-2"></i> Khoản Thu &amp; Hóa Đơn Khách Thuê (<?= count($invoices) ?>)
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link py-2 px-4 rounded-pill fw-bold" id="tab-expense-btn" data-bs-toggle="pill" data-bs-target="#tab-expense" type="button">
                <i class="fa-solid fa-money-bill-transfer me-2 text-danger"></i> Khoản Chi Cho Bên Thứ 3 Hàng Tháng (Điện, Nước, Dịch Vụ)
            </button>
        </li>
    </ul>

    <div class="tab-content position-relative z-2">
        <!-- TAB 1: KHOẢN THU & HÓA ĐƠN KHÁCH THUÊ -->
        <div class="tab-pane fade show active" id="tab-revenue" role="tabpanel">
            <div class="section-box mb-4">
                <!-- SUB-NAV CHUYỂN ĐỔI GIỮA BẢNG 1 VÀ BẢNG 2 -->
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 pb-3 border-bottom border-light no-print">
                    <ul class="nav nav-pills p-1 bg-light rounded-pill border" id="revenueSubTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?= ($filters['subtab'] === 'deposit') ? '' : 'active' ?> rounded-pill fw-bold" id="btn-sub-monthly" data-bs-toggle="pill" data-bs-target="#sub-monthly" type="button" role="tab">
                                <i class="fa-solid fa-file-invoice-dollar me-2 text-teal-main"></i> Bảng 2: Hóa Đơn Hàng Tháng (Phòng + Điện Nước + DV) <span class="badge rounded-pill bg-teal text-white ms-1"><?= count($invoices) ?></span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?= ($filters['subtab'] === 'deposit') ? 'active' : '' ?> rounded-pill fw-bold" id="btn-sub-deposit" data-bs-toggle="pill" data-bs-target="#sub-deposit" type="button" role="tab">
                                <i class="fa-solid fa-shield-halved me-2 text-warning-emphasis"></i> Bảng 1: Cọc &amp; Tiền Phòng Tháng Đầu (Trạng thái) <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis ms-1"><?= count($depositContracts) ?></span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?= ($filters['subtab'] === 'all') ? 'active' : '' ?> rounded-pill fw-bold" id="btn-sub-both" data-bs-toggle="pill" data-bs-target="#sub-both" type="button" role="tab">
                                <i class="fa-solid fa-table-columns me-2 text-primary"></i> Xem Cả 2 Bảng Đồng Thời
                            </button>
                        </li>
                    </ul>
                    <div class="text-muted small fw-bold">
                        <i class="fa-solid fa-circle-info text-teal-main me-1"></i> Bảng 1: Trạng thái cọc &amp; tháng đầu · Bảng 2: Dòng tiền phòng &amp; dịch vụ định kỳ
                    </div>
                </div>

                <div class="tab-content" id="revenueSubTabsContent">
                    <!-- SUB-TAB 2: HÓA ĐƠN HÀNG THÁNG -->
                    <div class="tab-pane fade <?= ($filters['subtab'] === 'deposit' || $filters['subtab'] === 'all') ? '' : 'show active' ?>" id="sub-monthly" role="tabpanel">
                        <?php require __DIR__ . '/table_monthly_invoices.php'; ?>
                    </div>

        <div class="table-rounded-wrap table-responsive">
            <table class="table table-staymate align-middle">
                <thead>
                    <tr>
                        <th>MÃ HĐ</th><th>PHÒNG &amp; KHÁCH</th><th>KỲ</th><th style="min-width:140px;">CỌC (TRẠNG THÁI)</th><th class="text-end">TIỀN PHÒNG</th><th class="text-end">ĐIỆN</th><th class="text-end">NƯỚC</th><th class="text-end">DỊCH VỤ</th><th class="text-end">TỔNG CỘNG</th><th>HẠN TT</th><th>TRẠNG THÁI</th><th class="text-end no-print">THAO TÁC</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($invoices): foreach ($invoices as $inv):
                    $b = $inv['breakdown'];
                    $eUsed = max(0, (int)($inv['electric_new'] ?? 0) - (int)($inv['electric_old'] ?? 0));
                    $wUsed = max(0, (int)($inv['water_new'] ?? 0) - (int)($inv['water_old'] ?? 0));
                ?>
                    <tr>
                        <td><a class="code-pill" href="index.php?route=invoices/detail&id=<?= (int)$inv['id'] ?>"><?= h($inv['code']) ?></a></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge-room-pill"><?= h($inv['room_number']) ?></span>
                                <div class="fw-bold tenant-name" style="font-size:0.92rem"><?= h($inv['tenant_name'] ?? 'Khách thuê') ?></div>
                            </div>
                        </td>
                        <td><span class="badge-period-pill"><i class="fa-regular fa-calendar text-teal-main"></i><?= h($inv['month_year']) ?></span></td>
                        <td>
                            <div class="d-flex flex-column gap-1">
                                <div>
                                    <span class="badge rounded-pill <?= $inv['deposit_badge_class'] ?> fw-bold px-2 py-1">
                                        <i class="<?= $inv['deposit_icon'] ?> me-1"></i> <?= h($inv['deposit_label']) ?>
                                    </span>
                                </div>
                                <div class="small fw-bold text-muted" style="font-size:0.82rem">
                                    <?= h($inv['deposit_sub']) ?>
                                </div>
                                <?php if (!empty($inv['contract_id_val'])): ?>
                                    <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-teal-main fw-bold no-print" style="font-size:.72rem;text-align:left"
                                            onclick="openDepositModal(<?= (int)$inv['contract_id_val'] ?>, <?= h(json_encode($inv['room_number'])) ?>, <?= h(json_encode($inv['tenant_name'] ?? '')) ?>, <?= (float)$inv['deposit_amount'] ?>, '<?= h($inv['deposit_key']) ?>')">
                                        <i class="fa-solid fa-pen-to-square me-1"></i> Sửa cọc
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="text-end"><?= money($b['room']) ?></td>
                        <td class="text-end"><?= money($b['elec']) ?><div class="sub-note"><?= $eUsed ?> kWh</div></td>
                        <td class="text-end"><?= money($b['water']) ?><div class="sub-note"><?= $wUsed ?> m³</div></td>
                        <td class="text-end"><?= money($b['other']) ?></td>
                        <td class="text-end" style="font-weight:900;color:var(--teal-main);font-size:.98rem"><?= money($inv['total_amount']) ?></td>
                        <td><span class="<?= $inv['is_overdue'] ? 'text-danger' : '' ?> fw-bold"><?= date('d/m/Y', strtotime($inv['due_date'])) ?></span></td>
                        <td>
                            <?php if ($inv['status'] === 'paid'): ?>
                                <span class="st-pill st-paid"><i class="fa-solid fa-circle-check"></i> Đã thu</span>
                                <div class="sub-note mt-1"><?= $inv['paid_at'] ? date('d/m/Y', strtotime($inv['paid_at'])) : '' ?> · <?= h($methods[$inv['payment_method'] ?? ''] ?? '') ?></div>
                            <?php elseif ($inv['is_overdue']): ?>
                                <span class="st-pill st-overdue"><i class="fa-solid fa-triangle-exclamation"></i> Quá hạn</span>
                            <?php else: ?>
                                <span class="st-pill st-unpaid"><i class="fa-regular fa-clock"></i> Chưa thu</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end no-print">
                            <div class="d-inline-flex gap-2">
                                <a class="btn-icon-circle btn-icon-view" title="Xem / In hóa đơn" href="index.php?route=invoices/detail&id=<?= (int)$inv['id'] ?>"><i class="fa-solid fa-eye"></i></a>
                                <?php if ($inv['status'] === 'unpaid'): ?>
                                    <button type="button" class="btn-icon-circle btn-icon-pay" title="Xác nhận thu tiền"
                                        onclick="openPayModal(<?= (int)$inv['id'] ?>, <?= h(json_encode($inv['code'])) ?>, <?= h(json_encode($inv['room_number'])) ?>, <?= h(json_encode($inv['tenant_name'] ?? '')) ?>, <?= (float)$inv['total_amount'] ?>)"><i class="fa-solid fa-money-bill-wave"></i></button>
                                    <form method="POST" action="index.php?route=invoices/delete" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa hóa đơn <?= h($inv['code']) ?> không?');">
                                        <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                                        <input type="hidden" name="id" value="<?= (int)$inv['id'] ?>">
                                        <input type="hidden" name="return_query" value="<?= h($returnQuery) ?>">
                                        <button type="submit" class="btn-icon-circle btn-icon-del" title="Xóa hóa đơn"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                <?php else: ?>
                                    <span class="btn-icon-circle btn-icon-lock" title="Đã thu tiền – hóa đơn bị khóa"><i class="fa-solid fa-lock"></i></span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="12" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-folder-open fs-1 text-teal-main opacity-25 mb-3 d-block"></i>
                        <p class="fw-bold mb-2">Chưa có hóa đơn nào phù hợp bộ lọc.</p>
                        <button type="button" class="btn-teal-custom" data-bs-toggle="modal" data-bs-target="#createModal"><i class="fa-solid fa-plus"></i> Lập hóa đơn</button>
                    </td></tr>
                <?php endif; ?>
                </tbody>
                <?php if ($invoices): ?>
                <tfoot>
                    <tr style="background:#f8fafc">
                        <td colspan="8" class="text-end fw-black" style="font-weight:900">TỔNG CỘNG (<?= count($invoices) ?> hóa đơn):</td>
                        <td class="text-end" style="font-weight:900;color:var(--teal-main)"><?= money($stats['total']) ?></td>
                        <td colspan="3" class="small fw-bold">Đã thu <span class="text-success"><?= money($stats['paid']) ?></span> · Còn nợ <span class="text-danger"><?= money($stats['unpaid']) ?></span></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
    </div> <!-- END TAB 1 -->

                    <!-- SUB-TAB XEM CẢ 2 BẢNG ĐỒNG THỜI -->
                    <div class="tab-pane fade <?= ($filters['subtab'] === 'all') ? 'show active' : '' ?>" id="sub-both" role="tabpanel">
                        <div class="mb-5 pb-4 border-bottom">
                            <h5 class="fw-black text-dark mb-3" style="font-weight:900">
                                <i class="fa-solid fa-shield-halved text-warning-emphasis me-2"></i> BẢNG 1: THEO DÕI CỌC &amp; TIỀN PHÒNG THÁNG ĐẦU
                            </h5>
                            <?php require __DIR__ . '/table_deposit_first_month.php'; ?>
                        </div>
                        <div>
                            <h5 class="fw-black text-dark mb-3" style="font-weight:900">
                                <i class="fa-solid fa-file-invoice-dollar text-teal-main me-2"></i> BẢNG 2: HÓA ĐƠN TIỀN PHÒNG HÀNG THÁNG + ĐIỆN NƯỚC, DỊCH VỤ SỬ DỤNG
                            </h5>
                            <?php require __DIR__ . '/table_monthly_invoices.php'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- END TAB 1 -->

        <!-- TAB 2: KHOẢN CHI CHO BÊN THỨ 3 HÀNG THÁNG -->
    <div class="tab-pane fade" id="tab-expense" role="tabpanel">
        <div class="section-box">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 pb-3 border-bottom border-light">
                <div>
                    <h5 class="fw-black text-dark mb-1" style="font-weight:900">
                        <i class="fa-solid fa-money-bill-transfer text-danger me-2"></i> Bảng Kê Khoản Chi Cho Bên Thứ 3 Hàng Tháng — <span class="text-teal-main"><?= $periodLabel ?></span>
                    </h5>
                    <div class="text-muted small fw-semibold">
                        Các chi phí thanh toán cho Điện lực EVN, Công ty Cấp nước, Nhà mạng Internet, Phí rác &amp; Bảo trì cơ sở vật chất
                    </div>
                </div>
                <div class="d-flex gap-2 no-print">
                    <button type="button" class="btn-teal-custom" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                        <i class="fa-solid fa-plus"></i> Ghi nhận khoản chi mới
                    </button>
                </div>
            </div>

            <!-- Thống kê nhanh khoản chi -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="p-3 rounded-4 bg-light border">
                        <div class="text-muted small fw-bold">Chi trả Điện lực (EVN)</div>
                        <div class="fw-black text-warning-emphasis fs-5"><?= money($thirdPartyFinance['total_elec_cost']) ?></div>
                        <div class="small text-muted">Lãi DV: <span class="text-success fw-bold">+<?= money($thirdPartyFinance['elec_profit']) ?></span></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 rounded-4 bg-light border">
                        <div class="text-muted small fw-bold">Chi trả Nước sạch</div>
                        <div class="fw-black text-info-emphasis fs-5"><?= money($thirdPartyFinance['total_water_cost']) ?></div>
                        <div class="small text-muted">Lãi DV: <span class="text-success fw-bold">+<?= money($thirdPartyFinance['water_profit']) ?></span></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 rounded-4 bg-light border">
                        <div class="text-muted small fw-bold">Chi Dịch vụ cố định (Mạng, Rác...)</div>
                        <div class="fw-black text-dark fs-5"><?= money($thirdPartyFinance['total_cost'] - $thirdPartyFinance['total_elec_cost'] - $thirdPartyFinance['total_water_cost']) ?></div>
                        <div class="small text-muted">3 tòa nhà vận hành</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 rounded-4 bg-light border border-teal-subtle">
                        <div class="text-muted small fw-bold">Lợi nhuận chênh lệch DV</div>
                        <div class="fw-black text-teal-main fs-5">+<?= money($thirdPartyFinance['service_profit']) ?></div>
                        <div class="small text-success fw-bold"><i class="fa-solid fa-arrow-trend-up me-1"></i> Đóng góp vào quỹ lãi</div>
                    </div>
                </div>
            </div>

            <div class="table-rounded-wrap table-responsive">
                <table class="table table-staymate align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width:50px">STT</th>
                            <th>KHOẢN CHI VẬN HÀNH</th>
                            <th>BÊN THỨ 3 NHẬN TIỀN</th>
                            <th class="text-center">SẢN LƯỢNG TIÊU THỤ</th>
                            <th class="text-end">ĐƠN GIÁ BÊN THỨ 3</th>
                            <th class="text-end">SỐ TIỀN CHI TRẢ</th>
                            <th class="text-end">THU LẠI TỪ KHÁCH</th>
                            <th class="text-end">CHÊNH LỆCH (LÃI/LỖ)</th>
                            <th>TRẠNG THÁI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($thirdPartyFinance['items'] as $idx => $exp): 
                            $profit = (float)$exp['profit'];
                        ?>
                            <tr>
                                <td class="fw-bold text-muted"><?= $idx + 1 ?></td>
                                <td>
                                    <div class="fw-black text-dark"><?= h($exp['title']) ?></div>
                                    <div class="text-muted small"><?= h($exp['notes']) ?></div>
                                </td>
                                <td>
                                    <div class="fw-bold text-secondary small">
                                        <i class="fa-solid fa-building-columns text-teal-main me-1"></i> <?= h($exp['provider']) ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-pill bg-light text-dark border fw-bold px-2 py-1">
                                        <?= number_format($exp['qty'], 0, ',', '.') ?> <?= h($exp['unit']) ?>
                                    </span>
                                </td>
                                <td class="text-end fw-bold text-muted"><?= money($exp['cost_price']) ?></td>
                                <td class="text-end fw-black text-danger" style="font-size:.95rem;font-weight:900">
                                    <?= money($exp['cost_amount']) ?>
                                </td>
                                <td class="text-end fw-bold text-dark">
                                    <?= $exp['customer_amount'] > 0 ? money($exp['customer_amount']) : '—' ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($profit > 0): ?>
                                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle fw-black px-2 py-1">
                                            +<?= money($profit) ?> (Lãi DV)
                                        </span>
                                    <?php elseif ($profit < 0): ?>
                                        <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle fw-bold px-2 py-1">
                                            <?= money($profit) ?> (Chi phí)
                                        </span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-light text-muted border fw-bold px-2 py-1">
                                            Hòa vốn (0 đ)
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle fw-bold px-2 py-1">
                                        <i class="fa-solid fa-circle-check me-1"></i> Đã thanh toán
                                    </span>
                                    <?php if (!empty($exp['paid_at'])): ?>
                                        <div class="text-muted small" style="font-size:0.82rem"><?= date('d/m/Y', strtotime($exp['paid_at'])) ?></div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="background:#f8fafc;font-weight:900">
                            <td colspan="5" class="text-end">TỔNG CỘNG CHI BÊN THỨ 3:</td>
                            <td class="text-end text-danger fs-6"><?= money($thirdPartyFinance['total_cost']) ?></td>
                            <td class="text-end text-dark"><?= money(array_sum(array_column($thirdPartyFinance['items'], 'customer_amount'))) ?></td>
                            <td class="text-end text-success fs-6">+<?= money($thirdPartyFinance['service_profit']) ?></td>
                            <td class="small text-muted">Lãi ròng dịch vụ</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div> <!-- END TAB 2 -->
    </div> <!-- END TAB CONTENT -->
</div>

<!-- MODAL LẬP HÓA ĐƠN -->
<div class="modal fade modal-staymate" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title text-teal-main mb-0" style="font-weight:900"><i class="fa-solid fa-file-circle-plus me-2"></i> Lập Hóa Đơn Từ Chỉ Số Điện Nước</h5>
                    <div class="text-muted small fw-bold">Tiền phòng + điện + nước + dịch vụ cố định, tự tính từ bảng giá &amp; chỉ số đã chốt</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <?php if ($pendingView): ?>
                    <?php
                    $pendingPeriods = array_values(array_unique(array_column($pendingView, 'month_year')));
                    ?>
                    <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
                        <span class="fw-bold small text-muted">Lập hàng loạt cho kỳ:</span>
                        <?php foreach ($pendingPeriods as $pp):
                            $cnt = count(array_filter($pendingView, fn($x) => $x['month_year'] === $pp)); ?>
                            <form method="POST" action="index.php?route=invoices/store-all" class="d-inline" onsubmit="return confirm('Lập <?= $cnt ?> hóa đơn kỳ <?= h($pp) ?>?');">
                                <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                                <input type="hidden" name="period" value="<?= h($pp) ?>">
                                <input type="hidden" name="return_query" value="<?= h($returnQuery) ?>">
                                <button type="submit" class="btn-pill-amber py-1 px-3" style="font-size:.8rem"><i class="fa-solid fa-layer-group"></i> Kỳ <?= h($pp) ?> (<?= $cnt ?>)</button>
                            </form>
                        <?php endforeach; ?>
                    </div>
                    <div class="table-rounded-wrap table-responsive" style="max-height:55vh;overflow:auto">
                        <table class="table table-staymate align-middle mb-0">
                            <thead><tr><th>PHÒNG &amp; KHÁCH</th><th>KỲ</th><th class="text-end">PHÒNG</th><th class="text-end">ĐIỆN</th><th class="text-end">NƯỚC</th><th class="text-end">DỊCH VỤ</th><th class="text-end">TỔNG DỰ KIẾN</th><th class="text-end">LẬP</th></tr></thead>
                            <tbody>
                            <?php foreach ($pendingView as $pv): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge-room-pill"><?= h($pv['room_number']) ?></span>
                                            <span class="tenant-name fw-bold" style="font-size:0.92rem"><?= h($pv['tenant_name'] ?? 'Chưa có hợp đồng') ?></span>
                                        </div>
                                    </td>
                                    <td><span class="badge-period-pill"><?= h($pv['month_year']) ?></span></td>
                                    <td class="text-end"><?= money($pv['room_price']) ?></td>
                                    <td class="text-end"><?= money($pv['e_used'] * $rates['elec']) ?><div class="sub-note"><?= $pv['e_used'] ?> kWh</div></td>
                                    <td class="text-end"><?= money($pv['w_used'] * $rates['water']) ?><div class="sub-note"><?= $pv['w_used'] ?> m³</div></td>
                                    <td class="text-end"><?= money($rates['others_total']) ?></td>
                                    <td class="text-end" style="font-weight:900;color:var(--teal-main)"><?= money($pv['preview_total']) ?></td>
                                    <td class="text-end">
                                        <?php if ($pv['contract_id']): ?>
                                        <form method="POST" action="index.php?route=invoices/store" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                                            <input type="hidden" name="reading_id" value="<?= (int)$pv['id'] ?>">
                                            <input type="hidden" name="return_query" value="<?= h($returnQuery) ?>">
                                            <button type="submit" class="btn-teal-custom py-1 px-3" style="font-size:.8rem"><i class="fa-solid fa-wand-magic-sparkles"></i> Lập</button>
                                        </form>
                                        <?php else: ?><span class="text-danger small fw-bold">Thiếu hợp đồng</span><?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fa-solid fa-circle-check text-success fs-1 mb-3 d-block"></i>
                        <div class="fw-bold">Tất cả bản ghi điện nước đã được lập hóa đơn.</div>
                        <a href="index.php?route=utilities" class="btn-teal-custom mt-3"><i class="fa-solid fa-bolt"></i> Chốt thêm chỉ số điện nước</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- MODAL THU TIỀN -->
<div class="modal fade modal-staymate" id="payModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-teal-main mb-0" style="font-weight:900"><i class="fa-solid fa-money-bill-wave me-2"></i> Xác Nhận Thu Tiền</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="index.php?route=invoices/pay">
                    <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                    <input type="hidden" name="id" id="pay_id">
                    <input type="hidden" name="return_query" value="<?= h($returnQuery) ?>">
                    <div class="text-center p-3 rounded-4 mb-3" style="background:#f0fdfa;border:2px solid #99f6e4">
                        <div class="small fw-bold text-muted" id="pay_info">—</div>
                        <div class="fw-black text-teal-main" style="font-weight:900;font-size:2rem" id="pay_amount">0 đ</div>
                    </div>
                    <label class="form-label fw-bold small text-uppercase text-muted">Hình thức thanh toán</label>
                    <div class="row g-2 mb-3">
                        <?php $first = true; foreach ($methods as $k => $lbl): $icon = ['cash' => 'fa-money-bill-1', 'transfer' => 'fa-building-columns', 'ewallet' => 'fa-mobile-screen'][$k] ?? 'fa-credit-card'; ?>
                            <div class="col-4">
                                <input type="radio" class="d-none" name="method" id="m_<?= h($k) ?>" value="<?= h($k) ?>" <?= $first ? 'checked' : '' ?>>
                                <label class="method-card" for="m_<?= h($k) ?>"><i class="fa-solid <?= $icon ?>"></i><?= h($lbl) ?></label>
                            </div>
                        <?php $first = false; endforeach; ?>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small text-uppercase text-muted">Ngày thu</label>
                            <input type="date" name="paid_date" class="form-control filter-control" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small text-uppercase text-muted">Ghi chú</label>
                            <input type="text" name="note" class="form-control filter-control" maxlength="255" placeholder="VD: Mã giao dịch...">
                        </div>
                    </div>
                    <div class="alert alert-warning rounded-4 py-2 small fw-bold"><i class="fa-solid fa-lock me-1"></i> Sau khi xác nhận, hóa đơn sẽ bị khóa và không thể sửa/xóa.</div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn-pill-white" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn-teal-custom"><i class="fa-solid fa-check"></i> Xác nhận đã thu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CẬP NHẬT TRẠNG THÁI CỌC -->
<div class="modal fade modal-staymate" id="depositModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-teal-main mb-0" style="font-weight:900">
                    <i class="fa-solid fa-shield-halved me-2"></i> Cập Nhật Trạng Thái Cọc Phòng
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="index.php?route=invoices/update-deposit">
                    <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                    <input type="hidden" name="contract_id" id="dep_contract_id">
                    <input type="hidden" name="return_query" value="<?= h($returnQuery) ?>">

                    <div class="p-3 rounded-4 mb-3" style="background:#f0fdfa;border:2px solid #99f6e4">
                        <div class="small fw-bold text-muted">Phòng &amp; Khách thuê</div>
                        <div class="fw-black text-teal-main fs-5" id="dep_room_info">—</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Trạng thái cọc <span class="text-danger">*</span></label>
                        <div class="d-flex flex-column gap-2">
                            <div class="form-check p-3 rounded-3 border">
                                <input class="form-check-input ms-0 me-2" type="radio" name="deposit_state_key" id="dep_state_50" value="partial_50">
                                <label class="form-check-label fw-bold text-warning-emphasis" for="dep_state_50">
                                    <i class="fa-solid fa-clock-rotate-left me-1"></i> Đã cọc 50%
                                    <div class="small fw-normal text-muted">Khách mới nộp 50% tiền cọc giữ phòng</div>
                                </label>
                            </div>
                            <div class="form-check p-3 rounded-3 border">
                                <input class="form-check-input ms-0 me-2" type="radio" name="deposit_state_key" id="dep_state_100" value="completed">
                                <label class="form-check-label fw-bold text-success" for="dep_state_100">
                                    <i class="fa-solid fa-circle-check me-1"></i> Hoàn thành cọc
                                    <div class="small fw-normal text-muted">Khách đã nộp đầy đủ 100% tiền cọc hợp đồng</div>
                                </label>
                            </div>
                            <div class="form-check p-3 rounded-3 border">
                                <input class="form-check-input ms-0 me-2" type="radio" name="deposit_state_key" id="dep_state_ref" value="refunded">
                                <label class="form-check-label fw-bold text-secondary" for="dep_state_ref">
                                    <i class="fa-solid fa-arrow-rotate-left me-1"></i> Hoàn cọc
                                    <div class="small fw-normal text-muted">Đã hoàn trả lại tiền cọc khi kết thúc hợp đồng</div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Số tiền cọc quy định (VNĐ)</label>
                        <input type="number" name="deposit_amount" id="dep_amount" class="form-control filter-control fw-bold" required min="0" step="50000">
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn-pill-white" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn-teal-custom"><i class="fa-solid fa-check"></i> Lưu trạng thái cọc</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- MODAL CẬP NHẬT TIỀN PHÒNG THÁNG ĐẦU -->
<div class="modal fade modal-staymate" id="firstMonthModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-teal-main mb-0" style="font-weight:900">
                    <i class="fa-solid fa-calendar-check me-2 text-success"></i> Cập Nhật Tiền Phòng Tháng Đầu
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="index.php?route=invoices/update-first-month">
                    <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                    <input type="hidden" name="return_query" value="<?= h($returnQuery) ?>">
                    <input type="hidden" name="subtab" value="deposit">
                    <input type="hidden" name="contract_id" id="fm_contract_id">

                    <div class="p-3 rounded-4 mb-3" style="background:#f0fdfa;border:2px solid #99f6e4">
                        <div class="small fw-bold text-muted">Hợp đồng &amp; Khách thuê</div>
                        <div class="fw-black text-teal-main fs-5" id="fm_info">—</div>
                        <div class="small fw-bold text-dark mt-1" id="fm_rent_amount">Tiền phòng tháng đầu: 0 đ</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Trạng thái thanh toán <span class="text-danger">*</span></label>
                        <select name="first_month_rent_paid" id="fm_status" class="form-select filter-control fw-bold" required>
                            <option value="1">Đã thu tiền phòng tháng đầu</option>
                            <option value="0">Chưa thu tiền phòng tháng đầu (Chờ thanh toán)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Ghi chú thanh toán</label>
                        <input type="text" name="first_month_paid_note" id="fm_note" class="form-control filter-control" placeholder="VD: Khách chuyển khoản khi ký hợp đồng...">
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn-pill-white" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn-teal-custom"><i class="fa-solid fa-check"></i> Lưu trạng thái</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- MODAL GHI NHẬN KHOẢN CHI BÊN THỨ 3 -->
<div class="modal fade modal-staymate" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-teal-main mb-0" style="font-weight:900">
                    <i class="fa-solid fa-file-invoice-dollar me-2 text-danger"></i> Ghi Nhận Khoản Chi Cho Bên Thứ 3
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="index.php?route=invoices/add-expense">
                    <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                    <input type="hidden" name="return_query" value="<?= h($returnQuery) ?>">

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Kỳ chi (MM-YYYY) <span class="text-danger">*</span></label>
                        <input type="text" name="month_year" class="form-control filter-control fw-bold" value="<?= $filters['period'] === 'all' ? date('m-Y') : h($filters['period']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Loại khoản chi <span class="text-danger">*</span></label>
                        <select name="expense_type" class="form-select filter-control fw-bold" required>
                            <option value="electric">Tiền Điện Lực EVN</option>
                            <option value="water">Tiền Nước Sạch</option>
                            <option value="internet">Cước Internet Viễn Thông</option>
                            <option value="garbage">Phí Thu Gom Rác &amp; Môi Trường</option>
                            <option value="maintenance">Bảo Trì &amp; Kỹ Thuật Tòa Nhà</option>
                            <option value="other">Khoản chi khác</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Tiêu đề khoản chi <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control filter-control" placeholder="VD: Hóa đơn điện EVN tháng 10..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Bên thứ 3 nhận tiền <span class="text-danger">*</span></label>
                        <input type="text" name="provider_name" class="form-control filter-control" placeholder="VD: Công ty Điện Lực Cầu Giấy..." required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small text-uppercase text-muted">Số lượng</label>
                            <input type="number" name="quantity" class="form-control filter-control" value="1" min="0" step="any">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small text-uppercase text-muted">Đơn vị tính</label>
                            <input type="text" name="unit" class="form-control filter-control" value="Gói" placeholder="kWh, m3, Gói...">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Số tiền chi trả cho bên thứ 3 (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control filter-control fw-black text-danger fs-5" required min="0" step="1000">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Ghi chú thanh toán</label>
                        <input type="text" name="notes" class="form-control filter-control" placeholder="Mã giao dịch, số tài khoản nhận...">
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn-pill-white" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn-teal-custom"><i class="fa-solid fa-check"></i> Lưu khoản chi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<footer class="footer-custom container-fluid px-4 px-xl-5 d-flex flex-wrap justify-content-between align-items-center">
    <div><i class="fa-solid fa-building me-1" style="color:var(--teal-main)"></i> © 2026 StayMate - Quản lý Nhà trọ Chuyên nghiệp</div>
    <div class="d-flex gap-4">
        <span><i class="fa-solid fa-shield-halved me-1" style="color:var(--teal-main)"></i> WAMP MVC Architecture</span>
        <span><i class="fa-solid fa-bolt me-1 text-warning"></i> Tự động hóa Điện, Nước &amp; Hóa đơn</span>
    </div>
</footer>

<!-- MODAL CHỈNH SỬA HÓA ĐƠN -->
<div class="modal fade modal-staymate" id="editInvoiceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-teal-main mb-0" style="font-weight:900">
                    <i class="fa-solid fa-pen-to-square me-2"></i> Chỉnh Sửa Hóa Đơn
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST" action="index.php?route=invoices/update">
                    <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                    <input type="hidden" name="id" id="edit_inv_id">
                    <input type="hidden" name="return_query" value="<?= h($returnQuery) ?>">

                    <div class="p-3 rounded-4 mb-3" style="background:#f0fdfa;border:2px solid #99f6e4">
                        <div class="small fw-bold text-muted">Hóa đơn &amp; Phòng</div>
                        <div class="fw-black text-teal-main fs-5" id="edit_inv_info">—</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Hạn thanh toán</label>
                        <input type="date" name="due_date" id="edit_inv_due_date" class="form-control filter-control fw-bold" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Trạng thái thanh toán</label>
                        <select name="status" id="edit_inv_status" class="form-select filter-control fw-bold">
                            <option value="unpaid">Chưa thu (Chờ thanh toán)</option>
                            <option value="paid">Đã thu (Đã thanh toán)</option>
                        </select>
                    </div>

                    <div class="mb-3" id="edit_inv_method_group">
                        <label class="form-label fw-bold small text-uppercase text-muted">Phương thức thanh toán (nếu đã thu)</label>
                        <select name="payment_method" id="edit_inv_method" class="form-select filter-control">
                            <option value="transfer">Chuyển khoản ngân hàng</option>
                            <option value="cash">Tiền mặt trực tiếp</option>
                            <option value="ewallet">Ví điện tử (Momo / ZaloPay)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-muted">Ghi chú hóa đơn</label>
                        <input type="text" name="payment_note" id="edit_inv_note" class="form-control filter-control" placeholder="Ghi chú thanh toán...">
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn-pill-white" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn-teal-custom"><i class="fa-solid fa-check"></i> Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function openDepositModal(contractId, room, tenant, amount, stateKey) {
        document.getElementById('dep_contract_id').value = contractId;
        document.getElementById('dep_room_info').textContent = 'Phòng ' + room + (tenant ? ' · ' + tenant : '');
        document.getElementById('dep_amount').value = Math.round(amount);

        if (stateKey === 'partial_50') {
            document.getElementById('dep_state_50').checked = true;
        } else if (stateKey === 'refunded') {
            document.getElementById('dep_state_ref').checked = true;
        } else {
            document.getElementById('dep_state_100').checked = true;
        }

        new bootstrap.Modal(document.getElementById('depositModal')).show();
    }

    function openPayModal(id, code, room, tenant, amount) {
        document.getElementById('pay_id').value = id;
        document.getElementById('pay_info').textContent = code + ' · Phòng ' + room + (tenant ? ' · ' + tenant : '');
        document.getElementById('pay_amount').textContent = Math.round(amount).toLocaleString('vi-VN') + ' đ';
        new bootstrap.Modal(document.getElementById('payModal')).show();
    }

        function openFirstMonthModal(contractId, room, tenant, rent, isPaid, note) {
        document.getElementById('fm_contract_id').value = contractId;
        document.getElementById('fm_info').textContent = 'Phòng ' + room + (tenant ? ' · ' + tenant : '');
        document.getElementById('fm_rent_amount').textContent = 'Tiền phòng tháng đầu: ' + Math.round(rent).toLocaleString('vi-VN') + ' đ';
        document.getElementById('fm_status').value = isPaid ? '1' : '0';
        document.getElementById('fm_note').value = note || '';
        new bootstrap.Modal(document.getElementById('firstMonthModal')).show();
    }

    function openEditInvoiceModal(id, code, room, tenant, dueDate, status, note, method) {
        document.getElementById('edit_inv_id').value = id;
        document.getElementById('edit_inv_info').textContent = code + ' · Phòng ' + room + (tenant ? ' · ' + tenant : '');
        document.getElementById('edit_inv_due_date').value = dueDate || '';
        document.getElementById('edit_inv_status').value = status || 'unpaid';
        document.getElementById('edit_inv_method').value = method || 'transfer';
        document.getElementById('edit_inv_note').value = note || '';
        new bootstrap.Modal(document.getElementById('editInvoiceModal')).show();
    }
</script>

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
</body>
</html>
