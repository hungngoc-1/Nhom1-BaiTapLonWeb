<div class="container-fluid py-4 px-4">
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 600px;">
        <div class="card-header bg-primary text-white mt-0">
            <h5 class="mb-0 py-2"><i class="bi bi-info-circle me-2"></i> Chi tiết Phòng <?= htmlspecialchars($room['room_number'] ?? '') ?></h5>
        </div>
        <div class="card-body p-4">
            <ul class="list-group list-group-flush mb-4">
                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span class="text-muted">Mã số phòng:</span>
                    <span class="fw-bold"><?= htmlspecialchars($room['room_number'] ?? 'N/A') ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span class="text-muted">Giá cho thuê:</span>
                    <span class="fw-bold text-danger"><?= number_format($room['price'] ?? 0, 0, ',', '.') ?> VNĐ/tháng</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span class="text-muted">Diện tích sử dụng:</span>
                    <span class="fw-bold"><?= htmlspecialchars($room['area'] ?? 'N/A') ?> m²</span>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <span class="text-muted">Trạng thái hiện tại:</span>
                    <span class="fw-bold"><?= htmlspecialchars($room['status'] ?? 'N/A') ?></span>
                </li>
            </ul>
            <div class="text-center">
                <a href="index.php?route=rooms_edit&id=<?= $room['id'] ?? '' ?>" class="btn btn-primary px-4 me-2">Chỉnh sửa</a>
                <a href="index.php?route=rooms" class="btn btn-outline-secondary px-4">Quay lại danh sách</a>
            </div>
        </div>
    </div>
</div>