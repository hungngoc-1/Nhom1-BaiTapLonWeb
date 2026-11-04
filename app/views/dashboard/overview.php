<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
$userFullName = $_SESSION['full_name'] ?? 'Quản trị viên Hệ thống';
$userRole     = $_SESSION['role'] ?? 'admin';
$userName     = $_SESSION['username'] ?? 'admin01';
$userEmail    = $_SESSION['email'] ?? 'admin@nhatro.com';
$userPhone    = $_SESSION['phone'] ?? '0901234567';

// Xác định thông tin Tòa nhà đang xem từ dữ liệu Controller truyền sang
$firstBldKey = !empty($buildings) ? (string)array_key_first($buildings) : 'A';
$building_id = $building_id ?? ($_GET['building_id'] ?? ($_SESSION['selected_building_id'] ?? $firstBldKey));
if (!isset($buildings[$building_id])) {
    $building_id = $firstBldKey;
}
$current_bld = [
    'id'      => $building_id,
    'code'    => $buildings[$building_id]['code'] ?? ('TÒA ' . $building_id),
    'name'    => $buildings[$building_id]['name'] ?? ('Tòa nhà ' . $building_id),
    'address' => $buildings[$building_id]['address'] ?? '',
    'manager' => $buildings[$building_id]['manager'] ?? $userFullName
];

$total_rooms      = $stats['total_rooms'] ?? 0;
$rented_rooms     = $stats['rented_rooms'] ?? 0;
$empty_rooms      = $stats['empty_rooms'] ?? 0;
$occupancy_rate   = $stats['occupancy_rate'] ?? 0;
$total_revenue    = $stats['total_revenue'] ?? 0;
$paid_count       = $stats['paid_invoices'] ?? 0;
$total_debt       = $stats['total_debt'] ?? 0;
$unpaid_count     = $stats['unpaid_invoices'] ?? 0;
$active_contracts = $stats['active_contracts'] ?? 0;
$total_contracts  = $stats['total_contracts'] ?? 0;

$currentStatusFilter = $_GET['status'] ?? '';
$currentKeyword      = $_GET['keyword'] ?? '';
$currentMonthFilter  = $_GET['month_year'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayMate - Tổng quan Tòa Nhà</title>
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
        .cursor-pointer { cursor: pointer; }

        /* Navbar */
        .navbar-custom { background: #ffffff; padding: 12px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04); z-index: 100; border-bottom: 1px solid #f1f5f9;}
        .brand-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .brand-logo i { color: var(--teal-main); font-size: 2.2rem; }
        .brand-text { color: var(--teal-main); font-weight: 900; font-size: 1.4rem; line-height: 1; letter-spacing: -0.5px;}
        .brand-sub { color: #64748b; font-size: 0.75rem; font-weight: 600; }
        
        .nav-pills { gap: 6px; flex-wrap: nowrap !important; }
        .nav-pills .nav-link { 
            color: #475569; font-weight: 800; font-size: 0.88rem; 
            padding: 8px 14px; border-radius: 50px; transition: all 0.2s; white-space: nowrap; 
        }
        .nav-pills .nav-link i { margin-right: 6px; }
        .nav-pills .nav-link:hover { color: var(--teal-main); background: #f0fdfa; }
        .nav-pills .nav-link.active { background-color: var(--teal-main); color: white; box-shadow: 0 6px 15px rgba(48, 143, 134, 0.35); }

        /* Avatar Khung tròn tuyệt đối có viền và bóng */
        .avatar-circle {
            width: 46px; height: 46px;
            background-color: var(--teal-main);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 900; font-size: 1rem;
            border: 3px solid #ffffff; 
            box-shadow: 0 6px 15px rgba(48, 143, 134, 0.4);
            transition: 0.2s;
        }

        /* Nút Quay Lại Bo Tròn & Pill Chuyển Tòa Nhà */
        .btn-back-content { 
            background: white; border: 3px solid #ffffff; color: #334155; font-weight: 800; font-size: 0.9rem; 
            padding: 10px 26px; border-radius: 50px; transition: 0.2s; text-decoration: none; 
            display: inline-flex; align-items: center; box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            position: relative; z-index: 2;
        }
        .btn-back-content:hover { background: #f8fafc; border-color: var(--teal-main); color: var(--teal-main); transform: translateY(-2px); box-shadow: 0 12px 25px rgba(0,0,0,0.12);}

        .bld-switch-pill {
            background: white; border: 2px solid #e2e8f0; color: #475569; font-weight: 800; font-size: 0.85rem;
            padding: 8px 18px; border-radius: 50px; text-decoration: none; transition: 0.2s;
            display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .bld-switch-pill:hover { border-color: var(--teal-main); color: var(--teal-main); background: #f0fdfa; }
        .bld-switch-pill.active { background: var(--teal-main); border-color: var(--teal-main); color: white; box-shadow: 0 6px 15px rgba(48, 143, 134, 0.3); }

        /* Header Tòa Nhà */
        .header-title-wrapper { position: relative; z-index: 2; display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; flex-wrap: wrap; gap: 15px; }
        .main-title { font-weight: 900; color: #0f172a; font-size: 1.9rem; letter-spacing: -0.5px; display: inline-flex; align-items: center; flex-wrap: wrap; }
        .bld-highlight { color: var(--teal-main); margin-left: 8px; position: relative;}
        
        .title-sparkle { position: absolute; background-color: #fde047; border-radius: 2px; }
        .ts-1 { width: 4px; height: 12px; top: -10px; right: -15px; transform: rotate(20deg); }
        .ts-2 { width: 12px; height: 4px; top: 15px; right: -25px; transform: rotate(15deg); }
        
        .bld-address-txt { font-size: 0.95rem; font-weight: 700; color: #64748b; margin-top: 5px;}
        
        /* Ngày tháng Khung bo tròn */
        .date-badge { background: white; border-radius: 50px; padding: 12px 28px; font-weight: 800; color: #475569; font-size: 0.9rem; box-shadow: 0 8px 20px rgba(0,0,0,0.08); border: 3px solid #ffffff; }

        .header-house-graphic {
            position: absolute; right: 0; top: -50px; width: 300px; height: 200px; z-index: 1; pointer-events: none;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 200"><path d="M0,180 Q150,150 300,180 L300,200 L0,200 Z" fill="%23dcfce7"/><rect x="120" y="80" width="80" height="100" fill="%23e0f2fe"/><polygon points="100,80 160,30 220,80" fill="%230284c7"/><polygon points="160,30 220,80 250,80 190,30" fill="%2338bdf8"/><rect x="140" y="100" width="15" height="20" fill="%23ffffff"/><rect x="165" y="100" width="15" height="20" fill="%23ffffff"/><rect x="140" y="140" width="15" height="40" fill="%23ffffff"/><circle cx="50" cy="140" r="30" fill="%234ade80"/><circle cx="80" cy="150" r="40" fill="%2322c55e"/><circle cx="240" cy="140" r="35" fill="%2322c55e"/><circle cx="270" cy="150" r="25" fill="%2316a34a"/></svg>');
            background-size: contain; background-repeat: no-repeat; background-position: bottom right;
        }

        /* Overview Cards - Viền Dày, Bóng Đậm, Bo Tròn Sâu */
        .overview-card {
            border-radius: 30px;
            padding: 24px; position: relative; overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.12);
            border: 4px solid #ffffff;
            height: 100%; transition: transform 0.3s ease, box-shadow 0.3s ease; backdrop-filter: blur(5px);
            cursor: pointer;
        }
        .overview-card:hover { transform: translateY(-8px); box-shadow: 0 25px 45px rgba(0,0,0,0.18); }
        
        .card-green { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); }
        .card-blue { background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); }
        .card-yellow { background: linear-gradient(135deg, #fef9c3 0%, #fef08a 100%); }
        .card-purple { background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%); }
        
        .card-bg-pattern { position: absolute; right: 0; bottom: 0; width: 140px; height: 140px; background-size: contain; background-repeat: no-repeat; background-position: bottom right; opacity: 0.9; pointer-events: none;}
        
        .pattern-house { background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect x="30" y="50" width="50" height="40" fill="%23bae6fd"/><polygon points="20,50 55,20 90,50" fill="%233b82f6"/><rect x="40" y="60" width="10" height="10" fill="%23ffffff"/><rect x="60" y="60" width="10" height="10" fill="%23ffffff"/><circle cx="15" cy="80" r="10" fill="%234ade80"/><circle cx="95" cy="75" r="12" fill="%2322c55e"/></svg>'); }
        .pattern-wave { background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path d="M0,70 Q25,40 50,70 T100,70 L100,100 L0,100 Z" fill="%23bae6fd" opacity="0.5"/><path d="M0,90 Q25,60 50,90 T100,90 L100,100 L0,100 Z" fill="%237dd3fc" opacity="0.5"/></svg>'); }
        .pattern-wave-yellow { background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path d="M0,70 Q25,40 50,70 T100,70 L100,100 L0,100 Z" fill="%23fde047" opacity="0.5"/><path d="M0,90 Q25,60 50,90 T100,90 L100,100 L0,100 Z" fill="%23facc15" opacity="0.5"/></svg>'); }
        .pattern-people { background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="30" cy="40" r="12" fill="%23c084fc" opacity="0.8"/><circle cx="50" cy="30" r="15" fill="%23a855f7"/><circle cx="70" cy="40" r="12" fill="%23c084fc" opacity="0.8"/><path d="M10,90 Q30,60 50,60 Q70,60 90,90" fill="%23a855f7" opacity="0.8"/></svg>'); }
        
        .overview-title { font-size: 0.8rem; font-weight: 900; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; text-transform: uppercase; position: relative; z-index: 2;}
        .overview-val { font-size: 2.1rem; font-weight: 900; line-height: 1; letter-spacing: -1px; position: relative; z-index: 2;}
        
        .overview-icon-bg {
            position: absolute; right: 20px; top: 50%; transform: translateY(-50%);
            width: 52px; height: 52px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1.35rem;
            box-shadow: 0 10px 20px rgba(0,0,0,0.15); z-index: 2;
        }
        .icon-blue { background-color: #3b82f6; }
        
        .doc-icon-wrapper {
            position: absolute; right: 22px; top: 50%; transform: translateY(-50%);
            width: 46px; height: 56px; background: #fbbf24; border-radius: 6px;
            display: flex; flex-direction: column; align-items: center; padding-top: 8px; gap: 4px;
            box-shadow: 2px 8px 15px rgba(0,0,0,0.15); z-index: 2;
        }
        .doc-line { width: 28px; height: 4px; background: white; border-radius: 2px; }
        .doc-badge { position: absolute; bottom: -10px; right: -10px; width: 26px; height: 26px; background: #eab308; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; border: 3px solid white; box-shadow: 0 4px 8px rgba(0,0,0,0.1);}

        /* Invoice & Chart Section Khung Tròn Bóng Đổ */
        .invoice-section { 
            background: rgba(255,255,255,0.96); 
            border-radius: 30px; 
            border: 4px solid #ffffff; 
            padding: 24px; 
            box-shadow: 0 15px 40px rgba(0,0,0,0.08); 
            position: relative; z-index: 2;
            margin-bottom: 24px;
        }
        /* Nút Lập Hóa Đơn Tròn Viên Thuốc */
        .btn-teal-custom { background: var(--teal-main); border-radius: 50px; font-weight: 800; color: white; padding: 10px 26px; border: 2px solid var(--teal-hover); box-shadow: 0 8px 20px rgba(48, 143, 134, 0.4); font-size: 0.92rem; transition: 0.2s; text-decoration: none; display: inline-flex; align-items: center; }
        .btn-teal-custom:hover { background: var(--teal-hover); color: white; transform: translateY(-2px); }

        .filter-tab {
            padding: 8px 18px; border-radius: 50px; font-weight: 800; font-size: 0.83rem;
            text-decoration: none; border: 1.5px solid #e2e8f0; color: #475569; background: #ffffff; transition: 0.2s;
        }
        .filter-tab:hover { border-color: var(--teal-main); color: var(--teal-main); }
        .filter-tab.active { background: var(--teal-main); border-color: var(--teal-main); color: #ffffff; }

        .table-custom thead th {
            background-color: #f0fdfa; color: var(--teal-main); font-size: 0.78rem; font-weight: 900;
            text-transform: uppercase; padding: 14px 14px; border-bottom: 2px solid #ccfbf1;
        }
        .table-custom tbody td {
            padding: 14px 14px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 600;
        }

        .empty-folder-illus {
            width: 150px; height: 120px; margin: 0 auto 15px auto;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 150 120"><path d="M20,100 Q75,110 130,100" stroke="%23cbd5e1" stroke-width="4" fill="none" stroke-linecap="round"/><path d="M10,80 Q30,40 50,70" fill="%236ee7b7"/><path d="M130,70 Q110,30 90,60" fill="%2334d399"/><path d="M40,50 L60,50 L70,40 L120,40 L120,90 L40,90 Z" fill="%2399f6e4"/><path d="M30,60 L70,60 L80,50 L130,50 L110,100 L20,100 Z" fill="%235eead4"/><path d="M40,30 L45,15" stroke="%23fbbf24" stroke-width="3" stroke-linecap="round"/><path d="M90,20 L105,10" stroke="%23fbbf24" stroke-width="3" stroke-linecap="round"/></svg>');
            background-size: contain; background-repeat: no-repeat; background-position: center;
        }

        .footer-custom { background: #ffffff; padding: 20px 0; color: #64748b; font-size: 0.85rem; font-weight: 700; margin-top: auto; border-top: 1px solid #f1f5f9; position: relative; z-index: 10;}
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container-fluid px-4 px-xl-5 d-flex justify-content-between align-items-center">
            <a class="brand-logo me-3 me-xl-4" href="index.php?route=dashboard">
                <i class="fa-solid fa-house-chimney text-teal-main"></i>
                <div>
                    <div class="brand-text">StayMate</div>
                    <div class="brand-sub">Quản Lý Nhà Trọ</div>
                </div>
            </a>

            <!-- MENU ĐÃ ĐƯỢC CHỈNH SỬA LẠI THEO CHUẨN -->
            <ul class="nav nav-pills flex-nowrap me-auto">
                <li class="nav-item"><a class="nav-link active" href="index.php?route=overview"><i class="fa-solid fa-border-all"></i> Tổng quan</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=rooms"><i class="fa-solid fa-bed"></i> Phòng trọ</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=tenants"><i class="fa-solid fa-users"></i> Khách thuê</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=contracts"><i class="fa-solid fa-file-signature"></i> Hợp đồng</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=utilities"><i class="fa-solid fa-bolt"></i> Điện Nước</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=invoices"><i class="fa-solid fa-file-invoice-dollar"></i> Hóa đơn</a></li>
                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?><li class="nav-item"><a class="nav-link" href="index.php?route=accounts"><i class="fa-solid fa-user-gear"></i> Tài khoản</a></li><?php endif; ?>
                <li class="nav-item"><a class="nav-link" href="index.php?route=showcase" target="_blank"><i class="fa-regular fa-eye"></i> Xem phòng khách</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=tenant/portal" target="_blank"><i class="fa-solid fa-user-shield text-teal-main"></i> Cổng khách thuê</a></li>
            </ul>

            <div class="d-flex align-items-center gap-4">
                <!-- Icon Chuông Thông Báo -->
                <div class="text-secondary cursor-pointer" title="Thông báo">
                    <i class="fa-solid fa-bell fs-5"></i>
                </div>
                <!-- Dropdown Tài khoản -->
                <div class="dropdown">
                    <div class="d-flex align-items-center gap-2 cursor-pointer" data-bs-toggle="dropdown">
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
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 px-xl-5 py-4 position-relative">

        <?php if (!empty($flashSuccess)): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 fw-bold mb-3 position-relative z-3" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> <?= htmlspecialchars($flashSuccess) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($flashError)): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 fw-bold mb-3 position-relative z-3" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> <?= htmlspecialchars($flashError) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Nút Quay Lại Trang Tòa Nhà -->
        <?php if (($userRole ?? '') === 'admin'): ?>
        <div class="d-flex justify-content-start align-items-center mb-4 position-relative z-2">
            <a href="index.php?route=dashboard" class="btn-back-content">
                <i class="fa-solid fa-arrow-left me-2"></i> Quay lại Tòa nhà
            </a>
        </div>
        <?php endif; ?>

        <div class="header-title-wrapper">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1" style="font-size: 0.85rem; font-weight: 700;">
                        <li class="breadcrumb-item"><a href="index.php?route=dashboard" class="text-teal-main text-decoration-none"><i class="fa-solid fa-house"></i> Hệ thống Quản lý</a></li>
                        <li class="breadcrumb-item text-muted" aria-current="page">Tổng quan Quản lý Nhà trọ</li>
                    </ol>
                </nav>
                <div class="main-title">
                    Tổng quan Quản lý Nhà trọ - 
                    <span class="bld-highlight">
                        <?= htmlspecialchars($current_bld['name']) ?>
                        <span class="title-sparkle ts-1"></span>
                        <span class="title-sparkle ts-2"></span>
                    </span>
                </div>
                <div class="bld-address-txt"><i class="fa-solid fa-location-dot text-teal-main me-1"></i> <?= htmlspecialchars($current_bld['address']) ?></div>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <div class="date-badge d-none d-md-block position-relative z-2">
                    <i class="fa-regular fa-calendar-days me-2"></i> Hôm nay, <?php echo date('d/m/Y'); ?>
                </div>
            </div>
            
            <div class="header-house-graphic d-none d-lg-block"></div>
        </div>

        <!-- HÀNG TRÊN: 4 Thẻ Biểu Đồ (Chỉ hiện Biểu đồ thuần túy, sạch sẽ, không rườm rà thông tin) -->
        <div class="row g-3 mb-3 position-relative z-2">
            <!-- 1. Tỷ lệ lấp đầy (Biểu đồ Tròn) -->
            <div class="col-12 col-md-6 col-xl-3">
                <div class="overview-card card-green d-flex flex-column justify-content-between p-3" style="height: 190px;" data-bs-toggle="modal" data-bs-target="#ovRoomsModal" title="Bấm để xem danh sách phòng">
                    <div class="overview-title text-success mb-1">
                        <i class="fa-solid fa-chart-pie me-1"></i> TỶ LỆ LẤP ĐẦY
                    </div>
                    <div class="position-relative mx-auto my-auto" style="width: 100%; height: 135px;">
                        <canvas id="chartCardOccupancy"></canvas>
                    </div>
                </div>
            </div>

            <!-- 2. Thực thu (Biểu đồ Cột dọc) -->
            <div class="col-12 col-md-6 col-xl-3">
                <div class="overview-card card-blue d-flex flex-column justify-content-between p-3" style="height: 190px;" data-bs-toggle="modal" data-bs-target="#ovInvoicesModal" title="Bấm để xem tóm tắt hóa đơn đã thu">
                    <div class="overview-title text-primary mb-1">
                        <i class="fa-solid fa-chart-column me-1"></i> THỰC THU (ĐÃ THU)
                    </div>
                    <div class="position-relative mx-auto my-auto" style="width: 100%; height: 135px;">
                        <canvas id="chartCardRevenue"></canvas>
                    </div>
                </div>
            </div>

            <!-- 3. Công nợ chưa thu (Biểu đồ Cột dọc) -->
            <div class="col-12 col-md-6 col-xl-3">
                <div class="overview-card card-yellow d-flex flex-column justify-content-between p-3" style="height: 190px;" data-bs-toggle="modal" data-bs-target="#ovInvoicesModal" title="Bấm để xem tóm tắt công nợ chưa thu">
                    <div class="overview-title mb-1" style="color: #b45309 !important;">
                        <i class="fa-solid fa-chart-bar me-1"></i> CÔNG NỢ CHƯA THU
                    </div>
                    <div class="position-relative mx-auto my-auto" style="width: 100%; height: 135px;">
                        <canvas id="chartCardDebt"></canvas>
                    </div>
                </div>
            </div>

            <!-- 4. Khách & Hợp đồng (Biểu đồ Tròn) -->
            <div class="col-12 col-md-6 col-xl-3">
                <div class="overview-card card-purple d-flex flex-column justify-content-between p-3" style="height: 190px;" data-bs-toggle="modal" data-bs-target="#ovContractsModal" title="Bấm để xem danh sách hợp đồng">
                    <div class="overview-title mb-1" style="color: #7e22ce;">
                        <i class="fa-solid fa-pie-chart me-1"></i> KHÁCH & HỢP ĐỒNG
                    </div>
                    <div class="position-relative mx-auto my-auto" style="width: 100%; height: 135px;">
                        <canvas id="chartCardContracts"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- HÀNG DƯỚI: Chia 2 Cột lớn theo đúng mẫu ảnh (Trái 6/12 gồm 2 box xếp dọc, Phải 6/12 là Khung Nhật ký hoạt động lớn) -->
        <div class="row g-3 mb-2 position-relative z-2 align-items-stretch">
            <!-- CỘT TRÁI (6/12): Xếp chồng 2 Biểu đồ Phân tích -->
            <div class="col-12 col-lg-6 d-flex flex-column gap-3">
                <!-- Box 1: Biểu đồ Doanh thu & Công nợ theo Kỳ -->
                <div class="invoice-section mb-0 p-3 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                        <div class="fw-bold text-dark fs-6">
                            <i class="fa-solid fa-chart-column me-2 text-teal-main"></i> Biểu đồ Doanh thu & Công nợ theo Kỳ
                        </div>
                        <span class="badge bg-light text-secondary border">Đơn vị: VNĐ <i class="fa-solid fa-angle-down ms-1"></i></span>
                    </div>
                    <div style="height: 240px; position: relative;">
                        <canvas id="revenueChartCanvas"></canvas>
                    </div>
                </div>

                <!-- Box 2: Cơ cấu Nguồn thu (Doughnut chart lớn nằm ngang cân đối) -->
                <div class="invoice-section mb-0 p-3 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                        <div class="fw-bold text-dark fs-6">
                            <i class="fa-solid fa-chart-pie me-2 text-teal-main"></i> Cơ cấu Nguồn thu
                        </div>
                        <button type="button" class="btn btn-sm btn-light border rounded-pill fw-bold text-teal-main py-1 px-3" style="font-size: 0.8rem;" data-bs-toggle="modal" data-bs-target="#ovRoomsModal">
                            <?= count($roomsList ?? []) ?> Phòng <i class="fa-solid fa-arrow-right ms-1"></i>
                        </button>
                    </div>
                    <div style="height: 230px; position: relative;">
                        <canvas id="breakdownPieChartCanvas"></canvas>
                    </div>
                </div>
            </div>

            <!-- CỘT PHẢI (6/12): Khung Nhật Ký Hoạt Động (Dài bằng toàn bộ 2 box bên trái, có timeline đẹp đúng ảnh mẫu) -->
            <div class="col-12 col-lg-6">
                <div class="invoice-section h-100 mb-0 d-flex flex-column p-0 overflow-hidden shadow-sm" style="min-height: 540px;">
                    <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #e0f2fe 0%, #ccfbf1 100%);">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" style="width: 36px; height: 36px; background: var(--teal-main);">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </div>
                            <div class="fw-bold text-dark fs-5">Nhật ký hoạt động</div>
                        </div>
                        
                    </div>
                    <div id="activityLogList" class="p-3 flex-grow-1 d-flex flex-column gap-3" style="background: #f8fafc; overflow-y: auto; max-height: 460px;">
                        <!-- Danh sách nhật ký thao tác dạng card trắng border cong thanh thoát -->
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- 0. MODAL TÓM TẮT HÓA ĐƠN & CÔNG NỢ (Khi bấm vào thẻ Thực thu / Công nợ) -->
    <div class="modal fade" id="ovInvoicesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light rounded-top-4">
                    <h5 class="modal-title fw-bold text-teal-main"><i class="fa-solid fa-file-invoice-dollar me-2"></i> Tóm tắt Hóa đơn & Công nợ - <?= htmlspecialchars($current_bld['name']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <div class="p-3 rounded-3 bg-primary-subtle border border-primary-subtle">
                                <div class="small fw-bold text-primary">ĐÃ THU (<?= $paid_count ?> hóa đơn)</div>
                                <div class="fs-5 fw-bold text-primary"><?= number_format($total_revenue, 0, ',', '.') ?> đ</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded-3 bg-warning-subtle border border-warning-subtle">
                                <div class="small fw-bold text-danger">CHƯA THU NỢ (<?= $unpaid_count ?> hóa đơn)</div>
                                <div class="fs-5 fw-bold text-danger"><?= number_format($total_debt, 0, ',', '.') ?> đ</div>
                            </div>
                        </div>
                    </div>
                    <table class="table table-hover align-middle border mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mã HĐ</th>
                                <th>Phòng</th>
                                <th>Khách thuê</th>
                                <th>Kỳ</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($debtList, 0, 8) as $inv): ?>
                                <tr>
                                    <td class="fw-bold text-teal-main"><?= htmlspecialchars($inv['invoice_code']) ?></td>
                                    <td><span class="badge bg-dark"><?= htmlspecialchars($inv['room_number']) ?></span></td>
                                    <td><?= htmlspecialchars($inv['tenant']) ?></td>
                                    <td><?= htmlspecialchars($inv['month_year']) ?></td>
                                    <td class="fw-bold text-danger"><?= number_format($inv['total_amount'], 0, ',', '.') ?> đ</td>
                                    <td>
                                        <?= $inv['status'] === 'paid'
                                            ? '<span class="badge bg-success rounded-pill">Đã thu</span>'
                                            : '<span class="badge bg-danger rounded-pill">Chưa thu</span>' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                    <a href="index.php?route=invoices" class="btn btn-teal-custom px-4">Mở trang Quản lý Hóa đơn</a>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- CÁC MODAL TƯƠNG TÁC TRÊN TRANG TỔNG QUAN (OVERVIEW)       -->
    <!-- ========================================================= -->

    <!-- 1. MODAL LẬP HÓA ĐƠN MỚI VÀO CSDL -->
    <div class="modal fade" id="createInvoiceModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light rounded-top-4">
                    <h5 class="modal-title fw-bold text-teal-main"><i class="fa-solid fa-file-circle-plus me-2"></i> Lập Hóa Đơn Mới (Lưu vào SQL)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="index.php?route=overview&building_id=<?= urlencode($building_id) ?>" id="createInvForm">
                        <input type="hidden" name="action" value="create_invoice">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label class="form-label fw-bold small text-muted">Chọn Phòng trọ</label>
                                <select name="room_id" id="invRoomSelect" class="form-select" required onchange="recalcPreview()">
                                    <?php foreach ($roomsList as $rm): ?>
                                        <option value="<?= (int)$rm['id'] ?>" data-price="<?= (float)$rm['price'] ?>">
                                            <?= htmlspecialchars($rm['room_number']) ?> - <?= number_format($rm['price'], 0, ',', '.') ?> đ (<?= !empty($rm['tenant_name']) ? htmlspecialchars($rm['tenant_name']) : 'Trống' ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-bold small text-muted">Kỳ tháng (MM-YYYY)</label>
                                <input type="text" name="month_year" class="form-control" value="11-2023" placeholder="VD: 11-2023" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">Chỉ số Điện cũ (kWh)</label>
                                <input type="number" name="electric_old" id="invElecOld" class="form-control" value="100" min="0" required oninput="recalcPreview()">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">Chỉ số Điện mới (kWh)</label>
                                <input type="number" name="electric_new" id="invElecNew" class="form-control" value="160" min="0" required oninput="recalcPreview()">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">Chỉ số Nước cũ (m³)</label>
                                <input type="number" name="water_old" id="invWaterOld" class="form-control" value="15" min="0" required oninput="recalcPreview()">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">Chỉ số Nước mới (m³)</label>
                                <input type="number" name="water_new" id="invWaterNew" class="form-control" value="21" min="0" required oninput="recalcPreview()">
                            </div>
                        </div>

                        <div class="bg-light rounded-4 p-3 mt-3 border">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>Đơn giá Điện / Nước (Bảng `services`):</span>
                                <strong><?= number_format($servicesMap['elec_price'] ?? 3500, 0, ',', '.') ?> đ/kWh • <?= number_format($servicesMap['water_price'] ?? 20000, 0, ',', '.') ?> đ/m³</strong>
                            </div>
                            <div class="d-flex justify-content-between small mb-1">
                                <span>Phí Rác + Internet cố định:</span>
                                <strong><?= number_format($servicesMap['other_total'] ?? 150000, 0, ',', '.') ?> đ/tháng</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                                <span class="fw-bold text-dark">Tạm tính Tổng Hóa Đơn:</span>
                                <span class="fw-bold fs-5 text-danger" id="previewTotalTxt">0 đ</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-teal-custom px-4"><i class="fa-solid fa-floppy-disk me-2"></i> Lưu Hóa Đơn</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. MODAL XEM CHI TIẾT & IN HÓA ĐƠN -->
    <div class="modal fade" id="invoiceDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light rounded-top-4">
                    <h5 class="modal-title fw-bold text-teal-main"><i class="fa-solid fa-receipt me-2"></i> Chi tiết Phiếu Báo Tiền Trọ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="printableInvoiceArea">
                    <div class="text-center mb-3 border-bottom pb-3">
                        <h5 class="fw-bold text-teal-main mb-1">HỆ THỐNG NHÀ TRỌ STAYMATE</h5>
                        <div class="small text-muted"><?= htmlspecialchars($current_bld['name']) ?></div>
                        <div class="badge bg-dark mt-2 px-3 py-1" id="detCode">HD-00</div>
                    </div>
                    <div class="row g-2 small mb-3">
                        <div class="col-6">Phòng: <strong id="detRoom">P101</strong></div>
                        <div class="col-6 text-end">Kỳ thanh toán: <strong id="detMonth">10-2023</strong></div>
                        <div class="col-6">Khách thuê: <strong id="detTenant">Nguyễn Văn A</strong></div>
                        <div class="col-6 text-end">Trạng thái: <strong id="detStatus">Chưa thu</strong></div>
                    </div>
                    <table class="table table-bordered small align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Khoản mục</th>
                                <th>Chi tiết chỉ số</th>
                                <th class="text-end">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1. Tiền thuê phòng</td>
                                <td>1 tháng</td>
                                <td class="text-end fw-bold" id="detRoomPrice">0 đ</td>
                            </tr>
                            <tr>
                                <td>2. Tiền Điện</td>
                                <td id="detElecDesc">0 kWh</td>
                                <td class="text-end fw-bold" id="detElecCost">0 đ</td>
                            </tr>
                            <tr>
                                <td>3. Tiền Nước</td>
                                <td id="detWaterDesc">0 m³</td>
                                <td class="text-end fw-bold" id="detWaterCost">0 đ</td>
                            </tr>
                            <tr>
                                <td>4. Rác & Internet</td>
                                <td>Trọn gói tháng</td>
                                <td class="text-end fw-bold" id="detServiceFee">0 đ</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="table-warning">
                                <th colspan="2" class="text-uppercase">Tổng cộng thanh toán</th>
                                <th class="text-end text-danger fs-6" id="detTotal">0 đ</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-teal-custom px-4" onclick="window.print()">
                        <i class="fa-solid fa-print me-2"></i> In Hóa Đơn
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. MODAL DANH SÁCH PHÒNG (Khi bấm thẻ Tỷ lệ lấp đầy) -->
    <div class="modal fade" id="ovRoomsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light rounded-top-4">
                    <h5 class="modal-title fw-bold text-success"><i class="fa-solid fa-bed me-2"></i> Danh sách Phòng - <?= htmlspecialchars($current_bld['name']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <table class="table table-hover align-middle border">
                        <thead class="table-success">
                            <tr>
                                <th>Phòng</th>
                                <th>Giá thuê</th>
                                <th>Mô tả</th>
                                <th>Khách đang thuê</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($roomsList as $rm): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($rm['room_number']) ?></td>
                                    <td class="fw-bold text-success"><?= number_format($rm['price'], 0, ',', '.') ?> đ</td>
                                    <td><?= htmlspecialchars($rm['description'] ?? '') ?></td>
                                    <td><?= !empty($rm['tenant_name']) ? htmlspecialchars($rm['tenant_name']) : '<span class="text-muted">Trống</span>' ?></td>
                                    <td>
                                        <?= $rm['status'] === 'rented'
                                            ? '<span class="badge bg-success rounded-pill">Đang thuê</span>'
                                            : '<span class="badge bg-warning text-dark rounded-pill">Phòng trống</span>' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                    <a href="index.php?route=rooms" class="btn btn-teal-custom px-4">Mở trang Quản lý Phòng</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. MODAL DANH SÁCH HỢP ĐỒNG (Khi bấm thẻ Khách & Hợp đồng) -->
    <div class="modal fade" id="ovContractsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light rounded-top-4">
                    <h5 class="modal-title fw-bold" style="color:#7e22ce;"><i class="fa-solid fa-file-signature me-2"></i> Hợp đồng thuê phòng - <?= htmlspecialchars($current_bld['name']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <table class="table table-hover align-middle border">
                        <thead class="table-light">
                            <tr>
                                <th>Mã HĐ</th>
                                <th>Phòng</th>
                                <th>Khách thuê</th>
                                <th>Thời hạn</th>
                                <th>Tiền cọc</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($contractsList as $con): ?>
                                <tr>
                                    <td class="fw-bold">#HD-0<?= (int)$con['id'] ?></td>
                                    <td><span class="badge bg-dark"><?= htmlspecialchars($con['room_number']) ?></span></td>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($con['tenant_name']) ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($con['tenant_phone']) ?></div>
                                    </td>
                                    <td class="small"><?= htmlspecialchars($con['start_date']) ?> ➔ <?= htmlspecialchars($con['end_date']) ?></td>
                                    <td class="fw-bold"><?= number_format($con['deposit_amount'], 0, ',', '.') ?> đ</td>
                                    <td>
                                        <?= $con['status'] === 'active'
                                            ? '<span class="badge bg-success rounded-pill">Đang hiệu lực</span>'
                                            : '<span class="badge bg-secondary rounded-pill">Đã thanh lý</span>' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                    <a href="index.php?route=contracts" class="btn btn-teal-custom px-4">Mở trang Quản lý Hợp đồng</a>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer-custom container-fluid px-4 px-xl-5 d-flex justify-content-between align-items-center">
        <div><i class="fa-solid fa-building me-1" style="color:var(--teal-main);"></i> © 2026 StayMate - Quản lý Nhà trọ Chuyên nghiệp</div>
        <div class="d-flex gap-4">
            <span><i class="fa-solid fa-shield-halved me-1" style="color:var(--teal-main);"></i> WAMP MVC Architecture</span>
            <span><i class="fa-solid fa-bolt me-1 text-warning"></i> Tự động hóa Điện, Nước & Hóa đơn</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const fmtVND = (n) => new Intl.NumberFormat('vi-VN').format(Math.round(n || 0)) + ' đ';

        // Biểu đồ Doanh thu & Công nợ 6 tháng + 4 Thẻ Biểu đồ Tổng quan từ SQL
        (function initCharts() {
            if (typeof Chart === 'undefined') return;
            const labels = <?= json_encode($chartData['labels'] ?? [], JSON_UNESCAPED_UNICODE) ?>;
            const shortLabels = labels.map(l => l.replace('Kỳ ', '').replace('-2023', ''));
            const revenues = <?= json_encode($chartData['revenues'] ?? []) ?>;
            const debts = <?= json_encode($chartData['debts'] ?? []) ?>;

            // 1. Thẻ 1: Biểu đồ Tròn Tỷ lệ lấp đầy (Doughnut)
            const ctxOcc = document.getElementById('chartCardOccupancy');
            if (ctxOcc) {
                new Chart(ctxOcc, {
                    type: 'doughnut',
                    data: {
                        labels: ['Đang thuê', 'Phòng trống'],
                        datasets: [{
                            data: [<?= (int)$rented_rooms ?>, <?= (int)$empty_rooms ?>],
                            backgroundColor: ['#16a34a', '#facc15'],
                            borderColor: '#ffffff',
                            borderWidth: 3,
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '58%',
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom',
                                labels: { boxWidth: 10, font: { size: 10, weight: 'bold' } }
                            }
                        }
                    }
                });
            }

            // 2. Thẻ 2: Biểu đồ Cột dọc Thực thu theo kỳ (Vertical Bar)
            const ctxRevMini = document.getElementById('chartCardRevenue');
            if (ctxRevMini) {
                new Chart(ctxRevMini, {
                    type: 'bar',
                    data: {
                        labels: shortLabels.length ? shortLabels : ['T05', 'T06', 'T07', 'T08', 'T09', 'T10'],
                        datasets: [{
                            label: 'Thực thu',
                            data: revenues.length ? revenues : [0],
                            backgroundColor: '#2563eb',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: { font: { size: 10, weight: 'bold' } } },
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(255,255,255,0.5)' },
                                ticks: {
                                    font: { size: 9 },
                                    callback: (v) => (v / 1000000) + 'tr'
                                }
                            }
                        }
                    }
                });
            }

            // 3. Thẻ 3: Biểu đồ Cột dọc Công nợ chưa thu theo kỳ (Vertical Bar)
            const ctxDebtMini = document.getElementById('chartCardDebt');
            if (ctxDebtMini) {
                new Chart(ctxDebtMini, {
                    type: 'bar',
                    data: {
                        labels: shortLabels.length ? shortLabels : ['T05', 'T06', 'T07', 'T08', 'T09', 'T10'],
                        datasets: [{
                            label: 'Công nợ',
                            data: debts.length ? debts : [0],
                            backgroundColor: '#dc2626',
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: { font: { size: 10, weight: 'bold' } } },
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(255,255,255,0.5)' },
                                ticks: {
                                    font: { size: 9 },
                                    callback: (v) => (v / 1000000) + 'tr'
                                }
                            }
                        }
                    }
                });
            }

            // 4. Thẻ 4: Biểu đồ Tròn Khách & Hợp đồng (Pie / Doughnut)
            const ctxCon = document.getElementById('chartCardContracts');
            if (ctxCon) {
                const activeCon = <?= (int)$active_contracts ?>;
                const endedCon = Math.max(0, <?= (int)($total_contracts - $active_contracts) ?>);
                new Chart(ctxCon, {
                    type: 'pie',
                    data: {
                        labels: ['Đang hiệu lực', 'Đã thanh lý'],
                        datasets: [{
                            data: (activeCon === 0 && endedCon === 0) ? [1, 0] : [activeCon, endedCon],
                            backgroundColor: ['#9333ea', '#cbd5e1'],
                            borderColor: '#ffffff',
                            borderWidth: 3,
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom',
                                labels: { boxWidth: 10, font: { size: 10, weight: 'bold' } }
                            }
                        }
                    }
                });
            }

            // 5. Biểu đồ lớn: Doanh thu & Công nợ 6 tháng
            const ctx = document.getElementById('revenueChartCanvas');
            if (!ctx) return;

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Thực thu (Đã đóng)',
                            data: revenues,
                            backgroundColor: '#308f86',
                            borderRadius: 8
                        },
                        {
                            label: 'Công nợ (Chưa đóng)',
                            data: debts,
                            backgroundColor: '#f59e0b',
                            borderRadius: 8
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            suggestedMin: 0,
                            suggestedMax: 10000000,
                            ticks: {
                                callback: function(val) {
                                    if (val === 0) return '0 tr';
                                    if (val >= 1000000) return (val / 1000000) + ' tr';
                                    return val.toLocaleString('vi-VN') + ' đ';
                                }
                            }
                        }
                    } } });

            // 6. Biểu đồ Tròn: Cơ cấu Doanh thu (Tiền phòng, Điện, Nước, Dịch vụ)
            const ctxBreakdown = document.getElementById('breakdownPieChartCanvas');
            if (ctxBreakdown) {
                <?php
                $sumRoom = 0; $sumElec = 0; $sumWater = 0; $sumService = 0;
                foreach ($debtList as $inv) {
                    $sumRoom += (float)($inv['room_price'] ?? 0);
                    $sumElec += (float)($inv['electricity_cost'] ?? 0);
                    $sumWater += (float)($inv['water_cost'] ?? 0);
                    $sumService += (float)($inv['service_fee'] ?? 0);
                }
                ?>
                new Chart(ctxBreakdown, {
                    type: 'doughnut',
                    data: {
                        labels: ['Tiền phòng', 'Phí dịch vụ'],
                        datasets: [{
                            data: [<?= $sumRoom ?>, <?= $sumService ?>],
                            backgroundColor: ['#308f86', '#8b5cf6'],
                            borderColor: '#ffffff',
                            borderWidth: 3,
                            hoverOffset: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '55%',
                        plugins: {
                            legend: { position: 'bottom' },
                            tooltip: {
                                callbacks: {
                                    label: function(c) {
                                        return c.label + ': ' + Number(c.raw).toLocaleString('vi-VN') + ' đ';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        })();

        // Tính nhẩm tổng tiền trong Modal Lập Hóa Đơn
        function recalcPreview() {
            const sel = document.getElementById('invRoomSelect');
            if (!sel || !sel.options.length) return;
            const roomPrice = parseFloat(sel.options[sel.selectedIndex]?.dataset?.price || 0);
            const eo = parseInt(document.getElementById('invElecOld').value || 0, 10);
            const en = parseInt(document.getElementById('invElecNew').value || 0, 10);
            const wo = parseInt(document.getElementById('invWaterOld').value || 0, 10);
            const wn = parseInt(document.getElementById('invWaterNew').value || 0, 10);

            const elecUsed = Math.max(0, en - eo);
            const waterUsed = Math.max(0, wn - wo);
            const elecPrice = <?= (float)($servicesMap['elec_price'] ?? 3500) ?>;
            const waterPrice = <?= (float)($servicesMap['water_price'] ?? 20000) ?>;
            const otherTotal = <?= (float)($servicesMap['other_total'] ?? 150000) ?>;

            const total = roomPrice + (elecUsed * elecPrice) + (waterUsed * waterPrice) + otherTotal;
            const el = document.getElementById('previewTotalTxt');
            if (el) el.textContent = fmtVND(total);
        }
        recalcPreview();

        // Xem chi tiết phiếu báo tiền trọ
        function openInvoiceDetailModal(inv) {
            document.getElementById('detCode').textContent = inv.invoice_code || ('#INV-' + inv.id);
            document.getElementById('detRoom').textContent = inv.room_number || '';
            document.getElementById('detMonth').textContent = inv.month_year || '';
            document.getElementById('detTenant').textContent = inv.tenant || '';
            document.getElementById('detStatus').textContent = inv.status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán';
            document.getElementById('detRoomPrice').textContent = fmtVND(inv.room_price);
            document.getElementById('detElecDesc').textContent = (inv.electric_old ?? 0) + ' ➔ ' + (inv.electric_new ?? 0) + ' (' + (inv.electric_used ?? 0) + ' kWh)';
            document.getElementById('detElecCost').textContent = fmtVND(inv.electric);
            document.getElementById('detWaterDesc').textContent = (inv.water_old ?? 0) + ' ➔ ' + (inv.water_new ?? 0) + ' (' + (inv.water_used ?? 0) + ' m³)';
            document.getElementById('detWaterCost').textContent = fmtVND(inv.water);
            document.getElementById('detServiceFee').textContent = fmtVND(inv.service_fee);
            document.getElementById('detTotal').textContent = fmtVND(inv.total_amount);

            const modal = new bootstrap.Modal(document.getElementById('invoiceDetailModal'));
            modal.show();
        }
    </script>

    <script>
        // Xóa sạch bộ nhớ tạm trình duyệt cũ lưu những hành động xem trang trước đây
        try { localStorage.removeItem("staymate_activity_logs"); } catch(e) {}

        const SERVER_ACTIVITIES = <?= json_encode($notifications ?? [], JSON_UNESCAPED_UNICODE) ?>;

        function renderActivityLogs() {
            const container = document.getElementById("activityLogList");
            
            if (!container) return;

            const list = Array.isArray(SERVER_ACTIVITIES) ? SERVER_ACTIVITIES : [];

            

            if (list.length === 0) {
                container.innerHTML = '<div class="text-center text-muted py-5 small"><i class="fa-regular fa-clipboard me-1 fs-4 d-block mb-2 text-secondary"></i>Chưa có thay đổi dữ liệu nào.<br><span class="text-secondary" style="font-size:0.75rem;">Khi bạn thêm/sửa/xóa tòa nhà hoặc lập/thu tiền hóa đơn, hoạt động sẽ được ghi nhận tại đây kèm tên tài khoản thực hiện.</span></div>';
                return;
            }

            container.innerHTML = list.map((item, idx) => {
                const iconClass = item.type === "success"
                    ? "fa-circle-check text-success"
                    : (item.type === "warning" ? "fa-triangle-exclamation text-warning" : "fa-bolt text-teal-main");
                
                const userText = item.user ? `${item.user}` : "Quản trị viên";
                const userAcc = item.username ? `(${item.username})` : "";
                
                return `
                    <div class="p-3 rounded-4 border bg-white shadow-sm d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: #ecfdf5; border: 1px solid #a7f3d0;">
                            <i class="fa-solid ${iconClass} fs-5"></i>
                        </div>
                        <div class="flex-grow-1" style="min-width: 0;">
                            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                <span class="badge bg-light text-dark border px-2 py-1" style="font-size: 0.76rem;">
                                    <i class="fa-solid fa-user me-1 text-teal-main"></i><strong>${userText}</strong> <span class="text-muted">${userAcc}</span>
                                </span>
                                <span class="text-muted small"><i class="fa-regular fa-clock me-1"></i>${item.time}</span>
                            </div>
                            <div class="fw-bold text-dark" style="font-size: 0.92rem; line-height: 1.35;">${item.title}</div>
                        </div>
                    </div>
                `;
            }).join("");
        }

        document.addEventListener("DOMContentLoaded", function() {
            renderActivityLogs();
        });
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
                        <button type="submit" class="btn btn-teal-custom px-4"><i class="fa-solid fa-check me-1"></i> Lưu thay đổi</button>
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
    
<?php require_once dirname(__DIR__) . '/components/toast.php'; ?>
</body>
</html>
