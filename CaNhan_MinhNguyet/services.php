<?php
require_once 'db.php';

$errors = [];
$success = "";

// 1. XÓA DỊCH VỤ (Xóa bằng phương thức POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = $_POST['id'] ?? 0;
    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Đã xóa dịch vụ thành công!";
    }
}

// 2. THÊM HOẶC SỬA DỊCH VỤ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && ($_POST['action'] === 'add' || $_POST['action'] === 'edit')) {
    $id = $_POST['id'] ?? '';
    $service_name = trim($_POST['service_name'] ?? '');
    $unit_price = trim($_POST['unit_price'] ?? '');
    $unit = trim($_POST['unit'] ?? '');

    // Validate dữ liệu cơ bản (phục vụ viết test case)
    if (empty($service_name)) {
        $errors[] = "Tên dịch vụ không được để trống.";
    }
    if (!is_numeric($unit_price) || $unit_price < 0) {
        $errors[] = "Đơn giá phải là một số lớn hơn hoặc bằng 0.";
    }
    if (empty($unit)) {
        $errors[] = "Đơn vị tính không được để trống.";
    }

    if (empty($errors)) {
        if ($_POST['action'] === 'add') {
            // Thêm mới dùng Prepared Statement
            $stmt = $pdo->prepare("INSERT INTO services (service_name, unit_price, unit) VALUES (?, ?, ?)");
            $stmt->execute([$service_name, $unit_price, $unit]);
            $success = "Thêm dịch vụ thành công!";
        } else {
            // Cập nhật dùng Prepared Statement
            $stmt = $pdo->prepare("UPDATE services SET service_name = ?, unit_price = ?, unit = ? WHERE id = ?");
            $stmt->execute([$service_name, $unit_price, $unit, $id]);
            $success = "Cập nhật dịch vụ thành công!";
        }
    }
}

// 3. TÌM KIẾM VÀ HIỂN THỊ DANH SÁCH (Read + Search)
$search = $_GET['search'] ?? '';
if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT * FROM services WHERE service_name LIKE ?");
    $stmt->execute(["%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM services");
}
$services = $stmt->fetchAll();

// Lấy dữ liệu nếu đang ở chế độ sửa
$editData = null;
if (isset($_GET['edit'])) {
    $editId = $_GET['edit'];
    $stmtEdit = $pdo->prepare("SELECT * FROM services WHERE id = ?");
    $stmtEdit->execute([$editId]);
    $editData = $stmtEdit->fetch();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Dịch vụ - Nhà trọ</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f4f6f9; }
        .container { max-width: 800px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #007bff; color: white; }
        form { margin-top: 20px; background: #f9f9f9; padding: 15px; border-radius: 5px; }
        input, button { padding: 8px; margin: 5px 0; width: 100%; box-sizing: border-box; }
        button { background: #28a745; color: white; border: none; cursor: pointer; }
        button:hover { background: #218838; }
        .error { color: red; }
        .success { color: green; }
        .btn-delete { background: #dc3545; width: auto; padding: 5px 10px; }
        .btn-edit { background: #ffc107; color: black; text-decoration: none; padding: 5px 10px; display: inline-block; border-radius: 3px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Quản lý Danh mục Dịch vụ</h2>

    <!-- Hiển thị thông báo lỗi hoặc thành công -->
    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul><?php foreach ($errors as $err) echo "<li>$err</li>"; ?></ul>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success"><p><?php echo $success; ?></p></div>
    <?php endif; ?>

    <!-- Form Tìm kiếm -->
    <form method="GET" action="services.php" style="background: none; padding: 0;">
        <input type="text" name="search" placeholder="Nhập tên dịch vụ cần tìm..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" style="width: 150px;">Tìm kiếm</button>
    </form>

    <!-- Form Thêm / Sửa -->
    <form method="POST" action="services.php">
        <h3><?php echo $editData ? 'Sửa Dịch Vụ' : 'Thêm Dịch Vụ Mới'; ?></h3>
        <input type="hidden" name="action" value="<?php echo $editData ? 'edit' : 'add'; ?>">
        <?php if ($editData): ?>
            <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
        <?php endif; ?>
        
        <label>Tên dịch vụ:</label>
        <input type="text" name="service_name" value="<?php echo htmlspecialchars($editData['service_name'] ?? ''); ?>" required>
        
        <label>Đơn giá:</label>
        <input type="number" step="0.01" name="unit_price" value="<?php echo htmlspecialchars($editData['unit_price'] ?? ''); ?>" required>
        
        <label>Đơn vị tính (kWh, Khối, Tháng...):</label>
        <input type="text" name="unit" value="<?php echo htmlspecialchars($editData['unit'] ?? ''); ?>" required>
        
        <button type="submit"><?php echo $editData ? 'Cập nhật' : 'Thêm mới'; ?></button>
    </form>

    <!-- Bảng hiển thị danh sách -->
    <table>
        <tr>
            <th>ID</th>
            <th>Tên dịch vụ</th>
            <th>Đơn giá</th>
            <th>Đơn vị tính</th>
            <th>Thao tác</th>
        </tr>
        <?php if (count($services) > 0): ?>
            <?php foreach ($services as $row): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['service_name']); ?></td>
                <td><?php echo number_format($row['unit_price'], 0, ',', '.'); ?> đ</td>
                <td><?php echo htmlspecialchars($row['unit']); ?></td>
                <td>
                    <a href="services.php?edit=<?php echo $row['id']; ?>" class="btn-edit">Sửa</a>
                    <form method="POST" action="services.php" style="display:inline; background:none; padding:0; margin:0;" onsubmit="return confirm('Bạn có chắc muốn xóa?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <button type="submit" class="btn-delete">Xóa</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5" style="text-align:center;">Không tìm thấy dịch vụ nào.</td></tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>