<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>CRUD Cá Nhân - Ánh Ngọc</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
    <div class="container bg-white p-4 rounded shadow">
        <h3 class="fw-bold mb-4 text-primary">Quản lý Tài Khoản (CRUD)</h3>

        <!-- Báo trạng thái -->
        <?php if (!empty($message)): ?> <div class="alert alert-danger"><?= $message ?></div> <?php endif; ?>
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'created'): ?> <div class="alert alert-success">Thêm thành công!</div> <?php endif; ?>
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?> <div class="alert alert-success">Cập nhật thành công!</div> <?php endif; ?>
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?> <div class="alert alert-success">Đã xóa tài khoản!</div> <?php endif; ?>

        <!-- Tìm kiếm -->
        <form method="GET" action="UserController.php" class="d-flex mb-4 w-50">
            <input type="text" name="keyword" class="form-control me-2" placeholder="Tìm tên, username..." value="<?= htmlspecialchars($keyword ?? '') ?>">
            <button type="submit" class="btn btn-primary">Tìm</button>
            <a href="UserController.php" class="btn btn-outline-secondary ms-2">Reset</a>
        </form>

        <div class="row">
            <!-- Form Thêm / Sửa -->
            <div class="col-md-4 mb-4">
                <div class="card card-body bg-light border-primary">
                    <h5 class="card-title text-primary"><?= $edit_user ? 'Sửa Tài Khoản' : 'Thêm Mới' ?></h5>
                    <form method="POST" action="UserController.php">
                        <input type="hidden" name="action" value="<?= $edit_user ? 'update' : 'create' ?>">
                        <?php if ($edit_user): ?> <input type="hidden" name="id" value="<?= $edit_user['id'] ?>"> <?php endif; ?>
                        
                        <div class="mb-2">
                            <label>Tên đăng nhập *</label>
                            <input type="text" name="username" class="form-control" value="<?= $edit_user ? $edit_user['username'] : '' ?>" <?= $edit_user ? 'readonly' : '' ?>>
                        </div>
                        <?php if (!$edit_user): ?>
                        <div class="mb-2">
                            <label>Mật khẩu *</label>
                            <input type="password" name="password" class="form-control">
                        </div>
                        <?php endif; ?>
                        <div class="mb-2">
                            <label>Họ và tên *</label>
                            <input type="text" name="full_name" class="form-control" value="<?= $edit_user ? $edit_user['full_name'] : '' ?>">
                        </div>
                        <div class="mb-3">
                            <label>Vai trò</label>
                            <select name="role" class="form-select">
                                <option value="tenant" <?= ($edit_user && $edit_user['role'] == 'tenant') ? 'selected' : '' ?>>Người thuê</option>
                                <option value="landlord" <?= ($edit_user && $edit_user['role'] == 'landlord') ? 'selected' : '' ?>>Chủ trọ</option>
                                <option value="admin" <?= ($edit_user && $edit_user['role'] == 'admin') ? 'selected' : '' ?>>Quản trị viên</option>
                            </select>
                        </div>
                        <button type="submit" class="btn <?= $edit_user ? 'btn-warning' : 'btn-success' ?> w-100">
                            <?= $edit_user ? 'Cập nhật' : 'Thêm tài khoản' ?>
                        </button>
                        <?php if ($edit_user): ?>
                            <a href="UserController.php" class="btn btn-secondary w-100 mt-2">Hủy sửa</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <!-- Bảng Dữ Liệu -->
            <div class="col-md-8">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th><th>Username</th><th>Họ tên</th><th>Vai trò</th><th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= $user['id'] ?></td>
                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($user['full_name']) ?></td>
                                    <td><?= htmlspecialchars($user['role']) ?></td>
                                    <td>
                                        <!-- Nút Sửa -->
                                        <a href="UserController.php?edit_id=<?= $user['id'] ?>" class="btn btn-sm btn-warning">Sửa</a>
                                        <!-- Nút Xóa bọc Form POST -->
                                        <form method="POST" action="UserController.php" style="display:inline;" onsubmit="return confirm('Xóa tài khoản này?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center">Không có dữ liệu</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>