<?php
/**
 * TRANG CHI TIẾT VĂN BẢN HỢP ĐỒNG - IN A4 KÝ TRỰC TIẾP, KÝ ONLINE & LƯU BẢN SCAN
 * Phụ trách: Hùng Văn Ngọc (Nhóm trưởng - MSSV: 224001738)
 */
if (!function_exists('h')) {
    function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
}

$cType = $contract['contract_type'] ?? 'official_lease';
$isHold = ($cType === 'deposit_hold');
$sigType = $contract['signature_type'] ?? 'paper';
$sigStatus = $contract['signature_status'] ?? 'pending';
$depositDate = !empty($contract['deposit_date']) ? $contract['deposit_date'] : $contract['start_date'];
$monthlyRent = (float)($contract['monthly_rent'] ?? $contract['room_price'] ?? $contract['deposit_amount']);
$depositAmount = (float)$contract['deposit_amount'];
$depositPercent = (int)($contract['deposit_paid_percent'] ?? ($isHold ? 50 : 100));
$firstMonthPaid = !empty($contract['first_month_rent_paid']);
$electricityRate = (float)($contract['electricity_rate'] ?? 3500);
$waterRate = (float)($contract['water_rate'] ?? 20000);
$serviceFee = (float)($contract['service_fee'] ?? 150000);
$startTs = strtotime($contract['start_date']);
$endTs = strtotime($contract['end_date']);
$durationDays = max(1, (int)round(($endTs - $startTs) / 86400));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayMate - Văn bản Hợp đồng <?= h($contract['contract_code']) ?></title>
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
            font-size: 0.92rem;
            color: #334155;
            min-height: 100vh;
        }
        .navbar-custom { background: #ffffff; padding: 12px 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04); z-index: 100; border-bottom: 1px solid #f1f5f9; }
        .brand-text { color: var(--teal-main); font-weight: 900; font-size: 1.4rem; }
        .text-teal-main { color: var(--teal-main) !important; }
        .nav-pills { gap: 6px; flex-wrap: nowrap !important; }
        .nav-pills .nav-link { 
            color: #475569; font-weight: 800; font-size: 0.88rem; 
            padding: 8px 14px; border-radius: 50px; transition: all 0.2s; white-space: nowrap; 
        }
        .nav-pills .nav-link i { margin-right: 6px; }
        .nav-pills .nav-link:hover { color: var(--teal-main); background: #f0fdfa; }
        .nav-pills .nav-link.active { background-color: var(--teal-main); color: white; box-shadow: 0 6px 15px rgba(48, 143, 134, 0.35); }
        .avatar-circle { width: 46px; height: 46px; background-color: var(--teal-main); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 900; font-size: 1rem; border: 3px solid #ffffff; box-shadow: 0 6px 15px rgba(48, 143, 134, 0.4); transition: 0.2s; }
        .btn-back-content { background: white; border: 3px solid #ffffff; color: #334155; font-weight: 800; font-size: 0.9rem; padding: 8px 22px; border-radius: 50px; text-decoration: none; display: inline-flex; align-items: center; box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
        .btn-teal-custom { background: var(--teal-main); border-radius: 50px; font-weight: 800; color: white; padding: 9px 22px; border: 2px solid var(--teal-hover); box-shadow: 0 8px 20px rgba(48, 143, 134, 0.35); font-size: 0.88rem; }
        .btn-teal-custom:hover { background: var(--teal-hover); color: white; }
        .btn-back-content { background: white; border: 2px solid #e2e8f0; color: #334155; font-weight: 800; font-size: 0.88rem; padding: 8px 20px; border-radius: 50px; text-decoration: none; display: inline-flex; align-items: center; }
        .btn-back-content:hover { border-color: var(--teal-main); color: var(--teal-main); }

        .a4-paper {
            background: #ffffff;
            border-radius: 24px;
            border: 4px solid #ffffff;
            box-shadow: 0 16px 45px rgba(0,0,0,0.08);
            max-width: 900px;
            margin: 0 auto;
            color: #111827;
        }

        .integration-box {
            background: #f0fdfa;
            border: 2px dashed #5eead4;
            border-radius: 20px;
            padding: 18px 22px;
        }

        @media print {
            body { background: #fff !important; font-size: 12pt; color: #000; }
            .d-print-none { display: none !important; }
            .a4-paper { box-shadow: none !important; border: none !important; max-width: 100% !important; margin: 0; padding: 0 !important; }
            .print-border-box { border: 1px solid #000 !important; }
            @page { margin: 15mm; size: A4; }
        }
    </style>
</head>
<body>

    <!-- THANH MENU ĐIỀU HƯỚNG TRÊN CÙNG -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top d-print-none">
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

    <div class="container py-4">

        <!-- THANH CÔNG CỤ THAO TÁC (ẨN KHI IN) -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 d-print-none">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="index.php?route=contracts" class="btn-back-content">
                    <i class="fa-solid fa-arrow-left me-2"></i> DS Hợp đồng
                </a>

                <?php if ($isHold): ?>
                    <span class="badge rounded-pill px-3 py-2" style="background:#fef3c7;color:#92400e;font-weight:800;border:1px solid #fde68a;">
                        <i class="fa-solid fa-clock me-1"></i> HĐ 1: Đặt cọc giữ chỗ 5 ngày (50% cọc)
                    </span>
                <?php else: ?>
                    <span class="badge rounded-pill px-3 py-2" style="background:#dcfce7;color:#166534;font-weight:800;border:1px solid #bbf7d0;">
                        <i class="fa-solid fa-shield-halved me-1"></i> HĐ 2: Thuê chính thức 6 tháng (100% cọc)
                    </span>
                <?php endif; ?>

                <?php if ($sigStatus === 'signed' || !empty($contract['scan_file'])): ?>
                    <span class="badge rounded-pill bg-success px-3 py-2 fw-bold"><i class="fa-solid fa-circle-check me-1"></i> Đã hoàn tất ký / Có Scan</span>
                <?php else: ?>
                    <span class="badge rounded-pill bg-warning text-dark px-3 py-2 fw-bold"><i class="fa-regular fa-clock me-1"></i> Chờ ký / Chờ nộp Scan</span>
                <?php endif; ?>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline-dark rounded-pill fw-bold px-3" onclick="window.print()">
                    <i class="fa-solid fa-print me-1"></i> In Hợp đồng Ra Giấy (A4)
                </button>
                <button type="button" class="btn btn-success rounded-pill fw-bold px-3" data-bs-toggle="modal" data-bs-target="#uploadScanModal">
                    <i class="fa-solid fa-camera me-1"></i> <?= !empty($contract['scan_file']) ? 'Đổi ảnh Scan' : 'Tải lên Bản Scan (Đã Ký Tay)' ?>
                </button>
                <button type="button" class="btn-teal-custom" data-bs-toggle="modal" data-bs-target="#signOnlineModal">
                    <i class="fa-solid fa-pen-nib me-1"></i> Ký Điện Tử (Online)
                </button>
            </div>
        </div>

        <?php if (!empty($flash_error)): ?>
            <div class="alert alert-danger rounded-4 fw-bold d-print-none"><i class="fa-solid fa-circle-exclamation me-2"></i><?= h($flash_error) ?></div>
        <?php endif; ?>
        <?php if (!empty($flash_success)): ?>
            <div class="alert alert-success rounded-4 fw-bold d-print-none"><i class="fa-solid fa-circle-check me-2"></i><?= h($flash_success) ?></div>
        <?php endif; ?>

        <!-- HỘP THÔNG TIN TÍCH HỢP PHÂN QUYỀN KHÁCH THUÊ (DÀNH CHO BẠN LÀM PHÂN QUYỀN) -->
        <div class="integration-box mb-4 d-print-none">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <div class="fw-bold text-teal-main small text-uppercase">
                    <i class="fa-solid fa-key me-1"></i> Thông Tin Tích Hợp Phân Quyền Khách Thuê (Dành cho thành viên phụ trách Phân quyền)
                </div>
                <span class="badge bg-white text-teal-main border border-teal-main px-3 py-1 fw-bold">ID Sẵn Sàng Liên Kết</span>
            </div>
            <div class="row g-2 small">
                <div class="col-md-3">
                    <span class="text-muted">Mã Khách Thuê (Tenant ID):</span>
                    <strong class="text-dark bg-white px-2 py-0.5 rounded border d-block mt-1">
                        ID: <span id="copy_tenant_id"><?= (int)$contract['tenant_id'] ?></span>
                    </strong>
                </div>
                <div class="col-md-3">
                    <span class="text-muted">Mã Hợp Đồng (Contract ID):</span>
                    <strong class="text-dark bg-white px-2 py-0.5 rounded border d-block mt-1">
                        ID: <span id="copy_contract_id"><?= (int)$contract['id'] ?></span>
                    </strong>
                </div>
                <div class="col-md-6">
                    <span class="text-muted">Đường dẫn xem HĐ cho Khách thuê:</span>
                    <div class="input-group input-group-sm mt-1">
                        <input type="text" class="form-control bg-white font-monospace" id="linkTenantView" value="<?= 'index.php?route=tenant/contract&id=' . (int)$contract['id'] ?>" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('linkTenantView').value); alert('Đã sao chép link xem hợp đồng!');">Copy Link</button>
                    </div>
                </div>
                <div class="col-12 mt-1">
                    <span class="text-muted">REST API JSON:</span>
                    <code class="d-block bg-white p-2 rounded border mt-1 user-select-all font-monospace">
                        index.php?route=api/tenant/contract&tenant_id=<?= (int)$contract['tenant_id'] ?>
                    </code>
                </div>
            </div>
        </div>

        <!-- KHUNG HIỂN THỊ BẢN SCAN ĐÃ KÝ NẾU ĐÃ TẢI LÊN -->
        <?php if (!empty($contract['scan_file'])): ?>
        <div class="card border-0 shadow-sm rounded-4 mb-4 d-print-none border-start border-success border-4">
            <div class="card-body py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <span class="badge bg-success rounded-pill px-3 py-2 me-2">
                        <i class="fa-solid fa-file-circle-check me-1"></i> Đã tải lên Bản Scan Hợp đồng Ký Giấy
                    </span>
                    <span class="small text-muted fw-bold">Khách thuê có thể xem trực tiếp bản scan này trong cổng phân quyền của mình.</span>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= h($contract['scan_file']) ?>" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Mở xem file gốc
                    </a>
                    <a href="<?= h($contract['scan_file']) ?>" download class="btn btn-sm btn-success rounded-pill px-3 fw-bold">
                        <i class="fa-solid fa-download me-1"></i> Tải về máy
                    </a>
                </div>
            </div>
            <div class="px-4 pb-3">
                <?php if (str_ends_with(strtolower($contract['scan_file']), '.pdf')): ?>
                    <iframe src="<?= h($contract['scan_file']) ?>" style="width:100%; height:480px;" class="border rounded-3"></iframe>
                <?php else: ?>
                    <div class="text-center bg-light p-3 rounded-4 border">
                        <img src="<?= h($contract['scan_file']) ?>" alt="Bản scan hợp đồng" class="img-fluid rounded-3 shadow-sm" style="max-height: 540px;">
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- ================= VĂN BẢN HỢP ĐỒNG THUÊ PHÒNG (CHUẨN A4 ĐỂ XEM & IN TRỰC TIẾP) ================= -->
        <div class="a4-paper">
            <div class="p-4 p-md-5" style="font-size: 0.95rem; line-height: 1.75;">
                <!-- Quốc hiệu & Tiêu ngữ -->
                <div class="text-center mb-4">
                    <div class="fw-bold text-uppercase" style="font-size: 1.05rem;">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>
                    <div class="fw-bold pb-1 d-inline-block border-bottom border-dark">Độc lập - Tự do - Hạnh phúc</div>
                    
                    <h3 class="fw-black text-uppercase mt-4 mb-1 text-teal-main">
                        <?= $isHold ? 'HỢP ĐỒNG ĐẶT CỌC GIỮ CHỖ THUÊ PHÒNG' : 'HỢP ĐỒNG THUÊ PHÒNG TRỌ CHÍNH THỨC' ?>
                    </h3>
                    <div class="fw-bold text-muted small">
                        Số: <strong><?= h($contract['contract_code']) ?></strong>
                        &bull; Loại: <strong><?= $isHold ? 'HĐ 1 (Hiệu lực 5 ngày - Cọc 50%)' : 'HĐ 2 (Hiệu lực 6 tháng - Cọc 100%)' ?></strong>
                        &bull; Trạng thái: <strong><?= h($statuses[$contract['status']] ?? $contract['status']) ?></strong>
                    </div>
                </div>

                <p class="mb-3">
                    Hôm nay, ngày <strong><?= date('d', strtotime($depositDate)) ?></strong> tháng <strong><?= date('m', strtotime($depositDate)) ?></strong> năm <strong><?= date('Y', strtotime($depositDate)) ?></strong>, tại Văn phòng Quản lý Nhà trọ StayMate, chúng tôi gồm có:
                </p>

                <!-- BÊN A & BÊN B -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 border bg-light bg-opacity-50 h-100 print-border-box">
                            <div class="fw-bold text-uppercase text-teal-main mb-1">I. BÊN CHO THUÊ (BÊN A):</div>
                            <div><strong>Đơn vị quản lý:</strong> Hệ thống Quản lý Nhà trọ StayMate</div>
                            <div><strong>Đại diện:</strong> Ban Quản Lý Tòa Nhà</div>
                            <div><strong>Số điện thoại:</strong> 0901.234.567</div>
                            <div><strong>Địa chỉ:</strong> Số 128 Cầu Giấy, TP. Hà Nội</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 border bg-light bg-opacity-50 h-100 print-border-box">
                            <div class="fw-bold text-uppercase text-teal-main mb-1">II. BÊN THUÊ (BÊN B):</div>
                            <div><strong>Ông / Bà:</strong> <?= h($contract['full_name'] ?? '—') ?></div>
                            <div><strong>Số điện thoại:</strong> <?= h($contract['phone'] ?? '—') ?></div>
                            <div><strong>Email liên hệ:</strong> <?= h($contract['email'] ?? '—') ?></div>
                            <div><strong>Phòng thuê:</strong> <strong>Phòng <?= h($contract['room_number'] ?? '—') ?></strong></div>
                        </div>
                    </div>
                </div>

                <p class="mb-2">Hai bên cùng thống nhất ký kết hợp đồng với các điều khoản cụ thể sau đây:</p>

                <!-- ĐIỀU 1 -->
                <div class="mb-3">
                    <div class="fw-bold text-uppercase text-teal-main">ĐIỀU 1: ĐỐI TƯỢNG VÀ THỜI HẠN THỎA THUẬN</div>
                    <div>1.1. Bên A đồng ý cho Bên B thuê/giữ chỗ <strong>Phòng số <?= h($contract['room_number'] ?? '') ?></strong> thuộc Hệ thống Nhà trọ StayMate.</div>
                    <div>1.2. Tiện nghi bàn giao kèm theo: <?= h($contract['room_description'] ?: 'Điều hòa, bình nóng lạnh, WC khép kín, giường tủ, Wifi internet') ?>.</div>
                    <?php if ($isHold): ?>
                        <div class="mt-1">
                            1.3. <strong>Thời hạn hiệu lực giữ chỗ (HĐ 1):</strong> Hợp đồng có hiệu lực trong vòng <strong>05 ngày</strong> kể từ ngày ký (từ <strong><?= date('d/m/Y', $startTs) ?></strong> đến hết ngày <strong><?= date('d/m/Y', $endTs) ?></strong>).
                        </div>
                        <div class="text-danger fw-bold mt-1">
                            1.4. <strong>Điều khoản tự động hủy:</strong> Trong vòng 05 ngày kể từ ngày ký, nếu Bên B không hoàn tất ký Hợp đồng thuê chính thức (HĐ 2) và thanh toán nốt tiền cọc, hợp đồng này sẽ <strong>TỰ ĐỘNG BỊ HỦY</strong> và lưu vào lịch sử theo quy chế quản lý.
                        </div>
                    <?php else: ?>
                        <div class="mt-1">
                            1.3. <strong>Thời hạn hợp đồng thuê chính thức (HĐ 2):</strong> Hợp đồng có hiệu lực trong vòng <strong>06 tháng</strong> (bắt đầu từ ngày <strong><?= date('d/m/Y', $startTs) ?></strong> đến ngày <strong><?= date('d/m/Y', $endTs) ?></strong>).
                        </div>
                        <?php if (!empty($contract['parent_contract_code'])): ?>
                            <div class="text-success small fw-bold mt-1">
                                &bull; Hợp đồng này được kế thừa và hoàn thiện từ Hợp đồng giữ chỗ số <strong><?= h($contract['parent_contract_code']) ?></strong>.
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- ĐIỀU 2 -->
                <div class="mb-3">
                    <div class="fw-bold text-uppercase text-teal-main">ĐIỀU 2: GIÁ THUÊ, TIỀN ĐẶT CỌC VÀ THU TIỀN KỲ ĐẦU</div>
                    <div>2.1. <strong>Giá thuê phòng cố định:</strong> <strong class="text-danger"><?= number_format($monthlyRent, 0, ',', '.') ?> đ / tháng</strong>.</div>
                    <div>
                        2.2. <strong>Tiền đặt cọc:</strong>
                        <?php if ($isHold): ?>
                            Ghi nhận Bên B đã đóng <strong>50% tiền cọc giữ chỗ</strong> với số tiền là <strong><?= number_format($depositAmount, 0, ',', '.') ?> đ</strong> vào ngày <?= date('d/m/Y', strtotime($depositDate)) ?>.
                        <?php else: ?>
                            Ghi nhận Bên B hoàn thành <strong>100% tiền cọc bảo đảm</strong> với số tiền là <strong><?= number_format($depositAmount, 0, ',', '.') ?> đ</strong> (tương đương 01 tháng tiền phòng), được lưu giữ trong suốt thời hạn hợp đồng.
                        <?php endif; ?>
                    </div>
                    <?php if (!$isHold): ?>
                        <div class="p-2 rounded-3 bg-light border mt-2">
                            <strong>2.3. Quy định thu trước tiền phòng tháng đầu:</strong>
                            Ngay sau khi ký Hợp đồng 2 (bất kể vào thời gian nào trong tháng), Bên B thanh toán trước <strong>01 tháng tiền thuê phòng (<?= number_format($monthlyRent, 0, ',', '.') ?> đ)</strong>. Số tiền này đã được Bên A ghi nhận và tự động cộng vào Doanh thu thực thu của hệ thống (Hóa đơn kỳ <?= date('m/Y', $startTs) ?> trạng thái ĐÃ THANH TOÁN).
                        </div>
                    <?php endif; ?>
                    <div class="mt-2">
                        <strong>2.4. Quy định thanh lý và giải quyết tiền cọc:</strong>
                        Khi kết thúc hợp đồng đúng hạn và bàn giao lại phòng đầy đủ, Bên A sẽ hoàn trả 100% tiền cọc cho Bên B. Nếu Bên B tự ý đơn phương chấm dứt hợp đồng trước thời hạn thì số tiền đặt cọc sẽ không được hoàn lại.
                    </div>
                </div>

                <!-- ĐIỀU 3 -->
                <div class="mb-3">
                    <div class="fw-bold text-uppercase text-teal-main">ĐIỀU 3: ĐƠN GIÁ DỊCH VỤ VÀ PHƯƠNG THỨC THANH TOÁN</div>
                    <div>3.1. Đơn giá áp dụng:
                        <strong>Tiền điện:</strong> <?= number_format($electricityRate, 0, ',', '.') ?> đ/kWh &bull;
                        <strong>Tiền nước:</strong> <?= number_format($waterRate, 0, ',', '.') ?> đ/khối &bull;
                        <strong>Phí dịch vụ:</strong> <?= number_format($serviceFee, 0, ',', '.') ?> đ/tháng.
                    </div>
                    <div>3.2. Tiền phòng và chi phí điện nước hàng tháng thanh toán theo thông báo Hóa đơn định kỳ từ ngày 01 đến ngày 05 hàng tháng.</div>
                </div>

                <!-- ĐIỀU 4 -->
                <div class="mb-3">
                    <div class="fw-bold text-uppercase text-teal-main">ĐIỀU 4: HÌNH THỨC XÁC THỰC VÀ HIỆU LỰC HỢP ĐỒNG</div>
                    <div>4.1. Hợp đồng có thể được in ra giấy ký trực tiếp bằng tay giữa hai bên, sau đó chụp/scan lưu trữ lên hệ thống để đối soát và tra cứu trực tuyến.</div>
                    <div>4.2. Hai bên cam kết thực hiện đúng mọi điều khoản đã thỏa thuận trong hợp đồng.</div>
                    <?php if (!empty($contract['notes'])): ?>
                        <div class="mt-1"><strong>Ghi chú bổ sung:</strong> <?= h($contract['notes']) ?></div>
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
                                <span class="text-muted small d-print-none">(Ký trực tiếp trên giấy A4)</span>
                            <?php endif; ?>
                        </div>
                        <div class="fw-bold mt-1">Ban Quản Lý StayMate</div>
                    </div>

                    <div class="col-6">
                        <div class="fw-bold text-uppercase">ĐẠI DIỆN BÊN THUÊ (BÊN B)</div>
                        <div class="small text-muted fst-italic mb-2">(Ký và ghi rõ họ tên)</div>
                        <div style="min-height: 110px;" class="d-flex align-items-center justify-content-center">
                            <?php if (!empty($contract['tenant_signature'])): ?>
                                <img src="<?= $contract['tenant_signature'] ?>" alt="Chữ ký Bên B" style="max-height: 95px; max-width: 220px;">
                            <?php else: ?>
                                <span class="text-muted small d-print-none">(Ký trực tiếp trên giấy A4)</span>
                            <?php endif; ?>
                        </div>
                        <div class="fw-bold mt-1"><?= h($contract['full_name'] ?? '') ?></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ================= MODAL: TẢI LÊN BẢN SCAN ĐÃ KÝ ================= -->
    <div class="modal fade d-print-none" id="uploadScanModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="index.php?route=contracts/upload-scan" enctype="multipart/form-data" class="modal-content" style="border-radius: 24px;">
                <input type="hidden" name="id" value="<?= (int)$contract['id'] ?>">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold text-success"><i class="fa-solid fa-camera me-2"></i>Tải Lên Bản Scan / Ảnh Chụp Hợp Đồng Đã Ký</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3 fw-semibold">
                        Sau khi in hợp đồng ra giấy và hai bên ký trực tiếp, chụp ảnh rõ nét hoặc scan thành file PDF/Ảnh rồi tải lên đây để lưu vào hồ sơ và hiển thị cho khách thuê tại cổng phân quyền.
                    </p>
                    <div class="mb-2">
                        <label class="form-label fw-bold small">Chọn file Bản Scan (JPG, PNG, WEBP hoặc PDF) <span class="text-danger">*</span></label>
                        <input type="file" name="scan_file" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold"><i class="fa-solid fa-cloud-arrow-up me-1"></i> Lưu Bản Scan Lên Hệ Thống</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= MODAL: KÝ ĐIỆN TỬ (ONLINE CANVAS) ================= -->
    <div class="modal fade d-print-none" id="signOnlineModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form method="POST" action="index.php?route=contracts/sign-online" id="signOnlineForm" class="modal-content" style="border-radius: 24px;">
                <input type="hidden" name="id" value="<?= (int)$contract['id'] ?>">
                <input type="hidden" name="manager_signature" id="manager_signature_input">
                <input type="hidden" name="tenant_signature" id="tenant_signature_input">

                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold text-teal-main"><i class="fa-solid fa-pen-nib me-2"></i>Ký Hợp đồng Điện tử (Online) — <?= h($contract['contract_code']) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold mb-0">Chữ ký Bên Cho Thuê (Bên A)</label>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 fw-bold" onclick="clearPad('managerPad')">Ký lại</button>
                            </div>
                            <div class="border rounded-4 bg-light text-center p-2">
                                <canvas id="managerPad" width="320" height="140" class="bg-white rounded-3 border w-100" style="touch-action: none; cursor: crosshair;"></canvas>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold mb-0">Chữ ký Khách Thuê (Bên B)</label>
                                <button type="button" class="btn btn-sm btn-link text-danger p-0 fw-bold" onclick="clearPad('tenantPad')">Ký lại</button>
                            </div>
                            <div class="border rounded-4 bg-light text-center p-2">
                                <canvas id="tenantPad" width="320" height="140" class="bg-white rounded-3 border w-100" style="touch-action: none; cursor: crosshair;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn-teal-custom"><i class="fa-solid fa-check me-1"></i> Lưu Chữ Ký Điện Tử</button>
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
