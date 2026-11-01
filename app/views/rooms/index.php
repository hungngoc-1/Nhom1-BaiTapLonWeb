<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Phòng trọ - StayMate</title>
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-teal: #287A74;
            --secondary-teal: #459690;
            --light-mint: #A3E4D7;
            --accent-yellow: #FEF9E7;
            --bg-main: #F4F8F7;
        }
        body {
            background-color: var(--bg-main);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        /* Thanh Navigation / Header phong cách StayMate */
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
        /* Nút bấm chủ đạo */
        .btn-teal {
            background-color: var(--primary-teal);
            color: white;
            border: none;
            transition: 0.2s;
        }
        .btn-teal:hover {
            background-color: #1f5f5b;
            color: white;
        }
        /* Thẻ phòng trọ (Room Card) */
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
        }
        .filter-badge:hover, .filter-badge.active {
            background-color: var(--primary-teal);
            color: white;
        }
    </style>
</head>
<body>

<!-- Thanh Menu trên cùng (Mô phỏng ảnh mẫu) -->
<nav class="navbar navbar-expand-lg navbar-custom mb-4">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold text-teal d-flex align-items-center gap-2" href="#" style="color: var(--primary-teal);">
            <i class="bi bi-buildings-fill fs-4"></i> SMART STAY <span class="fs-6 text-muted fw-normal">Quản Lý Nhà Trọ</span>
        </a>
        <div class="d-flex align-items-center gap-3">
            <a href="index.php?route=dashboard" class="nav-link-custom"><i class="bi bi-speedometer2"></i> Tổng quan</a>
            <a href="index.php?route=rooms" class="nav-link-custom active"><i class="bi bi-door-open"></i> Phòng trọ</a>
            <a href="#" class="nav-link-custom"><i class="bi bi-people"></i> Khách thuê</a>
            <a href="#" class="nav-link-custom"><i class="bi bi-file-text"></i> Hợp đồng</a>
        </div>
    </div>
</nav>

<div class="container-fluid px-4 pb-5">
    
    <!-- Các nút lọc trạng thái nhanh -->
    <div class="d-flex flex-wrap gap-2 mb-4 align-items-center justify-content-between">
        <div class="d-flex flex-wrap gap-2">
            <button class="filter-badge active">🏠 Tất cả (<?= count($rooms ?? []) ?>)</button>
            <button class="filter-badge">🟢 Phòng trống</button>
            <button class="filter-badge">🔵 Đang thuê</button>
            <button class="filter-badge">🟡 Bảo trì</button>
        </div>
        <button class="btn btn-teal px-4 py-2 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#addRoomModal">
            <i class="bi bi-plus-lg me-1"></i> Thêm Phòng
        </button>
    </div>

    <!-- Thanh Tìm kiếm và Bộ lọc chi tiết -->
    <div class="card shadow-sm border-0 mb-4 rounded-4 p-3 bg-white">
        <form method="GET" action="index.php" class="row g-3 align-items-center">
            <input type="hidden" name="route" value="rooms">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="keyword" class="form-control bg-light border-0" placeholder="Tìm mã phòng, tiện nghi, tên khách thuê..." value="<?= htmlspecialchars($keyword ?? '') ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select bg-light border-0">
                    <option value="">-- Mọi trạng thái --</option>
                    <option value="available">Phòng trống</option>
                    <option value="rented">Đang thuê</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-teal px-4 w-50"><i class="bi bi-funnel me-1"></i> Lọc</button>
                <a href="index.php?route=rooms" class="btn btn-outline-secondary w-50">Đặt lại</a>
            </div>
        </form>
    </div>

    <!-- THƯ VIỆN LƯỚI THẺ PHÒNG (CARD GRID) -->
    <div class="row g-4">
        <?php if (!empty($rooms)): ?>
            <?php foreach ($rooms as $room): ?>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="room-card h-100 d-flex flex-column justify-content-between">
                        <div>
                            <!-- Header của Thẻ phòng -->
                            <div class="room-header">
                                <div>
                                    <span class="fs-5 fw-bold"><?= htmlspecialchars($room['room_number']) ?></span>
                                    <div class="small opacity-75" style="font-size: 0.8rem;">Khu vực chính</div>
                                </div>
                                <span class="badge <?= ($room['status'] ?? 'available') == 'available' ? 'bg-success' : 'bg-primary' ?>">
                                    <?= ($room['status'] ?? 'available') == 'available' ? 'Trống' : 'Đang thuê' ?>
                                </span>
                            </div>

                            <!-- Thân Thẻ phòng -->
                            <div class="p-3">
                                <div class="text-muted small mb-2">
                                    <i class="bi bi-arrows-angle-expand me-1"></i> Diện tích: <b>20 m²</b> &bull; Tiêu chuẩn
                                </div>
                                <div class="mb-3">
                                    <span class="text-muted small">Giá thuê:</span>
                                    <div class="fs-5 fw-bold text-danger"><?= number_format($room['price'] ?? 0, 0, ',', '.') ?> đ <span class="fs-6 fw-normal text-muted">/tháng</span></div>
                                </div>
                                <div class="p-2 rounded-2 mb-3" style="background-color: var(--accent-yellow); font-size: 0.85_rem; color: #7a6b00;">
                                    <i class="bi bi-stars me-1"></i> <?= htmlspecialchars($room['description'] ?? 'Điều hòa, nóng lạnh, wifi đầy đủ...') ?>
                                </div>
                            </div>
                        </div>

                        <!-- Chân Thẻ phòng (Nút thao tác Sửa / Xóa) -->
                        <div class="p-3 pt-0 border-top bg-light bg-opacity-50 d-flex gap-2 mt-2">
                            <a href="index.php?route=rooms&edit_id=<?= $room['id'] ?>" class="btn btn-outline-teal btn-sm w-50 text-dark border-secondary">
                                <i class="bi bi-pencil me-1"></i> Chỉnh sửa
                            </a>
                            <form method="POST" action="index.php?route=rooms" class="w-50" onsubmit="return confirm('Xóa phòng này?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $room['id'] ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm w-50 w-100">
                                    <i class="bi bi-trash"></i> Xóa
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <div class="text-muted fs-5"><i class="bi bi-inbox display-4 d-block mb-2 text-muted opacity-50"></i> Không tìm thấy phòng trọ nào trong hệ thống.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL THÊM PHÒNG MỚI -->
<div class="modal fade" id="addRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="index.php?route=rooms" class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header text-white" style="background-color: var(--primary-teal);">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i> Thêm Phòng Trọ Mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="action" value="create">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Số phòng <span class="text-danger">*</span></label>
                    <input type="text" name="room_number" class="form-control bg-light" placeholder="VD: P105" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Giá thuê (VNĐ/tháng) <span class="text-danger">*</span></label>
                    <input type="number" name="price" class="form-control bg-light" placeholder="VD: 3000000" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Trạng thái</label>
                    <select name="status" class="form-select bg-light">
                        <option value="available">Phòng trống</option>
                        <option value="rented">Đang thuê</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Mô tả / Tiện ích</label>
                    <textarea name="description" class="form-control bg-light" rows="2" placeholder="Điều hòa, nóng lạnh, ban công..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-teal px-4">Lưu phòng</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>