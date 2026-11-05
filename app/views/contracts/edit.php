<?php
/**
 * TRANG CHỈNH SỬA HỢP ĐỒNG (EDIT CONTRACT)
 * Phụ trách: Hùng Văn Ngọc (Nhóm trưởng - MSSV: 224001738)
 */
if (!function_exists('h')) {
    function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
$monthlyRent = (float)($contract['monthly_rent'] ?? $contract['room_price'] ?? $contract['deposit_amount']);
$depositDate = !empty($contract['deposit_date']) ? $contract['deposit_date'] : $contract['start_date'];
$cType = $contract['contract_type'] ?? 'official_lease';
if (($contract['status'] ?? '') === 'terminated' || ($contract['status'] ?? '') === 'expired') {
    $_SESSION['flash_error'] = 'Hợp đồng đã thanh lý không thể chỉnh sửa, chỉ được xem chi tiết!';
    header('Location: index.php?route=contracts/detail&id=' . (int)$contract['id']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayMate - Sửa Hợp đồng <?= h($contract['contract_code']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --teal-main: #308f86; --teal-hover: #25756d; --bg-page: #f8fafc; }
        body {
            background-color: var(--bg-page);
            font-family: 'Nunito', sans-serif;
            font-size: 0.9rem;
            color: #334155;
            min-height: 100vh;
        }
        .text-teal-main { color: var(--teal-main) !important; }
        .edit-card {
            background: #ffffff;
            border-radius: 26px;
            border: 4px solid #ffffff;
            padding: 30px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.08);
            max-width: 860px;
            margin: 0 auto;
        }
        .form-control, .form-select { border-radius: 14px; border: 1.5px solid #e2e8f0; padding: 9px 14px; font-weight: 600; font-size: 0.9rem; }
        .form-control:focus, .form-select:focus { border-color: var(--teal-main); box-shadow: 0 0 0 3px rgba(48, 143, 134, 0.15); }
        .btn-teal-custom { background: var(--teal-main); border-radius: 50px; font-weight: 800; color: white; padding: 10px 26px; border: 2px solid var(--teal-hover); box-shadow: 0 8px 20px rgba(48, 143, 134, 0.35); }
        .btn-teal-custom:hover { background: var(--teal-hover); color: white; }
        .btn-back-content { background: white; border: 3px solid #ffffff; color: #334155; font-weight: 800; font-size: 0.9rem; padding: 9px 24px; border-radius: 50px; text-decoration: none; display: inline-flex; align-items: center; box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
        .navbar-custom { background: #ffffff; padding: 12px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04); z-index: 100; border-bottom: 1px solid #f1f5f9; }
        .brand-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .brand-logo i { color: var(--teal-main); font-size: 2.2rem; }
        .brand-text { color: var(--teal-main); font-weight: 900; font-size: 1.4rem; line-height: 1; letter-spacing: -0.5px; }
        .brand-sub { color: #64748b; font-size: 0.75rem; font-weight: 600; }
        .nav-pills { gap: 6px; flex-wrap: nowrap !important; }
        .nav-pills .nav-link { 
            color: #475569; font-weight: 800; font-size: 0.88rem; 
            padding: 8px 14px; border-radius: 50px; transition: all 0.2s; white-space: nowrap; 
        }
        .nav-pills .nav-link i { margin-right: 6px; }
        .nav-pills .nav-link:hover { color: var(--teal-main); background: #f0fdfa; }
        .nav-pills .nav-link.active { background-color: var(--teal-main); color: white !important; box-shadow: 0 6px 15px rgba(48, 143, 134, 0.35); }
        .avatar-circle { width: 46px; height: 46px; background-color: var(--teal-main); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 900; font-size: 1rem; border: 3px solid #ffffff; box-shadow: 0 6px 15px rgba(48, 143, 134, 0.4); transition: 0.2s; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container-fluid px-3 px-xl-5">
            <a class="brand-logo me-4" href="index.php?route=dashboard">
                <i class="fa-solid fa-house-chimney text-teal-main"></i>
                <div>
                    <div class="brand-text">StayMate</div>
                    <div class="brand-sub">Quản Lý Nhà Trọ</div>
                </div>
            </a>

            <div class="d-flex align-items-center gap-3 order-lg-3">
                <div class="dropdown">
                    <div class="d-flex align-items-center gap-2 cursor-pointer" data-bs-toggle="dropdown" style="cursor: pointer;">
                        <div class="avatar-circle">AS</div>
                        <div class="text-start d-none d-sm-block lh-sm">
                            <div class="fw-bold text-dark" style="font-size: 0.9rem;"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Quản trị viên Hệ thống') ?> <i class="fa-solid fa-angle-down ms-1 text-muted fs-6"></i></div>
                            <div class="text-muted text-capitalize" style="font-size: 0.75rem;"><?= htmlspecialchars($_SESSION['role'] ?? 'admin') ?></div>
                        </div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 mt-2 p-2">
                        <li>
                            <button type="button" class="dropdown-item rounded-3 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#profileModal">
                                <i class="fa-solid fa-id-card me-2 text-teal-main" style="color:#308f86;"></i> Thông tin tài khoản
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item rounded-3 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                                <i class="fa-solid fa-key me-2 text-warning"></i> Đổi mật khẩu
                            </button>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item rounded-3 py-2 text-danger fw-bold" href="index.php?route=auth/logout"><i class="fa-solid fa-right-from-bracket me-2"></i> Đăng xuất</a></li>
                    </ul>
                </div>

                <button class="btn-hamburger d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#staymateMobileNav">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>

            <div class="collapse navbar-collapse mobile-nav-collapse order-lg-2" id="staymateMobileNav">
                <ul class="nav nav-pills flex-nowrap me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php?route=overview"><i class="fa-solid fa-border-all"></i> Tổng quan</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?route=rooms"><i class="fa-solid fa-bed"></i> Phòng trọ</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?route=tenants"><i class="fa-solid fa-users"></i> Khách thuê</a></li>
                    <li class="nav-item"><a class="nav-link active" href="index.php?route=contracts"><i class="fa-solid fa-file-signature"></i> Hợp đồng</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?route=utilities"><i class="fa-solid fa-bolt"></i> Điện Nước</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?route=invoices"><i class="fa-solid fa-file-invoice-dollar"></i> Hóa đơn</a></li>
                    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="index.php?route=accounts"><i class="fa-solid fa-user-gear"></i> Tài khoản</a></li>
                <?php endif; ?>
                <?php if (in_array(($_SESSION['role'] ?? ''), ['admin', 'manager', 'landlord'], true)): ?>
                    <li class="nav-item"><a class="nav-link" href="index.php?route=reports_csv"><i class="fa-solid fa-chart-pie"></i> Báo cáo</a></li>
                <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="index.php?route=showcase" target="_blank"><i class="fa-regular fa-eye"></i> Xem phòng khách</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?route=tenant/portal" target="_blank"><i class="fa-solid fa-user-shield text-teal-main"></i> Cổng khách thuê</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4" style="max-width: 860px; margin: 0 auto;">
            <div>
                <h4 class="fw-bold mb-0 text-teal-main">
                    <i class="fa-solid fa-file-pen me-2"></i>Cập nhật Hợp đồng <?= h($contract['contract_code']) ?>
                </h4>
                <div class="small text-muted fw-semibold">Chỉnh sửa thông tin hợp đồng thuê phòng</div>
            </div>
        </div>

        <?php if (!empty($flash_error)): ?>
            <div class="alert alert-danger rounded-4 fw-bold mx-auto mb-3" style="max-width: 860px;"><?= h($flash_error) ?></div>
        <?php endif; ?>

        <div class="edit-card">
            <form action="index.php?route=contracts/update" method="POST">
                <input type="hidden" name="id" value="<?= (int)$contract['id'] ?>">

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Loại hợp đồng <span class="text-danger">*</span></label>
                        <select class="form-select" name="contract_type" id="edit_contract_type" onchange="onEditTypeChange()">
                            <option value="deposit_hold" <?= $cType === 'deposit_hold' ? 'selected' : '' ?>>HĐ 1: Đặt cọc giữ chỗ (5 ngày - 50% cọc)</option>
                            <option value="official_lease" <?= $cType === 'official_lease' ? 'selected' : '' ?>>HĐ 2: Thuê chính thức (6 tháng - 100% cọc)</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Trạng thái hợp đồng <span class="text-danger">*</span></label>
                        <select class="form-select" name="status">
                            <?php foreach ($statuses as $value => $label): ?>
                                <option value="<?= $value ?>" <?= $contract['status'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Phòng trọ <span class="text-danger">*</span></label>
                        <select class="form-select" name="room_id" id="edit_room_id" required onchange="onEditRoomSelect(this)">
                            <?php foreach ($rooms as $r): ?>
                                <option value="<?= (int)$r['id'] ?>"
                                        data-room-name="<?= h($r['room_number']) ?>"
                                        data-price="<?= (float)$r['price'] ?>"
                                        data-service-fee="<?= (float)($r['total_service_fee'] ?? 0) ?>"
                                        data-electric-rate="<?= (float)($r['electricity_rate'] ?? 3500) ?>"
                                        data-water-rate="<?= (float)($r['water_rate'] ?? 20000) ?>"
                                        data-services='<?= htmlspecialchars(json_encode($r['services'] ?? [], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>'
                                        <?= $r['id'] == $contract['room_id'] ? 'selected' : '' ?>>
                                    <?= h($r['room_number']) ?> - <?= number_format((float)$r['price'], 0, ',', '.') ?> đ (<?= $r['status'] === 'available' ? 'Đang trống' : 'Đang thuê' ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Khách thuê <span class="text-danger">*</span></label>
                        <select class="form-select" name="tenant_id" required>
                            <?php foreach ($tenants as $t): ?>
                                <option value="<?= (int)$t['id'] ?>" <?= $t['id'] == $contract['tenant_id'] ? 'selected' : '' ?>>
                                    <?= h($t['full_name']) ?> - <?= h($t['phone']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Ngày lập / đặt cọc <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="deposit_date" value="<?= h($depositDate) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Ngày bắt đầu <span class="text-danger">*</span></label>
                        <input type="date" class="form-control fw-bold text-teal-main" id="edit_start_date" name="start_date" value="<?= h($contract['start_date']) ?>" required onchange="onEditTypeChange()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Ngày kết thúc <span class="text-danger">*</span></label>
                        <input type="date" class="form-control fw-bold text-danger" id="edit_end_date" name="end_date" value="<?= h($contract['end_date']) ?>" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">
                            Giá thuê/tháng (VNĐ) <span class="text-danger">*</span>
                            <span class="badge bg-light text-secondary border rounded-pill ms-1" style="font-size:0.7rem;"><i class="fa-solid fa-lock text-warning me-1"></i>Mặc định theo phòng</span>
                        </label>
                        <div class="input-group">
                            <input type="number" min="0" step="50000" class="form-control bg-light fw-bold" id="edit_monthly_rent" name="monthly_rent" value="<?= (float)$monthlyRent ?>" readonly required oninput="calcDeposit()">
                            <span class="input-group-text bg-light text-muted small"><i class="fa-solid fa-lock text-warning"></i></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">
                            Tiền cọc thực tế
                            <span class="badge bg-teal-main text-white rounded-pill ms-1" id="edit_dep_percent_badge"><?= (int)($contract['deposit_paid_percent'] ?? ($cType === 'deposit_hold' ? 50 : 100)) ?>%</span>
                        </label>
                        <input type="hidden" name="deposit_paid_percent" id="edit_deposit_paid_percent" value="<?= (int)($contract['deposit_paid_percent'] ?? ($cType === 'deposit_hold' ? 50 : 100)) ?>">
                        <input type="number" min="0" class="form-control bg-light fw-bold text-teal-main" id="edit_deposit" name="deposit_amount" value="<?= (float)$contract['deposit_amount'] ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Hình thức ký</label>
                        <input type="text" class="form-control bg-light fw-bold text-secondary" value="Ký Giấy A4 (Có Scan)" readonly>
                        <input type="hidden" name="signature_type" value="paper">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">
                            Giá điện (đ/kWh)
                            <span class="badge bg-light text-secondary border rounded-pill ms-1" style="font-size:0.7rem;"><i class="fa-solid fa-lock text-warning me-1"></i>Mặc định</span>
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control bg-light fw-bold text-secondary" id="edit_electricity_rate" name="electricity_rate" value="<?= (float)($contract['electricity_rate'] ?? 3500) ?>" readonly>
                            <span class="input-group-text bg-light text-muted small"><i class="fa-solid fa-bolt text-warning"></i></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">
                            Giá nước (đ/khối)
                            <span class="badge bg-light text-secondary border rounded-pill ms-1" style="font-size:0.7rem;"><i class="fa-solid fa-lock text-warning me-1"></i>Mặc định</span>
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control bg-light fw-bold text-secondary" id="edit_water_rate" name="water_rate" value="<?= (float)($contract['water_rate'] ?? 20000) ?>" readonly>
                            <span class="input-group-text bg-light text-muted small"><i class="fa-solid fa-droplet text-primary"></i></span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">
                            Phí dịch vụ (đ/tháng)
                            <span class="badge bg-teal-subtle text-teal-main border rounded-pill ms-1" style="font-size:0.7rem;"><i class="fa-solid fa-calculator me-1"></i>Tự động tính</span>
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control bg-light fw-bold text-teal-main" id="edit_service_fee" name="service_fee" value="<?= (float)($contract['service_fee'] ?? 150000) ?>" readonly>
                            <span class="input-group-text bg-light text-muted small" title="Tự động tính từ dịch vụ phòng"><i class="fa-solid fa-calculator text-teal-main"></i></span>
                        </div>
                    </div>
                </div>

                <!-- BẢNG HIỂN THỊ DỊCH VỤ CỦA PHÒNG -->
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <div class="rounded-4 p-3 shadow-xs" style="background:#f8fafc; border:1.5px solid #cbd5e1;">
                            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge rounded-pill bg-teal-main text-white px-2.5 py-1.5 fw-bold" style="font-size:0.82rem;">
                                        <i class="fa-solid fa-layer-group me-1"></i> Dịch vụ đã chọn của phòng:
                                    </span>
                                    <span id="edit_room_badge" class="badge rounded-pill bg-teal-main text-white px-2.5 py-1.5 small fw-bold">
                                        Phòng <?= h($contract['room_number'] ?? '') ?>
                                    </span>
                                </div>
                                <div class="fw-bold d-flex align-items-center gap-2" style="color:#0f766e; font-size:0.92rem;">
                                    <span>Tổng tiền dịch vụ:</span>
                                    <span id="edit_display_total_service_fee" class="badge bg-white text-teal-main fs-6 px-3 py-1.5 fw-black border border-teal shadow-xs">
                                        <?= number_format((float)($contract['service_fee'] ?? 150000), 0, ',', '.') ?> đ / tháng
                                    </span>
                                </div>
                            </div>
                            <div class="table-responsive rounded-3 bg-white border shadow-xs mb-2">
                                <table class="table table-sm table-hover align-middle mb-0" style="font-size:0.86rem;">
                                    <thead class="table-light border-bottom">
                                        <tr>
                                            <th class="ps-3 py-2 text-secondary" style="width: 50px;">STT</th>
                                            <th class="py-2 text-secondary">Tên dịch vụ</th>
                                            <th class="py-2 text-secondary text-end" style="width: 170px;">Đơn giá</th>
                                            <th class="py-2 text-secondary text-center pe-3" style="width: 140px;">Đơn vị tính</th>
                                        </tr>
                                    </thead>
                                    <tbody id="edit_room_services_list_tbody">
                                        <!-- Render qua JS khi chọn phòng -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-1">
                                <small class="text-muted d-flex align-items-center gap-1" style="font-size:0.78rem;">
                                    <i class="fa-solid fa-lock text-warning"></i>
                                    Các giá tiền để mặc định theo phòng đã chỉnh sửa và chỉ thay đổi khi bên phòng thay đổi.
                                </small>
                                <a href="index.php?route=rooms" target="_blank" class="text-decoration-none fw-bold small text-teal-main d-inline-flex align-items-center gap-1">
                                    <span>Chỉnh sửa dịch vụ &amp; giá tiền tại trang Quản lý phòng</span>
                                    <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:0.75rem;"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label class="form-label small fw-bold">Ghi chú bổ sung</label>
                        <input type="text" class="form-control" name="notes" value="<?= h($contract['notes'] ?? '') ?>" placeholder="Ghi chú thỏa thuận riêng...">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="index.php?route=contracts" class="btn btn-light rounded-pill px-4 fw-bold">Hủy</a>
                    <button type="submit" class="btn-teal-custom"><i class="fa-solid fa-check me-2"></i> Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
    <script>
    function onEditRoomSelect(sel) {
        const opt = sel.options[sel.selectedIndex];
        if (!opt || !opt.value) return;

        const roomName = opt.dataset.roomName || opt.text.split(' - ')[0] || '';
        const price = parseFloat(opt.dataset.price || 0);
        const serviceFee = parseFloat(opt.dataset.serviceFee || 0);
        const electricRate = parseFloat(opt.dataset.electricRate || 3500);
        const waterRate = parseFloat(opt.dataset.waterRate || 20000);

        document.getElementById('edit_monthly_rent').value = price;
        document.getElementById('edit_service_fee').value = serviceFee;
        if (document.getElementById('edit_electricity_rate')) {
            document.getElementById('edit_electricity_rate').value = electricRate;
        }
        if (document.getElementById('edit_water_rate')) {
            document.getElementById('edit_water_rate').value = waterRate;
        }

        let services = [];
        try {
            services = JSON.parse(opt.dataset.services || '[]');
        } catch (e) {
            services = [];
        }

        renderEditRoomServices(roomName, services, serviceFee);
        calcDeposit();
    }

    function renderEditRoomServices(roomName, services, totalFee) {
        const badge = document.getElementById('edit_room_badge');
        const totalDisplay = document.getElementById('edit_display_total_service_fee');
        const tbody = document.getElementById('edit_room_services_list_tbody');

        if (badge) {
            badge.textContent = 'Phòng ' + roomName;
        }
        if (totalDisplay) {
            totalDisplay.textContent = formatVnd(totalFee) + ' / tháng';
        }
        if (!tbody) return;

        if (!services || services.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-muted py-3">
                        <i class="fa-solid fa-circle-info text-teal-main me-1"></i> Phòng này chưa có dịch vụ đăng ký riêng (Phí dịch vụ: 0 đ).
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        services.forEach((s, idx) => {
            html += `
                <tr>
                    <td class="ps-3 py-2 text-muted fw-bold">${idx + 1}</td>
                    <td class="py-2 fw-semibold text-dark">
                        <i class="fa-solid fa-circle-check text-teal-main me-1.5"></i> ${s.service_name}
                    </td>
                    <td class="py-2 text-end fw-bold text-teal-main">
                        ${formatVnd(parseFloat(s.unit_price || 0))}
                    </td>
                    <td class="py-2 text-center pe-3 text-secondary">
                        <span class="badge bg-light text-secondary border px-2 py-1 rounded-pill">${s.unit || 'Tháng'}</span>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }

    window.addEventListener('DOMContentLoaded', () => {
        const sel = document.getElementById('edit_room_id');
        if (sel) {
            onEditRoomSelect(sel);
        }
    });

    function onEditTypeChange() {
        const type = document.getElementById('edit_contract_type').value;
        const startVal = document.getElementById('edit_start_date').value || new Date().toISOString().split('T')[0];
        const start = new Date(startVal);

        if (type === 'deposit_hold') {
            const end = new Date(start);
            end.setDate(end.getDate() + 5);
            document.getElementById('edit_end_date').value = end.toISOString().split('T')[0];
            document.getElementById('edit_deposit_paid_percent').value = 50;
            document.getElementById('edit_dep_percent_badge').textContent = '50%';
        } else {
            const end = new Date(start);
            end.setMonth(end.getMonth() + 6);
            document.getElementById('edit_end_date').value = end.toISOString().split('T')[0];
            document.getElementById('edit_deposit_paid_percent').value = 100;
            document.getElementById('edit_dep_percent_badge').textContent = '100%';
        }
        calcDeposit();
    }

    function calcDeposit() {
        const rent = parseFloat(document.getElementById('edit_monthly_rent').value || 0);
        const type = document.getElementById('edit_contract_type').value;
        if (type === 'deposit_hold') {
            document.getElementById('edit_deposit').value = Math.round(rent * 0.5);
        } else {
            document.getElementById('edit_deposit').value = rent;
        }
    }
    </script>

    <!-- MODAL DOI MAT KHAU -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light rounded-top-4">
                    <h5 class="modal-title fw-bold text-teal-main"><i class="fa-solid fa-key me-2 text-warning"></i> Đổi Mật Khẩu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="index.php?route=dashboard">
                    <input type="hidden" name="action" value="change_password">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Mật khẩu hiện tại</label>
                            <input type="password" name="old_password" class="form-control rounded-3" placeholder="Nhập mật khẩu hiện tại" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Mật khẩu mới</label>
                            <input type="password" name="new_password" class="form-control rounded-3" placeholder="Nhập mật khẩu mới" required minlength="4">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Xác nhận mật khẩu mới</label>
                            <input type="password" name="confirm_password" class="form-control rounded-3" placeholder="Nhập lại mật khẩu mới" required minlength="4">
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-teal-custom px-4" style="background:#308f86;color:#fff;border-radius:50px;"><i class="fa-solid fa-check me-1"></i> Lưu thay đổi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL THONG TIN TAI KHOAN -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-light rounded-top-4">
                    <h5 class="modal-title fw-bold text-teal-main"><i class="fa-solid fa-user-shield me-2"></i> Thông tin Tài khoản</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold fs-4" style="width:60px; height:60px; background:#308f86;">AS</div>
                        <div>
                            <h5 class="fw-bold mb-0"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Quản trị viên Hệ thống') ?></h5>
                            <span class="badge bg-success rounded-pill text-uppercase"><?= htmlspecialchars($_SESSION['role'] ?? 'admin') ?></span>
                        </div>
                    </div>
                    <ul class="list-group list-group-flush border rounded-3">
                        <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Tên đăng nhập:</span> <strong><?= htmlspecialchars($_SESSION['username'] ?? 'admin01') ?></strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Email:</span> <strong><?= htmlspecialchars($_SESSION['email'] ?? 'admin@nhatro.com') ?></strong></li>
                        <li class="list-group-item d-flex justify-content-between"><span class="text-muted">Số điện thoại:</span> <strong><?= htmlspecialchars($_SESSION['phone'] ?? '0901234567') ?></strong></li>
                    </ul>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                    <a href="index.php?route=auth/logout" class="btn btn-danger rounded-pill px-4"><i class="fa-solid fa-right-from-bracket me-1"></i> Đăng xuất</a>
                </div>
            </div>
        </div>
    </div>
<?php require_once dirname(__DIR__) . '/components/toast.php'; ?>
</body>
</html>
