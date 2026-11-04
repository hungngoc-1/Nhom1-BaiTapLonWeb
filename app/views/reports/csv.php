<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

$userRole     = $_SESSION['role'] ?? 'admin';
$userFullName = $_SESSION['full_name'] ?? 'Quản Trị Viên';
$userName     = $_SESSION['username'] ?? 'admin';
$userEmail    = $_SESSION['email'] ?? 'admin@staymate.vn';
$userPhone    = $_SESSION['phone'] ?? '0901234567';

$brandHome = 'index.php?route=dashboard';
$pageTitle = 'Báo Cáo Doanh Thu, Công Nợ & Tổng Quan Hệ Thống';

// Tính các chỉ số cho Tab 2 (Phòng ốc & Cọc)
$tab2TotalRooms = count($roomRows);
$tab2Rented = 0;
$tab2Available = 0;
$tab2Maintenance = 0;
$tab2TotalListedPrice = 0;
$tab2TotalDepositHeld = 0;
foreach ($roomRows as $rm) {
    $tab2TotalListedPrice += (float)$rm['price'];
    $tab2TotalDepositHeld += (float)($rm['deposit_amount'] ?? 0);
    if ($rm['status'] === 'rented') $tab2Rented++;
    elseif ($rm['status'] === 'available') $tab2Available++;
    elseif ($rm['status'] === 'maintenance') $tab2Maintenance++;
}

// Tính các chỉ số cho Tab 3 (Khách thuê & Hợp đồng)
$tab3TotalTenants = count($tenantRows);
$tab3ExpiringSoon = 0;
$tab3TotalRent = 0;
$tab3TotalDeposit = 0;
foreach ($tenantRows as $tn) {
    $tab3TotalRent += (float)$tn['monthly_rent'];
    $tab3TotalDeposit += (float)$tn['deposit_amount'];
    if ((int)$tn['days_left'] <= 30) {
        $tab3ExpiringSoon++;
    }
}
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
            height: 100%;
        }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08); }
        .stat-title { font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-num { font-size: 2rem; font-weight: 900; line-height: 1.1; }

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
            display: inline-flex; align-items: center; gap: 8px; text-decoration: none;
        }
        .btn-teal-custom:hover { background: var(--teal-hover); color: white; transform: translateY(-2px); }

        .btn-export-csv {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 50px; font-weight: 800;
            color: white; padding: 10px 24px; border: none;
            box-shadow: 0 6px 18px rgba(16, 185, 129, 0.35); transition: 0.2s;
            display: inline-flex; align-items: center; gap: 8px; text-decoration: none;
        }
        .btn-export-csv:hover { background: linear-gradient(135deg, #059669 0%, #047857 100%); color: white; transform: translateY(-2px); }

        /* Filter Tab Buttons */
        .filter-tab {
            padding: 10px 22px; border-radius: 50px; font-weight: 800; font-size: 0.88rem;
            text-decoration: none; border: 2px solid #e2e8f0; color: #475569; background: #ffffff; transition: 0.2s;
            display: inline-flex; align-items: center; gap: 8px; cursor: pointer;
        }
        .filter-tab:hover { border-color: var(--teal-main); color: var(--teal-main); background: #f0fdfa; }
        .filter-tab.active { background: var(--teal-main) !important; border-color: var(--teal-main) !important; color: #ffffff !important; box-shadow: 0 6px 15px rgba(48, 143, 134, 0.35); }

        .table-custom thead th {
            background-color: #f0fdfa; color: var(--teal-main); font-size: 0.8rem; font-weight: 900;
            text-transform: uppercase; padding: 14px 16px; border-bottom: 2px solid #ccfbf1;
            vertical-align: middle;
        }
        .table-custom tbody td { padding: 14px 16px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 600; font-size: 0.88rem; }

        /* Matrix Table */
        .matrix-table thead th {
            background-color: #0f172a; color: #f8fafc; font-size: 0.78rem; font-weight: 900;
            text-transform: uppercase; padding: 14px 12px; border: none; vertical-align: middle;
        }
        .matrix-table tbody td { padding: 13px 12px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-size: 0.88rem; }
        .matrix-table tr.total-row td {
            background-color: #f8fafc; font-weight: 900;
            border-top: 2px solid #cbd5e1; border-bottom: 2px solid #cbd5e1;
        }

        .btn-hamburger { width: 42px; height: 42px; border-radius: 12px; background: #f0fdfa; border: 2px solid #99f6e4; color: var(--teal-main); display: inline-flex; align-items: center; justify-content: center; font-size: 1.2rem; }

        .invoice-link {
            color: var(--teal-main);
            font-weight: 800;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: color 0.15s;
        }
        .invoice-link:hover {
            color: var(--teal-hover);
            text-decoration: underline;
        }

        /* Mini KPI Badges in Tab Headers */
        .mini-kpi-pill {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 50px;
            padding: 6px 16px;
            font-size: 0.82rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        @media print {
            .no-print, .navbar, .btn, .filter-box, .filter-tab-container { display: none !important; }
            .section-box, .welcome-banner { box-shadow: none !important; border: none !important; padding: 0 !important; }
            .tab-pane { display: block !important; opacity: 1 !important; visibility: visible !important; margin-bottom: 30px; }
            body { background: #fff !important; background-image: none !important; }
        }
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
                        <div class="avatar-circle">AD</div>
                        <div class="text-start d-none d-sm-block lh-sm">
                            <div class="fw-bold text-dark" style="font-size: 0.9rem;"><?= htmlspecialchars($userFullName) ?> <i class="fa-solid fa-angle-down ms-1 text-muted fs-6"></i></div>
                            <div class="text-muted text-capitalize" style="font-size: 0.75rem;">Quản trị viên (Admin)</div>
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

            <!-- Menu chính đồng bộ (Chỉ hiện "Báo cáo") -->
            <div class="collapse navbar-collapse order-lg-2" id="staymateNav">
                <ul class="nav nav-pills flex-nowrap me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php?route=overview"><i class="fa-solid fa-border-all"></i> Tổng quan</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?route=rooms"><i class="fa-solid fa-bed"></i> Phòng trọ</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?route=tenants"><i class="fa-solid fa-users"></i> Khách thuê</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?route=contracts"><i class="fa-solid fa-file-signature"></i> Hợp đồng</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?route=utilities"><i class="fa-solid fa-bolt"></i> Điện Nước</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?route=invoices"><i class="fa-solid fa-file-invoice-dollar"></i> Hóa đơn</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?route=accounts"><i class="fa-solid fa-user-gear"></i> Tài khoản</a></li>
                    <li class="nav-item"><a class="nav-link active" href="index.php?route=reports_csv"><i class="fa-solid fa-chart-pie"></i> Báo cáo</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?route=showcase" target="_blank"><i class="fa-regular fa-eye"></i> Xem phòng khách</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?route=tenant/portal" target="_blank"><i class="fa-solid fa-user-shield text-teal-main"></i> Cổng khách thuê</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ================= MAIN CONTENT ================= -->
    <div class="container-fluid px-3 px-xl-5 py-4">

        <!-- Welcome Banner -->
        <div class="welcome-banner mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="welcome-icon-box">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
                <div>
                    <h3 class="fw-bold mb-1" style="font-size: 1.45rem; color: #0f172a;"><?= htmlspecialchars($pageTitle) ?></h3>
                    <div class="text-muted" style="font-size: 0.85rem; font-weight: 600;">
                        Quản trị tối cao: Đối soát toàn diện doanh thu, công nợ, hạn thanh toán, hiện trạng 60 phòng và hợp đồng khách thuê trên cả 3 tòa nhà.
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 no-print">
                <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill fw-bold d-none d-md-inline-block">
                    <i class="fa-regular fa-calendar me-1 text-teal-main"></i> <?= date('d/m/Y') ?>
                </span>
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-bold" onclick="window.print()">
                    <i class="fa-solid fa-print me-1"></i> In Báo Cáo
                </button>
                <?php 
                    $exportUrl = "index.php?route=reports_csv&export=csv"
                        . "&building_id=" . urlencode($buildingId)
                        . "&month_year=" . urlencode($monthYear)
                        . "&status=" . urlencode($status)
                        . "&keyword=" . urlencode($keyword);
                ?>
                <a href="<?= $exportUrl ?>" class="btn-export-csv">
                    <i class="fa-solid fa-file-arrow-down"></i> Xuất File CSV (Excel)
                </a>
            </div>
        </div>

        <!-- 4 Stats Cards -->
        <div class="row g-3 mb-4">
            <!-- 1. Thực Thu (Doanh Thu) -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card" style="border-left: 5px solid #10b981;">
                    <div class="stat-title" style="color: #10b981;"><i class="fa-solid fa-circle-check me-1"></i> THỰC THU (DOANH THU)</div>
                    <div class="stat-num text-dark mt-2"><?= number_format($systemSummary['paid_amount'], 0, ',', '.') ?> <span class="fs-6 text-muted">đ</span></div>
                    <div class="small text-muted mt-1 fw-bold"><?= $systemSummary['paid_count'] ?> / <?= $systemSummary['total_invoices'] ?> hóa đơn đã thanh toán</div>
                </div>
            </div>

            <!-- 2. Công Nợ (Chưa Thu) -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card" style="border-left: 5px solid #ef4444;">
                    <div class="stat-title text-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> CÔNG NỢ (CHƯA THU)</div>
                    <div class="stat-num text-dark mt-2"><?= number_format($systemSummary['unpaid_amount'], 0, ',', '.') ?> <span class="fs-6 text-muted">đ</span></div>
                    <div class="small text-muted mt-1 fw-bold"><?= $systemSummary['unpaid_count'] ?> hóa đơn còn nợ (Tỷ lệ thu: <?= $systemSummary['collection_rate'] ?>%)</div>
                </div>
            </div>

            <!-- 3. Tỷ Lệ Lấp Đầy -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card" style="border-left: 5px solid #308f86;">
                    <div class="stat-title text-teal-main"><i class="fa-solid fa-door-open me-1"></i> TỶ LỆ LẤP ĐẦY</div>
                    <div class="stat-num text-dark mt-2"><?= $systemSummary['occupancy_rate'] ?> <span class="fs-6 text-muted">%</span></div>
                    <div class="small text-muted mt-1 fw-bold"><?= $systemSummary['rented_rooms'] ?> / <?= $systemSummary['total_rooms'] ?> phòng đang cho thuê</div>
                </div>
            </div>

            <!-- 4. Tiền Cọc Đang Giữ -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card" style="border-left: 5px solid #0284c7;">
                    <div class="stat-title" style="color: #0284c7;"><i class="fa-solid fa-shield-halved me-1"></i> TIỀN CỌC ĐANG GIỮ</div>
                    <div class="stat-num text-dark mt-2"><?= number_format($systemSummary['deposit_held'], 0, ',', '.') ?> <span class="fs-6 text-muted">đ</span></div>
                    <div class="small text-muted mt-1 fw-bold"><?= $systemSummary['tenant_count'] ?> khách thuê có hợp đồng hiệu lực</div>
                </div>
            </div>
        </div>

        <!-- BẢNG MA TRẬN SO SÁNH 3 TÒA NHÀ & HỆ THỐNG -->
        <div class="section-box mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold text-dark mb-0" style="font-weight: 800;">
                        <i class="fa-solid fa-table-cells-large text-teal-main me-2"></i> Ma Trận So Sánh Toàn Diện 3 Tòa Nhà
                    </h5>
                    <div class="text-muted small">Đối chiếu tổng thể cơ cấu phòng, khách thuê, dòng tiền thực thu và công nợ giữa từng tòa nhà</div>
                </div>
                <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill fw-bold">
                    <i class="fa-solid fa-building text-teal-main me-1"></i> Cả 3 Tòa (Sunrise, Emerald, Horizon)
                </span>
            </div>

            <div class="table-responsive">
                <table class="table matrix-table align-middle text-center mb-0 border rounded-4 overflow-hidden">
                    <thead>
                        <tr>
                            <th class="text-start" style="min-width: 220px;">Tòa Nhà</th>
                            <th>Tổng Phòng</th>
                            <th>Đang Thuê</th>
                            <th>Phòng Trống</th>
                            <th>Bảo Trì</th>
                            <th>Lấp Đầy</th>
                            <th>Khách Ở</th>
                            <th class="text-end">Tiền Cọc Giữ</th>
                            <th class="text-end">Tổng Hóa Đơn</th>
                            <th class="text-end">Thực Thu (VNĐ)</th>
                            <th class="text-end">Công Nợ (VNĐ)</th>
                            <th class="text-center">Tỷ Lệ Thu Hồi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($matrix as $m): ?>
                            <tr>
                                <td class="text-start fw-bold text-dark">
                                    <i class="fa-solid fa-building text-teal-main me-2"></i><?= htmlspecialchars($m['name']) ?>
                                </td>
                                <td><span class="badge bg-secondary-subtle text-secondary rounded-pill px-2"><?= $m['total_rooms'] ?></span></td>
                                <td><span class="badge bg-success-subtle text-success rounded-pill px-2 fw-bold"><?= $m['rented_rooms'] ?></span></td>
                                <td>
                                    <?php if ($m['available_rooms'] > 0): ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2 fw-bold"><?= $m['available_rooms'] ?> trống</span>
                                    <?php else: ?>
                                        <span class="text-muted">0</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($m['maintenance_rooms'] > 0): ?>
                                        <span class="badge bg-light text-secondary border rounded-pill px-2"><?= $m['maintenance_rooms'] ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">0</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold text-teal-main"><?= $m['occupancy_rate'] ?>%</td>
                                <td class="fw-bold text-dark"><?= $m['tenant_count'] ?> người</td>
                                <td class="text-end fw-bold" style="color: #0284c7;"><?= number_format($m['deposit_held'], 0, ',', '.') ?> đ</td>
                                <td class="text-end fw-bold text-dark"><?= number_format($m['total_invoiced'], 0, ',', '.') ?> đ</td>
                                <td class="text-end fw-bold text-success"><?= number_format($m['paid_amount'], 0, ',', '.') ?> đ</td>
                                <td class="text-end fw-bold text-danger"><?= number_format($m['unpaid_amount'], 0, ',', '.') ?> đ</td>
                                <td class="text-center fw-bold text-teal-main"><?= $m['collection_rate'] ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                        <!-- DÒNG TỔNG CỘNG HỆ THỐNG -->
                        <tr class="total-row">
                            <td class="text-start text-uppercase text-dark" style="font-weight: 900;">
                                <i class="fa-solid fa-network-wired text-teal-main me-2"></i> TOÀN HỆ THỐNG (TỔNG CỘNG)
                            </td>
                            <td><span class="badge bg-dark rounded-pill px-3 py-1"><?= $systemSummary['total_rooms'] ?></span></td>
                            <td><span class="badge bg-success rounded-pill px-3 py-1 fw-bold"><?= $systemSummary['rented_rooms'] ?></span></td>
                            <td><span class="badge bg-warning rounded-pill px-3 py-1 fw-bold"><?= $systemSummary['available_rooms'] ?> trống</span></td>
                            <td><span class="badge bg-secondary rounded-pill px-3 py-1"><?= $systemSummary['maintenance_rooms'] ?></span></td>
                            <td class="fw-bold text-teal-main fs-6"><?= $systemSummary['occupancy_rate'] ?>%</td>
                            <td class="fw-bold text-dark"><?= $systemSummary['tenant_count'] ?> người</td>
                            <td class="text-end fw-bold" style="color: #0284c7;"><?= number_format($systemSummary['deposit_held'], 0, ',', '.') ?> đ</td>
                            <td class="text-end fw-bold text-dark"><?= number_format($systemSummary['total_invoiced'], 0, ',', '.') ?> đ</td>
                            <td class="text-end fw-bold text-success fs-6"><?= number_format($systemSummary['paid_amount'], 0, ',', '.') ?> đ</td>
                            <td class="text-end fw-bold text-danger fs-6"><?= number_format($systemSummary['unpaid_amount'], 0, ',', '.') ?> đ</td>
                            <td class="text-center fw-bold text-teal-main fs-6"><?= $systemSummary['collection_rate'] ?>%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- KHU VỰC CÁC TAB CHI TIẾT (3 TABS BÁO CÁO TOÀN DIỆN) -->
        <div class="section-box">
            <!-- TAB BUTTONS -->
            <div class="d-flex align-items-center gap-2 mb-4 filter-tab-container flex-wrap no-print">
                <button type="button" class="filter-tab active" data-tab-target="tabInvoices">
                    <i class="fa-solid fa-file-invoice-dollar"></i> 1. Doanh Thu & Công Nợ (<?= count($invoiceRows) ?>)
                </button>
                <button type="button" class="filter-tab" data-tab-target="tabRooms">
                    <i class="fa-solid fa-bed"></i> 2. Hiện Trạng Phòng & Tiền Cọc (<?= count($roomRows) ?>)
                </button>
                <button type="button" class="filter-tab" data-tab-target="tabTenants">
                    <i class="fa-solid fa-users"></i> 3. Khách Thuê & Hợp Đồng (<?= count($tenantRows) ?>)
                </button>
            </div>

            <div class="tab-content" id="reportTabsContent">

                <!-- ============================================================== -->
                <!-- TAB 1: DOANH THU & CÔNG NỢ CHI TIẾT -->
                <!-- ============================================================== -->
                <div class="tab-pane fade show active" id="tabInvoices" role="tabpanel" style="display: block;">
                    <!-- BỘ LỌC TÌM KIẾM HÓA ĐƠN -->
                    <div class="p-3 bg-light rounded-4 mb-3 no-print border">
                        <form method="GET" action="index.php" class="row g-2 align-items-end">
                            <input type="hidden" name="route" value="reports_csv">
                            
                            <div class="col-12 col-md-3">
                                <label class="form-label small fw-bold text-muted mb-1"><i class="fa-solid fa-building text-teal-main me-1"></i> Tòa nhà</label>
                                <select name="building_id" class="form-select rounded-3">
                                    <option value="all" <?= $buildingId === 'all' ? 'selected' : '' ?>>Tất cả 3 tòa nhà</option>
                                    <option value="A" <?= $buildingId === 'A' ? 'selected' : '' ?>>Tòa A - StayMate Sunrise</option>
                                    <option value="B" <?= $buildingId === 'B' ? 'selected' : '' ?>>Tòa B - StayMate Emerald</option>
                                    <option value="C" <?= $buildingId === 'C' ? 'selected' : '' ?>>Tòa C - StayMate Horizon</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-2">
                                <label class="form-label small fw-bold text-muted mb-1"><i class="fa-solid fa-calendar-days text-teal-main me-1"></i> Kỳ hóa đơn</label>
                                <select name="month_year" class="form-select rounded-3">
                                    <option value="all" <?= $monthYear === 'all' ? 'selected' : '' ?>>Tất cả các kỳ</option>
                                    <?php foreach ($periods as $p): ?>
                                        <option value="<?= htmlspecialchars($p) ?>" <?= $monthYear === $p ? 'selected' : '' ?>>Kỳ <?= htmlspecialchars($p) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 col-md-2">
                                <label class="form-label small fw-bold text-muted mb-1"><i class="fa-solid fa-filter text-teal-main me-1"></i> Trạng thái</label>
                                <select name="status" class="form-select rounded-3">
                                    <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>Tất cả trạng thái</option>
                                    <option value="paid" <?= $status === 'paid' ? 'selected' : '' ?>>Đã thu (Doanh thu)</option>
                                    <option value="unpaid" <?= $status === 'unpaid' ? 'selected' : '' ?>>Chưa thu (Công nợ)</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label small fw-bold text-muted mb-1"><i class="fa-solid fa-magnifying-glass text-teal-main me-1"></i> Từ khóa</label>
                                <input type="text" name="keyword" class="form-control rounded-3" placeholder="Số phòng, tên khách, SĐT..." value="<?= htmlspecialchars($keyword) ?>">
                            </div>

                            <div class="col-12 col-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-teal-custom w-100 justify-content-center">
                                    <i class="fa-solid fa-filter"></i> Lọc
                                </button>
                                <a href="index.php?route=reports_csv" class="btn btn-outline-secondary rounded-3 px-3" title="Đặt lại bộ lọc">
                                    <i class="fa-solid fa-rotate-left"></i>
                                </a>
                            </div>
                        </form>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="small text-muted">
                            <i class="fa-solid fa-circle-info text-teal-main me-1"></i> Bấm vào <strong>Mã HĐ</strong> để xem trực tiếp chỉ số điện, nước và đơn giá dịch vụ của từng hóa đơn.
                        </div>
                    </div>

                    <!-- BẢNG HÓA ĐƠN -->
                    <div class="table-responsive">
                        <table class="table table-hover table-custom align-middle border rounded-4 overflow-hidden">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 50px;">STT</th>
                                    <th>Mã HĐ</th>
                                    <th>Tòa Nhà</th>
                                    <th>Phòng</th>
                                    <th>Khách Thuê</th>
                                    <th>Kỳ</th>
                                    <th class="text-end">Tiền Phòng</th>
                                    <th class="text-end">Điện Nước & DV</th>
                                    <th class="text-end">Tổng Hóa Đơn</th>
                                    <th class="text-end text-success">Đã Thu (Doanh Thu)</th>
                                    <th class="text-end text-danger">Còn Nợ (Công Nợ)</th>
                                    <th class="text-center">Trạng Thái</th>
                                    <th>Thanh Toán / Hạn</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($invoiceRows)): ?>
                                    <tr>
                                        <td colspan="13" class="text-center py-5 text-muted">
                                            <i class="fa-solid fa-inbox fs-2 mb-2 d-block text-secondary"></i>
                                            Không có hóa đơn nào phù hợp với bộ lọc hiện tại.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php 
                                    $idx = 1;
                                    foreach ($invoiceRows as $r): 
                                        $isPaid = $r['status'] === 'paid';
                                        $paid = $isPaid ? (float)$r['total_amount'] : 0;
                                        $debt = !$isPaid ? (float)$r['total_amount'] : 0;
                                        $roomPrice = (float)$r['computed_room'];
                                        $serviceTotal = (float)$r['computed_util'];
                                        $dueDate = $r['due_date'];
                                        $isOverdue = !$isPaid && ($dueDate < date('Y-m-d'));
                                    ?>
                                        <tr>
                                            <td class="text-center text-muted fw-bold"><?= $idx++ ?></td>
                                            <td>
                                                <a href="index.php?route=invoices/detail&id=<?= $r['id'] ?>" target="_blank" class="invoice-link" title="Xem chi tiết tiền phòng, điện nước & dịch vụ">
                                                    HD-<?= str_pad((string)$r['id'], 4, '0', STR_PAD_LEFT) ?>
                                                    <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 0.72rem;"></i>
                                                </a>
                                            </td>
                                            <td><?= htmlspecialchars($r['building_name'] ?? ('Tòa ' . substr($r['room_number'], 0, 1))) ?></td>
                                            <td><span class="badge bg-dark rounded-pill px-3 py-1"><?= htmlspecialchars($r['room_number']) ?></span></td>
                                            <td>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($r['tenant_name'] ?? 'Khách vãng lai') ?></div>
                                                <?php if (!empty($r['tenant_phone'])): ?>
                                                    <div class="small text-muted"><i class="fa-solid fa-phone me-1" style="font-size: 0.7rem;"></i><?= htmlspecialchars($r['tenant_phone']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge bg-light text-secondary border"><?= htmlspecialchars($r['month_year']) ?></span></td>
                                            <!-- TIỀN PHÒNG -->
                                            <td class="text-end fw-bold text-dark"><?= number_format($roomPrice, 0, ',', '.') ?> đ</td>
                                            <!-- TIỀN ĐIỆN NƯỚC & DV -->
                                            <td class="text-end">
                                                <div class="fw-bold text-dark"><?= number_format($serviceTotal, 0, ',', '.') ?> đ</div>
                                                <div class="text-muted" style="font-size: 0.72rem;" title="Điện: <?= number_format((float)$r['electric_amount'], 0, ',', '.') ?> đ | Nước: <?= number_format((float)$r['water_amount'], 0, ',', '.') ?> đ | DV: <?= number_format((float)$r['service_fee'], 0, ',', '.') ?> đ">
                                                    Điện: <?= number_format((float)$r['electric_amount'] / 1000, 0) ?>k | Nước: <?= number_format((float)$r['water_amount'] / 1000, 0) ?>k
                                                </div>
                                            </td>
                                            <!-- TỔNG HÓA ĐƠN -->
                                            <td class="text-end fw-bold fs-6"><?= number_format((float)$r['total_amount'], 0, ',', '.') ?> đ</td>
                                            <!-- ĐÃ THU (DOANH THU) -->
                                            <td class="text-end fw-bold text-success">
                                                <?= $isPaid ? (number_format($paid, 0, ',', '.') . ' đ') : '-' ?>
                                            </td>
                                            <!-- CÒN NỢ (CÔNG NỢ) -->
                                            <td class="text-end fw-bold text-danger">
                                                <?= !$isPaid ? (number_format($debt, 0, ',', '.') . ' đ') : '-' ?>
                                            </td>
                                            <!-- TRẠNG THÁI -->
                                            <td class="text-center">
                                                <?php if ($isPaid): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold">
                                                        <i class="fa-solid fa-check me-1"></i> Đã thu
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 fw-bold">
                                                        <i class="fa-solid fa-clock me-1"></i> Chưa thu
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <!-- THANH TOÁN / HẠN THANH TOÁN -->
                                            <td>
                                                <?php if ($isPaid): ?>
                                                    <div class="text-success fw-bold small">
                                                        <i class="fa-solid fa-calendar-check me-1"></i><?= !empty($r['paid_at']) ? date('d/m/Y', strtotime($r['paid_at'])) : date('d/m/Y', strtotime($r['due_date'])) ?>
                                                    </div>
                                                    <div class="text-muted" style="font-size: 0.72rem;"><?= htmlspecialchars($r['payment_method'] ?? 'Chuyển khoản') ?></div>
                                                <?php else: ?>
                                                    <div class="text-danger fw-bold small">
                                                        <i class="fa-solid fa-clock me-1"></i>Hạn: <?= date('d/m/Y', strtotime($dueDate)) ?>
                                                    </div>
                                                    <?php if ($isOverdue): ?>
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2" style="font-size: 0.7rem;">
                                                            <i class="fa-solid fa-triangle-exclamation me-1"></i>Quá hạn
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2" style="font-size: 0.7rem;">
                                                            Chờ thu
                                                        </span>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot class="table-light border-top-2 fw-bold">
                                <tr>
                                    <td colspan="6" class="text-uppercase text-center py-3">TỔNG CỘNG THEO BỘ LỌC</td>
                                    <td class="text-end py-3 text-dark"><?= number_format($filterSummary['room_total'], 0, ',', '.') ?> đ</td>
                                    <td class="text-end py-3 text-dark"><?= number_format($filterSummary['utility_total'], 0, ',', '.') ?> đ</td>
                                    <td class="text-end py-3 text-dark fs-6"><?= number_format($filterSummary['total_amount'], 0, ',', '.') ?> đ</td>
                                    <td class="text-end py-3 text-success fs-6"><?= number_format($filterSummary['paid_amount'], 0, ',', '.') ?> đ</td>
                                    <td class="text-end py-3 text-danger fs-6"><?= number_format($filterSummary['unpaid_amount'], 0, ',', '.') ?> đ</td>
                                    <td class="text-center py-3 text-teal-main"><?= $filterSummary['paid_count'] ?> đã thu / <?= $filterSummary['unpaid_count'] ?> nợ</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- ============================================================== -->
                <!-- TAB 2: BÁO CÁO HIỆN TRẠNG PHÒNG ỐC & TIỀN CỌC GIỮ CHỖ (60 PHÒNG) -->
                <!-- ============================================================== -->
                <div class="tab-pane fade" id="tabRooms" role="tabpanel" style="display: none;">
                    
                    <!-- DÒNG THỐNG KÊ NHANH CHO TAB 2 -->
                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                        <div class="mini-kpi-pill">
                            <i class="fa-solid fa-bed text-dark"></i> Tổng phòng: <strong class="text-dark"><?= $tab2TotalRooms ?></strong>
                        </div>
                        <div class="mini-kpi-pill text-success border-success-subtle bg-success-subtle">
                            <i class="fa-solid fa-circle-check"></i> Đang cho thuê: <strong><?= $tab2Rented ?></strong>
                        </div>
                        <div class="mini-kpi-pill text-warning border-warning-subtle bg-warning-subtle">
                            <i class="fa-solid fa-door-open"></i> Phòng trống: <strong><?= $tab2Available ?></strong>
                        </div>
                        <div class="mini-kpi-pill text-secondary border-secondary-subtle bg-light">
                            <i class="fa-solid fa-wrench"></i> Đang bảo trì: <strong><?= $tab2Maintenance ?></strong>
                        </div>
                        <div class="mini-kpi-pill text-primary border-primary-subtle bg-primary-subtle ms-auto">
                            <i class="fa-solid fa-shield-halved"></i> Tổng cọc đang giữ: <strong><?= number_format($tab2TotalDepositHeld, 0, ',', '.') ?> đ</strong>
                        </div>
                    </div>

                    <!-- BỘ LỌC TÌM KIẾM TRỰC TIẾP TAB 2 -->
                    <div class="p-3 bg-light rounded-4 mb-3 no-print border">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-3">
                                <label class="form-label small fw-bold text-muted mb-1"><i class="fa-solid fa-building text-teal-main me-1"></i> Tòa nhà</label>
                                <select id="filterRoomBuilding" class="form-select rounded-3">
                                    <option value="all">Tất cả 3 tòa nhà</option>
                                    <option value="Tòa A">Tòa A - StayMate Sunrise</option>
                                    <option value="Tòa B">Tòa B - StayMate Emerald</option>
                                    <option value="Tòa C">Tòa C - StayMate Horizon</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label small fw-bold text-muted mb-1"><i class="fa-solid fa-filter text-teal-main me-1"></i> Trạng thái phòng</label>
                                <select id="filterRoomStatus" class="form-select rounded-3">
                                    <option value="all">Tất cả trạng thái</option>
                                    <option value="rented">Đang cho thuê</option>
                                    <option value="available">Phòng trống</option>
                                    <option value="maintenance">Đang bảo trì</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label small fw-bold text-muted mb-1"><i class="fa-solid fa-shield-halved text-teal-main me-1"></i> Tiền đặt cọc</label>
                                <select id="filterRoomDeposit" class="form-select rounded-3">
                                    <option value="all">Tất cả tình trạng cọc</option>
                                    <option value="has_deposit">Đang giữ tiền cọc</option>
                                    <option value="no_deposit">Chưa có tiền cọc</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label small fw-bold text-muted mb-1"><i class="fa-solid fa-magnifying-glass text-teal-main me-1"></i> Tìm kiếm phòng</label>
                                <div class="input-group">
                                    <input type="text" id="searchRoomKeyword" class="form-control rounded-start-3" placeholder="Số phòng, tên khách, SĐT...">
                                    <button type="button" id="btnResetRoomFilter" class="btn btn-outline-secondary rounded-end-3" title="Đặt lại bộ lọc phòng">
                                        <i class="fa-solid fa-rotate-left"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BẢNG BÁO CÁO 60 PHÒNG -->
                    <div class="table-responsive">
                        <table class="table table-hover table-custom align-middle border rounded-4 overflow-hidden" id="roomsReportTable">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 50px;">STT</th>
                                    <th>Số Phòng</th>
                                    <th>Tòa Nhà</th>
                                    <th>Tầng</th>
                                    <th class="text-end">Giá Niêm Yết</th>
                                    <th class="text-center">Trạng Thái</th>
                                    <th>Khách Đang Ở / Giữ Chỗ</th>
                                    <th>Số Điện Thoại</th>
                                    <th>Mã Hợp Đồng</th>
                                    <th class="text-end">Tiền Cọc Giữ</th>
                                    <th class="text-center">Tình Trạng Cọc</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rStt = 1;
                                foreach ($roomRows as $rm): 
                                    $isRented = ($rm['status'] === 'rented');
                                    $isAvail  = ($rm['status'] === 'available');
                                    $isMaint  = ($rm['status'] === 'maintenance');
                                    $bldName  = $rm['building_name'] ?? ('Tòa ' . substr($rm['room_number'], 0, 1));
                                    $depAmt   = (float)($rm['deposit_amount'] ?? 0);
                                    // Trích xuất tầng từ số phòng
                                    $floorNum = 'Tầng ' . (preg_match('/^[A-Z](\d)/i', $rm['room_number'], $fm) ? $fm[1] : '1');
                                ?>
                                    <tr class="room-row" 
                                        data-building="<?= htmlspecialchars($bldName) ?>" 
                                        data-status="<?= $rm['status'] ?>" 
                                        data-deposit="<?= $depAmt > 0 ? 'has_deposit' : 'no_deposit' ?>"
                                        data-keyword="<?= strtolower($rm['room_number'] . ' ' . ($rm['tenant_name'] ?? '') . ' ' . ($rm['tenant_phone'] ?? '') . ' ' . ($rm['contract_code'] ?? '')) ?>">
                                        <td class="text-center text-muted fw-bold room-index"><?= $rStt++ ?></td>
                                        <td class="fw-bold">
                                            <a href="index.php?route=rooms" class="badge bg-dark rounded-pill px-3 py-1 fs-6 text-decoration-none" title="Xem phòng trên sơ đồ">
                                                <?= htmlspecialchars($rm['room_number']) ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($bldName) ?></td>
                                        <td><span class="badge bg-light text-secondary border"><?= $floorNum ?></span></td>
                                        <td class="text-end fw-bold"><?= number_format((float)$rm['price'], 0, ',', '.') ?> đ</td>
                                        <td class="text-center">
                                            <?php if ($isRented): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold">
                                                    <i class="fa-solid fa-circle-check me-1"></i> Đang thuê
                                                </span>
                                            <?php elseif ($isAvail): ?>
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1 fw-bold">
                                                    <i class="fa-solid fa-door-open me-1"></i> Phòng trống
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-3 py-1 fw-bold">
                                                    <i class="fa-solid fa-wrench me-1"></i> Bảo trì
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($rm['tenant_name'])): ?>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($rm['tenant_name']) ?></div>
                                            <?php else: ?>
                                                <span class="text-muted fst-italic">Chưa có người ở</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($rm['tenant_phone'])): ?>
                                                <div class="small text-muted"><i class="fa-solid fa-phone me-1" style="font-size: 0.7rem;"></i><?= htmlspecialchars($rm['tenant_phone']) ?></div>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($rm['contract_code'])): ?>
                                                <a href="index.php?route=contracts" class="fw-bold text-teal-main text-decoration-none">
                                                    <?= htmlspecialchars($rm['contract_code']) ?>
                                                </a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end fw-bold" style="color: #0284c7;">
                                            <?= $depAmt > 0 ? (number_format($depAmt, 0, ',', '.') . ' đ') : '-' ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($depAmt > 0): ?>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2">Đang giữ cọc</span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light border-top-2 fw-bold">
                                <tr>
                                    <td colspan="4" class="text-uppercase text-center py-3">TỔNG CỘNG 60 PHÒNG</td>
                                    <td class="text-end py-3 text-dark"><?= number_format($tab2TotalListedPrice, 0, ',', '.') ?> đ</td>
                                    <td class="text-center py-3 text-success"><?= $tab2Rented ?> thuê / <?= $tab2Available ?> trống</td>
                                    <td colspan="3"></td>
                                    <td class="text-end py-3 text-primary fs-6"><?= number_format($tab2TotalDepositHeld, 0, ',', '.') ?> đ</td>
                                    <td class="text-center py-3 text-muted">100% bảo đảm</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- ============================================================== -->
                <!-- TAB 3: BÁO CÁO KHÁCH THUÊ & HỢP ĐỒNG CƯ TRÚ (52 KHÁCH) -->
                <!-- ============================================================== -->
                <div class="tab-pane fade" id="tabTenants" role="tabpanel" style="display: none;">
                    
                    <!-- DÒNG THỐNG KÊ NHANH CHO TAB 3 -->
                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                        <div class="mini-kpi-pill">
                            <i class="fa-solid fa-users text-dark"></i> Tổng khách cư trú: <strong class="text-dark"><?= $tab3TotalTenants ?></strong>
                        </div>
                        <div class="mini-kpi-pill text-success border-success-subtle bg-success-subtle">
                            <i class="fa-solid fa-file-contract"></i> Hợp đồng hiệu lực: <strong><?= $tab3TotalTenants ?></strong>
                        </div>
                        <?php if ($tab3ExpiringSoon > 0): ?>
                            <div class="mini-kpi-pill text-warning border-warning-subtle bg-warning-subtle">
                                <i class="fa-solid fa-clock"></i> Sắp hết hạn (≤ 30 ngày): <strong><?= $tab3ExpiringSoon ?></strong>
                            </div>
                        <?php endif; ?>
                        <div class="mini-kpi-pill text-teal-main border-teal-subtle bg-teal-subtle ms-auto">
                            <i class="fa-solid fa-coins"></i> Tiền thuê theo HĐ: <strong><?= number_format($tab3TotalRent, 0, ',', '.') ?> đ/tháng</strong>
                        </div>
                        <div class="mini-kpi-pill text-primary border-primary-subtle bg-primary-subtle">
                            <i class="fa-solid fa-shield-halved"></i> Tiền cọc bảo đảm: <strong><?= number_format($tab3TotalDeposit, 0, ',', '.') ?> đ</strong>
                        </div>
                    </div>

                    <!-- BỘ LỌC TÌM KIẾM TRỰC TIẾP TAB 3 -->
                    <div class="p-3 bg-light rounded-4 mb-3 no-print border">
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold text-muted mb-1"><i class="fa-solid fa-building text-teal-main me-1"></i> Tòa nhà</label>
                                <select id="filterTenantBuilding" class="form-select rounded-3">
                                    <option value="all">Tất cả 3 tòa nhà</option>
                                    <option value="Tòa A">Tòa A - StayMate Sunrise</option>
                                    <option value="Tòa B">Tòa B - StayMate Emerald</option>
                                    <option value="Tòa C">Tòa C - StayMate Horizon</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold text-muted mb-1"><i class="fa-solid fa-clock text-teal-main me-1"></i> Thời hạn hợp đồng</label>
                                <select id="filterTenantExpiry" class="form-select rounded-3">
                                    <option value="all">Tất cả thời hạn</option>
                                    <option value="expiring_soon">Sắp hết hạn (≤ 30 ngày)</option>
                                    <option value="long_term">Còn hạn dài (> 30 ngày)</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold text-muted mb-1"><i class="fa-solid fa-magnifying-glass text-teal-main me-1"></i> Tìm kiếm khách thuê</label>
                                <div class="input-group">
                                    <input type="text" id="searchTenantKeyword" class="form-control rounded-start-3" placeholder="Tên khách, SĐT, CCCD, phòng...">
                                    <button type="button" id="btnResetTenantFilter" class="btn btn-outline-secondary rounded-end-3" title="Đặt lại bộ lọc khách">
                                        <i class="fa-solid fa-rotate-left"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BẢNG BÁO CÁO KHÁCH THUÊ & HỢP ĐỒNG -->
                    <div class="table-responsive">
                        <table class="table table-hover table-custom align-middle border rounded-4 overflow-hidden" id="tenantsReportTable">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 50px;">STT</th>
                                    <th>Khách Thuê</th>
                                    <th>Số Điện Thoại</th>
                                    <th>Số CCCD</th>
                                    <th>Phòng</th>
                                    <th>Tòa Nhà</th>
                                    <th>Mã Hợp Đồng</th>
                                    <th>Thời Hạn HĐ</th>
                                    <th class="text-center">Thời Gian Còn Lại</th>
                                    <th class="text-end">Tiền Thuê/Tháng</th>
                                    <th class="text-end">Tiền Cọc Giữ</th>
                                    <th>Người Ở Ghép</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $tStt = 1;
                                foreach ($tenantRows as $tn): 
                                    $daysLeft = (int)($tn['days_left'] ?? 0);
                                    $tBldName = $tn['building_name'] ?? ('Tòa ' . substr($tn['room_number'], 0, 1));
                                ?>
                                    <tr class="tenant-row"
                                        data-building="<?= htmlspecialchars($tBldName) ?>"
                                        data-expiry="<?= $daysLeft <= 30 ? 'expiring_soon' : 'long_term' ?>"
                                        data-keyword="<?= strtolower($tn['full_name'] . ' ' . $tn['phone'] . ' ' . ($tn['cccd'] ?? '') . ' ' . $tn['room_number'] . ' ' . $tn['contract_code']) ?>">
                                        <td class="text-center text-muted fw-bold tenant-index"><?= $tStt++ ?></td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($tn['full_name']) ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars($tn['email'] ?? '') ?></div>
                                        </td>
                                        <td class="fw-bold"><i class="fa-solid fa-phone me-1 text-muted" style="font-size: 0.75rem;"></i><?= htmlspecialchars($tn['phone']) ?></td>
                                        <td><?= htmlspecialchars($tn['cccd'] ?? '-') ?></td>
                                        <td><span class="badge bg-dark rounded-pill px-3 py-1"><?= htmlspecialchars($tn['room_number']) ?></span></td>
                                        <td><?= htmlspecialchars($tBldName) ?></td>
                                        <td>
                                            <a href="index.php?route=contracts" class="fw-bold text-teal-main text-decoration-none">
                                                <?= htmlspecialchars($tn['contract_code']) ?>
                                            </a>
                                        </td>
                                        <td class="small">
                                            <div>Từ: <?= date('d/m/Y', strtotime($tn['start_date'])) ?></div>
                                            <div>Đến: <strong><?= date('d/m/Y', strtotime($tn['end_date'])) ?></strong></div>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($daysLeft < 0): ?>
                                                <span class="badge bg-danger rounded-pill px-3 py-1">Đã hết hạn</span>
                                            <?php elseif ($daysLeft <= 30): ?>
                                                <span class="badge bg-warning text-dark rounded-pill px-3 py-1 fw-bold">
                                                    <i class="fa-solid fa-clock me-1"></i> Còn <?= $daysLeft ?> ngày
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-secondary border rounded-pill px-3 py-1">Còn <?= $daysLeft ?> ngày</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end fw-bold text-dark"><?= number_format((float)$tn['monthly_rent'], 0, ',', '.') ?> đ</td>
                                        <td class="text-end fw-bold text-primary"><?= number_format((float)$tn['deposit_amount'], 0, ',', '.') ?> đ</td>
                                        <td class="small text-muted">
                                            <?php if (!empty($tn['roommate_name'])): ?>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($tn['roommate_name']) ?></div>
                                                <div><?= htmlspecialchars($tn['roommate_phone'] ?? '') ?></div>
                                            <?php else: ?>
                                                <span class="text-muted">Ở 1 mình</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light border-top-2 fw-bold">
                                <tr>
                                    <td colspan="6" class="text-uppercase text-center py-3">TỔNG CỘNG 52 KHÁCH THUÊ</td>
                                    <td colspan="3" class="text-center py-3 text-teal-main"><?= $tab3TotalTenants ?> HĐ hiệu lực (<?= $tab3ExpiringSoon ?> HĐ sắp hết hạn)</td>
                                    <td class="text-end py-3 text-dark fs-6"><?= number_format($tab3TotalRent, 0, ',', '.') ?> đ</td>
                                    <td class="text-end py-3 text-primary fs-6"><?= number_format($tab3TotalDeposit, 0, ',', '.') ?> đ</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            </div>

            <!-- FOOTER XUẤT CSV -->
            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top flex-wrap gap-2 no-print">
                <div class="text-muted small">
                    <i class="fa-solid fa-circle-check text-success me-1"></i> Toàn bộ 3 mục dữ liệu trên đều được xuất tổng hợp trong file CSV chuẩn quốc tế UTF-8 BOM.
                </div>
                <a href="<?= $exportUrl ?>" class="btn-export-csv">
                    <i class="fa-solid fa-file-arrow-down"></i> Tải Toàn Bộ Báo Cáo Dạng CSV
                </a>
            </div>
        </div>

    </div>

    <!-- MODAL THÔNG TIN TÀI KHOẢN -->
    <div class="modal fade" id="profileModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-id-card text-teal-main me-2"></i> Thông Tin Quản Trị Viên</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div class="avatar-circle mx-auto mb-3" style="width: 70px; height: 70px; font-size: 1.6rem;">AD</div>
                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($userFullName) ?></h5>
                    <span class="badge bg-teal-subtle text-teal-main border border-teal-subtle px-3 py-1 rounded-pill fw-bold mb-3">Quản trị viên (Admin)</span>
                    <div class="text-start p-3 bg-light rounded-3 small">
                        <div class="mb-2"><i class="fa-solid fa-user me-2 text-muted"></i> <strong>Tên đăng nhập:</strong> <?= htmlspecialchars($userName) ?></div>
                        <div class="mb-2"><i class="fa-solid fa-envelope me-2 text-muted"></i> <strong>Email:</strong> <?= htmlspecialchars($userEmail) ?></div>
                        <div><i class="fa-solid fa-phone me-2 text-muted"></i> <strong>Điện thoại:</strong> <?= htmlspecialchars($userPhone) ?></div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL ĐỔI MẬT KHẨU -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <form action="index.php?route=accounts/change_password" method="POST">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-key text-warning me-2"></i> Đổi Mật Khẩu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Mật khẩu hiện tại</label>
                            <input type="password" name="current_password" class="form-control rounded-3" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Mật khẩu mới</label>
                            <input type="password" name="new_password" class="form-control rounded-3" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Xác nhận mật khẩu mới</label>
                            <input type="password" name="confirm_password" class="form-control rounded-3" required minlength="6">
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-teal-custom">Lưu mật khẩu mới</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ==========================================
        // 1. CHUYỂN ĐỔI TAB BÁO CÁO (CHẮC CHẮN 100%)
        // ==========================================
        function activateReportTab(tabTargetId) {
            // Cập nhật trạng thái nút tab
            document.querySelectorAll('.filter-tab').forEach(function(b) {
                if (b.getAttribute('data-tab-target') === tabTargetId) {
                    b.classList.add('active');
                } else {
                    b.classList.remove('active');
                }
            });

            // Ẩn tất cả các tab-pane
            document.querySelectorAll('#reportTabsContent .tab-pane').forEach(function(pane) {
                pane.classList.remove('show', 'active');
                pane.style.display = 'none';
            });

            // Hiển thị tab-pane được chọn
            const targetPane = document.getElementById(tabTargetId);
            if (targetPane) {
                targetPane.style.display = 'block';
                setTimeout(function() {
                    targetPane.classList.add('show', 'active');
                }, 10);
            }

            // Lưu trạng thái vào URL hash
            history.replaceState(null, null, '#' + tabTargetId);
        }

        document.querySelectorAll('.filter-tab').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const target = this.getAttribute('data-tab-target');
                if (target) {
                    activateReportTab(target);
                }
            });
        });

        // Tự động mở đúng tab nếu URL có hash
        window.addEventListener('DOMContentLoaded', function() {
            const currentHash = window.location.hash.replace('#', '');
            if (currentHash && document.getElementById(currentHash)) {
                activateReportTab(currentHash);
            }
        });

        // ==========================================
        // 2. BỘ LỌC TÌM KIẾM TRỰC TIẾP CHO TAB 2 (PHÒNG & CỌC)
        // ==========================================
        function applyRoomFilter() {
            const bldVal = document.getElementById('filterRoomBuilding').value;
            const statusVal = document.getElementById('filterRoomStatus').value;
            const depVal = document.getElementById('filterRoomDeposit').value;
            const kw = document.getElementById('searchRoomKeyword').value.trim().toLowerCase();

            let visibleIdx = 1;
            document.querySelectorAll('#roomsReportTable tbody .room-row').forEach(function(row) {
                const rowBld = row.getAttribute('data-building') || '';
                const rowStatus = row.getAttribute('data-status') || '';
                const rowDep = row.getAttribute('data-deposit') || '';
                const rowKw = row.getAttribute('data-keyword') || '';

                const matchBld = (bldVal === 'all' || rowBld.indexOf(bldVal) !== -1);
                const matchStatus = (statusVal === 'all' || rowStatus === statusVal);
                const matchDep = (depVal === 'all' || rowDep === depVal);
                const matchKw = (kw === '' || rowKw.indexOf(kw) !== -1);

                if (matchBld && matchStatus && matchDep && matchKw) {
                    row.style.display = '';
                    row.querySelector('.room-index').textContent = visibleIdx++;
                } else {
                    row.style.display = 'none';
                }
            });
        }

        document.getElementById('filterRoomBuilding').addEventListener('change', applyRoomFilter);
        document.getElementById('filterRoomStatus').addEventListener('change', applyRoomFilter);
        document.getElementById('filterRoomDeposit').addEventListener('change', applyRoomFilter);
        document.getElementById('searchRoomKeyword').addEventListener('input', applyRoomFilter);
        document.getElementById('btnResetRoomFilter').addEventListener('click', function() {
            document.getElementById('filterRoomBuilding').value = 'all';
            document.getElementById('filterRoomStatus').value = 'all';
            document.getElementById('filterRoomDeposit').value = 'all';
            document.getElementById('searchRoomKeyword').value = '';
            applyRoomFilter();
        });

        // ==========================================
        // 3. BỘ LỌC TÌM KIẾM TRỰC TIẾP CHO TAB 3 (KHÁCH & HỢP ĐỒNG)
        // ==========================================
        function applyTenantFilter() {
            const bldVal = document.getElementById('filterTenantBuilding').value;
            const expVal = document.getElementById('filterTenantExpiry').value;
            const kw = document.getElementById('searchTenantKeyword').value.trim().toLowerCase();

            let visibleIdx = 1;
            document.querySelectorAll('#tenantsReportTable tbody .tenant-row').forEach(function(row) {
                const rowBld = row.getAttribute('data-building') || '';
                const rowExp = row.getAttribute('data-expiry') || '';
                const rowKw = row.getAttribute('data-keyword') || '';

                const matchBld = (bldVal === 'all' || rowBld.indexOf(bldVal) !== -1);
                const matchExp = (expVal === 'all' || rowExp === expVal);
                const matchKw = (kw === '' || rowKw.indexOf(kw) !== -1);

                if (matchBld && matchExp && matchKw) {
                    row.style.display = '';
                    row.querySelector('.tenant-index').textContent = visibleIdx++;
                } else {
                    row.style.display = 'none';
                }
            });
        }

        document.getElementById('filterTenantBuilding').addEventListener('change', applyTenantFilter);
        document.getElementById('filterTenantExpiry').addEventListener('change', applyTenantFilter);
        document.getElementById('searchTenantKeyword').addEventListener('input', applyTenantFilter);
        document.getElementById('btnResetTenantFilter').addEventListener('click', function() {
            document.getElementById('filterTenantBuilding').value = 'all';
            document.getElementById('filterTenantExpiry').value = 'all';
            document.getElementById('searchTenantKeyword').value = '';
            applyTenantFilter();
        });
    </script>
</body>
</html>
