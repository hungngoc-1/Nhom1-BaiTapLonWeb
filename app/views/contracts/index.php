<?php
/**
 * TRANG QUẢN LÝ HỢP ĐỒNG THUÊ PHÒNG (CONTRACTS MANAGEMENT)
 * Phụ trách: Hùng Văn Ngọc (Nhóm trưởng - MSSV: 224001738)
 */
if (!function_exists('h')) {
    function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
$today = date('Y-m-d');
$nextMonthFirstDay = date('Y-m-01', strtotime('+1 month'));
$sixMonthsEndDay = date('Y-m-d', strtotime('+6 months'));
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
            --teal-hover: #25726b;
            --teal-soft: #e6f4f2;
            --coral-accent: #f97316;
            --dark-card: #0f172a;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: #e6f0fa;
            background-image: 
                radial-gradient(at 0% 0%, rgba(48, 143, 134, 0.12) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(249, 115, 22, 0.08) 0px, transparent 50%);
            min-height: 100vh;
            color: #1e293b;
        }

                /* ================= NAVBAR CHUẨN ĐỒNG BỘ 100% VỚI KHÁCH THUÊ ================= */
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

        .header-content-wrapper {
            position: relative;
            padding: 30px 0 10px 0;
            margin-bottom: 20px;
        }
        .btn-back-content {
            background: #ffffff;
            border: 3px solid #ffffff;
            color: #334155;
            font-weight: 800;
            padding: 8px 22px;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-size: 0.88rem;
            margin-bottom: 16px;
            transition: 0.2s;
        }
        .btn-back-content:hover { background: #f8fafc; border-color: var(--teal-main); color: var(--teal-main); transform: translateY(-2px); }

        .main-title { font-size: 2.1rem; font-weight: 900; color: #0f172a; line-height: 1.2; position: relative; display: inline-block; }
        .bld-highlight { color: var(--teal-main); text-decoration: underline; text-decoration-color: #5eead4; text-underline-offset: 6px; }

        .overview-card {
            border-radius: 26px;
            padding: 22px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 12px 30px rgba(0,0,0,0.08);
            border: 4px solid #ffffff;
            height: 100%; transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .overview-card:hover { transform: translateY(-6px); box-shadow: 0 22px 42px rgba(0,0,0,0.16); }
        .card-green { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); }
        .card-blue { background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); }
        .card-yellow { background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); }
        .card-purple { background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%); }
        .overview-title { font-size: 0.78rem; font-weight: 900; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; text-transform: uppercase; position: relative; z-index: 2;}
        .overview-val { font-size: 2.1rem; font-weight: 900; line-height: 1; letter-spacing: -1px; position: relative; z-index: 2;}

        .contract-section { 
            background: #ffffff; 
            border-radius: 28px; 
            padding: 24px 28px; 
            box-shadow: 0 16px 36px rgba(0,0,0,0.06); 
            border: 4px solid #ffffff;
            margin-bottom: 40px;
        }

        .btn-teal-custom { background: var(--teal-main); border-radius: 50px; font-weight: 800; color: white; padding: 10px 24px; border: 2px solid var(--teal-hover); box-shadow: 0 8px 20px rgba(48, 143, 134, 0.35); font-size: 0.9rem; transition: 0.2s; text-decoration: none; display: inline-flex; align-items: center;}
        .btn-teal-custom:hover { background: var(--teal-hover); color: white; transform: translateY(-2px); }

        .btn-outline-teal { background: transparent; border-radius: 50px; font-weight: 800; color: var(--teal-main); padding: 8px 18px; border: 2px solid var(--teal-main); font-size: 0.88rem; transition: 0.2s; text-decoration: none; display: inline-flex; align-items: center; }
        .btn-outline-teal:hover { background: var(--teal-main); color: white; }

        .form-control, .form-select { border-radius: 14px; border: 1.5px solid #e2e8f0; padding: 9px 14px; font-weight: 600; font-size: 0.88rem; }
        .form-control:focus, .form-select:focus { border-color: var(--teal-main); box-shadow: 0 0 0 3px rgba(48, 143, 134, 0.15); }

        .table-responsive { 
            border-radius: 24px; 
            border: 2px solid #e2e8f0; 
            overflow-x: auto; 
            overflow-y: auto; 
            max-height: 540px; 
            background: #fff; 
        }
        .table-responsive::-webkit-scrollbar { width: 6px; height: 6px; }
        .table-responsive::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .table-responsive::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .table-custom thead th { position: sticky; top: 0; z-index: 5; background-color: #f0fdfa !important; color: var(--teal-main); font-size: 0.78rem; font-weight: 900; text-transform: uppercase; padding: 14px 16px; border-bottom: 2px solid #ccfbf1; }
        .table-custom tbody td { padding: 15px 16px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-weight: 600; }
        .table-custom tbody tr:hover { background-color: #f8fafc; }

        .badge-room { background: #0f172a; color: #fff; font-weight: 900; font-size: 0.85rem; padding: 5px 12px; border-radius: 10px; display: inline-block;}
        .badge-pill { padding: 5px 12px; border-radius: 50px; font-size: 0.76rem; font-weight: 800; display: inline-flex; align-items: center; gap: 5px;}
        .badge-hd1 { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-hd2 { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-revenue-paid { background: #d1fae5; color: #065f46; font-size: 0.7rem; font-weight: 800; padding: 3px 8px; border-radius: 50px; }
        .status-active { background: #dcfce7; color: #166534; }
        .status-terminated { background: #f1f5f9; color: #475569; }
        .status-expired { background: #fee2e2; color: #991b1b; }

        .btn-act { width: 34px; height: 34px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem; border: none; transition: 0.2s; text-decoration: none;}
        .btn-act-teal { background: var(--teal-soft); color: var(--teal-main); width: auto; padding: 0 12px; font-weight: 800; border-radius: 50px;}
        .btn-act-teal:hover { background: var(--teal-main); color: white;}
        .btn-act-info { background: #e0f2fe; color: #0284c7;}
        .btn-act-info:hover { background: #0284c7; color: white;}
        .btn-act-warn { background: #fef3c7; color: #d97706; width: auto; padding: 0 10px; font-weight: 800; border-radius: 50px;}
        .btn-act-warn:hover { background: #d97706; color: white;}
        .btn-act-danger { background: #fee2e2; color: #dc2626;}
        .btn-act-danger:hover { background: #dc2626; color: white;}
        .btn-act-upgrade { background: linear-gradient(135deg, #10b981, #059669); color: white; width: auto; padding: 0 12px; font-weight: 800; border-radius: 50px; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);}
        .btn-act-upgrade:hover { transform: translateY(-2px); color: white;}

        .contract-choice-card {
            border: 2px solid #e2e8f0;
            border-radius: 18px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.2s;
            background: #ffffff;
            height: 100%;
        }
        .contract-choice-card:hover {
            border-color: var(--teal-main);
            box-shadow: 0 6px 18px rgba(0,0,0,0.06);
        }
        .contract-choice-card.selected {
            border-color: var(--teal-main);
            background: #f0fdfa;
            box-shadow: 0 0 0 3px rgba(48, 143, 134, 0.2);
        }

        .footer-custom { font-size: 0.85rem; font-weight: 700; color: #64748b; padding: 20px 0 40px 0; }
    </style>
</head>
<body>

    <!-- THANH ĐIỀU HƯỚNG CHÍNH (ĐỒNG NHẤT HỆ THỐNG) -->
        <!-- THANH ĐIỀU HƯỚNG CHÍNH (ĐỒNG NHẤT 100% HỆ THỐNG) -->
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
                    <li class="nav-item"><a class="nav-link" href="index.php?route=tenants"><i class="fa-solid fa-users"></i> Khách thuê</a></li>
                    <li class="nav-item"><a class="nav-link active" href="index.php?route=contracts"><i class="fa-solid fa-file-signature"></i> Hợp đồng</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?route=utilities"><i class="fa-solid fa-bolt"></i> Điện Nước</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?route=invoices"><i class="fa-solid fa-file-invoice-dollar"></i> Hóa đơn</a></li>
                    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="index.php?route=accounts"><i class="fa-solid fa-user-gear"></i> Tài khoản</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?route=reports_csv"><i class="fa-solid fa-chart-pie"></i> Báo cáo</a></li>
                <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="index.php?route=showcase" target="_blank"><i class="fa-regular fa-eye"></i> Xem phòng khách</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?route=tenant/portal" target="_blank"><i class="fa-solid fa-user-shield text-teal-main"></i> Cổng khách thuê</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 px-xl-5">

        <!-- Thông báo Flash Messages -->
        <?php if (!empty($flash_success)): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 mt-3" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i><?= $flash_success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($flash_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 mt-3" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i><?= $flash_error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($flash_warning)): ?>
            <div class="alert alert-warning alert-dismissible fade show rounded-4 shadow-sm border-0 mt-3" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i><?= $flash_warning ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($pendingVerifications)): ?>
        <!-- BANNER YÊU CẦU XÁC MINH LÝ DO KHÔNG ĐÓNG TIỀN TỪ HÓA ĐƠN -->
        <div class="alert alert-danger rounded-4 p-4 shadow-sm mb-4 border-2 border-danger position-relative z-2" style="background: #fff5f5;">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <h5 class="fw-black text-danger mb-0" style="font-weight:900">
                    <i class="fa-solid fa-bell me-2"></i> YÊU CẦU XÁC MINH TỪ TRANG HÓA ĐƠN (<?= count($pendingVerifications) ?> PHÒNG)
                </h5>
                <span class="badge bg-danger rounded-pill px-3 py-2 fw-bold">Quá hạn 5 ngày chưa đóng tiền tháng đầu</span>
            </div>
            <p class="text-muted small fw-semibold mb-3">
                Bên Hóa đơn gửi yêu cầu xác minh các phòng sau do khách chưa thanh toán tiền phòng tháng đầu từ ngày thứ 6 trở đi. Quản trị viên vui lòng xác minh xem khách không đóng tiền vì <strong>hết hạn hợp đồng &amp; không gia hạn</strong> hay là <strong>bỏ phòng trước hạn</strong> để hệ thống tự động xử lý tiền cọc:
            </p>
            <div class="table-responsive bg-white rounded-3 border shadow-sm">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>PHÒNG</th>
                            <th>KHÁCH THUÊ</th>
                            <th>MÃ HỢP ĐỒNG</th>
                            <th class="text-end">TIỀN CỌC QUY ĐỊNH</th>
                            <th class="text-end">TIỀN PHÒNG THÁNG ĐẦU</th>
                            <th class="text-end">THAO TÁC XÁC MINH</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($pendingVerifications as $pv): ?>
                        <tr>
                            <td><span class="badge-room">P.<?= h($pv['room_number']) ?></span></td>
                            <td>
                                <div class="fw-bold text-dark"><?= h($pv['tenant_name']) ?></div>
                                <div class="small text-muted"><i class="fa-solid fa-phone me-1 text-teal-main"></i><?= h($pv['tenant_phone']) ?></div>
                            </td>
                            <td><span class="badge bg-light text-secondary border fw-bold"><?= h($pv['contract_code']) ?></span></td>
                            <td class="text-end fw-black text-teal-main"><?= number_format((float)$pv['deposit_amount'], 0, ',', '.') ?> đ</td>
                            <td class="text-end fw-black text-danger"><?= number_format((float)($pv['monthly_rent'] ?: $pv['room_price']), 0, ',', '.') ?> đ</td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 py-2 fw-bold shadow-sm"
                                        onclick="openVerificationModal(<?= (int)$pv['id'] ?>, <?= h(json_encode($pv['room_number'])) ?>, <?= h(json_encode($pv['tenant_name'])) ?>, <?= (float)$pv['deposit_amount'] ?>)">
                                    <i class="fa-solid fa-clipboard-check me-1"></i> Xác nhận lý do
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- HEADER TIÊU ĐỀ -->
        <div class="header-content-wrapper">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 small fw-bold">
                            <li class="breadcrumb-item"><a href="index.php?route=dashboard" class="text-teal-main text-decoration-none"><i class="fa-solid fa-house"></i> Hệ thống</a></li>
                            <li class="breadcrumb-item text-muted" aria-current="page">Quản lý Hợp đồng Thuê phòng</li>
                        </ol>
                    </nav>
                    <div class="main-title">
                        Quản lý Hợp đồng -
                        <span class="bld-highlight">StayMate Buildings</span>
                    </div>
                    <div class="text-muted fw-bold small mt-1">
                        HĐ 1 (Đặt cọc giữ chỗ 5 ngày - 50% cọc) &amp; HĐ 2 (Thuê chính thức 6 tháng - Thu trước 1 tháng phòng vào Doanh thu)
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    
                    <button type="button" class="btn-teal-custom" data-bs-toggle="modal" data-bs-target="#contractModal" onclick="resetContractModal()">
                        <i class="fa-solid fa-file-circle-plus me-2"></i> Lập Hợp đồng mới
                    </button>
                </div>
            </div>
        </div>

        <!-- 4 Thẻ Thống Kê Hợp Đồng -->
        <div class="row g-4 mb-4 position-relative z-2">
            <div class="col-12 col-md-6 col-lg-3">
                <div class="overview-card card-green">
                    <div class="overview-title text-success"><i class="fa-solid fa-file-circle-check fs-6"></i> HỢP ĐỒNG ĐANG HIỆU LỰC</div>
                    <div class="d-flex align-items-baseline mt-2">
                        <span class="overview-val text-dark me-2"><?= (int)($stats['active'] ?? 0) ?></span>
                        <span class="fw-bold text-muted small">/ <?= (int)($stats['total'] ?? 0) ?> tổng HĐ</span>
                    </div>
                    <div class="mt-2 text-success" style="font-size: 0.8rem; font-weight: 800;">
                        <span><i class="fa-solid fa-building me-1"></i> <?= (int)($stats['active_official'] ?? 0) ?> HĐ chính thức</span> |
                        <span><i class="fa-solid fa-clock me-1"></i> <?= (int)($stats['active_hold'] ?? 0) ?> HĐ cọc 5N</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="overview-card card-blue">
                    <div class="overview-title text-primary"><i class="fa-solid fa-vault fs-6"></i> DOANH THU TIỀN CỌC ĐANG GIỮ</div>
                    <div class="overview-val text-dark mt-2" style="font-size: 1.85rem;">
                        <?= number_format((float)($stats['total_deposit_held'] ?? 0), 0, ',', '.') ?> đ
                    </div>
                    <div class="mt-2 text-primary" style="font-size: 0.8rem; font-weight: 800;">
                        <i class="fa-solid fa-shield-halved me-1"></i> Tiền bảo đảm tài sản nhà trọ
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="overview-card card-yellow">
                    <div class="overview-title" style="color: #b45309;"><i class="fa-solid fa-hand-holding-dollar fs-6"></i> ĐÃ THU PHÒNG THÁNG ĐẦU (HĐ 2)</div>
                    <div class="overview-val text-dark mt-2" style="font-size: 1.85rem;">
                        <?= number_format((float)($stats['total_first_month_collected'] ?? 0), 0, ',', '.') ?> đ
                    </div>
                    <div class="mt-2" style="font-size: 0.8rem; font-weight: 800; color: #b45309;">
                        <i class="fa-solid fa-receipt me-1"></i> Đã tự động cộng vào Doanh thu
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="overview-card card-purple">
                    <div class="overview-title" style="color: #7e22ce;"><i class="fa-solid fa-clock-rotate-left fs-6"></i> LỊCH SỬ HỦY / THANH LÝ</div>
                    <div class="d-flex align-items-baseline mt-2">
                        <span class="overview-val text-dark me-2"><?= (int)($stats['expired_hold'] ?? 0) + (int)($stats['terminated'] ?? 0) ?></span>
                        <span class="fw-bold text-muted small">hợp đồng</span>
                    </div>
                    <div class="mt-2" style="font-size: 0.8rem; font-weight: 800; color: #7e22ce;">
                        <span><i class="fa-solid fa-triangle-exclamation me-1"></i> <?= (int)($stats['expired_hold'] ?? 0) ?> HĐ quá 5N tự hủy</span> |
                        <span><?= (int)($stats['terminated'] ?? 0) ?> đã thanh lý</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Khung Bảng Danh sách & Bộ lọc Hợp đồng -->
        <div class="contract-section">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 border-bottom border-light pb-3 mb-3">
                <form action="index.php" method="GET" class="d-flex flex-wrap gap-2 align-items-center">
                    <input type="hidden" name="route" value="contracts">
                    <div class="input-group" style="width: 250px;">
                        <span class="input-group-text bg-white border-end-0" style="border-radius: 14px 0 0 14px;"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="keyword" class="form-control border-start-0" style="border-radius: 0 14px 14px 0;" placeholder="Mã HĐ, phòng, khách..." value="<?= h($_GET['keyword'] ?? '') ?>">
                    </div>

                    <select name="contract_type" class="form-select" style="width: 210px;">
                        <option value="">Tất cả loại hợp đồng</option>
                        <option value="deposit_hold" <?= ($_GET['contract_type'] ?? '') === 'deposit_hold' ? 'selected' : '' ?>>HĐ 1: Đặt cọc giữ chỗ (5 ngày)</option>
                        <option value="official_lease" <?= ($_GET['contract_type'] ?? '') === 'official_lease' ? 'selected' : '' ?>>HĐ 2: Thuê chính thức (6 tháng)</option>
                    </select>

                    <select name="status" class="form-select" style="width: 175px;">
                        <option value="">Tất cả trạng thái</option>
                        <?php foreach ($statuses as $val => $label): ?>
                            <option value="<?= $val ?>" <?= ($_GET['status'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" class="btn-outline-teal"><i class="fa-solid fa-filter me-1"></i> Lọc</button>
                    <?php if (!empty($_GET['keyword']) || !empty($_GET['status']) || !empty($_GET['contract_type'])): ?>
                        <a href="index.php?route=contracts" class="btn btn-sm btn-light rounded-pill px-3 fw-bold">Xóa lọc</a>
                    <?php endif; ?>
                </form>

                <div class="small text-muted fw-bold">
                    Tìm thấy: <strong class="text-teal-main"><?= count($contracts) ?></strong> bản ghi hợp đồng
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-custom align-middle mb-0">
                    <thead>
                        <tr>
                            <th>MÃ HĐ &amp; LIÊN KẾT</th>
                            <th>LOẠI HỢP ĐỒNG</th>
                            <th>PHÒNG TRỌ</th>
                            <th>KHÁCH THUÊ</th>
                            <th>THỜI HẠN &amp; HIỆU LỰC</th>
                            <th>GIÁ THUÊ &amp; TIỀN CỌC</th>
                            <th>DOANH THU &amp; SCAN</th>
                            <th>TRẠNG THÁI</th>
                            <th class="text-end">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($contracts)): ?>
                            <?php foreach ($contracts as $c): ?>
                                <?php
                                $cType = $c['contract_type'] ?? 'official_lease';
                                $isHold = ($cType === 'deposit_hold');
                                $depStatus = $c['deposit_status'] ?? 'held';
                                $renStatus = $c['renewal_status'] ?? 'none';
                                $sigType = $c['signature_type'] ?? 'paper';
                                $depositDate = !empty($c['deposit_date']) ? $c['deposit_date'] : $c['start_date'];
                                $monthlyRent = (float)($c['monthly_rent'] ?? $c['room_price'] ?? $c['deposit_amount']);
                                $firstMonthPaid = !empty($c['first_month_rent_paid']);
                                ?>
                                <tr>
                                    <td>
                                        <a href="index.php?route=contracts/detail&id=<?= (int)$c['id'] ?>" class="fw-bold text-teal-main text-decoration-none d-block">
                                            <i class="fa-solid fa-file-contract me-1"></i><?= h($c['contract_code']) ?>
                                        </a>
                                        <div class="small text-muted mt-1">
                                            <span class="badge bg-light text-secondary border" title="ID hợp đồng">HĐ #<?= (int)$c['id'] ?></span>
                                            <span class="badge bg-light text-secondary border" title="ID khách thuê">Khách #<?= (int)$c['tenant_id'] ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($isHold): ?>
                                            <span class="badge-pill badge-hd1">
                                                <i class="fa-solid fa-clock me-1"></i> HĐ 1: Cọc 5 ngày
                                            </span>
                                            <div class="small text-muted mt-1">(50% tiền cọc)</div>
                                        <?php else: ?>
                                            <span class="badge-pill badge-hd2">
                                                <i class="fa-solid fa-shield-halved me-1"></i> HĐ 2: Chính thức 6T
                                            </span>
                                            <?php if (!empty($c['parent_contract_code'])): ?>
                                                <div class="small text-muted mt-1" title="Kế thừa từ HĐ giữ chỗ">
                                                    &larr; Từ <?= h($c['parent_contract_code']) ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge-room">P.<?= h($c['room_number'] ?? 'N/A') ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= h($c['full_name'] ?? 'Chưa rõ') ?></div>
                                        <div class="text-muted small"><i class="fa-solid fa-phone me-1"></i><?= h($c['phone'] ?? '') ?></div>
                                    </td>
                                    <td class="small">
                                        <div>
                                            <strong class="text-teal-main"><?= date('d/m/Y', strtotime($c['start_date'])) ?></strong>
                                            &rarr;
                                            <strong class="<?= $isHold ? 'text-warning' : 'text-danger' ?>"><?= date('d/m/Y', strtotime($c['end_date'])) ?></strong>
                                        </div>
                                        <?php if ($c['status'] === 'active'): ?>
                                            <?php if ($isHold): ?>
                                                <span class="badge bg-warning text-dark rounded-pill mt-1">
                                                    <i class="fa-solid fa-stopwatch me-1"></i> Còn <?= max(0, (int)$c['days_left']) ?> ngày giữ chỗ
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted"><?= (int)$c['days_left'] ?> ngày còn lại</span>
                                            <?php endif; ?>
                                        <?php elseif ($c['status'] === 'expired'): ?>
                                            <span class="badge bg-danger-subtle text-danger rounded-pill mt-1">Quá 5N - Tự hủy</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-danger"><?= number_format($monthlyRent, 0, ',', '.') ?> đ/tháng</div>
                                        <div class="small text-muted">
                                            Cọc: <strong><?= number_format((float)$c['deposit_amount'], 0, ',', '.') ?> đ</strong>
                                            (<?= (int)($c['deposit_paid_percent'] ?? ($isHold ? 50 : 100)) ?>%)
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($firstMonthPaid): ?>
                                            <span class="badge-revenue-paid d-inline-block mb-1">
                                                <i class="fa-solid fa-circle-check me-1"></i> Đã thu tháng đầu (+ Doanh thu)
                                            </span>
                                        <?php elseif ($isHold): ?>
                                            <span class="badge bg-light text-muted border small d-inline-block mb-1">Chưa thu tháng đầu</span>
                                        <?php endif; ?>

                                        <div>
                                            <?php if (!empty($c['scan_file'])): ?>
                                                <a href="<?= h($c['scan_file']) ?>" target="_blank" class="badge-pill bg-success text-white text-decoration-none">
                                                    <i class="fa-solid fa-file-arrow-down me-1"></i> Bản Scan Đã Ký
                                                </a>
                                            <?php elseif (!empty($c['tenant_signature'])): ?>
                                                <span class="badge-pill bg-info text-white"><i class="fa-solid fa-pen-nib"></i> Ký Online</span>
                                            <?php else: ?>
                                                <span class="badge-pill bg-light text-muted border"><i class="fa-solid fa-print"></i> Ký giấy A4</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $stClass = 'status-active';
                                        if ($c['status'] === 'terminated') $stClass = 'status-terminated';
                                        elseif ($c['status'] === 'expired') $stClass = 'status-expired';
                                        ?>
                                        <span class="badge-pill <?= $stClass ?>">
                                            <?= h($statuses[$c['status']] ?? $c['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex flex-wrap gap-1 justify-content-end">
                                            <!-- Nút Ký tiếp HĐ 2 nếu đang giữ chỗ HĐ 1 -->
                                            <?php if ($isHold && $c['status'] === 'active'): ?>
                                                <button type="button" class="btn-act btn-act-upgrade"
                                                        onclick="upgradeToOfficial(<?= (int)$c['id'] ?>, <?= (int)$c['tenant_id'] ?>, <?= (int)$c['room_id'] ?>, <?= (float)$monthlyRent ?>)"
                                                        title="Chuyển tiếp lên Hợp đồng 2 (Thuê chính thức 6 tháng - hoàn tất 100% cọc + thu trước 1 tháng phòng)">
                                                    <i class="fa-solid fa-bolt me-1"></i> Ký tiếp HĐ 2
                                                </button>
                                            <?php endif; ?>

                                            <a href="index.php?route=contracts/detail&id=<?= (int)$c['id'] ?>" class="btn-act btn-act-teal" title="Xem điều khoản A4, In & Tải Scan">
                                                <i class="fa-solid fa-eye me-1"></i> Chi tiết
                                            </a>

                                            <?php if ($c['status'] === 'active'): ?>
                                                <button type="button" class="btn-act btn-act-warn"
                                                        onclick="openTerminateModal(<?= (int)$c['id'] ?>, '<?= h($c['contract_code']) ?>', '<?= h($c['room_number'] ?? '') ?>', '<?= h($c['end_date']) ?>', <?= (float)$c['deposit_amount'] ?>)"
                                                        title="Thanh lý hợp đồng">
                                                    <i class="fa-solid fa-hand-holding-dollar"></i>
                                                </button>
                                            <?php endif; ?>

                                            <a href="index.php?route=contracts/edit&id=<?= (int)$c['id'] ?>" class="btn-act btn-act-info" title="Sửa hợp đồng">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>

                                            <form method="POST" action="index.php?route=contracts/delete" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa hợp đồng <?= h($c['contract_code']) ?> không?');">
                                                <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                                <button type="submit" class="btn-act btn-act-danger" title="Xóa hợp đồng"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fa-regular fa-folder-open fs-1 text-teal-main opacity-50 mb-2 d-block"></i>
                                    Không tìm thấy hợp đồng nào khớp với yêu cầu tìm kiếm.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- ================= MODAL 1: THANH LÝ HỢP ĐỒNG ================= -->
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

    <!-- ================= MODAL 2: LẬP HỢP ĐỒNG THUÊ PHÒNG (HĐ 1 HOẶC HĐ 2) ================= -->
    <div class="modal fade" id="contractModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <form method="POST" action="index.php?route=contracts/store" class="modal-content shadow-lg" style="border-radius: 26px; border: 4px solid #fff; overflow: hidden; max-height: 92vh;">
                <input type="hidden" name="parent_contract_id" id="contract_parent_id" value="">

                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-teal-main">
                        <i class="fa-solid fa-file-circle-plus me-2"></i>Lập Hợp Đồng Thuê Phòng
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <!-- LỰA CHỌN LOẠI HỢP ĐỒNG: HĐ 1 (5 NGÀY - 50% CỌC) VS HĐ 2 (6 THÁNG - 100% CỌC + THU THÁNG ĐẦU) -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-teal-main mb-2">
                            <i class="fa-solid fa-layer-group me-1"></i> Chọn Loại Hợp Đồng <span class="text-danger">*</span>
                        </label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="contract-choice-card" id="card_choice_hd1" onclick="selectContractTypeRadio('deposit_hold')">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <input type="radio" name="contract_type" value="deposit_hold" id="radio_hd1" class="form-check-input mt-0" onchange="onContractTypeChanged()">
                                        <label for="radio_hd1" class="fw-bold mb-0 text-dark cursor-pointer">
                                            HỢP ĐỒNG 1: ĐẶT CỌC GIỮ CHỖ
                                        </label>
                                    </div>
                                    <div class="small text-muted">
                                        • Hiệu lực trong <strong>5 ngày</strong>.<br>
                                        • Khách đóng <strong>1/2 tiền cọc (50%)</strong>.<br>
                                        • Quá 5 ngày sẽ <strong>tự động hủy</strong> và lưu vào lịch sử.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="contract-choice-card selected" id="card_choice_hd2" onclick="selectContractTypeRadio('official_lease')">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <input type="radio" name="contract_type" value="official_lease" id="radio_hd2" class="form-check-input mt-0" checked onchange="onContractTypeChanged()">
                                        <label for="radio_hd2" class="fw-bold mb-0 text-success cursor-pointer">
                                            HỢP ĐỒNG 2: THUÊ CHÍNH THỨC
                                        </label>
                                    </div>
                                    <div class="small text-muted">
                                        • Hoàn tất <strong>100% tiền cọc</strong> (= 1 tháng tiền phòng).<br>
                                        • Hiệu lực hợp đồng <strong>6 tháng</strong>.<br>
                                        • <span class="text-success fw-bold">Thu trước 01 tháng tiền phòng</span> tự động cộng vào Doanh thu.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- THÔNG TIN PHÒNG VÀ KHÁCH THUÊ -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold mb-1">
                                Khách thuê <span class="text-danger">*</span>
                                <span class="text-muted fw-normal" style="font-size:0.75rem;">(Chọn để tự động nhảy phòng đã đặt)</span>
                            </label>
                            <select name="tenant_id" id="contract_tenant_id" class="form-select" required onchange="onContractTenantSelect(this)">
                                <option value="">-- Chọn khách thuê --</option>
                                <?php foreach ($tenants as $t): ?>
                                    <option value="<?= (int)$t['id'] ?>"
                                            data-assigned-room-id="<?= (int)($t['assigned_room_id'] ?? 0) ?>"
                                            data-assigned-room-price="<?= (float)($t['assigned_room_price'] ?? 0) ?>"
                                            data-assigned-room-number="<?= h($t['assigned_room_number'] ?? '') ?>" data-phone="<?= h($t['phone'] ?? '') ?>">
                                        <?= h($t['full_name']) ?> - <?= h($t['phone']) ?>
                                        <?= !empty($t['assigned_room_number']) ? ' (Đã chọn P.' . h($t['assigned_room_number']) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold mb-1">
                                Phòng trọ chỉ định <span class="text-danger">*</span>
                            </label>
                            <select name="room_id" id="contract_room_id" class="form-select" required onchange="onContractRoomSelect(this)">
                                <option value="">-- Chọn phòng --</option>
                                <?php foreach ($rooms as $r): ?>
                                    <option value="<?= (int)$r['id'] ?>"
                                            data-room-name="<?= h($r['room_number']) ?>"
                                            data-price="<?= (float)$r['price'] ?>"
                                            data-service-fee="<?= (float)($r['total_service_fee'] ?? 0) ?>"
                                            data-electric-rate="<?= (float)($r['electricity_rate'] ?? 3500) ?>"
                                            data-water-rate="<?= (float)($r['water_rate'] ?? 20000) ?>"
                                            data-services='<?= htmlspecialchars(json_encode($r['services'] ?? [], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>'>
                                        <?= h($r['room_number']) ?> - <?= number_format((float)$r['price'], 0, ',', '.') ?> đ (<?= $r['status'] === 'available' ? 'Đang trống' : 'Đang thuê' ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-bold mb-1">Ngày lập / Đặt cọc <span class="text-danger">*</span></label>
                            <input type="date" name="deposit_date" id="contract_deposit_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-bold mb-1">Ngày bắt đầu tính <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="contract_start_date" class="form-control fw-bold text-teal-main" value="<?= date('Y-m-d') ?>" required onchange="syncContractDates()">
                        </div>

                        <div class="col-md-3" id="duration_select_wrap">
                            <label class="form-label small fw-bold mb-1">
                                Thời hạn hợp đồng
                                <span class="badge bg-light text-secondary border rounded-pill ms-1" style="font-size:0.7rem;"><i class="fa-solid fa-lock text-warning me-1"></i>Cố định</span>
                            </label>
                            <div class="input-group">
                                <input type="text" class="form-control bg-light fw-bold text-teal-main" id="contract_duration_display" value="Cố định 6 tháng" readonly>
                                <span class="input-group-text bg-light text-muted small" title="Hợp đồng chính thức cố định thời hạn 6 tháng"><i class="fa-solid fa-lock text-warning"></i></span>
                            </div>
                            <input type="hidden" name="duration_months" id="contract_duration_months" value="6">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-bold mb-1">
                                Ngày kết thúc <span class="text-danger">*</span>
                                <span class="badge bg-light text-secondary border rounded-pill ms-1" style="font-size:0.7rem;"><i class="fa-solid fa-lock text-warning me-1"></i>Tự tính</span>
                            </label>
                            <div class="input-group">
                                <input type="date" name="end_date" id="contract_end_date" class="form-control bg-light fw-bold text-danger" value="<?= $sixMonthsEndDay ?>" readonly required>
                                <span class="input-group-text bg-light text-muted small" title="Tự động tính đúng 6 tháng theo ngày bắt đầu"><i class="fa-solid fa-calendar-check text-danger"></i></span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold mb-1">
                                Giá thuê/tháng (VNĐ) <span class="text-danger">*</span>
                                <span class="badge bg-light text-secondary border rounded-pill ms-1" style="font-size:0.7rem;"><i class="fa-solid fa-lock text-warning me-1"></i>Mặc định theo phòng</span>
                            </label>
                            <div class="input-group">
                                <input type="number" step="50000" min="0" name="monthly_rent" id="contract_monthly_rent" class="form-control bg-light fw-bold" readonly required oninput="syncDepositAndPreview()">
                                <span class="input-group-text bg-light text-muted small" title="Cố định theo phòng đã chỉnh sửa"><i class="fa-solid fa-lock text-warning"></i></span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold mb-1">
                                Tiền đặt cọc thực thu
                                <span class="badge bg-teal-main text-white rounded-pill ms-1" id="deposit_percent_badge">100% cọc</span>
                            </label>
                            <div class="input-group">
                                <input type="number" name="deposit_amount" id="contract_deposit" class="form-control bg-light fw-bold text-teal-main" value="0" readonly>
                                <span class="input-group-text bg-light text-muted small"><i class="fa-solid fa-shield-halved text-teal-main"></i></span>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold mb-1">Hình thức thực hiện</label>
                            <select name="signature_type" class="form-select">
                                <option value="paper" selected>Ký Giấy A4 (Có lưu Scan)</option>
                                <option value="online">Chữ ký điện tử Online</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold mb-1">
                                Giá điện (đ/kWh)
                                <span class="badge bg-light text-secondary border rounded-pill ms-1" style="font-size:0.7rem;"><i class="fa-solid fa-lock text-warning me-1"></i>Mặc định</span>
                            </label>
                            <div class="input-group">
                                <input type="number" name="electricity_rate" id="contract_electricity_rate" class="form-control bg-light fw-bold text-secondary" value="3500" readonly>
                                <span class="input-group-text bg-light text-muted small"><i class="fa-solid fa-bolt text-warning"></i></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold mb-1">
                                Giá nước (đ/khối)
                                <span class="badge bg-light text-secondary border rounded-pill ms-1" style="font-size:0.7rem;"><i class="fa-solid fa-lock text-warning me-1"></i>Mặc định</span>
                            </label>
                            <div class="input-group">
                                <input type="number" name="water_rate" id="contract_water_rate" class="form-control bg-light fw-bold text-secondary" value="20000" readonly>
                                <span class="input-group-text bg-light text-muted small"><i class="fa-solid fa-droplet text-primary"></i></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold mb-1">
                                Tổng phí dịch vụ (đ/tháng)
                                <span class="badge bg-teal-subtle text-teal-main border rounded-pill ms-1" style="font-size:0.7rem;"><i class="fa-solid fa-calculator me-1"></i>Tự động tính</span>
                            </label>
                            <div class="input-group">
                                <input type="number" name="service_fee" id="contract_service_fee" class="form-control bg-light fw-bold text-teal-main" value="0" readonly oninput="syncDepositAndPreview()">
                                <span class="input-group-text bg-light text-muted small" title="Tự động tính từ các dịch vụ đã chọn bên Quản lý phòng"><i class="fa-solid fa-calculator text-teal-main"></i></span>
                            </div>
                        </div>

                        <!-- KHU VỰC HIỂN THỊ ĐẦY ĐỦ CÁC DỊCH VỤ ĐÃ CHỌN CỦA PHÒNG & TỔNG TIỀN DỊCH VỤ -->
                        <div class="col-12">
                            <div class="rounded-4 p-3 shadow-xs" style="background:#f8fafc; border:1.5px solid #cbd5e1;">
                                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge rounded-pill bg-teal-main text-white px-2.5 py-1.5 fw-bold" style="font-size:0.82rem;">
                                            <i class="fa-solid fa-layer-group me-1"></i> Dịch vụ đã chọn của phòng:
                                        </span>
                                        <span id="selected_room_badge" class="badge rounded-pill bg-secondary text-white px-2.5 py-1.5 small fw-bold">
                                            Chưa chọn phòng
                                        </span>
                                    </div>
                                    <div class="fw-bold d-flex align-items-center gap-2" style="color:#0f766e; font-size:0.92rem;">
                                        <span>Tổng tiền dịch vụ:</span>
                                        <span id="display_total_service_fee" class="badge bg-white text-teal-main fs-6 px-3 py-1.5 fw-black border border-teal shadow-xs">
                                            0 đ / tháng
                                        </span>
                                    </div>
                                </div>

                                <!-- Bảng danh sách dịch vụ chi tiết -->
                                <div class="table-responsive rounded-3 bg-white border shadow-xs mb-2">
                                    <table class="table table-sm table-hover align-middle mb-0" style="font-size:0.86rem;">
                                        <thead class="table-light border-bottom">
                                            <tr>
                                                <th class="ps-3 py-2 text-secondary" style="width: 50px;">STT</th>
                                                <th class="py-2 text-secondary">Tên dịch vụ</th>
                                                <th class="py-2 text-secondary text-end" style="width: 170px;">Đơn giá</th>
                                                <th class="py-2 text-secondary text-center pe-3" style="width: 140px;">Đơn vị tính</th>
                                            </tr>
                                        </thead>
                                        <tbody id="room_services_list_tbody">
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-3">
                                                    <i class="fa-solid fa-circle-info text-teal-main me-1"></i>
                                                    Vui lòng chọn khách thuê hoặc phòng để xem đầy đủ danh sách dịch vụ đã chọn.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-1">
                                    <small class="text-muted d-flex align-items-center gap-1" style="font-size:0.78rem;">
                                        <i class="fa-solid fa-lock text-warning"></i>
                                        Các giá tiền để mặc định theo phòng đã chỉnh sửa và chỉ thay đổi khi bên phòng thay đổi.
                                    </small>
                                    <a href="index.php?route=rooms" target="_blank" class="text-decoration-none fw-bold small text-teal-main d-inline-flex align-items-center gap-1">
                                        <span>Chỉnh sửa dịch vụ &amp; giá tiền tại trang Quản lý phòng</span>
                                        <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:0.75rem;"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- HỘP DỰ TÍNH THU TIỀN VÀ CỘNG VÀO DOANH THU -->
                        <div class="col-12">
                            <div id="move_in_preview_box" class="p-3 rounded-4 small fw-semibold" style="background:#f0fdfa;border:1.5px solid #99f6e4;color:#0f766e;">
                                <i class="fa-solid fa-calculator me-1"></i> Chọn phòng và khách thuê để tự động tính tiền cọc và tiền phòng đầu kỳ.
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold mb-1">Ghi chú thỏa thuận riêng</label>
                            <input type="text" name="notes" id="contract_notes" class="form-control" placeholder="VD: Bàn giao chìa khóa, 02 thẻ từ thang máy...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn-teal-custom">
                        <i class="fa-solid fa-pen-to-square me-2"></i> Tạo Hợp Đồng &amp; Mở Bản In Ký
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="footer-custom container-fluid px-4 px-xl-5 d-flex justify-content-between align-items-center mt-4">
        <div><i class="fa-solid fa-building me-1" style="color:var(--teal-main);"></i> © 2026 StayMate - Quản lý Nhà trọ Chuyên nghiệp</div>
        <div class="d-flex gap-4">
            <span><i class="fa-solid fa-shield-halved me-1" style="color:var(--teal-main);"></i> MVC Architecture</span>
            <span><i class="fa-solid fa-file-signature me-1 text-teal-main"></i> Hợp đồng 1 (5N) &amp; Hợp đồng 2 (6T)</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- MODAL XÁC NHẬN LÝ DO KHÔNG ĐÓNG TIỀN PHÒNG THÁNG ĐẦU -->
    <div class="modal fade" id="verificationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light rounded-top-4">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="fa-solid fa-clipboard-check me-2"></i> Xác Nhận Xử Lý Hợp Đồng &amp; Tiền Cọc
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="index.php?route=contracts/resolve-verification">
                    <input type="hidden" name="contract_id" id="verify_contract_id">
                    <div class="modal-body p-4">
                        <div class="p-3 rounded-4 mb-3" style="background:#fff5f5;border:2px solid #fecaca">
                            <div class="small fw-bold text-muted">Phòng &amp; Khách thuê cần xác nhận</div>
                            <div class="fw-black text-danger fs-5" id="verify_room_info">—</div>
                            <div class="small fw-bold text-dark mt-1" id="verify_deposit_info">Tiền cọc: 0 đ</div>
                        </div>

                        <label class="form-label fw-bold small text-uppercase text-muted">
                            Chọn nguyên nhân không đóng tiền phòng <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex flex-column gap-3 mb-3">
                            <div class="form-check p-3 rounded-3 border bg-light">
                                <input class="form-check-input ms-0 me-2" type="radio" name="case_key" id="case_expired" value="expired_no_renew" checked required>
                                <label class="form-check-label fw-bold text-success" for="case_expired">
                                    <i class="fa-solid fa-circle-check me-1"></i> Khách đã hết thời gian thuê &amp; Không muốn gia hạn
                                    <div class="small fw-normal text-muted mt-1">
                                        &rarr; Trạng thái cọc sẽ tự động chuyển thành <strong>Đã hoàn cọc</strong>, kết thúc hợp đồng và gửi thông báo xác nhận sang trang Hóa đơn để đối soát.
                                    </div>
                                </label>
                            </div>

                            <div class="form-check p-3 rounded-3 border bg-light">
                                <input class="form-check-input ms-0 me-2" type="radio" name="case_key" id="case_early" value="early_move_out" required>
                                <label class="form-check-label fw-bold text-danger" for="case_early">
                                    <i class="fa-solid fa-ban me-1"></i> Bỏ phòng / chuyển đi trước khi hết hợp đồng
                                    <div class="small fw-normal text-muted mt-1">
                                        &rarr; Khách <strong>KHÔNG ĐƯỢC HOÀN CỌC</strong> (Mất cọc/Tịch thu cọc theo điều khoản hợp đồng), hợp đồng thanh lý và giải phóng phòng.
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Ghi chú xác nhận</label>
                            <input type="text" name="verification_note" class="form-control" placeholder="VD: Khách trả phòng ngày 05/11, đã bàn giao đủ chìa khóa...">
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">
                            <i class="fa-solid fa-check me-1"></i> Xác nhận &amp; Cập nhật trạng thái
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function formatVnd(n) {
        return new Intl.NumberFormat('vi-VN').format(Math.round(n || 0)) + ' đ';
    }

    function selectContractTypeRadio(type) {
        if (type === 'deposit_hold') {
            document.getElementById('radio_hd1').checked = true;
        } else {
            document.getElementById('radio_hd2').checked = true;
        }
        onContractTypeChanged();
    }

    function onContractTypeChanged() {
        const isHd1 = document.getElementById('radio_hd1').checked;
        const card1 = document.getElementById('card_choice_hd1');
        const card2 = document.getElementById('card_choice_hd2');
        const durationWrap = document.getElementById('duration_select_wrap');

        if (isHd1) {
            card1.classList.add('selected');
            card2.classList.remove('selected');
            durationWrap.style.display = 'none';
        } else {
            card1.classList.remove('selected');
            card2.classList.add('selected');
            durationWrap.style.display = 'block';
        }

        syncContractDates();
        syncDepositAndPreview();
    }

    // Tự động nhảy phòng khi chọn khách thuê
    function onContractTenantSelect(selectEl) {
        const opt = selectEl.options[selectEl.selectedIndex];
        const roomSelect = document.getElementById('contract_room_id');
        if (!roomSelect) return;

        if (!opt || !selectEl.value) {
            roomSelect.value = '';
            resetRoomServicesDisplay();
            document.getElementById('contract_monthly_rent').value = '';
            document.getElementById('contract_deposit').value = '0';
            document.getElementById('contract_service_fee').value = '0';
            syncDepositAndPreview();
            return;
        }

        const assignedRoomId = opt.dataset.assignedRoomId;
        const assignedRoomNumber = (opt.dataset.assignedRoomNumber || '').trim();
        let matched = false;

        // 1. Khớp theo ID phòng
        if (assignedRoomId && parseInt(assignedRoomId, 10) > 0) {
            roomSelect.value = assignedRoomId;
            if (roomSelect.value === String(assignedRoomId)) {
                matched = true;
            }
        }

        // 2. Nếu chưa khớp theo ID, khớp theo tên/số phòng
        if (!matched && assignedRoomNumber) {
            for (let i = 0; i < roomSelect.options.length; i++) {
                const rOpt = roomSelect.options[i];
                const rName = (rOpt.dataset.roomName || rOpt.text.split(' - ')[0]).trim();
                if (rName.toLowerCase() === assignedRoomNumber.toLowerCase()) {
                    roomSelect.selectedIndex = i;
                    matched = true;
                    break;
                }
            }
        }

        // 3. Nếu phòng chưa có trong select (thuộc tòa khác hoặc phòng ẩn), tạo option mới để gán đúng
        if (!matched && assignedRoomId && parseInt(assignedRoomId, 10) > 0) {
            const price = parseFloat(opt.dataset.assignedRoomPrice || 0);
            const newOpt = document.createElement('option');
            newOpt.value = assignedRoomId;
            newOpt.dataset.roomName = assignedRoomNumber || ('Phòng #' + assignedRoomId);
            newOpt.dataset.price = price;
            newOpt.dataset.serviceFee = 0;
            newOpt.dataset.electricRate = 3500;
            newOpt.dataset.waterRate = 20000;
            newOpt.dataset.services = '[]';
            newOpt.textContent = (assignedRoomNumber ? assignedRoomNumber : ('P.' + assignedRoomId)) + ' - ' + formatVnd(price) + ' (Phòng của khách)';
            roomSelect.appendChild(newOpt);
            roomSelect.value = assignedRoomId;
            matched = true;
        }

        if (matched) {
            onContractRoomSelect(roomSelect);
        }
    }

    function onContractRoomSelect(selectEl) {
        const opt = selectEl.options[selectEl.selectedIndex];
        if (!opt || !opt.value) {
            resetRoomServicesDisplay();
            document.getElementById('contract_monthly_rent').value = '';
            document.getElementById('contract_deposit').value = '0';
            document.getElementById('contract_service_fee').value = '0';
            syncDepositAndPreview();
            return;
        }

        const roomName = opt.dataset.roomName || opt.text.split(' - ')[0] || '';
        const price = parseFloat(opt.dataset.price || 0);
        const serviceFee = parseFloat(opt.dataset.serviceFee || 0);
        const electricRate = parseFloat(opt.dataset.electricRate || 3500);
        const waterRate = parseFloat(opt.dataset.waterRate || 20000);

        // Các giá tiền để mặc định theo phòng đã chỉnh sửa
        document.getElementById('contract_monthly_rent').value = price;
        document.getElementById('contract_service_fee').value = serviceFee;
        if (document.getElementById('contract_electricity_rate')) {
            document.getElementById('contract_electricity_rate').value = electricRate;
        }
        if (document.getElementById('contract_water_rate')) {
            document.getElementById('contract_water_rate').value = waterRate;
        }

        // Đọc danh sách dịch vụ chi tiết của phòng
        let services = [];
        try {
            services = JSON.parse(opt.dataset.services || '[]');
        } catch (e) {
            services = [];
        }

        renderRoomServicesTable(roomName, services, serviceFee);
        syncDepositAndPreview();
    }

    function renderRoomServicesTable(roomName, services, totalFee) {
        const badge = document.getElementById('selected_room_badge');
        const totalDisplay = document.getElementById('display_total_service_fee');
        const tbody = document.getElementById('room_services_list_tbody');

        if (badge) {
            badge.className = 'badge rounded-pill bg-teal-main text-white px-2.5 py-1.5 small fw-bold';
            badge.innerHTML = `<i class="fa-solid fa-door-open me-1"></i> Phòng ${escapeHtml(roomName)}`;
        }
        if (totalDisplay) {
            totalDisplay.textContent = formatVnd(totalFee) + ' / tháng';
        }

        if (!tbody) return;

        if (!services || services.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-muted py-3">
                        <i class="fa-solid fa-info-circle text-teal-main me-1"></i> Phòng này chưa có dịch vụ đăng ký riêng (Phí dịch vụ: 0 đ).
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        services.forEach((s, idx) => {
            const sName = s.service_name || 'Dịch vụ';
            const sPrice = parseFloat(s.unit_price || 0);
            const sUnit = s.unit || 'Tháng';
            html += `
                <tr>
                    <td class="ps-3 py-2 text-muted fw-bold">${idx + 1}</td>
                    <td class="py-2 fw-semibold text-dark">
                        <i class="fa-solid fa-circle-check text-teal-main me-1.5"></i> ${escapeHtml(sName)}
                    </td>
                    <td class="py-2 text-end fw-bold text-teal-main">
                        ${formatVnd(sPrice)}
                    </td>
                    <td class="py-2 text-center pe-3 text-secondary">
                        <span class="badge bg-light text-secondary border px-2 py-1 rounded-pill">${escapeHtml(sUnit)}</span>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }

    function resetRoomServicesDisplay() {
        const badge = document.getElementById('selected_room_badge');
        const totalDisplay = document.getElementById('display_total_service_fee');
        const tbody = document.getElementById('room_services_list_tbody');
        if (badge) {
            badge.className = 'badge rounded-pill bg-secondary text-white px-2.5 py-1.5 small';
            badge.textContent = 'Chưa chọn phòng';
        }
        if (totalDisplay) {
            totalDisplay.textContent = '0 đ / tháng';
        }
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-muted py-3">
                        <i class="fa-solid fa-circle-info text-teal-main me-1"></i>
                        Vui lòng chọn khách thuê hoặc phòng để xem đầy đủ danh sách dịch vụ đã chọn.
                    </td>
                </tr>
            `;
        }
    }

    function escapeHtml(text) {
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function syncContractDates() {
        const startDateVal = document.getElementById('contract_start_date').value || new Date().toISOString().split('T')[0];
        const isHd1 = document.getElementById('radio_hd1').checked;
        const start = new Date(startDateVal);
        const durationDisplay = document.getElementById('contract_duration_display');
        const durationMonths = document.getElementById('contract_duration_months');

        if (isHd1) {
            // HĐ 1: Đặt cọc giữ chỗ 5 ngày
            const end = new Date(start);
            end.setDate(end.getDate() + 5);
            document.getElementById('contract_end_date').value = end.toISOString().split('T')[0];
            if (durationDisplay) durationDisplay.value = '5 ngày (Giữ chỗ)';
            if (durationMonths) durationMonths.value = '0';
        } else {
            // HĐ 2: Cố định đúng 6 tháng
            const end = new Date(start);
            end.setMonth(end.getMonth() + 6);
            document.getElementById('contract_end_date').value = end.toISOString().split('T')[0];
            if (durationDisplay) durationDisplay.value = 'Cố định 6 tháng';
            if (durationMonths) durationMonths.value = '6';
        }

        previewMoveInCalculation();
    }

    function syncDepositAndPreview() {
        const rent = parseFloat(document.getElementById('contract_monthly_rent').value || 0);
        const isHd1 = document.getElementById('radio_hd1').checked;
        const depPercent = isHd1 ? 50 : 100;
        const deposit = isHd1 ? Math.round(rent * 0.5) : rent;

        document.getElementById('contract_deposit').value = deposit;
        document.getElementById('deposit_percent_badge').textContent = depPercent + '% cọc (' + formatVnd(deposit) + ')';

        previewMoveInCalculation();
    }

    function previewMoveInCalculation() {
        const rent = parseFloat(document.getElementById('contract_monthly_rent').value || 0);
        const service = parseFloat(document.getElementById('contract_service_fee').value || 0);
        const isHd1 = document.getElementById('radio_hd1').checked;
        const box = document.getElementById('move_in_preview_box');
        if (rent <= 0) return;

        if (isHd1) {
            const deposit = Math.round(rent * 0.5);
            box.style.background = '#fefce8';
            box.style.borderColor = '#fef08a';
            box.style.color = '#854d0e';
            box.innerHTML = `
                <i class="fa-solid fa-clock text-warning me-1"></i>
                <strong>HỢP ĐỒNG 1: ĐẶT CỌC GIỮ CHỖ (HIỆU LỰC 5 NGÀY)</strong><br>
                • Số tiền khách đóng: <strong>${formatVnd(deposit)}</strong> (50% tiền cọc giữ phòng).<br>
                • Thời hạn giữ chỗ: <strong>5 ngày</strong> (từ ngày bắt đầu). Quá 5 ngày hợp đồng sẽ <strong>tự động hủy</strong> và lưu vào lịch sử theo quy định.<br>
                • Phí dịch vụ: <strong>${formatVnd(service)}/tháng</strong> (thu khi vào ở chính thức theo HĐ 2).
            `;
        } else {
            const deposit = rent;
            const totalFirstMonth = deposit + rent + service;
            box.style.background = '#f0fdfa';
            box.style.borderColor = '#99f6e4';
            box.style.color = '#0f766e';
            box.innerHTML = `
                <i class="fa-solid fa-shield-halved text-success me-1"></i>
                <strong>HỢP ĐỒNG 2: THUÊ CHÍNH THỨC (HIỆU LỰC 6 THÁNG)</strong><br>
                • Tiền cọc 100%: <strong>${formatVnd(deposit)}</strong> (= 01 tháng tiền phòng, cộng vào Doanh thu cọc).<br>
                • <strong class="text-success">Thu trước 01 tháng tiền phòng:</strong> <strong>${formatVnd(rent)}</strong> (Tự động tạo hóa đơn ĐÃ THANH TOÁN &amp; cộng vào Doanh thu thực thu).<br>
                • Phí dịch vụ đi kèm: <strong>${formatVnd(service)}/tháng</strong> (tính theo các dịch vụ đã chọn của phòng).<br>
                &rarr; <strong>Tổng số tiền thu khi ký HĐ 2:</strong> <span class="fs-6 text-danger fw-black">${formatVnd(totalFirstMonth)}</span> (Tiền phòng + Tiền cọc + Phí dịch vụ).
            `;
        }
    }

    // Nút Ký tiếp HĐ 2 trên bảng
    function upgradeToOfficial(contractId, tenantId, roomId, rent) {
        document.getElementById('contract_parent_id').value = contractId;
        selectContractTypeRadio('official_lease');

        const tenantSel = document.getElementById('contract_tenant_id');
        if (tenantSel) {
            tenantSel.value = tenantId;
        }

        const roomSel = document.getElementById('contract_room_id');
        if (roomSel) {
            roomSel.value = roomId;
        }

        document.getElementById('contract_monthly_rent').value = rent;
        document.getElementById('contract_notes').value = 'Kế thừa và chuyển tiếp từ HĐ giữ chỗ #' + contractId + ' (hoàn tất nốt 50% cọc)';

        syncContractDates();
        syncDepositAndPreview();

        new bootstrap.Modal(document.getElementById('contractModal')).show();
    }

    function resetContractModal() {
        document.getElementById('contract_parent_id').value = '';
        selectContractTypeRadio('official_lease');
        syncContractDates();
        const roomSel = document.getElementById('contract_room_id');
        if (roomSel && roomSel.value) {
            onContractRoomSelect(roomSel);
        } else {
            resetRoomServicesDisplay();
        }
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

    // Auto-open modal if URL parameters exist
    window.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        const preTenant = urlParams.get('tenant_id');
        const preRoom = urlParams.get('room_id');
        const preUpgrade = urlParams.get('upgrade_from');
        const preType = urlParams.get('type');

        if (preTenant || preRoom || preUpgrade) {
            const modal = new bootstrap.Modal(document.getElementById('contractModal'));
            if (preUpgrade) {
                document.getElementById('contract_parent_id').value = preUpgrade;
                selectContractTypeRadio('official_lease');
            } else if (preType === 'deposit_hold') {
                selectContractTypeRadio('deposit_hold');
            }
            if (preTenant) {
                document.getElementById('contract_tenant_id').value = preTenant;
            }
            if (preRoom) {
                const roomSel = document.getElementById('contract_room_id');
                roomSel.value = preRoom;
                onContractRoomSelect(roomSel);
            }
            syncContractDates();
            syncDepositAndPreview();
            modal.show();
        }
    });
    
    
function openVerificationModal(contractId, room, tenant, deposit) {
        document.getElementById('verify_contract_id').value = contractId;
        document.getElementById('verify_room_info').textContent = 'Phòng ' + room + (tenant ? ' · ' + tenant : '');
        document.getElementById('verify_deposit_info').textContent = 'Tiền cọc quy định: ' + Math.round(deposit).toLocaleString('vi-VN') + ' đ';
        new bootstrap.Modal(document.getElementById('verificationModal')).show();
    }
</script>

    <!-- MODAL XÁC NHẬN LÝ DO KHÔNG ĐÓNG TIỀN PHÒNG THÁNG ĐẦU -->
    <div class="modal fade" id="verificationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light rounded-top-4">
                    <h5 class="modal-title fw-bold text-danger">
                        <i class="fa-solid fa-clipboard-check me-2"></i> Xác Nhận Xử Lý Hợp Đồng &amp; Tiền Cọc
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="index.php?route=contracts/resolve-verification">
                    <input type="hidden" name="contract_id" id="verify_contract_id">
                    <div class="modal-body p-4">
                        <div class="p-3 rounded-4 mb-3" style="background:#fff5f5;border:2px solid #fecaca">
                            <div class="small fw-bold text-muted">Phòng &amp; Khách thuê cần xác nhận</div>
                            <div class="fw-black text-danger fs-5" id="verify_room_info">—</div>
                            <div class="small fw-bold text-dark mt-1" id="verify_deposit_info">Tiền cọc: 0 đ</div>
                        </div>

                        <label class="form-label fw-bold small text-uppercase text-muted">
                            Chọn nguyên nhân không đóng tiền phòng <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex flex-column gap-3 mb-3">
                            <div class="form-check p-3 rounded-3 border bg-light">
                                <input class="form-check-input ms-0 me-2" type="radio" name="case_key" id="case_expired" value="expired_no_renew" checked required>
                                <label class="form-check-label fw-bold text-success" for="case_expired">
                                    <i class="fa-solid fa-circle-check me-1"></i> Khách đã hết thời gian thuê &amp; Không muốn gia hạn
                                    <div class="small fw-normal text-muted mt-1">
                                        &rarr; Trạng thái cọc sẽ tự động chuyển thành <strong>Đã hoàn cọc</strong>, kết thúc hợp đồng và gửi thông báo xác nhận sang trang Hóa đơn để đối soát.
                                    </div>
                                </label>
                            </div>

                            <div class="form-check p-3 rounded-3 border bg-light">
                                <input class="form-check-input ms-0 me-2" type="radio" name="case_key" id="case_early" value="early_move_out" required>
                                <label class="form-check-label fw-bold text-danger" for="case_early">
                                    <i class="fa-solid fa-ban me-1"></i> Bỏ phòng / chuyển đi trước khi hết hợp đồng
                                    <div class="small fw-normal text-muted mt-1">
                                        &rarr; Khách <strong>KHÔNG ĐƯỢC HOÀN CỌC</strong> (Mất cọc/Tịch thu cọc theo điều khoản hợp đồng), hợp đồng thanh lý và giải phóng phòng.
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-muted">Ghi chú xác nhận</label>
                            <input type="text" name="verification_note" class="form-control" placeholder="VD: Khách trả phòng ngày 05/11, đã bàn giao đủ chìa khóa...">
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">
                            <i class="fa-solid fa-check me-1"></i> Xác nhận &amp; Cập nhật trạng thái
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


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
