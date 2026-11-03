<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Khách thuê - Smart Stay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --teal-dark: #287A74;
            --teal-medium: #58A8A1;
            --mint-light: #B2F0E8;
            --bg-body: #F0F7F6;
        }
        body { background: var(--bg-body); font-family: 'Segoe UI', sans-serif; }
        
        /* Menu y hệt trang phòng trọ */
        .topbar-staymate {
            background: #ffffff; border-radius: 16px; padding: 8px 20px;
            box-shadow: 0 4px 20px rgba(40, 122, 116, 0.08); margin: 16px 20px;
            display: flex; align-items: center; justify-content: space-between; gap: 15px;
        }
        .brand-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .brand-icon {
            width: 40px; height: 40px; border-radius: 12px; color: white;
            background: linear-gradient(135deg, var(--teal-dark), var(--teal-medium));
            display: flex; align-items: center; justify-content: center; font-size: 1.3rem;
        }
        .nav-menu-list { display: flex; gap: 4px; margin: 0; padding: 0; list-style: none; }
        .nav-menu-link {
            display: inline-flex; align-items: center; gap: 5px; padding: 8px 12px;
            border-radius: 10px; color: #5A6A7E; font-weight: 600; font-size: 0.85rem;
            text-decoration: none; transition: 0.2s; white-space: nowrap;
        }
        .nav-menu-link:hover { background-color: rgba(178, 240, 232, 0.35); color: var(--teal-dark); }
        .nav-menu-link.active { background-color: var(--teal-dark); color: #ffffff; }
        
        /* Bảng Khách Thuê */
        .table-custom { background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
        .table-custom thead { background-color: #F8FAFC; color: #64748B; font-size: 0.8rem; }
        .table-custom th { font-weight: 600; padding: 16px; border-bottom: 2px solid #E2E8F0; }
        .table-custom td { padding: 16px; vertical-align: middle; border-bottom: 1px solid #F1F5F9; font-size: 0.9rem; }
        .table-custom tbody tr:hover { background-color: #F0FAF8; }
        
        /* Nút thao tác */
        .btn-teal-primary { background: var(--teal-dark); color: white; border-radius: 30px; padding: 8px 20px; font-weight: 600; border: none; }
        .btn-teal-primary:hover { background: #1E5E59; color: white; }
    </style>
</head>
<body>

<!-- Menu Điều Hướng -->
<header class="topbar-staymate">
    <a href="index.php?route=dashboard" class="brand-logo">
        <div class="brand-icon"><i class="bi bi-buildings-fill"></i></div>
        <div>
            <div class="fw-bold" style="color: var(--teal-dark); font-size: 1.05rem;">SMART STAY</div>
            <div class="text-muted" style="font-size: 0.72rem;">Quản Lý Nhà Trọ</div>
        </div>
    </a>
    <ul class="nav-menu-list">
        <li><a href="index.php?route=overview" class="nav-menu-link"><i class="bi bi-building"></i> Tòa nhà</a></li>
        <li><a href="index.php?route=dashboard" class="nav-menu-link"><i class="bi bi-grid-1x2-fill"></i> Tổng quan</a></li>
        <li><a href="index.php?route=rooms" class="nav-menu-link"><i class="bi bi-door-open-fill"></i> Phòng trọ</a></li>
        <!-- Kích hoạt sáng màu ở trang Khách thuê -->
        <li><a href="index.php?route=tenants" class="nav-menu-link active"><i class="bi bi-people-fill"></i> Khách thuê</a></li>
        <li><a href="index.php?route=contracts" class="nav-menu-link"><i class="bi bi-file-earmark-text-fill"></i> Hợp đồng</a></li>
    </ul>
</header>

<div class="container-fluid px-4 pb-5">
    
    <!-- Thanh Tìm Kiếm -->
    <div class="bg-white rounded-4 p-3 shadow-sm mb-4 border">
        <form method="GET" action="index.php" class="d-flex gap-2 align-items-center">
            <input type="hidden" name="route" value="tenants">
            <div class="input-group w-25">
                <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                <input type="text" name="keyword" class="form-control bg-light border-0" placeholder="Tìm tên, CCCD, SĐT..." value="<?= htmlspecialchars($keyword ?? '') ?>">
            </div>
            <select class="form-select w-auto bg-light border-0"><option>-- Trạng thái --</option></select>
            <button class="btn btn-secondary px-4"><i class="bi bi-search"></i> Tìm</button>
            
            <div class="ms-auto d-flex gap-2">
                <button type="button" class="btn btn-danger rounded-pill px-3"><i class="bi bi-telephone-fill"></i> Khách liên hệ (8)</button>
                <button type="button" class="btn btn-teal-primary" data-bs-toggle="modal" data-bs-target="#addTenantModal">
                    <i class="bi bi-person-plus-fill"></i> Thêm Khách thuê
                </button>
            </div>
        </form>
    </div>

    <!-- Bảng Danh Sách Khách Thuê -->
    <div class="table-custom">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>HỌ VÀ TÊN</th>
                    <th>CCCD / CMND</th>
                    <th>ĐIỆN THOẠI</th>
                    <th>PHÒNG & THỜI HẠN</th>
                    <th>QUÊ QUÁN / NGHỀ NGHIỆP</th>
                    <th>TRẠNG THÁI</th>
                    <th>THAO TÁC</th>
                </tr>
            </thead>
            <tbody>
                <!-- Dữ liệu mẫu minh họa -->
                <tr>
                    <td>
                        <div class="fw-bold text-dark">Đinh Thảo Nguyên</div>
                        <div class="text-muted small">Nữ • NS: 15/06/2001</div>
                    </td>
                    <td class="text-danger fw-semibold">037201091234</td>
                    <td>
                        <div class="fw-bold">0937001122</div>
                        <div class="text-muted small">nguyendt@gmail.com</div>
                    </td>
                    <td><span class="text-muted">Chưa xếp phòng</span></td>
                    <td>
                        <div class="fw-bold">Ninh Bình</div>
                        <div class="text-muted small">Nhân viên Nhân sự (HR)</div>
                    </td>
                    <td><span class="badge bg-secondary rounded-pill">Đã trả phòng</span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-secondary rounded-circle" data-bs-toggle="modal" data-bs-target="#editTenantModal"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger rounded-circle"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="fw-bold text-dark">Lý Minh Khôi</div>
                        <div class="text-muted small">Nam • NS: 17/02/1995</div>
                    </td>
                    <td class="text-danger fw-semibold">019095090123</td>
                    <td>
                        <div class="fw-bold">0936990011</div>
                    </td>
                    <td>
                        <span class="badge bg-info text-dark">C201</span> Tòa C - Smart Stay
                        <div class="text-muted small">Cọc: 19/08/2026 • 01/09/2026 ➔ 01/09/2027</div>
                    </td>
                    <td>
                        <div class="fw-bold">Thái Nguyên</div>
                        <div class="text-muted small">Kiến trúc sư</div>
                    </td>
                    <td><span class="badge bg-success rounded-pill">Đang lưu trú</span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-secondary rounded-circle" data-bs-toggle="modal" data-bs-target="#editTenantModal"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger rounded-circle"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Thêm / Cập nhật Khách thuê (Giống ảnh) -->
<div class="modal fade" id="editTenantModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content">
            <div class="modal-header border-bottom px-4">
                <h5 class="modal-title fw-bold">Cập nhật Khách thuê: Đinh Thảo Nguyên</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 row g-3">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Họ và tên *</label>
                    <input type="text" class="form-control" value="Đinh Thảo Nguyên">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Số CCCD / CMND *</label>
                    <input type="text" class="form-control" value="037201091234">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Số điện thoại *</label>
                    <input type="text" class="form-control" value="0937001122">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Ngày sinh</label>
                    <input type="date" class="form-control" value="2001-06-15">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Giới tính</label>
                    <select class="form-select"><option selected>Nữ</option><option>Nam</option></select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Email</label>
                    <input type="email" class="form-control" value="nguyendt@gmail.com">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Nghề nghiệp / Nơi công tác</label>
                    <input type="text" class="form-control" value="Nhân viên Nhân sự (HR)">
                </div>
                <div class="col-md-12">
                    <label class="form-label small fw-semibold">Quê quán / Địa chỉ thường trú</label>
                    <input type="text" class="form-control" value="Ninh Bình">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Liên hệ khẩn cấp</label>
                    <input type="text" class="form-control" value="0904000111">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">Trạng thái</label>
                    <select class="form-select"><option selected>Đã chuyển đi</option><option>Đang lưu trú</option></select>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4" style="background: #4C5FD5; border:none;">Lưu thông tin</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>