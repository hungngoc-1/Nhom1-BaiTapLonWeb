<?php
/**
 * BẢNG 1: QUẢN LÝ TIỀN CỌC & TIỀN PHÒNG THÁNG ĐẦU (HIỂN THỊ KIỂU TRẠNG THÁI)
 * Variables: $depositContracts, $depositStats, $filters, $rooms, $csrf, $returnQuery
 */
?>
<div class="table-deposit-wrapper">
    <?php 
    $overdueUnnotified = array_filter($depositContracts, fn($x) => !empty($x['is_first_month_overdue']) && empty($x['unpaid_notice_sent']));
    if (!empty($overdueUnnotified)): 
    ?>
        <div class="alert alert-danger rounded-4 p-3 shadow-sm d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 border-2 border-danger-subtle no-print">
            <div class="fw-bold text-danger">
                <i class="fa-solid fa-triangle-exclamation fs-5 me-2"></i>
                CẢNH BÁO QUÁ HẠN: Có <strong><?= count($overdueUnnotified) ?> phòng</strong> chưa thanh toán tiền phòng tháng đầu quá 5 ngày quy định (Đã sang ngày thứ 6+).
                Vui lòng bấm nút <strong>"Báo sang HĐ"</strong> để gửi lệnh sang trang Hợp đồng xác minh lý do (hết hạn HĐ hay bỏ phòng trước hạn)!
            </div>
        </div>
    <?php endif; ?>

    <!-- 4 THẺ THỐNG KÊ NHANH CHO CỌC & THÁNG ĐẦU -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="p-3 rounded-4 bg-white border shadow-sm h-100 d-flex flex-column justify-content-between" style="border-left: 4px solid #308f86 !important;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-bold text-teal-main small text-uppercase"><i class="fa-solid fa-shield-halved me-1"></i> Tiền cọc đang giữ</span>
                    <span class="badge rounded-pill bg-teal-subtle text-teal-main fw-bold small"><?= $depositStats['total_contracts'] ?> phòng</span>
                </div>
                <div class="fw-black text-dark fs-5 my-1" style="font-weight:900"><?= money($depositStats['total_deposit_held']) ?></div>
                <div class="small text-muted">Quỹ cọc bảo đảm hợp đồng</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="p-3 rounded-4 bg-white border shadow-sm h-100 d-flex flex-column justify-content-between" style="border-left: 4px solid #16a34a !important;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-bold text-success small text-uppercase"><i class="fa-solid fa-circle-check me-1"></i> Hoàn thành cọc 100%</span>
                    <span class="badge rounded-pill bg-success-subtle text-success fw-bold small"><?= $depositStats['count_deposit_100'] ?> phòng</span>
                </div>
                <div class="fw-black text-success fs-5 my-1" style="font-weight:900"><?= $depositStats['count_deposit_100'] ?> / <?= $depositStats['total_contracts'] ?></div>
                <div class="small text-muted">Không cộng thêm cọc vào HĐ tháng</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="p-3 rounded-4 bg-white border shadow-sm h-100 d-flex flex-column justify-content-between" style="border-left: 4px solid #f59e0b !important;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-bold text-warning-emphasis small text-uppercase"><i class="fa-solid fa-clock-rotate-left me-1"></i> Mới cọc 50%</span>
                    <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis fw-bold small"><?= $depositStats['count_deposit_50'] ?> phòng</span>
                </div>
                <div class="fw-black fs-5 my-1" style="font-weight:900;color:#b45309;"><?= $depositStats['count_deposit_50'] ?> phòng</div>
                <div class="small text-muted">Sẽ tự cộng nợ 50% cọc vào HĐ tháng</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="p-3 rounded-4 bg-white border shadow-sm h-100 d-flex flex-column justify-content-between" style="border-left: 4px solid #0284c7 !important;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-bold text-primary small text-uppercase"><i class="fa-solid fa-calendar-check me-1"></i> Tiền phòng tháng đầu</span>
                    <span class="badge rounded-pill bg-primary-subtle text-primary fw-bold small"><?= $depositStats['count_first_month_paid'] ?> đã thu</span>
                </div>
                <div class="fw-black text-primary fs-5 my-1" style="font-weight:900"><?= money($depositStats['total_first_month_paid']) ?></div>
                <div class="small text-muted">Chưa thu: <strong class="text-danger"><?= money($depositStats['total_first_month_unpaid']) ?></strong> (<?= $depositStats['count_first_month_unpaid'] ?> phòng)</div>
            </div>
        </div>
    </div>

    <!-- BỘ LỌC CHO BẢNG 1 -->
    <form method="GET" action="index.php" class="row g-2 align-items-end mb-3 pb-3 border-bottom border-light no-print">
        <input type="hidden" name="route" value="invoices">
        <input type="hidden" name="subtab" value="deposit">
        <div class="col-12 col-md-4">
            <label class="form-label fw-bold text-teal-main text-uppercase mb-1" style="font-size:.72rem"><i class="fa-solid fa-magnifying-glass me-1"></i> Tìm phòng, khách, mã hợp đồng</label>
            <input type="text" name="keyword" class="form-control filter-control" placeholder="Gõ số phòng (A101), tên khách hoặc mã HĐ..." value="<?= h($filters['keyword']) ?>">
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label fw-bold text-muted text-uppercase mb-1" style="font-size:.72rem">Trạng thái Cọc</label>
            <select name="deposit_filter" class="form-select filter-control">
                <option value="">-- Tất cả trạng thái cọc --</option>
                <option value="completed" <?= ($filters['deposit_filter'] ?? '') === 'completed' ? 'selected' : '' ?>>Hoàn thành cọc (100%)</option>
                <option value="partial_50" <?= ($filters['deposit_filter'] ?? '') === 'partial_50' ? 'selected' : '' ?>>Đã cọc 50% (Còn nợ)</option>
                <option value="refunded" <?= ($filters['deposit_filter'] ?? '') === 'refunded' ? 'selected' : '' ?>>Hoàn cọc (Đã trả lại)</option>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label fw-bold text-muted text-uppercase mb-1" style="font-size:.72rem">Tiền phòng tháng đầu</label>
            <select name="first_month_filter" class="form-select filter-control">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="1" <?= (string)($filters['first_month_filter'] ?? '') === '1' ? 'selected' : '' ?>>Đã thu tiền phòng</option>
                <option value="0" <?= (string)($filters['first_month_filter'] ?? '') === '0' ? 'selected' : '' ?>>Chưa thu tiền phòng</option>
            </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn-teal-custom flex-fill justify-content-center"><i class="fa-solid fa-filter"></i> Lọc</button>
            <a href="index.php?route=invoices&subtab=deposit" class="btn-pill-white" title="Xóa lọc"><i class="fa-solid fa-rotate-left"></i></a>
        </div>
    </form>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="fw-bold text-dark" style="font-weight:900;font-size:1.02rem">
            <i class="fa-solid fa-list-check me-2 text-warning-emphasis"></i> Danh sách Theo Dõi Cọc &amp; Tiền Phòng Tháng Đầu (<?= count($depositContracts) ?> Hợp đồng)
        </div>
        <div class="text-muted small fw-bold">Bấm nút "Sửa cọc" hoặc "Cập nhật" để thay đổi trạng thái</div>
    </div>

    <!-- BẢNG DỮ LIỆU CỌC & THÁNG ĐẦU -->
    <div class="table-rounded-wrap table-responsive">
        <table class="table table-staymate align-middle mb-0">
            <thead>
                <tr>
                    <th style="width:110px">MÃ HĐ</th>
                    <th>PHÒNG &amp; KHÁCH THUÊ</th>
                    <th>NGÀY VÀO Ở</th>
                    <th class="text-end">TIỀN CỌC QUY ĐỊNH</th>
                    <th style="min-width:170px">TRẠNG THÁI CỌC</th>
                    <th class="text-end">TIỀN PHÒNG THÁNG ĐẦU</th>
                    <th style="min-width:160px">TRẠNG THÁI THU THÁNG ĐẦU</th>
                    <th class="text-end no-print" style="width:120px">THAO TÁC</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($depositContracts)): foreach ($depositContracts as $c): ?>
                <tr>
                    <td>
                        <span class="code-pill fw-bold"><?= h($c['contract_code']) ?></span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge-room-pill"><?= h($c['room_number']) ?></span>
                            <div>
                                <div class="fw-bold tenant-name" style="font-size:0.92rem"><?= h($c['tenant_name'] ?? 'Khách thuê') ?></div>
                                <div class="small text-muted"><i class="fa-solid fa-phone me-1 text-teal-main"></i><?= h($c['tenant_phone'] ?? 'Chưa có SĐT') ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge-period-pill"><i class="fa-regular fa-calendar text-teal-main"></i> <?= date('d/m/Y', strtotime($c['start_date'])) ?></span>
                    </td>
                    <td class="text-end fw-black" style="font-weight:900;font-size:0.95rem">
                        <?= money($c['deposit_amount']) ?>
                    </td>
                    <td>
                        <div class="d-flex flex-column gap-1">
                            <div>
                                <span class="badge rounded-pill <?= $c['deposit_badge_class'] ?> fw-bold px-2 py-1" style="font-size:0.82rem">
                                    <i class="<?= $c['deposit_icon'] ?> me-1"></i> <?= h($c['deposit_label']) ?>
                                </span>
                            </div>
                            <div class="small fw-bold text-muted" style="font-size:0.8rem">
                                <?= h($c['deposit_sub']) ?>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-teal rounded-pill px-2 py-1 fw-bold no-print mt-1 align-self-start" style="font-size:.74rem;"
                                    onclick="openDepositModal(<?= (int)$c['id'] ?>, <?= h(json_encode($c['room_number'])) ?>, <?= h(json_encode($c['tenant_name'] ?? '')) ?>, <?= (float)$c['deposit_amount'] ?>, '<?= h($c['deposit_key']) ?>')"
                                    title="Cập nhật trạng thái cọc">
                                <i class="fa-solid fa-pen-to-square me-1"></i> Sửa cọc
                            </button>
                        </div>
                    </td>
                    <td class="text-end fw-black text-dark" style="font-weight:900;font-size:0.95rem">
                        <?= money($c['first_month_rent']) ?>
                    </td>
                    <td>
                        <div class="d-flex flex-column gap-1">
                            <div>
                                <span class="badge rounded-pill <?= $c['first_month_badge_class'] ?> fw-bold px-2 py-1" style="font-size:0.82rem">
                                    <i class="<?= $c['first_month_icon'] ?> me-1"></i> <?= h($c['first_month_label']) ?>
                                </span>
                            </div>
                            <div class="small fw-semibold text-muted" style="font-size:0.78rem">
                                <?= h($c['first_month_note_text']) ?>
                            </div>

                            <div class="d-flex flex-wrap gap-1 mt-1 no-print">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1 fw-bold" style="font-size:.74rem;"
                                        onclick="openFirstMonthModal(<?= (int)$c['id'] ?>, <?= h(json_encode($c['room_number'])) ?>, <?= h(json_encode($c['tenant_name'] ?? '')) ?>, <?= (float)$c['first_month_rent'] ?>, <?= (int)$c['first_month_rent_paid'] ?>, <?= h(json_encode($c['first_month_paid_note'] ?? '')) ?>)"
                                        title="Cập nhật trạng thái thu tiền phòng tháng đầu">
                                    <i class="fa-solid fa-money-check me-1"></i> Cập nhật
                                </button>

                                <?php if ($c['first_month_rent_paid'] == 0 && !empty($c['is_first_month_overdue'])): ?>
                                    <?php if (empty($c['unpaid_notice_sent'])): ?>
                                        <form method="POST" action="index.php?route=invoices/send-verification" class="d-inline m-0" onsubmit="return confirm('Gửi yêu cầu xác minh lý do không đóng tiền phòng <?= h($c['room_number']) ?> sang bên Hợp đồng?');">
                                            <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                                            <input type="hidden" name="contract_id" value="<?= (int)$c['id'] ?>">
                                            <input type="hidden" name="subtab" value="deposit">
                                            <button type="submit" class="btn btn-sm btn-danger rounded-pill px-2 py-1 fw-bold" style="font-size:.74rem;" title="Khách chưa thanh toán từ ngày thứ 6 - Bấm để gửi lệnh sang trang Hợp đồng">
                                                <i class="fa-solid fa-paper-plane me-1"></i> Báo sang HĐ
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <?php if ($c['termination_verification_case'] === 'none'): ?>
                                            <a href="index.php?route=contracts" class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning fw-bold text-decoration-none py-1 px-2" style="font-size:.74rem;" title="Đã gửi sang Hợp đồng - Đang chờ xác nhận">
                                                <i class="fa-solid fa-clock-rotate-left me-1"></i> Đã báo sang HĐ &rarr;
                                            </a>
                                        <?php elseif ($c['termination_verification_case'] === 'expired_no_renew'): ?>
                                            <span class="badge rounded-pill bg-info-subtle text-info border border-info fw-bold py-1 px-2" style="font-size:.74rem;">
                                                <i class="fa-solid fa-check me-1"></i> Hết hạn HĐ &amp; Đã hoàn cọc
                                            </span>
                                        <?php elseif ($c['termination_verification_case'] === 'early_move_out'): ?>
                                            <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger fw-bold py-1 px-2" style="font-size:.74rem;">
                                                <i class="fa-solid fa-ban me-1"></i> Bỏ phòng &amp; Mất cọc
                                            </span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td class="text-end no-print">
                        <div class="d-inline-flex align-items-center gap-1">
                            <button type="button" class="btn-icon-circle btn-icon-edit" title="Sửa trạng thái cọc"
                                    onclick="openDepositModal(<?= (int)$c['id'] ?>, <?= h(json_encode($c['room_number'])) ?>, <?= h(json_encode($c['tenant_name'] ?? '')) ?>, <?= (float)$c['deposit_amount'] ?>, '<?= h($c['deposit_key']) ?>')">
                                <i class="fa-solid fa-shield-halved text-warning-emphasis"></i>
                            </button>
                            <button type="button" class="btn-icon-circle btn-icon-pay" title="Cập nhật thu tiền phòng tháng đầu"
                                    onclick="openFirstMonthModal(<?= (int)$c['id'] ?>, <?= h(json_encode($c['room_number'])) ?>, <?= h(json_encode($c['tenant_name'] ?? '')) ?>, <?= (float)$c['first_month_rent'] ?>, <?= (int)$c['first_month_rent_paid'] ?>, <?= h(json_encode($c['first_month_paid_note'] ?? '')) ?>)">
                                <i class="fa-solid fa-calendar-check text-success"></i>
                            </button>
                            <a class="btn-icon-circle btn-icon-view" title="Xem Hợp đồng" href="index.php?route=contracts"><i class="fa-solid fa-file-contract"></i></a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="fa-solid fa-folder-open fs-1 text-warning opacity-25 mb-3 d-block"></i>
                        <p class="fw-bold mb-0">Không tìm thấy hợp đồng nào phù hợp bộ lọc.</p>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
            <?php if (!empty($depositContracts)): ?>
            <tfoot>
                <tr style="background:#f8fafc">
                    <td colspan="3" class="fw-black text-end" style="font-weight:900">TỔNG TIỀN CỌC ĐANG GIỮ:</td>
                    <td class="text-end fw-black text-teal-main" style="font-weight:900;font-size:1rem"><?= money($depositStats['total_deposit_held']) ?></td>
                    <td colspan="2" class="fw-black text-end" style="font-weight:900">TIỀN PHÒNG THÁNG ĐẦU:</td>
                    <td colspan="2" class="small fw-bold">
                        Đã thu: <span class="text-success"><?= money($depositStats['total_first_month_paid']) ?></span> · Còn nợ: <span class="text-danger"><?= money($depositStats['total_first_month_unpaid']) ?></span>
                    </td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>
