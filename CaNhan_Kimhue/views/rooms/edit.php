<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa Phòng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card" style="max-width: 500px; margin: 0 auto;">
        <div class="card-header bg-warning"><h4 class="mb-0">Sửa Phòng</h4></div>
        <div class="card-body">
            <form method="POST" action="index.php?action=edit&id=<?= $room['id'] ?>">
                <div class="mb-3">
                    <label>Số phòng</label>
                    <input type="text" name="room_number" class="form-control" value="<?= htmlspecialchars($room['room_number']) ?>" required>
                </div>
                <div class="mb-3">
                    <label>Giá thuê (VNĐ)</label>
                    <input type="number" name="price" class="form-control" value="<?= $room['price'] ?>" required>
                </div>
                <div class="mb-3">
                    <label>Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="available" <?= $room['status'] == 'available' ? 'selected' : '' ?>>Trống (Available)</option>
                        <option value="rented" <?= $room['status'] == 'rented' ? 'selected' : '' ?>>Đang thuê (Rented)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Mô tả</label>
                    <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($room['description']) ?></textarea>
                </div>
                <button type="submit" class="btn btn-success w-100">Cập nhật</button>
                <a href="index.php" class="btn btn-secondary w-100 mt-2">Quay lại</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>s