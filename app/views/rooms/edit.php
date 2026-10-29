<div class="container-fluid py-4 px-4">
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 600px;">
        <div class="card-header bg-white pb-0 border-0 mt-3">
            <h4 class="fw-bold">Cập nhật Phòng: <?= htmlspecialchars($room['room_number'] ?? '') ?></h4>
        </div>
        <div class="card-body">
            <form method="POST" action="index.php?route=rooms_update">
                <input type="hidden" name="id" value="<?= $room['id'] ?? '' ?>">
                <div class="mb-3">
                    <label class="form-label">Mã / Số phòng *</label>
                    <input type="text" name="room_number" class="form-control" value="<?= htmlspecialchars($room['room_number'] ?? '') ?>" required readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Giá thuê (VNĐ/tháng) *</label>
                    <input type="number" name="price" class="form-control" value="<?= htmlspecialchars($room['price'] ?? 0) ?>" required>
                </div>
                <div class="mb-4">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="available" <?= (isset($room['status']) && $room['status'] == 'available') ? 'selected' : '' ?>>Phòng trống</option>
                        <option value="rented" <?= (isset($room['status']) && $room['status'] == 'rented') ? 'selected' : '' ?>>Đang thuê</option>
                        <option value="maintenance" <?= (isset($room['status']) && $room['status'] == 'maintenance') ? 'selected' : '' ?>>Đang bảo trì</option>
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-warning px-4">Cập nhật</button>
                    <a href="index.php?route=rooms" class="btn btn-outline-secondary">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>