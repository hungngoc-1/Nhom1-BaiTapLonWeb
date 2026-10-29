<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayMate - Quản Lý Nhà Trọ</title>
    <!-- Thêm font Nunito cho giống thiết kế bo tròn mượt mà -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-teal: #36837c;
            --primary-hover: #2a6a64;
            --bg-body: #f8fbfb;
            --bg-nav: #ffffff;
            --card-radius: 20px;
        }

        body {
            background-color: var(--bg-body);
            font-family: 'Nunito', sans-serif;
            font-size: 0.9rem;
            color: #4a5568;
            /* Thêm background mờ lượn sóng dưới đáy giống ảnh */
            background-image: radial-gradient(circle at 100% 100%, #e6f3f1 0%, transparent 40%),
                              radial-gradient(circle at 0% 100%, #e6f3f1 0%, transparent 30%);
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
        }

        /* Navbar */
        .navbar-custom {
            background-color: var(--bg-nav);
            padding: 10px 24px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.03);
            border-bottom: 1px solid #edf2f7;
        }
        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
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
        .nav-pills .nav-link {
            color: #64748b;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 8px 16px;
            border-radius: 20px;
            margin: 0 2px;
            transition: all 0.2s;
        }
        .nav-pills .nav-link i { margin-right: 6px; }
        .nav-pills .nav-link:hover { color: var(--primary-teal); background: #f1f5f9; }
        .nav-pills .nav-link.active {
            background-color: #e6f2f1;
            color: var(--primary-teal);
        }

        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(90deg, #fcf4e3 0%, #ebf5f4 100%);
            border-radius: var(--card-radius);
            padding: 20px 25px;
            position: relative;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        }
        .icon-check-circle {
            background: var(--primary-teal);
            color: white;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        /* Toolbar */
        .toolbar-select {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 8px 16px;
            color: var(--primary-teal);
            font-weight: 700;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        .btn-outline-custom {
            border: 1px solid #cbd5e1;
            color: #475569;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 8px 16px;
            background: white;
        }
        .btn-teal-custom {
            background-color: var(--primary-teal);
            color: white;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 8px 16px;
            border: none;
        }

        /* Building Cards */
        .building-card {
            background: #ffffff;
            border-radius: var(--card-radius);
            border: none;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
            height: 100%;
            position: relative;
            transition: transform 0.2s;
        }
        .building-card:hover {
            transform: translateY(-5px);
        }
        .badge-bld {
            background-color: #d1fae5;
            color: #047857;
            font-weight: 800;
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
        }
        /* Illustration Placeholder */
        .card-illustration {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 100px;
            height: 80px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 80"><rect x="20" y="20" width="40" height="60" fill="%23a7dcd6" rx="4"/><rect x="50" y="10" width="30" height="70" fill="%236bb5b0" rx="4"/><circle cx="15" cy="65" r="15" fill="%232b7a78" opacity="0.8"/><circle cx="85" cy="55" r="12" fill="%232b7a78" opacity="0.6"/></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: right top;
        }
        
        .stat-box {
            border-radius: 16px;
            padding: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        .stat-box-total { background-color: #e0f2fe; }
        .stat-box-rented { background-color: #dcfce7; }
        .stat-box-empty { background-color: #fef3c7; }
        
        .stat-val { font-size: 1.5rem; font-weight: 800; color: #1e293b; line-height: 1; }
        .stat-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: #64748b; }
        .stat-icon { font-size: 1rem; color: var(--primary-teal); opacity: 0.7; }
        
        .btn-card-main {
            background-color: var(--primary-teal);
            color: white;
            border-radius: 12px;
            font-weight: 700;
            padding: 10px;
            width: 100%;
            border: none;
            transition: 0.2s;
        }
        .btn-card-main:hover { background-color: var(--primary-hover); color: white; }
        .btn-card-folder {
            border: 1px solid var(--primary-teal);
            color: var(--primary-teal);
            border-radius: 12px;
            padding: 10px 14px;
            background: transparent;
        }

        /* Typography helpers */
        .text-teal { color: var(--primary-teal) !important; }
        .text-xs { font-size: 0.75rem; }
    </style>
</head>
<body>

    <!-- Header Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container-fluid px-2">
            <a class="brand-logo me-4" href="index.php?route=dashboard">
                <i class="fa-solid fa-house-chimney"></i>
                <div>
                    <div class="brand-text">StayMate</div>
                    <div class="text-muted text-xs">Quản Lý Nhà Trọ</div>
                </div>
            </a>

            <ul class="nav nav-pills me-auto d-none d-lg-flex">
                <li class="nav-item"><a class="nav-link active" href="index.php?route=dashboard"><i class="fa-solid fa-city"></i> Tòa nhà</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=overview"><i class="fa-solid fa-chart-pie"></i> Tổng quan</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=rooms"><i class="fa-solid fa-door-open"></i> Phòng trọ</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-users"></i> Khách thuê</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-file-contract"></i> Hợp đồng</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-bolt"></i> Điện Nước</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-file-invoice-dollar"></i> Hóa đơn & Thu tiền</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-chart-column"></i> Báo cáo</a></li>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <div class="position-relative cursor-pointer text-muted">
                    <i class="fa-solid fa-bell fs-5"></i>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                </div>
                <div class="dropdown d-flex align-items-center gap-2">
                    <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width:36px; height:36px; background-color: var(--primary-teal); font-size: 0.85rem;">AS</div>
                    <div class="text-start d-none d-sm-block lh-sm">
                        <div class="fw-bold text-dark" style="font-size: 0.8rem;">Quản trị viên Hệ thống <i class="fa-solid fa-angle-down ms-1 text-muted"></i></div>
                        <div class="text-muted" style="font-size: 0.7rem;">Nguyễn Kim Huệ (Admin)</div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid px-4 py-4" style="max-width: 1400px; margin: 0 auto;">
        
        <!-- Banner Xin chào -->
        <div class="welcome-banner d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-check-circle"><i class="fa-solid fa-check"></i></div>
                <div>
                    <h5 class="fw-bold mb-1 text-teal">Xin chào, Quản trị viên Hệ thống (Admin)!</h5>
                    <div class="text-muted text-xs">Chúc bạn một ngày làm việc hiệu quả!</div>
                </div>
            </div>
            <!-- Hình ảnh trang trí nhánh lá góc phải (dùng icon mô phỏng) -->
            <div class="text-teal opacity-50 pe-4" style="font-size: 2rem;">
                <i class="fa-solid fa-leaf"></i>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="toolbar-select">
                <i class="fa-solid fa-building text-teal"></i> Đang xem: Tất cả 3 Tòa nhà <i class="fa-solid fa-angle-down ms-2"></i>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-custom"><i class="fa-solid fa-eye me-1"></i> Xem Tất cả Tòa nhà</button>
                <button class="btn btn-teal-custom"><i class="fa-solid fa-plus me-1"></i> Thêm Tòa nhà</button>
            </div>
        </div>

        <!-- Grid 3 Tòa Nhà -->
        <div class="row g-4">
            
            <!-- Tòa A -->
            <div class="col-12 col-lg-4">
                <div class="building-card">
                    <span class="badge-bld">BLD-A</span>
                    <i class="fa-solid fa-ellipsis-vertical position-absolute text-muted" style="top:25px; right:25px; cursor:pointer;"></i>
                    <div class="card-illustration"></div>
                    
                    <h5 class="fw-bold text-dark mt-3 mb-2">Tòa A - StayMate Sunrise</h5>
                    <div class="text-muted text-xs mb-3"><i class="fa-solid fa-location-dot text-teal me-1"></i> Số 128 Cầu Giấy, Quận Cầu Giấy, TP. Hà Nội</div>
                    
                    <div class="d-flex justify-content-between text-xs mb-4">
                        <span class="text-muted"><i class="fa-regular fa-id-badge me-1"></i> Quản lý riêng:</span>
                        <span class="fw-bold text-dark">Quản lý Tòa A (Nguyễn Văn Quản) <span class="text-teal fw-normal">(nhanvien)</span></span>
                    </div>

                    <div class="row g-2 mb-4">
                        <div class="col-4">
                            <div class="stat-box stat-box-total">
                                <i class="fa-regular fa-building stat-icon mb-1"></i>
                                <div class="stat-val">15</div>
                                <div class="stat-label">Tổng</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-box stat-box-rented">
                                <i class="fa-solid fa-user-group stat-icon mb-1 text-success"></i>
                                <div class="stat-val text-success">12</div>
                                <div class="stat-label">Đang thuê</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-box stat-box-empty">
                                <i class="fa-solid fa-house-chimney stat-icon mb-1 text-warning"></i>
                                <div class="stat-val text-warning">3</div>
                                <div class="stat-label">Phòng trống</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-auto">
                        <a href="index.php?route=overview&building_id=A" class="btn btn-card-main text-decoration-none text-center d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-arrow-right-to-bracket me-2"></i> Vào quản lý Tòa này
                        </a>
                        <button class="btn btn-card-folder"><i class="fa-regular fa-folder-open"></i></button>
                    </div>
                </div>
            </div>

            <!-- Tòa B -->
            <div class="col-12 col-lg-4">
                <div class="building-card">
                    <span class="badge-bld">BLD-B</span>
                    <i class="fa-solid fa-ellipsis-vertical position-absolute text-muted" style="top:25px; right:25px; cursor:pointer;"></i>
                    <div class="card-illustration"></div>
                    
                    <h5 class="fw-bold text-dark mt-3 mb-2">Tòa B - StayMate Emerald</h5>
                    <div class="text-muted text-xs mb-3"><i class="fa-solid fa-location-dot text-teal me-1"></i> Số 45 Nguyễn Chí Thanh, Quận Đống Đa, TP. Hà Nội</div>
                    
                    <div class="d-flex justify-content-between text-xs mb-4">
                        <span class="text-muted"><i class="fa-regular fa-id-badge me-1"></i> Quản lý riêng:</span>
                        <span class="fw-bold text-dark">Quản lý Tòa B (Trần Thị Bích) <span class="text-teal fw-normal">(quanly_b)</span></span>
                    </div>

                    <div class="row g-2 mb-4">
                        <div class="col-4">
                            <div class="stat-box stat-box-total">
                                <i class="fa-regular fa-building stat-icon mb-1"></i>
                                <div class="stat-val">20</div>
                                <div class="stat-label">Tổng</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-box stat-box-rented">
                                <i class="fa-solid fa-user-group stat-icon mb-1 text-success"></i>
                                <div class="stat-val text-success">18</div>
                                <div class="stat-label">Đang thuê</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-box stat-box-empty">
                                <i class="fa-solid fa-house-chimney stat-icon mb-1 text-warning"></i>
                                <div class="stat-val text-warning">2</div>
                                <div class="stat-label">Phòng trống</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-auto">
                        <a href="index.php?route=overview&building_id=B" class="btn btn-card-main text-decoration-none text-center d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-arrow-right-to-bracket me-2"></i> Vào quản lý Tòa này
                        </a>
                        <button class="btn btn-card-folder"><i class="fa-regular fa-folder-open"></i></button>
                    </div>
                </div>
            </div>

            <!-- Tòa C -->
            <div class="col-12 col-lg-4">
                <div class="building-card">
                    <span class="badge-bld">BLD-C</span>
                    <i class="fa-solid fa-ellipsis-vertical position-absolute text-muted" style="top:25px; right:25px; cursor:pointer;"></i>
                    <div class="card-illustration"></div>
                    
                    <h5 class="fw-bold text-dark mt-3 mb-2">Tòa C - StayMate Horizon</h5>
                    <div class="text-muted text-xs mb-3"><i class="fa-solid fa-location-dot text-teal me-1"></i> Số 88 Nguyễn Trãi, Quận Thanh Xuân, TP. Hà Nội</div>
                    
                    <div class="d-flex justify-content-between text-xs mb-4">
                        <span class="text-muted"><i class="fa-regular fa-id-badge me-1"></i> Quản lý riêng:</span>
                        <span class="fw-bold text-dark">Quản lý Tòa C (Lê Hoàng Nam) <span class="text-teal fw-normal">(quanly_c)</span></span>
                    </div>

                    <div class="row g-2 mb-4">
                        <div class="col-4">
                            <div class="stat-box stat-box-total">
                                <i class="fa-regular fa-building stat-icon mb-1"></i>
                                <div class="stat-val">10</div>
                                <div class="stat-label">Tổng</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-box stat-box-rented">
                                <i class="fa-solid fa-user-group stat-icon mb-1 text-success"></i>
                                <div class="stat-val text-success">7</div>
                                <div class="stat-label">Đang thuê</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-box stat-box-empty">
                                <i class="fa-solid fa-house-chimney stat-icon mb-1 text-warning"></i>
                                <div class="stat-val text-warning">3</div>
                                <div class="stat-label">Phòng trống</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-auto">
                        <a href="index.php?route=overview&building_id=C" class="btn btn-card-main text-decoration-none text-center d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-arrow-right-to-bracket me-2"></i> Vào quản lý Tòa này
                        </a>
                        <button class="btn btn-card-folder"><i class="fa-regular fa-folder-open"></i></button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Footer -->
    <footer class="py-4 mt-5 text-muted text-xs" style="background: transparent;">
        <div class="container-fluid px-4 d-flex flex-column flex-md-row justify-content-between align-items-center">
            <div class="mb-2 mb-md-0 fw-semibold">
                <i class="fa-solid fa-building me-1 text-teal"></i> © 2026 StayMate - Quản lý Nhà trọ Chuyên nghiệp
            </div>
            <div class="d-flex gap-4 fw-semibold">
                <span><i class="fa-solid fa-shield-halved text-teal me-1"></i> WAMP MVC Architecture</span>
                <span><i class="fa-solid fa-bolt text-warning me-1"></i> Tự động hóa Điện, Nước & Hóa đơn</span>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>