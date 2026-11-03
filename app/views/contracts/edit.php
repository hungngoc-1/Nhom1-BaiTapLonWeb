<?php
/**
 * TRANG CHỈNH SỬA HỢP ĐỒNG (EDIT CONTRACT)
 * Phụ trách: Hùng Văn Ngọc (Nhóm trưởng - MSSV: 224001738)
 */
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
$monthlyRent = (float)($contract['monthly_rent'] ?? $contract['room_price'] ?? $contract['deposit_amount']);
$depositDate = !empty($contract['deposit_date']) ? $contract['deposit_date'] : $contract['start_date'];
$cType = $contract['contract_type'] ?? 'official_lease';
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
    <nav class="navbar navbar-custom sticky-top">
        <div class="container-fluid px-4 px-xl-5 d-flex justify-content-between align-items-center">
            <a class="navbar-brand d-flex align-items-center gap-2 text-decoration-none" href="index.php?route=dashboard">
                <i class="fa-solid fa-house-chimney text-teal-main fs-4"></i>
                <div class="brand-text">StayMate</div>
            </a>
            <ul class="nav nav-pills flex-nowrap me-auto d-none d-lg-flex">
                <li class="nav-item"><a class="nav-link" href="index.php?route=overview"><i class="fa-solid fa-border-all"></i> Tổng quan</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=rooms"><i class="fa-solid fa-bed"></i> Phòng trọ</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=tenants"><i class="fa-solid fa-users"></i> Khách thuê</a></li>
                <li class="nav-item"><a class="nav-link active" href="index.php?route=contracts"><i class="fa-solid fa-file-signature"></i> Hợp đồng</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=utilities"><i class="fa-solid fa-bolt"></i> Điện Nước</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=showcase" target="_blank"><i class="fa-regular fa-eye"></i> Xem phòng khách</a></li>
            </ul>
            <div class="d-flex align-items-center gap-4">
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
                        <select class="form-select" name="room_id" required onchange="onEditRoomSelect(this)">
                            <?php foreach ($rooms as $r): ?>
                                <option value="<?= (int)$r['id'] ?>" data-price="<?= (float)$r['price'] ?>" <?= $r['id'] == $contract['room_id'] ? 'selected' : '' ?>>
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
                        <label class="form-label small fw-bold">Giá thuê/tháng (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" min="0" step="50000" class="form-control" id="edit_monthly_rent" name="monthly_rent" value="<?= (float)$monthlyRent ?>" required oninput="calcDeposit()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">
                            Tiền cọc thực tế
                            <span class="badge bg-teal-main text-white rounded-pill ms-1" id="edit_dep_percent_badge"><?= (int)($contract['deposit_paid_percent'] ?? ($cType === 'deposit_hold' ? 50 : 100)) ?>%</span>
                        </label>
                        <input type="hidden" name="deposit_paid_percent" id="edit_deposit_paid_percent" value="<?= (int)($contract['deposit_paid_percent'] ?? ($cType === 'deposit_hold' ? 50 : 100)) ?>">
                        <input type="number" min="0" class="form-control bg-light fw-bold text-teal-main" id="edit_deposit" name="deposit_amount" value="<?= (float)$contract['deposit_amount'] ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Hình thức ký</label>
                        <select class="form-select" name="signature_type">
                            <option value="paper" <?= ($contract['signature_type'] ?? '') === 'paper' ? 'selected' : '' ?>>Hợp đồng Ký giấy (A4)</option>
                            <option value="online" <?= ($contract['signature_type'] ?? 'online') === 'online' ? 'selected' : '' ?>>Hợp đồng Điện tử (Online)</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Giá điện (đ/kWh)</label>
                        <input type="number" class="form-control" name="electricity_rate" value="<?= (float)($contract['electricity_rate'] ?? 3500) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Giá nước (đ/khối)</label>
                        <input type="number" class="form-control" name="water_rate" value="<?= (float)($contract['water_rate'] ?? 20000) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Phí dịch vụ (đ/tháng)</label>
                        <input type="number" class="form-control" name="service_fee" value="<?= (float)($contract['service_fee'] ?? 150000) ?>">
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
        if (opt && opt.dataset.price) {
            document.getElementById('edit_monthly_rent').value = opt.dataset.price;
            calcDeposit();
        }
    }

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
</body>
</html>
