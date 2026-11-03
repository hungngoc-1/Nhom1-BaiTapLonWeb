<?php
// 0. DỮ LIỆU TÒA NHÀ
$building_id = $_GET['building_id'] ?? 'A';
$buildings_data = [
    'A' => [
        'name' => 'Tòa A - StayMate Sunrise',
        'address' => 'Số 128 Cầu Giấy, Quận Cầu Giấy, TP. Hà Nội',
        'rooms' => 15, 'rented' => 12, 'empty' => 3
    ],
    'B' => [
        'name' => 'Tòa B - StayMate Emerald',
        'address' => 'Số 45 Nguyễn Chí Thanh, Quận Đống Đa, TP. Hà Nội',
        'rooms' => 20, 'rented' => 18, 'empty' => 2
    ],
    'C' => [
        'name' => 'Tòa C - StayMate Horizon',
        'address' => 'Số 88 Nguyễn Trãi, Quận Thanh Xuân, TP. Hà Nội',
        'rooms' => 10, 'rented' => 7, 'empty' => 3
    ]
];
$current_bld = $buildings_data[$building_id] ?? $buildings_data['A'];

// 1. KẾT NỐI CƠ SỞ DỮ LIỆU
$host = 'localhost';
$db_name = 'quan_ly_nha_tro';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. TRUY VẤN LẤY SỐ LIỆU TỪ SQL
    // Tỷ lệ lấp đầy
    $stmt = $conn->query("SELECT COUNT(*) as total FROM rooms");
    $total_rooms = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    $stmt = $conn->query("SELECT COUNT(*) as rented FROM rooms WHERE status = 'rented'");
    $rented_rooms = $stmt->fetch(PDO::FETCH_ASSOC)['rented'] ?? 0;
    
    $empty_rooms = $total_rooms - $rented_rooms;
    $occupancy_rate = $total_rooms > 0 ? round(($rented_rooms / $total_rooms) * 100) : 0;

    // Thực thu
    $stmt = $conn->query("SELECT SUM(total_amount) as total_paid FROM invoices WHERE status = 'paid'");
    $total_revenue = $stmt->fetch(PDO::FETCH_ASSOC)['total_paid'] ?? 0;

    // Công nợ
    $stmt = $conn->query("SELECT SUM(total_amount) as total_unpaid, COUNT(*) as unpaid_count FROM invoices WHERE status = 'unpaid'");
    $unpaid_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_debt = $unpaid_data['total_unpaid'] ?? 0;
    $unpaid_count = $unpaid_data['unpaid_count'] ?? 0;

    // Khách & Hợp đồng
    $stmt = $conn->query("SELECT COUNT(*) as active_contracts FROM contracts WHERE status = 'active'");
    $active_contracts = $stmt->fetch(PDO::FETCH_ASSOC)['active_contracts'] ?? 0;

    // Danh sách hóa đơn nợ
    $stmt = $conn->query("
        SELECT i.id, i.month_year, i.total_amount, r.room_number, u.full_name 
        FROM invoices i 
        JOIN contracts c ON i.contract_id = c.id
        JOIN rooms r ON i.room_id = r.id
        JOIN users u ON c.tenant_id = u.id
        WHERE i.status = 'unpaid' ORDER BY i.id DESC LIMIT 5
    ");
    $unpaid_invoices_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    // Nếu máy chưa cài SQL hoặc lỗi kết nối, hiển thị số 0 để không bị vỡ thẻ HTML
    $total_rooms = 0; $rented_rooms = 0; $empty_rooms = 0; $occupancy_rate = 0;
    $total_revenue = 0; $total_debt = 0; $unpaid_count = 0; $active_contracts = 0;
}
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

        /* Navbar */
        .navbar-custom { background: #ffffff; padding: 12px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04); z-index: 10; border-bottom: 1px solid #f1f5f9;}
        .brand-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .brand-logo i { color: var(--teal-main); font-size: 2.2rem; }
        .brand-text { color: var(--teal-main); font-weight: 900; font-size: 1.4rem; line-height: 1; letter-spacing: -0.5px;}
        .brand-sub { color: #64748b; font-size: 0.75rem; font-weight: 600; }
        
        .nav-pills { gap: 10px; }
        .nav-pills .nav-link { 
            color: #475569; font-weight: 800; font-size: 0.95rem; 
            padding: 10px 20px; border-radius: 50px; transition: all 0.2s; white-space: nowrap; 
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

        /* Nút Quay Lại Bo Tròn */
        .btn-back-content { 
            background: white; border: 3px solid #ffffff; color: #334155; font-weight: 800; font-size: 0.9rem; 
            padding: 10px 26px; border-radius: 50px; transition: 0.2s; text-decoration: none; 
            display: inline-flex; align-items: center; box-shadow: 0 8px 20px rgba(0,0,0,0.08); margin-bottom: 25px;
            position: relative; z-index: 2;
        }
        .btn-back-content:hover { background: #f8fafc; border-color: var(--teal-main); color: var(--teal-main); transform: translateY(-3px); box-shadow: 0 12px 25px rgba(0,0,0,0.12);}

        /* Header Tòa Nhà */
        .header-title-wrapper { position: relative; z-index: 2; display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px;}
        .main-title { font-weight: 900; color: #0f172a; font-size: 2rem; letter-spacing: -0.5px; display: inline-flex; align-items: center;}
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
            border-radius: 30px; /* Khung bo tròn rất sâu */
            padding: 24px; position: relative; overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.12); /* Bóng đổ 3D mạnh */
            border: 4px solid #ffffff; /* Viền trắng dày nổi bật */
            height: 100%; transition: transform 0.3s ease, box-shadow 0.3s ease; backdrop-filter: blur(5px);
        }
        .overview-card:hover { transform: translateY(-8px); box-shadow: 0 25px 45px rgba(0,0,0,0.18); }
        
        .card-green { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); }
        .card-blue { background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); }
        .card-yellow { background: linear-gradient(135deg, #fef9c3 0%, #fef08a 100%); }
        .card-purple { background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%); }
        
        .card-bg-pattern { position: absolute; right: 0; bottom: 0; width: 140px; height: 140px; background-size: contain; background-repeat: no-repeat; background-position: bottom right; opacity: 0.9;}
        
        .pattern-house { background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect x="30" y="50" width="50" height="40" fill="%23bae6fd"/><polygon points="20,50 55,20 90,50" fill="%233b82f6"/><rect x="40" y="60" width="10" height="10" fill="%23ffffff"/><rect x="60" y="60" width="10" height="10" fill="%23ffffff"/><circle cx="15" cy="80" r="10" fill="%234ade80"/><circle cx="95" cy="75" r="12" fill="%2322c55e"/></svg>'); }
        .pattern-wave { background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path d="M0,70 Q25,40 50,70 T100,70 L100,100 L0,100 Z" fill="%23bae6fd" opacity="0.5"/><path d="M0,90 Q25,60 50,90 T100,90 L100,100 L0,100 Z" fill="%237dd3fc" opacity="0.5"/></svg>'); }
        .pattern-wave-yellow { background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path d="M0,70 Q25,40 50,70 T100,70 L100,100 L0,100 Z" fill="%23fde047" opacity="0.5"/><path d="M0,90 Q25,60 50,90 T100,90 L100,100 L0,100 Z" fill="%23facc15" opacity="0.5"/></svg>'); }
        .pattern-people { background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="30" cy="40" r="12" fill="%23c084fc" opacity="0.8"/><circle cx="50" cy="30" r="15" fill="%23a855f7"/><circle cx="70" cy="40" r="12" fill="%23c084fc" opacity="0.8"/><path d="M10,90 Q30,60 50,60 Q70,60 90,90" fill="%23a855f7" opacity="0.8"/></svg>'); }
        
        .overview-title { font-size: 0.8rem; font-weight: 900; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; text-transform: uppercase; position: relative; z-index: 2;}
        .overview-val { font-size: 2.5rem; font-weight: 900; line-height: 1; letter-spacing: -1px; position: relative; z-index: 2;}
        
        .overview-icon-bg {
            position: absolute; right: 24px; top: 50%; transform: translateY(-50%);
            width: 54px; height: 54px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1.4rem;
            box-shadow: 0 10px 20px rgba(0,0,0,0.15); z-index: 2;
        }
        .icon-blue { background-color: #3b82f6; }
        
        .doc-icon-wrapper {
            position: absolute; right: 24px; top: 50%; transform: translateY(-50%);
            width: 48px; height: 58px; background: #fbbf24; border-radius: 6px;
            display: flex; flex-direction: column; align-items: center; padding-top: 8px; gap: 4px;
            box-shadow: 2px 8px 15px rgba(0,0,0,0.15); z-index: 2;
        }
        .doc-line { width: 30px; height: 4px; background: white; border-radius: 2px; }
        .doc-badge { position: absolute; bottom: -10px; right: -10px; width: 26px; height: 26px; background: #eab308; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; border: 3px solid white; box-shadow: 0 4px 8px rgba(0,0,0,0.1);}

        /* Invoice Section Khung Tròn Bóng Đổ */
        .invoice-section { 
            background: rgba(255,255,255,0.95); 
            border-radius: 30px; 
            border: 4px solid #ffffff; 
            padding: 24px; 
            box-shadow: 0 15px 40px rgba(0,0,0,0.08); 
            position: relative; z-index: 2;
        }
        /* Nút Lập Hóa Đơn Tròn Viên Thuốc */
        .btn-teal-custom { background: var(--teal-main); border-radius: 50px; font-weight: 800; color: white; padding: 12px 28px; border: 2px solid var(--teal-hover); box-shadow: 0 8px 20px rgba(48, 143, 134, 0.4); font-size: 0.95rem; transition: 0.2s; }
        .btn-teal-custom:hover { background: var(--teal-hover); transform: translateY(-2px); }

        .empty-folder-illus {
            width: 150px; height: 120px; margin: 0 auto 15px auto;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 150 120"><path d="M20,100 Q75,110 130,100" stroke="%23cbd5e1" stroke-width="4" fill="none" stroke-linecap="round"/><path d="M10,80 Q30,40 50,70" fill="%236ee7b7"/><path d="M130,70 Q110,30 90,60" fill="%2334d399"/><path d="M40,50 L60,50 L70,40 L120,40 L120,90 L40,90 Z" fill="%2399f6e4"/><path d="M30,60 L70,60 L80,50 L130,50 L110,100 L20,100 Z" fill="%235eead4"/><path d="M40,30 L45,15" stroke="%23fbbf24" stroke-width="3" stroke-linecap="round"/><path d="M90,20 L105,10" stroke="%23fbbf24" stroke-width="3" stroke-linecap="round"/></svg>');
            background-size: contain; background-repeat: no-repeat; background-position: center;
        }

        .footer-custom { background: #ffffff; padding: 20px 0; color: #64748b; font-size: 0.85rem; font-weight: 700; margin-top: auto; border-top: 1px solid #f1f5f9; position: relative; z-index: 10;}
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid px-4 px-xl-5 d-flex justify-content-between align-items-center">
            <a class="brand-logo me-5" href="index.php?route=dashboard">
                <i class="fa-solid fa-house-chimney text-teal-main"></i>
                <div>
                    <div class="brand-text">StayMate</div>
                    <div class="brand-sub">Quản Lý Nhà Trọ</div>
                </div>
            </a>

            <!-- MENU ĐÃ ĐƯỢC CHỈNH SỬA LẠI THEO CHUẨN -->
            <ul class="nav nav-pills me-auto d-none d-lg-flex">
                <li class="nav-item"><a class="nav-link active" href="index.php?route=overview"><i class="fa-solid fa-border-all"></i> Tổng quan</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=rooms"><i class="fa-solid fa-bed"></i> Phòng trọ</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=tenants"><i class="fa-solid fa-users"></i> Khách thuê</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=contracts"><i class="fa-solid fa-file-signature"></i> Hợp đồng</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=utilities"><i class="fa-solid fa-bolt"></i> Điện Nước</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=invoices"><i class="fa-solid fa-file-invoice-dollar"></i> Hóa đơn</a></li>
                <li class="nav-item"><a class="nav-link" href="TrangXemPhong/" target="_blank"><i class="fa-solid fa-eye"></i> Xem phòng</a></li>
            </ul>

            <div class="d-flex align-items-center gap-4">
                <div class="position-relative cursor-pointer" style="color: #64748b;">
                    <i class="fa-solid fa-bell fs-4"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white">8</span>
                </div>
                <div class="dropdown">
                    <div class="d-flex align-items-center gap-2 cursor-pointer" data-bs-toggle="dropdown">
                        <div class="avatar-circle">AS</div>
                        
                        <div class="text-start d-none d-sm-block lh-sm">
                            <div class="fw-bold text-dark" style="font-size: 0.9rem;">Quản trị viên Hệ thống <i class="fa-solid fa-angle-down ms-1 text-muted fs-6"></i></div>
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

    <div class="container-fluid px-4 px-xl-5 py-4 position-relative">
        
        <a href="index.php?route=dashboard" class="btn-back-content">
            <i class="fa-solid fa-arrow-left me-2"></i> Quay lại Tòa nhà
        </a>

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
                        <?php echo $current_bld['name']; ?>
                        <span class="title-sparkle ts-1"></span>
                        <span class="title-sparkle ts-2"></span>
                    </span>
                </div>
                <div class="bld-address-txt"><i class="fa-solid fa-location-dot text-teal-main me-1"></i> <?php echo $current_bld['address']; ?></div>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <div class="date-badge d-none d-md-block position-relative z-2">
                    <i class="fa-regular fa-calendar-days me-2"></i> Hôm nay, <?php echo date('d/m/Y'); ?>
                </div>
            </div>
            
            <div class="header-house-graphic d-none d-lg-block"></div>
        </div>

        <div class="row g-4 mb-4 position-relative z-2">
            <div class="col-12 col-md-6 col-lg-3">
                <div class="overview-card card-green">
                    <div class="card-bg-pattern pattern-house"></div>
                    <div class="overview-title text-success"><i class="fa-regular fa-calendar-check fs-6"></i> TỶ LỆ LẤP ĐẦY</div>
                    <div class="d-flex align-items-baseline mb-2 position-relative z-2">
                        <span class="overview-val text-dark me-2"><?php echo $current_bld['rented']; ?> / <?php echo $current_bld['rooms']; ?></span> 
                        <span class="fw-bold text-muted small">phòng</span>
                    </div>
                    <?php $percent = round(($current_bld['rented'] / $current_bld['rooms']) * 100); ?>
                    <div class="progress mb-2 position-relative z-2" style="height: 8px; background-color: rgba(255,255,255,0.7); border-radius: 4px;">
                        <div class="progress-bar bg-success" style="width: <?php echo $percent; ?>%; border-radius: 4px;"></div>
                    </div>
                    <div class="d-flex justify-content-between text-success position-relative z-2" style="font-size: 0.8rem; font-weight: 800;">
                        <span><i class="fa-solid fa-circle-check"></i> <?php echo $percent; ?>% đang thuê</span>
                        <span class="text-muted"><?php echo $current_bld['empty']; ?> trống</span>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-md-6 col-lg-3">
                <div class="overview-card card-blue">
                    <div class="card-bg-pattern pattern-wave"></div>
                    <div class="overview-title text-primary"><i class="fa-solid fa-calendar-days fs-6"></i> THỰC THU 2026</div>
                    <div class="overview-val text-dark mt-3 position-relative z-2">0 đ</div>
                    <div class="overview-icon-bg icon-blue"><i class="fa-solid fa-wallet"></i></div>
                </div>
            </div>
            
            <div class="col-12 col-md-6 col-lg-3">
                <div class="overview-card card-yellow">
                    <div class="card-bg-pattern pattern-wave-yellow"></div>
                    <div class="overview-title text-warning" style="color: #b45309 !important;"><i class="fa-solid fa-coins fs-6"></i> CÔNG NỢ CHƯA THU</div>
                    <div class="overview-val mt-2 position-relative z-2" style="color: #b45309;">0 đ</div>
                    <div class="mt-3 position-relative z-2" style="font-size: 0.8rem; font-weight: 800; color: #b45309;">
                        <i class="fa-regular fa-clock"></i> Tồn đọng: <span class="text-danger">0 hóa đơn</span>
                    </div>
                    <div class="doc-icon-wrapper">
                        <div class="doc-line"></div>
                        <div class="doc-line" style="width: 20px;"></div>
                        <div class="doc-line"></div>
                        <div class="doc-badge">!</div>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-md-6 col-lg-3">
                <div class="overview-card card-purple">
                    <div class="card-bg-pattern pattern-people"></div>
                    <div class="overview-title" style="color: #7e22ce;"><i class="fa-solid fa-users fs-6"></i> KHÁCH & HỢP ĐỒNG</div>
                    <div class="d-flex align-items-baseline mt-3 position-relative z-2">
                        <span class="overview-val text-dark me-2">0</span> 
                        <span class="fw-bold text-muted small">hợp đồng</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="invoice-section">
            <div class="d-flex justify-content-between align-items-center border-bottom border-light pb-3 mb-4">
                <div class="fw-bold text-dark fs-6"><i class="fa-solid fa-file-invoice-dollar me-2" style="color: var(--teal-main);"></i> Hóa đơn Cần Thu Nợ (0)</div>
                <button class="btn btn-teal-custom"><i class="fa-solid fa-plus me-2"></i> Lập hóa đơn</button>
            </div>
            
            <div class="text-center py-5 text-muted">
                <div class="empty-folder-illus"></div>
                <p class="mb-0 fw-bold" style="color: #64748b; font-size: 0.95rem;">Chưa có dữ liệu hóa đơn đang nợ trong Tòa nhà này.</p>
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
</body>
</html>