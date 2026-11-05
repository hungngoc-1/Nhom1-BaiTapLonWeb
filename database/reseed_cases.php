<?php
/**
 * RESEED DATA TOÀN DIỆN CHO TẤT CẢ CÁC TRANG & ĐÁP ỨNG ĐẦY ĐỦ TRƯỜNG HỢP KIỂM THỬ
 */

require_once __DIR__ . '/../app/Config/Database.php';

$db = Database::getConnection();
$db->exec("SET FOREIGN_KEY_CHECKS = 0");

echo "1. Dọn dẹp dữ liệu cũ...\n";
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

echo "2. Khởi tạo danh mục Dịch vụ (services)...\n";
$services = [
    [1, 'Tiền điện', 3500.00, 'kWh', 1],
    [2, 'Tiền nước', 80000.00, 'Người', 1],
    [3, 'Internet', 100000.00, 'Phòng', 1],
    [4, 'Thang máy', 150000.00, 'Phòng', 1],
    [5, 'Vệ sinh', 30000.00, 'Phòng', 1],
    [6, 'Gửi xe máy', 100000.00, 'Xe', 1],
    [7, 'Máy giặt chung', 50000.00, 'Người', 1],
    [8, 'Gửi ô tô', 1200000.00, 'Xe', 1],
    [9, 'Truyền hình cáp', 50000.00, 'Phòng', 1],
    [10, 'Phí quản lý vận hành', 200000.00, 'Phòng', 1],
    [11, 'Dọn phòng', 50000.00, 'Lượt', 1],
    [12, 'Phí thú cưng', 300000.00, 'Tháng', 1],
];
$insSrv = $db->prepare("INSERT INTO services (id, service_name, unit_price, unit, is_active) VALUES (?, ?, ?, ?, ?)");
foreach ($services as $s) {
    $insSrv->execute($s);
}

echo "3. Khởi tạo Ban Quản lý, Admin & Danh sách Khách thuê (users)...\n";
$pwdAdmin = password_hash('123456', PASSWORD_DEFAULT);
$pwdMgr = password_hash('123456', PASSWORD_DEFAULT);
$pwdTenant = password_hash('123', PASSWORD_DEFAULT);

$insUser = $db->prepare("
    INSERT INTO users (
        id, username, password, full_name, phone, email, role,
        preferred_room_id, cccd, birthday, gender, occupation,
        hometown, emergency_contact, status, roommate_name, roommate_phone
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

// Tài khoản Quản trị & Quản lý
$insUser->execute([1, 'admin', $pwdAdmin, 'Quản Trị Viên Hệ Thống', '0901234567', 'admin@staymate.vn', 'admin', null, '001090000001', '1990-01-15', 'Nam', 'Quản trị hệ thống', 'Hà Nội', '0988776655', 'active', null, null]);
$insUser->execute([2, 'quanly_a', $pwdMgr, 'Nguyễn Văn Quân', '0912345678', 'quanly_a@staymate.vn', 'manager', null, '001092000002', '1992-05-20', 'Nam', 'Quản lý Tòa A', 'Bắc Ninh', '0912345679', 'active', null, null]);
$insUser->execute([3, 'quanly_b', $pwdMgr, 'Trần Thị Bích', '0987654321', 'quanly_b@staymate.vn', 'manager', null, '001094000003', '1994-08-12', 'Nữ', 'Quản lý Tòa B', 'Hải Phòng', '0987654322', 'active', null, null]);
$insUser->execute([4, 'quanly_c', $pwdMgr, 'Lê Hoàng Nam', '0934567890', 'quanly_c@staymate.vn', 'manager', null, '001091000004', '1991-11-28', 'Nam', 'Quản lý Tòa C', 'Nam Định', '0934567891', 'active', null, null]);

// 4. Khởi tạo 3 Tòa nhà (buildings)
$insBld = $db->prepare("INSERT INTO buildings (id, name, address, manager_id) VALUES (?, ?, ?, ?)");
$buildingsData = [
    [1, 'Tòa A - StayMate Sunrise', 'Số 128 Cầu Giấy, Quận Cầu Giấy, TP. Hà Nội', 2],
    [2, 'Tòa B - StayMate Emerald', 'Số 45 Nguyễn Chí Thanh, Quận Đống Đa, TP. Hà Nội', 3],
    [3, 'Tòa C - StayMate Horizon', 'Số 88 Nguyễn Trãi, Quận Thanh Xuân, TP. Hà Nội', 4],
];
foreach ($buildingsData as $b) {
    $insBld->execute($b);
}

// 5. Khởi tạo 26 Phòng trọ (rooms)
echo "4. Khởi tạo 26 Phòng trọ với đầy đủ các trạng thái (rented, available, maintenance)...\n";
$insRoom = $db->prepare("INSERT INTO rooms (id, building_id, room_number, price, status, description) VALUES (?, ?, ?, ?, ?, ?)");
$insRoomSrv = $db->prepare("INSERT INTO room_services (room_id, service_name, unit_price, unit) VALUES (?, ?, ?, ?)");

$roomsConfig = [
    // Tòa A: 10 phòng (7 rented, 2 available, 1 maintenance)
    [1, 1, 'A101', 3000000.00, 'rented', 'Phòng tiêu chuẩn tầng 1, diện tích 22m2, tiện đi lại, đầy đủ điều hòa nóng lạnh.'],
    [2, 1, 'A102', 3200000.00, 'rented', 'Phòng ban công thoáng tầng 1, diện tích 25m2, đón gió tự nhiên, bàn làm việc.'],
    [3, 1, 'A103', 3200000.00, 'available', 'Phòng trống sạch sẽ vừa sơn mới tầng 1, diện tích 25m2, sẵn sàng đón khách vào ở ngay.'],
    [4, 1, 'A104', 3500000.00, 'rented', 'Phòng Studio full nội thất tầng 1, diện tích 28m2, bếp riêng, hút mùi, tủ lạnh.'],
    [5, 1, 'A105', 3500000.00, 'rented', 'Phòng góc 2 mặt thoáng tầng 1, diện tích 30m2, view công viên cây xanh.'],
    [6, 1, 'A201', 3000000.00, 'rented', 'Phòng tiêu chuẩn tầng 2, diện tích 22m2, điều hòa inveter, giường đệm cao cấp.'],
    [7, 1, 'A202', 3000000.00, 'rented', 'Phòng tiêu chuẩn tầng 2, diện tích 22m2, máy giặt riêng ngoài ban công.'],
    [8, 1, 'A203', 3000000.00, 'available', 'Phòng trống sạch sẽ tầng 2, đầy đủ nội thất cơ bản, cửa khóa vân tay an ninh.'],
    [9, 1, 'A204', 3500000.00, 'rented', 'Phòng VIP tầng 2, diện tích 30m2, sofa thông minh, bếp từ đôi cao cấp.'],
    [10, 1, 'A205', 3600000.00, 'maintenance', 'Phòng đang bảo trì hệ thống điều hòa và chống thấm ban công.'],

    // Tòa B: 8 phòng (6 rented, 1 available, 1 maintenance)
    [11, 2, 'B101', 4000000.00, 'rented', 'Căn hộ dịch vụ cao cấp tầng 1 Tòa B, diện tích 32m2, ban công riêng.'],
    [12, 2, 'B102', 4000000.00, 'rented', 'Căn hộ 1 ngủ 1 khách tầng 1, diện tích 35m2, bếp rộng rãi, tủ lạnh 2 cánh.'],
    [13, 2, 'B103', 3800000.00, 'rented', 'Phòng Studio tầng 1, diện tích 28m2, nội thất gỗ sồi cao cấp.'],
    [14, 2, 'B104', 3800000.00, 'available', 'Phòng trống tầng 1 Tòa B, diện tích 28m2, view thoáng, sẵn sàng ký hợp đồng.'],
    [15, 2, 'B201', 4200000.00, 'rented', 'Phòng ban công hướng Đông Nam tầng 2, đón nắng sớm, đầy đủ tiện nghi.'],
    [16, 2, 'B202', 4200000.00, 'rented', 'Phòng Studio cao cấp tầng 2, khóa cửa từ Kaadas, Smart TV 55 inch.'],
    [17, 2, 'B203', 4500000.00, 'rented', 'Căn góc 2 phòng ngủ tầng 2, diện tích 45m2, phù hợp gia đình trẻ.'],
    [18, 2, 'B204', 4000000.00, 'maintenance', 'Phòng đang sửa chữa thay mới gạch lát nền và hệ thống nước.'],

    // Tòa C: 8 phòng (6 rented, 2 available)
    [19, 3, 'C101', 2500000.00, 'rented', 'Phòng sinh viên giá tốt tầng 1 Tòa C, diện tích 20m2, khép kín sạch sẽ.'],
    [20, 3, 'C102', 2500000.00, 'rented', 'Phòng đơn tiêu chuẩn tầng 1, diện tích 20m2, có gác xép để đồ rộng rãi.'],
    [21, 3, 'C103', 2800000.00, 'available', 'Phòng trống tầng 1 Tòa C, diện tích 23m2, cửa sổ thoáng mát, giá rẻ sinh viên.'],
    [22, 3, 'C104', 2800000.00, 'rented', 'Phòng gác lửng tầng 1, diện tích 25m2, điều hòa mát lạnh, máy nước nóng.'],
    [23, 3, 'C201', 2700000.00, 'rented', 'Phòng ban công tầng 2, diện tích 22m2, phơi đồ riêng biệt, an ninh tốt.'],
    [24, 3, 'C202', 2700000.00, 'rented', 'Phòng khép kín tầng 2, diện tích 22m2, wifi tốc độ cao, camera an ninh 24/7.'],
    [25, 3, 'C203', 3000000.00, 'rented', 'Phòng lớn 2 người ở tầng 2, diện tích 28m2, giường đôi, 2 bàn học sinh viên.'],
    [26, 3, 'C204', 3000000.00, 'available', 'Phòng trống tầng 2 Tòa C, diện tích 28m2, mới sơn sửa sạch sẽ đón khách mới.'],
];

foreach ($roomsConfig as $r) {
    $insRoom->execute($r);
    // Gán dịch vụ mặc định cho phòng
    $insRoomSrv->execute([$r[0], 'Internet', 100000.00, 'Phòng']);
    $insRoomSrv->execute([$r[0], 'Thang máy', 150000.00, 'Phòng']);
    $insRoomSrv->execute([$r[0], 'Vệ sinh', 30000.00, 'Phòng']);
    $insRoomSrv->execute([$r[0], 'Phí quản lý vận hành', 200000.00, 'Phòng']);
}

// 6. Khởi tạo danh sách Khách thuê:
// Gồm 3 nhóm:
// Nhóm 1: Khách đang ở trọ (User ID 5 đến 23)
// Nhóm 2: Khách đã thanh lý phòng lưu trong lịch sử (User ID 24 đến 26)
// Nhóm 3: Khách mới đăng ký CHƯA CÓ HỢP ĐỒNG (User ID 27 đến 30)
echo "5. Khởi tạo danh sách Khách thuê đa dạng các trường hợp...\n";

$tenantsData = [
    // Nhóm 1: Đang ở trọ
    [5,  'kh_a101', 'Nguyễn Tiến Đạt',   '0901000001', 'dat.nt@gmail.com',   'Nam', '001201000001', '2001-03-15', 'Hà Nội',    'Kỹ sư phần mềm', 1],
    [6,  'kh_a102', 'Lê Thị Thu Hương',  '0901000002', 'huong.lt@gmail.com', 'Nữ',  '001202000002', '2002-07-22', 'Bắc Ninh',  'Chuyên viên Marketing', 2],
    [7,  'kh_a104', 'Phạm Thu Trang',    '0901000004', 'trang.pt@gmail.com', 'Nữ',  '001200000004', '2000-09-10', 'Nam Định',  'Kế toán tổng hợp', 4],
    [8,  'kh_a105', 'Vũ Minh Tuấn',      '0901000005', 'tuan.vm@gmail.com',  'Nam', '001199000005', '1999-12-05', 'Hải Dương', 'Thiết kế đồ họa', 5],
    [9,  'kh_a201', 'Hoàng Bảo Ngọc',   '0901000006', 'ngoc.hb@gmail.com',  'Nữ',  '001203000006', '2003-04-18', 'Thái Bình', 'Sinh viên ĐH Ngoại Thương', 6],
    [10, 'kh_a202', 'Đặng Quốc Anh',     '0901000007', 'anh.dq@gmail.com',   'Nam', '001201000007', '2001-08-30', 'Hà Nam',    'Lập trình viên Web', 7],
    [11, 'kh_a204', 'Bùi Quỳnh Chi',     '0901000009', 'chi.bq@gmail.com',   'Nữ',  '001202000009', '2002-11-25', 'Quảng Ninh','Nhân viên ngân hàng', 9],
    [12, 'kh_b101', 'Trịnh Đình Quang',  '0902000001', 'quang.td@gmail.com', 'Nam', '001198000010', '1998-02-14', 'Nghệ An',   'Kinh doanh tự do', 11],
    [13, 'kh_b102', 'Ngô Thảo Linh',     '0902000002', 'linh.nt@gmail.com',  'Nữ',  '001200000011', '2000-06-19', 'Thanh Hóa', 'Chuyên viên Nhân sự', 12],
    [14, 'kh_b103', 'Dương Hoàng Long',  '0902000003', 'long.dh@gmail.com',  'Nam', '001201000012', '2001-10-08', 'Hải Phòng', 'Chuyên viên IT', 13],
    [15, 'kh_b201', 'Võ Thị Mai Phương', '0902000005', 'phuong.vt@gmail.com','Nữ',  '001203000014', '2003-01-28', 'Vĩnh Phúc', 'Sinh viên ĐH Bách Khoa', 15],
    [16, 'kh_b202', 'Hồ Đức Thắng',      '0902000006', 'thang.hd@gmail.com', 'Nam', '001199000015', '1999-05-17', 'Phú Thọ',   'Trưởng nhóm Sale', 16],
    [17, 'kh_b203', 'Lý Gia Hân',        '0902000007', 'han.lg@gmail.com',   'Nữ',  '001202000016', '2002-09-03', 'Hà Nội',    'Nhiếp ảnh gia', 17],
    [18, 'kh_c101', 'Nguyễn Thị Minh Khai','0903000001','khai.ntm@gmail.com','Nữ',  '001204000018', '2004-03-08', 'Bắc Giang', 'Sinh viên ĐH Quốc Gia', 19],
    [19, 'kh_c102', 'Trần Văn Cường',    '0903000002', 'cuong.tv@gmail.com', 'Nam', '001201000019', '2001-07-15', 'Hòa Bình',  'Thực tập sinh Marketing', 20],
    [20, 'kh_c104', 'Lê Hữu Phước',     '0903000004', 'phuoc.lh@gmail.com', 'Nam', '001200000021', '2000-12-12', 'Hưng Yên',  'Kỹ sư xây dựng', 22],
    [21, 'kh_c201', 'Phạm Hồng Nhung',   '0903000005', 'nhung.ph@gmail.com', 'Nữ',  '001203000022', '2003-08-20', 'Thái Nguyên','Sinh viên Y Hà Nội', 23],
    [22, 'kh_c202', 'Hoàng Minh Tuấn',   '0903000006', 'tuan.hm@gmail.com',  'Nam', '001202000023', '2002-04-05', 'Lạng Sơn',  'Chuyên viên sáng tạo', 24],
    [23, 'kh_c203', 'Đỗ Thùy Trang',     '0903000007', 'trang.dt@gmail.com', 'Nữ',  '001201000024', '2001-11-30', 'Hà Nội',    'Nhân viên hành chính', 25],

    // Nhóm 2: Khách đã thanh lý phòng trong lịch sử
    [24, 'kh_thanhly_1', 'Trần Hoàng Long', '0909000001', 'long.th@gmail.com', 'Nam', '001198000091', '1998-04-12', 'Hải Phòng', 'Kỹ sư tự động hóa', null],
    [25, 'kh_thanhly_2', 'Dương Thị Trang',  '0909000002', 'trang.dt2@gmail.com','Nữ',  '001200000092', '2000-09-25', 'Bắc Ninh',  'Giao dịch viên ngân hàng', null],
    [26, 'kh_thanhly_3', 'Nguyễn Huy Hoàng', '0909000003', 'hoang.nh@gmail.com', 'Nam', '001197000093', '1997-12-01', 'Hà Nội',    'Kiến trúc sư', null],

    // Nhóm 3: Khách mới đăng ký CHƯA CÓ HỢP ĐỒNG (để chọn khi lập Hợp đồng mới)
    [27, 'kh_chuahd_1', 'Nguyễn Thị Bích Ngọc', '0933111222', 'ngoc.bich@gmail.com', 'Nữ',  '001204000101', '2004-06-15', 'Nam Định', 'Sinh viên mới nhập học', 3],
    [28, 'kh_chuahd_2', 'Trần Đình Khang',     '0933222333', 'khang.td@gmail.com',  'Nam', '001203000102', '2003-10-20', 'Thái Bình','Nhân viên mới chuyển công tác', 8],
    [29, 'kh_chuahd_3', 'Phạm Thảo Vy',        '0933444555', 'vy.pt@gmail.com',     'Nữ',  '001202000103', '2002-05-18', 'Hà Nam',   'Nhân viên văn phòng', 14],
    [30, 'kh_chuahd_4', 'Đoàn Hữu Phước',      '0933666777', 'phuoc.dh@gmail.com',  'Nam', '001201000104', '2001-08-09', 'Nghệ An',  'Kỹ sư CNTT', 21]
];

foreach ($tenantsData as $t) {
    $insUser->execute([
        $t[0], $t[1], $pwdTenant, $t[2], $t[3], $t[4], 'tenant',
        $t[10], $t[6], $t[7], $t[5], $t[9],
        $t[8], '0988000999', 'active', null, null
    ]);
}

// 7. Khởi tạo Hợp đồng (contracts)
echo "6. Khởi tạo danh mục Hợp đồng đầy đủ các tình huống nghiệp vụ...\n";
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

$contractId = 1;
$activeContracts = []; // map room_id => contract info

$activeRooms = [
    1 => 5,   // P.A101 - Nguyễn Tiến Đạt
    2 => 6,   // P.A102 - Lê Thị Thu Hương
    4 => 7,   // P.A104 - Phạm Thu Trang
    5 => 8,   // P.A105 - Vũ Minh Tuấn
    6 => 9,   // P.A201 - Hoàng Bảo Ngọc
    7 => 10,  // P.A202 - Đặng Quốc Anh
    9 => 11,  // P.A204 - Bùi Quỳnh Chi
    11 => 12, // P.B101 - Trịnh Đình Quang
    12 => 13, // P.B102 - Ngô Thảo Linh
    13 => 14, // P.B103 - Dương Hoàng Long
    15 => 15, // P.B201 - Võ Thị Mai Phương
    16 => 16, // P.B202 - Hồ Đức Thắng
    17 => 17, // P.B203 - Lý Gia Hân
    19 => 18, // P.C101 - Nguyễn Thị Minh Khai
    20 => 19, // P.C102 - Trần Văn Cường
    22 => 20, // P.C104 - Lê Hữu Phước
    23 => 21, // P.C201 - Phạm Hồng Nhung
    24 => 22, // P.C202 - Hoàng Minh Tuấn
    25 => 23, // P.C203 - Đỗ Thùy Trang
];

$roomsMap = [];
foreach ($roomsConfig as $rc) {
    $roomsMap[$rc[0]] = $rc;
}

foreach ($activeRooms as $rId => $tId) {
    $rPrice = $roomsMap[$rId][3];
    $cCode = sprintf("HD-2026-%s", $roomsMap[$rId][2]);
    $startDate = '2026-05-01';
    
    // Tạo 2 hợp đồng sắp hết hạn trong tháng 11/2026 để test cảnh báo gia hạn
    if ($rId == 7) {
        $endDate = '2026-11-15'; // Còn 9 ngày
    } elseif ($rId == 13) {
        $endDate = '2026-11-20'; // Còn 14 ngày
    } else {
        $endDate = '2027-05-01'; // 1 năm
    }

    $depositAmount = $rPrice; // 100% cọc

    $insContract->execute([
        $contractId, $cCode, 'official_lease', null,
        $rId, $tId, $startDate, $endDate, $depositAmount,
        $startDate, 100, 'held', $rPrice,
        1, null, 3500.00,
        20000.00, 150000.00, 'paper', 'signed',
        $startDate . ' 09:00:00', 'none', null, null,
        'Hợp đồng thuê chính thức đang hiệu lực', 'active', $startDate . ' 09:30:00', 'Đã thu tiền phòng tháng đầu kèm cọc',
        0, null, 'none', 0, null, $startDate . ' 08:00:00'
    ]);

    $activeContracts[$rId] = [
        'id' => $contractId,
        'code' => $cCode,
        'room_id' => $rId,
        'room_number' => $roomsMap[$rId][2],
        'tenant_id' => $tId,
        'monthly_rent' => $rPrice,
        'deposit_amount' => $depositAmount,
        'start_date' => $startDate,
        'end_date' => $endDate
    ];

    $contractId++;
}

// B. HĐ 1: ĐẶT CỌC GIỮ CHỖ 5 NGÀY (50% cọc) - Đang hiệu lực cho phòng trống A103 (Room ID 3)
// Khách: Nguyễn Thị Bích Ngọc (User 27)
$insContract->execute([
    $contractId, 'HD-HOLD-A103', 'deposit_hold', null,
    3, 27, '2026-11-03', '2026-11-08', 1600000.00, // 50% của 3.200.000 đ
    '2026-11-03', 50, 'held', 3200000.00,
    0, null, 3500.00,
    20000.00, 150000.00, 'paper', 'signed',
    '2026-11-03 14:00:00', 'none', null, null,
    'Đặt cọc giữ chỗ 50% trong 5 ngày. Hạn ký HĐ chính thức ngày 08/11/2026.', 'active', null, null,
    0, null, 'none', 0, null, '2026-11-03 14:00:00'
]);
$contractId++;

// C. LỊCH SỬ THANH LÝ PHÒNG:
// Trường hợp 1: Trả phòng đúng hạn và không muốn gia hạn -> ĐÃ HOÀN CỌC 100% (refunded)
$insContract->execute([
    $contractId, 'HD-TERM-A103', 'official_lease', null,
    3, 24, '2025-11-01', '2026-11-01', 3200000.00,
    '2025-11-01', 100, 'refunded', 3200000.00,
    1, null, 3500.00,
    20000.00, 150000.00, 'paper', 'signed',
    '2025-11-01 09:00:00', 'none', '2026-10-31',
    'Hết thời gian thuê và không muốn gia hạn — Khách bàn giao phòng sạch sẽ nguyên vẹn, Ban Quản Lý đã chuyển khoản hoàn lại 100% tiền đặt cọc (3.200.000 đ).',
    'Thanh lý hợp đồng đúng hạn và hoàn cọc đầy đủ', 'terminated', '2025-11-01 10:00:00', 'Đã thu khi ký HĐ',
    0, null, 'refund_standard', 1, null, '2025-11-01 08:30:00'
]);
$contractId++;

// Trường hợp 2: Trả phòng / Bỏ phòng trước hạn -> MẤT CỌC / KHÔNG HOÀN CỌC (forfeited)
$insContract->execute([
    $contractId, 'HD-TERM-A203', 'official_lease', null,
    8, 25, '2026-03-01', '2027-03-01', 3000000.00,
    '2026-03-01', 100, 'forfeited', 3000000.00,
    1, null, 3500.00,
    20000.00, 150000.00, 'paper', 'signed',
    '2026-03-01 10:00:00', 'none', '2026-10-25',
    'Trả phòng trước thời hạn hợp đồng (Hạn gốc: 01/03/2027) — Không báo trước 30 ngày, vi phạm điều khoản cam kết, tịch thu toàn bộ tiền cọc (3.000.000 đ) sung quỹ.',
    'Vi phạm thanh lý trước hạn - Mất cọc', 'terminated', '2026-03-01 10:30:00', 'Đã thu khi ký HĐ',
    1, '2026-10-25 15:00:00', 'breach_forfeited', 0, null, '2026-03-01 09:30:00'
]);
$contractId++;

// Trường hợp 3: HĐ cọc giữ chỗ 5 ngày quá hạn không đến ký -> TỰ HỦY (expired)
$insContract->execute([
    $contractId, 'HD-HOLD-EXP-B104', 'deposit_hold', null,
    14, 26, '2026-10-10', '2026-10-15', 1900000.00, // 50% cọc
    '2026-10-10', 50, 'forfeited', 3800000.00,
    0, null, 3500.00,
    20000.00, 150000.00, 'paper', 'signed',
    '2026-10-10 11:00:00', 'none', '2026-10-16',
    'Hợp đồng đặt cọc giữ chỗ quá 5 ngày khách không đến nhận phòng và không ký hợp đồng chính thức. Hệ thống tự động hủy giữ chỗ và tịch thu cọc theo quy định.',
    'Hết hạn 5 ngày tự động hủy giữ chỗ', 'expired', null, null,
    1, '2026-10-16 08:30:00', 'hold_expired', 0, null, '2026-10-10 11:00:00'
]);
$contractId++;

echo "   -> Đã tạo xong danh mục Hợp đồng chuẩn nghiệp vụ.\n";

// 8. Khởi tạo 5 THÁNG LỊCH SỬ ĐIỆN NƯỚC & HÓA ĐƠN (06-2026 đến 10-2026):
// Tháng gần đây nhất (Kỳ 11-2026) CHƯA CÓ ĐIỆN NƯỚC VÀ CHƯA CÓ HÓA ĐƠN!
echo "7. Khởi tạo 5 tháng lịch sử Điện Nước & Hóa Đơn (06-2026 đến 10-2026)...\n";
echo "   Kỳ 11-2026 để trống (Chờ chốt số và chờ lập hóa đơn) theo đúng yêu cầu đề bài!\n";

$historyMonths = [
    '06-2026', // Tháng 1
    '07-2026', // Tháng 2
    '08-2026', // Tháng 3
    '09-2026', // Tháng 4
    '10-2026', // Tháng 5 (Tháng trước kỳ hiện tại)
];

$insReading = $db->prepare("
    INSERT INTO utilities_readings (
        room_id, month_year, electric_old, electric_new, water_old, water_new, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?)
");

$insInvoice = $db->prepare("
    INSERT INTO invoices (
        id, room_id, contract_id, month_year, room_price,
        electric_amount, water_amount, service_fee, total_amount,
        status, due_date, paid_at, payment_method, payment_note, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$insInvDetail = $db->prepare("
    INSERT INTO invoice_details (
        invoice_id, service_id, quantity, applied_price, subtotal
    ) VALUES (?, ?, ?, ?, ?)
");

// Khởi tạo chỉ số ban đầu cho các phòng
$meterBase = [];
foreach ($activeContracts as $rId => $c) {
    $meterBase[$rId] = [
        'e' => 150 + ($rId * 15),
        'w' => 20 + ($rId * 2)
    ];
}

$roomHistoryUsage = []; // [rId][mIdx] = ['e_used', 'w_used', 'e_cost', 'w_cost']

// A. Tạo chỉ số điện nước cho 5 tháng
foreach ($historyMonths as $mIdx => $mStr) {
    list($mm, $yyyy) = explode('-', $mStr);
    $createdDate = "$yyyy-$mm-28 20:00:00";

    foreach ($activeContracts as $rId => $c) {
        $eOld = $meterBase[$rId]['e'];
        $wOld = $meterBase[$rId]['w'];

        // Mùa hè (tháng 6, 7, 8) dùng điều hòa nhiều hơn
        $isSummer = in_array($mm, ['06', '07', '08']);
        $eUsed = $isSummer ? (95 + (($rId * 7) % 80)) : (60 + (($rId * 5) % 50));
        $wUsed = 4 + ($rId % 5);

        $eNew = $eOld + $eUsed;
        $wNew = $wOld + $wUsed;

        $insReading->execute([$rId, $mStr, $eOld, $eNew, $wOld, $wNew, $createdDate]);

        $eCost = $eUsed * 3500.00;
        $wCost = $wUsed * 20000.00;

        $roomHistoryUsage[$rId][$mIdx] = [
            'e_used' => $eUsed,
            'w_used' => $wUsed,
            'e_cost' => $eCost,
            'w_cost' => $wCost
        ];

        // Lũy tiến mốc cũ cho tháng sau
        $meterBase[$rId]['e'] = $eNew;
        $meterBase[$rId]['w'] = $wNew;
    }
}

// B. Tạo Hóa đơn cho 5 tháng (06-2026 đến 10-2026)
$invoiceId = 1;

foreach ($historyMonths as $mIdx => $mStr) {
    list($mm, $yyyy) = explode('-', $mStr);
    $invCreatedDate = "$yyyy-$mm-30 08:30:00";
    $dueDate = sprintf("%04d-%02d-10", ($mm == '12' ? (int)$yyyy + 1 : (int)$yyyy), (($mm % 12) + 1));

    foreach ($activeContracts as $rId => $c) {
        $roomPrice = $c['monthly_rent'];
        $eUsed = $roomHistoryUsage[$rId][$mIdx]['e_used'];
        $wUsed = $roomHistoryUsage[$rId][$mIdx]['w_used'];
        $eCost = $roomHistoryUsage[$rId][$mIdx]['e_cost'];
        $wCost = $roomHistoryUsage[$rId][$mIdx]['w_cost'];

        // Dịch vụ cố định: Internet 100k, Thang máy 150k, Vệ sinh 30k, Vận hành 200k = 480k
        $serviceFee = 480000.00;
        $totalAmount = $roomPrice + $eCost + $wCost + $serviceFee;

        // Trạng thái thanh toán:
        // Các tháng 06, 07, 08, 09-2026: 100% ĐÃ THANH TOÁN (status = 'paid')
        // Tháng 10-2026 (tháng vừa rồi):
        // - Đa số (15 phòng): ĐÃ THU (paid)
        // - 3 phòng (P.A104, P.B102, P.C104): CHƯA THU (unpaid) hạn nộp 10/11/2026 để test thu tiền
        // - 1 phòng (P.A204): QUÁ HẠN (unpaid, due_date = 2026-10-25) để test cảnh báo quá hạn
        if ($mIdx < 4) {
            $status = 'paid';
            $paidAt = sprintf("%04d-%02d-05 14:30:00", (int)$yyyy, ((int)$mm % 12) + 1);
            $payMethod = ($rId % 2 == 0) ? 'transfer' : 'cash';
            $payNote = ($payMethod == 'transfer') ? 'Chuyển khoản VietQR đã khớp sao kê' : 'Nộp tiền mặt tại văn phòng quản lý';
        } else {
            // Tháng 10-2026
            if ($rId == 4 || $rId == 12 || $rId == 22) {
                // Chưa thu (unpaid)
                $status = 'unpaid';
                $paidAt = null;
                $payMethod = null;
                $payNote = 'Chờ khách chuyển khoản (Hạn chót: 10/11/2026)';
            } elseif ($rId == 9) {
                // Quá hạn (unpaid)
                $status = 'unpaid';
                $dueDate = '2026-10-25'; // Đã quá hạn!
                $paidAt = null;
                $payMethod = null;
                $payNote = 'Quá hạn thanh toán 12 ngày - Đã gửi tin nhắn nhắc nhở';
            } else {
                // Đã thu (paid)
                $status = 'paid';
                $paidAt = '2026-11-02 10:15:00';
                $payMethod = 'transfer';
                $payNote = 'Đã thanh toán chuyển khoản thành công';
            }
        }

        $insInvoice->execute([
            $invoiceId, $rId, $c['id'], $mStr, $roomPrice,
            $eCost, $wCost, $serviceFee, $totalAmount,
            $status, $dueDate, $paidAt, $payMethod, $payNote, $invCreatedDate
        ]);

        // Chi tiết hóa đơn (invoice_details)
        // Service 1: Tiền điện
        $insInvDetail->execute([$invoiceId, 1, $eUsed, 3500.00, $eCost]);
        // Service 2: Tiền nước
        $insInvDetail->execute([$invoiceId, 2, $wUsed, 20000.00, $wCost]);
        // Service 3: Internet
        $insInvDetail->execute([$invoiceId, 3, 1, 100000.00, 100000.00]);
        // Service 4: Thang máy
        $insInvDetail->execute([$invoiceId, 4, 1, 150000.00, 150000.00]);
        // Service 5: Vệ sinh
        $insInvDetail->execute([$invoiceId, 5, 1, 30000.00, 30000.00]);
        // Service 10: Vận hành
        $insInvDetail->execute([$invoiceId, 10, 1, 200000.00, 200000.00]);

        $invoiceId++;
    }
}

echo "   -> Đã tạo thành công " . ($invoiceId - 1) . " hóa đơn cho 5 kỳ lịch sử.\n";

// 9. Khởi tạo Khoản chi đối tác bên thứ 3 (third_party_expenses)
echo "8. Khởi tạo danh mục Chi phí bên thứ 3 hàng tháng...\n";
$insExp = $db->prepare("
    INSERT INTO third_party_expenses (
        month_year, expense_type, title, provider_name, quantity, unit,
        unit_price, amount, customer_amount, status, paid_at, notes, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$expMonths = ['06-2026', '07-2026', '08-2026', '09-2026', '10-2026'];
foreach ($expMonths as $eM) {
    list($emm, $eyyy) = explode('-', $eM);
    // Internet Tòa A
    $insExp->execute([$eM, 'internet', 'Cáp quang Internet Tòa A', 'VNPT Cầu Giấy', 1.0, 'Tháng', 1500000.0, 1500000.0, 1800000.0, 'paid', "$eyyy-$emm-05", 'Gói cáp quang doanh nghiệp tốc độ 500Mbps', "$eyyy-$emm-01 08:00:00"]);
    // Internet Tòa B
    $insExp->execute([$eM, 'internet', 'Cáp quang Internet Tòa B', 'Viettel Đống Đa', 1.0, 'Tháng', 1500000.0, 1500000.0, 1800000.0, 'paid', "$eyyy-$emm-05", 'Gói internet doanh nghiệp FTTH', "$eyyy-$emm-01 08:00:00"]);
    // Internet Tòa C
    $insExp->execute([$eM, 'internet', 'Cáp quang Internet Tòa C', 'FPT Telecom Thanh Xuân', 1.0, 'Tháng', 1500000.0, 1500000.0, 1800000.0, 'paid', "$eyyy-$emm-05", 'Gói Super 500', "$eyyy-$emm-01 08:00:00"]);
    // Vệ sinh
    $insExp->execute([$eM, 'cleaning', 'Dịch vụ vệ sinh hành lang & khu chung', 'Công ty Môi Trường Xanh', 3.0, 'Tòa', 1200000.0, 3600000.0, 4200000.0, 'paid', "$eyyy-$emm-10", 'Vệ sinh định kỳ thứ 2, 4, 6 hàng tuần', "$eyyy-$emm-01 08:00:00"]);
    // Thang máy
    $insExp->execute([$eM, 'elevator', 'Bảo trì định kỳ thang máy', 'Thang máy MitsuLift', 3.0, 'Tháng', 1000000.0, 3000000.0, 3500000.0, 'paid', "$eyyy-$emm-15", 'Bảo dưỡng buồng thang, bôi trơn ray dẫn', "$eyyy-$emm-01 08:00:00"]);
    // Rác
    $insExp->execute([$eM, 'garbage', 'Thu gom rác thải sinh hoạt', 'Hợp tác xã Vệ sinh Môi trường', 3.0, 'Tháng', 600000.0, 1800000.0, 2100000.0, 'paid', "$eyyy-$emm-08", 'Thu gom rác mỗi ngày lúc 19h00', "$eyyy-$emm-01 08:00:00"]);
}

// 10. Khởi tạo Yêu cầu khách thuê (tenant_requests)
echo "9. Khởi tạo Yêu cầu của khách thuê...\n";
$insReq = $db->prepare("
    INSERT INTO tenant_requests (
        id, tenant_id, request_type, field_target, old_value, new_value, reason, status, manager_note, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$insReq->execute([1, 5, 'profile_update', 'phone', '0901000001', '0988112233', 'Em bị mất SIM cũ, xin đổi số điện thoại mới để nhận thông báo hóa đơn.', 'pending', null, '2026-11-04 15:30:00']);
$insReq->execute([2, 6, 'profile_update', 'email', 'huong.lt@gmail.com', 'huong.le.mkt@gmail.com', 'Đổi sang email công việc chính thức.', 'approved', 'Đã duyệt cập nhật thông tin.', '2026-11-02 09:15:00']);
$insReq->execute([3, 8, 'repair', 'air_conditioner', 'Điều hòa phòng A105', 'Cần vệ sinh lưới lọc', 'Điều hòa kêu to và phả gió yếu, nhờ BQL kiểm tra.', 'pending', null, '2026-11-05 08:00:00']);

// 11. Khách liên hệ xem phòng (tenant_contact_leads)
echo "10. Khởi tạo Khách liên hệ xem phòng...\n";
$insLead = $db->prepare("
    INSERT INTO tenant_contact_leads (
        id, customer_name, phone, room_label, room_id, visit_time, note, status, created_at, contacted_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$insLead->execute([1, 'Trần Thu Thủy', '0977889900', 'Phòng A103 - Ban công thoáng', 3, '2026-11-08 15:00:00', 'Đặt lịch xem phòng vào chiều thứ 7 lúc 15h.', 'contacted', '2026-11-04 16:00:00', '2026-11-04 16:30:00']);
$insLead->execute([2, 'Nguyễn Văn Đạt', '0966554433', 'Phòng B104 - Tầng 1 tiện đi lại', 14, '2026-11-09 10:00:00', 'Muốn xem phòng và kiểm tra chỗ để xe máy.', 'new', '2026-11-05 10:30:00', null]);

$db->exec("SET FOREIGN_KEY_CHECKS = 1");

echo "\n=======================================================\n";
echo "RESEED DỮ LIỆU TOÀN DIỆN THÀNH CÔNG 100%!\n";
echo "- 3 Tòa nhà, 26 Phòng trọ (rented, available, maintenance)\n";
echo "- 30 Người dùng (Admin, 3 Quản lý, 19 Khách đang thuê, 3 Khách thanh lý, 4 Khách mới chưa có HĐ)\n";
echo "- 22 Hợp đồng (Chính thức, Giữ chỗ 5 ngày, Sắp hết hạn, Hoàn cọc 100%, Mất cọc, Hết hạn tự hủy)\n";
echo "- 5 Tháng lịch sử Điện Nước & Hóa Đơn: 06-2026 đến 10-2026 (95 hóa đơn có chi tiết dịch vụ)\n";
echo "- Kỳ hiện tại 11-2026: ĐỂ TRỐNG (Chờ chốt số điện nước & chờ lập hóa đơn demo)\n";
echo "=======================================================\n";
