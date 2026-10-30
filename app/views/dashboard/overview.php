<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayMate - Tổng quan Tòa Nhà</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary-teal: #36837c; --primary-hover: #2a6a64; --bg-body: #f4f8f7; }
        body { background-color: var(--bg-body); font-family: 'Nunito', sans-serif; font-size: 0.9rem; color: #334155; }
        
        .navbar-custom { background-color: #ffffff; padding: 10px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); border-bottom: 2px solid #e2e8f0; }
        .brand-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .brand-logo i { color: var(--primary-teal); font-size: 2rem; }
        .brand-text { color: var(--primary-teal); font-weight: 800; font-size: 1.2rem; line-height: 1.1; }
        .cursor-pointer { cursor: pointer; }
        
        .nav-pills { gap: 2px; }
        .nav-pills .nav-link { color: #64748b; font-weight: 700; font-size: 0.8rem; padding: 7px 11px; border-radius: 16px; transition: all 0.2s; white-space: nowrap; }
        .nav-pills .nav-link i { margin-right: 4px; }
        .nav-pills .nav-link:hover { color: var(--primary-teal); background: #e6f3f1; }
        .nav-pills .nav-link.active { background-color: var(--primary-teal); color: white; box-shadow: 0 4px 12px rgba(54, 131, 124, 0.3); }

        .overview-card { border-radius: 16px; padding: 20px; position: relative; overflow: hidden; border: 2px solid; box-shadow: 0 10px 25px rgba(0,0,0,0.06); }
        .bg-green-light { background-color: #dcfce7; border-color: #bbf7d0; }
        .bg-blue-light { background-color: #e0f2fe; border-color: #bae6fd; }
        .bg-yellow-light { background-color: #fef9c3; border-color: #fef08a; }
        .bg-purple-light { background-color: #f3e8ff; border-color: #e9d5ff; }
        .overview-icon { position: absolute; right: 20px; top: 50%; transform: translateY(-50%); width: 36px; height: 36px; display:flex; align-items:center; justify-content:center; border-radius: 50%; color: white; }
        .icon-green { background-color: #22c55e; }
        .icon-blue { background-color: #3b82f6; }
        .icon-yellow { background-color: #eab308; }
        .icon-purple { background-color: #a855f7; }

        .invoice-section { background: white; border-radius: 16px; border: 2px solid #cbd5e1; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
        .btn-teal-custom { background-color: var(--primary-teal); color: white; border-radius: 20px; font-weight: 700; font-size: 0.85rem; padding: 8px 20px; border: none; }
    </style>
</head>
<body>

    <!-- Navbar Có Đầy Đủ Menu Và Tên Người Dùng -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container-fluid px-3 px-xl-4">
            <a class="brand-logo me-4" href="index.php?route=dashboard">
                <i class="fa-solid fa-house-chimney"></i>
                <div>
                    <div class="brand-text">StayMate</div>
                    <div class="text-muted" style="font-size: 0.7rem;">Quản Lý Nhà Trọ</div>
                </div>
            </a>

            <!-- Menu với Nút Quay Lại -->
            <ul class="nav nav-pills me-auto d-none d-lg-flex">
                <li class="nav-item"><a class="nav-link" href="index.php?route=dashboard"><i class="fa-solid fa-arrow-left"></i> Quay lại</a></li>
                <li class="nav-item"><a class="nav-link active" href="#"><i class="fa-solid fa-chart-pie"></i> Tổng quan</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-door-open"></i> Phòng trọ</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-users"></i> Khách thuê</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-file-contract"></i> Hợp đồng</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-bolt"></i> Điện Nước</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-file-invoice-dollar"></i> Hóa đơn</a></li>
            </ul>

            <!-- Thông tin người dùng -->
            <div class="d-flex align-items-center gap-3">
                <div class="position-relative cursor-pointer text-muted"><i class="fa-solid fa-bell fs-5"></i></div>
                <div class="dropdown">
                    <div class="d-flex align-items-center gap-2 cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width:38px; height:38px; background-color: var(--primary-teal); font-size: 0.9rem;">AS</div>
                        <div class="text-start d-none d-sm-block lh-sm">
                            <div class="fw-bold text-dark" style="font-size: 0.85rem;">Quản trị viên Hệ thống <i class="fa-solid fa-angle-down ms-1 text-muted"></i></div>
                            <div class="text-muted" style="font-size: 0.75rem;">Nguyễn Kim Huệ (Admin)</div>
                        </div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                        <li><a class="dropdown-item text-danger" href="index.php?route=auth/logout"><i class="fa-solid fa-right-from-bracket me-2"></i> Đăng xuất</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container-fluid px-4 px-xl-5 py-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1" style="font-size: 0.8rem;">
                        <li class="breadcrumb-item"><a href="index.php?route=dashboard" class="text-teal text-decoration-none"><i class="fa-solid fa-house"></i> Hệ thống Quản lý</a></li>
                        <li class="breadcrumb-item active fw-bold" aria-current="page">Tổng quan Quản lý Nhà trọ</li>
                    </ol>
                </nav>
                <h3 class="fw-bold text-dark m-0">Tổng quan Quản lý Nhà trọ <span class="text-teal">- Tòa A</span></h3>
            </div>
            <div class="border rounded-pill px-3 py-2 bg-white text-muted text-xs fw-bold shadow-sm">
                <i class="fa-regular fa-calendar me-1"></i> Hôm nay, 30/10/2026
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6 col-lg-3"><div class="overview-card bg-green-light h-100"><div class="fw-bold text-success mb-2" style="font-size: 0.8rem;">TỶ LỆ LẤP ĐẦY</div><div class="d-flex align-items-end mb-2"><h3 class="fw-bold m-0 me-2">1 / 3</h3> <span class="text-muted small">phòng</span></div><div class="progress mb-2" style="height: 6px; background-color: #bbf7d0;"><div class="progress-bar bg-success" style="width: 33%"></div></div><div class="d-flex justify-content-between text-xs fw-semibold text-success"><span><i class="fa-solid fa-circle-check"></i> 33% đang thuê</span><span class="text-muted">2 trống</span></div><div class="overview-icon icon-green"><i class="fa-solid fa-door-closed fs-6"></i></div></div></div>
            <div class="col-12 col-md-6 col-lg-3"><div class="overview-card bg-blue-light h-100"><div class="fw-bold text-primary mb-2" style="font-size: 0.8rem;">THỰC THU 2026</div><h3 class="fw-bold m-0 mb-4">0 đ</h3><div class="overview-icon icon-blue"><i class="fa-solid fa-money-bill-wave fs-6"></i></div></div></div>
            <div class="col-12 col-md-6 col-lg-3"><div class="overview-card bg-yellow-light h-100"><div class="fw-bold text-warning mb-2" style="font-size: 0.8rem;">CÔNG NỢ CHƯA THU</div><h3 class="fw-bold text-danger m-0 mb-3">0 đ</h3><div class="d-flex justify-content-between text-xs fw-semibold text-danger mt-2 border-top border-warning pt-2"><span><i class="fa-regular fa-clock"></i> Tồn đọng:</span><span>0 hóa đơn</span></div><div class="overview-icon icon-yellow"><i class="fa-solid fa-circle-exclamation fs-6"></i></div></div></div>
            <div class="col-12 col-md-6 col-lg-3"><div class="overview-card bg-purple-light h-100"><div class="fw-bold mb-2" style="font-size: 0.8rem; color: #a855f7;">KHÁCH & HỢP ĐỒNG</div><div class="d-flex align-items-end mb-4"><h3 class="fw-bold m-0 me-2 text-dark">0</h3> <span class="text-muted small">hợp đồng</span></div><div class="overview-icon icon-purple"><i class="fa-solid fa-users fs-6"></i></div></div></div>
        </div>

        <div class="invoice-section">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                <div class="fw-bold text-dark"><i class="fa-solid fa-file-invoice-dollar me-2 text-teal"></i> Hóa đơn Cần Thu Nợ (0)</div>
                <button class="btn btn-teal-custom"><i class="fa-solid fa-plus me-1"></i> Lập hóa đơn</button>
            </div>
            <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-folder-open mb-3 opacity-50" style="font-size: 3rem; color: var(--primary-teal);"></i>
                <p class="mb-0 fw-semibold">Chưa có dữ liệu hóa đơn công nợ trong Tòa nhà này.</p>
            </div>
        </div>

    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>