<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayMate - Quản Lý Nhà Trọ</title>
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

        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, #fffbf0 0%, #e6f5f3 100%);
            border-radius: var(--card-radius);
            padding: 24px 32px;
            border: 2px solid #cbd5e1;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.07);
        }
        .icon-check-circle {
            background: var(--primary-teal);
            color: white;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            box-shadow: 0 4px 10px rgba(54, 131, 124, 0.4);
        }

        /* Toolbar */
        .toolbar-select {
            background: #ffffff;
            border: 2px solid #cbd5e1;
            border-radius: 20px;
            padding: 10px 20px;
            color: var(--primary-teal);
            font-weight: 800;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            cursor: pointer;
        }
        .btn-outline-custom {
            border: 2px solid #cbd5e1;
            color: #475569;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.85rem;
            padding: 10px 20px;
            background: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: 0.2s;
        }
        .btn-outline-custom:hover { background: #f8fafc; border-color: #94a3b8; }
        
        .btn-teal-custom {
            background-color: var(--primary-teal);
            color: white;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.85rem;
            padding: 10px 20px;
            border: none;
            box-shadow: 0 6px 16px rgba(54, 131, 124, 0.35);
            transition: 0.2s;
        }
        .btn-teal-custom:hover { background-color: var(--primary-hover); transform: translateY(-2px); }

        /* THẺ TÒA NHÀ */
        .building-card {
            background: #ffffff;
            border-radius: var(--card-radius);
            border: 2px solid #cbd5e1;
            padding: 32px;
            box-shadow: 0 16px 45px rgba(15, 23, 42, 0.1);
            height: 100%;
            position: relative;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .building-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 22px 55px rgba(15, 23, 42, 0.16);
            border-color: var(--primary-teal);
        }
        
        .badge-bld {
            background-color: #d1fae5;
            color: #047857;
            font-weight: 800;
            padding: 6px 14px;
            border-radius: 12px;
            font-size: 0.8rem;
            border: 1px solid #6ee7b7;
        }
        
        .card-illustration {
            position: absolute;
            top: 25px;
            right: 25px;
            width: 110px;
            height: 90px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 80"><rect x="20" y="20" width="40" height="60" fill="%23a7dcd6" rx="4"/><rect x="50" y="10" width="30" height="70" fill="%236bb5b0" rx="4"/><circle cx="15" cy="65" r="15" fill="%232b7a78" opacity="0.8"/><circle cx="85" cy="55" r="12" fill="%232b7a78" opacity="0.6"/></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: right top;
        }
        
        .stat-box {
            border-radius: 18px;
            padding: 16px 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: 2px solid;
            box-shadow: 0 6px 15px rgba(0,0,0,0.06);
            transition: 0.2s;
        }
        .stat-box:hover { transform: scale(1.03); }
        
        .stat-box-total { background-color: #f0fdf4; border-color: #bbf7d0; }
        .stat-box-rented { background-color: #f0fdf4; border-color: #bbf7d0; }
        .stat-box-empty { background-color: #fefce8; border-color: #fef08a; }
        
        .stat-val { font-size: 1.7rem; font-weight: 800; color: #0f172a; line-height: 1; }
        .stat-label { font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: #475569; }
        .stat-icon { font-size: 1.2rem; color: var(--primary-teal); }
        
        .btn-card-main {
            background-color: var(--primary-teal);
            color: white;
            border-radius: 16px;
            font-weight: 800;
            padding: 14px;
            width: 100%;
            border: none;
            transition: 0.2s;
            box-shadow: 0 6px 16px rgba(54, 131, 124, 0.35);
        }
        .btn-card-main:hover { background-color: var(--primary-hover); color: white; transform: translateY(-2px); }
        
        .btn-card-folder {
            border: 2px solid var(--primary-teal);
            color: var(--primary-teal);
            border-radius: 16px;
            padding: 14px 18px;
            background: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: 0.2s;
        }
        .btn-card-folder:hover { background: #e6f3f1; }

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
                <li class="nav-item"><a class="nav-link active" href="index.php?route=dashboard"><i class="fa-solid fa-city"></i> Tòa nhà</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=overview"><i class="fa-solid fa-chart-pie"></i> Tổng quan</a></li>
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
        
        <!-- Welcome Banner -->
        <div class="welcome-banner d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-check-circle"><i class="fa-solid fa-check"></i></div>
                <div>
                    <h5 class="fw-bold mb-1 text-teal">Xin chào, Quản trị viên Hệ thống (Admin)!</h5>
                    <div class="text-muted text-xs fw-semibold">Chúc bạn một ngày làm việc hiệu quả và tràn đầy năng lượng!</div>
                </div>
            </div>
            <div class="text-teal opacity-50 pe-4 d-none d-md-block" style="font-size: 2.2rem;">
                <i class="fa-solid fa-leaf"></i>
            </div>
        </div>

        <!-- Toolbar Chức năng Thêm Tòa Nhà -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="toolbar-select">
                <i class="fa-solid fa-building text-teal"></i> Đang xem: Tất cả 3 Tòa nhà <i class="fa-solid fa-angle-down ms-2"></i>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-custom" onclick="viewAllBuildings()"><i class="fa-solid fa-eye me-1"></i> Xem Tất cả Tòa nhà</button>
                <!-- Nút mở Popup Thêm Tòa Nhà -->
                <button class="btn btn-teal-custom" data-bs-toggle="modal" data-bs-target="#addBuildingModal"><i class="fa-solid fa-plus me-1"></i> Thêm Tòa nhà</button>
            </div>
        </div>

        <!-- Grid 3 Tòa Nhà -->
        <div class="row g-4">
            
            <!-- Tòa A -->
            <div class="col-12 col-lg-4" id="building-A">
                <div class="building-card">
                    <span class="badge-bld">BLD-A</span>
                    <div class="dropdown position-absolute" style="top:25px; right:20px;">
                        <i class="fa-solid fa-ellipsis-vertical text-muted fs-5 cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false"></i>
                        <ul class="dropdown-menu shadow border-0">
                            <li><a class="dropdown-item" href="#" onclick="editBuilding('A', 'Tòa A - StayMate Sunrise', 'Số 128 Cầu Giấy, Quận Cầu Giấy, TP. Hà Nội')"><i class="fa-solid fa-pen-to-square text-primary me-2"></i> Sửa thông tin</a></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteBuilding('A')"><i class="fa-solid fa-trash me-2"></i> Xóa tòa nhà</a></li>
                        </ul>
                    </div>
                    <div class="card-illustration"></div>
                    
                    <h4 class="fw-bold text-dark mt-3 mb-2">Tòa A - StayMate Sunrise</h4>
                    <div class="text-muted text-xs mb-3 fw-semibold"><i class="fa-solid fa-location-dot text-teal me-1"></i> Số 128 Cầu Giấy, Quận Cầu Giấy, TP. Hà Nội</div>
                    
                    <div class="d-flex justify-content-between text-xs mb-4 p-2 bg-light rounded-3 border">
                        <span class="text-muted fw-semibold"><i class="fa-regular fa-id-badge me-1"></i> Quản lý:</span>
                        <span class="fw-bold text-dark">Nguyễn Văn Quản <span class="text-teal fw-normal">(nhanvien)</span></span>
                    </div>

                    <div class="row g-3 mb-4">
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
                                <div class="stat-label">Trống</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-auto">
                        <a href="index.php?route=overview&building_id=A" class="btn btn-card-main text-decoration-none text-center d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-arrow-right-to-bracket me-2"></i> Vào quản lý Tòa này
                        </a>
                        <button class="btn btn-card-folder" onclick="openFolder('Tòa A')" title="Hồ sơ tòa nhà"><i class="fa-regular fa-folder-open"></i></button>
                    </div>
                </div>
            </div>

            <!-- Tòa B -->
            <div class="col-12 col-lg-4" id="building-B">
                <div class="building-card">
                    <span class="badge-bld">BLD-B</span>
                    <div class="dropdown position-absolute" style="top:25px; right:20px;">
                        <i class="fa-solid fa-ellipsis-vertical text-muted fs-5 cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false"></i>
                        <ul class="dropdown-menu shadow border-0">
                            <li><a class="dropdown-item" href="#" onclick="editBuilding('B', 'Tòa B - StayMate Emerald', 'Số 45 Nguyễn Chí Thanh, Quận Đống Đa, TP. Hà Nội')"><i class="fa-solid fa-pen-to-square text-primary me-2"></i> Sửa thông tin</a></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteBuilding('B')"><i class="fa-solid fa-trash me-2"></i> Xóa tòa nhà</a></li>
                        </ul>
                    </div>
                    <div class="card-illustration"></div>
                    
                    <h4 class="fw-bold text-dark mt-3 mb-2">Tòa B - StayMate Emerald</h4>
                    <div class="text-muted text-xs mb-3 fw-semibold"><i class="fa-solid fa-location-dot text-teal me-1"></i> Số 45 Nguyễn Chí Thanh, Quận Đống Đa, TP. Hà Nội</div>
                    
                    <div class="d-flex justify-content-between text-xs mb-4 p-2 bg-light rounded-3 border">
                        <span class="text-muted fw-semibold"><i class="fa-regular fa-id-badge me-1"></i> Quản lý:</span>
                        <span class="fw-bold text-dark">Trần Thị Bích <span class="text-teal fw-normal">(quanly_b)</span></span>
                    </div>

                    <div class="row g-3 mb-4">
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
                                <div class="stat-label">Trống</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-auto">
                        <a href="index.php?route=overview&building_id=B" class="btn btn-card-main text-decoration-none text-center d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-arrow-right-to-bracket me-2"></i> Vào quản lý Tòa này
                        </a>
                        <button class="btn btn-card-folder" onclick="openFolder('Tòa B')" title="Hồ sơ tòa nhà"><i class="fa-regular fa-folder-open"></i></button>
                    </div>
                </div>
            </div>

            <!-- Tòa C -->
            <div class="col-12 col-lg-4" id="building-C">
                <div class="building-card">
                    <span class="badge-bld">BLD-C</span>
                    <div class="dropdown position-absolute" style="top:25px; right:20px;">
                        <i class="fa-solid fa-ellipsis-vertical text-muted fs-5 cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false"></i>
                        <ul class="dropdown-menu shadow border-0">
                            <li><a class="dropdown-item" href="#" onclick="editBuilding('C', 'Tòa C - StayMate Horizon', 'Số 88 Nguyễn Trãi, Quận Thanh Xuân, TP. Hà Nội')"><i class="fa-solid fa-pen-to-square text-primary me-2"></i> Sửa thông tin</a></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteBuilding('C')"><i class="fa-solid fa-trash me-2"></i> Xóa tòa nhà</a></li>
                        </ul>
                    </div>
                    <div class="card-illustration"></div>
                    
                    <h4 class="fw-bold text-dark mt-3 mb-2">Tòa C - StayMate Horizon</h4>
                    <div class="text-muted text-xs mb-3 fw-semibold"><i class="fa-solid fa-location-dot text-teal me-1"></i> Số 88 Nguyễn Trãi, Quận Thanh Xuân, TP. Hà Nội</div>
                    
                    <div class="d-flex justify-content-between text-xs mb-4 p-2 bg-light rounded-3 border">
                        <span class="text-muted fw-semibold"><i class="fa-regular fa-id-badge me-1"></i> Quản lý:</span>
                        <span class="fw-bold text-dark">Lê Hoàng Nam <span class="text-teal fw-normal">(quanly_c)</span></span>
                    </div>

                    <div class="row g-3 mb-4">
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
                                <div class="stat-label">Trống</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-auto">
                        <a href="index.php?route=overview&building_id=C" class="btn btn-card-main text-decoration-none text-center d-flex align-items-center justify-content-center">
                            <i class="fa-solid fa-arrow-right-to-bracket me-2"></i> Vào quản lý Tòa này
                        </a>
                        <button class="btn btn-card-folder" onclick="openFolder('Tòa C')" title="Hồ sơ tòa nhà"><i class="fa-regular fa-folder-open"></i></button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL THÊM TÒA NHÀ -->
    <div class="modal fade" id="addBuildingModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light rounded-top-4">
                    <h5 class="modal-title fw-bold text-teal" id="addModalLabel"><i class="fa-solid fa-building-circle-arrow-right me-2"></i> Thêm Tòa Nhà Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="addBuildingForm" onsubmit="submitAddBuilding(event)">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Mã tòa nhà (Ví dụ: BLD-D)</label>
                            <input type="text" class="form-control" id="bldCode" required placeholder="Nhập mã tòa...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Tên tòa nhà</label>
                            <input type="text" class="form-control" id="bldName" required placeholder="Ví dụ: Tòa D - StayMate Star">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Địa chỉ chi tiết</label>
                            <input type="text" class="form-control" id="bldAddress" required placeholder="Nhập địa chỉ tại Hà Nội...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Nhân viên quản lý</label>
                            <input type="text" class="form-control" id="bldManager" required placeholder="Họ tên quản lý phụ trách">
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy bỏ</button>
                            <button type="submit" class="btn btn-teal-custom px-4">Lưu tòa nhà</button>
                        </div>
                    </form>
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

    <!-- JavaScript xử lý các chức năng tương tác -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function viewAllBuildings() {
            alert("Hệ thống đang hiển thị toàn bộ danh sách 3 tòa nhà quản lý.");
        }

        function openFolder(buildingName) {
            alert("Mở kho lưu trữ hồ sơ, hợp đồng và tài liệu của " + buildingName);
        }

        function editBuilding(id, currentName, currentAddress) {
            let newName = prompt("Cập nhật tên tòa nhà:", currentName);
            let newAddress = prompt("Cập nhật địa chỉ mới:", currentAddress);
            if (newName && newAddress) {
                alert("Đã cập nhật thành công thông tin cho Tòa " + id + "!");
                location.reload();
            }
        }

        function deleteBuilding(id) {
            if (confirm("Bạn có chắc chắn muốn xóa Tòa " + id + " khỏi hệ thống quản lý không?")) {
                let card = document.getElementById('building-' + id);
                if (card) {
                    card.style.transition = "0.5s";
                    card.style.opacity = "0";
                    setTimeout(() => card.remove(), 500);
                }
            }
        }

        function submitAddBuilding(event) {
            event.preventDefault();
            let code = document.getElementById('bldCode').value;
            let name = document.getElementById('bldName').value;
            
            alert("Đã thêm thành công tòa nhà mới: " + name + " (" + code + ")");
            
            // Đóng modal sau khi thêm
            let modalEl = document.getElementById('addBuildingModal');
            let modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();
            
            // Reset form
            document.getElementById('addBuildingForm').reset();
        }
    </script>
</body>
</html>