<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Phòng trọ - Smart Stay</title>
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-teal: #287A74;
            --secondary-teal: #3b9c95;
            --light-mint: #A3E4D7;
            --accent-yellow: #FEF9E7;
            --bg-main: #F4F8F7;
        }
        body {
            background-color: var(--bg-main);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar-custom {
            background-color: white;
            box-shadow: 0 2px 10px rgba(40, 122, 116, 0.08);
            padding: 12px 24px;
        }
        .nav-link-custom {
            color: #555;
            font-weight: 500;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .nav-link-custom:hover, .nav-link-custom.active {
            background-color: var(--primary-teal);
            color: white;
        }
        .btn-teal {
            background-color: var(--primary-teal);
            color: white;
            border: none;
        }
        .btn-teal:hover {
            background-color: #1f5f5b;
            color: white;
        }
        .room-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.2s, box-shadow 0.2s;
            background: white;
            overflow: hidden;
        }
        .room-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(40, 122, 116, 0.15);
        }
        .room-header {
            background-color: var(--primary-teal);
            color: white;
            padding: 12px 16px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .filter-badge {
            cursor: pointer;
            border-radius: 20px;
            padding: 8px 16px;
            font-weight: 500;
            border: 1px solid #d1e7e5;
            background: white;
            color: var(--primary-teal);
            transition: all 0.2s;
            text-decoration: none;
        }
        .filter-badge:hover, .filter-badge.active {
            background-color: var(--primary-teal);
            color: white;
        }
        .modal-content {
            border-radius: 20px;
            border: none;
        }
    </style>
</head>
<body>

<!-- Thanh Menu Điều Hướng (Giống Ảnh 1) -->
<nav class="navbar navbar-expand-lg navbar-custom mb-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="#" style="color: var(--primary-teal);">
            <i class="bi bi-buildings-fill fs-4"></i> SMART STAY <span class="fs-6 text-muted fw-normal">Quản Lý Nhà Trọ</span>
        </a>
        <div class="d-flex align-items-center gap-2">
            <a href="index.php?route=dashboard" class="nav-link-custom"><i class="bi bi-speedometer2"></i> Tổng quan</a>
            <a href="index.php?route=rooms" class="nav-link-custom active"><i class="bi bi-door-open"></i> Phòng trọ</a>
            <a href="#" class="nav-link-custom"><i class="bi bi-people"></i> Khách thuê</a>
            <a href="#" class="nav-link-custom"><i class="bi bi-file-text"></i> Hợp đồng</a>
        </div>
    </div>
</nav>

<div class="container-fluid px-4 pb-5">
    
    <!-- Các nút lọc trạng thái nhanh (Giống Ảnh 1) -->
    <div class="d-flex flex-wrap gap-2 mb-4 align-items-center justify-content-between">
        <div class="d-flex flex-wrap gap-2">
            <a href="index.php?route=rooms" class="filter-badge active">🏠 Tất cả (<?= count($rooms ?? []) ?>)</a>
            <a href="index.php?route=rooms&status=available" class="filter-badge">🟢 Phòng trống</a>
            <a href="index.php?route=rooms&status=rented" class="filter-badge">🔵 Đang thuê</a>
        </div>
        <!-- Nút mở Modal Thêm Phòng (Ảnh 3) -->
        <button class="btn btn-teal px-4 py-2 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#addRoomModal">
            <i class="bi bi-plus-lg me-1"></i> Thêm Phòng
        </button>
    </div>

    <!-- Thanh Tìm kiếm và Bộ lọc (Giống Ảnh 1) -->
    <div class="card shadow-sm border-0 mb-4 rounded-4 p-3 bg-white">
        <form method="GET" action="index.php" class="row g-3 align-items-center">
            <input type="hidden" name="route" value="rooms">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="keyword" class="form-control bg-light border-0" placeholder="Tìm mã phòng, tiện nghi, mô tả..." value="<?= htmlspecialchars($keyword ?? '') ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select bg-light border-0">
                    <option value="">-- Mọi trạng thái --</option>
                    <option value="available" <?= (isset($_GET['status']) && $_GET['status'] == 'available') ? 'selected' : '' ?>>Phòng trống</option>
                    <option value="rented" <?= (isset($_GET['status']) && $_GET['status'] == 'rented') ? 'selected' : '' ?>>Đang thuê</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-teal px-4 w-50"><i class="bi bi-funnel me-1"></i> Lọc</button>
                <a href="index.php?route=rooms" class="btn btn-outline-secondary w-50">Đặt lại</a>
            </div>
        </form>
    </div>

    <!-- LƯỚI THẺ PHÒNG (Giao diện chính Ảnh 1) -->
    <div class="row g-4">
        <?php if (!empty($rooms)): ?>
            <?php foreach ($rooms as $room): ?>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="room-card h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="room-header">
                                <div>
                                    <span class="fs-5 fw-bold"><?= htmlspecialchars($room['room_number']) ?></span>
                                    <div class="small opacity-75" style="font-size: 0.8rem;">BLD-A &bull; Tầng 1</div>
                                </div>
                                <span class="badge <?= ($room['status'] ?? 'available') == 'available' ? 'bg-success' : 'bg-primary' ?>">
                                    <?= ($room['status'] ?? 'available') == 'available' ? 'Trống' : 'Đang thuê' ?>
                                </span>
                            </div>

                            <div class="p-3">
                                <div class="text-muted small mb-2">
                                    <i class="bi bi-arrows-angle-expand me-1"></i> Diện tích: <b>22 m²</b> &bull; Tiêu chuẩn
                                </div>
                                <div class="mb-3">
                                    <span class="text-muted small">Giá thuê:</span>
                                    <div class="fs-5 fw-bold text-danger"><?= number_format($room['price'] ?? 0, 0, ',', '.') ?> đ <span class="fs-6 fw-normal text-muted">/tháng</span></div>
                                </div>
                                <div class="p-2 rounded-2 mb-3" style="background-color: var(--accent-yellow); font-size: 0.85rem; color: #7a6b00;">
                                    <i class="bi bi-stars me-1"></i> <?= htmlspecialchars($room['description'] ?? 'Máy lạnh, nước nóng, wifi đầy đủ...') ?>
                                </div>
                            </div>
                        </div>

                        <!-- Thao tác: Nút Sửa (mở modal sửa tương ứng) và Xóa -->
                        <div class="p-3 pt-0 border-top bg-light bg-opacity-50 d-flex gap-2 mt-2 pt-2">
                            <!-- Nút bấm kích hoạt Modal Sửa (Ảnh 2) với id tương ứng -->
                            <button type="button" class="btn btn-outline-teal btn-sm w-50 text-dark border-secondary" data-bs-toggle="modal" data-bs-target="#editRoomModal<?= $room['id'] ?>">
                                <i class="bi bi-pencil me-1"></i> Chỉnh sửa
                            </button>
                            
                            <form method="POST" action="index.php?route=rooms" class="w-50" onsubmit="return confirm('Bạn có chắc chắn muốn xóa phòng này không?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $room['id'] ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                    <i class="bi bi-trash"></i> Xóa
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- MODAL CẬP NHẬT PHÒNG (Mỗi phòng một modal riêng theo ID - Giống Ảnh 2) -->
                <div class="modal fade" id="editRoomModal<?= $room['id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <form method="POST" action="index.php?route=rooms" class="modal-content shadow-lg">
                            <div class="modal-header text-white" style="background-color: var(--primary-teal);">
                                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i> Cập nhật Phòng <?= htmlspecialchars($room['room_number']) ?></h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?= $room['id'] ?>">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Tòa nhà <span class="text-danger">*</span></label>
                                        <select class="form-select bg-light">
                                            <option selected>Tòa A - Smart Stay Sunrise</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Loại phòng <span class="text-danger">*</span></label>
                                        <select class="form-select bg-light">
                                            <option selected>Phòng Tiêu chuẩn (Standard)</option>
                                            <option>Phòng Gác lửng (Duplex)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Mã phòng <span class="text-danger">*</span></label>
                                        <input type="text" name="room_number" class="form-control bg-light" value="<?= htmlspecialchars($room['room_number']) ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Tầng <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control bg-light" value="1">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Diện tích (m²) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.1" class="form-control bg-light" value="22.00">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Số người tối đa <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control bg-light" value="2">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Giá thuê (VNĐ/tháng) <span class="text-danger">*</span></label>
                                        <input type="number" name="price" class="form-control bg-light" value="<?= htmlspecialchars($room['price']) ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Trạng thái</label>
                                        <select name="status" class="form-select bg-light">
                                            <option value="available" <?= $room['status'] == 'available' ? 'selected' : '' ?>>Phòng trống</option>
                                            <option value="rented" <?= $room['status'] == 'rented' ? 'selected' : '' ?>>Đang thuê</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Tiện nghi trong phòng</label>
                                    <input type="text" class="form-control bg-light" value="Máy lạnh, Nước nóng, Wifi, Gác lửng">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Ghi chú / Mô tả</label>
                                    <textarea name="description" class="form-control bg-light" rows="2"><?= htmlspecialchars($room['description'] ?? '') ?></textarea>
                                </div>
                            </div>
                            <div class="modal-footer border-0 p-4 pt-0">
                                <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">Hủy</button>
                                <button type="submit" class="btn btn-teal px-4 rounded-pill">Lưu thông tin</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="text-muted fs-5"><i class="bi bi-inbox display-4 d-block mb-2 text-muted opacity-50"></i> Không tìm thấy phòng trọ nào.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL THÊM PHÒNG TRỌ MỚI (Giống Ảnh 3) -->
<div class="modal fade" id="addRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="POST" action="index.php?route=rooms" class="modal-content shadow-lg">
            <div class="modal-header text-white" style="background-color: var(--primary-teal);">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i> Thêm Phòng trọ mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="action" value="create">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Tòa nhà <span class="text-danger">*</span></label>
                        <select class="form-select bg-light">
                            <option selected>Tòa A - Smart Stay Sunrise</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Loại phòng <span class="text-danger">*</span></label>
                        <select class="form-select bg-light">
                            <option selected>Phòng Tiêu chuẩn (Standard)</option>
                            <option>Phòng Gác lửng (Duplex)</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Mã phòng <span class="text-danger">*</span></label>
                        <input type="text" name="room_number" class="form-control bg-light" placeholder="VD: P105" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Tầng <span class="text-danger">*</span></label>
                        <input type="number" class="form-control bg-light" value="1">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Diện tích (m²) <span class="text-danger">*</span></label>
                        <input type="number" step="0.1" class="form-control bg-light" placeholder="VD: 20">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Số người tối đa <span class="text-danger">*</span></label>
                        <input type="number" class="form-control bg-light" value="2">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Giá thuê (VNĐ/tháng) <span class="text-danger">*</span></label>
                        <input type="number" name="price" class="form-control bg-light" placeholder="VD: 2500000" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Trạng thái</label>
                        <select name="status" class="form-select bg-light">
                            <option value="available">Phòng trống</option>
                            <option value="rented">Đang thuê</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Tiện nghi trong phòng</label>
                    <input type="text" class="form-control bg-light" placeholder="Máy lạnh, Nước nóng, Wifi...">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Ghi chú / Mô tả</label>
                    <textarea name="description" class="form-control bg-light" rows="2" placeholder="Thông tin chi tiết về phòng..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light px-4 rounded-pill" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-teal px-4 rounded-pill">Lưu thông tin</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>