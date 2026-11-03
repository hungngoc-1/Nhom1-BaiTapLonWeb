<?php
if (!function_exists('h')) {
    function h($s): string {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('money')) {
    function money($n): string {
        return number_format((float)$n, 0, ',', '.') . ' đ';
    }
}

$tenantName = $contract['tenant_name'] ?? ($profile['full_name'] ?? 'Khách thuê');
$tenantPhone = $contract['tenant_phone'] ?? ($profile['phone'] ?? '---');
$tenantEmail = $contract['tenant_email'] ?? ($profile['email'] ?? '---');
$roomNumber = $contract['room_number'] ?? ($profile['room_number'] ?? '---');
$roomPrice = (float)($contract['room_price'] ?? ($profile['room_price'] ?? 0));
$monthlyRent = (float)($contract['monthly_rent'] ?? $roomPrice);
$depositAmount = (float)($contract['deposit_amount'] ?? $monthlyRent);
$depositPercent = (int)($contract['deposit_paid_percent'] ?? 100);
$buildingName = $profile['building_name'] ?? 'Khu Căn Hộ StayMate';
$buildingAddress = $profile['building_address'] ?? 'Số 128 Cầu Giấy, Hà Nội';
$managerName = $profile['manager_name'] ?? 'Ban Quản Lý StayMate';
$managerPhone = $profile['manager_phone'] ?? '0988 888 999';
$contractCode = $contract['contract_code'] ?? ('HD-' . date('Y') . '-' . str_pad((string)($contract['id'] ?? 1), 3, '0', STR_PAD_LEFT));
$startDate = !empty($contract['start_date']) ? date('d/m/Y', strtotime($contract['start_date'])) : date('d/m/Y');
$endDate = !empty($contract['end_date']) ? date('d/m/Y', strtotime($contract['end_date'])) : date('d/m/Y', strtotime('+6 months'));
$scanFile = $contract['scan_file'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hợp Đồng Thuê Phòng <?= h($contractCode) ?> - StayMate</title>
    <link href="https://fonts.googleapis.com/css2?family=Times+New+Roman&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: #525659;
            font-family: 'Times New Roman', Times, serif;
            color: #111827;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Top Action Bar (ẩn khi in) */
        .pdf-toolbar {
            background: #2b2d30;
            color: #ffffff;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            font-family: 'Nunito', sans-serif;
        }
        .pdf-toolbar-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.95rem;
            font-weight: 700;
        }
        .btn-action {
            background: #308f86;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.88rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-action:hover {
            background: #25756d;
            transform: translateY(-1px);
        }
        .btn-close-tab {
            background: #475569;
        }
        .btn-close-tab:hover {
            background: #334155;
        }

        /* Khổ giấy A4 mô phỏng chuẩn */
        .a4-wrapper {
            display: flex;
            justify-content: center;
            padding: 24px 10px;
            flex-grow: 1;
        }
        .a4-page {
            background: #ffffff;
            width: 820px;
            min-height: 1120px;
            padding: 55px 65px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.35);
            font-size: 14.5px;
            line-height: 1.6;
            color: #000000;
        }

        /* Header quốc hiệu */
        .header-quoc-hieu {
            text-align: center;
            margin-bottom: 24px;
        }
        .quoc-hieu-1 {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .quoc-hieu-2 {
            font-size: 15px;
            font-weight: bold;
            margin-top: 2px;
        }
        .quoc-hieu-line {
            width: 170px;
            height: 1.5px;
            background: #000000;
            margin: 6px auto 0 auto;
        }

        /* Tiêu đề hợp đồng */
        .contract-main-title {
            text-align: center;
            margin: 28px 0 20px 0;
        }
        .contract-main-title h1 {
            font-size: 21px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .contract-code-text {
            font-size: 13.5px;
            font-style: italic;
        }

        /* Nội dung điều khoản */
        .legal-intro {
            font-style: italic;
            margin-bottom: 16px;
            text-align: justify;
        }
        .section-title {
            font-weight: bold;
            font-size: 15px;
            text-transform: uppercase;
            margin: 18px 0 8px 0;
        }
        .clause-item {
            margin-bottom: 8px;
            text-align: justify;
        }
        .clause-item strong {
            font-weight: bold;
        }
        .party-box {
            margin-bottom: 12px;
        }
        .party-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .party-box td {
            padding: 3px 0;
            vertical-align: top;
        }

        /* Chữ ký */
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }
        .sign-col {
            width: 45%;
            text-align: center;
        }
        .sign-title {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 14px;
        }
        .sign-sub {
            font-style: italic;
            font-size: 12.5px;
            color: #4b5563;
            margin-bottom: 70px;
        }
        .sign-name {
            font-weight: bold;
            font-size: 14px;
        }
        .seal-stamp {
            display: inline-block;
            border: 2px dashed #dc2626;
            color: #dc2626;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            margin-top: 6px;
        }

        /* Khi in A4 */
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .pdf-toolbar {
                display: none !important;
            }
            .a4-wrapper {
                padding: 0;
                margin: 0;
            }
            .a4-page {
                box-shadow: none;
                width: 100%;
                padding: 35px 45px;
                min-height: auto;
            }
            @page {
                size: A4;
                margin: 12mm 15mm 15mm 15mm;
            }
        }
    </style>
</head>
<body>

    <!-- THANH CÔNG CỤ ĐIỀU KHIỂN (Ẩn khi in / xuất PDF) -->
    <div class="pdf-toolbar">
        <div class="pdf-toolbar-title">
            <i class="fa-solid fa-file-pdf text-danger fs-5"></i>
            <span>Văn Bản Hợp Đồng: <strong><?= h($contractCode) ?></strong> - Phòng <?= h($roomNumber) ?></span>
        </div>
        <div style="display: flex; gap: 8px;">
            <?php if (!empty($scanFile)): ?>
                <a href="<?= h($scanFile) ?>" target="_blank" class="btn-action" style="background:#0284c7;">
                    <i class="fa-solid fa-paperclip"></i> Mở Bản Scan Gốc
                </a>
            <?php endif; ?>
            <button onclick="window.print()" class="btn-action">
                <i class="fa-solid fa-print"></i> In / Lưu PDF
            </button>
            <button onclick="window.close()" class="btn-action btn-close-tab">
                <i class="fa-solid fa-xmark"></i> Đóng tab nhỏ
            </button>
        </div>
    </div>

    <!-- KHUNG TRANG A4 HỢP ĐỒNG -->
    <div class="a4-wrapper">
        <div class="a4-page">

            <!-- QUỐC HIỆU TIÊU NGỮ -->
            <div class="header-quoc-hieu">
                <div class="quoc-hieu-1">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>
                <div class="quoc-hieu-2">Độc lập - Tự do - Hạnh phúc</div>
                <div class="quoc-hieu-line"></div>
            </div>

            <!-- TIÊU ĐỀ -->
            <div class="contract-main-title">
                <h1>HỢP ĐỒNG THUÊ PHÒNG TRỌ / CĂN HỘ</h1>
                <div class="contract-code-text">Số: <?= h($contractCode) ?> / HĐTN-STAYMATE</div>
            </div>

            <div class="legal-intro">
                - Căn cứ Bộ luật Dân sự số 91/2015/QH13 được Quốc hội Nước CHXHCN Việt Nam thông qua ngày 24/11/2015;<br>
                - Căn cứ Luật Nhà ở số 65/2014/QH13 và các văn bản hướng dẫn thi hành hiện hành;<br>
                - Căn cứ vào nhu cầu và khả năng thực tế của hai bên tham gia ký kết hợp đồng.
            </div>

            <div class="clause-item">
                Hôm nay, ngày <?= date('d') ?> tháng <?= date('m') ?> năm <?= date('Y') ?>, tại địa chỉ: <strong><?= h($buildingAddress) ?></strong>, chúng tôi gồm có:
            </div>

            <!-- BÊN A -->
            <div class="party-box">
                <div class="clause-item"><strong>BÊN CHO THUÊ (BÊN A):</strong></div>
                <table>
                    <tr>
                        <td style="width: 160px;">Đại diện quản lý:</td>
                        <td><strong><?= h($managerName) ?></strong></td>
                    </tr>
                    <tr>
                        <td>Số điện thoại:</td>
                        <td><?= h($managerPhone) ?></td>
                    </tr>
                    <tr>
                        <td>Địa chỉ cơ sở:</td>
                        <td><?= h($buildingAddress) ?> (<?= h($buildingName) ?>)</td>
                    </tr>
                </table>
            </div>

            <!-- BÊN B -->
            <div class="party-box">
                <div class="clause-item"><strong>BÊN THUÊ PHÒNG (BÊN B):</strong></div>
                <table>
                    <tr>
                        <td style="width: 160px;">Ông/Bà:</td>
                        <td><strong><?= h($tenantName) ?></strong></td>
                    </tr>
                    <tr>
                        <td>Số điện thoại:</td>
                        <td><?= h($tenantPhone) ?></td>
                    </tr>
                    <tr>
                        <td>Địa chỉ Email:</td>
                        <td><?= h($tenantEmail) ?></td>
                    </tr>
                    <tr>
                        <td>Thuê căn phòng:</td>
                        <td><strong>Phòng <?= h($roomNumber) ?></strong> thuộc cơ sở <?= h($buildingName) ?></td>
                    </tr>
                </table>
            </div>

            <div class="clause-item" style="margin-top: 10px;">
                Hai bên cùng thỏa thuận và thống nhất ký kết Hợp đồng thuê phòng trọ với các điều khoản chi tiết như sau:
            </div>

            <!-- ĐIỀU 1 -->
            <div class="section-title">ĐIỀU 1: PHÒNG THUÊ VÀ THỜI HẠN THUÊ</div>
            <div class="clause-item">
                1.1. Bên A đồng ý cho Bên B thuê phòng số <strong><?= h($roomNumber) ?></strong> tại tòa nhà <strong><?= h($buildingName) ?></strong> để sử dụng vào mục đích lưu trú sinh hoạt hợp pháp.<br>
                1.2. Thời hạn thuê là từ ngày <strong><?= h($startDate) ?></strong> đến ngày <strong><?= h($endDate) ?></strong>.<br>
                1.3. Khi hết hạn hợp đồng, nếu Bên B có nhu cầu tiếp tục thuê và thực hiện đầy đủ nghĩa vụ, Bên B được quyền ưu tiên gia hạn hợp đồng mới trước ít nhất 15 ngày.
            </div>

            <!-- ĐIỀU 2 -->
            <div class="section-title">ĐIỀU 2: GIÁ THUÊ, TIỀN ĐẶT CỌC VÀ HÌNH THỨC THANH TOÁN</div>
            <div class="clause-item">
                2.1. Giá thuê phòng được hai bên thống nhất là: <strong><?= money($monthlyRent) ?>/tháng</strong> (Bằng chữ: <em><?= money($monthlyRent) ?></em>).<br>
                2.2. Tiền đặt cọc giữ chỗ và bảo đảm thực hiện hợp đồng là: <strong><?= money($depositAmount) ?></strong> (Đã hoàn tất đóng: <?= $depositPercent ?>%). Khoản tiền đặt cọc này không được cấn trừ vào tiền thuê nhà hàng tháng và sẽ được Bên A hoàn trả đầy đủ cho Bên B khi kết thúc hợp đồng theo đúng quy định tại Điều 5.<br>
                2.3. Kỳ thanh toán: Bên B có nghĩa vụ thanh toán tiền phòng và chi phí dịch vụ từ ngày 01 đến ngày 05 của tháng theo hóa đơn phát hành.
            </div>

            <!-- ĐIỀU 3 -->
            <div class="section-title">ĐIỀU 3: ĐƠN GIÁ DỊCH VỤ, ĐIỆN NƯỚC</div>
            <div class="clause-item">
                3.1. Tiền điện: Áp dụng mức <strong>3.500 đ/kWh</strong>, tính theo chỉ số công tơ điện riêng của từng phòng chốt ngày cuối tháng.<br>
                3.2. Tiền nước: Áp dụng mức <strong>20.000 đ/khối</strong> (hoặc tính theo số lượng người ở đăng ký thực tế theo bảng giá niêm yết).<br>
                3.3. Các chi phí tiện ích khác (Internet, rác thải sinh hoạt, vệ sinh chung, phí gửi xe) được thu minh bạch theo đúng Bảng đơn giá dịch vụ công khai của tòa nhà.
            </div>

            <!-- ĐIỀU 4 -->
            <div class="section-title">ĐIỀU 4: TRÁCH NHIỆM VÀ QUYỀN HẠN CỦA HAI BÊN</div>
            <div class="clause-item">
                4.1. <strong>Trách nhiệm Bên A:</strong> Bàn giao phòng và trang thiết bị hoạt động bình thường; đảm bảo an ninh trật tự khu nhà; kịp thời bảo trì, khắc phục các sự cố điện nước kỹ thuật chung không do lỗi của Bên B gây ra.<br>
                4.2. <strong>Trách nhiệm Bên B:</strong> Sử dụng phòng đúng mục đích cư trú; giữ gìn vệ sinh chung; nghiêm cấm tàng trữ chất cấm, chất cháy nổ; chấp hành nghiêm quy định phòng cháy chữa cháy; thanh toán đầy đủ tiền thuê và dịch vụ đúng hạn.
            </div>

            <!-- ĐIỀU 5 -->
            <div class="section-title">ĐIỀU 5: CHẤM DỨT HỢP ĐỒNG VÀ HOÀN TIỀN CỌC</div>
            <div class="clause-item">
                5.1. Hợp đồng chấm dứt khi hết thời hạn thỏa thuận và hai bên tiến hành bàn giao lại phòng.<br>
                5.2. Nếu một trong hai bên muốn chấm dứt hợp đồng trước thời hạn thì phải thông báo bằng văn bản hoặc thông báo qua hệ thống trước ít nhất 30 ngày.<br>
                5.3. Sau khi Bên B bàn giao lại phòng nguyên trạng, hoàn tất nghĩa vụ tài chính và các khoản điện nước, Bên A sẽ hoàn trả 100% số tiền đặt cọc cho Bên B trong vòng 03 ngày làm việc.
            </div>

            <!-- ĐIỀU 6 -->
            <div class="section-title">ĐIỀU 6: ĐIỀU KHOẢN THI HÀNH</div>
            <div class="clause-item">
                6.1. Hợp đồng này được lập thành 02 (hai) bản có giá trị pháp lý như nhau, mỗi bên giữ 01 bản để thực hiện.<br>
                6.2. Hợp đồng này có hiệu lực kể từ ngày hai bên hoàn tất việc ký kết.
            </div>

            <!-- KHỐI CHỮ KÝ -->
            <div class="signature-section">
                <div class="sign-col">
                    <div class="sign-title">ĐẠI DIỆN BÊN CHO THUÊ (BÊN A)</div>
                    <div class="sign-sub">(Ký, ghi rõ họ tên và đóng dấu)</div>
                    <div>
                        <div class="seal-stamp"><i class="fa-solid fa-stamp me-1"></i> STAYMATE ĐÃ PHÊ DUYỆT</div>
                    </div>
                    <div class="sign-name" style="margin-top: 15px;"><?= h($managerName) ?></div>
                </div>

                <div class="sign-col">
                    <div class="sign-title">ĐẠI DIỆN BÊN THUÊ PHÒNG (BÊN B)</div>
                    <div class="sign-sub">(Ký và ghi rõ họ tên)</div>
                    <div>
                        <span style="font-family:'Segoe Script', cursive, sans-serif; font-size:18px; color:#1e40af; font-weight:bold;">
                            <?= h($tenantName) ?>
                        </span>
                    </div>
                    <div class="sign-name" style="margin-top: 25px;"><?= h($tenantName) ?></div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
