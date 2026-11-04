<?php
/**
 * BẢNG 2: HÓA ĐƠN HÀNG THÁNG (TIỀN PHÒNG THÁNG SAU + ĐIỆN NƯỚC + DỊCH VỤ SỬ DỤNG)
 * Variables: $invoices, $stats, $filters, $periods, $rooms, $csrf, $returnQuery, $methods
 */
?>
<div class="table-monthly-wrapper">
    <!-- FORM BỘ LỌC CHO HÓA ĐƠN HÀNG THÁNG -->
    <form method="GET" action="index.php" class="row g-3 align-items-end mb-4 pb-3 border-bottom border-light no-print">
        <input type="hidden" name="route" value="invoices">
        <input type="hidden" name="subtab" value="monthly">
        <div class="col-12 col-lg-3">
            <label class="form-label fw-bold text-teal-main text-uppercase mb-1" style="font-size:.75rem"><i class="fa-solid fa-magnifying-glass me-1"></i> Tìm kiếm</label>
            <input type="text" name="keyword" class="form-control filter-control" placeholder="Mã HD, số phòng, tên khách..." value="<?= h($filters['keyword']) ?>">
        </div>
        <div class="col-6 col-lg-2">
            <label class="form-label fw-bold text-muted text-uppercase mb-1" style="font-size:.75rem">Kỳ</label>
            <select name="period" class="form-select filter-control">
                <option value="all" <?= $filters['period'] === 'all' ? 'selected' : '' ?>>-- Tất cả các kỳ --</option>
                <?php foreach ($periods as $per): ?><option value="<?= h($per) ?>" <?= $filters['period'] === $per ? 'selected' : '' ?>>Kỳ <?= h($per) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-lg-2">
            <label class="form-label fw-bold text-muted text-uppercase mb-1" style="font-size:.75rem">Phòng</label>
            <select name="room_id" class="form-select filter-control">
                <option value="">-- Tất cả phòng --</option>
                <?php foreach ($rooms as $rm): ?><option value="<?= (int)$rm['id'] ?>" <?= (string)$filters['room_id'] === (string)$rm['id'] ? 'selected' : '' ?>>Phòng <?= h($rm['room_number']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-lg-2">
            <label class="form-label fw-bold text-muted text-uppercase mb-1" style="font-size:.75rem">Trạng thái HĐ</label>
            <select name="status" class="form-select filter-control">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="paid" <?= $filters['status'] === 'paid' ? 'selected' : '' ?>>Đã thanh toán (Đã thu)</option>
                <option value="unpaid" <?= $filters['status'] === 'unpaid' ? 'selected' : '' ?>>Chưa thanh toán (Chưa thu)</option>
                <option value="recorded" <?= $filters['status'] === 'recorded' ? 'selected' : '' ?>>Đã chốt (Đã chốt ĐN)</option>
                <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Chưa chốt (Chưa chốt ĐN)</option>
                <option value="overdue" <?= $filters['status'] === 'overdue' ? 'selected' : '' ?>>Quá hạn</option>
            </select>
        </div>
        <div class="col-6 col-lg-3 d-flex gap-2">
            <button type="submit" class="btn-teal-custom flex-fill justify-content-center"><i class="fa-solid fa-filter"></i> Lọc</button>
            <a href="index.php?route=invoices&period=all&subtab=monthly" class="btn-pill-white" title="Xóa bộ lọc"><i class="fa-solid fa-rotate-left"></i></a>
        </div>
    </form>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="fw-bold text-dark" style="font-weight:900;font-size:1.05rem">
            <i class="fa-solid fa-file-invoice-dollar me-2 text-teal-main"></i> Bảng Hóa Đơn Định Kỳ Hàng Tháng (<?= count($invoices) ?> hóa đơn)
        </div>
        <div class="text-muted small fw-bold">Tiền phòng tháng này/sau + Tiền điện nước tiêu thụ + Dịch vụ tòa nhà</div>
    </div>

    <div class="table-rounded-wrap table-responsive">
        <table class="table table-staymate align-middle mb-0">
            <thead>
                <tr>
                    <th>MÃ HĐ</th>
                    <th>PHÒNG &amp; KHÁCH</th>
                    <th>KỲ</th>
                    <th class="text-end">TIỀN PHÒNG</th>
                    <th class="text-end">ĐIỆN</th>
                    <th class="text-end">NƯỚC</th>
                    <th class="text-end">DỊCH VỤ</th>
                    <th class="text-end">TỔNG CỘNG</th>
                    <th>HẠN TT</th>
                    <th>TRẠNG THÁI</th>
                    <th class="text-end no-print">THAO TÁC</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($invoices)): foreach ($invoices as $inv):
                $b = $inv['breakdown'];
                $eUsed = max(0, (int)($inv['electric_new'] ?? 0) - (int)($inv['electric_old'] ?? 0));
                $wUsed = max(0, (int)($inv['water_new'] ?? 0) - (int)($inv['water_old'] ?? 0));
            ?>
                <tr>
                    <td><a class="code-pill" href="index.php?route=invoices/detail&id=<?= (int)$inv['id'] ?>"><?= h($inv['code']) ?></a></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge-room-pill"><?= h($inv['room_number']) ?></span>
                            <div>
                                <div class="fw-bold tenant-name" style="font-size:0.92rem"><?= h($inv['tenant_name'] ?? 'Khách thuê') ?></div>
                                <div class="small mt-1">
                                    <span class="badge rounded-pill <?= $inv['deposit_badge_class'] ?> fw-bold px-2 py-0" style="font-size:0.72rem;">
                                        <i class="<?= $inv['deposit_icon'] ?> me-1"></i> Cọc: <?= h($inv['deposit_label']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge-period-pill"><i class="fa-regular fa-calendar text-teal-main"></i><?= h($inv['month_year']) ?></span></td>
                    <td class="text-end fw-bold"><?= money($b['room']) ?></td>
                    <td class="text-end fw-bold"><?= money($b['elec']) ?><div class="sub-note"><?= $eUsed ?> kWh</div></td>
                    <td class="text-end fw-bold"><?= money($b['water']) ?><div class="sub-note"><?= $wUsed ?> m³</div></td>
                    <td class="text-end fw-bold"><?= money($b['other']) ?></td>
                    <td class="text-end">
                        <div style="font-weight:900;color:var(--teal-main);font-size:1.02rem;"><?= money($inv['total_amount']) ?></div>
                        <?php if (!empty($inv['deposit_remaining_due']) && $inv['deposit_remaining_due'] > 0): ?>
                            <div class="small fw-bold text-warning-emphasis" style="font-size:0.75rem;" title="Bao gồm số tiền cọc 50% còn thiếu">+<?= money($inv['deposit_remaining_due']) ?> cọc 50%</div>
                        <?php elseif ($inv['deposit_key'] === 'refunded'): ?>
                            <div class="small fw-bold text-secondary" style="font-size:0.74rem;">(Đã hoàn cọc)</div>
                        <?php elseif ($inv['deposit_key'] === 'forfeited'): ?>
                            <div class="small fw-bold text-danger" style="font-size:0.74rem;">(Mất cọc - Không hoàn cọc)</div>
                        <?php else: ?>
                            <div class="small fw-semibold text-muted" style="font-size:0.74rem;">(Đã đủ 100% cọc)</div>
                        <?php endif; ?>
                    </td>
                    <td><span class="<?= $inv['is_overdue'] ? 'text-danger' : '' ?> fw-bold"><?= date('d/m/Y', strtotime($inv['due_date'])) ?></span></td>
                    <td>
                        <?php if ($inv['status'] === 'paid'): ?>
                            <span class="st-pill st-paid"><i class="fa-solid fa-circle-check"></i> Đã thu</span>
                            <div class="sub-note mt-1"><?= $inv['paid_at'] ? date('d/m/Y', strtotime($inv['paid_at'])) : '' ?> · <?= h($methods[$inv['payment_method'] ?? ''] ?? '') ?></div>
                        <?php elseif ($inv['is_overdue']): ?>
                            <span class="st-pill st-overdue"><i class="fa-solid fa-triangle-exclamation"></i> Quá hạn</span>
                        <?php else: ?>
                            <span class="st-pill st-unpaid"><i class="fa-regular fa-clock"></i> Chưa thu</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end no-print">
                        <div class="d-inline-flex align-items-center gap-1">
                            <a class="btn-icon-circle btn-icon-view" title="Xem chi tiết & In hóa đơn" href="index.php?route=invoices/detail&id=<?= (int)$inv['id'] ?>"><i class="fa-solid fa-eye"></i></a>
                            <button type="button" class="btn-icon-circle btn-icon-edit" title="Chỉnh sửa thông tin hóa đơn"
                                    onclick="openEditInvoiceModal(<?= (int)$inv['id'] ?>, <?= h(json_encode($inv['code'])) ?>, <?= h(json_encode($inv['room_number'])) ?>, <?= h(json_encode($inv['tenant_name'] ?? '')) ?>, '<?= $inv['due_date'] ?>', '<?= $inv['status'] ?>', <?= h(json_encode($inv['payment_note'] ?? '')) ?>, '<?= h($inv['payment_method'] ?? '') ?>')">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <?php if ($inv['status'] === 'unpaid'): ?>
                                <button type="button" class="btn-icon-circle btn-icon-pay" title="Xác nhận thu tiền"
                                    onclick="openPayModal(<?= (int)$inv['id'] ?>, <?= h(json_encode($inv['code'])) ?>, <?= h(json_encode($inv['room_number'])) ?>, <?= h(json_encode($inv['tenant_name'] ?? '')) ?>, <?= (float)$inv['total_amount'] ?>)"><i class="fa-solid fa-money-bill-wave"></i></button>
                                <form method="POST" action="index.php?route=invoices/delete" class="d-inline" onsubmit="return confirm('Xóa hóa đơn <?= h($inv['code']) ?>? Có thể lập lại từ bản ghi điện nước.');">
                                    <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                                    <input type="hidden" name="id" value="<?= (int)$inv['id'] ?>">
                                    <input type="hidden" name="return_query" value="<?= h($returnQuery) ?>">
                                    <button type="submit" class="btn-icon-circle btn-icon-del" title="Xóa hóa đơn"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            <?php else: ?>
                                <span class="btn-icon-circle btn-icon-lock" title="Đã thu tiền"><i class="fa-solid fa-circle-check text-success"></i></span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="11" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fs-1 text-teal-main opacity-25 mb-3 d-block"></i>
                    <p class="fw-bold mb-2">Chưa có hóa đơn nào phù hợp bộ lọc.</p>
                    <button type="button" class="btn-teal-custom" data-bs-toggle="modal" data-bs-target="#createModal"><i class="fa-solid fa-plus"></i> Lập hóa đơn</button>
                </td></tr>
            <?php endif; ?>
            </tbody>
            <?php if (!empty($invoices)): ?>
            <tfoot>
                <tr style="background:#f8fafc">
                    <td colspan="7" class="text-end fw-black" style="font-weight:900">TỔNG CỘNG (<?= count($invoices) ?> hóa đơn):</td>
                    <td class="text-end" style="font-weight:900;color:var(--teal-main)"><?= money($stats['total']) ?></td>
                    <td colspan="3" class="small fw-bold">Đã thu <span class="text-success"><?= money($stats['paid']) ?></span> · Còn nợ <span class="text-danger"><?= money($stats['unpaid']) ?></span></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
