<?php
if (!function_exists('h')) {
    function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('money')) {
    function money($n) { return number_format((float)$n, 0, ',', '.') . ' đ'; }
}
$csrf = $_SESSION['csrf_token'] ?? '';
$b = $inv['breakdown'];
$isPaid = $inv['status'] === 'paid';
$stLabel = $isPaid ? 'ĐÃ THU TIỀN' : ($inv['is_overdue'] ? 'QUÁ HẠN' : 'CHƯA THU');
$stColor = $isPaid ? '#15803d' : ($inv['is_overdue'] ? '#b91c1c' : '#b45309');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn <?= h($inv['code']) ?> - StayMate</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --teal-main: #308f86; --teal-hover: #25756d; }
        body { background: #eef6f5; font-family: 'Nunito', sans-serif; font-size: .92rem; color: #334155; padding: 24px 12px 60px; }
        .toolbar { max-width: 820px; margin: 0 auto 18px; display: flex; flex-wrap: wrap; gap: 10px; justify-content: space-between; align-items: center; }
        .btn-pill { border-radius: 50px; font-weight: 800; padding: 10px 22px; border: 2px solid #e2e8f0; background: #fff; color: #334155; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,.05); transition: .2s; }
        .btn-pill:hover { border-color: var(--teal-main); color: var(--teal-main); transform: translateY(-2px); }
        .btn-teal { background: var(--teal-main); color: #fff; border-color: var(--teal-hover); box-shadow: 0 8px 20px rgba(48,143,134,.35); }
        .btn-teal:hover { background: var(--teal-hover); color: #fff; }
        .paper { max-width: 820px; margin: 0 auto; background: #fff; border-radius: 30px; border: 4px solid #fff; box-shadow: 0 20px 50px rgba(0,0,0,.1); padding: 40px 44px; position: relative; overflow: hidden; }
        .stamp { position: absolute; right: 36px; top: 120px; border: 4px solid; border-radius: 16px; padding: 6px 18px; font-weight: 900; font-size: 1.25rem; transform: rotate(-12deg); opacity: .85; letter-spacing: 1px; }
        .brand { display: flex; align-items: center; gap: 12px; }
        .brand i { color: var(--teal-main); font-size: 2.4rem; }
        .brand b { color: var(--teal-main); font-size: 1.5rem; font-weight: 900; line-height: 1; display: block; }
        .info-box { background: #f0fdfa; border: 2px solid #ccfbf1; border-radius: 20px; padding: 16px 20px; }
        .label-s { font-size: .72rem; text-transform: uppercase; font-weight: 800; color: #64748b; }
        table.inv { width: 100%; border-collapse: separate; border-spacing: 0; border: 2px solid #e2e8f0; border-radius: 20px; overflow: hidden; }
        table.inv th { background: #f0fdfa; color: var(--teal-main); font-size: .75rem; text-transform: uppercase; font-weight: 900; padding: 12px 14px; }
        table.inv td { padding: 12px 14px; border-top: 1px solid #f1f5f9; font-weight: 600; }
        table.inv tfoot td { background: #f0fdfa; font-weight: 900; font-size: 1.1rem; color: var(--teal-main); border-top: 2px solid #99f6e4; }
        .method-card { cursor: pointer; border: 2px solid #e2e8f0; border-radius: 16px; padding: 10px; text-align: center; font-weight: 800; font-size: .8rem; display: block; background: #fff; }
        input[type=radio]:checked + .method-card { border-color: var(--teal-main); background: #f0fdfa; color: var(--teal-main); }
        @media print { body { background: #fff; padding: 0; } .toolbar, .no-print { display: none !important; } .paper { box-shadow: none; border: none; padding: 10px; } }
    </style>
</head>
<body>

<div class="toolbar no-print">
    <a class="btn-pill" href="index.php?route=invoices&period=<?= urlencode($inv['month_year']) ?>"><i class="fa-solid fa-arrow-left"></i> Về danh sách hóa đơn</a>
    <div class="d-flex gap-2">
        <?php if (!$isPaid): ?>
            <button class="btn-pill btn-teal" data-bs-toggle="collapse" data-bs-target="#payBox"><i class="fa-solid fa-money-bill-wave"></i> Thu tiền</button>
        <?php endif; ?>
        <button class="btn-pill" onclick="window.print()"><i class="fa-solid fa-print"></i> In hóa đơn</button>
    </div>
</div>

<?php if ($flash_success): ?><div class="alert alert-success rounded-pill fw-bold no-print mx-auto" style="max-width:820px"><i class="fa-solid fa-circle-check me-2"></i><?= h($flash_success) ?></div><?php endif; ?>
<?php if ($flash_error): ?><div class="alert alert-danger rounded-pill fw-bold no-print mx-auto" style="max-width:820px"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= h($flash_error) ?></div><?php endif; ?>

<?php if (!$isPaid): ?>
<div class="collapse no-print mx-auto mb-3" id="payBox" style="max-width:820px">
    <form method="POST" action="index.php?route=invoices/pay" class="paper py-4" style="padding:24px 32px">
        <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
        <input type="hidden" name="id" value="<?= (int)$inv['id'] ?>">
        <input type="hidden" name="from" value="detail">
        <div class="fw-bold mb-3"><i class="fa-solid fa-money-bill-wave text-success me-2"></i>Xác nhận thu <span class="text-teal-main" style="color:var(--teal-main)"><?= money($inv['total_amount']) ?></span></div>
        <div class="row g-2 mb-3">
            <?php $first = true; foreach ($methods as $k => $lbl): ?>
                <div class="col-4">
                    <input type="radio" class="d-none" name="method" id="m_<?= h($k) ?>" value="<?= h($k) ?>" <?= $first ? 'checked' : '' ?>>
                    <label class="method-card" for="m_<?= h($k) ?>"><?= h($lbl) ?></label>
                </div>
            <?php $first = false; endforeach; ?>
        </div>
        <div class="row g-2 mb-3">
            <div class="col-6"><input type="date" name="paid_date" class="form-control rounded-pill" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required></div>
            <div class="col-6"><input type="text" name="note" class="form-control rounded-pill" maxlength="255" placeholder="Ghi chú / mã giao dịch"></div>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <span class="small fw-bold text-warning-emphasis"><i class="fa-solid fa-lock me-1"></i>Hóa đơn sẽ bị khóa sau khi xác nhận.</span>
            <button class="btn-pill btn-teal" type="submit"><i class="fa-solid fa-check"></i> Xác nhận đã thu</button>
        </div>
    </form>
</div>
<?php endif; ?>

<div class="paper">
    <div class="stamp" style="color:<?= $stColor ?>;border-color:<?= $stColor ?>"><?= $stLabel ?></div>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div class="brand">
            <i class="fa-solid fa-house-chimney"></i>
            <div><b>StayMate</b><span class="small text-muted fw-bold">Quản Lý Nhà Trọ</span></div>
        </div>
        <div class="text-end">
            <div class="label-s">Hóa đơn tiền phòng</div>
            <div style="font-weight:900;font-size:1.6rem;color:var(--teal-main)"><?= h($inv['code']) ?></div>
            <div class="small fw-bold text-muted">Kỳ <?= h($inv['month_year']) ?></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="info-box h-100">
                <div class="label-s">Khách thuê</div>
                <div class="fw-bold fs-6"><?= h($inv['tenant_name'] ?? '—') ?></div>
                <div class="small text-muted fw-bold"><i class="fa-solid fa-phone me-1"></i><?= h($inv['tenant_phone'] ?? '—') ?></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-box h-100">
                <div class="d-flex justify-content-between"><span class="label-s">Phòng</span><b>Phòng <?= h($inv['room_number']) ?></b></div>
                <div class="d-flex justify-content-between"><span class="label-s">Ngày lập</span><b><?= date('d/m/Y', strtotime($inv['created_at'])) ?></b></div>
                <div class="d-flex justify-content-between"><span class="label-s">Hạn thanh toán</span><b class="<?= $inv['is_overdue'] ? 'text-danger' : '' ?>"><?= date('d/m/Y', strtotime($inv['due_date'])) ?></b></div>
            </div>
        </div>
    </div>

    <?php if (isset($inv['electric_old'])): ?>
    <div class="row g-3 mb-4">
        <div class="col-6"><div class="info-box" style="background:#fffbeb;border-color:#fde68a"><div class="label-s"><i class="fa-solid fa-bolt text-warning"></i> Chỉ số điện</div><b><?= (int)$inv['electric_old'] ?> → <?= (int)$inv['electric_new'] ?></b> <span class="small text-muted fw-bold">(<?= max(0, $inv['electric_new'] - $inv['electric_old']) ?> kWh)</span></div></div>
        <div class="col-6"><div class="info-box" style="background:#f0f9ff;border-color:#bae6fd"><div class="label-s"><i class="fa-solid fa-droplet text-info"></i> Chỉ số nước</div><b><?= (int)$inv['water_old'] ?> → <?= (int)$inv['water_new'] ?></b> <span class="small text-muted fw-bold">(<?= max(0, $inv['water_new'] - $inv['water_old']) ?> m³)</span></div></div>
    </div>
    <?php endif; ?>

    <table class="inv mb-4">
        <thead><tr><th>#</th><th>Khoản mục</th><th class="text-center">Số lượng</th><th class="text-end">Đơn giá</th><th class="text-end">Thành tiền</th></tr></thead>
        <tbody>
        <?php foreach ($b['lines'] as $i => $l): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= h($l['label']) ?></td>
                <td class="text-center"><?= rtrim(rtrim(number_format($l['qty'], 2, ',', '.'), '0'), ',') ?> <?= h($l['unit']) ?></td>
                <td class="text-end"><?= money($l['price']) ?></td>
                <td class="text-end"><?= money($l['amount']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot><tr><td colspan="4" class="text-end">TỔNG CỘNG</td><td class="text-end"><?= money($inv['total_amount']) ?></td></tr></tfoot>
    </table>

    <div class="row g-3">
        <div class="col-md-7">
            <div class="info-box" style="background:<?= $isPaid ? '#f0fdf4' : '#fffbeb' ?>;border-color:<?= $isPaid ? '#bbf7d0' : '#fde68a' ?>">
                <?php if ($isPaid): ?>
                    <div class="label-s text-success"><i class="fa-solid fa-circle-check"></i> Đã thanh toán</div>
                    <div class="fw-bold"><?= $inv['paid_at'] ? date('d/m/Y', strtotime($inv['paid_at'])) : '' ?> · <?= h($methods[$inv['payment_method'] ?? ''] ?? '—') ?></div>
                    <?php if (!empty($inv['payment_note'])): ?><div class="small text-muted fw-bold">Ghi chú: <?= h($inv['payment_note']) ?></div><?php endif; ?>
                <?php else: ?>
                    <div class="label-s" style="color:#b45309"><i class="fa-regular fa-clock"></i> Chưa thanh toán</div>
                    <div class="fw-bold">Vui lòng thanh toán trước <?= date('d/m/Y', strtotime($inv['due_date'])) ?>.</div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-5 text-center small fw-bold text-muted pt-2">
            <div class="d-flex justify-content-around"><div>Người lập<br><br><br>.....................</div><div>Khách thuê<br><br><br>.....................</div></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php require_once dirname(__DIR__) . '/components/toast.php'; ?>
</body>
</html>
