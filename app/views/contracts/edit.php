<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayMate - Sửa Hợp đồng</title>
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
    <div class="container py-5" style="max-width: 760px;">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0" style="color: var(--primary-purple);">
                <i class="fa-solid fa-file-pen me-2"></i>Sửa Hợp đồng HD-<?= date('Y', strtotime($contract['created_at'])) ?>-<?= str_pad($contract['id'], 3, '0', STR_PAD_LEFT) ?>
            </h4>
            <a href="index.php?route=contracts" class="btn btn-outline-custom">← Quay lại danh sách</a>
        </div>

        <?php if (!empty($flash_error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($flash_error) ?></div>
        <?php endif; ?>

        <div class="table-card p-4">
            <form action="index.php?route=contracts/update" method="POST">
                <input type="hidden" name="id" value="<?= (int)$contract['id'] ?>">

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="fw-bold" style="font-size:0.8rem">Phòng trọ <span class="text-danger">*</span></label>
                        <select class="form-select" name="room_id" required>
                            <?php foreach ($rooms as $r): ?>
                                <option value="<?= (int)$r['id'] ?>" <?= $r['id'] == $contract['room_id'] ? 'selected' : '' ?>><?= htmlspecialchars($r['room_number']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="fw-bold" style="font-size:0.8rem">Khách thuê <span class="text-danger">*</span></label>
                        <select class="form-select" name="tenant_id" required>
                            <?php foreach ($tenants as $t): ?>
                                <option value="<?= (int)$t['id'] ?>" <?= $t['id'] == $contract['tenant_id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['full_name']) ?> - <?= htmlspecialchars($t['phone']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="fw-bold" style="font-size:0.8rem">Ngày bắt đầu <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="start_date" value="<?= htmlspecialchars($contract['start_date']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold" style="font-size:0.8rem">Ngày kết thúc <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="end_date" value="<?= htmlspecialchars($contract['end_date']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold" style="font-size:0.8rem">Tiền cọc (VNĐ) <span class="text-danger">*</span></label>
                        <input type="number" min="0" class="form-control" name="deposit_amount" value="<?= (float)$contract['deposit_amount'] ?>" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="fw-bold" style="font-size:0.8rem">Trạng thái hợp đồng</label>
                    <select class="form-select" name="status">
                        <?php foreach ($statuses as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $contract['status'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="index.php?route=contracts" class="btn btn-outline-custom">Hủy</a>
                    <button type="submit" class="btn btn-primary-custom"><i class="fa-solid fa-check me-1"></i> Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
