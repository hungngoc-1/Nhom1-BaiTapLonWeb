<?php
// ==========================================
// 1. KẾT NỐI CƠ SỞ DỮ LIỆU THÔNG QUA CONFIG MVC
// ==========================================
require_once __DIR__ . '/../../Config/Database.php';
$database = new Database();
$conn = $database->getConnection();

// ==========================================
// 2. XỬ LÝ CÁC CHỨC NĂNG CRUD (THÊM, SỬA, XÓA)
// ==========================================
$msg = ''; $msgType = '';

// A. THÊM CHỈ SỐ MỚI (CREATE)
if (isset($_POST['action']) && $_POST['action'] == 'add') {
    try {
        $room_id = $_POST['room_id'];
        $month_year = $_POST['month_year'];
        $electric_old = $_POST['electric_old'];
        $electric_new = $_POST['electric_new'];
        $water_old = $_POST['water_old'];
        $water_new = $_POST['water_new'];

        $check = $conn->prepare("SELECT id FROM utilities_readings WHERE room_id = ? AND month_year = ?");
        $check->execute([$room_id, $month_year]);
        if ($check->rowCount() > 0) {
            $msg = "Phòng này đã có chỉ số điện nước trong kỳ $month_year!"; $msgType = "danger";
        } elseif ($electric_new < $electric_old || $water_new < $water_old) {
            $msg = "Chỉ số mới không được nhỏ hơn chỉ số cũ!"; $msgType = "warning";
        } else {
            $stmt = $conn->prepare("INSERT INTO utilities_readings (room_id, month_year, electric_old, electric_new, water_old, water_new) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$room_id, $month_year, $electric_old, $electric_new, $water_old, $water_new]);
            $msg = "Đã chốt chỉ số điện nước thành công!"; $msgType = "success";
        }
    } catch(PDOException $e) {
        $msg = "Lỗi hệ thống: " . $e->getMessage(); $msgType = "danger";
    }
}

// B. XÓA CHỈ SỐ (DELETE)
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM utilities_readings WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $msg = "Đã xóa bản ghi chỉ số thành công!"; $msgType = "success";
    } catch(PDOException $e) {
        $msg = "Không thể xóa: " . $e->getMessage(); $msgType = "danger";
    }
}

// C. CẬP NHẬT CHỈ SỐ (UPDATE)
if (isset($_POST['action']) && $_POST['action'] == 'edit') {
    try {
        $id = $_POST['id'];
        $electric_new = $_POST['electric_new'];
        $water_new = $_POST['water_new'];
        
        $stmt = $conn->prepare("UPDATE utilities_readings SET electric_new = ?, water_new = ? WHERE id = ?");
        $stmt->execute([$electric_new, $water_new, $id]);
        $msg = "Đã cập nhật chỉ số mới thành công!"; $msgType = "success";
    } catch(PDOException $e) {
        $msg = "Lỗi cập nhật: " . $e->getMessage(); $msgType = "danger";
    }
}

// ==========================================
// 3. TRUY VẤN LẤY DỮ LIỆU VÀ TÌM KIẾM (READ)
// ==========================================
$rooms = $conn->query("SELECT id, room_number FROM rooms WHERE status = 'rented' ORDER BY room_number")->fetchAll(PDO::FETCH_ASSOC);

// Xử lý tìm kiếm phòng theo yêu cầu cá nhân BTVN số 5
$search = trim($_GET['search_room'] ?? '');
if ($search !== '') {
    $stmtSearch = $conn->prepare("
        SELECT u.id, u.month_year, u.electric_old, u.electric_new, u.water_old, u.water_new, r.room_number 
        FROM utilities_readings u
        JOIN rooms r ON u.room_id = r.id
        WHERE r.room_number LIKE ?
        ORDER BY u.id DESC
    ");
    $stmtSearch->execute(['%' . $search . '%']);
    $readings = $stmtSearch->fetchAll(PDO::FETCH_ASSOC);
} else {
    $query = "
        SELECT u.id, u.month_year, u.electric_old, u.electric_new, u.water_old, u.water_new, r.room_number 
        FROM utilities_readings u
        JOIN rooms r ON u.room_id = r.id
        ORDER BY u.id DESC
    ";
    $readings = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayMate - Quản lý Điện Nước</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-teal: #36837c;
            --primary-hover: #2a6a64;
            --bg-body: #f4f8f7;
            --card-radius: 20px;
        }
        body { background-color: var(--bg-body); font-family: 'Nunito', sans-serif; font-size: 0.9rem; color: #334155; min-height: 100vh; }
        
        /* Navbar */
        .navbar-custom { background-color: #ffffff; padding: 10px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06); border-bottom: 2px solid #e2e8f0; }
        .brand-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .brand-logo i { color: var(--primary-teal); font-size: 2rem; }
        .brand-text { color: var(--primary-teal); font-weight: 800; font-size: 1.2rem; line-height: 1.1; }
        .nav-pills .nav-link { color: #64748b; font-weight: 700; font-size: 0.8rem; padding: 7px 12px; border-radius: 14px; transition: all 0.2s; white-space: nowrap; margin-right: 3px; }
        .nav-pills .nav-link:hover { color: var(--primary-teal); background: #e6f3f1; }
        .nav-pills .nav-link.active { background-color: var(--primary-teal); color: white; box-shadow: 0 4px 12px rgba(54, 131, 124, 0.25); }

        /* Card & Table Design */
        .content-card { 
            background: #ffffff; 
            border-radius: var(--card-radius); 
            border: 2px solid #cbd5e1; 
            box-shadow: 0 14px 40px rgba(15, 23, 42, 0.08); 
            overflow: hidden; 
        }
        
        .btn-teal-custom { 
            background-color: var(--primary-teal); 
            color: white; 
            border-radius: 14px; 
            font-weight: 700; 
            font-size: 0.85rem; 
            padding: 10px 20px; 
            border: none; 
            box-shadow: 0 4px 14px rgba(54, 131, 124, 0.35); 
            transition: all 0.2s ease; 
        }
        .btn-teal-custom:hover { background-color: var(--primary-hover); color: white; transform: translateY(-2px); box-shadow: 0 6px 18px rgba(54, 131, 124, 0.45); }
        
        .text-teal { color: var(--primary-teal) !important; }
        
        /* Viền bảng đậm và rõ nét */
        .table-custom { margin-bottom: 0; border-collapse: separate; border-spacing: 0; }
        .table-custom th { 
            background-color: #f1f5f9; 
            color: #334155; 
            font-weight: 800; 
            font-size: 0.78rem; 
            text-transform: uppercase; 
            letter-spacing: 0.6px; 
            border-bottom: 3px solid #cbd5e1; 
            padding: 18px 20px; 
        }
        .table-custom td { 
            padding: 18px 20px; 
            vertical-align: middle; 
            color: #1e293b; 
            font-weight: 600; 
            border-bottom: 2px solid #e2e8f0; 
        }
        .table-hover tbody tr:hover { background-color: #f8fafc; }
        
        /* Badges chỉ số tiêu thụ */
        .usage-badge { 
            background-color: #e0f2fe; 
            color: #0369a1; 
            padding: 6px 12px; 
            border-radius: 10px; 
            font-weight: 800; 
            font-size: 0.75rem; 
            border: 1px solid #bae6fd;
            box-shadow: 0 2px 5px rgba(3, 105, 161, 0.08);
        }
        .usage-badge.water { 
            background-color: #dcfce7; 
            color: #15803d; 
            border-color: #bbf7d0;
            box-shadow: 0 2px 5px rgba(21, 128, 61, 0.08);
        }

        /* Nút chức năng nổi bật */
        .action-btn-edit {
            background-color: #eff6ff; color: #2563eb; border: 2px solid #93c5fd;
            width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;
            border-radius: 12px; font-weight: bold; box-shadow: 0 3px 8px rgba(37, 99, 235, 0.15); transition: all 0.2s ease;
        }
        .action-btn-edit:hover { background-color: #2563eb; color: #ffffff; border-color: #2563eb; transform: translateY(-2px); box-shadow: 0 5px 12px rgba(37, 99, 235, 0.3); }

        .action-btn-delete {
            background-color: #fef2f2; color: #dc2626; border: 2px solid #fca5a5;
            width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center;
            border-radius: 12px; font-weight: bold; box-shadow: 0 3px 8px rgba(220, 38, 38, 0.15); transition: all 0.2s ease;
        }
        .action-btn-delete:hover { background-color: #dc2626; color: #ffffff; border-color: #dc2626; transform: translateY(-2px); box-shadow: 0 5px 12px rgba(220, 38, 38, 0.3); }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container-fluid px-3 px-xl-4">
            <a class="brand-logo me-3" href="index.php?route=dashboard">
                <i class="fa-solid fa-house-chimney"></i>
                <div>
                    <div class="brand-text">StayMate</div>
                    <div class="text-muted" style="font-size: 0.75rem;">Quản Lý Nhà Trọ</div>
                </div>
            </a>
            <ul class="nav nav-pills me-auto d-none d-lg-flex">
                <li class="nav-item"><a class="nav-link" href="index.php?route=dashboard"><i class="fa-solid fa-city"></i> Tòa nhà</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=overview"><i class="fa-solid fa-chart-pie"></i> Tổng quan</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-door-open"></i> Phòng trọ</a></li>
                <li class="nav-item"><a class="nav-link active" href="index.php?route=utilities"><i class="fa-solid fa-bolt"></i> Điện Nước</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=invoices"><i class="fa-solid fa-file-invoice-dollar"></i> Hóa đơn</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width:38px; height:38px; background-color: var(--primary-teal); font-size: 0.9rem;">AS</div>
                <div class="text-start d-none d-sm-block lh-sm">
                    <div class="fw-bold text-dark" style="font-size: 0.85rem;">Quản trị viên <i class="fa-solid fa-angle-down ms-1 text-muted"></i></div>
                    <div class="text-muted" style="font-size: 0.75rem;">Admin hệ thống</div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container-fluid px-4 px-xl-5 py-4">
        <!-- Tiêu đề trang -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1 text-dark">Quản lý Điện Nước</h3>
                <div class="text-muted fw-semibold" style="font-size: 0.85rem;">Chỉ số hàng tháng để thiết lập tự động hóa hóa đơn</div>
            </div>
            <button class="btn btn-teal-custom" data-bs-toggle="modal" data-bs-target="#addUtilityModal">
                <i class="fa-solid fa-plus me-2"></i> Chỉ số mới
            </button>
        </div>

        <!-- Thông báo -->
        <?php if($msg): ?>
            <div class="alert alert-<?= $msgType ?> alert-dismissible fade show border-0 shadow-sm rounded-4 fw-bold" role="alert">
                <i class="fa-solid <?= $msgType == 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation' ?> me-2"></i> <?= $msg ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- THANH TÌM KIẾM THEO SỐ PHÒNG (ĐÁP ỨNG YÊU CẦU BTVN SỐ 5) -->
        <div class="content-card mb-4 p-3">
            <form method="GET" action="index.php" class="row g-3 align-items-center">
                <input type="hidden" name="route" value="utilities">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-2 border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" class="form-control border-2 border-start-0 ps-0 fw-semibold" name="search_room" placeholder="Nhập số phòng cần tìm..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-teal-custom px-4"><i class="fa-solid fa-filter me-1"></i> Tìm kiếm</button>
                    <?php if($search !== ''): ?>
                        <a href="index.php?route=utilities" class="btn btn-outline-secondary rounded-pill px-3 fw-bold ms-2">Hủy lọc</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Bảng danh sách chính -->
        <div class="content-card">
            <div class="card-header bg-white border-bottom border-2 py-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-list-check me-2 text-teal"></i> Lịch sử chỉ số điện nước</h6>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom table-hover align-middle">
                        <thead>
                            <tr>
                                <th class="ps-4">PHÒNG</th>
                                <th>KỲ CHỐT</th>
                                <th>CHỈ SỐ ĐIỆN (CŨ <i class="fa-solid fa-arrow-right text-muted mx-1"></i> MỚI)</th>
                                <th>CHỈ SỐ NƯỚC (CŨ <i class="fa-solid fa-arrow-right text-muted mx-1"></i> MỚI)</th>
                                <th class="text-end pe-4">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($readings) > 0): ?>
                                <?php foreach($readings as $row): 
                                    $dien_tieu_thu = $row['electric_new'] - $row['electric_old'];
                                    $nuoc_tieu_thu = $row['water_new'] - $row['water_old'];
                                ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-teal fs-6"><?= htmlspecialchars($row['room_number']) ?></td>
                                    <td><span class="badge bg-light text-dark border border-2 px-2 py-1"><?= htmlspecialchars($row['month_year']) ?></span></td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="text-muted fw-bold"><?= $row['electric_old'] ?></span>
                                            <i class="fa-solid fa-bolt text-warning"></i>
                                            <span class="fw-bold text-dark"><?= $row['electric_new'] ?></span>
                                            <span class="usage-badge ms-2">+<?= $dien_tieu_thu ?> số</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="text-muted fw-bold"><?= $row['water_old'] ?></span>
                                            <i class="fa-solid fa-droplet text-info"></i>
                                            <span class="fw-bold text-dark"><?= $row['water_new'] ?></span>
                                            <span class="usage-badge water ms-2">+<?= $nuoc_tieu_thu ?> khối</span>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="action-btn-edit me-2" title="Chỉnh sửa chỉ số" 
                                            onclick="openEditModal(<?= $row['id'] ?>, '<?= $row['room_number'] ?>', '<?= $row['month_year'] ?>', <?= $row['electric_old'] ?>, <?= $row['electric_new'] ?>, <?= $row['water_old'] ?>, <?= $row['water_new'] ?>)">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <a href="index.php?route=utilities&action=delete&id=<?= $row['id'] ?>" class="action-btn-delete" title="Xóa bản ghi" onclick="return confirm('Bạn có chắc chắn muốn xóa bản ghi chỉ số này không?');">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-folder-open fs-1 text-teal opacity-50 mb-3"></i>
                                        <p class="fw-semibold mb-0">Không tìm thấy dữ liệu chốt điện nước phù hợp.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL THÊM CHỈ SỐ -->
    <div class="modal fade" id="addUtilityModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-light py-3 px-4 border-bottom border-2">
                    <h5 class="modal-title fw-bold text-teal"><i class="fa-solid fa-bolt me-2"></i> Chốt Chỉ Số Điện Nước</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="index.php?route=utilities">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-xs text-muted text-uppercase">Phòng trọ</label>
                                <select class="form-select fw-semibold border-2" name="room_id" required>
                                    <option value="">-- Chọn phòng --</option>
                                    <?php foreach($rooms as $r): ?>
                                        <option value="<?= $r['id'] ?>"><?= $r['room_number'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-xs text-muted text-uppercase">Kỳ chốt (Tháng-Năm)</label>
                                <input type="text" class="form-control fw-semibold border-2" name="month_year" value="<?= date('m-Y') ?>" required>
                            </div>
                        </div>

                        <div class="p-3 bg-light rounded-3 border border-2 mb-3">
                            <h6 class="fw-bold text-warning mb-3"><i class="fa-solid fa-bolt me-1"></i> Thông số Điện</h6>
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label text-xs fw-bold">Chỉ số CŨ</label>
                                    <input type="number" class="form-control border-2" name="electric_old" required min="0" placeholder="VD: 500">
                                </div>
                                <div class="col-6">
                                    <label class="form-label text-xs fw-bold">Chỉ số MỚI</label>
                                    <input type="number" class="form-control border-2" name="electric_new" required min="0" placeholder="VD: 600">
                                </div>
                            </div>
                        </div>

                        <div class="p-3 bg-light rounded-3 border border-2 mb-4">
                            <h6 class="fw-bold text-info mb-3"><i class="fa-solid fa-droplet me-1"></i> Thông số Nước</h6>
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label text-xs fw-bold">Chỉ số CŨ</label>
                                    <input type="number" class="form-control border-2" name="water_old" required min="0" placeholder="VD: 50">
                                </div>
                                <div class="col-6">
                                    <label class="form-label text-xs fw-bold">Chỉ số MỚI</label>
                                    <input type="number" class="form-control border-2" name="water_new" required min="0" placeholder="VD: 65">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-teal-custom px-4"><i class="fa-solid fa-floppy-disk me-1"></i> Lưu chỉ số</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL SỬA CHỈ SỐ -->
    <div class="modal fade" id="editUtilityModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-light py-3 px-4 border-bottom border-2">
                    <h5 class="modal-title fw-bold text-teal"><i class="fa-solid fa-pen-to-square me-2"></i> Cập nhật Chỉ số</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="index.php?route=utilities">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        
                        <div class="mb-3 text-center">
                            <span class="badge text-white fs-6 px-3 py-2 rounded-pill shadow-sm" style="background-color: var(--primary-teal);" id="edit_title">Phòng -- Kỳ --</span>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <label class="form-label text-xs fw-bold text-warning"><i class="fa-solid fa-bolt"></i> Điện mới</label>
                                <input type="number" class="form-control fw-bold border-2" name="electric_new" id="edit_electric_new" required>
                                <div class="form-text text-xs fw-bold" id="edit_electric_old_text">Chỉ số cũ: 0</div>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-xs fw-bold text-info"><i class="fa-solid fa-droplet"></i> Nước mới</label>
                                <input type="number" class="form-control fw-bold border-2" name="water_new" id="edit_water_new" required>
                                <div class="form-text text-xs fw-bold" id="edit_water_old_text">Chỉ số cũ: 0</div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-teal-custom px-4"><i class="fa-solid fa-check me-1"></i> Cập nhật</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openEditModal(id, room, month, e_old, e_new, w_old, w_new) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_title').innerText = 'Phòng ' + room + ' - Kỳ ' + month;
            
            document.getElementById('edit_electric_new').value = e_new;
            document.getElementById('edit_electric_new').min = e_old;
            document.getElementById('edit_electric_old_text').innerText = 'Chỉ số cũ: ' + e_old;
            
            document.getElementById('edit_water_new').value = w_new;
            document.getElementById('edit_water_new').min = w_old;
            document.getElementById('edit_water_old_text').innerText = 'Chỉ số cũ: ' + w_old;
            
            new bootstrap.Modal(document.getElementById('editUtilityModal')).show();
        }
    </script>
</body>
</html>