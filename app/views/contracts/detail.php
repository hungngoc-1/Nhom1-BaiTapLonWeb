<?php
$sigType = $contract['signature_type'] ?? 'online';
$sigStatus = $contract['signature_status'] ?? 'pending';
$depositDate = !empty($contract['deposit_date']) ? $contract['deposit_date'] : $contract['start_date'];
$isAdvanceDeposit = (strtotime($depositDate) < strtotime($contract['start_date']));
$monthlyRent = (float)($contract['monthly_rent'] ?? $contract['room_price'] ?? $contract['deposit_amount']);
$electricityRate = (float)($contract['electricity_rate'] ?? 3500);
$waterRate = (float)($contract['water_rate'] ?? 20000);
$serviceFee = (float)($contract['service_fee'] ?? 150000);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayMate - Bản Hợp đồng <?= htmlspecialchars($contract['contract_code']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --teal-main: #308f86;
            --teal-hover: #25756d;
            --bg-page: #f8fafc;
        }
        body {
            background-color: var(--bg-page);
            font-family: 'Nunito', sans-serif;
            font-size: 0.9rem;
            color: #334155;
            min-height: 100vh;
            background-image: 
                url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23e6f4f1" fill-opacity="0.8" d="M0,160L48,170.7C96,181,192,203,288,197.3C384,192,480,160,576,149.3C672,139,768,149,864,170.7C960,192,1056,224,1152,213.3C1248,203,1344,149,1392,122.7L1440,96L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0C192,0,96,0,48,0L0,0Z"></path></svg>');
            background-position: top center;
            background-repeat: no-repeat;
            background-size: 100% auto;
        }
        .navbar-custom { background: #ffffff; padding: 12px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04); border-bottom: 1px solid #f1f5f9;}
        .brand-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .brand-logo i { color: var(--teal-main); font-size: 2.2rem; }
        .brand-text { color: var(--teal-main); font-weight: 900; font-size: 1.4rem; line-height: 1; }
        .brand-sub { color: #64748b; font-size: 0.75rem; font-weight: 600; }
        .text-teal-main { color: var(--teal-main) !important; }
        .nav-pills { gap: 10px; }
        .nav-pills .nav-link { color: #475569; font-weight: 800; font-size: 0.95rem; padding: 10px 20px; border-radius: 50px; }
        .nav-pills .nav-link.active { background-color: var(--teal-main); color: white; box-shadow: 0 6px 15px rgba(48, 143, 134, 0.35); }
        .btn-back-content { background: white; border: 3px solid #ffffff; color: #334155; font-weight: 800; font-size: 0.9rem; padding: 8px 22px; border-radius: 50px; text-decoration: none; display: inline-flex; align-items: center; box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
        .btn-teal-custom { background: var(--teal-main); border-radius: 50px; font-weight: 800; color: white; padding: 9px 22px; border: 2px solid var(--teal-hover); box-shadow: 0 8px 20px rgba(48, 143, 134, 0.35); font-size: 0.88rem; }
        .btn-teal-custom:hover { background: var(--teal-hover); color: white; }
        .a4-paper {
            background: #ffffff;
            border-radius: 26px;
            border: 4px solid #ffffff;
            box-shadow: 0 18px 45px rgba(0,0,0,0.1);
            max-width: 880px;
            margin: 0 auto;
            color: #111827;
        }
        @media print {
            body { background: #fff !important; }
            .d-print-none { display: none !important; }
            .a4-paper { box-shadow: none !important; border: none !important; max-width: 100% !important; }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-custom d-print-none">
        <div class="container-fluid px-4 px-xl-5 d-flex justify-content-between align-items-center">
            <a class="brand-logo me-5" href="index.php?route=dashboard">
                <i class="fa-solid fa-house-chimney text-teal-main"></i>
                <div>
                    <div class="brand-text">StayMate</div>
                    <div class="brand-sub">Quản Lý Nhà Trọ</div>
                </div>
            </a>
            <ul class="nav nav-pills me-auto d-none d-lg-flex">
                <li class="nav-item"><a class="nav-link" href="index.php?route=dashboard"><i class="fa-solid fa-city me-1"></i> Tòa nhà</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=overview"><i class="fa-solid fa-border-all me-1"></i> Tổng quan</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=rooms"><i class="fa-solid fa-bed me-1"></i> Phòng trọ</a></li>
                <li class="nav-item"><a class="nav-link active" href="index.php?route=contracts"><i class="fa-solid fa-file-signature me-1"></i> Hợp đồng</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=utilities"><i class="fa-solid fa-bolt me-1"></i> Điện Nước</a></li>
            </ul>
        </div>
    </nav>

    <div class="container py-4">

        <!-- Thanh công cụ trên cùng (ẩn khi in) -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 d-print-none">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="index.php?route=contracts" class="btn-back-content">
                    <i class="fa-solid fa-arrow-left me-2"></i> Quay lại DS Hợp đồng
                </a>
                <?php if ($sigType === 'online'): ?>
                    <span class="badge rounded-pill px-3 py-2" style="background:#e0f2fe;color:#0369a1;font-weight:800;">
                        <i class="fa-solid fa-pen-nib me-1"></i> Hợp đồng Điện tử (Ký Online)
                    </span>
                <?php else: ?>
                    <span class="badge rounded-pill px-3 py-2" style="background:#f1f5f9;color:#334155;font-weight:800;">
                        <i class="fa-solid fa-file-lines me-1"></i> Hợp đồng Ký giấy
                    </span>
                <?php endif; ?>

                <?php if ($sigStatus === 'signed'): ?>
                    <span class="badge rounded-pill bg-success px-3 py-2 fw-bold"><i class="fa-solid fa-circle-check me-1"></i> Đã ký xác nhận</span>
                <?php else: ?>
                    <span class="badge rounded-pill bg-warning text-dark px-3 py-2 fw-bold"><i class="fa-regular fa-clock me-1"></i> Chờ ký</span>
                <?php endif; ?>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn-teal-custom" data-bs-toggle="modal" data-bs-target="#signOnlineModal">
                    <i class="fa-solid fa-signature me-1"></i> Ký Điện tử (Online)
                </button>
                <button type="button" class="btn btn-outline-dark rounded-pill fw-bold px-3" onclick="window.print()">
                    <i class="fa-solid fa-print me-1"></i> In Hợp đồng Giấy (A4)
                </button>
                <button type="button" class="btn btn-success rounded-pill fw-bold px-3" data-bs-toggle="modal" data-bs-target="#uploadScanModal">
                    <i class="fa-solid fa-cloud-arrow-up me-1"></i> <?= !empty($contract['scan_file']) ? 'Cập nhật Bản Scan' : 'Lưu Bản Scan (Ký giấy)' ?>
                </button>
                <button type="button" class="btn btn-outline-primary rounded-pill fw-bold px-3" data-bs-toggle="modal" data-bs-target="#renewModal">
                    <i class="fa-solid fa-rotate me-1"></i> Gia hạn HĐ
                </button>
            </div>
        </div>

        <?php if (!empty($flash_error)): ?>
            <div class="alert alert-danger rounded-4 fw-bold d-print-none"><i class="fa-solid fa-circle-exclamation me-2"></i><?= htmlspecialchars($flash_error) ?></div>
        <?php endif; ?>
        <?php if (!empty($flash_success)): ?>
            <div class="alert alert-success rounded-4 fw-bold d-print-none"><i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($flash_success) ?></div>
        <?php endif; ?>

        <!-- Khung hiển thị Bản Scan nếu đã tải lên -->
        <?php if (!empty($contract['scan_file'])): ?>
        <div class="card border-0 shadow-sm rounded-4 mb-4 d-print-none border-start border-success border-4">
            <div class="card-body py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <span class="badge bg-success rounded-pill px-3 py-2 me-2"><i class="fa-solid fa-file-circle-check me-1"></i> Đã lưu Bản Scan Hợp đồng giấy</span>
                    <span class="small text-muted fw-bold">Cập nhật lúc: <?= !empty($contract['signed_at']) ? date('d/m/Y H:i', strtotime($contract['signed_at'])) : 'Mới đây' ?></span>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= htmlspecialchars($contract['scan_file']) ?>" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold">
                        <i class="fa-solid fa-eye me-1"></i> Mở xem Bản Scan gốc
                    </a>
                    <button class="btn btn-sm btn-light border rounded-pill px-3 fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#scanPreviewBox">
                        Ẩn/Hiện ảnh Scan
                    </button>
                </div>
            </div>
            <div class="collapse show px-4 pb-3" id="scanPreviewBox">
                <?php if (str_ends_with(strtolower($contract['scan_file']), '.pdf')): ?>
                    <iframe src="<?= htmlspecialchars($contract['scan_file']) ?>" style="width:100%; height:480px;" class="border rounded-3"></iframe>
                <?php else: ?>
                    <div class="text-center bg-light p-3 rounded-4 border">
                        <img src="<?= htmlspecialchars($contract['scan_file']) ?>" alt="Bản scan hợp đồng" class="img-fluid rounded-3" style="max-height: 520px;">
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- VĂN BẢN HỢP ĐỒNG THUÊ PHÒNG TRỌ (CHUẨN A4 ĐỂ XEM & IN) -->
        <div class="a4-paper">
            <div class="p-4 p-md-5" style="font-size: 0.95rem; line-height: 1.7;">
                <!-- Quốc hiệu & Tiêu ngữ -->
                <div class="text-center mb-4">
                    <div class="fw-bold text-uppercase" style="font-size: 1rem;">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>
                    <div class="fw-bold pb-1 d-inline-block border-bottom border-dark">Độc lập - Tự do - Hạnh phúc</div>
                    <h4 class="fw-bold text-uppercase mt-3 mb-1 text-teal-main">HỢP ĐỒNG THUÊ PHÒNG TRỌ</h4>
                    <div class="small text-muted fw-semibold">
                        Số hợp đồng: <strong><?= htmlspecialchars($contract['contract_code']) ?></strong>
                        &bull; Hình thức: <strong><?= $sigType === 'online' ? 'Hợp đồng Điện tử' : 'Hợp đồng Ký giấy' ?></strong>
                        &bull; Trạng thái: <strong><?= htmlspecialchars($statuses[$contract['status']] ?? $contract['status']) ?></strong>
                    </div>
                </div>

                <p class="mb-3">
                    Hôm nay, ngày <strong><?= date('d', strtotime($depositDate)) ?></strong> tháng <strong><?= date('m', strtotime($depositDate)) ?></strong> năm <strong><?= date('Y', strtotime($depositDate)) ?></strong>, tại <strong>Hệ thống Quản lý Nhà trọ StayMate</strong>, chúng tôi gồm có:
                </p>

                <!-- BÊN A & BÊN B -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 border bg-light bg-opacity-50 h-100">
                            <div class="fw-bold text-uppercase text-teal-main mb-1">I. BÊN CHO THUÊ PHÒNG (BÊN A):</div>
                            <div><strong>Đơn vị quản lý:</strong> Hệ thống Nhà trọ StayMate</div>
                            <div><strong>Người đại diện:</strong> Ban Quản Lý Tòa Nhà</div>
                            <div><strong>Số điện thoại:</strong> 0901.234.567</div>
                            <div><strong>Địa chỉ:</strong> Số 128 Cầu Giấy, TP. Hà Nội</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 border bg-light bg-opacity-50 h-100">
                            <div class="fw-bold text-uppercase text-teal-main mb-1">II. BÊN THUÊ PHÒNG (BÊN B):</div>
                            <div><strong>Ông / Bà:</strong> <?= htmlspecialchars($contract['full_name'] ?? '—') ?></div>
                            <div><strong>Số điện thoại:</strong> <?= htmlspecialchars($contract['phone'] ?? '—') ?></div>
                            <div><strong>Email liên hệ:</strong> <?= htmlspecialchars($contract['email'] ?? '—') ?></div>
                            <div><strong>Phòng đăng ký thuê:</strong> <strong><?= htmlspecialchars($contract['room_number'] ?? '—') ?></strong></div>
                        </div>
                    </div>
                </div>

                <p class="mb-2">Hai bên thống nhất thỏa thuận và ký kết Hợp đồng thuê phòng trọ với các điều khoản cụ thể sau đây:</p>

                <!-- ĐIỀU 1 -->
                <div class="mb-3">
                    <div class="fw-bold text-uppercase text-teal-main">ĐIỀU 1: ĐỐI TƯỢNG VÀ THỜI HẠN HỢP ĐỒNG</div>
                    <div>1.1. Bên A đồng ý cho Bên B thuê <strong>Phòng số <?= htmlspecialchars($contract['room_number'] ?? '') ?></strong> thuộc Hệ thống Nhà trọ StayMate.</div>
                    <div>1.2. Tiện nghi bàn giao kèm theo phòng: <?= htmlspecialchars($contract['room_description'] ?: 'Điều hòa, bình nóng lạnh, Wifi tốc độ cao, WC khép kín') ?>.</div>
                    <?php
                    $startTs = strtotime($contract['start_date']);
                    $endTs = strtotime($contract['end_date']);
                    $durationMonths = max(1, (int)round((date('Y', $endTs) - date('Y', $startTs)) * 12 + (date('n', $endTs) - date('n', $startTs))));
                    ?>
                    <div>
                        1.3. <strong>Thời hạn có hiệu lực của hợp đồng:</strong> Hợp đồng có thời hạn <strong><?= $durationMonths ?> tháng</strong>, bắt đầu từ ngày <strong><?= date('d/m/Y', $startTs) ?></strong> đến ngày <strong><?= date('d/m/Y', $endTs) ?></strong>.
                    </div>
                </div>

                <!-- ĐIỀU 2 -->
                <div class="mb-3">
                    <div class="fw-bold text-uppercase text-teal-main">ĐIỀU 2: GIÁ THUÊ PHÒNG, TIỀN ĐẶT CỌC VÀ QUY TẮC TÍNH TIỀN</div>
                    <div>2.1. <strong>Giá thuê phòng cố định:</strong> <strong class="text-danger"><?= number_format($monthlyRent, 0, ',', '.') ?> đ / tháng</strong>.</div>
                    <div>
                        2.2. <strong>Tiền đặt cọc giữ phòng &amp; đảm bảo hợp đồng:</strong> <strong><?= number_format((float)$contract['deposit_amount'], 0, ',', '.') ?> đ</strong> (bằng đúng 01 tháng tiền thuê phòng).
                        <ul class="mb-1 mt-1">
                            <li>
                                <strong>Ngày đặt cọc thực tế:</strong> Ghi nhận Bên B đã đặt cọc vào ngày <strong><?= date('d/m/Y', strtotime($depositDate)) ?></strong>
                                <?= $isAdvanceDeposit ? '(Đặt cọc trước, hợp đồng chính thức tính hiệu lực từ ngày ' . date('d/m/Y', $startTs) . ')' : '(Đặt cọc và nhận phòng)' ?>.
                            </li>
                            <li>
                                <strong>Quy định tính tiền khi vào ở:</strong> Nếu Bên B vào ở từ ngày mùng 01 đến ngày 15 của tháng thì tính 100% tiền phòng tháng đó và phí dịch vụ như bình thường; nếu vào ở sau ngày 15 của tháng thì tính nửa tháng tiền phòng (50%) và phí dịch vụ như bình thường.
                            </li>
                            <li>
                                <strong>Quy định hoàn trả tiền cọc khi rời đi:</strong> Khi Bên B trả phòng đúng hoặc sau khi hết hạn hợp đồng (từ ngày <?= date('d/m/Y', $endTs) ?> trở đi), Bên A hoàn trả lại 100% tiền cọc (<?= number_format((float)$contract['deposit_amount'], 0, ',', '.') ?> đ). Trường hợp Bên B tự ý chấm dứt hợp đồng và rời đi trước hạn thì sẽ <strong>mất toàn bộ tiền đặt cọc</strong>.
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- ĐIỀU 3 -->
                <div class="mb-3">
                    <div class="fw-bold text-uppercase text-teal-main">ĐIỀU 3: CHI PHÍ ĐIỆN, NƯỚC, DỊCH VỤ VÀ PHƯƠNG THỨC THANH TOÁN</div>
                    <div>3.1. Đơn giá áp dụng hàng tháng:
                        <strong>Tiền điện:</strong> <?= number_format($electricityRate, 0, ',', '.') ?> đ/kWh &bull;
                        <strong>Tiền nước:</strong> <?= number_format($waterRate, 0, ',', '.') ?> đ/khối &bull;
                        <strong>Phí dịch vụ chung:</strong> <?= number_format($serviceFee, 0, ',', '.') ?> đ/tháng.
                    </div>
                    <div>3.2. Tiền phòng và chi phí dịch vụ được thông báo qua Hóa đơn hàng tháng và thanh toán từ ngày 01 đến ngày 05 đầu mỗi tháng.</div>
                </div>

                <!-- ĐIỀU 4 -->
                <div class="mb-3">
                    <div class="fw-bold text-uppercase text-teal-main">ĐIỀU 4: GIA HẠN TỰ ĐỘNG VÀ TRÁCH NHIỆM CỦA HAI BÊN</div>
                    <div>4.1. Trước ngày hết hạn hợp đồng 01 tháng (30 ngày), hai bên thỏa thuận gia hạn. Nếu Bên B đồng ý ở tiếp thì hợp đồng được gia hạn theo kỳ mới.</div>
                    <div>4.2. Hợp đồng này được lập dưới hình thức <strong>Hợp đồng Điện tử (ký trực tuyến)</strong> hoặc <strong>Hợp đồng Ký giấy (in ra ký trực tiếp và lưu bản Scan trên hệ thống)</strong>, có giá trị pháp lý ngang nhau.</div>
                    <?php if (!empty($contract['notes'])): ?>
                        <div class="mt-1"><strong>Ghi chú bổ sung:</strong> <?= htmlspecialchars($contract['notes']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($contract['termination_reason'])): ?>
                        <div class="mt-2 p-2 rounded-3 bg-warning-subtle border border-warning small">
                            <strong>Biên bản thanh lý (<?= !empty($contract['terminated_at']) ? date('d/m/Y', strtotime($contract['terminated_at'])) : '' ?>):</strong>
                            <?= htmlspecialchars($contract['termination_reason']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- CHỮ KÝ HAI BÊN -->
                <div class="row mt-4 pt-3 text-center">
                    <div class="col-6">
                        <div class="fw-bold text-uppercase">ĐẠI DIỆN BÊN CHO THUÊ (BÊN A)</div>
                        <div class="small text-muted fst-italic mb-2">(Ký và ghi rõ họ tên)</div>
                        <div style="min-height: 110px;" class="d-flex align-items-center justify-content-center">
                            <?php if (!empty($contract['manager_signature'])): ?>
                                <img src="<?= $contract['manager_signature'] ?>" alt="Chữ ký Bên A" style="max-height: 95px; max-width: 220px;">
                            <?php else: ?>
                                <span class="text-muted small d-print-none">(Chưa có chữ ký điện tử Bên A)</span>
                            <?php endif; ?>
                        </div>
                        <div class="fw-bold mt-1">Ban Quản Lý StayMate</div>
                    </div>

                    <div class="col-6">
                        <div class="fw-bold text-uppercase">ĐẠI DIỆN BÊN THUÊ PHÒNG (BÊN B)</div>
                        <div class="small text-muted fst-italic mb-2">(Ký và ghi rõ họ tên)</div>
                        <div style="min-height: 110px;" class="d-flex align-items-center justify-content-center">
                            <?php if (!empty($contract['tenant_signature'])): ?>
                                <img src="<?= $contract['tenant_signature'] ?>" alt="Chữ ký Bên B" style="max-height: 95px; max-width: 220px;">
                            <?php else: ?>
                                <span class="text-muted small d-print-none">(Chưa có chữ ký điện tử Bên B)</span>
                            <?php endif; ?>
                        </div>
                        <div class="fw-bold mt-1"><?= htmlspecialchars($contract['full_name'] ?? '') ?></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- MODAL 1: KÝ HỢP ĐỒNG ĐIỆN TỬ (ONLINE CANVAS) -->
    <div class="modal fade d-print-none" id="signOnlineModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form method="POST" action="index.php?route=contracts/sign-online" id="signOnlineForm" class="modal-content" style="border-radius: 24px;">
                <input type="hidden" name="id" value="<?= (int)$contract['id'] ?>">
                <input type="hidden" name="manager_signature" id="manager_signature_input">
                <input type="hidden" name="tenant_signature" id="tenant_signature_input">

                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold text-teal-main"><i class="fa-solid fa-pen-nib me-2"></i>Ký Hợp đồng Điện tử (Online) — <?= htmlspecialchars($contract['contract_code']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold mb-0">Chữ ký Bên A (Chủ trọ / Quản lý)</label>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 fw-bold" onclick="clearPad('managerPad')">Xóa ký lại</button>
                            </div>
                            <div class="border rounded-4 bg-light text-center p-2">
                                <canvas id="managerPad" width="320" height="140" class="bg-white rounded-3 border w-100" style="touch-action: none; cursor: crosshair;"></canvas>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold mb-0">Chữ ký Bên B (<?= htmlspecialchars($contract['full_name'] ?? 'Khách thuê') ?>)</label>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 fw-bold" onclick="clearPad('tenantPad')">Xóa ký lại</button>
                            </div>
                            <div class="border rounded-4 bg-light text-center p-2">
                                <canvas id="tenantPad" width="320" height="140" class="bg-white rounded-3 border w-100" style="touch-action: none; cursor: crosshair;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="small text-muted mt-2 fw-semibold">
                        <i class="fa-solid fa-circle-info text-teal-main me-1"></i> Dùng chuột hoặc màn hình cảm ứng vẽ chữ ký trực tiếp vào khung trắng ở trên, sau đó nhấn <strong>Lưu Chữ ký Điện tử</strong>.
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn-teal-custom"><i class="fa-solid fa-check me-1"></i> Lưu Chữ ký Điện tử</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: LƯU BẢN SCAN HỢP ĐỒNG KÝ GIẤY -->
    <div class="modal fade d-print-none" id="uploadScanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="index.php?route=contracts/upload-scan" enctype="multipart/form-data" class="modal-content" style="border-radius: 24px;">
                <input type="hidden" name="id" value="<?= (int)$contract['id'] ?>">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold text-success"><i class="fa-solid fa-cloud-arrow-up me-2"></i>Lưu Bản Scan Hợp đồng Ký giấy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3 fw-semibold">
                        Sau khi in hợp đồng giấy ra ký trực tiếp, bạn hãy tải ảnh chụp hoặc file PDF bản Scan lên đây để lưu trữ vào hồ sơ hợp đồng <strong><?= htmlspecialchars($contract['contract_code']) ?></strong>.
                    </p>
                    <div class="mb-2">
                        <label class="form-label fw-bold small">Chọn file Bản Scan (Ảnh JPG, PNG hoặc PDF) <span class="text-danger">*</span></label>
                        <input type="file" name="scan_file" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold"><i class="fa-solid fa-upload me-1"></i> Tải lên &amp; Lưu Bản Scan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: GIA HẠN HỢP ĐỒNG -->
    <div class="modal fade d-print-none" id="renewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="index.php?route=contracts/renew" class="modal-content" style="border-radius: 24px;">
                <input type="hidden" name="id" value="<?= (int)$contract['id'] ?>">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold text-teal-main"><i class="fa-solid fa-rotate me-2"></i>Gia hạn Hợp đồng <?= htmlspecialchars($contract['contract_code']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted fw-semibold mb-3">
                        Ngày hết hạn hiện tại: <strong class="text-danger"><?= date('d/m/Y', strtotime($contract['end_date'])) ?></strong>
                    </p>
                    <label class="form-label small fw-bold">Chọn số tháng gia hạn thêm</label>
                    <select name="renewal_months" class="form-select">
                        <option value="6">Gia hạn thêm 6 tháng</option>
                        <option value="12" selected>Gia hạn thêm 12 tháng (1 năm)</option>
                        <option value="24">Gia hạn thêm 24 tháng (2 năm)</option>
                    </select>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn-teal-custom"><i class="fa-solid fa-check me-1"></i> Xác nhận Gia hạn</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    const padState = {};

    function initPad(canvasId) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        ctx.strokeStyle = '#0f766e';
        ctx.lineWidth = 2.4;
        ctx.lineCap = 'round';
        padState[canvasId] = { drawing: false, dirty: false, canvas, ctx };

        const getPos = (e) => {
            const rect = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: (clientX - rect.left) * (canvas.width / rect.width),
                y: (clientY - rect.top) * (canvas.height / rect.height)
            };
        };

        const start = (e) => {
            e.preventDefault();
            const pos = getPos(e);
            padState[canvasId].drawing = true;
            padState[canvasId].dirty = true;
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
        };
        const move = (e) => {
            if (!padState[canvasId].drawing) return;
            e.preventDefault();
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        };
        const end = () => { padState[canvasId].drawing = false; };

        canvas.addEventListener('mousedown', start);
        canvas.addEventListener('mousemove', move);
        window.addEventListener('mouseup', end);
        canvas.addEventListener('touchstart', start, { passive: false });
        canvas.addEventListener('touchmove', move, { passive: false });
        canvas.addEventListener('touchend', end);
    }

    function clearPad(canvasId) {
        const st = padState[canvasId];
        if (!st) return;
        st.ctx.clearRect(0, 0, st.canvas.width, st.canvas.height);
        st.dirty = false;
    }

    document.addEventListener('DOMContentLoaded', function () {
        initPad('managerPad');
        initPad('tenantPad');

        const form = document.getElementById('signOnlineForm');
        if (form) {
            form.addEventListener('submit', function () {
                if (padState['managerPad'] && padState['managerPad'].dirty) {
                    document.getElementById('manager_signature_input').value = padState['managerPad'].canvas.toDataURL('image/png');
                }
                if (padState['tenantPad'] && padState['tenantPad'].dirty) {
                    document.getElementById('tenant_signature_input').value = padState['tenantPad'].canvas.toDataURL('image/png');
                }
            });
        }
    });
    </script>
</body>
</html>
