<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Phòng trọ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">Danh sách Phòng trọ</h2>
    <a href="index.php?action=create" class="btn btn-primary mb-3">Thêm phòng mới</a>
    <!-- Form tìm kiếm -->
    <form method="GET" action="index.php" class="input-group mb-3" style="max-width: 400px;">
        <input type="hidden" name="route" value="rooms">
        <input type="text" name="keyword" class="form-control" placeholder="Nhập số phòng hoặc mô tả..." value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
        <button class="btn btn-outline-secondary" type="submit">Tìm kiếm</button>
        <?php if(!empty($_GET['keyword'])): ?>
            <a href="index.php" class="btn btn-outline-danger">Hủy</a>
        <?php endif; ?>
    </form>
    <table class="table table-bordered table-hover bg-white">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Số phòng</th>
                <th>Giá thuê</th>
                <th>Trạng thái</th>
                <th>Mô tả</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rooms as $room): ?>
            <tr>
                <td><?= $room['id'] ?></td>
                <td><?= htmlspecialchars($room['room_number']) ?></td>
                <td><?= number_format($room['price'], 0, ',', '.') ?> VNĐ</td>
                <td>
                    <?php if($room['status'] == 'available'): ?>
                        <span class="badge bg-success">Trống (Available)</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Đang thuê (Rented)</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($room['description']) ?></td>
                <td>
                    <a href="index.php?action=edit&id=<?= $room['id'] ?>" class="btn btn-sm btn-warning">Sửa</a>
                    <form method="POST" action="index.php?action=delete" style="display:inline-block;" onsubmit="return confirm('Bạn có chắc muốn xóa phòng này?');">
                        <input type="hidden" name="id" value="<?= $room['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>