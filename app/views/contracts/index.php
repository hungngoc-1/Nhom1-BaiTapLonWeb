<?php
$nextMonthFirstDay = date('Y-m-01', strtotime('first day of next month'));
$nextYearEndDay = date('Y-m-01', strtotime($nextMonthFirstDay . ' +12 months'));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayMate - Quản lý Hợp đồng Thuê phòng</title>
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

        /* Navbar chuẩn Trang Tổng quan */
        .navbar-custom { background: #ffffff; padding: 12px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04); z-index: 10; border-bottom: 1px solid #f1f5f9;}
        .brand-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .brand-logo i { color: var(--teal-main); font-size: 2.2rem; }
        .brand-text { color: var(--teal-main); font-weight: 900; font-size: 1.4rem; line-height: 1; letter-spacing: -0.5px;}
        .brand-sub { color: #64748b; font-size: 0.75rem; font-weight: 600; }
        .text-teal-main { color: var(--teal-main) !important; }
        
        .nav-pills { gap: 10px; }
        .nav-pills .nav-link { 
            color: #475569; font-weight: 800; font-size: 0.95rem; 
            padding: 10px 20px; border-radius: 50px; transition: all 0.2s; white-space: nowrap; 
        }
        .nav-pills .nav-link i { margin-right: 6px; }
        .nav-pills .nav-link:hover { color: var(--teal-main); background: #f0fdfa; }
        .nav-pills .nav-link.active { background-color: var(--teal-main); color: white; box-shadow: 0 6px 15px rgba(48, 143, 134, 0.35); }

        .avatar-circle {
            width: 46px; height: 46px;
            background-color: var(--teal-main);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 900; font-size: 1rem;
            border: 3px solid #ffffff; 
            box-shadow: 0 6px 15px rgba(48, 143, 134, 0.4);
        }

        .btn-back-content { 
            background: white; border: 3px solid #ffffff; color: #334155; font-weight: 800; font-size: 0.9rem; 
            padding: 10px 26px; border-radius: 50px; transition: 0.2s; text-decoration: none; 
            display: inline-flex; align-items: center; box-shadow: 0 8px 20px rgba(0,0,0,0.08); margin-bottom: 20px;
            position: relative; z-index: 2;
        }
        .btn-back-content:hover { background: #f8fafc; border-color: var(--teal-main); color: var(--teal-main); transform: translateY(-2px); }

        .header-title-wrapper { position: relative; z-index: 2; display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 26px;}
        .main-title { font-weight: 900; color: #0f172a; font-size: 2rem; letter-spacing: -0.5px; display: inline-flex; align-items: center;}
        .bld-highlight { color: var(--teal-main); margin-left: 8px; position: relative;}
        .title-sparkle { position: absolute; background-color: #fde047; border-radius: 2px; }
        .ts-1 { width: 4px; height: 12px; top: -10px; right: -15px; transform: rotate(20deg); }
        .ts-2 { width: 12px; height: 4px; top: 15px; right: -25px; transform: rotate(15deg); }
        .bld-address-txt { font-size: 0.95rem; font-weight: 700; color: #64748b; margin-top: 5px;}
        .date-badge { background: white; border-radius: 50px; padding: 12px 28px; font-weight: 800; color: #475569; font-size: 0.9rem; box-shadow: 0 8px 20px rgba(0,0,0,0.08); border: 3px solid #ffffff; }

        .header-house-graphic {
            position: absolute; right: 0; top: -50px; width: 300px; height: 200px; z-index: 1; pointer-events: none;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 200"><path d="M0,180 Q150,150 300,180 L300,200 L0,200 Z" fill="%23dcfce7"/><rect x="120" y="80" width="80" height="100" fill="%23e0f2fe"/><polygon points="100,80 160,30 220,80" fill="%230284c7"/><polygon points="160,30 220,80 250,80 190,30" fill="%2338bdf8"/><rect x="140" y="100" width="15" height="20" fill="%23ffffff"/><rect x="165" y="100" width="15" height="20" fill="%23ffffff"/><rect x="140" y="140" width="15" height="40" fill="%23ffffff"/><circle cx="50" cy="140" r="30" fill="%234ade80"/><circle cx="80" cy="150" r="40" fill="%2322c55e"/><circle cx="240" cy="140" r="35" fill="%2322c55e"/><circle cx="270" cy="150" r="25" fill="%2316a34a"/></svg>');
            background-size: contain; background-repeat: no-repeat; background-position: bottom right;
        }

        /* 4 Thẻ thống kê giống hệt Trang Tổng quan */
        .overview-card {
            border-radius: 30px;
            padding: 22px 24px; position: relative; overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.12);
            border: 4px solid #ffffff;
            height: 100%; transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .overview-card:hover { transform: translateY(-6px); box-shadow: 0 22px 42px rgba(0,0,0,0.16); }
        .card-green { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); }
        .card-blue { background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); }
        .card-yellow { background: linear-gradient(135deg, #fef9c3 0%, #fef08a 100%); }
        .card-purple { background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%); }
        .overview-title { font-size: 0.8rem; font-weight: 900; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; text-transform: uppercase; position: relative; z-index: 2;}
        .overview-val { font-size: 2.2rem; font-weight: 900; line-height: 1; letter-spacing: -1px; position: relative; z-index: 2;}

        /* Khung chính danh sách hợp đồng */
        .contract-section { 
            background: rgba(255,255,255,0.96); 
            border-radius: 30px; 
            border: 4px solid #ffffff; 
            padding: 24px; 
            box-shadow: 0 15px 40px rgba(0,0,0,0.08); 
            position: relative; z-index: 2;
        }

        .btn-teal-custom { background: var(--teal-main); border-radius: 50px; font-weight: 800; color: white; padding: 10px 24px; border: 2px solid var(--teal-hover); box-shadow: 0 8px 20px rgba(48, 143, 134, 0.35); font-size: 0.9rem; transition: 0.2s; text-decoration: none; display: inline-flex; align-items: center;}
        .btn-teal-custom:hover { background: var(--teal-hover); color: white; transform: translateY(-2px); }

        .btn-outline-teal { background: #f0fdfa; color: var(--teal-main); border: 1.5px solid #99f6e4; border-radius: 50px; font-weight: 800; padding: 8px 18px; font-size: 0.85rem; transition: 0.2s; text-decoration: none;}
        .btn-outline-teal:hover { background: var(--teal-main); color: white; }

        .form-control, .form-select { border-radius: 14px; border: 1.5px solid #e2e8f0; padding: 9px 14px; font-weight: 600; font-size: 0.88rem; }
        .form-control:focus, .form-select:focus { border-color: var(--teal-main); box-shadow: 0 0 0 3px rgba(48, 143, 134, 0.15); }

        .table-custom thead th { background-color: #f0fdfa; color: var(--teal-main); font-size: 0.78rem; font-weight: 900; text-transform: uppercase; padding: 14px 16px; border-bottom: 2px solid #ccfbf1; }
        .table-custom tbody td { padding: 15px 16px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 600; }
        .table-custom tbody tr:hover { background-color: #f8fafc; }

        .badge-room { background: var(--teal-main); color: white; padding: 5px 12px; border-radius: 50px; font-weight: 800; font-size: 0.8rem; box-shadow: 0 4px 10px rgba(48, 143, 134, 0.25); }
        .badge-pill { padding: 5px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 800; display: inline-flex; align-items: center; gap: 4px; }
        .status-active { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .status-terminated { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
        .status-expired { background: #fef9c3; color: #b45309; border: 1px solid #fde047; }

        .btn-act { border-radius: 50px; padding: 5px 12px; font-size: 0.78rem; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; border: 1.5px solid transparent; transition: 0.2s; }
        .btn-act-teal { background: #ccfbf1; color: #0f766e; border-color: #99f6e4; }
        .btn-act-teal:hover { background: var(--teal-main); color: white; }
        .btn-act-warn { background: #fef3c7; color: #b45309; border-color: #fde68a; }
        .btn-act-warn:hover { background: #f59e0b; color: white; }
        .btn-act-info { background: #e0f2fe; color: #0369a1; border-color: #bae6fd; }
        .btn-act-info:hover { background: #0284c7; color: white; }
        .btn-act-danger { background: #fee2e2; color: #dc2626; border-color: #fecaca; }
        .btn-act-danger:hover { background: #ef4444; color: white; }

        .footer-custom { background: #ffffff; padding: 20px 0; color: #64748b; font-size: 0.85rem; font-weight: 700; margin-top: auto; border-top: 1px solid #f1f5f9; position: relative; z-index: 10;}
    </style>
</head>
<body>

    <!-- Navbar chuẩn StayMate -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container-fluid px-4 px-xl-5 d-flex justify-content-between align-items-center">
            <a class="brand-logo me-5" href="index.php?route=dashboard">
                <i class="fa-solid fa-house-chimney text-teal-main"></i>
                <div>
                    <div class="brand-text">StayMate</div>
                    <div class="brand-sub">Quản Lý Nhà Trọ</div>
                </div>
            </a>

            <ul class="nav nav-pills me-auto d-none d-lg-flex">
                <li class="nav-item"><a class="nav-link" href="index.php?route=dashboard"><i class="fa-solid fa-city"></i> Tòa nhà</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=overview"><i class="fa-solid fa-border-all"></i> Tổng quan</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=rooms"><i class="fa-solid fa-bed"></i> Phòng trọ</a></li>
                <li class="nav-item"><a class="nav-link active" href="index.php?route=contracts"><i class="fa-solid fa-file-signature"></i> Hợp đồng</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=utilities"><i class="fa-solid fa-bolt"></i> Điện Nước</a></li>
                <li class="nav-item"><a class="nav-link" href="TrangXemPhong/" target="_blank"><i class="fa-solid fa-eye"></i> Xem phòng khách</a></li>
            </ul>

            <div class="d-flex align-items-center gap-4">
                <div class="dropdown">
                    <div class="d-flex align-items-center gap-2" style="cursor:pointer;" data-bs-toggle="dropdown">
                        <div class="avatar-circle">HN</div>
                        <div class="text-start d-none d-sm-block lh-sm">
                            <div class="fw-bold text-dark" style="font-size: 0.9rem;">Quản trị viên Hệ thống <i class="fa-solid fa-angle-down ms-1 text-muted fs-6"></i></div>
                            <div class="text-muted" style="font-size: 0.75rem;">Hủng Văn Ngọc (Admin)</div>
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

        <a href="index.php?route=overview" class="btn-back-content">
            <i class="fa-solid fa-arrow-left me-2"></i> Quay lại Tổng quan
        </a>

        <!-- Header giống Trang Tổng quan -->
        <div class="header-title-wrapper">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1" style="font-size: 0.85rem; font-weight: 700;">
                        <li class="breadcrumb-item"><a href="index.php?route=dashboard" class="text-teal-main text-decoration-none"><i class="fa-solid fa-house"></i> Hệ thống Quản lý</a></li>
                        <li class="breadcrumb-item text-muted" aria-current="page">Phân hệ Hợp đồng Thuê phòng</li>
                    </ol>
                </nav>
                <div class="main-title">
                    Quản lý Hợp đồng -
                    <span class="bld-highlight">
                        Ký Điện tử &amp; Quyết toán Cọc
                        <span class="title-sparkle ts-1"></span>
                        <span class="title-sparkle ts-2"></span>
                    </span>
                </div>
                <div class="bld-address-txt">
                    <i class="fa-solid fa-shield-halved text-teal-main me-1"></i>
                    Quy tắc chuẩn: Cọc = 1 tháng tiền phòng • Vào ở ≤ ngày 15 tính 100%, sau ngày 15 tính 50% • Rời trước hạn mất cọc
                </div>
            </div>

            <div class="d-flex align-items-center gap-3 position-relative z-2">
                <div class="date-badge d-none d-md-block">
                    <i class="fa-regular fa-calendar-days me-2 text-teal-main"></i> Hôm nay, <?= date('d/m/Y') ?>
                </div>
            </div>

            <div class="header-house-graphic d-none d-lg-block"></div>
        </div>

        <!-- Thông báo Flash -->
        <?php if (!empty($flash_error)): ?>
            <div class="alert alert-danger rounded-4 border-0 shadow-sm fw-bold position-relative z-2"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($flash_error) ?></div>
        <?php endif; ?>
        <?php if (!empty($flash_warning)): ?>
            <div class="alert alert-warning rounded-4 border-0 shadow-sm fw-bold position-relative z-2"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= htmlspecialchars($flash_warning) ?></div>
        <?php endif; ?>
        <?php if (!empty($flash_success)): ?>
            <div class="alert alert-success rounded-4 border-0 shadow-sm fw-bold position-relative z-2"><i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($flash_success) ?></div>
        <?php endif; ?>

        <!-- 4 Thẻ Thống Kê Hợp Đồng (cùng style với Trang Tổng quan) -->
        <div class="row g-4 mb-4 position-relative z-2">
            <div class="col-12 col-md-6 col-lg-3">
                <div class="overview-card card-green">
                    <div class="overview-title text-success"><i class="fa-solid fa-file-circle-check fs-6"></i> HỢP ĐỒNG HIỆU LỰC</div>
                    <div class="d-flex align-items-baseline mt-2">
                        <span class="overview-val text-dark me-2"><?= (int)($stats['active'] ?? 0) ?></span>
                        <span class="fw-bold text-muted small">/ <?= (int)($stats['total'] ?? 0) ?> tổng HĐ</span>
                    </div>
                    <div class="mt-3 text-success" style="font-size: 0.8rem; font-weight: 800;">
                        <i class="fa-solid fa-circle-check me-1"></i> Phòng đang có khách ở
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="overview-card card-blue">
                    <div class="overview-title text-primary"><i class="fa-solid fa-vault fs-6"></i> TIỀN CỌC ĐANG GIỮ</div>
                    <div class="overview-val text-dark mt-2" style="font-size: 1.85rem;">
                        <?= number_format((float)($stats['total_deposit_held'] ?? 0), 0, ',', '.') ?> đ
                    </div>
                    <div class="mt-3 text-primary" style="font-size: 0.8rem; font-weight: 800;">
                        <i class="fa-solid fa-coins me-1"></i> Cọc = 01 tháng tiền phòng
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="overview-card card-yellow">
                    <div class="overview-title" style="color: #b45309;"><i class="fa-regular fa-bell fs-6"></i> SẮP HẾT HẠN (≤ 30 NGÀY)</div>
                    <div class="d-flex align-items-baseline mt-2">
                        <span class="overview-val me-2" style="color: #b45309;"><?= (int)($stats['expiring_soon'] ?? 0) ?></span>
                        <span class="fw-bold text-muted small">hợp đồng</span>
                    </div>
                    <div class="mt-3" style="font-size: 0.8rem; font-weight: 800; color: #b45309;">
                        <i class="fa-regular fa-clock me-1"></i> Nhắc gia hạn trước 1 tháng
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="overview-card card-purple">
                    <div class="overview-title" style="color: #7e22ce;"><i class="fa-solid fa-file-circle-xmark fs-6"></i> ĐÃ THANH LÝ</div>
                    <div class="d-flex align-items-baseline mt-2">
                        <span class="overview-val text-dark me-2"><?= (int)($stats['terminated'] ?? 0) ?></span>
                        <span class="fw-bold text-muted small">hợp đồng</span>
                    </div>
                    <div class="mt-3" style="font-size: 0.8rem; font-weight: 800; color: #7e22ce;">
                        <i class="fa-solid fa-scale-balanced me-1"></i> Tự động hoàn / thu cọc
                    </div>
                </div>
            </div>
        </div>

        <!-- Khung Bảng Danh sách & Bộ lọc Hợp đồng -->
        <div class="contract-section">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 border-bottom border-light pb-3 mb-3">
                <form action="index.php" method="GET" class="d-flex flex-wrap gap-2 align-items-center">
                    <input type="hidden" name="route" value="contracts">
                    <div class="input-group" style="width: 260px;">
                        <span class="input-group-text bg-white border-end-0" style="border-radius: 14px 0 0 14px;"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="keyword" class="form-control border-start-0" style="border-radius: 0 14px 14px 0;" placeholder="Tìm mã HĐ, phòng, tên khách..." value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
                    </div>
                    <select name="status" class="form-select" style="width: 185px;">
                        <option value="">Tất cả trạng thái</option>
                        <?php foreach ($statuses as $val => $label): ?>
                            <option value="<?= $val ?>" <?= ($_GET['status'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn-outline-teal"><i class="fa-solid fa-filter me-1"></i> Lọc</button>
                    <?php if (!empty($_GET['keyword']) || !empty($_GET['status'])): ?>
                        <a href="index.php?route=contracts" class="btn btn-sm btn-light rounded-pill px-3 fw-bold">Xóa lọc</a>
                    <?php endif; ?>
                </form>

                <button type="button" class="btn-teal-custom" data-bs-toggle="modal" data-bs-target="#contractModal">
                    <i class="fa-solid fa-file-circle-plus me-2"></i> Lập Hợp đồng mới
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Mã HĐ</th>
                            <th>Phòng</th>
                            <th>Khách thuê</th>
                            <th>Ngày cọc &amp; Hiệu lực</th>
                            <th>Giá thuê / Tiền cọc</th>
                            <th>Hình thức ký</th>
                            <th>Trạng thái</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($contracts)): ?>
                            <?php foreach ($contracts as $c): ?>
                                <?php
                                $depStatus = $c['deposit_status'] ?? 'held';
                                $renStatus = $c['renewal_status'] ?? 'none';
                                $sigType = $c['signature_type'] ?? 'online';
                                $sigStatus = $c['signature_status'] ?? 'pending';
                                $depositDate = !empty($c['deposit_date']) ? $c['deposit_date'] : $c['start_date'];
                                $monthlyRent = (float)($c['monthly_rent'] ?? $c['room_price'] ?? $c['deposit_amount']);
                                ?>
                                <tr>
                                    <td>
                                        <a href="index.php?route=contracts/detail&id=<?= (int)$c['id'] ?>" class="fw-bold text-teal-main text-decoration-none">
                                            <i class="fa-solid fa-file-lines me-1"></i><?= htmlspecialchars($c['contract_code']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge-room"><?= htmlspecialchars($c['room_number'] ?? 'N/A') ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($c['full_name'] ?? 'Chưa rõ') ?></div>
                                        <div class="text-muted small"><i class="fa-solid fa-phone me-1"></i><?= htmlspecialchars($c['phone'] ?? '') ?></div>
                                    </td>
                                    <td class="small">
                                        <div class="text-muted">Ngày cọc: <strong class="text-dark"><?= date('d/m/Y', strtotime($depositDate)) ?></strong></div>
                                        <div>Hiệu lực: <strong class="text-teal-main"><?= date('d/m/Y', strtotime($c['start_date'])) ?></strong> &rarr; <strong class="text-danger"><?= date('d/m/Y', strtotime($c['end_date'])) ?></strong></div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-danger"><?= number_format($monthlyRent, 0, ',', '.') ?> đ/tháng</div>
                                        <div class="small text-muted">
                                            Cọc: <strong><?= number_format((float)$c['deposit_amount'], 0, ',', '.') ?> đ</strong>
                                            <?php if ($depStatus === 'refunded'): ?>
                                                <span class="badge bg-success-subtle text-success rounded-pill ms-1">Đã hoàn cọc</span>
                                            <?php elseif ($depStatus === 'forfeited'): ?>
                                                <span class="badge bg-danger-subtle text-danger rounded-pill ms-1">Mất cọc</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($sigType === 'online'): ?>
                                            <span class="badge-pill" style="background:#e0f2fe;color:#0369a1;"><i class="fa-solid fa-pen-nib"></i> Điện tử</span>
                                        <?php else: ?>
                                            <span class="badge-pill" style="background:#f1f5f9;color:#334155;"><i class="fa-solid fa-print"></i> Ký giấy</span>
                                        <?php endif; ?>

                                        <?php if (!empty($c['scan_file'])): ?>
                                            <a href="<?= htmlspecialchars($c['scan_file']) ?>" target="_blank" class="badge-pill bg-success text-white text-decoration-none ms-1">
                                                <i class="fa-solid fa-image"></i> Bản Scan
                                            </a>
                                        <?php elseif ($sigStatus === 'signed'): ?>
                                            <span class="badge-pill status-active ms-1"><i class="fa-solid fa-check"></i> Đã ký</span>
                                        <?php else: ?>
                                            <span class="badge-pill status-expired ms-1"><i class="fa-regular fa-clock"></i> Chờ ký</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $stClass = 'status-active';
                                        if ($c['status'] === 'terminated') $stClass = 'status-terminated';
                                        elseif ($c['status'] === 'expired') $stClass = 'status-expired';
                                        ?>
                                        <span class="badge-pill <?= $stClass ?>"><?= htmlspecialchars($statuses[$c['status']] ?? $c['status']) ?></span>

                                        <?php if ($renStatus === 'renewed'): ?>
                                            <span class="badge-pill ms-1" style="background:#ccfbf1;color:#0f766e;"><i class="fa-solid fa-rotate"></i> Đã gia hạn</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex flex-wrap gap-1 justify-content-end">
                                            <a href="index.php?route=contracts/detail&id=<?= (int)$c['id'] ?>" class="btn-act btn-act-teal" title="Xem điều khoản A4, Ký online, In hoặc tải bản Scan">
                                                <i class="fa-solid fa-file-signature"></i> Bản HĐ / Ký
                                            </a>
                                            <?php if ($c['status'] === 'active'): ?>
                                                <button type="button" class="btn-act btn-act-warn"
                                                        onclick="openTerminateModal(<?= (int)$c['id'] ?>, '<?= htmlspecialchars($c['contract_code'], ENT_QUOTES) ?>', '<?= htmlspecialchars($c['room_number'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($c['end_date'], ENT_QUOTES) ?>', <?= (float)$c['deposit_amount'] ?>)">
                                                    <i class="fa-solid fa-hand-holding-dollar"></i> Thanh lý
                                                </button>
                                            <?php endif; ?>
                                            <a href="index.php?route=contracts/edit&id=<?= (int)$c['id'] ?>" class="btn-act btn-act-info" title="Sửa hợp đồng">
                                                <i class="fa-solid fa-pen"></i> Sửa
                                            </a>
                                            <form method="POST" action="index.php?route=contracts/delete" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa hợp đồng <?= htmlspecialchars($c['contract_code'], ENT_QUOTES) ?> không?');">
                                                <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                                <button type="submit" class="btn-act btn-act-danger" title="Xóa hợp đồng"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fa-regular fa-folder-open fs-1 text-teal-main opacity-50 mb-2 d-block"></i>
                                    Không tìm thấy hợp đồng nào khớp với yêu cầu.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- MODAL 1: THANH LÝ HỢP ĐỒNG (Tự động tính hoàn 100% cọc hoặc mất cọc) -->
    <div class="modal fade" id="terminateContractModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="index.php?route=contracts/terminate" class="modal-content" style="border-radius: 24px; border: 3px solid #fff;">
                <input type="hidden" name="id" id="term_contract_id">
                <input type="hidden" id="term_end_date_raw">
                <input type="hidden" id="term_deposit_raw">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-teal-main">
                        <i class="fa-solid fa-scale-balanced me-2"></i>Thanh lý Hợp đồng — <span id="term_contract_code"></span> (P.<span id="term_room_number"></span>)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between small p-3 rounded-4 bg-light border mb-3">
                        <span>Hạn kết thúc HĐ: <strong id="term_end_date_display" class="text-dark"></strong></span>
                        <span>Tiền cọc đang giữ: <strong id="term_deposit_display" class="text-danger"></strong></span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Ngày khách trả phòng / rời đi thực tế <span class="text-danger">*</span></label>
                        <input type="date" name="move_out_date" id="term_move_out_date" class="form-control" value="<?= date('Y-m-d') ?>" required onchange="previewDepositSettlement()">
                    </div>

                    <div id="term_settlement_alert" class="alert alert-warning rounded-4 py-2 px-3 small fw-bold mb-3"></div>

                    <div class="mb-1">
                        <label class="form-label small fw-bold">Ghi chú bàn giao</label>
                        <input type="text" name="termination_reason" class="form-control" placeholder="Bàn giao chìa khóa, chốt chỉ số điện nước...">
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark"><i class="fa-solid fa-check me-1"></i> Xác nhận Thanh lý</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: LẬP HỢP ĐỒNG THUÊ PHÒNG MỚI (Giống đầy đủ chức năng quanly-nhatro cũ) -->
    <div class="modal fade" id="contractModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form method="POST" action="index.php?route=contracts/store" class="modal-content" style="border-radius: 26px; border: 4px solid #fff;">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-teal-main">
                        <i class="fa-solid fa-file-circle-plus me-2"></i>Lập Hợp đồng Thuê phòng mới
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold mb-1">Phòng trọ <span class="text-danger">*</span></label>
                            <select name="room_id" id="contract_room_id" class="form-select" required onchange="onContractRoomSelect(this)">
                                <option value="">-- Chọn phòng --</option>
                                <?php foreach ($rooms as $r): ?>
                                    <option value="<?= (int)$r['id'] ?>" data-price="<?= (float)$r['price'] ?>">
                                        <?= htmlspecialchars($r['room_number']) ?> - <?= number_format((float)$r['price'], 0, ',', '.') ?> đ (<?= $r['status'] === 'available' ? 'Đang trống' : 'Đang thuê' ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold mb-1">Khách thuê <span class="text-danger">*</span></label>
                            <select name="tenant_id" class="form-select" required>
                                <option value="">-- Chọn khách thuê --</option>
                                <?php foreach ($tenants as $t): ?>
                                    <option value="<?= (int)$t['id'] ?>">
                                        <?= htmlspecialchars($t['full_name']) ?> - <?= htmlspecialchars($t['phone']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-bold mb-1">Ngày đặt cọc <span class="text-danger">*</span></label>
                            <input type="date" name="deposit_date" id="contract_deposit_date" class="form-control" value="<?= date('Y-m-d') ?>" required onchange="onDepositDateChange()">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold mb-1">Ngày bắt đầu <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="contract_start_date" class="form-control fw-bold text-teal-main" value="<?= $nextMonthFirstDay ?>" required onchange="syncContractEndDate()">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold mb-1">Thời hạn thuê</label>
                            <select name="duration_months" id="contract_duration_months" class="form-select" onchange="syncContractEndDate()">
                                <option value="6">6 tháng</option>
                                <option value="12" selected>12 tháng</option>
                                <option value="24">24 tháng</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold mb-1">Ngày kết thúc <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="contract_end_date" class="form-control bg-light fw-bold text-danger" value="<?= $nextYearEndDay ?>" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold mb-1">Giá thuê/tháng (VNĐ) <span class="text-danger">*</span></label>
                            <input type="number" step="50000" min="0" name="monthly_rent" id="contract_monthly_rent" class="form-control" required oninput="syncDepositAndPreview()">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold mb-1">Tiền cọc (= 1 tháng)</label>
                            <input type="number" name="deposit_amount" id="contract_deposit" class="form-control bg-light fw-bold text-teal-main" value="0" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold mb-1">Hình thức ký hợp đồng</label>
                            <select name="signature_type" class="form-select">
                                <option value="online">Hợp đồng Điện tử (Ký Online)</option>
                                <option value="paper">Hợp đồng Ký giấy (In &amp; Lưu Scan)</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold mb-1">Giá điện (đ/kWh)</label>
                            <input type="number" name="electricity_rate" class="form-control" value="3500">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold mb-1">Giá nước (đ/khối)</label>
                            <input type="number" name="water_rate" class="form-control" value="20000">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold mb-1">Phí dịch vụ (đ/tháng)</label>
                            <input type="number" name="service_fee" id="contract_service_fee" class="form-control" value="150000" oninput="previewMoveInCalculation()">
                        </div>

                        <div class="col-12">
                            <div id="move_in_preview_box" class="p-3 rounded-4 small fw-semibold" style="background:#f0fdfa;border:1.5px solid #99f6e4;color:#0f766e;">
                                <i class="fa-solid fa-calculator me-1"></i> Chọn phòng để tự động tính Tiền cọc 1 tháng và Hóa đơn đầu kỳ (vào ở ≤ ngày 15 tính 100%, sau ngày 15 tính 50% tiền phòng).
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold mb-1">Ghi chú thỏa thuận riêng</label>
                            <input type="text" name="notes" class="form-control" placeholder="VD: Bàn giao 02 thẻ từ, điều hòa, bình nóng lạnh...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn-teal-custom"><i class="fa-solid fa-pen-to-square me-2"></i> Tạo &amp; Mở Bản Hợp đồng để Ký</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer-custom container-fluid px-4 px-xl-5 d-flex justify-content-between align-items-center mt-4">
        <div><i class="fa-solid fa-building me-1" style="color:var(--teal-main);"></i> © 2026 StayMate - Quản lý Nhà trọ Chuyên nghiệp</div>
        <div class="d-flex gap-4">
            <span><i class="fa-solid fa-shield-halved me-1" style="color:var(--teal-main);"></i> WAMP MVC Architecture</span>
            <span><i class="fa-solid fa-file-signature me-1 text-teal-main"></i> Hợp đồng Điện tử &amp; Quyết toán Tự động</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function formatVnd(n) {
        return new Intl.NumberFormat('vi-VN').format(Math.round(n || 0)) + ' đ';
    }

    function onContractRoomSelect(selectEl) {
        const opt = selectEl.options[selectEl.selectedIndex];
        if (opt && opt.dataset.price) {
            document.getElementById('contract_monthly_rent').value = opt.dataset.price;
            syncDepositAndPreview();
        }
    }

    function syncDepositAndPreview() {
        const rent = parseFloat(document.getElementById('contract_monthly_rent').value || 0);
        document.getElementById('contract_deposit').value = rent;
        previewMoveInCalculation();
    }

    function onDepositDateChange() {
        const depVal = document.getElementById('contract_deposit_date').value;
        if (!depVal) return;
        const parts = depVal.split('-');
        let y = parseInt(parts[0], 10);
        let m = parseInt(parts[1], 10) + 1;
        if (m > 12) { m = 1; y += 1; }
        document.getElementById('contract_start_date').value = `${y}-${String(m).padStart(2, '0')}-01`;
        syncContractEndDate();
    }

    function syncContractEndDate() {
        const startVal = document.getElementById('contract_start_date').value;
        const months = parseInt(document.getElementById('contract_duration_months')?.value || '12', 10);
        if (!startVal) return;

        const parts = startVal.split('-');
        let y = parseInt(parts[0], 10);
        let m = parseInt(parts[1], 10);
        let d = parts[2] || '01';

        const totalMonths = (y * 12 + (m - 1)) + months;
        const endY = Math.floor(totalMonths / 12);
        const endM = (totalMonths % 12) + 1;
        document.getElementById('contract_end_date').value = `${endY}-${String(endM).padStart(2, '0')}-${d}`;
        previewMoveInCalculation();
    }

    function previewMoveInCalculation() {
        const rent = parseFloat(document.getElementById('contract_monthly_rent').value || 0);
        const service = parseFloat(document.getElementById('contract_service_fee').value || 0);
        const startDate = document.getElementById('contract_start_date').value;
        const box = document.getElementById('move_in_preview_box');
        if (!startDate || rent <= 0) return;

        const day = parseInt(startDate.split('-')[2], 10);
        const isAfter15 = day > 15;
        const roomFee = isAfter15 ? (rent / 2) : rent;
        const deposit = rent;
        const total = roomFee + service + deposit;

        box.innerHTML = `
            <i class="fa-solid fa-calculator me-1"></i>
            <strong>Dự tính Hóa đơn đầu kỳ (vào ở ngày ${day}):</strong>
            Tiền phòng (${isAfter15 ? '50% - sau ngày 15' : '100% - từ 01→15'}): <strong>${formatVnd(roomFee)}</strong>
            + Dịch vụ: <strong>${formatVnd(service)}</strong>
            + Cọc 1 tháng: <strong>${formatVnd(deposit)}</strong>
            = <strong class="text-danger fs-6">${formatVnd(total)}</strong>
        `;
    }

    function openTerminateModal(id, code, room, endDate, deposit) {
        document.getElementById('term_contract_id').value = id;
        document.getElementById('term_contract_code').textContent = code;
        document.getElementById('term_room_number').textContent = room;
        document.getElementById('term_end_date_raw').value = endDate;
        document.getElementById('term_end_date_display').textContent = endDate.split('-').reverse().join('/');
        document.getElementById('term_deposit_raw').value = deposit;
        document.getElementById('term_deposit_display').textContent = formatVnd(deposit);
        previewDepositSettlement();
        new bootstrap.Modal(document.getElementById('terminateContractModal')).show();
    }

    function previewDepositSettlement() {
        const moveOut = document.getElementById('term_move_out_date').value;
        const endDate = document.getElementById('term_end_date_raw').value;
        const deposit = parseFloat(document.getElementById('term_deposit_raw').value || 0);
        const alertBox = document.getElementById('term_settlement_alert');

        if (moveOut >= endDate) {
            alertBox.className = 'alert alert-success rounded-4 py-2 px-3 small fw-bold mb-3';
            alertBox.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i> Trả phòng đúng/sau hạn HĐ: <strong>Hoàn trả 100% tiền cọc (${formatVnd(deposit)})</strong> cho khách thuê.`;
        } else {
            alertBox.className = 'alert alert-danger rounded-4 py-2 px-3 small fw-bold mb-3';
            alertBox.innerHTML = `<i class="fa-solid fa-circle-xmark me-1"></i> Trả phòng trước hạn HĐ: <strong>Khách thuê mất tiền cọc (${formatVnd(deposit)})</strong> theo điều khoản hợp đồng.`;
        }
    }
    </script>
</body>
</html>
