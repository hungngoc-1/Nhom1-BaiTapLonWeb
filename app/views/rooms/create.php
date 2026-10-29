<div class="container-fluid py-4 px-4">
    <div class="card shadow-sm border-0 max-w-500 mx-auto" style="max-width: 600px;">
        <div class="card-header bg-white pb-0 border-0 mt-3">
            <h4 class="fw-bold">Thêm Phòng Trọ Mới</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="index.php?route=rooms_store">
                <div class="mb-3">
                    <label class="form-label">Mã / Số phòng *</label>
                    <input type="text" name="room_number" class="form-control" placeholder="VD: P101" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Giá thuê (VNĐ/tháng) *</label>
                    <input type="number" name="price" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Diện tích (m²)</label>
                    <input type="number" name="area" class="form-control">
                </div>
                <div class="mb-4">
                    <label class="form-label">Trạng thái ban đầu</label>
                    <select name="status" class="form-select">
                        <option value="available">Phòng trống</option>
                        <option value="maintenance">Đang bảo trì</option>
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">Lưu phòng</button>
                    <a href="index.php?route=rooms" class="btn btn-outline-secondary">Hủy và quay lại</a>
                </div>
            </form>
        </div>
    </div>
</div>