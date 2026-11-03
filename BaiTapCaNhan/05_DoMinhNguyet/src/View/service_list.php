<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Dịch vụ - StayMate</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary-teal: #36837c; --primary-hover: #2a6a64; --bg-body: #f4f8f7; --card-radius: 20px; }
        body { background-color: var(--bg-body); font-family: 'Nunito', sans-serif; font-size: 0.9rem; color: #334155; }
        .navbar-custom { background-color: #ffffff; padding: 10px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); border-bottom: 1px solid #e2e8f0; }
        .brand-logo i { color: var(--primary-teal); font-size: 2rem; }
        .brand-text { color: var(--primary-teal); font-weight: 800; font-size: 1.2rem; }
        .nav-pills .nav-link { color: #64748b; font-weight: 700; font-size: 0.85rem; padding: 8px 14px; border-radius: 14px; margin-right: 4px; }
        .nav-pills .nav-link.active { background-color: var(--primary-teal); color: white; box-shadow: 0 4px 12px rgba(54, 131, 124, 0.3); }
        .content-card { background: #ffffff; border-radius: var(--card-radius); border: 1px solid #e2e8f0; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04); }
        .btn-teal-custom { background-color: var(--primary-teal); color: white; border-radius: 14px; font-weight: 700; padding: 10px 22px; }
        .btn-teal-custom:hover { background-color: var(--primary-hover); color: white; transform: translateY(-2px); }
        .table-container { border: 1px solid #cbd5e1; border-radius: 16px; overflow: hidden; }
        .table-custom th { background-color: #f8fafc; color: #475569; font-weight: 800; padding: 18px 20px; text-transform: uppercase; }
        .table-custom td { padding: 18px 20px; vertical-align: middle; font-weight: 600; }
        .action-btn-edit, .action-btn-delete { width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; border: none; cursor: pointer; transition: 0.2s; }
        .action-btn-edit { background-color: #eff6ff; color: #2563eb; }
        .action-btn-edit:hover { background-color: #2563eb; color: #fff; transform: translateY(-2px); }
        .action-btn-delete { background-color: #fef2f2; color: #dc2626; }
        .action-btn-delete:hover { background-color: #dc2626; color: #fff; transform: translateY(-2px); }
    </style>
</head>
<body>
    <?php $rootUrl = (strpos($_SERVER['SCRIPT_NAME'] ?? '', '/BaiTapCaNhan/') !== false) ? '../../../index.php' : 'index.php'; ?>
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container-fluid px-3 px-xl-4">
            <a class="brand-logo d-flex align-items-center gap-2 text-decoration-none me-3" href="<?= $rootUrl ?>?route=dashboard">
                <i class="fa-solid fa-house-chimney"></i>
                <div>
                    <div class="brand-text lh-1">StayMate</div>
                    <div class="text-muted" style="font-size: 0.75rem;">Quản Lý Nhà Trọ</div>
                </div>
            </a>
            <ul class="nav nav-pills me-auto d-none d-lg-flex">
                <li class="nav-item"><a class="nav-link" href="<?= $rootUrl ?>?route=dashboard"><i class="fa-solid fa-city"></i> Tòa nhà</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $rootUrl ?>?route=overview"><i class="fa-solid fa-chart-pie"></i> Tổng quan</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $rootUrl ?>?route=rooms"><i class="fa-solid fa-bed"></i> Phòng trọ</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $rootUrl ?>?route=contracts"><i class="fa-solid fa-file-signature"></i> Hợp đồng</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $rootUrl ?>?route=utilities"><i class="fa-solid fa-bolt"></i> Điện Nước</a></li>
                <li class="nav-item"><a class="nav-link active" href="<?= $rootUrl ?>?route=services"><i class="fa-solid fa-money-bill-wave"></i> Cấu hình Đơn giá</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $rootUrl ?>?route=reports"><i class="fa-solid fa-chart-simple"></i> Báo cáo</a></li>
            </ul>
            <a href="<?= $rootUrl ?>?route=utilities" class="btn btn-outline-secondary rounded-pill fw-bold text-xs"><i class="fa-solid fa-arrow-left me-1"></i> Quay lại Điện Nước</a>
        </div>
    </nav>

    <div class="container-fluid px-4 px-xl-5 py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1 text-dark">Quản lý Đơn giá Dịch vụ</h3>
                <div class="text-muted fw-semibold" style="font-size: 0.85rem;">Thiết lập giá cơ sở để tự động tính toán hóa đơn</div>
            </div>
            <button class="btn btn-teal-custom" data-bs-toggle="modal" data-bs-target="#serviceModal" onclick="openAddModal()">
                <i class="fa-solid fa-plus me-2"></i> Thêm Dịch Vụ
            </button>
        </div>

        <?php if(!empty($msg)): ?>
            <div class="alert alert-<?= $msgType ?> alert-dismissible fade show fw-bold rounded-4 shadow-sm" role="alert">
                <i class="fa-solid <?= $msgType == 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation' ?> me-2"></i> <?= htmlspecialchars($msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="content-card p-0">
            <div class="table-container table-responsive">
                <table class="table table-custom table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">TÊN DỊCH VỤ</th>
                            <th>ĐƠN GIÁ</th>
                            <th>ĐƠN VỊ TÍNH</th>
                            <th class="text-end pe-4">THAO TÁC</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $sv): ?>
                        <tr>
                            <td class="ps-4 fw-bold" style="color: var(--primary-teal); font-size: 1rem;"><i class="fa-solid fa-tags me-2 text-muted"></i><?= htmlspecialchars($sv['service_name']) ?></td>
                            <td><span class="badge bg-light text-dark border border-2 px-3 py-2 fs-6"><?= number_format($sv['unit_price']) ?> đ</span></td>
                            <td class="fw-bold text-muted"><?= htmlspecialchars($sv['unit']) ?></td>
                            <td class="text-end pe-4">
                                <button class="action-btn-edit me-2" onclick="openEditModal(<?= $sv['id'] ?>, '<?= htmlspecialchars($sv['service_name']) ?>', <?= $sv['unit_price'] ?>, '<?= htmlspecialchars($sv['unit']) ?>')">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <a href="index.php?route=services&action=delete&id=<?= $sv['id'] ?>" class="action-btn-delete" onclick="return confirm('Bạn có chắc chắn muốn xóa dịch vụ này?');">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Form (Dùng chung cho cả Thêm và Sửa) -->
    <div class="modal fade" id="serviceModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-light py-3 px-4 border-bottom border-2">
                    <h5 class="modal-title fw-bold" style="color: var(--primary-teal)" id="modalTitle"><i class="fa-solid fa-plus me-2"></i>Thêm Dịch Vụ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="index.php?route=services">
                        <input type="hidden" name="action" id="formAction" value="add">
                        <input type="hidden" name="id" id="serviceId">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-xs text-muted text-uppercase">Tên dịch vụ</label>
                            <input type="text" class="form-control fw-bold border-2 p-2" name="service_name" id="serviceName" required placeholder="VD: Tiền gửi xe">
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <label class="form-label fw-bold text-xs text-muted text-uppercase">Đơn giá (VNĐ)</label>
                                <input type="number" class="form-control fw-bold border-2 p-2" name="unit_price" id="servicePrice" required min="0" placeholder="VD: 150000">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold text-xs text-muted text-uppercase">Đơn vị tính</label>
                                <input type="text" class="form-control fw-bold border-2 p-2" name="unit" id="serviceUnit" required placeholder="VD: Chiếc/Tháng">
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary border-2 rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-teal-custom px-4"><i class="fa-solid fa-floppy-disk me-1"></i> Lưu thông tin</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openAddModal() {
            document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-plus me-2"></i>Thêm Dịch Vụ Mới';
            document.getElementById('formAction').value = 'add';
            document.getElementById('serviceId').value = '';
            document.getElementById('serviceName').value = '';
            document.getElementById('servicePrice').value = '';
            document.getElementById('serviceUnit').value = '';
        }

        function openEditModal(id, name, price, unit) {
            document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-pen-to-square me-2"></i>Cập nhật Dịch Vụ';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('serviceId').value = id;
            document.getElementById('serviceName').value = name;
            document.getElementById('servicePrice').value = price;
            document.getElementById('serviceUnit').value = unit;
            new bootstrap.Modal(document.getElementById('serviceModal')).show();
        }
    </script>
</body>
</html>