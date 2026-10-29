<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayMate - Quản lý Hợp đồng</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-purple: #6d28d9;
            --primary-hover: #5b21b6;
            --bg-body: #f3f4f6;
            --text-main: #374151;
            --border-color: #e5e7eb;
        }
        body { background-color: var(--bg-body); font-family: 'Nunito', sans-serif; font-size: 0.85rem; color: var(--text-main); }
        .navbar-custom { background-color: #ffffff; padding: 10px 20px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05); border-bottom: 1px solid var(--border-color); }
        .brand-logo i { color: var(--primary-purple); font-size: 1.8rem; }
        .brand-text { color: var(--primary-purple); font-weight: 800; font-size: 1.1rem; line-height: 1; }
        .nav-pills .nav-link { color: #6b7280; font-weight: 700; font-size: 0.8rem; padding: 8px 12px; border-radius: 12px; margin-right: 4px; }
        .nav-pills .nav-link:hover { background: #f3f4f6; color: var(--primary-purple); }
        .nav-pills .nav-link.active { background-color: var(--primary-purple); color: white; }
        .toolbar { background: #ffffff; border-radius: 16px; padding: 16px 20px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); margin-bottom: 20px; }
        .form-control, .form-select { font-size: 0.85rem; border-radius: 10px; border: 1px solid var(--border-color); padding: 8px 12px; }
        .btn-primary-custom { background-color: var(--primary-purple); color: white; border-radius: 10px; font-weight: 700; border: none; padding: 8px 16px; font-size: 0.85rem; transition: 0.2s; }
        .btn-primary-custom:hover { background-color: var(--primary-hover); color: white; }
        .btn-outline-custom { border: 1px solid var(--border-color); background: white; color: #4b5563; border-radius: 10px; font-weight: 600; padding: 8px 16px; }
        .table-card { background: #ffffff; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); overflow: hidden; }
        .table thead th { background-color: #f9fafb; color: #6b7280; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; padding: 16px; border-bottom: 1px solid var(--border-color); }
        .table tbody td { padding: 16px; vertical-align: middle; border-bottom: 1px solid var(--border-color); color: #1f2937; font-weight: 600; }
        .badge-room { background-color: #111827; color: white; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; }
        .badge-status { padding: 4px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; }
        .status-active { background-color: #d1fae5; color: #059669; border: 1px solid #10b981; }
        .status-ended { background-color: #f3f4f6; color: #6b7280; border: 1px solid #d1d5db; }
        .status-wait { background-color: #fef3c7; color: #d97706; border: 1px solid #f59e0b; }
        .badge-sign { background-color: #e0f2fe; color: #0284c7; padding: 4px 8px; border-radius: 6px; font-size: 0.7rem; }
        .btn-action-main { background-color: #ede9fe; color: var(--primary-purple); border: 1px solid #ddd6fe; border-radius: 8px; padding: 4px 10px; font-size: 0.75rem; font-weight: 700; text-decoration: none; display: inline-block; }
        .btn-action-danger { background-color: #fef2f2; color: #ef4444; border: 1px solid #fecaca; border-radius: 8px; padding: 4px 10px; font-size: 0.75rem; font-weight: 600; text-decoration: none; display: inline-block; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container-fluid px-4">
            <a class="brand-logo d-flex align-items-center gap-2 text-decoration-none me-4" href="index.php?route=dashboard">
                <i class="fa-solid fa-building-user"></i>
                <div>
                    <div class="brand-text">SMART STAY</div>
                    <div class="text-muted" style="font-size: 0.7rem; font-weight: 600;">Quản Lý Nhà Trọ</div>
                </div>
            </a>
            <ul class="nav nav-pills me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php?route=dashboard"><i class="fa-solid fa-chart-pie"></i> Tổng quan</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=rooms"><i class="fa-solid fa-door-open"></i> Phòng trọ</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-users"></i> Khách thuê</a></li>
                <li class="nav-item"><a class="nav-link active" href="index.php?route=contracts"><i class="fa-solid fa-file-contract"></i> Hợp đồng</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-bolt"></i> Điện Nước</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-file-invoice-dollar"></i> Hóa đơn</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-chart-column"></i> Báo cáo</a></li>
            </ul>
        </div>
    </nav>

    <div class="container-fluid px-4 py-4">
        
        <!-- BỘ LỌC ĐỘNG (Lưu lại giá trị tìm kiếm) -->
        <div class="toolbar d-flex justify-content-between align-items-center">
            <form action="index.php" method="GET" class="d-flex gap-3 align-items-center" style="width: 70%;">
                <input type="hidden" name="route" value="contracts">
                
                <input type="text" name="keyword" class="form-control" placeholder="Tìm phòng, khách..." 
                       value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>" style="max-width: 250px;">
                
                <select name="status" class="form-select" style="max-width: 200px;">
                    <option value="">Tất cả trạng thái</option>
                    <option value="Đang thuê" <?= ($_GET['status'] ?? '') === 'Đang thuê' ? 'selected' : '' ?>>Đang thuê</option>
                    <option value="Chờ duyệt" <?= ($_GET['status'] ?? '') === 'Chờ duyệt' ? 'selected' : '' ?>>Chờ duyệt</option>
                    <option value="Đã thanh lý" <?= ($_GET['status'] ?? '') === 'Đã thanh lý' ? 'selected' : '' ?>>Đã thanh lý</option>
                </select>
                
                <button type="submit" class="btn btn-outline-custom"><i class="fa-solid fa-filter me-1"></i> Lọc</button>
            </form>
            
            <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#createContractModal">
                <i class="fa-solid fa-plus me-2"></i> Lập Hợp đồng
            </button>
        </div>

        <!-- BẢNG DỮ LIỆU ĐỘNG PHP -->
        <div class="table-card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Mã HĐ</th>
                            <th>Phòng</th>
                            <th>Khách thuê</th>
                            <th>Ngày hiệu lực</th>
                            <th>Tiền cọc</th>
                            <th>Hình thức ký</th>
                            <th>Trạng thái</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($contracts)): ?>
                            <?php foreach ($contracts as $contract): ?>
                                <tr>
                                    <td class="text-primary fw-bold">HD-<?= date('Y') ?>-<?= str_pad($contract['id'], 3, '0', STR_PAD_LEFT) ?></td>
                                    <td><span class="badge-room"><?= htmlspecialchars($contract['room_number'] ?? 'Trống') ?></span></td>
                                    <td>
                                        <div><?= htmlspecialchars($contract['full_name'] ?? 'Không rõ') ?></div>
                                        <div class="text-muted fw-normal" style="font-size: 0.75rem;"><?= htmlspecialchars($contract['phone'] ?? '') ?></div>
                                    </td>
                                    <td style="font-size: 0.8rem;">
                                        <div class="text-muted">Bắt đầu: <span class="text-dark fw-bold"><?= date('d/m/Y', strtotime($contract['start_date'])) ?></span></div>
                                        <div class="text-muted">Kết thúc: <span class="text-dark fw-bold"><?= date('d/m/Y', strtotime($contract['end_date'])) ?></span></div>
                                    </td>
                                    <td style="font-size: 0.8rem;">
                                        <div class="text-danger fw-bold"><?= number_format($contract['deposit_amount'], 0, ',', '.') ?> đ</div>
                                    </td>
                                    <td>
                                        <span class="badge-sign"><i class="fa-solid fa-pen-nib me-1"></i> Điện tử</span>
                                    </td>
                                    <td>
                                        <?php 
                                            // Chỉnh màu badge theo trạng thái
                                            $badgeClass = 'status-wait';
                                            if ($contract['status'] == 'Đang thuê') $badgeClass = 'status-active';
                                            if ($contract['status'] == 'Đã thanh lý') $badgeClass = 'status-ended';
                                        ?>
                                        <span class="badge-status <?= $badgeClass ?>"><?= htmlspecialchars($contract['status']) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <a href="index.php?route=contracts/edit&id=<?= $contract['id'] ?>" class="btn-action-main me-1"><i class="fa-solid fa-pen me-1"></i> Sửa</a>
                                        <a href="index.php?route=contracts/delete&id=<?= $contract['id'] ?>" class="btn-action-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa hợp đồng số HD-<?= date('Y') ?>-<?= str_pad($contract['id'], 3, '0', STR_PAD_LEFT) ?> này không?');"><i class="fa-solid fa-trash me-1"></i> Xóa</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Không tìm thấy hợp đồng nào khớp với yêu cầu.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL THÊM HỢP ĐỒNG -->
    <div class="modal fade" id="createContractModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="border-radius: 16px;">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Lập Hợp đồng Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <!-- Form gọi đến route xử lý Thêm -->
                <form action="index.php?route=contracts/store" method="POST">
                    <div class="modal-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="fw-bold" style="font-size:0.8rem">ID Phòng trọ <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="room_id" placeholder="Nhập ID phòng (VD: 1)" required>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold" style="font-size:0.8rem">ID Khách thuê <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="tenant_id" placeholder="Nhập ID khách (VD: 1)" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <label class="fw-bold" style="font-size:0.8rem">Ngày bắt đầu <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="start_date" required>
                            </div>
                            <div class="col-md-3">
                                <label class="fw-bold" style="font-size:0.8rem">Ngày kết thúc <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="end_date" required>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold" style="font-size:0.8rem">Tiền cọc (VNĐ) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="deposit_amount" required>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="fw-bold" style="font-size:0.8rem">Trạng thái hợp đồng</label>
                            <select class="form-select" name="status">
                                <option value="Chờ duyệt">Chờ duyệt</option>
                                <option value="Đang thuê">Đang thuê</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-outline-custom" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary-custom"><i class="fa-solid fa-check me-1"></i> Lưu Hợp đồng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>