<?php
/**
 * RESEED DỮ LIỆU CHỈ RIÊNG TÒA A (XÓA HẾT TÒA B & C)
 * - Kỳ 08-2026 & Kỳ 09-2026: Đầy đủ chi tiết Điện Nước, Hóa Đơn, Chi tiết dịch vụ (invoice_details), Chi phí bên thứ 3.
 * - Kỳ 10-2026: CHƯA CHỐT (để trống chỉ số điện nước và hóa đơn để demo chốt số & lập hóa đơn).
 */

require_once __DIR__ . '/../app/Config/Database.php';

$db = Database::getConnection();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec("SET FOREIGN_KEY_CHECKS = 0");

echo "1. Dọn dẹp toàn bộ dữ liệu cũ (Xóa sạch Tòa B & C)...\n";
$tables = [
    'invoice_details',
    'invoices',
    'utilities_readings',
    'third_party_expenses',
    'contracts',
    'tenant_requests',
    'tenant_contact_leads',
    'room_services',
    'rooms',
    'users',
    'buildings',
    'services'
];

foreach ($tables as $tbl) {
    try {
        $db->exec("TRUNCATE TABLE `$tbl`");
    } catch (PDOException $e) {
        $db->exec("DELETE FROM `$tbl`");
    }
}

echo "2. Khởi tạo danh mục Dịch vụ chi tiết (services)...\n";
$services = [
    [1, 'Tiền điện', 3500.00, 'kWh', 1],
    [2, 'Tiền nước', 25000.00, 'm³', 1],
    [3, 'Internet cáp quang', 100000.00, 'Phòng', 1],
    [4, 'Thang máy', 80000.00, 'Phòng', 1],
    [5, 'Vệ sinh hành lang', 30000.00, 'Phòng', 1],
    [6, 'Gửi xe máy', 100000.00, 'Xe', 1],
    [7, 'Máy giặt chung', 50000.00, 'Phòng', 1],
];
$insSrv = $db->prepare("INSERT INTO services (id, service_name, unit_price, unit, is_active) VALUES (?, ?, ?, ?, ?)");
foreach ($services as $s) {
    $insSrv->execute($s);
}
// Tổng phí dịch vụ cố định hàng tháng (Internet 100k + Thang máy 80k + Vệ sinh 30k + Gửi xe 100k + Máy giặt 50k = 360.000đ)
$otherServicesFixed = [
    [3, 'Internet cáp quang', 100000.00, 'Phòng'],
    [4, 'Thang máy', 80000.00, 'Phòng'],
    [5, 'Vệ sinh hành lang', 30000.00, 'Phòng'],
    [6, 'Gửi xe máy', 100000.00, 'Xe'],
    [7, 'Máy giặt chung', 50000.00, 'Phòng'],
];
$otherServicesTotal = 360000.00;

echo "3. Khởi tạo duy nhất Tòa A (buildings)...\n";
$insBld = $db->prepare("INSERT INTO buildings (id, name, address, manager_id) VALUES (?, ?, ?, ?)");
$insBld->execute([1, 'Tòa A - StayMate Sunrise', 'Số 128 Cầu Giấy, Quận Cầu Giấy, TP. Hà Nội', 2]);

echo "4. Khởi tạo 15 Phòng trọ thuộc Tòa A (rooms & room_services)...\n";
$insRoom = $db->prepare("INSERT INTO rooms (id, building_id, room_number, price, status, description) VALUES (?, ?, ?, ?, ?, ?)");
$insRoomSrv = $db->prepare("INSERT INTO room_services (room_id, service_name, unit_price, unit) VALUES (?, ?, ?, ?)");

$roomsConfig = [
    // Tầng 1: 6 phòng (4 rented, 1 available, 1 maintenance)
    [1,  1, 'A101', 3000000.00, 'rented',      'Phòng tiêu chuẩn tầng 1 | Diện tích: 22m2 | Tối đa: 2 người | Điều hòa, nóng lạnh, giường tủ, khóa vân tay.'],
    [2,  1, 'A102', 3200000.00, 'rented',      'Phòng ban công thoáng tầng 1 | Diện tích: 25m2 | Tối đa: 2 người | Ban công đón nắng, bàn làm việc, kệ bếp.'],
    [3,  1, 'A103', 3200000.00, 'available',   'Phòng trống sạch sẽ tầng 1 | Diện tích: 25m2 | Tối đa: 2 người | Vừa sơn mới, sẵn sàng bàn giao ngay.'],
    [4,  1, 'A104', 3500000.00, 'rented',      'Phòng Studio full nội thất tầng 1 | Diện tích: 28m2 | Tối đa: 3 người | Bếp từ, hút mùi, tủ lạnh, sofa.'],
    [5,  1, 'A105', 3500000.00, 'rented',      'Phòng góc 2 mặt thoáng tầng 1 | Diện tích: 30m2 | Tối đa: 3 người | View sân vườn, đầy đủ tiện nghi cao cấp.'],
    [6,  1, 'A106', 2800000.00, 'maintenance', 'Phòng tiêu chuẩn tầng 1 | Diện tích: 20m2 | Đang bảo trì đường ống nước và bảo dưỡng điều hòa định kỳ.'],

    // Tầng 2: 5 phòng (3 rented, 2 available)
    [7,  1, 'A201', 3000000.00, 'rented',      'Phòng tiêu chuẩn tầng 2 | Diện tích: 22m2 | Tối đa: 2 người | Điều hòa Inverter, giường đệm cao cấp.'],
    [8,  1, 'A202', 3200000.00, 'rented',      'Phòng ban công tầng 2 | Diện tích: 25m2 | Tối đa: 2 người | Có máy giặt riêng ngoài ban công.'],
    [9,  1, 'A203', 3000000.00, 'available',   'Phòng trống tầng 2 | Diện tích: 22m2 | Tối đa: 2 người | Nội thất cơ bản đầy đủ, cửa khóa vân tay.'],
    [10, 1, 'A204', 3500000.00, 'rented',      'Phòng VIP Studio tầng 2 | Diện tích: 30m2 | Tối đa: 3 người | Sofa thông minh, bếp từ đôi, tủ lạnh 2 cánh.'],
    [11, 1, 'A205', 3200000.00, 'available',   'Phòng trống ban công tầng 2 | Diện tích: 25m2 | Tối đa: 2 người | Thoáng mát, yên tĩnh, sẵn sàng đón khách.'],

    // Tầng 3: 4 phòng (4 rented)
    [12, 1, 'A301', 3200000.00, 'rented',      'Phòng ban công tầng 3 | Diện tích: 25m2 | Tối đa: 2 người | View toàn cảnh Cầu Giấy, ánh sáng tự nhiên.'],
    [13, 1, 'A302', 3000000.00, 'rented',      'Phòng tiêu chuẩn tầng 3 | Diện tích: 22m2 | Tối đa: 2 người | Đầy đủ điều hòa, nóng lạnh, giường tủ.'],
    [14, 1, 'A303', 3500000.00, 'rented',      'Phòng Studio tầng 3 | Diện tích: 28m2 | Tối đa: 3 người | Full nội thất cao cấp, mới nhận bàn giao.'],
    [15, 1, 'A304', 3000000.00, 'rented',      'Phòng tiêu chuẩn tầng 3 | Diện tích: 22m2 | Tối đa: 2 người | Khách vừa đặt cọc giữ chỗ 5 ngày.'],
];

foreach ($roomsConfig as $rm) {
    $insRoom->execute($rm);
    $rId = $rm[0];
    $insRoomSrv->execute([$rId, 'Tiền điện', 3500.00, 'kWh']);
    $insRoomSrv->execute([$rId, 'Tiền nước', 25000.00, 'm³']);
    foreach ($otherServicesFixed as $os) {
        $insRoomSrv->execute([$rId, $os[1], $os[2], $os[3]]);
    }
}

echo "5. Khởi tạo Tài khoản Admin, Quản lý Tòa A & Khách thuê Tòa A (users)...\n";
$pwdAdmin  = password_hash('123456', PASSWORD_DEFAULT);
$pwdMgr    = password_hash('123456', PASSWORD_DEFAULT);
$pwdTenant = password_hash('123', PASSWORD_DEFAULT);

$insUser = $db->prepare("
    INSERT INTO users (
        id, username, password, full_name, phone, email, role,
        preferred_room_id, cccd, birthday, gender, occupation,
        hometown, emergency_contact, status, roommate_name, roommate_phone
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

// Ban quản lý (Chỉ Admin và Quản lý Tòa A)
$insUser->execute([1, 'admin',    $pwdAdmin, 'Quản Trị Viên Hệ Thống', '0901234567', 'admin@staymate.vn',    'admin',   null, '001090000001', '1990-01-15', 'Nam', 'Quản trị hệ thống', 'Hà Nội',   '0988776655', 'active', null, null]);
$insUser->execute([2, 'quanly_a', $pwdMgr,   'Nguyễn Văn Quân',        '0912345678', 'quanly_a@staymate.vn', 'manager', null, '001092000002', '1992-05-20', 'Nam', 'Quản lý Tòa A',     'Bắc Ninh', '0912345679', 'active', null, null]);

// 11 Khách đang thuê tại Tòa A
$activeTenants = [
    [5,  '0901000001', 'Nguyễn Tiến Đạt',   '0901000001', 'dat.nt@gmail.com',   1,  '001201001001', '2001-03-12', 'Nam', 'Kỹ sư Phần mềm FPT',   'Hà Nội',    '0981111001', 'Đang thuê', 'Trần Quốc Bảo',  '0971000111'],
    [6,  '0901000002', 'Lê Thị Thu Hương',  '0901000002', 'huong.lt@gmail.com', 2,  '001202001002', '2002-07-24', 'Nữ',  'Chuyên viên Marketing','Hải Dương', '0981111002', 'Đang thuê', 'Nguyễn Mai Chi', '0971000112'],
    [7,  '0901000004', 'Phạm Minh Hoàng',   '0901000004', 'hoang.pm@gmail.com', 4,  '001200001004', '2000-11-05', 'Nam', 'Kiến trúc sư nội thất','Nam Định',  '0981111004', 'Đang thuê', 'Lê Văn Tùng',   '0971000114'],
    [8,  '0901000005', 'Vũ Minh Tuấn',      '0901000005', 'tuan.vm@gmail.com',  5,  '001201001005', '2001-09-18', 'Nam', 'Chuyên viên Tài chính','Thái Bình', '0981111005', 'Đang thuê', null,             null],
    [9,  '0901000006', 'Hoàng Thị Mai',     '0901000006', 'mai.ht@gmail.com',   7,  '001203001006', '2003-02-14', 'Nữ',  'Sinh viên ĐH Quốc Gia','Nghệ An',   '0981111006', 'Đang thuê', 'Hoàng Thị Lan',  '0971000116'],
    [10, '0901000007', 'Đỗ Quang Huy',      '0901000007', 'huy.dq@gmail.com',   8,  '001201001007', '2001-06-30', 'Nam', 'Thiết kế đồ họa UI/UX','Hải Phòng', '0981111007', 'Đang thuê', null,             null],
    [11, '0901000009', 'Ngô Thanh Tùng',    '0901000009', 'tung.nt@gmail.com',  10, '001199001009', '1999-12-20', 'Nam', 'Trưởng nhóm Kinh doanh','Hà Nam',   '0981111009', 'Đang thuê', 'Ngô Minh Đức',   '0971000119'],
    [12, '0901000010', 'Bùi Thị Lan Anh',   '0901000010', 'lananh.bt@gmail.com',12, '001202001010', '2002-04-08', 'Nữ',  'Giáo viên Tiếng Anh',  'Vĩnh Phúc', '0981111010', 'Đang thuê', null,             null],
    [13, '0901000011', 'Đặng Quốc Khánh',   '0901000011', 'khanh.dq@gmail.com', 13, '001201001011', '2001-08-19', 'Nam', 'Lập trình viên Mobile','Phú Thọ',   '0981111011', 'Đang thuê', null,             null],
    [14, '0901000012', 'Trương Mỹ Linh',    '0901000012', 'linh.tm@gmail.com',  14, '001203001012', '2003-10-10', 'Nữ',  'Nhân viên Ngân hàng',  'Thanh Hóa', '0981111012', 'Đang thuê', null,             null],
    [15, '0901000013', 'Lý Hoàng Nam',      '0901000013', 'nam.lh@gmail.com',   15, '001202001013', '2002-01-25', 'Nam', 'Kỹ sư Cầu đường',      'Bắc Giang', '0981111013', 'Chờ nhận phòng', null,        null],
];
foreach ($activeTenants as $t) {
    $insUser->execute([
        $t[0], $t[1], $pwdTenant, $t[2], $t[3], $t[4], 'tenant',
        $t[5], $t[6], $t[7], $t[8], $t[9], $t[10], $t[11], $t[12], $t[13], $t[14]
    ]);
}

// 3 Khách đã thanh lý hợp đồng (Lịch sử phòng thanh lý Tòa A)
$terminatedTenants = [
    [16, '0901000021', 'Phan Văn Trị',   '0901000021', 'tri.pv@gmail.com',   3,  '001198001021', '1998-05-15', 'Nam', 'Kế toán trưởng',      'Thừa Thiên Huế', '0981111021', 'Đã chuyển đi', null, null],
    [17, '0901000022', 'Hồ Xuân Hương',  '0901000022', 'huong.hx@gmail.com', 9,  '001200001022', '2000-09-02', 'Nữ',  'Biên tập viên Báo chí','Nghệ An',       '0981111022', 'Đã chuyển đi', null, null],
    [18, '0901000023', 'Đoàn Thị Điểm',  '0901000023', 'diem.dt@gmail.com',  11, '001201001023', '2001-11-30', 'Nữ',  'Dược sĩ lâm sàng',    'Hưng Yên',       '0981111023', 'Đã chuyển đi', null, null],
];
foreach ($terminatedTenants as $t) {
    $insUser->execute([
        $t[0], $t[1], $pwdTenant, $t[2], $t[3], $t[4], 'tenant',
        $t[5], $t[6], $t[7], $t[8], $t[9], $t[10], $t[11], $t[12], $t[13], $t[14]
    ]);
}

// 3 Khách thuê mới ĐÃ CHỌN PHÒNG DỰ KIẾN nhưng CHƯA LẬP HỢP ĐỒNG (để test lập HĐ thủ công)
$pendingTenants = [
    [19, '0902000001', 'Trần Đình Khang', '0902000001', 'khang.td@gmail.com', 3,  '001204002001', '2004-03-22', 'Nam', 'Sinh viên ĐH Bách Khoa', 'Ninh Bình', '0982222001', 'Chờ nhận phòng', null, null],
    [20, '0902000002', 'Phạm Thảo Vy',    '0902000002', 'vy.pt@gmail.com',    9,  '001204002002', '2004-07-14', 'Nữ',  'Sinh viên ĐH Ngoại Thương','Quảng Ninh','0982222002', 'Chờ nhận phòng', null, null],
    [21, '0902000003', 'Đoàn Hữu Phước',  '0902000003', 'phuoc.dh@gmail.com', 11, '001203002003', '2003-12-01', 'Nam', 'Chuyên viên QA/Tester',   'Đà Nẵng',  '0982222003', 'Chờ nhận phòng', null, null],
];
foreach ($pendingTenants as $t) {
    $insUser->execute([
        $t[0], $t[1], $pwdTenant, $t[2], $t[3], $t[4], 'tenant',
        $t[5], $t[6], $t[7], $t[8], $t[9], $t[10], $t[11], $t[12], $t[13], $t[14]
    ]);
}

echo "6. Khởi tạo Hợp đồng Tòa A đầy đủ các trường hợp nghiệp vụ (contracts)...\n";
$insContract = $db->prepare("
    INSERT INTO contracts (
        id, contract_code, contract_type, parent_contract_id,
        room_id, tenant_id, start_date, end_date, deposit_amount,
        deposit_date, deposit_paid_percent, deposit_status, monthly_rent,
        first_month_rent_paid, first_month_invoice_id, electricity_rate,
        water_rate, service_fee, signature_type, signature_status,
        signed_at, renewal_status, terminated_at, termination_reason,
        notes, status, first_month_paid_at, first_month_paid_note,
        unpaid_notice_sent, unpaid_notice_sent_at, termination_verification_case,
        refund_confirmed_invoice, unpaid_notice_resolved_at, created_at
    ) VALUES (
        ?, ?, ?, ?,
        ?, ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?,
        ?, ?, ?
    )
");

$soonEndDate = date('Y-m-d', strtotime('+12 days'));
$overdueStartDate = date('Y-m-d', strtotime('-8 days'));
$overdueEndDate = date('Y-m-d', strtotime($overdueStartDate . ' +6 months'));
$holdStartDate = date('Y-m-d', strtotime('-1 days'));
$holdEndDate = date('Y-m-d', strtotime($holdStartDate . ' +5 days'));

$contractsData = [
    // 8 Hợp đồng chính thức ổn định từ 01/08/2026 (Đã cọc 100%, đã thu tiền tháng đầu)
    [1,  'HD-2026-A101', 'official_lease', null, 1,  5,  '2026-08-01', '2027-02-01', 3000000.00, '2026-08-01', 100, 'held', 3000000.00, 1, null, 3500.00, 25000.00, 360000.00, 'paper', 'signed',  '2026-08-01 09:00:00', 'none', null, null, 'Hợp đồng chính thức 6 tháng phòng A101. Ở cùng: Trần Quốc Bảo (0971000111).', 'active', '2026-08-01 09:15:00', 'Đã thu tiền mặt khi nhận phòng', 0, null, 'none', 0, null, '2026-08-01 09:00:00'],
    [2,  'HD-2026-A102', 'official_lease', null, 2,  6,  '2026-08-01', '2027-02-01', 3200000.00, '2026-08-01', 100, 'held', 3200000.00, 1, null, 3500.00, 25000.00, 360000.00, 'paper', 'signed',  '2026-08-01 09:30:00', 'none', null, null, 'Hợp đồng chính thức 6 tháng phòng A102. Ở cùng: Nguyễn Mai Chi (0971000112).', 'active', '2026-08-01 09:40:00', 'Khách chuyển khoản Vietcombank', 0, null, 'none', 0, null, '2026-08-01 09:30:00'],
    [3,  'HD-2026-A104', 'official_lease', null, 4,  7,  '2026-08-01', '2027-02-01', 3500000.00, '2026-08-01', 100, 'held', 3500000.00, 1, null, 3500.00, 25000.00, 360000.00, 'paper', 'signed',  '2026-08-01 10:00:00', 'none', null, null, 'Hợp đồng chính thức 6 tháng phòng Studio A104.', 'active', '2026-08-01 10:15:00', 'Đã thu đủ tiền phòng tháng đầu', 0, null, 'none', 0, null, '2026-08-01 10:00:00'],
    [4,  'HD-2026-A105', 'official_lease', null, 5,  8,  '2026-08-01', '2027-02-01', 3500000.00, '2026-08-01', 100, 'held', 3500000.00, 1, null, 3500.00, 25000.00, 360000.00, 'paper', 'signed',  '2026-08-01 10:30:00', 'none', null, null, 'Hợp đồng chính thức 6 tháng phòng góc A105.', 'active', '2026-08-01 10:45:00', 'Đã thu tiền mặt', 0, null, 'none', 0, null, '2026-08-01 10:30:00'],
    [5,  'HD-2026-A201', 'official_lease', null, 7,  9,  '2026-08-01', '2027-02-01', 3000000.00, '2026-08-01', 100, 'held', 3000000.00, 1, null, 3500.00, 25000.00, 360000.00, 'paper', 'signed',  '2026-08-01 11:00:00', 'none', null, null, 'Hợp đồng chính thức 6 tháng phòng A201.', 'active', '2026-08-01 11:10:00', 'Khách chuyển khoản MB Bank', 0, null, 'none', 0, null, '2026-08-01 11:00:00'],
    [6,  'HD-2026-A202', 'official_lease', null, 8,  10, '2026-08-01', '2027-02-01', 3200000.00, '2026-08-01', 100, 'held', 3200000.00, 1, null, 3500.00, 25000.00, 360000.00, 'paper', 'signed',  '2026-08-01 14:00:00', 'none', null, null, 'Hợp đồng chính thức 6 tháng phòng A202.', 'active', '2026-08-01 14:15:00', 'Đã thu đủ tháng đầu', 0, null, 'none', 0, null, '2026-08-01 14:00:00'],
    [7,  'HD-2026-A204', 'official_lease', null, 10, 11, '2026-08-01', '2027-02-01', 3500000.00, '2026-08-01', 100, 'held', 3500000.00, 1, null, 3500.00, 25000.00, 360000.00, 'paper', 'signed',  '2026-08-01 15:00:00', 'none', null, null, 'Hợp đồng chính thức 6 tháng phòng VIP A204.', 'active', '2026-08-01 15:10:00', 'Đã thu chuyển khoản', 0, null, 'none', 0, null, '2026-08-01 15:00:00'],
    [8,  'HD-2026-A301', 'official_lease', null, 12, 12, '2026-05-20', $soonEndDate, 3200000.00, '2026-05-20', 100, 'held', 3200000.00, 1, null, 3500.00, 25000.00, 360000.00, 'paper', 'signed',  '2026-05-20 09:00:00', 'none', null, null, 'Hợp đồng sắp đến hạn gia hạn (còn 12 ngày).', 'active', '2026-05-20 09:15:00', 'Đã thu tiền mặt', 0, null, 'none', 0, null, '2026-05-20 09:00:00'],
    [9,  'HD-2026-A302', 'official_lease', null, 13, 13, '2026-08-01', '2027-02-01', 3000000.00, '2026-08-01', 100, 'held', 3000000.00, 1, null, 3500.00, 25000.00, 360000.00, 'paper', 'signed',  '2026-08-01 16:00:00', 'none', null, null, 'Hợp đồng chính thức 6 tháng phòng A302.', 'active', '2026-08-01 16:15:00', 'Đã thu tiền mặt', 0, null, 'none', 0, null, '2026-08-01 16:00:00'],

    // Trường hợp đặc biệt 1: Hợp đồng quá 5 ngày đầu chưa đóng tiền phòng tháng đầu (A303)
    [10, 'HD-2026-A303', 'official_lease', null, 14, 14, $overdueStartDate, $overdueEndDate, 3500000.00, $overdueStartDate, 100, 'held', 3500000.00, 0, null, 3500.00, 25000.00, 360000.00, 'paper', 'pending', null, 'none', null, null, 'Khách đã qua 5 ngày đầu nhưng chưa đóng tiền phòng tháng đầu (cần gửi xác nhận sang trang Hợp đồng).', 'active', null, null, 0, null, 'none', 0, null, $overdueStartDate . ' 09:00:00'],

    // Trường hợp đặc biệt 2: Hợp đồng 1 - Đặt cọc giữ chỗ 5 ngày (Cọc 50%) đang hiệu lực (A304)
    [11, 'HD-HOLD-A304', 'deposit_hold',   null, 15, 15, $holdStartDate,    $holdEndDate,    1500000.00, $holdStartDate,     50, 'held', 3000000.00, 0, null, 3500.00, 25000.00, 360000.00, 'paper', 'pending', null, 'none', null, null, '[HĐ 1 - Đặt cọc giữ chỗ 50% tiền cọc (1.500.000đ). Chờ ký tiếp HĐ 2 chính thức]', 'active', null, null, 0, null, 'none', 0, null, $holdStartDate . ' 10:00:00'],

    // Trường hợp đặc biệt 3, 4, 5: Lịch sử Phòng Thanh Lý (Hoàn cọc 100%, Mất cọc, Hết hạn 5 ngày tự hủy)
    [12, 'HD-TERM-A103', 'official_lease', null, 3,  16, '2026-02-01', '2026-08-01', 3200000.00, '2026-02-01', 100, 'refunded',  3200000.00, 1, null, 3500.00, 25000.00, 360000.00, 'paper', 'signed', '2026-02-01 08:30:00', 'none', '2026-08-01', 'Hết thời hạn thuê 6 tháng, khách không gia hạn và bàn giao phòng tốt - Đã hoàn 100% tiền cọc (3.200.000đ).', 'Biên bản thanh lý tốt, không hư hại tài sản.', 'terminated', '2026-02-01 08:45:00', 'Đã thu đủ', 1, '2026-08-01 09:00:00', 'expired_no_renew', 0, '2026-08-01 10:00:00', '2026-02-01 08:30:00'],
    [13, 'HD-TERM-A203', 'official_lease', null, 9,  17, '2026-04-01', '2026-10-01', 3000000.00, '2026-04-01', 100, 'forfeited', 3000000.00, 1, null, 3500.00, 25000.00, 360000.00, 'paper', 'signed', '2026-04-01 09:00:00', 'none', '2026-07-25', 'Khách tự ý trả phòng trước hạn hợp đồng (01/10/2026) - Không được hoàn tiền cọc (Mất cọc 3.000.000đ).', 'Vi phạm thời hạn cam kết thuê 6 tháng.', 'terminated', '2026-04-01 09:15:00', 'Đã thu đủ', 1, '2026-07-25 14:00:00', 'early_move_out', 1, '2026-07-25 15:00:00', '2026-04-01 09:00:00'],
    [14, 'HD-HOLD-A205', 'deposit_hold',   null, 11, 18, '2026-09-10', '2026-09-15', 1600000.00, '2026-09-10',  50, 'forfeited', 3200000.00, 0, null, 3500.00, 25000.00, 360000.00, 'paper', 'pending', null, 'none', '2026-09-16', 'Quá hạn 5 ngày đặt cọc giữ chỗ mà khách không đến ký Hợp đồng chính thức - Tự động hủy.', 'Khách báo bận công tác xa không nhận phòng.', 'expired', null, null, 0, null, 'none', 0, null, '2026-09-10 11:00:00'],
];

foreach ($contractsData as $cd) {
    $insContract->execute($cd);
}

echo "7. Khởi tạo Chỉ số Điện Nước & Hóa Đơn chi tiết cho Kỳ 08-2026 và Kỳ 09-2026 (Kỳ 10-2026 để trống chưa chốt)...\n";

$insReading = $db->prepare("
    INSERT INTO utilities_readings (id, room_id, month_year, electric_old, electric_new, water_old, water_new, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$insInvoice = $db->prepare("
    INSERT INTO invoices (
        id, room_id, contract_id, month_year, room_price, electric_amount, water_amount, service_fee,
        total_amount, status, due_date, paid_at, payment_method, payment_note, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$insDetail = $db->prepare("
    INSERT INTO invoice_details (invoice_id, service_id, quantity, applied_price, subtotal)
    VALUES (?, ?, ?, ?, ?)
");

// Danh sách 9 phòng thuê ổn định có đủ dữ liệu cả Kỳ 08-2026 và Kỳ 09-2026
// Cấu trúc: [room_id, contract_id, room_price, e_start, e_aug, e_sep, w_start, w_aug, w_sep, sep_status, sep_method, sep_note]
$monthlyPlan = [
    [1,  1, 3000000.00, 210, 285, 365, 18, 23, 29, 'paid',   'transfer', 'Khách Nguyễn Tiến Đạt chuyển khoản Vietcombank đúng hạn'],
    [2,  2, 3200000.00, 180, 260, 348, 15, 20, 26, 'unpaid', null,       'Chờ khách Lê Thị Thu Hương thanh toán kỳ 09-2026'],
    [4,  3, 3500000.00, 320, 415, 510, 24, 31, 38, 'paid',   'transfer', 'Khách Phạm Minh Hoàng thanh toán qua QR Banking'],
    [5,  4, 3500000.00, 150, 230, 312, 12, 17, 22, 'unpaid', null,       'Đã gửi thông báo nhắc thanh toán kỳ 09-2026'],
    [7,  5, 3000000.00, 240, 310, 384, 20, 25, 30, 'paid',   'cash',     'Thu tiền mặt trực tiếp tại văn phòng Tòa A'],
    [8,  6, 3200000.00, 195, 270, 350, 16, 21, 27, 'paid',   'transfer', 'Khách Đỗ Quang Huy chuyển khoản Techcombank'],
    [10, 7, 3500000.00, 290, 390, 495, 22, 29, 36, 'paid',   'transfer', 'Khách Ngô Thanh Tùng thanh toán chuyển khoản'],
    [12, 8, 3200000.00, 410, 480, 556, 32, 37, 42, 'paid',   'cash',     'Khách Bùi Thị Lan Anh đóng tiền mặt'],
    [13, 9, 3000000.00, 160, 228, 302, 14, 18, 23, 'unpaid', null,       'Chưa thu tiền kỳ 09-2026 (còn nợ)'],
];

$readingId = 1;
$invoiceId = 1;

// --- A. KỲ 08-2026 (Tất cả 9 phòng đã chốt số & đã thanh toán đủ 100%) ---
foreach ($monthlyPlan as $mp) {
    [$rId, $cId, $rPrice, $e0, $eAug, $eSep, $w0, $wAug, $wSep] = $mp;

    $insReading->execute([$readingId++, $rId, '08-2026', $e0, $eAug, $w0, $wAug, '2026-08-28 09:00:00']);

    $eUsed = $eAug - $e0;
    $wUsed = $wAug - $w0;
    $eCost = $eUsed * 3500.00;
    $wCost = $wUsed * 25000.00;
    $total = $rPrice + $eCost + $wCost + $otherServicesTotal;

    $insInvoice->execute([
        $invoiceId, $rId, $cId, '08-2026',
        $rPrice, $eCost, $wCost, $otherServicesTotal,
        $total, 'paid', '2026-09-10', '2026-09-05 10:30:00',
        'transfer', 'Đã thanh toán đầy đủ hóa đơn kỳ 08-2026', '2026-08-28 09:30:00'
    ]);

    // Chi tiết hóa đơn (invoice_details): Tiền phòng + Điện + Nước + 5 dịch vụ cố định
    $insDetail->execute([$invoiceId, null, 1, $rPrice, $rPrice]);
    $insDetail->execute([$invoiceId, 1, $eUsed, 3500.00, $eCost]);
    $insDetail->execute([$invoiceId, 2, $wUsed, 25000.00, $wCost]);
    foreach ($otherServicesFixed as $os) {
        $insDetail->execute([$invoiceId, $os[0], 1, $os[2], $os[2]]);
    }
    $invoiceId++;
}

// --- B. KỲ 09-2026 (9 phòng đã chốt & lập HĐ: 6 paid, 3 unpaid; + Phòng A303 đã chốt số chờ lập HĐ) ---
foreach ($monthlyPlan as $mp) {
    [$rId, $cId, $rPrice, $e0, $eAug, $eSep, $w0, $wAug, $wSep, $sepStatus, $sepMethod, $sepNote] = $mp;

    $insReading->execute([$readingId++, $rId, '09-2026', $eAug, $eSep, $wAug, $wSep, '2026-09-28 09:00:00']);

    $eUsed = $eSep - $eAug;
    $wUsed = $wSep - $wAug;
    $eCost = $eUsed * 3500.00;
    $wCost = $wUsed * 25000.00;
    $total = $rPrice + $eCost + $wCost + $otherServicesTotal;

    $paidAt = ($sepStatus === 'paid') ? '2026-10-05 14:20:00' : null;

    $insInvoice->execute([
        $invoiceId, $rId, $cId, '09-2026',
        $rPrice, $eCost, $wCost, $otherServicesTotal,
        $total, $sepStatus, '2026-10-10', $paidAt,
        $sepMethod, $sepNote, '2026-09-28 09:30:00'
    ]);

    // Chi tiết hóa đơn (invoice_details)
    $insDetail->execute([$invoiceId, null, 1, $rPrice, $rPrice]);
    $insDetail->execute([$invoiceId, 1, $eUsed, 3500.00, $eCost]);
    $insDetail->execute([$invoiceId, 2, $wUsed, 25000.00, $wCost]);
    foreach ($otherServicesFixed as $os) {
        $insDetail->execute([$invoiceId, $os[0], 1, $os[2], $os[2]]);
    }
    $invoiceId++;
}

// Thêm 1 bản ghi Điện Nước Kỳ 09-2026 cho phòng A303 (room_id=14) ĐÃ CHỐT SỐ nhưng CHỜ LẬP HÓA ĐƠN
$insReading->execute([$readingId++, 14, '09-2026', 100, 165, 10, 14, '2026-09-29 10:00:00']);

// --- C. KỲ 10-2026: ĐỂ TRỐNG HOÀN TOÀN (CHƯA CHỐT SỐ ĐIỆN NƯỚC & CHƯA LẬP HÓA ĐƠN) ---

echo "8. Khởi tạo Bảng kê Khoản chi cho Bên thứ 3 của Tòa A kỳ 08-2026 và 09-2026 (third_party_expenses)...\n";
$insExp = $db->prepare("
    INSERT INTO third_party_expenses (
        month_year, expense_type, title, provider_name, quantity, unit,
        unit_price, amount, customer_amount, status, paid_at, notes, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

// Kỳ 08-2026
$insExp->execute(['08-2026', 'electricity', 'Tiền điện tổng trạm biến áp Tòa A (Kỳ 08/2026)', 'Công ty Điện lực Cầu Giấy (EVN HANOI)', 693, 'kWh', 2500.00, 1732500.00, 2425500.00, 'paid', '2026-08-30', 'Thanh toán hóa đơn điện tổng công tơ 3 pha Tòa A', '2026-08-30 09:00:00']);
$insExp->execute(['08-2026', 'water',       'Tiền nước sạch sinh hoạt Tòa A (Kỳ 08/2026)',   'Công ty Nước sạch Hà Nội (HAWACO)',      47,  'm³',  15000.00, 705000.00,  1175000.00, 'paid', '2026-08-30', 'Thanh toán tiền nước sạch đầu nguồn Tòa A',       '2026-08-30 09:15:00']);
$insExp->execute(['08-2026', 'internet',    'Cước cáp quang doanh nghiệp FTTH Tòa A',        'VNPT Cầu Giấy - Hà Nội',                 1,   'Gói', 650000.00, 650000.00, 900000.00,  'paid', '2026-08-28', 'Gói cáp quang tốc độ cao 500Mbps cho 3 tầng Tòa A','2026-08-28 10:00:00']);
$insExp->execute(['08-2026', 'elevator',    'Bảo trì định kỳ thang máy & chiếu sáng Tòa A',  'Công ty Thang máy MitsuLift Việt Nam',   1,   'Tháng',500000.00, 500000.00, 720000.00,  'paid', '2026-08-29', 'Bảo dưỡng cáp tải, tra dầu ray dẫn thang máy Tòa A','2026-08-29 14:00:00']);
$insExp->execute(['08-2026', 'cleaning',    'Dịch vụ vệ sinh hành lang & khu chung Tòa A',   'Công ty Môi Trường Xanh Cầu Giấy',       1,   'Tháng',450000.00, 450000.00, 720000.00,  'paid', '2026-08-29', 'Lau dọn hành lang, cầu thang, sân để xe Tòa A',    '2026-08-29 15:00:00']);

// Kỳ 09-2026
$insExp->execute(['09-2026', 'electricity', 'Tiền điện tổng trạm biến áp Tòa A (Kỳ 09/2026)', 'Công ty Điện lực Cầu Giấy (EVN HANOI)', 792, 'kWh', 2500.00, 1980000.00, 2772000.00, 'paid', '2026-09-30', 'Thanh toán hóa đơn điện tổng công tơ 3 pha Tòa A', '2026-09-30 09:00:00']);
$insExp->execute(['09-2026', 'water',       'Tiền nước sạch sinh hoạt Tòa A (Kỳ 09/2026)',   'Công ty Nước sạch Hà Nội (HAWACO)',      56,  'm³',  15000.00, 840000.00,  1400000.00, 'paid', '2026-09-30', 'Thanh toán tiền nước sạch đầu nguồn Tòa A',       '2026-09-30 09:15:00']);
$insExp->execute(['09-2026', 'internet',    'Cước cáp quang doanh nghiệp FTTH Tòa A',        'VNPT Cầu Giấy - Hà Nội',                 1,   'Gói', 650000.00, 650000.00, 900000.00,  'paid', '2026-09-28', 'Gói cáp quang tốc độ cao 500Mbps cho 3 tầng Tòa A','2026-09-28 10:00:00']);
$insExp->execute(['09-2026', 'elevator',    'Bảo trì định kỳ thang máy & chiếu sáng Tòa A',  'Công ty Thang máy MitsuLift Việt Nam',   1,   'Tháng',500000.00, 500000.00, 720000.00,  'paid', '2026-09-29', 'Bảo dưỡng định kỳ thang máy Tòa A tháng 09/2026',  '2026-09-29 14:00:00']);
$insExp->execute(['09-2026', 'cleaning',    'Dịch vụ vệ sinh hành lang & thu gom rác Tòa A', 'Công ty Môi Trường Xanh Cầu Giấy',       1,   'Tháng',450000.00, 450000.00, 720000.00,  'paid', '2026-09-29', 'Vệ sinh định kỳ thứ 2, 4, 6 và thu gom rác mỗi tối','2026-09-29 15:00:00']);

echo "9. Khởi tạo Yêu cầu khách thuê & Lịch hẹn xem phòng Tòa A...\n";
$insReq = $db->prepare("
    INSERT INTO tenant_requests (
        id, tenant_id, request_type, field_target, old_value, new_value, reason, status, manager_note, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$insReq->execute([1, 5, 'profile_update', 'phone', '0901000001', '0988112233', 'Em đổi sang số điện thoại mới để nhận thông báo hóa đơn phòng A101.', 'pending', null, '2026-10-01 15:30:00']);
$insReq->execute([2, 6, 'profile_update', 'email', 'huong.lt@gmail.com', 'huong.le.mkt@gmail.com', 'Cập nhật email công việc mới.', 'approved', 'BQL Tòa A đã duyệt cập nhật.', '2026-09-25 09:15:00']);

$insLead = $db->prepare("
    INSERT INTO tenant_contact_leads (
        id, customer_name, phone, room_label, room_id, visit_time, note, status, created_at, contacted_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$insLead->execute([1, 'Trần Thu Thủy', '0977889900', 'Phòng A103 - Ban công tầng 1', 3,  '2026-10-05 15:00:00', 'Muốn xem phòng A103 vào chiều thứ 7 lúc 15h.', 'contacted', '2026-10-01 16:00:00', '2026-10-01 16:30:00']);
$insLead->execute([2, 'Nguyễn Văn Đạt', '0966554433', 'Phòng A203 - Tiêu chuẩn tầng 2', 9, '2026-10-06 10:00:00', 'Hỏi chỗ để xe máy và hẹn xem phòng A203.', 'new',       '2026-10-02 10:30:00', null]);

$db->exec("SET FOREIGN_KEY_CHECKS = 1");

echo "\n=======================================================\n";
echo "HOÀN TẤT LÀM MỚI DỮ LIỆU CHỈ RIÊNG TÒA A (STAYMATE SUNRISE)!\n";
echo "- Tòa nhà: Duy nhất Tòa A (Đã xóa sạch Tòa B & Tòa C)\n";
echo "- Phòng trọ Tòa A: 15 phòng (11 đang thuê, 3 phòng trống A103/A203/A205, 1 bảo trì A106)\n";
echo "- Kỳ 08-2026: 9 bản ghi Điện Nước + 9 Hóa đơn (Đã thu 100%) + 63 mục chi tiết dịch vụ + 5 khoản chi bên thứ 3\n";
echo "- Kỳ 09-2026: 10 bản ghi Điện Nước + 9 Hóa đơn (6 Đã thu, 3 Chưa thu) + 1 bản ghi chờ lập HĐ (A303) + 5 khoản chi bên thứ 3\n";
echo "- Kỳ 10-2026: CHƯA CHỐT (0 chỉ số, 0 hóa đơn - Sẵn sàng để chốt số và lập hóa đơn)\n";
echo "=======================================================\n";
