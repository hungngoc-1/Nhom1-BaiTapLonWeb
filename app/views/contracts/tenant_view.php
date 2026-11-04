<?php
/**
 * TRANG XEM HỢP ĐỒNG DÀNH CHO PHÂN QUYỀN KHÁCH THUÊ (TENANT PORTAL VIEW)
 * Phụ trách liên kết: Hùng Văn Ngọc (Nhóm trưởng) & Nguyễn Ánh Ngọc (Phân quyền khách thuê)
 */
if (!function_exists('h')) {
    function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayMate - Hợp Đồng Thuê Phòng Của Tôi</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --teal-main: #308f86;
            --teal-hover: #25726b;
            --teal-soft: #e6f4f2;
            --coral-accent: #f97316;
            --dark-card: #0f172a;
        }
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
        }
        .header-bar {
            background: #ffffff;
            border-bottom: 2px solid #e2e8f0;
            padding: 14px 24px;
        }
        .brand-text {
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--teal-main);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .card-custom {
            background: #ffffff;
            border-radius: 24px;
            border: 3px solid #ffffff;
            box-shadow: 0 10px 30px rgba(0,0,0,0.06);
            overflow: hidden;
        }
        .badge-c1 {
            background: #fef3c7;
            color: #92400e;
            border: 1.5px solid #fde68a;
            font-weight: 800;
            padding: 6px 14px;
            border-radius: 50px;
        }
        .badge-c2 {
            background: #dcfce7;
            color: #166534;
            border: 1.5px solid #bbf7d0;
            font-weight: 800;
            padding: 6px 14px;
            border-radius: 50px;
        }
        .api-info-box {
            background: #f0fdfa;
            border: 2px dashed #99f6e4;
            border-radius: 20px;
            padding: 18px;
        }
        .scan-preview-img {
            max-height: 520px;
            width: 100%;
            object-fit: contain;
            border-radius: 16px;
            background: #f1f5f9;
        }
    </style>
</head>
<body>

    <!-- Header bar -->
    <header class="header-bar sticky-top shadow-sm">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="index.php" class="brand-text">
                <i class="fa-solid fa-house-chimney"></i> StayMate
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="badge rounded-pill bg-light text-dark border px-3 py-2 fw-bold">
                    <i class="fa-solid fa-user-shield me-1 text-teal-main"></i> Cổng Khách Thuê
                </span>
                <a href="index.php?route=contracts" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold">
                    <i class="fa-solid fa-arrow-left me-1"></i> Về Quản lý
                </a>
            </div>
        </div>
    </header>

    <main class="container py-4">
        <?php if (!$contract): ?>
            <div class="card-custom p-5 text-center my-5">
                <div class="display-1 text-muted mb-3"><i class="fa-solid fa-folder-open"></i></div>
                <h4 class="fw-bold text-dark">Chưa có hợp đồng nào được liên kết</h4>
                <p class="text-muted">Bạn chưa có hợp đồng thuê phòng nào hoặc chưa được chỉ định hợp đồng trong hệ thống.</p>
                <div class="api-info-box max-w-600 mx-auto text-start mt-4">
                    <div class="fw-bold text-teal-main mb-2"><i class="fa-solid fa-circle-info me-1"></i> Dành cho bạn phụ trách phân quyền (Nguyễn Ánh Ngọc):</div>
                    <div class="small text-muted">Truyền tham số qua URL để kiểm tra:</div>
                    <code class="d-block bg-white p-2 rounded border mt-2">index.php?route=tenant/contract&id={ID_HOP_DONG}</code>
                    <code class="d-block bg-white p-2 rounded border mt-2">index.php?route=tenant/contract&tenant_id={ID_KHACH_THUE}</code>
                </div>
            </div>
        <?php else: ?>
            <?php
            $isC1 = ($contract['contract_type'] ?? '') === 'deposit_hold';
            $cTypeLabel = $isC1 ? 'HĐ 1: Đặt cọc giữ chỗ (Hiệu lực 5 ngày)' : 'HĐ 2: Thuê chính thức (Hiệu lực 6 tháng)';
            $rentFmt = number_format((float)$contract['monthly_rent'], 0, ',', '.') . ' đ';
            $depositFmt = number_format((float)$contract['deposit_amount'], 0, ',', '.') . ' đ';
            $status = $contract['status'] ?? 'active';
            ?>

            <!-- Top Welcome Banner -->
            <div class="card-custom p-4 mb-4" style="background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%); color: #fff;">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <span class="badge bg-white text-dark rounded-pill fw-bold px-3 py-1 mb-2">
                            MÃ HỢP ĐỒNG: <?= h($contract['contract_code']) ?>
                        </span>
                        <h2 class="fw-black mb-1">
                            <i class="fa-solid fa-file-contract me-2 text-warning"></i>
                            <?= $isC1 ? 'HỢP ĐỒNG ĐẶT CỌC GIỮ CHỖ (5 NGÀY)' : 'HỢP ĐỒNG THUÊ PHÒNG CHÍNH THỨC (6 THÁNG)' ?>
                        </h2>
                        <div class="small opacity-75">
                            Khách thuê: <strong><?= h($contract['full_name']) ?></strong> (SĐT: <?= h($contract['phone']) ?>) — Phòng trọ: <strong class="fs-6 text-warning">P.<?= h($contract['room_number']) ?></strong>
                        </div>
                    </div>
                    <div class="text-end">
                        <?php if ($status === 'active'): ?>
                            <span class="badge bg-success rounded-pill px-4 py-2 fs-6 fw-bold shadow"><i class="fa-solid fa-circle-check me-1"></i> Đang hiệu lực</span>
                        <?php elseif ($status === 'expired'): ?>
                            <span class="badge bg-danger rounded-pill px-4 py-2 fs-6 fw-bold shadow"><i class="fa-solid fa-triangle-exclamation me-1"></i> Đã hủy / Hết hạn</span>
                        <?php else: ?>
                            <span class="badge bg-secondary rounded-pill px-4 py-2 fs-6 fw-bold shadow"><i class="fa-solid fa-ban me-1"></i> Đã thanh lý</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Cột trái: Thông tin hợp đồng & Chi phí -->
                <div class="col-lg-6">
                    <div class="card-custom p-4 h-100">
                        <h5 class="fw-bold text-dark border-bottom pb-3 mb-3">
                            <i class="fa-solid fa-circle-info text-teal-main me-2"></i>Chi Tiết Hợp Đồng &amp; Khoản Thu
                        </h5>

                        <div class="row g-3">
                            <div class="col-6">
                                <label class="small text-muted fw-bold d-block">Loại hợp đồng</label>
                                <span class="<?= $isC1 ? 'badge-c1' : 'badge-c2' ?> d-inline-block mt-1">
                                    <?= $cTypeLabel ?>
                                </span>
                            </div>
                            <div class="col-6">
                                <label class="small text-muted fw-bold d-block">Số phòng trọ</label>
                                <div class="fs-5 fw-bold text-teal-main">Phòng <?= h($contract['room_number']) ?></div>
                            </div>

                            <div class="col-6">
                                <label class="small text-muted fw-bold d-block">Thời hạn hợp đồng</label>
                                <div class="fw-bold text-dark">
                                    <?= date('d/m/Y', strtotime($contract['start_date'])) ?> → <?= date('d/m/Y', strtotime($contract['end_date'])) ?>
                                </div>
                                <div class="small text-muted"><?= (int)($contract['days_left'] ?? 0) ?> ngày còn lại</div>
                            </div>
                            <div class="col-6">
                                <label class="small text-muted fw-bold d-block">Hình thức ký</label>
                                <div class="fw-bold text-dark">
                                    <?= ($contract['signature_type'] ?? '') === 'paper' ? '✍ Ký giấy trực tiếp (Có Scan)' : '💻 Chữ ký điện tử Online' ?>
                                </div>
                            </div>

                            <div class="col-6">
                                <label class="small text-muted fw-bold d-block">Giá thuê hàng tháng</label>
                                <div class="fs-5 fw-bold text-danger"><?= $rentFmt ?>/tháng</div>
                            </div>
                            <div class="col-6">
                                <label class="small text-muted fw-bold d-block">Tiền cọc giữ chỗ / bảo đảm</label>
                                <div class="fs-5 fw-bold text-teal-main"><?= $depositFmt ?></div>
                                <span class="badge bg-light text-dark border small">
                                    <?= (int)($contract['deposit_paid_percent'] ?? 100) ?>% tiền cọc
                                </span>
                            </div>

                            <!-- Thu tiền phòng tháng đầu đối với HĐ 2 -->
                            <?php if (!$isC1): ?>
                                <div class="col-12">
                                    <div class="p-3 rounded-4 bg-light border border-success">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-bold text-success">
                                                    <i class="fa-solid fa-receipt me-1"></i> Thu trước 01 tháng tiền phòng khi ký HĐ
                                                </div>
                                                <div class="small text-muted">Đã thu và cộng vào doanh thu hệ thống: <strong><?= $rentFmt ?></strong></div>
                                            </div>
                                            <span class="badge bg-success rounded-pill px-3 py-2 fw-bold">
                                                <i class="fa-solid fa-check me-1"></i> ĐÃ THANH TOÁN
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="col-12">
                                    <div class="alert alert-warning rounded-4 small fw-bold mb-0">
                                        <i class="fa-solid fa-clock me-1"></i>
                                        Hợp đồng đặt cọc 50% có thời hạn 5 ngày. Quý khách vui lòng chuyển tiếp sang Hợp đồng thuê chính thức (HĐ 2) trong 5 ngày để giữ phòng.
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Đơn giá dịch vụ -->
                            <div class="col-4">
                                <label class="small text-muted fw-bold d-block">Giá điện</label>
                                <div class="fw-bold text-dark"><?= number_format((float)($contract['electricity_rate'] ?? 3500), 0, ',', '.') ?> đ/kWh</div>
                            </div>
                            <div class="col-4">
                                <label class="small text-muted fw-bold d-block">Giá nước</label>
                                <div class="fw-bold text-dark"><?= number_format((float)($contract['water_rate'] ?? 20000), 0, ',', '.') ?> đ/khối</div>
                            </div>
                            <div class="col-4">
                                <label class="small text-muted fw-bold d-block">Phí dịch vụ</label>
                                <div class="fw-bold text-dark"><?= number_format((float)($contract['service_fee'] ?? 150000), 0, ',', '.') ?> đ/tháng</div>
                            </div>
                        </div>

                        <!-- Hộp thông tin tích hợp dành cho bạn phân quyền -->
                        <div class="api-info-box mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong class="text-teal-main small text-uppercase">
                                    <i class="fa-solid fa-link me-1"></i> Thông số tích hợp Phân quyền Khách thuê:
                                </strong>
                                <span class="badge bg-white text-muted border">REST API Ready</span>
                            </div>
                            <div class="row g-2 small">
                                <div class="col-sm-6">
                                    <span class="text-muted">Mã Khách Thuê (Tenant ID):</span>
                                    <strong class="text-dark bg-white px-2 py-0.5 rounded border"><?= (int)$contract['tenant_id'] ?></strong>
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted">Mã Hợp Đồng (Contract ID):</span>
                                    <strong class="text-dark bg-white px-2 py-0.5 rounded border"><?= (int)$contract['id'] ?></strong>
                                </div>
                                <div class="col-12 mt-2">
                                    <span class="text-muted d-block">API JSON lấy dữ liệu trực tiếp:</span>
                                    <code class="d-block bg-white p-2 rounded border mt-1 user-select-all">
                                        index.php?route=api/tenant/contract&tenant_id=<?= (int)$contract['tenant_id'] ?>
                                    </code>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Cột phải: Bản Scan Hợp Đồng Ký Trực Tiếp & Ký Online -->
                <div class="col-lg-6">
                    <div class="card-custom p-4 h-100">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                            <h5 class="fw-bold text-dark mb-0">
                                <i class="fa-solid fa-signature text-teal-main me-2"></i>Bản Scan Hợp Đồng Đã Ký
                            </h5>
                            <?php if (!empty($contract['scan_file'])): ?>
                                <a href="<?= h($contract['scan_file']) ?>" download class="btn btn-sm btn-outline-success rounded-pill fw-bold">
                                    <i class="fa-solid fa-download me-1"></i> Tải bản scan
                                </a>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($contract['scan_file'])): ?>
                            <?php
                            $ext = strtolower(pathinfo($contract['scan_file'], PATHINFO_EXTENSION));
                            ?>
                            <div class="text-center">
                                <?php if ($ext === 'pdf'): ?>
                                    <div class="ratio ratio-4x3 rounded-4 overflow-hidden border">
                                        <iframe src="<?= h($contract['scan_file']) ?>" class="w-100 h-100"></iframe>
                                    </div>
                                <?php else: ?>
                                    <a href="<?= h($contract['scan_file']) ?>" target="_blank" title="Bấm để phóng to">
                                        <img src="<?= h($contract['scan_file']) ?>" alt="Bản scan hợp đồng" class="scan-preview-img border shadow-sm">
                                    </a>
                                <?php endif; ?>
                                <div class="mt-2 text-muted small">
                                    <i class="fa-solid fa-circle-check text-success me-1"></i> Bản scan hợp đồng đã ký đã được xác thực bởi Bên Cho Thuê.
                                </div>
                            </div>
                        <?php elseif (!empty($contract['tenant_signature']) || !empty($contract['manager_signature'])): ?>
                            <div class="alert alert-info rounded-4 small mb-3">
                                <i class="fa-solid fa-pen-nib me-1"></i> Hợp đồng này được xác nhận bằng <strong>Chữ ký điện tử Online</strong>:
                            </div>
                            <div class="row g-3 text-center">
                                <div class="col-6">
                                    <label class="small text-muted fw-bold d-block mb-1">Chữ ký Bên Cho Thuê</label>
                                    <div class="p-2 border rounded-3 bg-light">
                                        <?php if (!empty($contract['manager_signature'])): ?>
                                            <img src="<?= h($contract['manager_signature']) ?>" alt="Chữ ký Bên A" style="max-height:80px; max-width:100%;">
                                        <?php else: ?>
                                            <span class="text-muted small">Đang chờ ký</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="small text-muted fw-bold d-block mb-1">Chữ ký Khách Thuê</label>
                                    <div class="p-2 border rounded-3 bg-light">
                                        <?php if (!empty($contract['tenant_signature'])): ?>
                                            <img src="<?= h($contract['tenant_signature']) ?>" alt="Chữ ký Bên B" style="max-height:80px; max-width:100%;">
                                        <?php else: ?>
                                            <span class="text-muted small">Đang chờ ký</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="p-5 text-center bg-light rounded-4 border">
                                <i class="fa-solid fa-file-arrow-up display-4 text-muted mb-3 d-block"></i>
                                <h6 class="fw-bold text-dark">Chưa có bản scan hợp đồng</h6>
                                <p class="small text-muted mb-0">Hợp đồng giấy sau khi in và ký trực tiếp giữa 2 bên sẽ được chụp ảnh/scan và tải lên hiển thị tại đây.</p>
                            </div>
                        <?php endif; ?>

                        <!-- Nút xem toàn văn bản hợp đồng -->
                        <div class="mt-4 pt-3 border-top text-center">
                            <a href="index.php?route=contracts/detail&id=<?= (int)$contract['id'] ?>" class="btn btn-outline-teal rounded-pill px-4 fw-bold" style="border-color: var(--teal-main); color: var(--teal-main);">
                                <i class="fa-solid fa-file-lines me-1"></i> Xem Văn Bản Hợp Đồng Chi Tiết (A4)
                            </a>
                        </div>

                    </div>
                </div>
            </div>

        <?php endif; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
