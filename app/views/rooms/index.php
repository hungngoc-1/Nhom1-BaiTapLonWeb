<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Phòng trọ</title>
    <!-- Thư viện Bootstrap 5 & Icons giúp làm đẹp giao diện -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Quản lý Danh sách Phòng</h3>
            <p class="text-muted mb-0">Tra cứu và lọc trạng thái phòng trọ</p>
        </div>
        <!-- Nút chuyển sang trang thêm mới -->
        <a href="index.php?route=rooms_create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Thêm phòng mới
        </a>
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
                            <th>Trạng thái</th>
                            <th class="text-end pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($rooms)): ?>
                            <?php foreach ($rooms as $room): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-primary"><?= htmlspecialchars($room['room_number']) ?></td>
                                    <td><?= htmlspecialchars($room['area'] ?? 0) ?> m²</td>
                                    <td class="fw-semibold text-danger"><?= number_format($room['price'] ?? 0, 0, ',', '.') ?> đ</td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2">
                                            <?= htmlspecialchars($room['status'] ?? 'Trống') ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <!-- Các nút điều hướng -->
                                        <a href="index.php?route=rooms_show&id=<?= $room['id'] ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i> Xem</a>
                                        <a href="index.php?route=rooms_edit&id=<?= $room['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Sửa</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">Không có dữ liệu phòng.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>