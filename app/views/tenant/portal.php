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

$csrf = $_SESSION['csrf_token'] ?? '';
$tenantName = $profile['full_name'] ?? ($_SESSION['full_name'] ?? 'Khách thuê');
$roomNumber = $profile['room_number'] ?? 'Chưa gán phòng';
$buildingName = $profile['building_name'] ?? 'Khu Căn Hộ StayMate';
$contractCode = $contract['contract_code'] ?? 'Chưa có hợp đồng';
$userRole = $_SESSION['role'] ?? 'tenant';
$isManagerPreview = ($userRole === 'admin' || $userRole === 'manager' || $userRole === 'landlord');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayMate - Cổng Thông Tin Khách Thuê</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --teal-main: #308f86;
            --teal-hover: #25756d;
            --teal-soft: #e6f4f2;
            --teal-bg: #f0fdfa;
            --coral-accent: #f97316;
            --coral-hover: #ea580c;
            --bg-page: #f8fafc;
            --dark-card: #0f172a;
        }
        body {
            background-color: var(--bg-page);
            font-family: 'Nunito', sans-serif;
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
        .bg-teal-main { background-color: var(--teal-main) !important; color: #fff; }
        .bg-teal-soft { background-color: var(--teal-soft) !important; }

        /* Navbar */
        .navbar-custom {
            background: #ffffff;
            padding: 12px 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            border-bottom: 1px solid #f1f5f9;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .brand-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .brand-logo i { color: var(--teal-main); font-size: 2.2rem; }
        .brand-text { color: var(--teal-main); font-weight: 900; font-size: 1.4rem; line-height: 1; letter-spacing: -0.5px; }
        .brand-sub { color: #64748b; font-size: 0.75rem; font-weight: 600; }

        .tenant-tag {
            background: #f0fdfa;
            border: 1.5px solid #99f6e4;
            color: var(--teal-main);
            padding: 6px 14px;
            border-radius: 50px;
            font-weight: 800;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* Banner Welcome */
        .welcome-card {
            background: linear-gradient(135deg, #186b62 0%, #2f8e85 60%, #46aba1 100%);
            border-radius: 24px;
            color: #ffffff;
            padding: 28px 32px;
            margin-bottom: 24px;
            box-shadow: 0 12px 30px rgba(48,143,134,0.22);
            position: relative;
            overflow: hidden;
        }
        .welcome-card::after {
            content: '';
            position: absolute;
            right: -20px;
            bottom: -30px;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, rgba(255,255,255,0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        /* Stat cards */
        .stat-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 18px 20px;
            border: 2px solid #ffffff;
            box-shadow: 0 8px 24px rgba(0,0,0,0.04);
            display: flex;
            align-items: center;
            gap: 16px;
            transition: all 0.25s ease;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(0,0,0,0.08);
            border-color: #ccfbf1;
        }
        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        /* Nav Tabs */
        .nav-portal {
            display: flex;
            gap: 8px;
            background: #ffffff;
            padding: 8px;
            border-radius: 20px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.03);
            border: 1px solid #e2e8f0;
            margin-bottom: 24px;
            overflow-x: auto;
        }
        .nav-portal .nav-link {
            border: none;
            color: #475569;
            font-weight: 800;
            font-size: 0.92rem;
            padding: 10px 20px;
            border-radius: 14px;
            transition: all 0.2s ease;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .nav-portal .nav-link:hover {
            background: #f1f5f9;
            color: var(--teal-main);
        }
        .nav-portal .nav-link.active {
            background: var(--teal-main);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(48,143,134,0.3);
        }

        /* Content Card */
        .portal-card {
            background: #ffffff;
            border-radius: 24px;
            border: 2px solid #ffffff;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            padding: 28px;
            margin-bottom: 24px;
        }
        .card-header-custom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
            flex-wrap: wrap;
            gap: 12px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 16px;
        }
        .card-header-title {
            font-size: 1.25rem;
            font-weight: 900;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
        }

        /* Table styles */
        .table-custom {
            width: 100%;
            vertical-align: middle;
        }
        .table-custom thead th {
            background: #f8fafc;
            color: #64748b;
            font-weight: 800;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px 14px;
            border-bottom: 2px solid #e2e8f0;
        }
        .table-custom tbody td {
            padding: 14px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.9rem;
        }
        .table-custom tbody tr:hover {
            background: #f8fafc;
        }

        /* Badges */
        .badge-paid {
            background: #dcfce7;
            color: #166534;
            font-weight: 800;
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .badge-unpaid {
            background: #fee2e2;
            color: #991b1b;
            font-weight: 800;
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .badge-pending {
            background: #fef3c7;
            color: #92400e;
            font-weight: 800;
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        /* Form readonly style */
        .form-control-readonly {
            background-color: #f1f5f9 !important;
            border: 1.5px solid #e2e8f0;
            color: #334155;
            font-weight: 700;
            cursor: not-allowed;
            border-radius: 12px;
            padding: 10px 14px;
        }
        .readonly-lock {
            color: #94a3b8;
            font-size: 0.82rem;
            margin-left: 6px;
        }

        /* Contract Card Banner */
        .contract-highlight-box {
            background: linear-gradient(135deg, #f0fdfa 0%, #e6f4f2 100%);
            border: 2px dashed #5eead4;
            border-radius: 20px;
            padding: 22px;
        }

        /* Buttons */
        .btn-teal {
            background-color: var(--teal-main);
            color: #ffffff;
            font-weight: 800;
            border-radius: 12px;
            padding: 9px 18px;
            border: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-teal:hover {
            background-color: var(--teal-hover);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(48,143,134,0.3);
        }
        .btn-pdf-tab {
            background: #ffffff;
            color: #e11d48;
            border: 2px solid #fda4af;
            font-weight: 800;
            border-radius: 14px;
            padding: 11px 22px;
            transition: all 0.25s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(225,29,72,0.1);
            text-decoration: none;
        }
        .btn-pdf-tab:hover {
            background: #e11d48;
            color: #ffffff;
            border-color: #e11d48;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(225,29,72,0.25);
        }

        .notice-banner {
            background: #fffbeb;
            border: 1.5px solid #fde68a;
            border-radius: 16px;
            padding: 14px 18px;
            color: #92400e;
            font-size: 0.88rem;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <header class="navbar-custom">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <a href="index.php?route=tenant/portal" class="brand-logo">
                    <i class="fa-solid fa-house-chimney"></i>
                    <div>
                        <div class="brand-text">StayMate</div>
                        <div class="brand-sub">Cổng Khách Thuê Thông Minh</div>
                    </div>
                </a>
                <span class="tenant-tag d-none d-md-inline-flex">
                    <i class="fa-solid fa-door-open text-teal-main"></i> Phòng: <strong><?= h($roomNumber) ?></strong>
                </span>
            </div>

            <!-- Profile & Actions -->
            <div class="d-flex align-items-center gap-2">
                <?php if ($isManagerPreview): ?>
                    <!-- Dropdown chuyển đổi khách thuê xem thử nếu là Admin / Manager -->
                    <div class="dropdown me-2">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle rounded-pill fw-bold" type="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-user-secret me-1"></i> Đang xem: <?= h($tenantName) ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="max-height: 300px; overflow-y: auto;">
                            <li><h6 class="dropdown-header">Chọn khách thuê xem thử:</h6></li>
                            <?php foreach ($allTenants as $t): ?>
                                <li>
                                    <a class="dropdown-item <?= ($t['id'] == ($profile['id'] ?? 0)) ? 'active fw-bold' : '' ?>"
                                       href="index.php?route=tenant/portal&tenant_id=<?= (int)$t['id'] ?>">
                                        <?= h($t['full_name']) ?> (<?= h($t['username']) ?>)
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <a href="index.php?route=dashboard" class="btn btn-sm btn-outline-secondary rounded-pill fw-bold me-2">
                        <i class="fa-solid fa-arrow-left me-1"></i> Trang Quản lý
                    </a>
                <?php endif; ?>

                <div class="d-flex align-items-center gap-2 me-2">
                    <div class="avatar-circle bg-teal-main" style="width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.9rem;">
                        <?= mb_substr($tenantName, 0, 1, 'UTF-8') ?>
                    </div>
                    <div class="d-none d-sm-block text-start">
                        <div class="fw-bold text-dark" style="font-size: 0.88rem; line-height: 1.2;"><?= h($tenantName) ?></div>
                        <div class="text-muted" style="font-size: 0.75rem;"><?= h($profile['phone'] ?? '') ?></div>
                    </div>
                </div>

                <a href="index.php?route=auth/logout" class="btn btn-sm btn-light border rounded-pill px-3 fw-bold text-danger" title="Đăng xuất">
                    <i class="fa-solid fa-right-from-bracket me-1"></i> Đăng xuất
                </a>
            </div>
        </div>
    </header>

    <!-- MAIN CONTAINER -->
    <main class="container-fluid px-3 px-lg-4 py-4 flex-grow-1">

        <!-- THÔNG BÁO FLASH MESSAGE -->
        <?php if (!empty($flash_success)): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 d-flex align-items-center gap-3 mb-4" role="alert">
                <i class="fa-solid fa-circle-check fs-4 text-success"></i>
                <div class="fw-bold"><?= h($flash_success) ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($flash_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 d-flex align-items-center gap-3 mb-4" role="alert">
                <i class="fa-solid fa-triangle-exclamation fs-4 text-danger"></i>
                <div class="fw-bold"><?= h($flash_error) ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- BANNER WELCOME & SUMMARY -->
        <div class="welcome-card">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-25 px-3 py-1 rounded-pill mb-2" style="font-size: 0.82rem; font-weight: 700;">
                        <i class="fa-solid fa-building-user"></i> Căn hộ thuê: <?= h($buildingName) ?>
                    </div>
                    <h2 class="fw-extrabold mb-2" style="font-weight: 900; letter-spacing: -0.5px;">
                        Xin chào, <?= h($tenantName) ?>! 👋
                    </h2>
                    <p class="mb-0 opacity-90" style="font-size: 0.95rem; max-width: 600px;">
                        Chào mừng bạn đến với Cổng tra cứu thông tin khách thuê StayMate. Tại đây bạn có thể xem hóa đơn hàng tháng, kiểm tra đơn giá dịch vụ minh bạch, tra cứu hợp đồng và gửi yêu cầu hỗ trợ đến Ban Quản Lý.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <div class="bg-white bg-opacity-10 p-3 rounded-4 border border-white border-opacity-25 d-inline-block text-start">
                        <div class="small opacity-75">Phòng bạn đang ở:</div>
                        <div class="fs-4 fw-black text-warning"><i class="fa-solid fa-key me-1"></i> Phòng <?= h($roomNumber) ?></div>
                        <div class="small opacity-90"><i class="fa-solid fa-file-contract me-1"></i> Mã HĐ: <strong><?= h($contractCode) ?></strong></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4 THẺ THỐNG KÊ NHANH -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #e0f2fe; color: #0284c7;">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-bold">Tổng hóa đơn</div>
                        <div class="fs-5 fw-extrabold text-dark" style="font-weight: 800;"><?= $totalInvoices ?> kỳ</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: <?= $unpaidCount > 0 ? '#fee2e2' : '#dcfce7' ?>; color: <?= $unpaidCount > 0 ? '#e11d48' : '#16a34a' ?>;">
                        <i class="fa-solid <?= $unpaidCount > 0 ? 'fa-clock-rotate-left' : 'fa-circle-check' ?>"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-bold">Chưa thanh toán</div>
                        <div class="fs-5 fw-extrabold <?= $unpaidCount > 0 ? 'text-danger' : 'text-success' ?>" style="font-weight: 800;">
                            <?= $unpaidCount ?> kỳ <?= $unpaidAmount > 0 ? '(' . money($unpaidAmount) . ')' : '' ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #fef3c7; color: #d97706;">
                        <i class="fa-solid fa-file-signature"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-bold">Thời hạn hợp đồng</div>
                        <div class="fs-5 fw-extrabold text-dark" style="font-weight: 800;">
                            <?= (isset($contract['days_left']) && (int)$contract['days_left'] >= 0) ? 'Còn ' . (int)$contract['days_left'] . ' ngày' : 'Đang hiệu lực' ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #f0fdfa; color: var(--teal-main);">
                        <i class="fa-solid fa-bell-concierge"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-bold">Dịch vụ đang dùng</div>
                        <div class="fs-5 fw-extrabold text-teal-main" style="font-weight: 800;"><?= count($services) ?> dịch vụ</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB NAVIGATION -->
        <ul class="nav nav-portal" id="portalTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoices-content" type="button" role="tab">
                    <i class="fa-solid fa-file-invoice-dollar"></i> Hóa đơn của tôi
                    <?php if ($unpaidCount > 0): ?>
                        <span class="badge bg-danger rounded-pill ms-1"><?= $unpaidCount ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="contract-tab" data-bs-toggle="tab" data-bs-target="#contract-content" type="button" role="tab">
                    <i class="fa-solid fa-file-contract"></i> Hợp đồng thuê phòng
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="services-tab" data-bs-toggle="tab" data-bs-target="#services-content" type="button" role="tab">
                    <i class="fa-solid fa-tags"></i> Bảng đơn giá dịch vụ
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-content" type="button" role="tab">
                    <i class="fa-solid fa-id-card"></i> Thông tin cá nhân &amp; Yêu cầu
                    <?php if (!empty($requests)): ?>
                        <span class="badge bg-secondary rounded-pill ms-1"><?= count($requests) ?></span>
                    <?php endif; ?>
                </button>
            </li>
        </ul>

        <!-- TAB CONTENTS -->
        <div class="tab-content" id="portalTabContent">

            <!-- ========================================== -->
            <!-- TAB 1: BẢNG HÓA ĐƠN CỦA RIÊNG KHÁCH THUÊ -->
            <!-- ========================================== -->
            <div class="tab-pane fade show active" id="invoices-content" role="tabpanel">
                <div class="portal-card">
                    <div class="card-header-custom">
                        <div>
                            <h3 class="card-header-title">
                                <i class="fa-solid fa-file-invoice-dollar text-teal-main"></i> Hóa Đơn Tiền Phòng &amp; Dịch Vụ Của Bạn
                            </h3>
                            <p class="text-muted small mb-0 mt-1">Danh sách tất cả các kỳ hóa đơn phòng <?= h($roomNumber) ?>. Nhấn "Xem chi tiết &amp; Thanh toán" để xem bóc tách chi phí và mã QR thanh toán nhanh.</p>
                        </div>
                        <?php if ($unpaidCount > 0): ?>
                            <div class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 p-2 px-3 rounded-pill fw-bold">
                                <i class="fa-solid fa-circle-exclamation me-1"></i> Có <?= $unpaidCount ?> hóa đơn chưa thanh toán (Tổng nợ: <?= money($unpaidAmount) ?>)
                            </div>
                        <?php else: ?>
                            <div class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 p-2 px-3 rounded-pill fw-bold">
                                <i class="fa-solid fa-check-circle me-1"></i> Bạn đã hoàn thành tất cả hóa đơn!
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($invoices)): ?>
                        <div class="text-center py-5">
                            <i class="fa-solid fa-receipt fs-1 text-muted opacity-50 mb-3"></i>
                            <h5 class="fw-bold text-dark">Chưa có hóa đơn nào được phát hành</h5>
                            <p class="text-muted">Khi Ban Quản Lý chốt chỉ số điện nước cuối tháng và lập hóa đơn, thông tin sẽ hiển thị đầy đủ tại đây.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-custom">
                                <thead>
                                    <tr>
                                        <th>Mã HĐ</th>
                                        <th>Kỳ hóa đơn</th>
                                        <th>Phòng</th>
                                        <th>Điện tiêu thụ</th>
                                        <th>Nước tiêu thụ</th>
                                        <th>Hạn thanh toán</th>
                                        <th class="text-end">Tổng tiền</th>
                                        <th class="text-center">Trạng thái</th>
                                        <th class="text-center">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($invoices as $inv): ?>
                                        <tr>
                                            <td class="fw-bold text-teal-main"><?= h($inv['code']) ?></td>
                                            <td class="fw-bold text-dark">Tháng <?= h($inv['month_year']) ?></td>
                                            <td><span class="badge bg-light text-dark border fw-bold"><?= h($inv['room_number']) ?></span></td>
                                            <td>
                                                <span class="fw-bold text-primary"><?= (int)$inv['electric_used'] ?> kWh</span>
                                                <span class="text-muted small">(<?= (int)$inv['electric_old'] ?> &rarr; <?= (int)$inv['electric_new'] ?>)</span>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-info"><?= (int)$inv['water_used'] ?> khối/người</span>
                                                <span class="text-muted small">(<?= (int)$inv['water_old'] ?> &rarr; <?= (int)$inv['water_new'] ?>)</span>
                                            </td>
                                            <td>
                                                <span class="fw-bold <?= $inv['is_overdue'] ? 'text-danger' : 'text-secondary' ?>">
                                                    <?= date('d/m/Y', strtotime($inv['due_date'])) ?>
                                                    <?php if ($inv['is_overdue']): ?>
                                                        <span class="badge bg-danger rounded-pill" style="font-size:0.7rem;">Quá hạn</span>
                                                    <?php endif; ?>
                                                </span>
                                            </td>
                                            <td class="text-end fw-extrabold fs-6 text-dark" style="font-weight: 800;">
                                                <?= money($inv['total_amount']) ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($inv['status'] === 'paid'): ?>
                                                    <span class="badge-paid"><i class="fa-solid fa-circle-check"></i> Đã thanh toán</span>
                                                <?php elseif ($inv['is_overdue']): ?>
                                                    <span class="badge-unpaid"><i class="fa-solid fa-triangle-exclamation"></i> Quá hạn</span>
                                                <?php else: ?>
                                                    <span class="badge-unpaid"><i class="fa-solid fa-clock"></i> Chưa thanh toán</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-teal rounded-pill"
                                                        onclick="viewInvoiceDetail(<?= (int)$inv['id'] ?>, '<?= h($inv['code']) ?>', '<?= h($inv['month_year']) ?>', '<?= money($inv['total_amount']) ?>', '<?= h($inv['status']) ?>')">
                                                    <i class="fa-solid fa-eye me-1"></i> Chi tiết &amp; QR
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 2: HỢP ĐỒNG CỦA KHÁCH THUÊ (XEM PDF TAB NHỎ) -->
            <!-- ========================================== -->
            <div class="tab-pane fade" id="contract-content" role="tabpanel">
                <div class="portal-card">
                    <div class="card-header-custom">
                        <div>
                            <h3 class="card-header-title">
                                <i class="fa-solid fa-file-contract text-teal-main"></i> Hợp Đồng Thuê Phòng Của Bạn
                            </h3>
                            <p class="text-muted small mb-0 mt-1">Thông tin pháp lý hợp đồng thuê giữa bạn và Ban Quản Lý StayMate. Theo quy định pháp lý, khách thuê không thể tự sửa hợp đồng.</p>
                        </div>

                        <!-- NÚT MỞ HỢP ĐỒNG TRONG TAB NHỎ / PDF -->
                        <div>
                            <a href="index.php?route=tenant/contract-pdf<?= !empty($contract['id']) ? '&id=' . (int)$contract['id'] : '' ?><?= $isManagerPreview ? '&tenant_id=' . (int)($profile['id'] ?? 0) : '' ?>"
                               target="_blank"
                               onclick="window.open(this.href, 'ContractPDFWindow', 'width=880,height=960,scrollbars=yes,resizable=yes'); return false;"
                               class="btn-pdf-tab">
                                <i class="fa-solid fa-file-pdf fs-5"></i> Xem Hợp Đồng PDF (Mở tab nhỏ / In A4)
                            </a>
                        </div>
                    </div>

                    <?php if (!$contract): ?>
                        <div class="text-center py-5">
                            <i class="fa-solid fa-file-excel fs-1 text-muted opacity-50 mb-3"></i>
                            <h5 class="fw-bold text-dark">Chưa có hợp đồng nào được gán cho tài khoản này</h5>
                            <p class="text-muted">Vui lòng liên hệ Ban Quản Lý tòa nhà để được kiểm tra và kích hoạt hợp đồng thuê phòng.</p>
                        </div>
                    <?php else: ?>
                        <!-- THÔNG TIN HỢP ĐỒNG TÓM TẮT -->
                        <div class="contract-highlight-box mb-4">
                            <div class="row g-4 align-items-center">
                                <div class="col-md-7">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-teal-main px-3 py-1 rounded-pill fw-bold">
                                            <?= ($contract['contract_type'] ?? '') === 'deposit_hold' ? 'HĐ 1: Đặt cọc giữ chỗ 5 ngày' : 'HĐ 2: Hợp đồng thuê chính thức' ?>
                                        </span>
                                        <span class="badge bg-success px-3 py-1 rounded-pill fw-bold">
                                            <i class="fa-solid fa-shield-check me-1"></i> Đang có hiệu lực
                                        </span>
                                    </div>
                                    <h4 class="fw-black text-dark mb-1">Mã hợp đồng: <span class="text-teal-main"><?= h($contract['contract_code']) ?></span></h4>
                                    <p class="text-muted small mb-3">Ký kết giữa Ban Quản Lý StayMate và khách thuê: <strong><?= h($tenantName) ?></strong></p>

                                    <div class="row g-3">
                                        <div class="col-sm-6">
                                            <div class="text-muted small">Căn phòng thuê:</div>
                                            <div class="fw-bold text-dark fs-6"><i class="fa-solid fa-door-open text-teal-main me-1"></i> Phòng <?= h($contract['room_number'] ?? $roomNumber) ?></div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="text-muted small">Tòa nhà &amp; Địa chỉ:</div>
                                            <div class="fw-bold text-dark fs-6"><i class="fa-solid fa-location-dot text-danger me-1"></i> <?= h($buildingName) ?></div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="text-muted small">Thời hạn thuê:</div>
                                            <div class="fw-bold text-dark">
                                                <?= date('d/m/Y', strtotime($contract['start_date'])) ?> &rarr; <?= date('d/m/Y', strtotime($contract['end_date'])) ?>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="text-muted small">Số ngày còn lại:</div>
                                            <div class="fw-bold text-warning">
                                                <i class="fa-solid fa-hourglass-half me-1"></i> Còn <?= (int)($contract['days_left'] ?? 0) ?> ngày
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-5">
                                    <div class="bg-white p-4 rounded-4 shadow-sm border text-center">
                                        <div class="text-muted small mb-1 fw-bold">GIÁ THUÊ HÀNG THÁNG</div>
                                        <div class="fs-3 fw-black text-teal-main mb-3" style="font-weight: 900;">
                                            <?= money($contract['monthly_rent']) ?>
                                        </div>
                                        <hr class="my-2">
                                        <div class="d-flex justify-content-between text-muted small py-1">
                                            <span>Tiền đặt cọc:</span>
                                            <strong class="text-dark"><?= money($contract['deposit_amount']) ?></strong>
                                        </div>
                                        <div class="d-flex justify-content-between text-muted small py-1">
                                            <span>Tỷ lệ cọc hoàn tất:</span>
                                            <strong class="text-success"><?= (int)($contract['deposit_paid_percent'] ?? 100) ?>% (Đã nộp đủ)</strong>
                                        </div>
                                        <div class="d-flex justify-content-between text-muted small py-1">
                                            <span>Hình thức ký kết:</span>
                                            <strong class="text-primary"><?= ($contract['signature_type'] ?? '') === 'online' ? 'Ký điện tử Online' : 'Ký văn bản giấy' ?></strong>
                                        </div>

                                        <div class="mt-3">
                                            <a href="index.php?route=tenant/contract-pdf<?= !empty($contract['id']) ? '&id=' . (int)$contract['id'] : '' ?><?= $isManagerPreview ? '&tenant_id=' . (int)($profile['id'] ?? 0) : '' ?>"
                                               target="_blank"
                                               onclick="window.open(this.href, 'ContractPDFWindow', 'width=880,height=960,scrollbars=yes,resizable=yes'); return false;"
                                               class="btn btn-teal w-100 justify-content-center py-2">
                                                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Mở Bản PDF Trong Tab Nhỏ
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CÁC ĐIỀU KHOẢN CHÍNH MINH BẠCH -->
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 bg-light bg-opacity-50 h-100">
                                    <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-clipboard-check text-success me-2"></i>Quyền lợi của khách thuê:</h6>
                                    <ul class="text-muted small ps-3 mb-0" style="line-height: 1.8;">
                                        <li>Sử dụng toàn quyền phòng trọ và trang thiết bị đi kèm theo thỏa thuận.</li>
                                        <li>Được bảo vệ quyền riêng tư, an ninh trật tự và vệ sinh môi trường chung.</li>
                                        <li>Được nhận lại 100% tiền cọc khi hết hạn hợp đồng và bàn giao phòng nguyên trạng.</li>
                                        <li>Được cấp biên nhận / hóa đơn minh bạch cho mọi khoản thanh toán.</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 bg-light bg-opacity-50 h-100">
                                    <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>Nghĩa vụ và quy định tòa nhà:</h6>
                                    <ul class="text-muted small ps-3 mb-0" style="line-height: 1.8;">
                                        <li>Thanh toán tiền phòng và dịch vụ điện nước đúng hạn trước ngày 10 hàng tháng.</li>
                                        <li>Giữ gìn vệ sinh chung, không gây ồn ào sau 23h đêm.</li>
                                        <li>Tuân thủ phòng cháy chữa cháy, không tàng trữ chất cháy nổ.</li>
                                        <li>Khi có nhu cầu chấm dứt hoặc thay đổi thông tin cần báo trước ít nhất 30 ngày.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 3: BẢNG ĐƠN GIÁ DỊCH VỤ CÔNG KHAI -->
            <!-- ========================================== -->
            <div class="tab-pane fade" id="services-content" role="tabpanel">
                <div class="portal-card">
                    <div class="card-header-custom">
                        <div>
                            <h3 class="card-header-title">
                                <i class="fa-solid fa-tags text-teal-main"></i> Bảng Đơn Giá Dịch Vụ Công Khai &amp; Minh Bạch
                            </h3>
                            <p class="text-muted small mb-0 mt-1">Toàn bộ đơn giá dịch vụ điện, nước, internet, rác và tiện ích tại tòa nhà <?= h($buildingName) ?> được niêm yết cố định.</p>
                        </div>
                        <span class="badge bg-teal-soft text-teal-main border border-teal-main px-3 py-2 rounded-pill fw-bold">
                            <i class="fa-solid fa-shield-halved me-1"></i> Áp dụng minh bạch toàn hệ thống
                        </span>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-custom">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">STT</th>
                                    <th>Tên dịch vụ</th>
                                    <th>Đơn giá niêm yết</th>
                                    <th>Đơn vị tính</th>
                                    <th>Cách tính &amp; Ghi chú</th>
                                    <th class="text-center">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($services)): ?>
                                    <?php $stt = 1; foreach ($services as $srv): ?>
                                        <tr>
                                            <td class="fw-bold text-muted"><?= $stt++ ?></td>
                                            <td>
                                                <div class="fw-bold text-dark">
                                                    <?php
                                                    $sName = mb_strtolower($srv['service_name'], 'UTF-8');
                                                    if (strpos($sName, 'điện') !== false) echo '<i class="fa-solid fa-bolt text-warning me-2"></i>';
                                                    elseif (strpos($sName, 'nước') !== false) echo '<i class="fa-solid fa-faucet-drip text-info me-2"></i>';
                                                    elseif (strpos($sName, 'internet') !== false || strpos($sName, 'wifi') !== false) echo '<i class="fa-solid fa-wifi text-primary me-2"></i>';
                                                    elseif (strpos($sName, 'rác') !== false) echo '<i class="fa-solid fa-trash-can text-secondary me-2"></i>';
                                                    elseif (strpos($sName, 'xe') !== false) echo '<i class="fa-solid fa-motorcycle text-danger me-2"></i>';
                                                    elseif (strpos($sName, 'vệ sinh') !== false) echo '<i class="fa-solid fa-broom text-success me-2"></i>';
                                                    elseif (strpos($sName, 'thang máy') !== false) echo '<i class="fa-solid fa-elevator text-primary me-2"></i>';
                                                    else echo '<i class="fa-solid fa-circle-check text-teal-main me-2"></i>';
                                                    ?>
                                                    <?= h($srv['service_name']) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-extrabold text-teal-main fs-6" style="font-weight: 800;">
                                                    <?= money($srv['unit_price']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border fw-bold px-2 py-1">
                                                    / <?= h($srv['unit']) ?>
                                                </span>
                                            </td>
                                            <td class="text-muted small">
                                                <?php
                                                if (strpos($sName, 'điện') !== false) {
                                                    echo 'Theo chỉ số công tơ điện riêng từng phòng chốt ngày cuối tháng.';
                                                } elseif (strpos($sName, 'nước') !== false) {
                                                    echo 'Theo đồng hồ nước hoặc định mức tính theo số lượng người ở thực tế.';
                                                } elseif (strpos($sName, 'internet') !== false || strpos($sName, 'wifi') !== false) {
                                                    echo 'Cáp quang tốc độ cao 300Mbps không giới hạn băng thông.';
                                                } elseif (strpos($sName, 'xe') !== false) {
                                                    echo 'Trông giữ xe 24/7 có camera an ninh và thẻ từ ra vào.';
                                                } else {
                                                    echo 'Thu cố định hàng tháng theo kỳ hóa đơn.';
                                                }
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge-paid"><i class="fa-solid fa-check"></i> Đang áp dụng</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Chưa có dữ liệu bảng giá dịch vụ.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- HƯỚNG DẪN TÍNH CHỈ SỐ -->
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-4 border">
                                <h6 class="fw-bold text-dark"><i class="fa-solid fa-calendar-check text-teal-main me-2"></i>Ngày chốt chỉ số:</h6>
                                <p class="text-muted small mb-0">Ban Quản Lý sẽ tiến hành ghi nhận chỉ số công tơ điện nước từ ngày 28 đến ngày 30 hàng tháng và cập nhật lên hệ thống.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-4 border">
                                <h6 class="fw-bold text-dark"><i class="fa-solid fa-camera text-primary me-2"></i>Chụp ảnh đối soát:</h6>
                                <p class="text-muted small mb-0">Khách thuê có quyền tự chụp lại mặt công tơ tại thời điểm cuối tháng để đối soát với hóa đơn nếu phát hiện sai lệch.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-4 border">
                                <h6 class="fw-bold text-dark"><i class="fa-solid fa-headset text-danger me-2"></i>Đường dây hỗ trợ:</h6>
                                <p class="text-muted small mb-0">Mọi thắc mắc về chỉ số và tiền dịch vụ, vui lòng gọi hotline BQL: <strong><?= h($profile['manager_phone'] ?? '0988 888 999') ?></strong>.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TAB 4: THÔNG TIN CÁ NHÂN & GỬI YÊU CẦU DUYỆT -->
            <!-- ========================================== -->
            <div class="tab-pane fade" id="profile-content" role="tabpanel">
                <div class="portal-card">
                    <div class="card-header-custom">
                        <div>
                            <h3 class="card-header-title">
                                <i class="fa-solid fa-id-card text-teal-main"></i> Thông Tin Cá Nhân Khách Thuê
                            </h3>
                            <p class="text-muted small mb-0 mt-1">Thông tin cá nhân được ràng buộc pháp lý theo hợp đồng thuê phòng. Khách thuê không tự ý sửa trực tiếp mà cần gửi yêu cầu để Ban Quản Lý xét duyệt.</p>
                        </div>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border px-3 py-2 rounded-pill fw-bold">
                            <i class="fa-solid fa-lock me-1"></i> Chế độ chỉ đọc (Readonly)
                        </span>
                    </div>

                    <!-- THÔNG BÁO QUY ĐỊNH KHÔNG ĐƯỢC TỰ SỬA -->
                    <div class="notice-banner">
                        <i class="fa-solid fa-shield-halved fs-4 text-warning"></i>
                        <div>
                            <div class="fw-bold mb-1">Quy định bảo mật &amp; Pháp lý hợp đồng thuê:</div>
                            Để bảo vệ tính toàn vẹn của hợp đồng và an ninh của toàn bộ cư dân trong tòa nhà, bạn không thể tự sửa trực tiếp các thông tin như Họ tên, Số điện thoại, Email hoặc Số người ở. Nếu thông tin của bạn có sự thay đổi, xin vui lòng gửi yêu cầu bằng form bên dưới để Ban Quản Lý phê duyệt và cập nhật.
                        </div>
                    </div>

                    <!-- FORM XEM THÔNG TIN CÁ NHÂN (READONLY) -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark small">Họ và tên khách thuê <i class="fa-solid fa-lock readonly-lock"></i></label>
                            <input type="text" class="form-control form-control-readonly" value="<?= h($tenantName) ?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark small">Tên đăng nhập hệ thống <i class="fa-solid fa-lock readonly-lock"></i></label>
                            <input type="text" class="form-control form-control-readonly" value="<?= h($profile['username'] ?? '') ?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark small">Số điện thoại liên hệ <i class="fa-solid fa-lock readonly-lock"></i></label>
                            <input type="text" class="form-control form-control-readonly" value="<?= h($profile['phone'] ?? '') ?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark small">Địa chỉ Email <i class="fa-solid fa-lock readonly-lock"></i></label>
                            <input type="email" class="form-control form-control-readonly" value="<?= h($profile['email'] ?? '') ?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark small">Phòng đang thuê <i class="fa-solid fa-lock readonly-lock"></i></label>
                            <input type="text" class="form-control form-control-readonly" value="Phòng <?= h($roomNumber) ?> (<?= money($profile['room_price'] ?? 0) ?>/tháng)" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-dark small">Tòa nhà cư trú <i class="fa-solid fa-lock readonly-lock"></i></label>
                            <input type="text" class="form-control form-control-readonly" value="<?= h($buildingName) ?>" readonly>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- FORM GỬI YÊU CẦU ĐIỀU CHỈNH LÊN QUẢN LÝ -->
                    <div class="row g-4">
                        <div class="col-lg-5">
                            <div class="p-4 rounded-4 border bg-light h-100">
                                <h5 class="fw-bold text-dark mb-3">
                                    <i class="fa-solid fa-paper-plane text-teal-main me-2"></i>Gửi Yêu Cầu Cập Nhật Thông Tin
                                </h5>
                                <p class="text-muted small mb-3">
                                    Chọn loại thông tin bạn muốn thay đổi và nhập dữ liệu mới. Quản lý tòa nhà sẽ tiếp nhận và xử lý sau khi xác thực.
                                </p>

                                <form method="POST" action="index.php?route=tenant/request">
                                    <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                                    <?php if ($isManagerPreview): ?>
                                        <input type="hidden" name="tenant_id" value="<?= (int)($profile['id'] ?? 0) ?>">
                                    <?php endif; ?>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-dark small">Thông tin cần điều chỉnh: <span class="text-danger">*</span></label>
                                        <select name="field_target" class="form-select rounded-3 fw-bold" required id="selectFieldTarget" onchange="updateOldValuePlaceholder()">
                                            <option value="phone" data-old="<?= h($profile['phone'] ?? '') ?>">Số điện thoại (Hiện tại: <?= h($profile['phone'] ?? '') ?>)</option>
                                            <option value="email" data-old="<?= h($profile['email'] ?? '') ?>">Địa chỉ Email (Hiện tại: <?= h($profile['email'] ?? '') ?>)</option>
                                            <option value="full_name" data-old="<?= h($profile['full_name'] ?? '') ?>">Họ và tên (Hiện tại: <?= h($profile['full_name'] ?? '') ?>)</option>
                                            <option value="id_card" data-old="Căn cước công dân">Số CCCD / Ngày cấp</option>
                                            <option value="co_tenant" data-old="Người ở cùng">Đăng ký thêm người ở cùng / Đổi xe</option>
                                            <option value="other" data-old="Yêu cầu khác">Yêu cầu khác</option>
                                        </select>
                                    </div>

                                    <input type="hidden" name="old_value" id="hiddenOldValue" value="<?= h($profile['phone'] ?? '') ?>">

                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-dark small">Nội dung thông tin mới: <span class="text-danger">*</span></label>
                                        <input type="text" name="new_value" class="form-control rounded-3" placeholder="Nhập số điện thoại mới, email mới hoặc nội dung cần sửa..." required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-dark small">Lý do yêu cầu thay đổi:</label>
                                        <textarea name="reason" rows="2" class="form-control rounded-3" placeholder="VD: Mất sim điện thoại cũ, đổi nơi làm việc, cấp đổi CCCD mới..."></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-teal w-100 py-2 justify-content-center">
                                        <i class="fa-solid fa-paper-plane me-1"></i> Gửi Yêu Cầu Cho Ban Quản Lý
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- DANH SÁCH LỊCH SỬ YÊU CẦU ĐÃ GỬI -->
                        <div class="col-lg-7">
                            <h5 class="fw-bold text-dark mb-3">
                                <i class="fa-solid fa-clock-rotate-left text-teal-main me-2"></i>Lịch Sử Yêu Cầu Đã Gửi (<?= count($requests) ?>)
                            </h5>

                            <?php if (empty($requests)): ?>
                                <div class="text-center py-4 bg-light rounded-4 border">
                                    <i class="fa-solid fa-inbox fs-2 text-muted opacity-50 mb-2"></i>
                                    <div class="fw-bold text-dark small">Bạn chưa gửi yêu cầu điều chỉnh thông tin nào</div>
                                    <div class="text-muted" style="font-size:0.8rem;">Khi bạn gửi yêu cầu, tiến độ xử lý và phản hồi của Quản lý sẽ hiển thị tại đây.</div>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-custom">
                                        <thead>
                                            <tr>
                                                <th>Ngày gửi</th>
                                                <th>Mục cần sửa</th>
                                                <th>Thông tin mới</th>
                                                <th>Trạng thái</th>
                                                <th>Phản hồi của QL</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($requests as $req): ?>
                                                <tr>
                                                    <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($req['created_at'])) ?></td>
                                                    <td class="fw-bold text-dark">
                                                        <?php
                                                        $mapField = [
                                                            'phone'     => 'Số điện thoại',
                                                            'email'     => 'Địa chỉ Email',
                                                            'full_name' => 'Họ và tên',
                                                            'id_card'   => 'CCCD / Định danh',
                                                            'co_tenant' => 'Người ở cùng',
                                                            'other'     => 'Yêu cầu khác'
                                                        ];
                                                        echo h($mapField[$req['field_target']] ?? $req['field_target']);
                                                        ?>
                                                    </td>
                                                    <td class="small fw-bold text-teal-main"><?= h($req['new_value']) ?></td>
                                                    <td>
                                                        <?php if ($req['status'] === 'approved'): ?>
                                                            <span class="badge-paid"><i class="fa-solid fa-check"></i> Đã duyệt</span>
                                                        <?php elseif ($req['status'] === 'rejected'): ?>
                                                            <span class="badge-unpaid"><i class="fa-solid fa-xmark"></i> Từ chối</span>
                                                        <?php else: ?>
                                                            <span class="badge-pending"><i class="fa-solid fa-hourglass-start"></i> Đang chờ duyệt</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="small text-muted">
                                                        <?= !empty($req['manager_note']) ? h($req['manager_note']) : '<em>Đang chờ xử lý...</em>' ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </main>

    <!-- FOOTER -->
    <footer class="text-center py-4 text-muted small border-top bg-white mt-auto">
        <div class="container">
            <span class="fw-bold text-teal-main">StayMate</span> - Nền tảng quản lý nhà trọ &amp; căn hộ cho thuê hiện đại | Phiên bản Khách thuê 2.0
        </div>
    </footer>

    <!-- MODAL CHI TIẾT HÓA ĐƠN & THANH TOÁN QR -->
    <div class="modal fade" id="invoiceDetailModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom bg-teal-main text-white rounded-top-4 p-3 px-4">
                    <h5 class="modal-title fw-bold" id="modalInvTitle">
                        <i class="fa-solid fa-file-invoice-dollar me-2"></i> Chi Tiết Hóa Đơn
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="modalInvLoading" class="text-center py-4">
                        <div class="spinner-border text-teal-main" role="status"></div>
                        <div class="mt-2 text-muted fw-bold">Đang tải chi tiết hóa đơn...</div>
                    </div>
                    <div id="modalInvBody" style="display: none;">
                        <!-- Nội dung render chi tiết -->
                    </div>
                </div>
                <div class="modal-footer border-top p-3 px-4 bg-light rounded-bottom-4 justify-content-between">
                    <div class="text-muted small">
                        <i class="fa-solid fa-circle-info text-teal-main me-1"></i> Chuyển khoản với cú pháp được tạo sẵn trên QR để hệ thống tự động ghi nhận.
                    </div>
                    <button type="button" class="btn btn-secondary rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateOldValuePlaceholder() {
            const select = document.getElementById('selectFieldTarget');
            const opt = select.options[select.selectedIndex];
            document.getElementById('hiddenOldValue').value = opt.getAttribute('data-old') || '';
        }

        // Xem chi tiết hóa đơn qua AJAX
        function viewInvoiceDetail(invoiceId, code, period, totalFmt, status) {
            const modal = new bootstrap.Modal(document.getElementById('invoiceDetailModal'));
            document.getElementById('modalInvTitle').innerHTML = '<i class="fa-solid fa-file-invoice-dollar me-2"></i> Hóa đơn ' + code + ' (Kỳ ' + period + ')';
            document.getElementById('modalInvLoading').style.display = 'block';
            document.getElementById('modalInvBody').style.display = 'none';
            modal.show();

            const tenantParam = '<?= $isManagerPreview ? '&tenant_id=' . (int)($profile['id'] ?? 0) : '' ?>';
            fetch('index.php?route=tenant/invoice-detail&id=' + invoiceId + tenantParam)
                .then(r => r.json())
                .then(res => {
                    document.getElementById('modalInvLoading').style.display = 'none';
                    const container = document.getElementById('modalInvBody');
                    container.style.display = 'block';

                    if (!res.success || !res.invoice) {
                        container.innerHTML = '<div class="alert alert-danger">Không thể tải thông tin chi tiết hóa đơn!</div>';
                        return;
                    }

                    const inv = res.invoice;
                    const totalNum = parseFloat(inv.total_amount) || 0;
                    const qrContent = encodeURIComponent('HD' + inv.id + ' ' + (inv.room_number || '') + ' TIEN PHONG');
                    const qrUrl = 'https://img.vietqr.io/image/MB-0901000001-compact2.png?amount=' + totalNum + '&addInfo=' + qrContent + '&accountName=STAYMATE%20QUAN%20LY';

                    let detailsHtml = '';
                    if (inv.details && inv.details.length > 0) {
                        inv.details.forEach(d => {
                            const unitPrice = parseFloat(d.applied_price).toLocaleString('vi-VN') + ' đ';
                            const subtotal = parseFloat(d.subtotal).toLocaleString('vi-VN') + ' đ';
                            const sName = d.service_name || 'Tiền phòng';
                            const sUnit = d.unit ? ('/' + d.unit) : '';
                            detailsHtml += `
                                <tr>
                                    <td class="fw-bold">${sName}</td>
                                    <td class="text-center">${d.quantity} ${sUnit}</td>
                                    <td class="text-end">${unitPrice}</td>
                                    <td class="text-end fw-bold">${subtotal}</td>
                                </tr>
                            `;
                        });
                    } else {
                        // Fallback điện nước
                        const eUsed = Math.max(0, (parseInt(inv.electric_new) || 0) - (parseInt(inv.electric_old) || 0));
                        const wUsed = Math.max(0, (parseInt(inv.water_new) || 0) - (parseInt(inv.water_old) || 0));
                        detailsHtml += `
                            <tr>
                                <td class="fw-bold">Tiền thuê phòng ${inv.room_number || ''}</td>
                                <td class="text-center">1 tháng</td>
                                <td class="text-end">${parseFloat(inv.room_price || 0).toLocaleString('vi-VN')} đ</td>
                                <td class="text-end fw-bold">${parseFloat(inv.room_price || 0).toLocaleString('vi-VN')} đ</td>
                            </tr>
                            <tr>
                                <td class="fw-bold"><i class="fa-solid fa-bolt text-warning me-1"></i> Tiền điện (${inv.electric_old || 0} &rarr; ${inv.electric_new || 0})</td>
                                <td class="text-center">${eUsed} kWh</td>
                                <td class="text-end">3.500 đ</td>
                                <td class="text-end fw-bold">${(eUsed * 3500).toLocaleString('vi-VN')} đ</td>
                            </tr>
                            <tr>
                                <td class="fw-bold"><i class="fa-solid fa-faucet-drip text-info me-1"></i> Tiền nước (${inv.water_old || 0} &rarr; ${inv.water_new || 0})</td>
                                <td class="text-center">${wUsed} khối</td>
                                <td class="text-end">20.000 đ</td>
                                <td class="text-end fw-bold">${(wUsed * 20000).toLocaleString('vi-VN')} đ</td>
                            </tr>
                        `;
                    }

                    container.innerHTML = `
                        <div class="row g-4">
                            <div class="col-md-7">
                                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                                    <i class="fa-solid fa-list-check text-teal-main me-1"></i> Bảng Kê Chi Tiết Các Khoản
                                </h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Khoản thu</th>
                                                <th class="text-center">Số lượng</th>
                                                <th class="text-end">Đơn giá</th>
                                                <th class="text-end">Thành tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${detailsHtml}
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-warning">
                                                <th colspan="3" class="text-end fs-6 fw-extrabold">TỔNG CỘNG:</th>
                                                <th class="text-end fs-5 fw-black text-danger">${totalFmt}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="p-2 bg-light rounded-3 small text-muted">
                                    <strong>Trạng thái:</strong> ${status === 'paid' ? '<span class="text-success fw-bold">Đã thanh toán</span>' : '<span class="text-danger fw-bold">Chưa thanh toán</span>'}<br>
                                    <strong>Hợp đồng:</strong> ${inv.contract_code || 'Hợp đồng phòng ' + inv.room_number}
                                </div>
                            </div>
                            <div class="col-md-5 text-center">
                                <div class="p-3 bg-light rounded-4 border">
                                    <h6 class="fw-bold text-dark mb-1">
                                        <i class="fa-solid fa-qrcode text-teal-main me-1"></i> Quét Mã QR Thanh Toán
                                    </h6>
                                    <p class="text-muted small mb-2">Hỗ trợ tất cả ứng dụng Ngân hàng &amp; Ví Momo</p>
                                    <div class="bg-white p-2 rounded-3 border d-inline-block shadow-sm mb-2">
                                        <img src="${qrUrl}" alt="VietQR" style="max-width: 190px; width: 100%; border-radius: 8px;" onerror="this.src='https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=${qrContent}'">
                                    </div>
                                    <div class="small fw-bold text-dark">STK: <span class="text-teal-main">0901000001</span> - MB Bank</div>
                                    <div class="small text-muted">Chủ TK: STAYMATE QUAN LY</div>
                                    <div class="small text-muted">Nội dung: <code>HD${inv.id} ${inv.room_number || ''} TIEN PHONG</code></div>
                                </div>
                            </div>
                        </div>
                    `;
                })
                .catch(err => {
                    document.getElementById('modalInvLoading').style.display = 'none';
                    document.getElementById('modalInvBody').style.display = 'block';
                    document.getElementById('modalInvBody').innerHTML = '<div class="alert alert-danger">Lỗi kết nối máy chủ khi lấy chi tiết hóa đơn!</div>';
                });
        }
    </script>
</body>
</html>
