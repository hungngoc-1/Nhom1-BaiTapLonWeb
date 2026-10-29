<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayMate - Tổng quan Tòa nhà</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-teal: #287A74; 
            --teal-light: #e6f2f1;
            --bg-body: #f4f7f6;
        }

        body {
            background-color: var(--bg-body);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 0.9rem;
        }

        /* Navbar Styling */
        .navbar-custom {
            background: #ffffff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 0.5rem 1rem;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            color: var(--primary-teal);
            font-size: 1.1rem;
            text-decoration: none;
        }

        .brand-logo .icon-box {
            background: var(--primary-teal);
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .nav-pills .nav-link {
            color: #6c757d;
            font-weight: 500;
            padding: 0.5rem 0.8rem;
            border-radius: 8px;
            transition: all 0.2s;
            font-size: 0.85rem;
        }

        .nav-pills .nav-link:hover {
            background-color: var(--teal-light);
            color: var(--primary-teal);
        }

        .nav-pills .nav-link.active {
            background-color: var(--primary-teal);
            color: white;
        }
    </style>
</head>
<body>

    <!-- Header / Navbar Chuẩn StayMate -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container-fluid px-3">
            <a class="brand-logo me-4" href="index.php?route=dashboard">
                <div class="icon-box"><i class="fa-solid fa-building-shield"></i></div>
                <div>
                    <div style="line-height: 1.2;">StayMate</div>
                    <small class="text-muted" style="font-size: 0.65rem; font-weight: normal;">Quản Lý Nhà Trọ</small>
                </div>
            </a>

            <!-- Menu Tabs -->
            <ul class="nav nav-pills me-auto d-none d-lg-flex gap-1">
                <li class="nav-item"><a class="nav-link" href="index.php?route=dashboard"><i class="fa-solid fa-city me-1"></i> Tòa nhà</a></li>
                <li class="nav-item"><a class="nav-link active" href="index.php?route=overview"><i class="fa-solid fa-chart-pie me-1"></i> Tổng quan</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=rooms"><i class="fa-solid fa-door-open me-1"></i> Phòng trọ</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-users me-1"></i> Khách thuê</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-file-contract me-1"></i> Hợp đồng</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-bolt me-1"></i> Điện Nước</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-file-invoice-dollar me-1"></i> Hóa đơn & Thu tiền</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-chart-column me-1"></i> Báo cáo</a></li>
            </ul>

            <!-- Tài khoản Admin -->
            <div class="d-flex align-items-center gap-3">
                <div class="position-relative cursor-pointer">
                    <i class="fa-solid fa-bell fs-5 text-secondary"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">8</span>
                </div>
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" data-bs-toggle="dropdown">
                        <img src="https://ui-avatars.com/api/?name=Admin+StayMate&background=287A74&color=fff" class="rounded-circle me-2" width="35" height="35" alt="Admin">
                        <div class="text-start d-none d-sm-block">
                            <div class="fw-bold" style="font-size: 0.8rem;">Quản trị viên Hệ thống</div>
                            <small class="text-muted" style="font-size: 0.7rem;">Nguyễn Kim Huệ (Admin)</small>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- NỘI DUNG TỔNG QUAN -->
    <div class="container-fluid px-4 my-4">
        <!-- Tiêu đề trang & Breadcrumb -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1" style="font-size: 0.85rem;">
                        <li class="breadcrumb-item"><a href="index.php?route=dashboard" class="text-decoration-none text-success" style="color: var(--primary-teal) !important;"><i class="fa-solid fa-house me-1"></i> Hệ thống Quản lý</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Tổng quan Quản lý Nhà trọ</li>
                    </ol>
                </nav>
                <h3 class="fw-bold mb-0 text-dark">
                    Tổng quan Quản lý Nhà trọ
                    <?php if (isset($_GET['building_id']) && $_GET['building_id'] !== 'all'): ?>
                        <span style="color: var(--primary-teal);"> - Tòa <?= htmlspecialchars($_GET['building_id']) ?></span>
                    <?php endif; ?>
                </h3>
            </div>
            <div>
                <button class="btn btn-light border bg-white shadow-sm text-muted">
                    <i class="fa-regular fa-calendar me-2"></i> Hôm nay, <?= date('d/m/Y') ?>
                </button>
            </div>
        </div>

        <!-- 4 Thẻ Thống kê -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 rounded-3 p-3">
                    <div class="text-muted small fw-bold text-uppercase mb-2">Tỷ lệ lấp đầy</div>
                    <div class="d-flex justify-content-between align-items-end">
                        <h3 class="fw-bold mb-0 text-dark"><?= $stats['rented_rooms'] ?? 18 ?> / <?= $stats['total_rooms'] ?? 24 ?> <span class="fs-6 text-muted fw-normal">phòng</span></h3>
                        <div class="p-2 bg-success bg-opacity-10 text-success rounded"><i class="fa-solid fa-door-closed"></i></div>
                    </div>
                    <div class="mt-3 progress" style="height: 4px;">
                        <div class="progress-bar bg-success" style="width: 75%"></div>
                    </div>
                    <div class="d-flex justify-content-between mt-2 small">
                        <span class="text-success"><i class="fa-solid fa-circle-check me-1"></i>75% đang thuê</span>
                        <span class="text-muted">6 trống</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 rounded-3 p-3">
                    <div class="text-muted small fw-bold text-uppercase mb-2">Thực thu 2026</div>
                    <div class="d-flex justify-content-between align-items-end">
                        <h3 class="fw-bold mb-0 text-dark"><?= number_format($stats['monthly_revenue'] ?? 20595000, 0, ',', '.') ?> đ</h3>
                        <div class="p-2 bg-info bg-opacity-10 text-info rounded"><i class="fa-solid fa-money-bill-wave"></i></div>
                    </div>
                    <hr class="text-muted my-2">
                    <div class="d-flex justify-content-between mt-1 small">
                        <span class="text-muted">Tổng phát sinh:</span>
                        <span class="fw-medium text-dark">29.225.000 đ</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 rounded-3 p-3">
                    <div class="text-muted small fw-bold text-uppercase mb-2">Công nợ chưa thu</div>
                    <div class="d-flex justify-content-between align-items-end">
                        <h3 class="fw-bold mb-0 text-danger">8.630.000 đ</h3>
                        <div class="p-2 bg-danger bg-opacity-10 text-danger rounded"><i class="fa-solid fa-circle-exclamation"></i></div>
                    </div>
                    <hr class="text-muted my-2">
                    <div class="d-flex justify-content-between mt-1 small">
                        <span class="text-danger"><i class="fa-regular fa-clock me-1"></i> Tồn đọng:</span>
                        <span class="fw-medium text-danger">3 hóa đơn</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 rounded-3 p-3">
                    <div class="text-muted small fw-bold text-uppercase mb-2">Khách & Hợp đồng</div>
                    <div class="d-flex justify-content-between align-items-end">
                        <h3 class="fw-bold mb-0 text-dark"><?= $stats['total_tenants'] ?? 4 ?> <span class="fs-6 text-muted fw-normal">lưu trú</span></h3>
                        <div class="p-2 bg-success bg-opacity-10 text-success rounded"><i class="fa-solid fa-users"></i></div>
                    </div>
                    <hr class="text-muted my-2">
                    <div class="d-flex justify-content-between mt-1 small">
                        <span class="text-muted">Hợp đồng hiệu lực:</span>
                        <span class="fw-medium text-dark">4 hợp đồng</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Cột Trái: Bảng Hóa đơn -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0"><i class="fa-solid fa-file-invoice me-2" style="color: var(--primary-teal);"></i> Hóa đơn Cần Thu Nợ (3)</h6>
                        <button class="btn btn-sm text-white" style="background-color: var(--primary-teal);"><i class="fa-solid fa-plus me-1"></i> Lập hóa đơn</button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 text-center" style="font-size: 0.9rem;">
                                <thead class="table-light text-muted small">
                                    <tr>
                                        <th>MÃ HĐ</th>
                                        <th>PHÒNG / KHÁCH</th>
                                        <th>KỲ</th>
                                        <th>CÒN PHẢI THU</th>
                                        <th>HẠN ĐÓNG</th>
                                        <th>THAO TÁC</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-muted">INV-2610-P201</td>
                                        <td><span class="badge bg-success bg-opacity-10 text-success me-1">P201</span> P. M. Tuấn</td>
                                        <td>T10/26</td>
                                        <td class="fw-medium">3.605.000 đ</td>
                                        <td>15/10 <span class="badge bg-danger bg-opacity-10 text-danger ms-1">Quá hạn</span></td>
                                        <td>
                                            <button class="btn btn-sm text-white" style="background-color: var(--primary-teal);">Thu</button>
                                            <button class="btn btn-sm btn-outline-secondary">Chi tiết</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">INV-2610-P101</td>
                                        <td><span class="badge bg-success bg-opacity-10 text-success me-1">P101</span> N. V. An</td>
                                        <td>T10/26</td>
                                        <td class="fw-medium">1.505.000 đ</td>
                                        <td>25/10 <span class="badge bg-warning bg-opacity-10 text-warning ms-1">1 phần</span></td>
                                        <td>
                                            <button class="btn btn-sm text-white" style="background-color: var(--primary-teal);">Thu</button>
                                            <button class="btn btn-sm btn-outline-secondary">Chi tiết</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">INV-2610-P202</td>
                                        <td><span class="badge bg-success bg-opacity-10 text-success me-1">P202</span> V. T. T. Hà</td>
                                        <td>T10/26</td>
                                        <td class="fw-medium">3.520.000 đ</td>
                                        <td>28/10</td>
                                        <td>
                                            <button class="btn btn-sm text-white" style="background-color: var(--primary-teal);">Thu</button>
                                            <button class="btn btn-sm btn-outline-secondary">Chi tiết</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cột Phải: Thông báo & Biểu đồ -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0"><i class="fa-solid fa-bell me-2" style="color: var(--primary-teal);"></i> Đặt trước Phòng <span class="badge bg-danger rounded-pill ms-1">3 mới</span></h6>
                        <a href="#" class="text-decoration-none small text-muted">Lịch sử</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0" style="font-size: 0.85rem;">
                                <thead class="text-muted small">
                                    <tr>
                                        <th>PHÒNG</th>
                                        <th>KHÁCH ĐẶT</th>
                                        <th>VÀO Ở</th>
                                        <th>XỬ LÝ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="badge bg-success bg-opacity-10 text-success">P103</span></td>
                                        <td>Nguyễn Văn B <span class="text-muted">(0987654321)</span></td>
                                        <td>29/10</td>
                                        <td class="text-success"><i class="fa-solid fa-circle-check me-1"></i> Đã LH</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-bottom-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0"><i class="fa-regular fa-calendar-xmark me-2 text-warning"></i> Sắp hết hạn (60 ngày)</h6>
                        <button class="btn btn-sm btn-outline-secondary">Quản lý HĐ</button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0" style="font-size: 0.85rem;">
                                <thead class="text-muted small">
                                    <tr>
                                        <th>PHÒNG</th>
                                        <th>KHÁCH THUÊ</th>
                                        <th>HẾT HẠN</th>
                                        <th>CÒN LẠI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><span class="badge bg-success bg-opacity-10 text-success">P201</span></td>
                                        <td>Phạm Tuấn <span class="text-muted">(0912345003)</span></td>
                                        <td>05/11/26</td>
                                        <td><span class="badge bg-warning bg-opacity-10 text-warning">Còn 9 ngày</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-top py-3 mt-5 text-muted small">
        <div class="container-fluid px-4 d-flex justify-content-between align-items-center">
            <div><i class="fa-solid fa-building-shield me-1" style="color: var(--primary-teal);"></i> © 2026 StayMate - Quản lý Nhà trọ Chuyên nghiệp</div>
            <div class="d-flex gap-3">
                <span><i class="fa-solid fa-shield-halved text-success me-1"></i> WAMP MVC Architecture</span>
                <span><i class="fa-solid fa-bolt text-warning me-1"></i> Tự động hóa Điện, Nước & Hóa đơn</span>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>