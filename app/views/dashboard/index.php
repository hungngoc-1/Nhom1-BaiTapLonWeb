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
        :root { --primary-teal: #36837c; --primary-hover: #2a6a64; --bg-body: #f4f8f7; --card-radius: 24px; }
        body { background-color: var(--bg-body); font-family: 'Nunito', sans-serif; font-size: 0.9rem; color: #334155; }
        
        .navbar-custom { background-color: #ffffff; padding: 10px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08); border-bottom: 2px solid #e2e8f0; }
        .brand-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .brand-logo i { color: var(--primary-teal); font-size: 2rem; }
        .brand-text { color: var(--primary-teal); font-weight: 800; font-size: 1.2rem; line-height: 1.1; }
        .cursor-pointer { cursor: pointer; }

        .welcome-banner { background: linear-gradient(135deg, #ffffff 0%, #f0fdfa 100%); border-radius: var(--card-radius); padding: 20px 28px; border: 1px solid #ccfbf1; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.04); }
        .icon-wave { background: var(--primary-teal); color: white; border-radius: 50%; width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; box-shadow: 0 4px 12px rgba(54, 131, 124, 0.3); }

        .overview-card { border-radius: 16px; padding: 20px; position: relative; overflow: hidden; border: 2px solid; box-shadow: 0 10px 25px rgba(0,0,0,0.06); transition: all 0.3s ease; }
        .overview-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.12); }
        .bg-green-light { background-color: #dcfce7; border-color: #bbf7d0; }
        .bg-blue-light { background-color: #e0f2fe; border-color: #bae6fd; }
        .bg-yellow-light { background-color: #fef9c3; border-color: #fef08a; }
        .bg-purple-light { background-color: #f3e8ff; border-color: #e9d5ff; }
        .overview-icon { position: absolute; right: 20px; top: 50%; transform: translateY(-50%); width: 36px; height: 36px; display:flex; align-items:center; justify-content:center; border-radius: 50%; color: white; }
        .icon-green { background-color: #22c55e; }
        .icon-blue { background-color: #3b82f6; }
        .icon-yellow { background-color: #eab308; }
        .icon-purple { background-color: #a855f7; }

        .toolbar-select { background: #ffffff; border: 2px solid #cbd5e1; border-radius: 20px; padding: 10px 20px; color: var(--primary-teal); font-weight: 800; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.06); cursor: pointer; }
        .btn-outline-custom { border: 2px solid #cbd5e1; color: #475569; border-radius: 20px; font-weight: 700; font-size: 0.85rem; padding: 10px 20px; background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: 0.2s; }
        .btn-outline-custom:hover { background: #f8fafc; border-color: #94a3b8; }
        .btn-teal-custom { background-color: var(--primary-teal); color: white; border-radius: 20px; font-weight: 700; font-size: 0.85rem; padding: 10px 20px; border: none; box-shadow: 0 6px 16px rgba(54, 131, 124, 0.35); transition: 0.2s; }
        
        .building-card { background: #ffffff; border-radius: var(--card-radius); border: 2px solid #cbd5e1; padding: 32px; box-shadow: 0 16px 45px rgba(15, 23, 42, 0.1); height: 100%; position: relative; transition: transform 0.25s ease, box-shadow 0.25s ease; }
        .building-card:hover { transform: translateY(-6px); box-shadow: 0 22px 55px rgba(15, 23, 42, 0.16); border-color: var(--primary-teal); }
        .badge-bld { background-color: #d1fae5; color: #047857; font-weight: 800; padding: 6px 14px; border-radius: 12px; font-size: 0.8rem; border: 1px solid #6ee7b7; }
        .card-illustration { position: absolute; top: 25px; right: 25px; width: 110px; height: 90px; background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 80"><rect x="20" y="20" width="40" height="60" fill="%23a7dcd6" rx="4"/><rect x="50" y="10" width="30" height="70" fill="%236bb5b0" rx="4"/><circle cx="15" cy="65" r="15" fill="%232b7a78" opacity="0.8"/><circle cx="85" cy="55" r="12" fill="%232b7a78" opacity="0.6"/></svg>'); background-size: contain; background-repeat: no-repeat; background-position: right top; }
        
        .stat-box { border-radius: 18px; padding: 16px 10px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; border: 2px solid; box-shadow: 0 6px 15px rgba(0,0,0,0.06); transition: 0.2s; }
        .stat-box-total { background-color: #f0fdf4; border-color: #bbf7d0; }
        .stat-box-rented { background-color: #f0fdf4; border-color: #bbf7d0; }
        .stat-box-empty { background-color: #fefce8; border-color: #fef08a; }
        .stat-val { font-size: 1.7rem; font-weight: 800; color: #0f172a; line-height: 1; }
        .stat-label { font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: #475569; }
        .stat-icon { font-size: 1.2rem; color: var(--primary-teal); }
        .btn-card-main { background-color: var(--primary-teal); color: white; border-radius: 16px; font-weight: 800; padding: 14px; width: 100%; text-decoration: none; transition: 0.2s; box-shadow: 0 6px 16px rgba(54, 131, 124, 0.35); }
        .btn-card-main:hover { background-color: var(--primary-hover); color: white; transform: translateY(-2px); }
        .btn-card-folder { border: 2px solid var(--primary-teal); color: var(--primary-teal); border-radius: 16px; padding: 14px 18px; background: white; transition: 0.2s; }
        .btn-card-folder:hover { background: #e6f3f1; }
        .text-teal { color: var(--primary-teal) !important; }
        .text-xs { font-size: 0.8rem; }
    </style>
</head>
<body>

    <!-- Navbar Không Menu, có Thông tin User -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container-fluid px-3 px-xl-4 d-flex justify-content-between align-items-center">
            <a class="brand-logo" href="index.php?route=dashboard">
                <i class="fa-solid fa-house-chimney"></i>
                <div>
                    <div class="brand-text">StayMate</div>
                    <div class="text-muted" style="font-size: 0.7rem;">Quản Lý Nhà Trọ</div>
                </div>
            </a>
            
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
        
        <div class="welcome-banner d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="icon-wave"><i class="fa-solid fa-hand-sparkles"></i></div>
                <div>
                    <h4 class="fw-bold mb-1 text-teal">Xin chào, Nguyễn Kim Huệ!</h4>
                    <div class="text-muted text-xs fw-semibold">Theo dõi tình hình kinh doanh tổng thể của toàn bộ hệ thống ngay hôm nay.</div>
                </div>
            </div>
            <div class="border rounded-pill px-3 py-2 bg-white text-muted text-xs fw-bold shadow-sm d-none d-md-block">
                <i class="fa-regular fa-calendar me-1"></i> Hôm nay, 30/10/2026
            </div>
        </div>

        <div class="row g-3 mb-5">
            <div class="col-12 col-md-6 col-lg-3"><div class="overview-card bg-green-light h-100"><div class="fw-bold text-success mb-2" style="font-size: 0.8rem;">TỶ LỆ LẤP ĐẦY</div><div class="d-flex align-items-end mb-2"><h3 class="fw-bold m-0 me-2">1 / 3</h3> <span class="text-muted small">phòng</span></div><div class="progress mb-2" style="height: 6px; background-color: #bbf7d0;"><div class="progress-bar bg-success" style="width: 33%"></div></div><div class="d-flex justify-content-between text-xs fw-semibold text-success"><span><i class="fa-solid fa-circle-check"></i> 33% đang thuê</span><span class="text-muted">2 trống</span></div><div class="overview-icon icon-green"><i class="fa-solid fa-door-closed fs-6"></i></div></div></div>
            <div class="col-12 col-md-6 col-lg-3"><div class="overview-card bg-blue-light h-100"><div class="fw-bold text-primary mb-2" style="font-size: 0.8rem;">THỰC THU 2026</div><h3 class="fw-bold m-0 mb-4">0 đ</h3><div class="overview-icon icon-blue"><i class="fa-solid fa-money-bill-wave fs-6"></i></div></div></div>
            <div class="col-12 col-md-6 col-lg-3"><div class="overview-card bg-yellow-light h-100"><div class="fw-bold text-warning mb-2" style="font-size: 0.8rem;">CÔNG NỢ CHƯA THU</div><h3 class="fw-bold text-danger m-0 mb-3">0 đ</h3><div class="d-flex justify-content-between text-xs fw-semibold text-danger mt-2 border-top border-warning pt-2"><span><i class="fa-regular fa-clock"></i> Tồn đọng:</span><span>0 hóa đơn</span></div><div class="overview-icon icon-yellow"><i class="fa-solid fa-circle-exclamation fs-6"></i></div></div></div>
            <div class="col-12 col-md-6 col-lg-3"><div class="overview-card bg-purple-light h-100"><div class="fw-bold mb-2" style="font-size: 0.8rem; color: #a855f7;">KHÁCH & HỢP ĐỒNG</div><div class="d-flex align-items-end mb-4"><h3 class="fw-bold m-0 me-2 text-dark">0</h3> <span class="text-muted small">hợp đồng</span></div><div class="overview-icon icon-purple"><i class="fa-solid fa-users fs-6"></i></div></div></div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="toolbar-select"><i class="fa-solid fa-building text-teal"></i> Đang xem: Tất cả 3 Tòa nhà <i class="fa-solid fa-angle-down ms-2"></i></div>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-custom"><i class="fa-solid fa-eye me-1"></i> Xem Tất cả Tòa nhà</button>
                <button class="btn btn-teal-custom" data-bs-toggle="modal" data-bs-target="#addBuildingModal"><i class="fa-solid fa-plus me-1"></i> Thêm Tòa nhà</button>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-lg-4">
                <div class="building-card">
                    <span class="badge-bld">BLD-A</span><div class="card-illustration"></div>
                    <h4 class="fw-bold text-dark mt-3 mb-2">Tòa A - StayMate Sunrise</h4>
                    <div class="text-muted text-xs mb-3 fw-semibold"><i class="fa-solid fa-location-dot text-teal me-1"></i> Số 128 Cầu Giấy, HN</div>
                    <div class="d-flex justify-content-between text-xs mb-4 p-2 bg-light rounded-3 border">
                        <span class="text-muted fw-semibold"><i class="fa-regular fa-id-badge me-1"></i> Quản lý:</span><span class="fw-bold text-dark">Nguyễn Văn Quản</span>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-4"><div class="stat-box stat-box-total"><i class="fa-regular fa-building stat-icon mb-1"></i><div class="stat-val">15</div><div class="stat-label">Tổng</div></div></div>
                        <div class="col-4"><div class="stat-box stat-box-rented"><i class="fa-solid fa-user-group stat-icon mb-1 text-success"></i><div class="stat-val text-success">12</div><div class="stat-label">Đang thuê</div></div></div>
                        <div class="col-4"><div class="stat-box stat-box-empty"><i class="fa-solid fa-house-chimney stat-icon mb-1 text-warning"></i><div class="stat-val text-warning">3</div><div class="stat-label">Trống</div></div></div>
                    </div>
                    <div class="d-flex gap-2 mt-auto">
                        <a href="index.php?route=overview&building_id=A" class="btn btn-card-main d-flex align-items-center justify-content-center"><i class="fa-solid fa-arrow-right-to-bracket me-2"></i> Vào quản lý Tòa này</a>
                        <button class="btn btn-card-folder"><i class="fa-solid fa-door-open"></i></button>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="building-card">
                    <span class="badge-bld">BLD-B</span><div class="card-illustration"></div>
                    <h4 class="fw-bold text-dark mt-3 mb-2">Tòa B - StayMate Emerald</h4>
                    <div class="text-muted text-xs mb-3 fw-semibold"><i class="fa-solid fa-location-dot text-teal me-1"></i> Số 45 Nguyễn Chí Thanh, HN</div>
                    <div class="d-flex justify-content-between text-xs mb-4 p-2 bg-light rounded-3 border">
                        <span class="text-muted fw-semibold"><i class="fa-regular fa-id-badge me-1"></i> Quản lý:</span><span class="fw-bold text-dark">Trần Thị Bích</span>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-4"><div class="stat-box stat-box-total"><i class="fa-regular fa-building stat-icon mb-1"></i><div class="stat-val">20</div><div class="stat-label">Tổng</div></div></div>
                        <div class="col-4"><div class="stat-box stat-box-rented"><i class="fa-solid fa-user-group stat-icon mb-1 text-success"></i><div class="stat-val text-success">18</div><div class="stat-label">Đang thuê</div></div></div>
                        <div class="col-4"><div class="stat-box stat-box-empty"><i class="fa-solid fa-house-chimney stat-icon mb-1 text-warning"></i><div class="stat-val text-warning">2</div><div class="stat-label">Trống</div></div></div>
                    </div>
                    <div class="d-flex gap-2 mt-auto">
                        <a href="index.php?route=overview&building_id=B" class="btn btn-card-main d-flex align-items-center justify-content-center"><i class="fa-solid fa-arrow-right-to-bracket me-2"></i> Vào quản lý Tòa này</a>
                        <button class="btn btn-card-folder"><i class="fa-solid fa-door-open"></i></button>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="building-card">
                    <span class="badge-bld">BLD-C</span><div class="card-illustration"></div>
                    <h4 class="fw-bold text-dark mt-3 mb-2">Tòa C - StayMate Horizon</h4>
                    <div class="text-muted text-xs mb-3 fw-semibold"><i class="fa-solid fa-location-dot text-teal me-1"></i> Số 88 Nguyễn Trãi, HN</div>
                    <div class="d-flex justify-content-between text-xs mb-4 p-2 bg-light rounded-3 border">
                        <span class="text-muted fw-semibold"><i class="fa-regular fa-id-badge me-1"></i> Quản lý:</span><span class="fw-bold text-dark">Lê Hoàng Nam</span>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-4"><div class="stat-box stat-box-total"><i class="fa-regular fa-building stat-icon mb-1"></i><div class="stat-val">10</div><div class="stat-label">Tổng</div></div></div>
                        <div class="col-4"><div class="stat-box stat-box-rented"><i class="fa-solid fa-user-group stat-icon mb-1 text-success"></i><div class="stat-val text-success">7</div><div class="stat-label">Đang thuê</div></div></div>
                        <div class="col-4"><div class="stat-box stat-box-empty"><i class="fa-solid fa-house-chimney stat-icon mb-1 text-warning"></i><div class="stat-val text-warning">3</div><div class="stat-label">Trống</div></div></div>
                    </div>
                    <div class="d-flex gap-2 mt-auto">
                        <a href="index.php?route=overview&building_id=C" class="btn btn-card-main d-flex align-items-center justify-content-center"><i class="fa-solid fa-arrow-right-to-bracket me-2"></i> Vào quản lý Tòa này</a>
                        <button class="btn btn-card-folder"><i class="fa-solid fa-door-open"></i></button>
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
                    <h5 class="modal-title fw-bold text-teal"><i class="fa-solid fa-building-circle-arrow-right me-2"></i> Thêm Tòa Nhà Mới</h5>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>