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
            background-color: var(--bg-page); font-family: 'Nunito', sans-serif; font-size: 0.9rem; color: #334155;
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
        .nav-pills { gap: 8px; flex-wrap: nowrap; }
        .nav-pills .nav-link { color: #475569; font-weight: 800; font-size: .92rem; padding: 10px 18px; border-radius: 50px; transition: .2s; white-space: nowrap; }
        .nav-pills .nav-link i { margin-right: 6px; }
        .nav-pills .nav-link:hover { color: var(--teal-main); background: #f0fdfa; }
        .nav-pills .nav-link.active { background: var(--teal-main); color: #fff; box-shadow: 0 6px 15px rgba(48,143,134,.35); }
        .avatar-circle { width: 46px; height: 46px; background: var(--teal-main); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 900; border: 3px solid #fff; box-shadow: 0 6px 15px rgba(48,143,134,.4); }

        .btn-back-content { background: #fff; border: 3px solid #fff; color: #334155; font-weight: 800; padding: 10px 26px; border-radius: 50px; transition: .2s; text-decoration: none; display: inline-flex; align-items: center; box-shadow: 0 8px 20px rgba(0,0,0,.08); }
        .btn-back-content:hover { border-color: var(--teal-main); color: var(--teal-main); transform: translateY(-3px); }
        .header-title-wrapper { position: relative; z-index: 2; display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; flex-wrap: wrap; gap: 16px; }
        .main-title { font-weight: 900; color: #0f172a; font-size: 1.95rem; letter-spacing: -.5px; display: inline-flex; align-items: center; flex-wrap: wrap; }
        .bld-highlight { color: var(--teal-main); margin-left: 8px; position: relative; }
        .title-sparkle { position: absolute; background: #fde047; border-radius: 2px; }
        .ts-1 { width: 4px; height: 12px; top: -10px; right: -15px; transform: rotate(20deg); }
        .ts-2 { width: 12px; height: 4px; top: 15px; right: -25px; transform: rotate(15deg); }
        .bld-address-txt { font-size: .92rem; font-weight: 700; color: #64748b; margin-top: 6px; }

        .overview-card { border-radius: 30px; padding: 24px; position: relative; overflow: hidden; box-shadow: 0 15px 35px rgba(0,0,0,.12); border: 4px solid #fff; height: 100%; transition: .3s; }
        .overview-card:hover { transform: translateY(-7px); box-shadow: 0 25px 45px rgba(0,0,0,.17); }
        .card-green { background: linear-gradient(135deg,#dcfce7,#bbf7d0); }
        .card-blue { background: linear-gradient(135deg,#e0f2fe,#bae6fd); }
        .card-yellow { background: linear-gradient(135deg,#fef9c3,#fef08a); }
        .card-purple { background: linear-gradient(135deg,#f3e8ff,#e9d5ff); }
        .overview-title { font-size: .8rem; font-weight: 900; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; text-transform: uppercase; position: relative; z-index: 2; }
        .overview-val { font-size: 2rem; font-weight: 900; line-height: 1.05; letter-spacing: -1px; position: relative; z-index: 2; }
        .overview-icon-bg { position: absolute; right: 20px; top: 22px; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.3rem; box-shadow: 0 8px 18px rgba(0,0,0,.15); z-index: 2; border: 3px solid #fff; }
        .icon-green { background: linear-gradient(135deg,#16a34a,#4ade80); }
        .icon-blue { background: linear-gradient(135deg,#0284c7,#38bdf8); }
        .icon-yellow { background: linear-gradient(135deg,#f59e0b,#eab308); }
        .icon-purple { background: linear-gradient(135deg,#9333ea,#a855f7); }

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
        .sub-note { font-size: .72rem; color: #64748b; font-weight: 700; }

        .badge-room-pill { background: var(--teal-main); color: #fff; padding: 6px 12px; border-radius: 50px; font-weight: 900; font-size: .8rem; box-shadow: 0 4px 10px rgba(48,143,134,.25); display: inline-flex; }
        .badge-period-pill { background: #f1f5f9; color: #334155; border: 2px solid #cbd5e1; padding: 5px 11px; border-radius: 50px; font-weight: 800; font-size: .78rem; display: inline-flex; gap: 6px; align-items: center; white-space: nowrap; }
        .code-pill { font-weight: 900; color: var(--teal-main); background: #f0fdfa; border: 1.5px solid #99f6e4; border-radius: 12px; padding: 4px 10px; font-size: .8rem; text-decoration: none; }
        .code-pill:hover { background: var(--teal-main); color: #fff; }
        .st-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 50px; font-weight: 800; font-size: .76rem; white-space: nowrap; border: 1.5px solid; }
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
    <div class="container-fluid px-4 px-xl-5 d-flex justify-content-between align-items-center">
        <a class="brand-logo me-4" href="index.php?route=dashboard">
            <i class="fa-solid fa-house-chimney"></i>
            <div><div class="brand-text">StayMate</div><div class="brand-sub">Quản Lý Nhà Trọ</div></div>
        </a>
        <ul class="nav nav-pills me-auto d-none d-lg-flex">
            <li class="nav-item"><a class="nav-link" href="index.php?route=dashboard"><i class="fa-solid fa-city"></i> Tòa nhà</a></li>
            <li class="nav-item"><a class="nav-link" href="index.php?route=overview"><i class="fa-solid fa-border-all"></i> Tổng quan</a></li>
            <li class="nav-item"><a class="nav-link" href="index.php?route=rooms"><i class="fa-solid fa-bed"></i> Phòng trọ</a></li>
            <li class="nav-item"><a class="nav-link" href="index.php?route=contracts"><i class="fa-solid fa-file-signature"></i> Hợp đồng</a></li>
            <li class="nav-item"><a class="nav-link" href="index.php?route=utilities"><i class="fa-solid fa-bolt"></i> Điện Nước</a></li>
            <li class="nav-item"><a class="nav-link active" href="index.php?route=invoices"><i class="fa-solid fa-file-invoice-dollar"></i> Hóa đơn</a></li>
            <li class="nav-item"><a class="nav-link" href="TrangXemPhong/" target="_blank"><i class="fa-solid fa-eye"></i> Xem phòng</a></li>
        </ul>
        <div class="d-flex align-items-center gap-4">
            <div class="position-relative" style="color:#64748b" title="Hóa đơn quá hạn">
                <i class="fa-solid fa-bell fs-4"></i>
                <?php if ($stats['overdue_count'] > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white"><?= (int)$stats['overdue_count'] ?></span>
                <?php endif; ?>
            </div>
            <div class="dropdown">
                <div class="d-flex align-items-center gap-2" style="cursor:pointer" data-bs-toggle="dropdown">
                    <div class="avatar-circle">AS</div>
                    <div class="text-start d-none d-sm-block lh-sm">
                        <div class="fw-bold text-dark" style="font-size:.9rem">Quản trị viên Hệ thống <i class="fa-solid fa-angle-down ms-1 text-muted fs-6"></i></div>
                        <div class="text-muted" style="font-size:.75rem">Admin</div>
                    </div>
                </div>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 mt-2">
                    <li><a class="dropdown-item text-danger fw-bold" href="index.php?route=auth/logout"><i class="fa-solid fa-right-from-bracket me-2"></i> Đăng xuất</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<div class="container-fluid px-4 px-xl-5 py-4 position-relative">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 no-print">
        <a href="index.php?route=overview" class="btn-back-content mb-2"><i class="fa-solid fa-arrow-left me-2"></i> Quay lại Tổng quan</a>
        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
            <span class="fw-bold text-muted small"><i class="fa-regular fa-calendar-check me-1 text-teal-main"></i> Kỳ đang xem:</span>
            <?php foreach (array_slice($periods, 0, 5) as $per): ?>
                <a href="index.php?route=invoices&period=<?= urlencode($per) ?>" class="<?= $filters['period'] === $per ? 'btn-teal-custom' : 'btn-pill-white' ?> py-1 px-3" style="font-size:.82rem">Kỳ <?= h($per) ?></a>
            <?php endforeach; ?>
            <a href="index.php?route=invoices&period=all" class="<?= $filters['period'] === 'all' ? 'btn-teal-custom' : 'btn-pill-white' ?> py-1 px-3" style="font-size:.82rem">Tất cả các kỳ</a>
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
                <span class="bld-highlight"><?= h($periodLabel) ?><span class="title-sparkle ts-1"></span><span class="title-sparkle ts-2"></span></span>
            </div>
            <div class="bld-address-txt d-flex flex-wrap gap-2 mt-2">
                <span class="rate-chip"><i class="fa-solid fa-bolt text-warning"></i> Điện: <?= money($rates['elec']) ?>/kWh</span>
                <span class="rate-chip"><i class="fa-solid fa-droplet text-info"></i> Nước: <?= money($rates['water']) ?>/Khối</span>
                <?php foreach ($rates['others'] as $o): ?>
                    <span class="rate-chip"><i class="fa-solid fa-wifi text-teal-main"></i> <?= h($o['service_name']) ?>: <?= money($o['unit_price']) ?>/<?= h(mb_strtolower($o['unit'])) ?></span>
                <?php endforeach; ?>
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

    <!-- 4 THẺ THỐNG KÊ -->
    <div class="row g-4 mb-4 position-relative" style="z-index:2">
        <div class="col-12 col-md-6 col-lg-3">
            <div class="overview-card card-green">
                <div class="overview-icon-bg icon-green"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                <div class="overview-title text-success"><i class="fa-solid fa-receipt fs-6"></i> TỔNG HÓA ĐƠN</div>
                <div class="overview-val text-dark mt-2"><?= (int)$stats['count'] ?> <span class="fs-6 fw-bold text-muted">hóa đơn</span></div>
                <div class="mt-3 text-success fw-bold" style="font-size:.82rem"><i class="fa-solid fa-coins me-1"></i> Tổng phải thu: <span class="text-dark"><?= money($stats['total']) ?></span></div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="overview-card card-blue">
                <div class="overview-icon-bg icon-blue"><i class="fa-solid fa-wallet"></i></div>
                <div class="overview-title text-primary"><i class="fa-solid fa-hand-holding-dollar fs-6"></i> ĐÃ THU</div>
                <div class="overview-val text-dark mt-2" style="font-size:1.7rem"><?= money($stats['paid']) ?></div>
                <div class="progress mt-3" style="height:9px;background:rgba(255,255,255,.75);border-radius:10px"><div class="progress-bar bg-primary" style="width:<?= (int)$stats['rate'] ?>%;border-radius:10px"></div></div>
                <div class="d-flex justify-content-between text-primary fw-bold mt-1" style="font-size:.8rem"><span><?= (int)$stats['paid_count'] ?> hóa đơn</span><span><?= (int)$stats['rate'] ?>% đã thu</span></div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="overview-card card-yellow">
                <div class="overview-icon-bg icon-yellow"><i class="fa-solid fa-hourglass-half"></i></div>
                <div class="overview-title" style="color:#b45309"><i class="fa-regular fa-clock fs-6"></i> CÔNG NỢ CHƯA THU</div>
                <div class="overview-val mt-2" style="font-size:1.7rem;color:#b45309"><?= money($stats['unpaid']) ?></div>
                <div class="mt-3 fw-bold" style="font-size:.82rem;color:#b45309"><i class="fa-solid fa-file-circle-exclamation me-1"></i> Tồn đọng: <span class="text-danger"><?= (int)$stats['unpaid_count'] ?> hóa đơn</span></div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
            <div class="overview-card card-purple">
                <div class="overview-icon-bg icon-purple"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div class="overview-title" style="color:#7e22ce"><i class="fa-solid fa-bell fs-6"></i> QUÁ HẠN THANH TOÁN</div>
                <div class="overview-val mt-2" style="font-size:1.7rem;color:#7e22ce"><?= money($stats['overdue']) ?></div>
                <div class="mt-3 fw-bold" style="font-size:.82rem;color:#7e22ce"><i class="fa-solid fa-calendar-xmark me-1"></i> <?= (int)$stats['overdue_count'] ?> hóa đơn (hạn: ngày 10 tháng sau)</div>
            </div>
        </div>
    </div>

    <!-- BỘ LỌC + BẢNG -->
    <div class="section-box">
        <form method="GET" action="index.php" class="row g-3 align-items-end mb-4 pb-3 border-bottom border-light no-print">
            <input type="hidden" name="route" value="invoices">
            <div class="col-12 col-lg-3">
                <label class="form-label fw-bold text-teal-main text-uppercase mb-1" style="font-size:.75rem"><i class="fa-solid fa-magnifying-glass me-1"></i> Tìm kiếm</label>
                <input type="text" name="keyword" class="form-control filter-control" placeholder="Mã HD, số phòng, tên khách..." value="<?= h($filters['keyword']) ?>">
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label fw-bold text-muted text-uppercase mb-1" style="font-size:.75rem">Kỳ</label>
                <select name="period" class="form-select filter-control">
                    <option value="all" <?= $filters['period'] === 'all' ? 'selected' : '' ?>>-- Tất cả --</option>
                    <?php foreach ($periods as $per): ?><option value="<?= h($per) ?>" <?= $filters['period'] === $per ? 'selected' : '' ?>>Kỳ <?= h($per) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label fw-bold text-muted text-uppercase mb-1" style="font-size:.75rem">Phòng</label>
                <select name="room_id" class="form-select filter-control">
                    <option value="">-- Tất cả --</option>
                    <?php foreach ($rooms as $rm): ?><option value="<?= (int)$rm['id'] ?>" <?= (string)$filters['room_id'] === (string)$rm['id'] ? 'selected' : '' ?>>Phòng <?= h($rm['room_number']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label fw-bold text-muted text-uppercase mb-1" style="font-size:.75rem">Trạng thái</label>
                <select name="status" class="form-select filter-control">
                    <option value="">-- Tất cả --</option>
                    <option value="paid" <?= $filters['status'] === 'paid' ? 'selected' : '' ?>>Đã thu</option>
                    <option value="unpaid" <?= $filters['status'] === 'unpaid' ? 'selected' : '' ?>>Chưa thu (trong hạn)</option>
                    <option value="overdue" <?= $filters['status'] === 'overdue' ? 'selected' : '' ?>>Quá hạn</option>
                </select>
            </div>
            <div class="col-6 col-lg-3 d-flex gap-2">
                <button type="submit" class="btn-teal-custom flex-fill justify-content-center"><i class="fa-solid fa-filter"></i> Lọc</button>
                <a href="index.php?route=invoices&period=all" class="btn-pill-white" title="Xóa bộ lọc"><i class="fa-solid fa-rotate-left"></i></a>
            </div>
        </form>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="fw-bold text-dark" style="font-weight:900;font-size:1.05rem"><i class="fa-solid fa-list-check me-2 text-teal-main"></i> Danh sách Hóa đơn (<?= count($invoices) ?>)</div>
            <div class="text-muted small fw-bold">Hóa đơn đã thu tiền sẽ bị khóa, không sửa/xóa được</div>
        </div>

        <div class="table-rounded-wrap table-responsive">
            <table class="table table-staymate align-middle">
                <thead>
                    <tr>
                        <th>MÃ HĐ</th><th>PHÒNG &amp; KHÁCH</th><th>KỲ</th><th class="text-end">TIỀN PHÒNG</th><th class="text-end">ĐIỆN</th><th class="text-end">NƯỚC</th><th class="text-end">DỊCH VỤ</th><th class="text-end">TỔNG CỘNG</th><th>HẠN TT</th><th>TRẠNG THÁI</th><th class="text-end no-print">THAO TÁC</th>
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
                                <div class="fw-bold tenant-name" style="font-size:.85rem"><?= h($inv['tenant_name'] ?? 'Khách thuê') ?></div>
                            </div>
                        </td>
                        <td><span class="badge-period-pill"><i class="fa-regular fa-calendar text-teal-main"></i><?= h($inv['month_year']) ?></span></td>
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
                                    <form method="POST" action="index.php?route=invoices/delete" class="d-inline" onsubmit="return confirm('Xóa hóa đơn <?= h($inv['code']) ?>? Có thể lập lại từ bản ghi điện nước.');">
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
                    <tr><td colspan="11" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-folder-open fs-1 text-teal-main opacity-25 mb-3 d-block"></i>
                        <p class="fw-bold mb-2">Chưa có hóa đơn nào phù hợp bộ lọc.</p>
                        <button type="button" class="btn-teal-custom" data-bs-toggle="modal" data-bs-target="#createModal"><i class="fa-solid fa-plus"></i> Lập hóa đơn</button>
                    </td></tr>
                <?php endif; ?>
                </tbody>
                <?php if ($invoices): ?>
                <tfoot>
                    <tr style="background:#f8fafc">
                        <td colspan="7" class="text-end fw-black" style="font-weight:900">TỔNG CỘNG (<?= count($invoices) ?> hóa đơn):</td>
                        <td class="text-end" style="font-weight:900;color:var(--teal-main)"><?= money($stats['total']) ?></td>
                        <td colspan="3" class="small fw-bold">Đã thu <span class="text-success"><?= money($stats['paid']) ?></span> · Còn nợ <span class="text-danger"><?= money($stats['unpaid']) ?></span></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
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
                                            <span class="tenant-name fw-bold" style="font-size:.85rem"><?= h($pv['tenant_name'] ?? 'Chưa có hợp đồng') ?></span>
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

<footer class="footer-custom container-fluid px-4 px-xl-5 d-flex flex-wrap justify-content-between align-items-center">
    <div><i class="fa-solid fa-building me-1" style="color:var(--teal-main)"></i> © 2026 StayMate - Quản lý Nhà trọ Chuyên nghiệp</div>
    <div class="d-flex gap-4">
        <span><i class="fa-solid fa-shield-halved me-1" style="color:var(--teal-main)"></i> WAMP MVC Architecture</span>
        <span><i class="fa-solid fa-bolt me-1 text-warning"></i> Tự động hóa Điện, Nước &amp; Hóa đơn</span>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function openPayModal(id, code, room, tenant, amount) {
        document.getElementById('pay_id').value = id;
        document.getElementById('pay_info').textContent = code + ' · Phòng ' + room + (tenant ? ' · ' + tenant : '');
        document.getElementById('pay_amount').textContent = Math.round(amount).toLocaleString('vi-VN') + ' đ';
        new bootstrap.Modal(document.getElementById('payModal')).show();
    }
</script>
</body>
</html>
