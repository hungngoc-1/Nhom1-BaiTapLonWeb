<?php
$monthlyRent = (float)($contract['monthly_rent'] ?? $contract['room_price'] ?? $contract['deposit_amount']);
$depositDate = !empty($contract['deposit_date']) ? $contract['deposit_date'] : $contract['start_date'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayMate - Sửa Hợp đồng <?= htmlspecialchars($contract['contract_code']) ?></title>
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
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23e6f4f1" fill-opacity="0.8" d="M0,160L48,170.7C96,181,192,203,288,197.3C384,192,480,160,576,149.3C672,139,768,149,864,170.7C960,192,1056,224,1152,213.3C1248,203,1344,149,1392,122.7L1440,96L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0C192,0,96,0,48,0L0,0Z"></path></svg>');
            background-position: top center;
            background-repeat: no-repeat;
            background-size: 100% auto;
        }
        .text-teal-main { color: var(--teal-main) !important; }
        .edit-card {
            background: rgba(255,255,255,0.96);
            border-radius: 30px;
            border: 4px solid #ffffff;
            padding: 28px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.08);
            max-width: 820px;
            margin: 0 auto;
        }
        .form-control, .form-select { border-radius: 14px; border: 1.5px solid #e2e8f0; padding: 9px 14px; font-weight: 600; font-size: 0.9rem; }
        .form-control:focus, .form-select:focus { border-color: var(--teal-main); box-shadow: 0 0 0 3px rgba(48, 143, 134, 0.15); }
        .btn-teal-custom { background: var(--teal-main); border-radius: 50px; font-weight: 800; color: white; padding: 10px 26px; border: 2px solid var(--teal-hover); box-shadow: 0 8px 20px rgba(48, 143, 134, 0.35); }
        .btn-teal-custom:hover { background: var(--teal-hover); color: white; }
        .btn-back-content { background: white; border: 3px solid #ffffff; color: #334155; font-weight: 800; font-size: 0.9rem; padding: 9px 24px; border-radius: 50px; text-decoration: none; display: inline-flex; align-items: center; box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4" style="max-width: 820px; margin: 0 auto;">
            <div>
                <h4 class="fw-bold mb-0 text-teal-main">
                    <i class="fa-solid fa-file-pen me-2"></i>Cập nhật Hợp đồng <?= htmlspecialchars($contract['contract_code']) ?>
                </h4>
                <div class="small text-muted fw-semibold">Chỉnh sửa thông tin hợp đồng thuê phòng</div>
            </div>
            <a href="index.php?route=contracts" class="btn-back-content"><i class="fa-solid fa-arrow-left me-2"></i>Quay lại danh sách</a>
        </div>

        <?php if (!empty($flash_error)): ?>
            <div class="alert alert-danger rounded-4 fw-bold mx-auto mb-3" style="max-width: 820px;"><?= htmlspecialchars($flash_error) ?></div>
        <?php endif; ?>

        <div class="edit-card">
            <form action="index.php?route=contracts/update" method="POST">
                <input type="hidden" name="id" value="<?= (int)$contract['id'] ?>">

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Phòng trọ <span class="text-danger">*</span></label>
                        <select class="form-select" name="room_id" required onchange="onEditRoomSelect(this)">
                            <?php foreach ($rooms as $r): ?>
                                <option value="<?= (int)$r['id'] ?>" data-price="<?= (float)$r['price'] ?>" <?= $r['id'] == $contract['room_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($r['room_number']) ?> - <?= number_format((float)$r['price'], 0, ',', '.') ?> đ
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Khách thuê <span class="text-danger">*</span></label>
                        <select class="form-select" name="tenant_id" required>
                            <?php foreach ($tenants as $t): ?>
                                <option value="<?= (int)$t['id'] ?>" <?= $t['id'] == $contract['tenant_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['full_name']) ?> - <?= htmlspecialchars($t['phone']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Ngày đặt cọc <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="deposit_date" value="<?= htmlspecialchars($depositDate) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Ngày bắt đầu <span class="text-danger">*</span></label>
                        <input type="date" class="form-control fw-bold text-teal-main" name="start_date" value="<?= htmlspecialchars($contract['start_date']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Ngày kết thúc <span class="text-danger">*</span></label>
                        <input type="date" class="form-control fw-bold text-danger" name="end_date" value="<?= htmlspecialchars($contract['end_date']) ?>" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Giá thuê/tháng (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" min="0" step="50000" class="form-control" id="edit_monthly_rent" name="monthly_rent" value="<?= (float)$monthlyRent ?>" required oninput="document.getElementById('edit_deposit').value=this.value">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Tiền cọc (= 1 tháng)</label>
                        <input type="number" min="0" class="form-control bg-light fw-bold text-teal-main" id="edit_deposit" name="deposit_amount" value="<?= (float)$contract['deposit_amount'] ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Hình thức ký</label>
                        <select class="form-select" name="signature_type">
                            <option value="online" <?= ($contract['signature_type'] ?? 'online') === 'online' ? 'selected' : '' ?>>Hợp đồng Điện tử (Online)</option>
                            <option value="paper" <?= ($contract['signature_type'] ?? '') === 'paper' ? 'selected' : '' ?>>Hợp đồng Ký giấy (Scan)</option>
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
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Trạng thái hợp đồng</label>
                        <select class="form-select" name="status">
                            <?php foreach ($statuses as $value => $label): ?>
                                <option value="<?= $value ?>" <?= $contract['status'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small fw-bold">Ghi chú bổ sung</label>
                        <input type="text" class="form-control" name="notes" value="<?= htmlspecialchars($contract['notes'] ?? '') ?>" placeholder="Ghi chú thỏa thuận riêng...">
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
            document.getElementById('edit_deposit').value = opt.dataset.price;
        }
    }
    </script>
</body>
</html>
