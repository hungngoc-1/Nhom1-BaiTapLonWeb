<?php
// 1. DỮ LiỆU MẪU (Sau này Người 3 chỉ cần truyền biến $rooms từ Controller sang và xóa đoạn mẫu này)
if (!isset($rooms)) {
    $rooms = [
        ['id' => 1, 'room_number' => 'P101', 'area' => 20, 'price' => 2500000, 'max_occupants' => 2, 'status' => 'trong', 'note' => 'Có ban công, điều hòa'],
        ['id' => 2, 'room_number' => 'P102', 'area' => 25, 'price' => 3200000, 'max_occupants' => 3, 'status' => 'dang_thue', 'note' => 'Full nội thất'],
        ['id' => 3, 'room_number' => 'P201', 'area' => 18, 'price' => 2200000, 'max_occupants' => 2, 'status' => 'trong', 'note' => 'Tầng 2, thoáng mát'],
        ['id' => 4, 'room_number' => 'P202', 'area' => 22, 'price' => 2800000, 'max_occupants' => 3, 'status' => 'bao_tri', 'note' => 'Đang sửa bình nóng lạnh'],
    ];
}

// Xử lý lọc và tìm kiếm trực tiếp trên dữ liệu mẫu để demo chạy được ngay
$keyword = trim($_GET['keyword'] ?? '');
$statusFilter = $_GET['status'] ?? '';

$filteredRooms = array_filter($rooms, function ($room) use ($keyword, $statusFilter) {
    $matchKeyword = ($keyword === '') || (stripos($room['room_number'], $keyword) !== false);
    $matchStatus  = ($statusFilter === '') || ($room['status'] === $statusFilter);
    return $matchKeyword && $matchStatus;
});
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý phòng trọ</title>
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Sau này ghép code với Người 1 thì chỉ cần giữ lại phần từ thẻ <div class="container-fluid..."> trở xuống -->
<div class="container-fluid py-4 px-4">
    
    <!-- Tiêu đề & Nút thêm phòng -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Quản lý Danh sách Phòng</h3>
            <p class="text-muted mb-0">Tra cứu, lọc trạng thái và cập nhật thông tin phòng trọ</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoomModal">
            <i class="bi bi-plus-lg me-1"></i> Thêm phòng mới
        </button>
    </div>

    <!-- Vùng hiển thị thông báo lỗi / thành công (Flash Message) -->
    <div id="alertBox" class="alert alert-danger d-none" role="alert"></div>

    <!-- Thanh Tìm kiếm & Lọc trạng thái -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Tìm kiếm số phòng</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="keyword" class="form-control" placeholder="Nhập mã phòng (VD: P101)..." value="<?= htmlspecialchars($keyword) ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Trạng thái phòng</label>
                    <select name="status" class="form-select">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="trong" <?= $statusFilter === 'trong' ? 'selected' : '' ?>>Phòng trống</option>
                        <option value="dang_thue" <?= $statusFilter === 'dang_thue' ? 'selected' : '' ?>>Đang thuê</option>
                        <option value="bao_tri" <?= $statusFilter === 'bao_tri' ? 'selected' : '' ?>>Đang bảo trì</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-dark w-100"><i class="bi bi-funnel me-1"></i> Lọc</button>
                    <a href="index.php" class="btn btn-outline-secondary w-100">Đặt lại</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bảng Danh sách phòng -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Mã phòng</th>
                            <th>Diện tích</th>
                            <th>Giá thuê / tháng</th>
                            <th>Tối đa</th>
                            <th>Trạng thái</th>
                            <th>Ghi chú / Tiện ích</th>
                            <th class="text-end pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($filteredRooms)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Không tìm thấy phòng nào phù hợp.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($filteredRooms as $room): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-primary"><?= htmlspecialchars($room['room_number']) ?></td>
                                    <td><?= htmlspecialchars($room['area']) ?> m²</td>
                                    <td class="fw-semibold text-danger"><?= number_format($room['price'], 0, ',', '.') ?> đ</td>
                                    <td><i class="bi bi-people me-1"></i><?= htmlspecialchars($room['max_occupants']) ?> người</td>
                                    <td>
                                        <?php if ($room['status'] === 'trong'): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">Phòng trống</span>
                                        <?php elseif ($room['status'] === 'dang_thue'): ?>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">Đang thuê</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2">Đang bảo trì</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted small"><?= htmlspecialchars($room['note']) ?></td>
                                    <td class="text-end pe-4">
                                        <a href="show.php?id=<?= $room['id'] ?>" class="btn btn-sm btn-outline-info" title="Xem chi tiết"><i class="bi bi-eye"></i></a>
                                        <a href="edit.php?id=<?= $room['id'] ?>" class="btn btn-sm btn-outline-primary" title="Sửa phòng"><i class="bi bi-pencil"></i></a>
                                        <?php if ($room['status'] === 'trong'): ?>
                                            <a href="../contracts/create.php?room_id=<?= $room['id'] ?>" class="btn btn-sm btn-success" title="Lập hợp đồng"><i class="bi bi-file-earmark-plus"></i> Thuê</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- MODAL THÊM PHÒNG MỚI (Có kiểm tra lỗi dữ liệu) -->
<div class="modal fade" id="addRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formAddRoom" method="POST" action="" class="modal-content needs-validation" novalidate>
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Thêm Phòng Trọ Mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- CSRF Token bắt buộc theo yêu cầu bảo mật của đề bài -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? 'sample_csrf_token') ?>">

                <div class="mb-3">
                    <label class="form-label">Mã / Số phòng <span class="text-danger">*</span></label>
                    <input type="text" name="room_number" id="room_number" class="form-control" placeholder="VD: P103" required>
                    <div class="invalid-feedback">Vui lòng nhập mã phòng (không được để trống).</div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Diện tích (m²) <span class="text-danger">*</span></label>
                        <input type="number" name="area" id="area" class="form-control" min="5" step="0.5" placeholder="VD: 20" required>
                        <div class="invalid-feedback">Diện tích phải lớn hơn hoặc bằng 5m².</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Số người tối đa <span class="text-danger">*</span></label>
                        <input type="number" name="max_occupants" id="max_occupants" class="form-control" min="1" max="10" value="2" required>
                        <div class="invalid-feedback">Số người phải từ 1 đến 10.</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Giá thuê cơ bản (VNĐ/tháng) <span class="text-danger">*</span></label>
                    <input type="number" name="price" id="price" class="form-control" min="100000" step="50000" placeholder="VD: 2500000" required>
                    <div class="invalid-feedback">Giá phòng không hợp lệ (không được âm hoặc nhỏ hơn 100.000đ).</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Trạng thái ban đầu</label>
                    <select name="status" class="form-select">
                        <option value="trong">Phòng trống</option>
                        <option value="bao_tri">Đang bảo trì</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ghi chú / Tiện ích</label>
                    <textarea name="note" class="form-control" rows="2" placeholder="Điều hòa, nóng lạnh, ban công..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="submit" class="btn btn-primary">Lưu thông tin</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// JS bắt lỗi dữ liệu Form ngay trên giao diện
document.getElementById('formAddRoom').addEventListener('submit', function (event) {
    if (!this.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
    } else {
        event.preventDefault(); // Chặn gửi thật khi đang test dữ liệu mẫu
        alert('Dữ liệu hợp lệ! Sẵn sàng gửi về API của Người 3.');
    }
    this.classList.add('was-validated');
});
</script>
</body>
</html>