<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Phòng Trọ - CRUD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
    <div class="container bg-white p-4 rounded shadow">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-primary mb-0">Quản lý Phòng Trọ (Thêm - Sửa - Xóa - Tìm kiếm)</h3>
            <a href="index.php?route=dashboard" class="btn btn-outline-secondary btn-sm">← Về Dashboard Tổng quan</a>
        </div>

        <!-- Báo trạng thái -->
        <?php if (!empty($message)): ?> <div class="alert alert-danger"><?= $message ?></div> <?php endif; ?>
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'created'): ?> <div class="alert alert-success">Thêm phòng thành công!</div> <?php endif; ?>
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?> <div class="alert alert-success">Cập nhật phòng thành công!</div> <?php endif; ?>
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?> <div class="alert alert-success">Đã xóa phòng thành công!</div> <?php endif; ?>

        <!-- Ô Tìm kiếm -->
        <form method="GET" action="index.php" class="d-flex mb-4 w-50">
            <input type="hidden" name="route" value="rooms">
            <input type="text" name="keyword" class="form-control me-2" placeholder="Tìm số phòng, mô tả..." value="<?= htmlspecialchars($keyword ?? '') ?>">
            <button type="submit" class="btn btn-primary">Tìm</button>
            <a href="index.php?route=rooms" class="btn btn-outline-secondary ms-2">Reset</a>
        </form>

        <div class="row">
            <!-- Form Thêm mới / Cập nhật (Nằm bên trái) -->
            <div class="col-md-4 mb-4">
                <div class="card card-body bg-light border-primary">
                    <h5 class="card-title text-primary"><?= $edit_room ? 'Sửa Thông Tin Phòng' : 'Thêm Phòng Mới' ?></h5>
                    <form method="POST" action="index.php?route=rooms">
                        <input type="hidden" name="action" value="<?= $edit_room ? 'update' : 'create' ?>">
                        <?php if ($edit_room): ?> <input type="hidden" name="id" value="<?= $edit_room['id'] ?>"> <?php endif; ?>
                        
                        <div class="mb-2">
                            <label>Số phòng *</label>
                            <input type="text" name="room_number" class="form-control" value="<?= $edit_room ? $edit_room['room_number'] : '' ?>" required placeholder="VD: P101">
                        </div>
                        <div class="mb-2">
                            <label>Giá thuê (VNĐ) *</label>
                            <input type="number" name="price" class="form-control" value="<?= $edit_room ? $edit_room['price'] : '' ?>" required placeholder="VD: 2500000">
                        </div>
                        <div class="mb-2">
                            <label>Trạng thái</label>
                            <select name="status" class="form-select">
                                <option value="available" <?= ($edit_room && $edit_room['status'] == 'available') ? 'selected' : '' ?>>Trống (available)</option>
                                <option value="rented" <?= ($edit_room && $edit_room['status'] == 'rented') ? 'selected' : '' ?>>Đang thuê (rented)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Mô tả / Tiện ích</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Điều hòa, nóng lạnh..."><?= $edit_room ? $edit_room['description'] : '' ?></textarea>
                        </div>
                        <button type="submit" class="btn <?= $edit_room ? 'btn-warning' : 'btn-success' ?> w-100">
                            <?= $edit_room ? 'Cập nhật phòng' : 'Thêm phòng mới' ?>
                        </button>
                        <?php if ($edit_room): ?>
                            <a href="index.php?route=rooms" class="btn btn-secondary w-100 mt-2">Hủy sửa</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Bảng Dữ Liệu Danh Sách Phòng (Nằm bên phải) -->
            <div class="col-md-8">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Số phòng</th>
                            <th>Giá thuê</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($rooms)): ?>
                            <?php foreach ($rooms as $room): ?>
                                <tr>
                                    <td><?= $room['id'] ?></td>
                                    <td class="fw-bold text-primary"><?= htmlspecialchars($room['room_number']) ?></td>
                                    <td class="text-danger fw-semibold"><?= number_format($room['price'], 0, ',', '.') ?> đ</td>
                                    <td>
                                        <span class="badge <?= $room['status'] == 'available' ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= htmlspecialchars($room['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <!-- Nút Sửa -->
                                        <a href="index.php?route=rooms&edit_id=<?= $room['id'] ?>" class="btn btn-sm btn-warning">Sửa</a>
                                        <!-- Nút Xóa bọc Form POST bảo mật -->
                                        <form method="POST" action="index.php?route=rooms" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa phòng này không?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $room['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">Không có dữ liệu phòng trọ</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>