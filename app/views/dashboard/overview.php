<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayMate - Tổng quan Tòa nhà</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-teal: #36837c;
            --primary-hover: #2a6a64;
            --bg-body: #f4f8f7;
            --card-radius: 24px;
        }

        body {
            background-color: var(--bg-body);
            font-family: 'Nunito', sans-serif;
            font-size: 0.9rem;
            color: #334155;
            min-height: 100vh;
        }

        /* Navbar */
        .navbar-custom {
            background-color: #ffffff;
            padding: 10px 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-bottom: 2px solid #e2e8f0;
        }
        .brand-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            flex-shrink: 0;
        }
        .brand-logo i {
            color: var(--primary-teal);
            font-size: 2rem;
        }
        .brand-text {
            color: var(--primary-teal);
            font-weight: 800;
            font-size: 1.2rem;
            line-height: 1.1;
        }
        
        .nav-pills { gap: 2px; }
        .nav-pills .nav-link {
            color: #64748b;
            font-weight: 700;
            font-size: 0.8rem;
            padding: 7px 11px;
            border-radius: 16px;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .nav-pills .nav-link i { margin-right: 4px; }
        .nav-pills .nav-link:hover { color: var(--primary-teal); background: #e6f3f1; }
        .nav-pills .nav-link.active {
            background-color: var(--primary-teal);
            color: white;
            box-shadow: 0 4px 12px rgba(54, 131, 124, 0.3);
        }

        /* CÁC THẺ CARD MÀU SẮC SINH ĐỘNG NỔI KHỐI 3D */
        .stat-card {
            border-radius: var(--card-radius);
            border: 2px solid;
            padding: 24px;
            box-shadow: 0 12px 35px rgba(15, 23, 42, 0.08);
            height: 100%;
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-4px); }

        /* Phối màu riêng biệt cho từng thẻ card thống kê */
        .card-green { background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-color: #86efac; }
        .card-blue { background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-color: #7dd3fc; }
        .card-orange { background: linear-gradient(135deg, #fffbeb00 0%, #fef3c7 100%); border-color: #fde047; background-color: #fffdf5; }
        .card-purple { background: linear-gradient(135deg, #fdf4ff 0%, #fae8ff 100%); border-color: #f0abfc; background-color: #fefbff; }

        /* Khung bảng nội dung */
        .content-card {
            background: #ffffff;
            border-radius: var(--card-radius);
            border: 2px solid #cbd5e1;
            box-shadow: 0 12px 35px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .btn-teal-custom {
            background-color: var(--primary-teal);
            color: white;
            border-radius: 16px;
            font-weight: 700;
            font-size: 0.85rem;
            padding: 8px 16px;
            border: none;
            box-shadow: 0 4px 12px rgba(54, 131, 124, 0.3);
            transition: 0.2s;
        }
        .btn-teal-custom:hover { background-color: var(--primary-hover); color: white; }

        .text-teal { color: var(--primary-teal) !important; }
        .text-xs { font-size: 0.8rem; }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container-fluid px-3 px-xl-4">
            <a class="brand-logo me-3" href="index.php?route=dashboard">
                <i class="fa-solid fa-house-chimney"></i>
                <div>
                    <div class="brand-text">StayMate</div>
                    <div class="text-muted text-xs">Quản Lý Nhà Trọ</div>
                </div>
            </a>

            <ul class="nav nav-pills me-auto d-none d-lg-flex">
                <li class="nav-item"><a class="nav-link" href="index.php?route=dashboard"><i class="fa-solid fa-city"></i> Tòa nhà</a></li>
                <li class="nav-item"><a class="nav-link active" href="index.php?route=overview"><i class="fa-solid fa-chart-pie"></i> Tổng quan</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=rooms"><i class="fa-solid fa-door-open"></i> Phòng trọ</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-users"></i> Khách thuê</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-file-contract"></i> Hợp đồng</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-bolt"></i> Điện Nước</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-file-invoice-dollar"></i> Hóa đơn</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-chart-column"></i> Báo cáo</a></li>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <div class="position-relative cursor-pointer text-muted">
                    <i class="fa-solid fa-bell fs-5"></i>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                </div>
                <div class="dropdown d-flex align-items-center gap-2">
                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width:38px; height:38px; background-color: var(--primary-teal); font-size: 0.9rem;">AS</div>
                    <div class="text-start d-none d-sm-block lh-sm">
                        <div class="fw-bold text-dark" style="font-size: 0.85rem;">Quản trị viên Hệ thống <i class="fa-solid fa-angle-down ms-1 text-muted"></i></div>
                        <div class="text-muted" style="font-size: 0.75rem;">Nguyễn Kim Huệ (Admin)</div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container-fluid px-4 px-xl-5 py-4">
        
        <!-- Tiêu đề & Breadcrumb -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 text-xs fw-semibold">
                        <li class="breadcrumb-item"><a href="index.php?route=dashboard" class="text-decoration-none text-teal"><i class="fa-solid fa-house me-1"></i> Hệ thống Quản lý</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Tổng quan Quản lý Nhà trọ</li>
                    </ol>
                </nav>
                <h3 class="fw-bold mb-0 text-dark">
                    Tổng quan Quản lý Nhà trọ
                    <?php if (isset($_GET['building_id']) && $_GET['building_id'] !== 'all'): ?>
                        <span class="text-teal"> - Tòa <?= htmlspecialchars($_GET['building_id']) ?></span>
                    <?php endif; ?>
                </h3>
            </div>
            <div>
                <button class="btn btn-light border bg-white shadow-sm text-muted rounded-pill px-3 py-2 fw-bold text-xs">
                    <i class="fa-regular fa-calendar me-2 text-teal"></i> Hôm nay, <?= date('d/m/Y') ?>
                </button>
            </div>
        </div>

        <!-- 4 Thẻ Thống kê Màu Sắc Sinh Động -->
        <div class="row g-4 mb-4">
            <!-- Tỷ lệ lấp đầy (Xanh lá) -->
            <div class="col-md-3">
                <div class="stat-card card-green">
                    <div class="text-success text-xs fw-bold text-uppercase mb-2">Tỷ lệ lấp đầy</div>
                    <div class="d-flex justify-content-between align-items-end mb-3">
                        <h3 class="fw-bold mb-0 text-dark">-- / -- <span class="fs-6 text-muted fw-normal">phòng</span></h3>
                        <div class="p-2 bg-success text-white rounded-circle shadow-sm"><i class="fa-solid fa-door-closed"></i></div>
                    </div>
                    <div class="progress mb-2 bg-white" style="height: 6px;">
                        <div class="progress-bar bg-success rounded" style="width: 0%"></div>
                    </div>
                    <div class="d-flex justify-content-between text-xs fw-bold text-success">
                        <span><i class="fa-solid fa-circle-check me-1"></i>0% đang thuê</span>
                        <span class="text-muted">-- trống</span>
                    </div>
                </div>
            </div>

            <!-- Thực thu (Xanh dương) -->
            <div class="col-md-3">
                <div class="stat-card card-blue">
                    <div class="text-primary text-xs fw-bold text-uppercase mb-2">Thực thu <?= date('Y') ?></div>
                    <div class="d-flex justify-content-between align-items-end mb-3">
                        <h3 class="fw-bold mb-0 text-dark">0 đ</h3>
                        <div class="p-2 bg-primary text-white rounded-circle shadow-sm"><i class="fa-solid fa-money-bill-wave"></i></div>
                    </div>
                    <hr class="text-muted my-2 opacity-25">
                    <div class="d-flex justify-content-between text-xs">
                        <span class="text-muted">Tổng phát sinh:</span>
                        <span class="fw-bold text-primary">0 đ</span>
                    </div>
                </div>
            </div>

            <!-- Công nợ (Cam ấm) -->
            <div class="col-md-3">
                <div class="stat-card card-orange">
                    <div class="text-warning text-xs fw-bold text-uppercase mb-2" style="color: #b45309 !important;">Công nợ chưa thu</div>
                    <div class="d-flex justify-content-between align-items-end mb-3">
                        <h3 class="fw-bold mb-0 text-danger">0 đ</h3>
                        <div class="p-2 bg-warning text-white rounded-circle shadow-sm"><i class="fa-solid fa-circle-exclamation"></i></div>
                    </div>
                    <hr class="text-muted my-2 opacity-25">
                    <div class="d-flex justify-content-between text-xs">
                        <span class="text-danger fw-semibold"><i class="fa-regular fa-clock me-1"></i> Tồn đọng:</span>
                        <span class="fw-bold text-danger">0 hóa đơn</span>
                    </div>
                </div>
            </div>

            <!-- Khách & Hợp đồng (Tím nhạt) -->
            <div class="col-md-3">
                <div class="stat-card card-purple">
                    <div class="text-xs fw-bold text-uppercase mb-2" style="color: #a21caf !important;">Khách & Hợp đồng</div>
                    <div class="d-flex justify-content-between align-items-end mb-3">
                        <h3 class="fw-bold mb-0 text-dark">0 <span class="fs-6 text-muted fw-normal">lưu trú</span></h3>
                        <div class="p-2 text-white rounded-circle shadow-sm" style="background-color: #c084fc;"><i class="fa-solid fa-users"></i></div>
                    </div>
                    <hr class="text-muted my-2 opacity-25">
                    <div class="d-flex justify-content-between text-xs">
                        <span class="text-muted">Hợp đồng hiệu lực:</span>
                        <span class="fw-bold" style="color: #a21caf;">0 hợp đồng</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phần Bảng Khung Trống Chờ Dữ Liệu -->
        <div class="row g-4">
            <!-- Hóa đơn cần thu nợ -->
            <div class="col-lg-7">
                <div class="content-card h-100">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-file-invoice me-2 text-teal"></i> Hóa đơn Cần Thu Nợ (0)</h6>
                        <button class="btn btn-teal-custom btn-sm"><i class="fa-solid fa-plus me-1"></i> Lập hóa đơn</button>
                    </div>
                    <div class="card-body p-4 text-center text-muted">
                        <div class="py-5">
                            <i class="fa-solid fa-folder-open fs-1 text-teal opacity-50 mb-3"></i>
                            <p class="fw-semibold mb-0">Chưa có dữ liệu hóa đơn công nợ trong hệ thống.</p>
                            <small class="text-xs">Dữ liệu sẽ hiển thị khi kết nối Cơ sở dữ liệu.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cột Phải: Đặt trước phòng & Sắp hết hạn -->
            <div class="col-lg-5 d-flex flex-column gap-4">
                <!-- Đặt trước phòng -->
                <div class="content-card">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-bell me-2 text-teal"></i> Đặt trước Phòng <span class="badge bg-secondary rounded-pill ms-1">0 mới</span></h6>
                        <a href="#" class="text-decoration-none text-xs text-muted fw-semibold">Lịch sử</a>
                    </div>
                    <div class="card-body p-3 text-center text-muted">
                        <small class="text-xs"><i class="fa-solid fa-circle-info me-1"></i> Không có lịch đặt phòng chờ xử lý.</small>
                    </div>
                </div>

                <!-- Sắp hết hạn -->
                <div class="content-card">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fa-regular fa-calendar-xmark me-2 text-warning"></i> Sắp hết hạn (60 ngày)</h6>
                        <button class="btn btn-sm btn-outline-secondary py-1 px-3 text-xs fw-bold">Quản lý HĐ</button>
                    </div>
                    <div class="card-body p-3 text-center text-muted">
                        <small class="text-xs"><i class="fa-solid fa-circle-check text-success me-1"></i> Không có hợp đồng nào sắp hết hạn.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="py-4 mt-5 text-muted text-xs border-top bg-white">
        <div class="container-fluid px-4 px-xl-5 d-flex flex-column flex-md-row justify-content-between align-items-center">
            <div class="mb-2 mb-md-0 fw-bold text-dark">
                <i class="fa-solid fa-building me-1 text-teal"></i> © 2026 StayMate - Quản lý Nhà trọ Chuyên nghiệp
            </div>
            <div class="d-flex gap-4 fw-bold">
                <span><i class="fa-solid fa-shield-halved text-teal me-1"></i> WAMP MVC Architecture</span>
                <span><i class="fa-solid fa-bolt text-warning me-1"></i> Tự động hóa Điện, Nước & Hóa đơn</span>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>