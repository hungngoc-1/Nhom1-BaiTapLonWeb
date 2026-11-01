<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayMate - Quản Lý Nhà Trọ</title>
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
        
        /* Họa tiết Lá cây góc trái */
        .deco-leaf-left {
            position: absolute; left: -10px; top: -20px; width: 100px; height: 150px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 150"><path d="M-10,30 Q40,50 60,160" stroke="%2316a34a" stroke-width="2" fill="none"/><path d="M10,40 Q30,20 50,35 Q30,50 10,40" fill="%234ade80"/><path d="M25,70 Q55,50 65,75 Q45,90 25,70" fill="%2322c55e"/><path d="M40,110 Q65,90 75,115 Q50,130 40,110" fill="%2315803d"/></svg>');
            background-size: contain; background-repeat: no-repeat;
            z-index: 1; opacity: 0.9;
        }

        /* Họa tiết Ngôi nhà góc phải */
        .deco-house-right {
            position: absolute; right: 20px; bottom: 0; width: 220px; height: 130px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 150"><circle cx="160" cy="110" r="30" fill="%23a7dcd6" opacity="0.4"/><rect x="50" y="60" width="80" height="70" fill="%23ffffff"/><rect x="130" y="70" width="30" height="60" fill="%23f1f5f9"/><polygon points="40,60 90,20 140,60" fill="%23308f86"/><polygon points="90,20 140,60 170,60 120,20" fill="%2375c2b9"/><rect x="70" y="80" width="12" height="15" fill="%23a7dcd6" rx="2"/><rect x="95" y="80" width="12" height="15" fill="%23a7dcd6" rx="2"/><rect x="70" y="105" width="12" height="15" fill="%23a7dcd6" rx="2"/><rect x="95" y="105" width="12" height="15" fill="%23a7dcd6" rx="2"/><rect x="140" y="90" width="10" height="15" fill="%23a7dcd6" rx="2"/><circle cx="45" cy="120" r="15" fill="%2322c55e"/><circle cx="65" cy="125" r="20" fill="%2316a34a"/><circle cx="150" cy="125" r="18" fill="%2315803d"/><circle cx="175" cy="120" r="12" fill="%2322c55e"/></svg>');
            background-size: contain; background-repeat: no-repeat; background-position: bottom right;
            z-index: 1;
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
        
        /* Tia chớp vàng (sparkles) quanh icon */
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
        .overview-val { font-size: 2.2rem; font-weight: 900; line-height: 1; letter-spacing: -1px;}
        .overview-icon-bg {
            position: absolute; right: 24px; top: 50%; transform: translateY(-50%);
            width: 54px; height: 54px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1.4rem;
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
            z-index: 2;
        }
        .icon-green { background-color: #22c55e; }
        .icon-blue { background-color: #3b82f6; }
        .icon-yellow { background-color: #eab308; }
        .icon-purple { background-color: #a855f7; }

        /* Toolbar */
        .toolbar-select { background: white; border-radius: 20px; padding: 10px 24px; font-weight: 800; color: var(--teal-main); display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 6px 15px rgba(0,0,0,0.06); border: 2px solid #e2e8f0; }
        .btn-outline-custom { background: white; border-radius: 20px; font-weight: 700; color: #475569; padding: 10px 24px; border: 2px solid #e2e8f0; box-shadow: 0 6px 15px rgba(0,0,0,0.06); }
        .btn-teal-custom { background: var(--teal-main); border-radius: 20px; font-weight: 700; color: white; padding: 10px 24px; border: none; box-shadow: 0 8px 20px rgba(48, 143, 134, 0.4); }

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
        .bld-address { font-size: 0.8rem; color: #64748b; font-weight: 700; margin-bottom: 20px; }
        
        .manager-info { font-size: 0.8rem; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        
        /* Stats inside Building Card */
        .stat-group { display: flex; gap: 10px; margin-bottom: 24px; }
        .stat-item {
            flex: 1; border-radius: 16px; padding: 14px 8px; text-align: center;
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px;
            border: 2px solid;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .stat-total { background-color: #e0f2fe; border-color: #bae6fd;}
        .stat-total .stat-val, .stat-total i { color: #0284c7; }
        
        .stat-rented { background-color: #dcfce7; border-color: #bbf7d0;}
        .stat-rented .stat-val, .stat-rented i { color: #16a34a; }
        
        .stat-empty { background-color: #fef9c3; border-color: #fde047;}
        .stat-empty .stat-val, .stat-empty i { color: #d97706; }
        
        .stat-val { font-size: 1.6rem; font-weight: 900; line-height: 1; }
        .stat-label { font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: #475569;}

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
            box-shadow: 0 6px 15px rgba(0,0,0,0.08); transition: 0.2s;
        }
        .btn-bld-folder:hover { background: #f0fdfa; }

        .house-img { position: absolute; right: 20px; top: 10px; width: 120px; z-index: 1;}
        .house-shadow { position: absolute; right: 25px; top: 110px; width: 110px; height: 15px; background: rgba(0,0,0,0.1); border-radius: 50%; filter: blur(4px); z-index: 0;}
        
        /* Footer */
        .footer-custom { background: rgba(255,255,255,0.8); padding: 15px 0; color: #64748b; font-size: 0.8rem; font-weight: 700; margin-top: auto; border-top: 1px solid #e2e8f0;}
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid px-4 px-xl-5 d-flex justify-content-between align-items-center">
            <a class="brand-logo" href="#">
                <i class="fa-solid fa-house-chimney text-teal-main"></i>
                <div>
                    <div class="brand-text">StayMate</div>
                    <div class="brand-sub">Quản Lý Nhà Trọ</div>
                </div>
            </a>
            
            <div class="d-flex align-items-center gap-4">
                <div class="position-relative cursor-pointer" style="color: #94a3b8;">
                    <i class="fa-solid fa-bell fs-5"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white" style="font-size: 0.6rem;">8</span>
                </div>
                <div class="dropdown">
                    <div class="d-flex align-items-center gap-2 cursor-pointer" data-bs-toggle="dropdown">
                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width:40px; height:40px; background-color: var(--teal-main); border: 2px solid #ffffff;">AS</div>
                        <div class="text-start d-none d-sm-block lh-sm">
                            <div class="fw-bold text-dark" style="font-size: 0.85rem;">Quản trị viên Hệ thống <i class="fa-solid fa-angle-down ms-1 text-muted fs-6"></i></div>
                            <div class="text-muted" style="font-size: 0.75rem;">Admin</div>
                        </div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                        <li><a class="dropdown-item text-danger" href="index.php?route=auth/logout"><i class="fa-solid fa-right-from-bracket me-2"></i> Đăng xuất</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid px-4 px-xl-5 py-2 position-relative z-1">
        
        <!-- Khung Welcome Banner Cực Đẹp -->
        <div class="welcome-banner mb-4">
            <!-- Họa tiết trang trí 2 bên -->
            <div class="deco-leaf-left"></div>
            <div class="deco-house-right"></div>
            
            <!-- Nội dung chính (Trái) -->
            <div class="d-flex align-items-center gap-4 position-relative z-2">
                <div class="welcome-icon-wrapper">
                    <i class="fa-solid fa-hand-sparkles"></i>
                    <span class="sparkle sp-1"></span>
                    <span class="sparkle sp-2"></span>
                    <span class="sparkle sp-3"></span>
                </div>
                <div>
                    <h3 class="welcome-title mb-1">Xin chào, Admin!</h3>
                    <div class="text-muted font-weight-600" style="font-size: 0.85rem;">Theo dõi tình hình kinh doanh tổng thể của toàn bộ hệ thống ngay hôm nay.</div>
                </div>
            </div>
            
            <!-- Nội dung chính (Phải) -->
            <div class="date-badge d-none d-md-block">
                <i class="fa-regular fa-calendar me-2" style="color: var(--teal-main);"></i> Hôm nay, <?php echo date('d/m/Y'); ?>
            </div>
        </div>

        <!-- 4 Stats Cards -->
        <div class="row g-4 mb-5">
            <!-- Tỷ lệ lấp đầy -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="overview-card card-green">
                    <div class="card-bg-pattern pattern-green"></div>
                    <div class="overview-title text-success"><i class="fa-regular fa-calendar-check"></i> TỶ LỆ LẤP ĐẦY</div>
                    <div class="d-flex align-items-baseline mb-2 position-relative z-2"><span class="overview-val text-dark me-2">1 / 3</span> <span class="fw-bold text-muted small">phòng</span></div>
                    <div class="progress mb-2 position-relative z-2" style="height: 6px; background-color: rgba(255,255,255,0.7);"><div class="progress-bar bg-success" style="width: 33%"></div></div>
                    <div class="d-flex justify-content-between text-success position-relative z-2" style="font-size: 0.75rem; font-weight: 800;">
                        <span><i class="fa-solid fa-circle-check"></i> 33% đang thuê</span><span class="text-muted">2 trống</span>
                    </div>
                </div>
            </div>
            <!-- Thực thu -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="overview-card card-blue">
                    <div class="card-bg-pattern pattern-blue"></div>
                    <div class="overview-title text-primary"><i class="fa-regular fa-calendar"></i> THỰC THU 2026</div>
                    <div class="overview-val text-dark mt-2 position-relative z-2">0 đ</div>
                    <div class="overview-icon-bg icon-blue"><i class="fa-solid fa-wallet"></i></div>
                </div>
            </div>
            <!-- Công nợ -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="overview-card card-yellow">
                    <div class="card-bg-pattern pattern-yellow"></div>
                    <div class="overview-title text-warning" style="color: #b45309 !important;"><i class="fa-solid fa-coins"></i> CÔNG NỢ CHƯA THU</div>
                    <div class="overview-val mt-2 position-relative z-2" style="color: #b45309;">0 đ</div>
                    <div class="mt-3 position-relative z-2" style="font-size: 0.75rem; font-weight: 800; color: #b45309;"><i class="fa-regular fa-clock"></i> Tồn đọng: 0 hóa đơn</div>
                    <div class="overview-icon-bg icon-yellow"><i class="fa-solid fa-exclamation"></i></div>
                </div>
            </div>
            <!-- Khách & Hợp đồng -->
            <div class="col-12 col-md-6 col-lg-3">
                <div class="overview-card card-purple">
                    <div class="card-bg-pattern pattern-purple"></div>
                    <div class="overview-title" style="color: #7e22ce;"><i class="fa-solid fa-users"></i> KHÁCH & HỢP ĐỒNG</div>
                    <div class="d-flex align-items-baseline mt-2 position-relative z-2"><span class="overview-val text-dark me-2">0</span> <span class="fw-bold text-muted small">hợp đồng</span></div>
                    <div class="overview-icon-bg icon-purple"><i class="fa-solid fa-user-group"></i></div>
                </div>
            </div>
        </div>

        <!-- Toolbar Tòa nhà -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div class="toolbar-select">
                <i class="fa-solid fa-building"></i> Đang xem: Tất cả 3 Tòa nhà <i class="fa-solid fa-angle-down ms-1"></i>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-custom"><i class="fa-regular fa-eye me-1"></i> Xem Tất cả Tòa nhà</button>
                <button class="btn btn-teal-custom" data-bs-toggle="modal" data-bs-target="#addBuildingModal"><i class="fa-solid fa-plus me-1"></i> Thêm Tòa nhà</button>
            </div>
        </div>

        <!-- Building Grid -->
        <div class="row g-4">
            <!-- Tòa A -->
            <div class="col-12 col-lg-4">
                <div class="building-card">
                    <div class="position-relative z-2"><span class="badge-bld">BLD-A</span></div>
                    <div class="house-shadow"></div>
                    <svg class="house-img" viewBox="0 0 120 120">
                        <polygon points="10,60 60,20 110,60" fill="#94a3b8" opacity="0.3"/>
                        <rect x="25" y="40" width="70" height="70" fill="#a7dcd6" rx="4"/>
                        <rect x="40" y="30" width="40" height="80" fill="#75c2b9" rx="4"/>
                        <polygon points="15,45 60,10 105,45" fill="#308f86"/>
                        <rect x="35" y="60" width="10" height="15" fill="#ffffff" opacity="0.8"/>
                        <rect x="75" y="60" width="10" height="15" fill="#ffffff" opacity="0.8"/>
                        <rect x="35" y="85" width="10" height="15" fill="#ffffff" opacity="0.8"/>
                        <rect x="75" y="85" width="10" height="15" fill="#ffffff" opacity="0.8"/>
                        <circle cx="20" cy="100" r="10" fill="#22c55e" opacity="0.8"/>
                        <circle cx="100" cy="95" r="15" fill="#16a34a" opacity="0.7"/>
                    </svg>
                    <div class="position-absolute z-2" style="top:25px; right:20px; color:#94a3b8; cursor:pointer;"><i class="fa-solid fa-ellipsis-vertical fs-5"></i></div>
                    
                    <h3 class="bld-title position-relative z-2">Tòa A - StayMate Sunrise</h3>
                    <div class="bld-address position-relative z-2"><i class="fa-solid fa-location-dot" style="color:var(--teal-main);"></i> Số 128 Cầu Giấy, Quận Cầu Giấy, TP. Hà Nội</div>
                    
                    <div class="manager-info text-muted position-relative z-2">
                        <span><i class="fa-solid fa-user-tie me-1"></i> Quản lý riêng:</span>
                        <span class="text-dark fw-bold">Quản lý Tòa A (Nguyễn Văn Quản) <span style="color:var(--teal-main); font-weight:600;">(nhanvien)</span></span>
                    </div>

                    <div class="stat-group position-relative z-2">
                        <div class="stat-item stat-total">
                            <i class="fa-regular fa-building fs-4"></i>
                            <div class="stat-val">15</div>
                            <div class="stat-label">Tổng</div>
                        </div>
                        <div class="stat-item stat-rented">
                            <i class="fa-solid fa-user-group fs-4"></i>
                            <div class="stat-val">12</div>
                            <div class="stat-label">Đang thuê</div>
                        </div>
                        <div class="stat-item stat-empty">
                            <i class="fa-solid fa-house-chimney fs-4"></i>
                            <div class="stat-val">3</div>
                            <div class="stat-label">Phòng trống</div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-auto position-relative z-2">
                        <a href="index.php?route=overview&building_id=A" class="btn-bld-main"><i class="fa-solid fa-arrow-right-to-bracket me-2"></i> Vào quản lý Tòa này</a>
                        <a href="#" class="btn-bld-folder"><i class="fa-regular fa-folder-open"></i></a>
                    </div>
                </div>
            </div>

            <!-- Tòa B -->
            <div class="col-12 col-lg-4">
                <div class="building-card">
                    <div class="position-relative z-2"><span class="badge-bld">BLD-B</span></div>
                    <div class="house-shadow"></div>
                    <svg class="house-img" viewBox="0 0 120 120">
                        <polygon points="10,60 60,20 110,60" fill="#94a3b8" opacity="0.3"/>
                        <rect x="25" y="40" width="70" height="70" fill="#bae6fd" rx="4"/>
                        <rect x="40" y="30" width="40" height="80" fill="#7dd3fc" rx="4"/>
                        <polygon points="15,45 60,10 105,45" fill="#0284c7"/>
                        <rect x="35" y="60" width="10" height="15" fill="#ffffff" opacity="0.8"/>
                        <rect x="75" y="60" width="10" height="15" fill="#ffffff" opacity="0.8"/>
                        <rect x="35" y="85" width="10" height="15" fill="#ffffff" opacity="0.8"/>
                        <rect x="75" y="85" width="10" height="15" fill="#ffffff" opacity="0.8"/>
                        <circle cx="20" cy="100" r="10" fill="#22c55e" opacity="0.8"/>
                        <circle cx="100" cy="95" r="15" fill="#16a34a" opacity="0.7"/>
                    </svg>
                    <div class="position-absolute z-2" style="top:25px; right:20px; color:#94a3b8; cursor:pointer;"><i class="fa-solid fa-ellipsis-vertical fs-5"></i></div>
                    
                    <h3 class="bld-title position-relative z-2">Tòa B - StayMate Emerald</h3>
                    <div class="bld-address position-relative z-2"><i class="fa-solid fa-location-dot" style="color:var(--teal-main);"></i> Số 45 Nguyễn Chí Thanh, Quận Đống Đa, TP. Hà Nội</div>
                    
                    <div class="manager-info text-muted position-relative z-2">
                        <span><i class="fa-solid fa-user-tie me-1"></i> Quản lý riêng:</span>
                        <span class="text-dark fw-bold">Quản lý Tòa B (Trần Thị Bích) <span style="color:var(--teal-main); font-weight:600;">(quanly_b)</span></span>
                    </div>

                    <div class="stat-group position-relative z-2">
                        <div class="stat-item stat-total">
                            <i class="fa-regular fa-building fs-4"></i><div class="stat-val">20</div><div class="stat-label">Tổng</div>
                        </div>
                        <div class="stat-item stat-rented">
                            <i class="fa-solid fa-user-group fs-4"></i><div class="stat-val">18</div><div class="stat-label">Đang thuê</div>
                        </div>
                        <div class="stat-item stat-empty">
                            <i class="fa-solid fa-house-chimney fs-4"></i><div class="stat-val">2</div><div class="stat-label">Phòng trống</div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-auto position-relative z-2">
                        <a href="index.php?route=overview&building_id=B" class="btn-bld-main"><i class="fa-solid fa-arrow-right-to-bracket me-2"></i> Vào quản lý Tòa này</a>
                        <a href="#" class="btn-bld-folder"><i class="fa-regular fa-folder-open"></i></a>
                    </div>
                </div>
            </div>

            <!-- Tòa C -->
            <div class="col-12 col-lg-4">
                <div class="building-card">
                    <div class="position-relative z-2"><span class="badge-bld" style="background:#fef9c3; color:#a16207; border-color:#fef08a;">BLD-C</span></div>
                    <div class="house-shadow"></div>
                    <svg class="house-img" viewBox="0 0 120 120">
                        <polygon points="10,60 60,20 110,60" fill="#94a3b8" opacity="0.3"/>
                        <rect x="25" y="40" width="70" height="70" fill="#fef08a" rx="4"/>
                        <rect x="40" y="30" width="40" height="80" fill="#fde047" rx="4"/>
                        <polygon points="15,45 60,10 105,45" fill="#ca8a04"/>
                        <rect x="35" y="60" width="10" height="15" fill="#ffffff" opacity="0.8"/>
                        <rect x="75" y="60" width="10" height="15" fill="#ffffff" opacity="0.8"/>
                        <rect x="35" y="85" width="10" height="15" fill="#ffffff" opacity="0.8"/>
                        <rect x="75" y="85" width="10" height="15" fill="#ffffff" opacity="0.8"/>
                        <circle cx="20" cy="100" r="10" fill="#22c55e" opacity="0.8"/>
                        <circle cx="100" cy="95" r="15" fill="#16a34a" opacity="0.7"/>
                    </svg>
                    <div class="position-absolute z-2" style="top:25px; right:20px; color:#94a3b8; cursor:pointer;"><i class="fa-solid fa-ellipsis-vertical fs-5"></i></div>
                    
                    <h3 class="bld-title position-relative z-2">Tòa C - StayMate Horizon</h3>
                    <div class="bld-address position-relative z-2"><i class="fa-solid fa-location-dot" style="color:var(--teal-main);"></i> Số 88 Nguyễn Trãi, Quận Thanh Xuân, TP. Hà Nội</div>
                    
                    <div class="manager-info text-muted position-relative z-2">
                        <span><i class="fa-solid fa-user-tie me-1"></i> Quản lý riêng:</span>
                        <span class="text-dark fw-bold">Quản lý Tòa C (Lê Hoàng Nam) <span style="color:var(--teal-main); font-weight:600;">(quanly_c)</span></span>
                    </div>

                    <div class="stat-group position-relative z-2">
                        <div class="stat-item stat-total">
                            <i class="fa-regular fa-building fs-4"></i><div class="stat-val">10</div><div class="stat-label">Tổng</div>
                        </div>
                        <div class="stat-item stat-rented">
                            <i class="fa-solid fa-user-group fs-4"></i><div class="stat-val">7</div><div class="stat-label">Đang thuê</div>
                        </div>
                        <div class="stat-item stat-empty">
                            <i class="fa-solid fa-house-chimney fs-4"></i><div class="stat-val">3</div><div class="stat-label">Phòng trống</div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-auto position-relative z-2">
                        <a href="index.php?route=overview&building_id=C" class="btn-bld-main"><i class="fa-solid fa-arrow-right-to-bracket me-2"></i> Vào quản lý Tòa này</a>
                        <a href="#" class="btn-bld-folder"><i class="fa-regular fa-folder-open"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL THÊM TÒA NHÀ -->
    <div class="modal fade" id="addBuildingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light rounded-top-4">
                    <h5 class="modal-title fw-bold text-teal-main"><i class="fa-solid fa-building-circle-arrow-right me-2"></i> Thêm Tòa Nhà Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form onsubmit="event.preventDefault(); alert('Đã thêm thành công!'); bootstrap.Modal.getInstance(document.getElementById('addBuildingModal')).hide();">
                        <div class="mb-3"><label class="form-label fw-bold small text-muted">Mã tòa nhà</label><input type="text" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label fw-bold small text-muted">Tên tòa nhà</label><input type="text" class="form-control" required></div>
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-teal-custom px-4">Lưu tòa nhà</button>
                        </div>
                    </form>
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
</body>
</html>