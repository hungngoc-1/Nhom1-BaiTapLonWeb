<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
$allowedBldIds = $allowedBldIds ?? ($this->reportModel->getAllowedBuildingIds());
$userRole = $_SESSION['role'] ?? 'admin';
$userFullName = $_SESSION['full_name'] ?? 'Quản trị viên Hệ thống';
$userRole     = $_SESSION['role'] ?? 'admin';
$userName     = $_SESSION['username'] ?? 'admin01';
$userEmail    = $_SESSION['email'] ?? 'admin@nhatro.com';
$userPhone    = $_SESSION['phone'] ?? '0901234567';

$totalBuildingsCount = count($buildings ?? []);
$displayedBuildings = $buildings ?? [];
if (!empty($filterBuilding) && $filterBuilding !== 'all' && isset($buildings[$filterBuilding])) {
    $displayedBuildings = [$filterBuilding => $buildings[$filterBuilding]];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayMate - Quản Lý Tòa Nhà & Nhà Trọ</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --teal-main: #308f86;
            --teal-hover: #25756d;
            --bg-page: #f8fafc; 
            --radius-xl: 24px;
            --radius-lg: 16px;
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
                url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23e6f4f1" fill-opacity="1" d="M0,160L48,170.7C96,181,192,203,288,197.3C384,192,480,160,576,149.3C672,139,768,149,864,170.7C960,192,1056,224,1152,213.3C1248,203,1344,149,1392,122.7L1440,96L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0C192,0,96,0,48,0L0,0Z"></path></svg>'),
                url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><path fill="%234ade80" opacity="0.15" d="M30 100 Q 50 20 100 50 T 170 100 Q 150 180 100 150 T 30 100 Z"/></svg>'),
                url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><path fill="%234ade80" opacity="0.15" d="M30 100 Q 50 20 100 50 T 170 100 Q 150 180 100 150 T 30 100 Z"/></svg>');
            background-position: top center, -50px 100px, calc(100% + 50px) calc(100% - 100px);
            background-repeat: no-repeat;
            background-size: 100% auto, 250px, 250px;
            background-attachment: fixed;
        }

        .text-teal-main { color: var(--teal-main) !important; }
        .cursor-pointer { cursor: pointer; }

        /* Navbar */
        .navbar-custom { background: transparent; padding: 15px 20px; }
        .brand-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .brand-logo i { color: var(--teal-main); font-size: 2.2rem; }
        .brand-text { color: var(--teal-main); font-weight: 900; font-size: 1.4rem; line-height: 1; letter-spacing: -0.5px;}
        .brand-sub { color: #64748b; font-size: 0.75rem; font-weight: 600; }

        /* KHUNG WELCOME BANNER NÂNG CẤP */
        .welcome-banner {
            background-color: #ffffff;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 200" preserveAspectRatio="none"><path fill="%23f0fdfa" d="M0,100 Q150,20 300,100 T600,100 T1000,50 L1000,0 L0,0 Z"/></svg>');
            background-size: cover;
            background-repeat: no-repeat;
            border-radius: 30px;
            padding: 24px 32px;
            box-shadow: 0 12px 30px rgba(48, 143, 134, 0.12);
            border: 3px solid #ffffff;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .deco-leaf-left {
            position: absolute; left: -10px; top: -20px; width: 100px; height: 150px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 150"><path d="M-10,30 Q40,50 60,160" stroke="%2316a34a" stroke-width="2" fill="none"/><path d="M10,40 Q30,20 50,35 Q30,50 10,40" fill="%234ade80"/><path d="M25,70 Q55,50 65,75 Q45,90 25,70" fill="%2322c55e"/><path d="M40,110 Q65,90 75,115 Q50,130 40,110" fill="%2315803d"/></svg>');
            background-size: contain; background-repeat: no-repeat;
            z-index: 1; opacity: 0.9; pointer-events: none;
        }

        .deco-house-right {
            position: absolute; right: 20px; bottom: 0; width: 220px; height: 130px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 150"><circle cx="160" cy="110" r="30" fill="%23a7dcd6" opacity="0.4"/><rect x="50" y="60" width="80" height="70" fill="%23ffffff"/><rect x="130" y="70" width="30" height="60" fill="%23f1f5f9"/><polygon points="40,60 90,20 140,60" fill="%23308f86"/><polygon points="90,20 140,60 170,60 120,20" fill="%2375c2b9"/><rect x="70" y="80" width="12" height="15" fill="%23a7dcd6" rx="2"/><rect x="95" y="80" width="12" height="15" fill="%23a7dcd6" rx="2"/><rect x="70" y="105" width="12" height="15" fill="%23a7dcd6" rx="2"/><rect x="95" y="105" width="12" height="15" fill="%23a7dcd6" rx="2"/><rect x="140" y="90" width="10" height="15" fill="%23a7dcd6" rx="2"/><circle cx="45" cy="120" r="15" fill="%2322c55e"/><circle cx="65" cy="125" r="20" fill="%2316a34a"/><circle cx="150" cy="125" r="18" fill="%2315803d"/><circle cx="175" cy="120" r="12" fill="%2322c55e"/></svg>');
            background-size: contain; background-repeat: no-repeat; background-position: bottom right;
            z-index: 1; pointer-events: none;
        }

        .welcome-icon-wrapper {
            width: 58px; height: 58px;
            background-color: var(--teal-main);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1.6rem;
            box-shadow: 0 8px 16px rgba(48, 143, 134, 0.3);
            position: relative; z-index: 2; margin-left: 30px;
        }
        
        .sparkle { position: absolute; background-color: #fde047; border-radius: 4px; z-index: 2; }
        .sp-1 { width: 4px; height: 12px; top: -12px; right: 10px; transform: rotate(15deg); }
        .sp-2 { width: 12px; height: 4px; top: 10px; right: -14px; transform: rotate(15deg); }
        .sp-3 { width: 4px; height: 10px; bottom: 0px; right: -8px; transform: rotate(-30deg); }

        .welcome-title { font-weight: 900; color: var(--teal-main); font-size: 1.55rem; letter-spacing: -0.5px;}
        .date-badge { background: white; border-radius: 20px; padding: 8px 18px; font-weight: 700; color: #475569; font-size: 0.85rem; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border: 2px solid #e2e8f0; position: relative; z-index: 2; margin-right: 180px;}

        /* Overview Cards */
        .overview-card {
            border-radius: var(--radius-xl); padding: 24px; position: relative; overflow: hidden;
            box-shadow: 0 12px 25px rgba(0,0,0,0.1); 
            border: 3px solid #ffffff;
            height: 100%; transition: transform 0.3s ease, box-shadow 0.3s ease;
            backdrop-filter: blur(5px);
            cursor: pointer;
        }
        .overview-card:hover { transform: translateY(-5px); box-shadow: 0 18px 35px rgba(0,0,0,0.15); }
        
        .card-green { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); }
        .card-blue { background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); }
        .card-yellow { background: linear-gradient(135deg, #fef9c3 0%, #fef08a 100%); }
        .card-purple { background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%); }
        
        .card-bg-pattern {
            position: absolute; right: -10px; bottom: -10px; width: 100px; height: 100px; opacity: 0.15;
            background-size: contain; background-repeat: no-repeat; background-position: bottom right;
        }
        .pattern-green { background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><polygon points="10,50 50,10 90,50" fill="%2316a34a"/><rect x="20" y="50" width="60" height="40" fill="%2316a34a"/></svg>'); }
        .pattern-blue { background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="30" cy="50" r="20" fill="%232563eb"/><circle cx="50" cy="50" r="20" fill="%232563eb"/><circle cx="70" cy="50" r="20" fill="%232563eb"/></svg>'); }
        .pattern-yellow { background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect x="20" y="20" width="60" height="70" fill="%23ca8a04" rx="4"/><line x1="35" y1="40" x2="65" y2="40" stroke="%23ffffff" stroke-width="4"/><line x1="35" y1="60" x2="65" y2="60" stroke="%23ffffff" stroke-width="4"/></svg>'); }
        .pattern-purple { background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="30" r="15" fill="%237e22ce"/><path d="M20 90 Q 50 60 80 90" fill="%237e22ce"/></svg>'); }
        
        .overview-title { font-size: 0.75rem; font-weight: 900; margin-bottom: 8px; display: flex; align-items: center; gap: 6px; text-transform: uppercase;}
        .overview-val { font-size: 2rem; font-weight: 900; line-height: 1; letter-spacing: -1px;}
        .overview-icon-bg {
            position: absolute; right: 20px; top: 50%; transform: translateY(-50%);
            width: 50px; height: 50px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1.3rem;
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
            z-index: 2;
        }
        .icon-green { background-color: #22c55e; }
        .icon-blue { background-color: #3b82f6; }
        .icon-yellow { background-color: #eab308; }
        .icon-purple { background-color: #a855f7; }

        /* Toolbar */
        .toolbar-select { background: white; border-radius: 20px; padding: 10px 24px; font-weight: 800; color: var(--teal-main); display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 6px 15px rgba(0,0,0,0.06); border: 2px solid #e2e8f0; cursor: pointer; text-decoration: none; }
        .toolbar-select:hover { border-color: var(--teal-main); background: #f0fdfa; }
        .btn-outline-custom { background: white; border-radius: 20px; font-weight: 700; color: #475569; padding: 10px 22px; border: 2px solid #e2e8f0; box-shadow: 0 6px 15px rgba(0,0,0,0.06); text-decoration: none; display: inline-flex; align-items: center; transition: 0.2s; }
        .btn-outline-custom:hover { border-color: var(--teal-main); color: var(--teal-main); background: #f0fdfa; }
        .btn-teal-custom { background: var(--teal-main); border-radius: 20px; font-weight: 700; color: white; padding: 10px 24px; border: none; box-shadow: 0 8px 20px rgba(48, 143, 134, 0.4); transition: 0.2s; }
        .btn-teal-custom:hover { background: var(--teal-hover); color: white; transform: translateY(-2px); }

        /* Building Cards */
        .building-card {
            background: rgba(255, 255, 255, 0.98); border-radius: 30px; padding: 24px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.08); 
            border: 2px solid #cbd5e1;
            position: relative; transition: all 0.3s ease; height: 100%;
            display: flex; flex-direction: column; backdrop-filter: blur(5px);
        }
        .building-card:hover { transform: translateY(-8px); box-shadow: 0 25px 50px rgba(48, 143, 134, 0.15); border-color: var(--teal-main);}
        
        .badge-bld { background: #dcfce7; color: #166534; font-weight: 900; font-size: 0.75rem; padding: 6px 14px; border-radius: 12px; display: inline-block; margin-bottom: 12px; border: 1px solid #86efac;}
        .bld-title { font-weight: 900; font-size: 1.3rem; color: #0f172a; margin-bottom: 6px; letter-spacing: -0.5px;}
        .bld-address { font-size: 0.8rem; color: #64748b; font-weight: 700; margin-bottom: 16px; }
        
        .manager-info { font-size: 0.8rem; display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        
        /* Stats inside Building Card */
        .stat-group { display: flex; gap: 10px; margin-bottom: 16px; }
        .stat-item {
            flex: 1; border-radius: 16px; padding: 14px 8px; text-align: center;
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px;
            border: 2px solid;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            cursor: pointer; transition: 0.2s;
        }
        .stat-item:hover { transform: scale(1.03); }
        .stat-total { background-color: #e0f2fe; border-color: #bae6fd;}
        .stat-total .stat-val, .stat-total i { color: #0284c7; }
        
        .stat-rented { background-color: #dcfce7; border-color: #bbf7d0;}
        .stat-rented .stat-val, .stat-rented i { color: #16a34a; }
        
        .stat-empty { background-color: #fef9c3; border-color: #fde047;}
        .stat-empty .stat-val, .stat-empty i { color: #d97706; }
        
        .stat-val { font-size: 1.6rem; font-weight: 900; line-height: 1; }
        .stat-label { font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: #475569;}

        .bld-finance-bar {
            background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px;
            padding: 10px 14px; margin-bottom: 18px;
            display: flex; justify-content: space-between; align-items: center; font-size: 0.78rem; font-weight: 700;
        }

        /* Building Card Buttons */
        .btn-bld-main {
            background: var(--teal-main); color: white; border-radius: 16px;
            font-weight: 800; padding: 14px; flex: 1; text-align: center; text-decoration: none;
            box-shadow: 0 8px 20px rgba(48, 143, 134, 0.35); transition: 0.2s; border: 2px solid var(--teal-hover);
        }
        .btn-bld-main:hover { background: var(--teal-hover); color: white; }
        .btn-bld-folder {
            background: white; color: var(--teal-main); border: 2px solid var(--teal-main);
            border-radius: 16px; width: 54px; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 6px 15px rgba(0,0,0,0.08); transition: 0.2s; cursor: pointer;
        }
        .btn-bld-folder:hover { background: var(--teal-main); color: white; }

        .house-img { position: absolute; right: 20px; top: 10px; width: 120px; z-index: 1; pointer-events: none; }
        .house-shadow { position: absolute; right: 25px; top: 110px; width: 110px; height: 15px; background: rgba(0,0,0,0.1); border-radius: 50%; filter: blur(4px); z-index: 0; pointer-events: none; }
        
        /* Footer */
        .footer-custom { background: rgba(255,255,255,0.8); padding: 15px 0; color: #64748b; font-size: 0.8rem; font-weight: 700; margin-top: auto; border-top: 1px solid #e2e8f0;}
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid px-4 px-xl-5 d-flex justify-content-between align-items-center">
            <a class="brand-logo" href="index.php?route=dashboard">
                <i class="fa-solid fa-house-chimney text-teal-main"></i>
                <div>
                    <div class="brand-text">StayMate</div>
                    <div class="brand-sub">Quản Lý Nhà Trọ</div>
                </div>
            </a>
            
            <div class="d-flex align-items-center gap-4">
                <!-- Dropdown Tài khoản -->
                <div class="dropdown">
                    <div class="d-flex align-items-center gap-2 cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width:40px; height:40px; background-color: var(--teal-main); border: 2px solid #ffffff;">AS</div>
                        <div class="text-start d-none d-sm-block lh-sm">
                            <div class="fw-bold text-dark" style="font-size: 0.85rem;"><?= htmlspecialchars($userFullName) ?> <i class="fa-solid fa-angle-down ms-1 text-muted fs-6"></i></div>
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

    <!-- Main Content -->
    <div class="container-fluid px-4 px-xl-5 py-3 position-relative z-1">

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
        
        <!-- Khung Welcome Banner Cực Đẹp -->
        <div class="welcome-banner mb-4">
            <div class="deco-leaf-left"></div>
            <div class="deco-house-right"></div>
            
            <div class="d-flex align-items-center gap-4 position-relative z-2">
                <div class="welcome-icon-wrapper">
                    <i class="fa-solid fa-hand-sparkles"></i>
                    <span class="sparkle sp-1"></span>
                    <span class="sparkle sp-2"></span>
                    <span class="sparkle sp-3"></span>
                </div>
                <div>
                    <h3 class="welcome-title mb-1">Xin chào, <?= htmlspecialchars($userFullName) ?>!</h3>
                    <div class="text-muted font-weight-600" style="font-size: 0.85rem;">Theo dõi tình hình kinh doanh tổng thể của toàn bộ hệ thống từ CSDL ngay hôm nay.</div>
                </div>
            </div>
            
            <div class="date-badge d-none d-md-block">
                <i class="fa-regular fa-calendar me-2" style="color: var(--teal-main);"></i> Hôm nay, <?php echo date('d/m/Y'); ?>
            </div>
        </div>

        <!-- 4 Stats Cards (Lấy 100% từ SQL mới, bấm vào mở Modal chi tiết) -->
        <div class="row g-4 mb-5">
            <!-- 1. Tỷ lệ lấp đầy -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="overview-card card-green" data-bs-toggle="modal" data-bs-target="#roomsModal" title="Bấm để xem danh sách phòng trong CSDL">
                    <div class="card-bg-pattern pattern-green"></div>
                    <div class="overview-title text-success"><i class="fa-regular fa-calendar-check"></i> TỶ LỆ LẤP ĐẦY</div>
                    <div class="d-flex align-items-baseline mb-2 position-relative z-2">
                        <span class="overview-val text-dark me-2"><?= $stats['rented_rooms'] ?> / <?= $stats['total_rooms'] ?></span>
                        <span class="fw-bold text-muted small">phòng</span>
                    </div>
                    <div class="progress mb-2 position-relative z-2" style="height: 6px; background-color: rgba(255,255,255,0.7);">
                        <div class="progress-bar bg-success" style="width: <?= $stats['occupancy_rate'] ?>%"></div>
                    </div>
                    <div class="d-flex justify-content-between text-success position-relative z-2" style="font-size: 0.75rem; font-weight: 800;">
                        <span><i class="fa-solid fa-circle-check"></i> <?= $stats['occupancy_rate'] ?>% đang thuê</span>
                        <span class="text-muted"><?= $stats['empty_rooms'] ?> trống</span>
                    </div>
                </div>
            </div>

            <!-- 2. Thực thu -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="overview-card card-blue" data-bs-toggle="modal" data-bs-target="#revenueModal" title="Bấm để xem chi tiết doanh thu thực thu từ CSDL">
                    <div class="card-bg-pattern pattern-blue"></div>
                    <div class="overview-title text-primary"><i class="fa-regular fa-calendar"></i> THỰC THU (ĐÃ THU)</div>
                    <div class="overview-val text-dark mt-2 position-relative z-2"><?= number_format($stats['total_revenue'], 0, ',', '.') ?> đ</div>
                    <div class="mt-3 position-relative z-2 text-primary" style="font-size: 0.75rem; font-weight: 800;">
                        <i class="fa-solid fa-check-double"></i> Đã thu: <?= $stats['paid_invoices'] ?> hóa đơn
                    </div>
                    <div class="overview-icon-bg icon-blue"><i class="fa-solid fa-wallet"></i></div>
                </div>
            </div>

            <!-- 3. Công nợ -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="overview-card card-yellow" data-bs-toggle="modal" data-bs-target="#debtModal" title="Bấm để xem và thu tiền các hóa đơn công nợ">
                    <div class="card-bg-pattern pattern-yellow"></div>
                    <div class="overview-title text-warning" style="color: #b45309 !important;"><i class="fa-solid fa-coins"></i> CÔNG NỢ CHƯA THU</div>
                    <div class="overview-val mt-2 position-relative z-2" style="color: #b45309;"><?= number_format($stats['total_debt'], 0, ',', '.') ?> đ</div>
                    <div class="mt-3 position-relative z-2" style="font-size: 0.75rem; font-weight: 800; color: #b45309;">
                        <i class="fa-regular fa-clock"></i> Tồn đọng: <?= $stats['unpaid_invoices'] ?> hóa đơn
                    </div>
                    <div class="overview-icon-bg icon-yellow"><i class="fa-solid fa-exclamation"></i></div>
                </div>
            </div>

            <!-- 4. Khách & Hợp đồng -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="overview-card card-purple" data-bs-toggle="modal" data-bs-target="#contractsModal" title="Bấm để xem danh sách hợp đồng & khách thuê">
                    <div class="card-bg-pattern pattern-purple"></div>
                    <div class="overview-title" style="color: #7e22ce;"><i class="fa-solid fa-users"></i> KHÁCH & HỢP ĐỒNG</div>
                    <div class="d-flex align-items-baseline mt-2 position-relative z-2">
                        <span class="overview-val text-dark me-2"><?= $stats['active_contracts'] ?></span>
                        <span class="fw-bold text-muted small">hợp đồng hiệu lực</span>
                    </div>
                    <div class="mt-3 position-relative z-2" style="font-size: 0.75rem; font-weight: 800; color: #7e22ce;">
                        <i class="fa-solid fa-file-contract"></i> Tổng <?= $stats['total_contracts'] ?> HĐ • <?= $stats['total_tenants'] ?> khách thuê
                    </div>
                    <div class="overview-icon-bg icon-purple"><i class="fa-solid fa-user-group"></i></div>
                </div>
            </div>
        </div>

        <!-- Toolbar Tòa nhà -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <!-- Dropdown chọn Tòa nhà đang xem -->
                <div class="dropdown">
                    <button class="toolbar-select" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-building"></i>
                        Đang xem:
                        <?php if ($filterBuilding === 'all' || !isset($buildings[$filterBuilding])): ?>
                            Tất cả <?= $totalBuildingsCount ?> Tòa nhà
                        <?php else: ?>
                            <?= htmlspecialchars($buildings[$filterBuilding]['name']) ?>
                        <?php endif; ?>
                        <i class="fa-solid fa-angle-down ms-1"></i>
                    </button>
                    <ul class="dropdown-menu shadow border-0 rounded-4 p-2 mt-2">
                        <li>
                            <a class="dropdown-item rounded-3 py-2 fw-bold <?= $filterBuilding === 'all' ? 'bg-light text-teal-main' : '' ?>" href="index.php?route=dashboard&building_id=all">
                                <i class="fa-solid fa-layer-group me-2 text-teal-main"></i> Tất cả <?= $totalBuildingsCount ?> Tòa nhà (<?= $stats['total_rooms'] ?> phòng)
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <?php foreach ($buildings as $bId => $bItem): ?>
                            <li>
                                <a class="dropdown-item rounded-3 py-2 fw-bold <?= $filterBuilding === (string)$bId ? 'bg-light text-teal-main' : '' ?>" href="index.php?route=dashboard&building_id=<?= urlencode((string)$bId) ?>">
                                    <i class="fa-solid fa-building me-2 text-teal-main"></i> <?= htmlspecialchars($bItem['name']) ?> (<?= $bItem['total_rooms'] ?> phòng)
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Ô Danh sách loại phòng bên cạnh chỗ Đang xem -->
                <div class="dropdown">
                    <button class="toolbar-select" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="background:#ffffff; color:#0f766e; border: 1.5px solid #99f6e4;">
                        <i class="fa-solid fa-shapes text-teal-main me-1"></i>
                        Danh sách loại phòng
                        <span class="badge rounded-pill ms-1" style="background:rgba(48,143,134,0.15); color:var(--teal-main); font-size:0.75rem;"><?= count($roomTypesList ?? []) ?></span>
                        <i class="fa-solid fa-angle-down ms-1 text-muted" style="font-size:0.8rem;"></i>
                    </button>
                    <div class="dropdown-menu shadow-lg border-0 rounded-4 p-3 mt-2" style="min-width: 330px; max-width: 380px;">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="fw-bold text-dark small"><i class="fa-solid fa-layer-group text-teal-main me-1"></i> Các Loại phòng hiện có</span>
                            <button type="button" class="btn btn-sm btn-outline-teal-custom py-0 px-2" style="font-size:0.75rem; border-radius:12px;" data-bs-toggle="modal" data-bs-target="#addRoomTypeModal">
                                <i class="fa-solid fa-plus me-1"></i> Thêm mới
                            </button>
                        </div>
                        <div class="d-flex flex-column gap-2" style="max-height: 280px; overflow-y: auto;">
                            <?php foreach (($roomTypesList ?? []) as $rtKey => $rtItem): ?>
                                <div class="p-2 rounded-3 border bg-light-subtle">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="fw-bold text-teal-main" style="font-size:0.85rem;">
                                            <i class="fa-solid fa-tag me-1" style="font-size:0.75rem; color: <?= htmlspecialchars($rtItem['badge_color'] ?? '#308f86') ?>;"></i>
                                            <?= htmlspecialchars($rtItem['name']) ?>
                                        </div>
                                        <span class="fw-bold text-danger" style="font-size:0.8rem;"><?= number_format($rtItem['price'], 0, ',', '.') ?> đ</span>
                                    </div>
                                    <div class="text-muted small mt-1 d-flex justify-content-between" style="font-size:0.75rem;">
                                        <span><i class="fa-solid fa-vector-square me-1"></i>DT: <?= htmlspecialchars($rtItem['area'] ?? '--') ?></span>
                                    </div>
                                    <?php if (!empty($rtItem['amenities'])): ?>
                                        <div class="text-secondary small mt-1" style="font-size:0.72rem; line-height: 1.3;">
                                            <i class="fa-solid fa-check text-success me-1"></i><?= htmlspecialchars($rtItem['amenities']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <?php if ($filterBuilding !== 'all'): ?>
                    <a href="index.php?route=dashboard&building_id=all" class="btn btn-outline-custom">
                        <i class="fa-solid fa-rotate-left me-1"></i> Hiện Tất cả <?= $totalBuildingsCount ?> Tòa
                    </a>
                <?php endif; ?>
                <button type="button" class="btn btn-outline-teal-custom" data-bs-toggle="modal" data-bs-target="#manageAccountsModal" style="border: 2px solid var(--teal-main); color: var(--teal-main); font-weight: 800; border-radius: 50px; padding: 10px 22px; background: #ffffff; display: inline-flex; align-items: center; transition: 0.2s;">
                    <i class="fa-solid fa-users-gear me-2"></i> Quản lý Tài khoản
                </button>
                <button type="button" class="btn btn-teal-custom" data-bs-toggle="modal" data-bs-target="#addBuildingModal">
                    <i class="fa-solid fa-plus me-1"></i> Thêm Tòa nhà
                </button>
            </div>
        </div>

        <!-- Building Grid (Hiển thị số liệu thực tế từ CSDL cho từng Tòa nhà) -->
        <div class="row g-4 mb-4">
            <?php foreach ($displayedBuildings as $bId => $bld): $isAllowed = true; ?>
                <?php
                    $theme = $bld['theme'] ?? 'teal';
                    $badgeStyle = '';
                    $rect1 = '#a7dcd6'; $rect2 = '#75c2b9'; $roof = '#308f86';
                    if ($theme === 'blue') {
                        $rect1 = '#bae6fd'; $rect2 = '#7dd3fc'; $roof = '#0284c7';
                    } elseif ($theme === 'yellow') {
                        $badgeStyle = 'background:#fef9c3; color:#a16207; border-color:#fef08a;';
                        $rect1 = '#fef08a'; $rect2 = '#fde047'; $roof = '#ca8a04';
                    }
                ?>
                <div class="col-12 col-lg-4">
                    <div class="building-card">
                        <div class="position-relative z-2">
                            <span class="badge-bld" style="<?= $badgeStyle ?>"><?= htmlspecialchars($bld['code']) ?></span>
                        </div>
                        <div class="house-shadow"></div>
                        <svg class="house-img" viewBox="0 0 120 120">
                            <polygon points="10,60 60,20 110,60" fill="#94a3b8" opacity="0.3"/>
                            <rect x="25" y="40" width="70" height="70" fill="<?= $rect1 ?>" rx="4"/>
                            <rect x="40" y="30" width="40" height="80" fill="<?= $rect2 ?>" rx="4"/>
                            <polygon points="15,45 60,10 105,45" fill="<?= $roof ?>"/>
                            <rect x="35" y="60" width="10" height="15" fill="#ffffff" opacity="0.8"/>
                            <rect x="75" y="60" width="10" height="15" fill="#ffffff" opacity="0.8"/>
                            <rect x="35" y="85" width="10" height="15" fill="#ffffff" opacity="0.8"/>
                            <rect x="75" y="85" width="10" height="15" fill="#ffffff" opacity="0.8"/>
                            <circle cx="20" cy="100" r="10" fill="#22c55e" opacity="0.8"/>
                            <circle cx="100" cy="95" r="15" fill="#16a34a" opacity="0.7"/>
                        </svg>

                        <!-- Menu 3 chấm trên thẻ Tòa nhà -->
                        <div class="dropdown position-absolute z-3" style="top:22px; right:20px;">
                            <button class="btn btn-sm btn-light rounded-circle shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Tùy chọn tòa nhà">
                                <i class="fa-solid fa-ellipsis-vertical text-secondary"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 p-2">
                                <li>
                                    <a class="dropdown-item rounded-3 py-2 fw-bold" href="index.php?route=overview&building_id=<?= urlencode((string)$bId) ?>">
                                        <i class="fa-solid fa-chart-line me-2 text-teal-main"></i> Vào Tổng quan Tòa này
                                    </a>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item rounded-3 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#bldDetailModal_<?= htmlspecialchars((string)$bId) ?>">
                                        <i class="fa-regular fa-folder-open me-2 text-primary"></i> Xem Phòng & Hóa đơn CSDL
                                    </button>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item rounded-3 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#editBldModal_<?= htmlspecialchars((string)$bId) ?>">
                                        <i class="fa-solid fa-pen-to-square me-2 text-warning"></i> Chỉnh sửa Tòa nhà
                                    </button>
                                </li>
                                <?php if (count($buildings) > 1): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="index.php?route=dashboard" onsubmit="return confirm('Bạn có chắc muốn xóa tòa nhà này khỏi danh sách hiển thị?');">
                                            <input type="hidden" name="action" value="delete_building">
                                            <input type="hidden" name="building_id" value="<?= htmlspecialchars((string)$bId) ?>">
                                            <button type="submit" class="dropdown-item rounded-3 py-2 fw-bold text-danger">
                                                <i class="fa-solid fa-trash-can me-2"></i> Xóa Tòa nhà
                                            </button>
                                        </form>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        
                        <h3 class="bld-title position-relative z-2"><?= htmlspecialchars($bld['name']) ?></h3>
                        <div class="bld-address position-relative z-2">
                            <i class="fa-solid fa-location-dot" style="color:var(--teal-main);"></i> <?= htmlspecialchars($bld['address']) ?>
                        </div>
                        
                        
                        
                        <div class="manager-info text-muted position-relative z-2">
                            <div>
                                <i class="fa-solid fa-user-tie me-1 text-teal-main"></i> <strong>Quản lý riêng:</strong>
                                <span class="text-dark fw-bold"><?= htmlspecialchars($bld['manager']) ?></span>
                                <span style="color:var(--teal-main); font-weight:600;">(<?= htmlspecialchars($bld['manager_acc']) ?>)</span>
                            </div>
                            <?php if (!empty($bld['manager_phone']) || !empty($bld['manager_email'])): ?>
                                <div class="small mt-1 text-secondary" style="font-size: 0.76rem;">
                                    <?php if (!empty($bld['manager_phone'])): ?>
                                        <span class="me-2"><i class="fa-solid fa-phone me-1 text-teal-main"></i><?= htmlspecialchars($bld['manager_phone']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($bld['manager_email'])): ?>
                                        <span><i class="fa-solid fa-envelope me-1 text-teal-main"></i><?= htmlspecialchars($bld['manager_email']) ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- 3 ô số liệu Phòng lấy trực tiếp từ bảng `rooms` trong SQL -->
                        <div class="stat-group position-relative z-2" data-bs-toggle="modal" data-bs-target="#bldDetailModal_<?= htmlspecialchars((string)$bId) ?>" title="Bấm để xem chi tiết các phòng thuộc <?= htmlspecialchars($bld['name']) ?>">
                            <div class="stat-item stat-total">
                                <i class="fa-regular fa-building fs-4"></i>
                                <div class="stat-val"><?= $bld['total_rooms'] ?></div>
                                <div class="stat-label">Tổng phòng</div>
                            </div>
                            <div class="stat-item stat-rented">
                                <i class="fa-solid fa-user-group fs-4"></i>
                                <div class="stat-val"><?= $bld['rented_rooms'] ?></div>
                                <div class="stat-label">Đang thuê</div>
                            </div>
                            <div class="stat-item stat-empty">
                                <i class="fa-solid fa-house-chimney fs-4"></i>
                                <div class="stat-val"><?= $bld['empty_rooms'] ?></div>
                                <div class="stat-label">Phòng trống</div>
                            </div>
                        </div>

                        <!-- Thanh tóm tắt Thực thu & Công nợ của Tòa này từ bảng `invoices` -->
                        <div class="bld-finance-bar position-relative z-2">
                            <span class="text-primary"><i class="fa-solid fa-wallet me-1"></i> Đã thu: <?= number_format($bld['total_revenue'], 0, ',', '.') ?> đ</span>
                            <span class="text-danger"><i class="fa-solid fa-coins me-1"></i> Nợ: <?= number_format($bld['total_debt'], 0, ',', '.') ?> đ (<?= $bld['unpaid_invoices'] ?> HĐ)</span>
                        </div>

                        <div class="d-flex gap-2 mt-auto position-relative z-2">
                            <a href="index.php?route=overview&building_id=<?= urlencode((string)$bId) ?>" class="btn-bld-main">
                                <i class="fa-solid fa-arrow-right-to-bracket me-2"></i> Vào quản lý Tòa này
                            </a>
                            <button type="button" class="btn-bld-folder" data-bs-toggle="modal" data-bs-target="#bldDetailModal_<?= htmlspecialchars((string)$bId) ?>" title="Xem nhanh hồ sơ Phòng & Hóa đơn của <?= htmlspecialchars($bld['name']) ?>">
                                <i class="fa-regular fa-folder-open"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- CÁC MODAL TƯƠNG TÁC TRỰC TIẾP VỚI CSDL TRÊN TRANG TÒA NHÀ -->
    <!-- ========================================================= -->

    
    <!-- ================= MODAL THÊM LOẠI PHÒNG MỚI ================= -->
    <div class="modal fade" id="addRoomTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light rounded-top-4">
                    <h5 class="modal-title fw-bold text-teal-main"><i class="fa-solid fa-shapes me-2"></i> Thêm Loại Phòng Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="index.php?route=dashboard">
                    <input type="hidden" name="action" value="add_room_type">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Tên loại phòng <span class="text-danger">*</span></label>
                            <input type="text" name="rt_name" class="form-control rounded-3" placeholder="Ví dụ: Phòng Duplex Penthouse, Phòng Studio Mini..." required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">Giá tham khảo (VNĐ/tháng) <span class="text-danger">*</span></label>
                                <input type="number" name="rt_price" class="form-control rounded-3" placeholder="VD: 4500000" value="3500000" required step="50000">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">Diện tích (m²)</label>
                                <input type="text" name="rt_area" class="form-control rounded-3" placeholder="VD: 25 - 30m²" value="25m²">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Tiện ích đặc trưng</label>
                            <textarea name="rt_amenities" class="form-control rounded-3" rows="2" placeholder="Ví dụ: Điều hòa Inverter, Nóng lạnh, Tủ lạnh, Bếp từ, Ban công riêng..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-teal-custom px-4"><i class="fa-solid fa-check me-1"></i> Lưu loại phòng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- 1. MODAL THÊM TÒA NHÀ MỚI (RỘNG RÃI, VỪA KHUNG HÌNH MÁY TÍNH) -->
    <div class="modal fade" id="addBuildingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" style="max-width: 820px;">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light rounded-top-4 py-3 px-4">
                    <h5 class="modal-title fw-bold text-teal-main"><i class="fa-solid fa-building-circle-arrow-right me-2"></i> Thêm Tòa Nhà Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="index.php?route=dashboard">
                        <input type="hidden" name="action" value="add_building">
                        <div class="row g-3">
                            <!-- Nhóm 1: Thông tin Tòa nhà -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Mã tòa nhà <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control rounded-3" placeholder="VD: BLD-D" value="BLD-D" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-bold small text-muted">Tên tòa nhà <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control rounded-3" placeholder="VD: Tòa D - StayMate Diamond" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">Địa chỉ tòa nhà <span class="text-danger">*</span></label>
                                <input type="text" name="address" class="form-control rounded-3" placeholder="VD: Số 102 Xuân Thủy, Cầu Giấy, Hà Nội" required>
                            </div>

                            <!-- Nhóm 2: Thông tin Người quản lý (Rõ ràng hơn: Tên, SĐT, Email, Tài khoản, Mật khẩu) -->
                            <div class="col-12 mt-3 pt-2 border-top">
                                <div class="fw-bold text-teal-main small mb-1"><i class="fa-solid fa-user-tie me-1"></i> Thông tin Người quản lý tòa nhà</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Tên người quản lý <span class="text-danger">*</span></label>
                                <input type="text" name="manager" class="form-control rounded-3" placeholder="VD: Nguyễn Văn An" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Số điện thoại <span class="text-danger">*</span></label>
                                <input type="text" name="manager_phone" class="form-control rounded-3" placeholder="VD: 0912 345 678" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Email liên hệ <span class="text-danger">*</span></label>
                                <input type="email" name="manager_email" class="form-control rounded-3" placeholder="VD: an.nguyen@gmail.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Tài khoản quản lý <span class="text-danger">*</span></label>
                                <input type="text" name="manager_acc" class="form-control rounded-3" placeholder="VD: quanly_d" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Mật khẩu ban đầu</label>
                                <input type="text" name="manager_pwd" class="form-control rounded-3 bg-light" value="123" readonly title="Mật khẩu mặc định khởi tạo luôn là 123">
                            </div>

                            <!-- Nhóm 3: Các hạng phòng có trong tòa -->
                            <div class="col-12 mt-3 pt-2 border-top">
                                <label class="form-label fw-bold small text-teal-main mb-1 d-block"><i class="fa-solid fa-shapes me-1"></i> Chọn các Loại / Hạng phòng có trong tòa nhà này:</label>
                                <div class="p-3 rounded-3 border bg-light-subtle" style="max-height: 180px; overflow-y: auto;">
                                    <div class="row g-2">
                                        <?php foreach (($roomTypesList ?? []) as $rtKey => $rtItem): ?>
                                            <div class="col-md-6">
                                                <div class="form-check p-2 bg-white rounded-3 border">
                                                    <input class="form-check-input ms-0 me-2" type="checkbox" name="room_types[]" value="<?= htmlspecialchars($rtItem['name']) ?>" id="rt_add_<?= htmlspecialchars($rtKey) ?>" checked>
                                                    <label class="form-check-label fw-bold small text-dark cursor-pointer" for="rt_add_<?= htmlspecialchars($rtKey) ?>">
                                                        <?= htmlspecialchars($rtItem['name']) ?>
                                                        <span class="text-muted d-block" style="font-size: 0.75rem;"><?= number_format($rtItem['price'], 0, ',', '.') ?> đ • <?= htmlspecialchars($rtItem['area']) ?></span>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <button type="button" class="btn btn-outline-secondary px-4 rounded-pill fw-bold" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-teal-custom px-4">Lưu tòa nhà</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. CÁC MODAL CHI TIẾT & CHỈNH SỬA CHO TỪNG TÒA NHÀ -->
    <?php foreach ($buildings as $bId => $bld): ?>
        <!-- Modal Hồ sơ Phòng & Hóa đơn của Tòa nhà -->
        <div class="modal fade" id="bldDetailModal_<?= htmlspecialchars((string)$bId) ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header bg-light rounded-top-4">
                        <h5 class="modal-title fw-bold text-teal-main">
                            <i class="fa-regular fa-folder-open me-2"></i> Hồ sơ CSDL: <?= htmlspecialchars($bld['name']) ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-center bg-light p-3 rounded-4 mb-3 border">
                            <div>
                                <div class="fw-bold text-dark"><i class="fa-solid fa-location-dot text-teal-main me-1"></i> <?= htmlspecialchars($bld['address']) ?></div>
                                <div class="small text-muted">Quản lý: <strong><?= htmlspecialchars($bld['manager']) ?></strong> • Nhóm phòng SQL: <code><?= htmlspecialchars($bld['room_prefix']) ?>%</code></div>
                            </div>
                            <div class="d-flex gap-2 mt-2 mt-md-0">
                                <span class="badge bg-primary rounded-pill px-3 py-2">Tổng: <?= $bld['total_rooms'] ?> phòng</span>
                                <span class="badge bg-success rounded-pill px-3 py-2">Đang thuê: <?= $bld['rented_rooms'] ?></span>
                                <span class="badge bg-warning text-dark rounded-pill px-3 py-2">Trống: <?= $bld['empty_rooms'] ?></span>
                            </div>
                        </div>

                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-bed text-teal-main me-1"></i> Danh sách Phòng thuộc Tòa này (Từ bảng `rooms` & `contracts`):</h6>
                        <div class="table-responsive mb-4">
                            <table class="table table-hover align-middle border rounded-3 overflow-hidden">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mã Phòng</th>
                                        <th>Giá Thuê</th>
                                        <th>Mô Tả</th>
                                        <th>Khách Đang Thuê</th>
                                        <th>Trạng Thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($bld['rooms_list'])): ?>
                                        <?php foreach ($bld['rooms_list'] as $rm): ?>
                                            <tr>
                                                <td class="fw-bold text-teal-main"><?= htmlspecialchars($rm['room_number']) ?></td>
                                                <td class="fw-bold"><?= number_format($rm['price'], 0, ',', '.') ?> đ</td>
                                                <td class="small text-muted"><?= htmlspecialchars($rm['description'] ?? '') ?></td>
                                                <td>
                                                    <?php if (!empty($rm['tenant_name'])): ?>
                                                        <div class="fw-bold text-dark"><?= htmlspecialchars($rm['tenant_name']) ?></div>
                                                        <div class="small text-muted"><?= htmlspecialchars($rm['tenant_phone'] ?? '') ?></div>
                                                    <?php else: ?>
                                                        <span class="text-muted fst-italic">Chưa có khách</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($rm['status'] === 'rented'): ?>
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Đang thuê</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3">Phòng trống</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center text-muted py-3">Chưa có phòng nào thuộc nhóm <?= htmlspecialchars($bld['room_prefix']) ?>xx trong CSDL.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-file-invoice-dollar text-teal-main me-1"></i> Hóa đơn của Tòa này (Từ bảng `invoices`):</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle border">
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
                                    <?php if (!empty($bld['invoices_list'])): ?>
                                        <?php foreach (array_slice($bld['invoices_list'], 0, 8) as $inv): ?>
                                            <tr>
                                                <td class="fw-bold"><?= htmlspecialchars($inv['invoice_code']) ?></td>
                                                <td><span class="badge bg-secondary"><?= htmlspecialchars($inv['room_number']) ?></span></td>
                                                <td><?= htmlspecialchars($inv['tenant']) ?></td>
                                                <td><?= htmlspecialchars($inv['month_year']) ?></td>
                                                <td class="fw-bold text-danger"><?= number_format($inv['total_amount'], 0, ',', '.') ?> đ</td>
                                                <td>
                                                    <?php if ($inv['status'] === 'paid'): ?>
                                                        <span class="badge bg-success">Đã thu</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Chưa thu</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center text-muted py-3">Chưa có hóa đơn.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <a href="index.php?route=rooms&floor=<?= urlencode(substr($bld['room_prefix'], 1)) ?>" class="btn btn-outline-secondary rounded-pill px-3">
                            <i class="fa-solid fa-bed me-1"></i> Quản lý Phòng
                        </a>
                        <a href="index.php?route=overview&building_id=<?= urlencode((string)$bId) ?>" class="btn btn-teal-custom px-4">
                            <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> Vào Tổng quan <?= htmlspecialchars($bld['code']) ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Chỉnh sửa Tòa nhà (Rộng rãi, đầy đủ SĐT, Email, Loại phòng, vừa vặn máy tính) -->
        <div class="modal fade" id="editBldModal_<?= htmlspecialchars((string)$bId) ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" style="max-width: 820px;">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header bg-light rounded-top-4 py-3 px-4">
                        <h5 class="modal-title fw-bold text-teal-main"><i class="fa-solid fa-pen-to-square me-2"></i> Chỉnh sửa <?= htmlspecialchars($bld['name']) ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form method="POST" action="index.php?route=dashboard">
                            <input type="hidden" name="action" value="edit_building">
                            <input type="hidden" name="building_id" value="<?= htmlspecialchars((string)$bId) ?>">
                            <div class="row g-3">
                                <!-- Nhóm 1: Thông tin Tòa nhà -->
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">Mã tòa nhà <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control rounded-3" value="<?= htmlspecialchars($bld['code']) ?>" required>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label fw-bold small text-muted">Tên tòa nhà <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control rounded-3" value="<?= htmlspecialchars($bld['name']) ?>" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold small text-muted">Địa chỉ tòa nhà <span class="text-danger">*</span></label>
                                    <input type="text" name="address" class="form-control rounded-3" value="<?= htmlspecialchars($bld['address']) ?>" required>
                                </div>

                                <!-- Nhóm 2: Thông tin Người quản lý (Tên, SĐT, Email, Tài khoản) -->
                                <div class="col-12 mt-3 pt-2 border-top">
                                    <div class="fw-bold text-teal-main small mb-1"><i class="fa-solid fa-user-tie me-1"></i> Thông tin Người quản lý tòa nhà</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">Tên người quản lý <span class="text-danger">*</span></label>
                                    <input type="text" name="manager" class="form-control rounded-3" value="<?= htmlspecialchars($bld['manager']) ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">Số điện thoại <span class="text-danger">*</span></label>
                                    <input type="text" name="manager_phone" class="form-control rounded-3" value="<?= htmlspecialchars($bld['manager_phone'] ?? '0912 345 678') ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small text-muted">Email liên hệ <span class="text-danger">*</span></label>
                                    <input type="email" name="manager_email" class="form-control rounded-3" value="<?= htmlspecialchars($bld['manager_email'] ?? ($bld['manager_acc'] . '@nhatro.com')) ?>" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-bold small text-muted">Tài khoản quản lý <span class="text-danger">*</span></label>
                                    <input type="text" name="manager_acc" class="form-control rounded-3" value="<?= htmlspecialchars($bld['manager_acc']) ?>" required>
                                </div>

                                <!-- Nhóm 3: Các loại phòng thuộc tòa này -->
                                <div class="col-12 mt-3 pt-2 border-top">
                                    <label class="form-label fw-bold small text-teal-main mb-1 d-block"><i class="fa-solid fa-shapes me-1"></i> Chọn các Loại / Hạng phòng có trong tòa nhà này:</label>
                                    <div class="p-3 rounded-3 border bg-light-subtle" style="max-height: 180px; overflow-y: auto;">
                                        <div class="row g-2">
                                            <?php foreach (($roomTypesList ?? []) as $rtKey => $rtItem): ?>
                                                <?php $isChecked = in_array($rtItem['name'], $bld['room_types'] ?? [], true); ?>
                                                <div class="col-md-6">
                                                    <div class="form-check p-2 bg-white rounded-3 border">
                                                        <input class="form-check-input ms-0 me-2" type="checkbox" name="room_types[]" value="<?= htmlspecialchars($rtItem['name']) ?>" id="rt_edit_<?= htmlspecialchars($bId . '_' . $rtKey) ?>" <?= $isChecked ? 'checked' : '' ?>>
                                                        <label class="form-check-label fw-bold small text-dark cursor-pointer" for="rt_edit_<?= htmlspecialchars($bId . '_' . $rtKey) ?>">
                                                            <?= htmlspecialchars($rtItem['name']) ?>
                                                            <span class="text-muted d-block" style="font-size: 0.75rem;"><?= number_format($rtItem['price'], 0, ',', '.') ?> đ • <?= htmlspecialchars($rtItem['area']) ?></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                                <button type="button" class="btn btn-outline-secondary px-4 rounded-pill fw-bold" data-bs-dismiss="modal">Hủy</button>
                                <button type="submit" class="btn btn-teal-custom px-4">Cập nhật tòa nhà</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
                            
    <?php endforeach; ?>

    <!-- 3. MODAL DANH SÁCH TẤT CẢ PHÒNG TRỌ TỪ SQL (Khi bấm thẻ Tỷ lệ lấp đầy) -->
    <div class="modal fade" id="roomsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light rounded-top-4">
                    <h5 class="modal-title fw-bold text-success"><i class="fa-solid fa-bed me-2"></i> Tình trạng Phòng trọ trong CSDL (<?= $stats['rented_rooms'] ?>/<?= $stats['total_rooms'] ?> đang thuê)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle border">
                            <thead class="table-success">
                                <tr>
                                    <th>Mã Phòng</th>
                                    <th>Giá Thuê</th>
                                    <th>Mô tả loại phòng</th>
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
                                        <td><?= !empty($rm['tenant_name']) ? htmlspecialchars($rm['tenant_name']) : '<span class="text-muted">Đang trống</span>' ?></td>
                                        <td>
                                            <?php if ($rm['status'] === 'rented'): ?>
                                                <span class="badge bg-success rounded-pill">Đang thuê</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark rounded-pill">Phòng trống</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                    <a href="index.php?route=rooms" class="btn btn-teal-custom px-4">Đi tới Quản lý Phòng trọ</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. MODAL THỰC THU & DOANH THU 6 THÁNG TỪ SQL (Khi bấm thẻ Thực thu) -->
    <div class="modal fade" id="revenueModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light rounded-top-4">
                    <h5 class="modal-title fw-bold text-primary"><i class="fa-solid fa-wallet me-2"></i> Thống kê Thực thu từ CSDL: <?= number_format($stats['total_revenue'], 0, ',', '.') ?> đ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <h6 class="fw-bold mb-3">Tổng hợp Doanh thu & Công nợ theo Kỳ tháng (Bảng `invoices`):</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle text-center">
                            <thead class="table-primary">
                                <tr>
                                    <th>Kỳ Hóa Đơn</th>
                                    <th>Số lượng HĐ</th>
                                    <th>Thực Thu (Đã đóng)</th>
                                    <th>Công Nợ (Chưa đóng)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($chartData['rows'])): ?>
                                    <?php foreach ($chartData['rows'] as $cr): ?>
                                        <tr>
                                            <td class="fw-bold">Tháng <?= htmlspecialchars($cr['month_year']) ?></td>
                                            <td><?= (int)$cr['invoice_count'] ?> hóa đơn</td>
                                            <td class="fw-bold text-success"><?= number_format($cr['revenue'], 0, ',', '.') ?> đ</td>
                                            <td class="fw-bold text-danger"><?= number_format($cr['debt'], 0, ',', '.') ?> đ</td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                    <a href="index.php?route=overview&status=paid#invoice-section" class="btn btn-teal-custom px-4">Xem toàn bộ Hóa đơn đã thu</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. MODAL CÔNG NỢ CHƯA THU (Khi bấm thẻ Công nợ) -->
    <div class="modal fade" id="debtModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light rounded-top-4">
                    <h5 class="modal-title fw-bold text-warning" style="color:#b45309 !important;">
                        <i class="fa-solid fa-coins me-2"></i> Danh sách Hóa đơn Chưa thu (<?= $stats['unpaid_invoices'] ?> HĐ - <?= number_format($stats['total_debt'], 0, ',', '.') ?> đ)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle border">
                            <thead class="table-warning">
                                <tr>
                                    <th>Mã HĐ</th>
                                    <th>Phòng</th>
                                    <th>Khách thuê</th>
                                    <th>Kỳ</th>
                                    <th>Số tiền nợ</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $unpaidOnly = array_filter($debtList ?? [], fn($i) => $i['status'] === 'unpaid');
                                ?>
                                <?php if (!empty($unpaidOnly)): ?>
                                    <?php foreach ($unpaidOnly as $uInv): ?>
                                        <tr>
                                            <td class="fw-bold"><?= htmlspecialchars($uInv['invoice_code']) ?></td>
                                            <td><span class="badge bg-dark"><?= htmlspecialchars($uInv['room_number']) ?></span></td>
                                            <td class="fw-bold"><?= htmlspecialchars($uInv['tenant']) ?></td>
                                            <td><?= htmlspecialchars($uInv['month_year']) ?></td>
                                            <td class="fw-bold text-danger"><?= number_format($uInv['total_amount'], 0, ',', '.') ?> đ</td>
                                            <td>
                                                <form method="POST" action="index.php?route=dashboard" class="d-inline">
                                                    <input type="hidden" name="action" value="pay_invoice">
                                                    <input type="hidden" name="invoice_id" value="<?= (int)$uInv['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 fw-bold">
                                                        <i class="fa-solid fa-check me-1"></i> Thu tiền
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center text-muted py-4">Tuyệt vời! Không còn hóa đơn tồn đọng.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                    <a href="index.php?route=overview&status=unpaid#invoice-section" class="btn btn-teal-custom px-4">Quản lý Công nợ trên Trang Tổng quan</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. MODAL KHÁCH & HỢP ĐỒNG TỪ SQL (Khi bấm thẻ Khách & Hợp đồng) -->
    <div class="modal fade" id="contractsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light rounded-top-4">
                    <h5 class="modal-title fw-bold" style="color:#7e22ce;"><i class="fa-solid fa-file-signature me-2"></i> Danh sách Hợp đồng trong CSDL (<?= count($contractsList ?? []) ?> hợp đồng)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="table-responsive">
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
                                        <td><span class="badge bg-teal-main" style="background:var(--teal-main);"><?= htmlspecialchars($con['room_number']) ?></span></td>
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($con['tenant_name']) ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars($con['tenant_phone']) ?></div>
                                        </td>
                                        <td class="small"><?= htmlspecialchars($con['start_date']) ?> ➔ <?= htmlspecialchars($con['end_date']) ?></td>
                                        <td class="fw-bold"><?= number_format($con['deposit_amount'], 0, ',', '.') ?> đ</td>
                                        <td>
                                            <?php if ($con['status'] === 'active'): ?>
                                                <span class="badge bg-success rounded-pill">Đang hiệu lực</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary rounded-pill">Đã thanh lý</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <a href="index.php?route=tenants" class="btn btn-outline-secondary rounded-pill px-4">Trang Khách thuê</a>
                    <a href="index.php?route=contracts" class="btn btn-teal-custom px-4">Đi tới Quản lý Hợp đồng</a>
                </div>
            </div>
        </div>
    </div>

    <!-- 7. MODAL HỒ SƠ TÀI KHOẢN ĐANG ĐĂNG NHẬP -->
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

    <footer class="footer-custom container-fluid px-4 px-xl-5 d-flex justify-content-between position-relative z-1">
        <div><i class="fa-solid fa-building me-1" style="color:var(--teal-main);"></i> © 2026 StayMate - Quản lý Nhà trọ Chuyên nghiệp</div>
        <div class="d-flex gap-4">
            <span><i class="fa-solid fa-shield-halved me-1" style="color:var(--teal-main);"></i> WAMP MVC Architecture</span>
            <span><i class="fa-solid fa-bolt me-1 text-warning"></i> Tự động hóa Điện, Nước & Hóa đơn</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Xóa sạch bộ nhớ tạm trình duyệt cũ lưu những hành động xem trang trước đây
        try { localStorage.removeItem("staymate_activity_logs"); } catch(e) {}
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


    <!-- 4. MODAL POPUP QUAN LY TAI KHOAN (Hiện danh sách tài khoản hiện có, cho phép Xóa và Sửa, không cần Thêm) -->
    <div class="modal fade" id="manageAccountsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 960px;">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light rounded-top-4 py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="p-2 bg-white rounded-circle shadow-sm" style="color:var(--teal-main);">
                            <i class="fa-solid fa-users-gear fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-teal-main mb-0">Quản Lý Tài Khoản Hệ Thống</h5>
                            <span class="text-muted small">Danh sách tài khoản hiện có trong hệ thống (Sửa / Xóa)</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-secondary small fw-bold">
                                <tr>
                                    <th class="ps-3 py-3" style="width: 50px;">STT</th>
                                    <th class="py-3">Tài khoản & Họ tên</th>
                                    <th class="py-3">Số điện thoại</th>
                                    <th class="py-3">Email liên hệ</th>
                                    <th class="py-3 text-center">Vai trò</th>
                                    <th class="pe-3 py-3 text-end" style="width: 140px;">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($allAccountsList)): ?>
                                    <?php foreach ($allAccountsList as $idx => $acc): ?>
                                        <?php
                                            $roleBadge = '';
                                            if ($acc['role'] === 'admin') {
                                                $roleBadge = '<span class="badge rounded-pill" style="background:#ede9fe; color:#6d28d9; font-weight:700;"><i class="fa-solid fa-shield-halved me-1"></i>Admin</span>';
                                            } elseif ($acc['role'] === 'landlord') {
                                                $roleBadge = '<span class="badge rounded-pill" style="background:#ccfbf1; color:#0f766e; font-weight:700;"><i class="fa-solid fa-user-tie me-1"></i>Quản lý</span>';
                                            } else {
                                                $roleBadge = '<span class="badge rounded-pill" style="background:#e0f2fe; color:#0369a1; font-weight:700;"><i class="fa-solid fa-user me-1"></i>Khách thuê</span>';
                                            }
                                            $isSelf = (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$acc['id']);
                                        ?>
                                        <tr>
                                            <td class="ps-3 fw-bold text-muted"><?= $idx + 1 ?></td>
                                            <td>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($acc['full_name']) ?></div>
                                                <div class="small text-muted font-monospace"><i class="fa-regular fa-user me-1"></i><?= htmlspecialchars($acc['username']) ?></div>
                                            </td>
                                            <td>
                                                <span class="small fw-semibold text-secondary">
                                                    <?= !empty($acc['phone']) ? htmlspecialchars($acc['phone']) : '<span class="text-muted font-italic">Chưa có</span>' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="small text-secondary">
                                                    <?= !empty($acc['email']) ? htmlspecialchars($acc['email']) : '<span class="text-muted font-italic">Chưa có</span>' ?>
                                                </span>
                                            </td>
                                            <td class="text-center"><?= $roleBadge ?></td>
                                            <td class="pe-3 text-end">
                                                <div class="btn-group gap-1">
                                                    <!-- Nút Sửa -->
                                                    <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#editUserModal_<?= $acc['id'] ?>" title="Sửa thông tin tài khoản">
                                                        <i class="fa-solid fa-pen-to-square me-1"></i> Sửa
                                                    </button>
                                                    
                                                    <!-- Nút Xóa -->
                                                    <?php if (!$isSelf): ?>
                                                        <form method="POST" action="index.php?route=dashboard" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài khoản này không?');">
                                                            <input type="hidden" name="action" value="delete_user_account">
                                                            <input type="hidden" name="user_id" value="<?= $acc['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2 fw-bold" title="Xóa tài khoản">
                                                                <i class="fa-solid fa-trash-can"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Không có tài khoản nào trong hệ thống.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4 py-2 px-4 d-flex justify-content-between align-items-center">
                    <span class="small text-muted"><i class="fa-solid fa-circle-info me-1"></i> Tổng cộng <?= count($allAccountsList ?? []) ?> tài khoản</span>
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL CON CHỈNH SỬA TỪNG TÀI KHOẢN -->
    <?php if (!empty($allAccountsList)): ?>
        <?php foreach ($allAccountsList as $acc): ?>
            <div class="modal fade" id="editUserModal_<?= $acc['id'] ?>" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
                <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
                    <div class="modal-content border-0 shadow-lg rounded-4">
                        <div class="modal-header bg-light rounded-top-4 py-3 px-4">
                            <h6 class="modal-title fw-bold text-teal-main mb-0">
                                <i class="fa-solid fa-user-pen me-2"></i> Chỉnh sửa tài khoản: <span class="text-dark"><?= htmlspecialchars($acc['username']) ?></span>
                            </h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <form method="POST" action="index.php?route=dashboard">
                                <input type="hidden" name="action" value="edit_user_account">
                                <input type="hidden" name="user_id" value="<?= $acc['id'] ?>">
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-muted">Họ và tên <span class="text-danger">*</span></label>
                                    <input type="text" name="full_name" class="form-control rounded-3" value="<?= htmlspecialchars($acc['full_name']) ?>" required>
                                </div>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-bold small text-muted">Số điện thoại</label>
                                        <input type="text" name="phone" class="form-control rounded-3" value="<?= htmlspecialchars($acc['phone']) ?>" placeholder="0912 345 678">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-bold small text-muted">Email <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control rounded-3" value="<?= htmlspecialchars($acc['email']) ?>" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-muted">Phân quyền</label>
                                    <select name="role" class="form-select rounded-3">
                                        <option value="tenant" <?= $acc['role'] === 'tenant' ? 'selected' : '' ?>>Khách thuê</option>
                                        <option value="landlord" <?= $acc['role'] === 'landlord' ? 'selected' : '' ?>>Quản lý tòa nhà</option>
                                        <option value="admin" <?= $acc['role'] === 'admin' ? 'selected' : '' ?>>Quản trị viên (Admin)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-muted">Mật khẩu mới (Bỏ trống nếu giữ nguyên)</label>
                                    <input type="password" name="new_password" class="form-control rounded-3" placeholder="Nhập mật khẩu mới nếu muốn đổi">
                                </div>
                                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                                    <button type="submit" class="btn btn-teal-custom px-4"><i class="fa-solid fa-check me-1"></i> Lưu thông tin</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
</body>
</html>
