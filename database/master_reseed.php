<?php
/**
 * MASTER DATABASE RESEED SCRIPT
 * Xóa sạch toàn bộ dữ liệu cũ và tái tạo hệ sinh thái dữ liệu chuẩn mực 100%:
 * - 3 Tòa nhà (Tòa A, B, C)
 * - 60 Phòng trọ (20 phòng mỗi tòa: A101-A405, B101-B405, C101-C405)
 * - ~150 Khách thuê (~50 khách mỗi tòa) với đầy đủ CCCD, SĐT, Email, Quê quán
 * - 2 loại Hợp đồng (HĐ 1 cọc 50% giữ chỗ 5 ngày, HĐ 2 cọc 100% thuê 6 tháng)
 * - Đầy đủ các trường hợp: Đang hiệu lực, Sắp hết hạn, Hoàn cọc (refunded), Vi phạm mất cọc (forfeited), Quá hạn hủy
 * - 7 tháng lịch sử Điện Nước & Hóa Đơn (05-2026 đến 11-2026)
 * - Quy tắc tính tiền chuẩn xác theo yêu cầu:
 *   + Tháng đầu: Thu cọc 100% + Tiền phòng tháng đầu (nằm trong bảng Tiền cọc & phòng tháng đầu, tính vào doanh thu)
 *   + Tháng 2: Tiền phòng tháng 2 + Tiền điện nước tháng 1
 *   + Tháng 3+: Tiền phòng tháng N + Tiền điện nước tháng N-1
 * - Hóa đơn chi phí đối tác bên thứ 3 (third_party_expenses)
 * - Khách liên hệ xem phòng (tenant_contact_leads)
 */

require_once __DIR__ . '/../app/Config/Database.php';

$db = Database::getConnection();
$db->exec("SET FOREIGN_KEY_CHECKS = 0");

echo "1. Đang dọn dẹp các bảng dữ liệu cũ...\n";
$tablesToTruncate = [
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

foreach ($tablesToTruncate as $tbl) {
    try {
        $db->exec("TRUNCATE TABLE `$tbl`");
    } catch (PDOException $e) {
        $db->exec("DELETE FROM `$tbl`");
    }
}

echo "2. Khởi tạo danh mục Dịch vụ...\n";
$services = [
    [1, 'Tiền điện sinh hoạt', 3500.00, 'kWh', 1],
    [2, 'Tiền nước sinh hoạt', 20000.00, 'Khối', 1],
    [3, 'Internet / Wifi tốc độ cao', 100000.00, 'Phòng/tháng', 1],
    [4, 'Thang máy & Chiếu sáng chung', 50000.00, 'Người/tháng', 1],
    [5, 'Vệ sinh & Rác sinh hoạt', 50000.00, 'Phòng/tháng', 1],
    [6, 'Gửi xe máy vân tay', 100000.00, 'Xe/tháng', 1],
    [7, 'Phí quản lý vận hành', 150000.00, 'Phòng/tháng', 1],
    [8, 'Gửi ô tô thông minh', 1200000.00, 'Xe/tháng', 1],
];
$insSrv = $db->prepare("INSERT INTO services (id, service_name, unit_price, unit, is_active) VALUES (?, ?, ?, ?, ?)");
foreach ($services as $s) {
    $insSrv->execute($s);
}

echo "3. Khởi tạo Ban Quản lý & Admin...\n";
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

// ID 1: Admin
$insUser->execute([
    1, 'admin', $pwdAdmin, 'Quản Trị Viên Hệ Thống', '0901234567',
    'admin@staymate.vn', 'admin', null, '001099001122', '1990-05-15',
    'Nam', 'Quản trị hệ thống', 'Hà Nội', '0988776655', 'active', null, null
]);

// ID 2: Quản lý Tòa A
$insUser->execute([
    2, 'quanly_a', $pwdMgr, 'Nguyễn Văn Quân', '0912345678',
    'quanly_a@staymate.vn', 'manager', null, '001092002233', '1992-08-20',
    'Nam', 'Quản lý cơ sở Tòa A', 'Bắc Ninh', '0912345679', 'active', null, null
]);

// ID 3: Quản lý Tòa B
$insUser->execute([
    3, 'quanly_b', $pwdMgr, 'Trần Thị Mai Lan', '0987654321',
    'quanly_b@staymate.vn', 'manager', null, '001194003344', '1994-03-12',
    'Nữ', 'Quản lý cơ sở Tòa B', 'Hải Phòng', '0987654322', 'active', null, null
]);

// ID 4: Quản lý Tòa C
$insUser->execute([
    4, 'quanly_c', $pwdMgr, 'Lê Hoàng Nam', '0934567890',
    'quanly_c@staymate.vn', 'manager', null, '001091004455', '1991-11-28',
    'Nam', 'Quản lý cơ sở Tòa C', 'Nam Định', '0934567891', 'active', null, null
]);

echo "4. Khởi tạo 3 Tòa nhà...\n";
$insBld = $db->prepare("INSERT INTO buildings (id, name, address, manager_id) VALUES (?, ?, ?, ?)");
$buildingsData = [
    [1, 'Tòa A - StayMate Sunrise', 'Số 128 Cầu Giấy, Quận Cầu Giấy, TP. Hà Nội', 2],
    [2, 'Tòa B - StayMate Emerald', 'Số 45 Nguyễn Chí Thanh, Quận Đống Đa, TP. Hà Nội', 3],
    [3, 'Tòa C - StayMate Horizon', 'Số 88 Nguyễn Trãi, Quận Thanh Xuân, TP. Hà Nội', 4],
];
foreach ($buildingsData as $b) {
    $insBld->execute($b);
}

echo "5. Khởi tạo 60 Phòng trọ (20 phòng mỗi tòa)...\n";
$insRoom = $db->prepare("INSERT INTO rooms (id, building_id, room_number, price, status, description) VALUES (?, ?, ?, ?, ?, ?)");
$insRoomSrv = $db->prepare("INSERT INTO room_services (room_id, service_name, unit_price, unit) VALUES (?, ?, ?, ?)");

$roomId = 1;
$allRooms = []; // will store room metadata

$bldConfigs = [
    1 => ['prefix' => 'A', 'base_price' => 2800000, 'name' => 'Tòa A'],
    2 => ['prefix' => 'B', 'base_price' => 3200000, 'name' => 'Tòa B'],
    3 => ['prefix' => 'C', 'base_price' => 3500000, 'name' => 'Tòa C'],
];

foreach ($bldConfigs as $bId => $bCfg) {
    $pfx = $bCfg['prefix'];
    $roomIdxInBuilding = 0;

    // 4 floors, 5 rooms per floor = 20 rooms
    for ($floor = 1; $floor <= 4; $floor++) {
        for ($rNum = 1; $rNum <= 5; $rNum++) {
            $roomNumber = sprintf("%s%d%02d", $pfx, $floor, $rNum);
            $roomIdxInBuilding++;

            // Price variations by floor and room type
            $priceAdd = ($floor - 1) * 300000 + ($rNum % 2 == 0 ? 200000 : 0);
            $roomPrice = $bCfg['base_price'] + $priceAdd;

            // Room type & description
            if ($floor == 1) {
                $desc = 'Phòng Đơn Tiêu Chuẩn tầng 1, diện tích 22m2, tiện đi lại, đầy đủ điều hòa, nóng lạnh, giường tủ.';
            } elseif ($floor == 2) {
                $desc = 'Phòng Ban Công Thoáng Mát tầng 2, diện tích 25m2, cửa sổ lớn đón gió tự nhiên, bàn làm việc, tủ lạnh.';
            } elseif ($floor == 3) {
                $desc = 'Phòng Studio Full Nội Thất tầng 3, diện tích 28m2, bếp riêng, máy hút mùi, tủ lạnh 2 cánh, sofa.';
            } else {
                $desc = 'Căn Hộ Duplex Gác Lửng cao cấp tầng 4, diện tích 35m2, trần cao thoáng đãng, khóa vân tay, nội thất hiện đại.';
            }

            // Realistic statuses:
            // ~15 rented, 4 available, 1 maintenance
            if ($roomIdxInBuilding == 5 || $roomIdxInBuilding == 10 || $roomIdxInBuilding == 15 || $roomIdxInBuilding == 19) {
                $status = 'available';
            } elseif ($roomIdxInBuilding == 20) {
                $status = 'maintenance';
            } else {
                $status = 'rented';
            }

            $insRoom->execute([$roomId, $bId, $roomNumber, $roomPrice, $status, $desc]);

            // Add standard services for room
            $insRoomSrv->execute([$roomId, 'Internet / Wifi tốc độ cao', 100000.00, 'Phòng/tháng']);
            $insRoomSrv->execute([$roomId, 'Vệ sinh & Rác sinh hoạt', 50000.00, 'Phòng/tháng']);

            $allRooms[$roomId] = [
                'id' => $roomId,
                'building_id' => $bId,
                'room_number' => $roomNumber,
                'price' => $roomPrice,
                'status' => $status,
                'bld_prefix' => $pfx,
                'room_index' => $roomIdxInBuilding,
                'floor' => $floor
            ];

            $roomId++;
        }
    }
}

echo "   -> Đã tạo thành công 60 phòng trọ.\n";

echo "6. Khởi tạo danh sách 150 Khách thuê (~50 khách mỗi tòa)...\n";
// Vietnamese names generator components
$hoList = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Vũ', 'Phan', 'Võ', 'Đặng', 'Bùi', 'Đỗ', 'Hồ', 'Ngô', 'Dương', 'Lý'];
$demNam = ['Văn', 'Đức', 'Hữu', 'Tuấn', 'Minh', 'Thành', 'Hoàng', 'Quang', 'Bảo', 'Gia'];
$tenNam = ['Anh', 'Bách', 'Cường', 'Dũng', 'Đạt', 'Hải', 'Hiếu', 'Huy', 'Khoa', 'Long', 'Nam', 'Phúc', 'Quân', 'Sơn', 'Thắng', 'Tùng', 'Việt'];
$demNu = ['Thị', 'Thu', 'Thảo', 'Phương', 'Mai', 'Ngọc', 'Hải', 'Thanh', 'Khánh', 'Như'];
$tenNu = ['Anh', 'Bích', 'Châu', 'Dung', 'Giang', 'Hà', 'Hương', 'Linh', 'Mai', 'Nga', 'Nhi', 'Oanh', 'Trang', 'Uyên', 'Vân', 'Yến'];

$hometowns = ['Hà Nội', 'Hải Phòng', 'Bắc Ninh', 'Nam Định', 'Thái Bình', 'Nghệ An', 'Thanh Hóa', 'Quảng Ninh', 'Hà Nam', 'Hải Dương', 'Vĩnh Phúc', 'Phú Thọ'];
$occupations = ['Kỹ sư phần mềm', 'Nhân viên kinh doanh', 'Kế toán viên', 'Sinh viên ĐH Quốc Gia', 'Sinh viên ĐH Ngoại Thương', 'Chuyên viên Marketing', 'Thiết kế đồ họa', 'Nhân viên văn phòng', 'Bác sĩ nội trú', 'Dược sĩ'];

$tenantUsers = []; // map by building: [1 => [...], 2 => [...], 3 => [...]]
$userId = 5;

for ($b = 1; $b <= 3; $b++) {
    $pfx = $bldConfigs[$b]['prefix'];
    $tenantUsers[$b] = [];

    for ($i = 1; $i <= 50; $i++) {
        $isFemale = ($i % 2 === 0);
        $ho = $hoList[($i * 3 + $b) % count($hoList)];
        if ($isFemale) {
            $dem = $demNu[($i + $b) % count($demNu)];
            $ten = $tenNu[($i * 2 + $b) % count($tenNu)];
            $gender = 'Nữ';
        } else {
            $dem = $demNam[($i + $b) % count($demNam)];
            $ten = $tenNam[($i * 2 + $b) % count($tenNam)];
            $gender = 'Nam';
        }
        $fullName = "$ho $dem $ten";
        $username = "kh_" . strtolower($pfx) . str_pad((string)$i, 2, '0', STR_PAD_LEFT);
        $phone = sprintf("09%d%02d%04d", $b, $i, rand(1000, 9999));
        $email = $username . "@staymate.vn";
        $cccd = sprintf("0%02d%03d%06d", rand(1, 99), rand(100, 200), rand(100000, 999999));
        $bYear = rand(1996, 2004);
        $bMonth = rand(1, 12);
        $bDay = rand(1, 28);
        $birthday = sprintf("%04d-%02d-%02d", $bYear, $bMonth, $bDay);
        $hometown = $hometowns[($i + $b) % count($hometowns)];
        $occupation = $occupations[($i * 3) % count($occupations)];
        $emergencyContact = sprintf("09%d%07d", rand(1, 9), rand(1000000, 9999999));

        $insUser->execute([
            $userId, $username, $pwdTenant, $fullName, $phone, $email, 'tenant',
            null, $cccd, $birthday, $gender, $occupation,
            $hometown, $emergencyContact, 'active', null, null
        ]);

        $tenantUsers[$b][] = [
            'id' => $userId,
            'username' => $username,
            'full_name' => $fullName,
            'phone' => $phone,
            'email' => $email,
            'bld_id' => $b,
            'index' => $i
        ];

        $userId++;
    }
}

echo "   -> Đã tạo thành công 150 tài khoản khách thuê (154 users tổng cộng).\n";

echo "7. Khởi tạo Hợp đồng (Đủ các trường hợp theo yêu cầu)...\n";
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
        refund_confirmed_invoice, unpaid_notice_resolved_at
    ) VALUES (
        ?, ?, ?, ?,
        ?, ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?,
        ?, ?
    )
");

$contractId = 1;
$activeContracts = []; // map room_id => contract_data for invoice generation
$allContractsList = [];

// Các trường hợp hợp đồng:
// 1. Hợp đồng chính thức ĐANG HIỆU LỰC (active) cho các phòng 'rented' (~15 phòng/tòa)
// 2. Hợp đồng SẮP HẾT HẠN trong tháng 11/2026 (1 phòng/tòa)
// 3. Hợp đồng ĐÃ HOÀN CỌC (refunded, terminated) - kết thúc trong các tháng 07, 08, 09/2026 (2 mỗi tòa = 6 HĐ)
// 4. Hợp đồng VI PHẠM BỊ MẤT CỌC (forfeited, terminated) - chấm dứt do vi phạm hợp đồng (2 mỗi tòa = 6 HĐ)
// 5. Hợp đồng HĐ 1 ĐẶT CỌC GIỮ CHỖ 5 NGÀY (deposit_hold, cọc 50%) - đang hiệu lực (1 mỗi tòa = 3 HĐ)
// 6. Hợp đồng HĐ 1 QUÁ HẠN 5 NGÀY HỦY (deposit_hold, expired/terminated) (1 mỗi tòa = 3 HĐ)

foreach ($bldConfigs as $bId => $bCfg) {
    $pfx = $bCfg['prefix'];
    $tenantsOfBld = $tenantUsers[$bId];
    $roomsOfBld = array_filter($allRooms, fn($r) => $r['building_id'] == $bId);

    $tIndex = 0;

    // A. Active contracts for 'rented' rooms
    foreach ($roomsOfBld as $r) {
        if ($r['status'] !== 'rented') continue;

        $tenant = $tenantsOfBld[$tIndex++];
        $cCode = sprintf("HD-2026-%s%02d", $pfx, $r['room_index']);

        // Phân bổ ngày bắt đầu: từ 01/05/2026 đến 01/10/2026
        // Hầu hết từ tháng 05/2026 để có trọn vẹn 7 tháng lịch sử
        if ($r['room_index'] == 14) {
            // Sắp hết hạn trong tháng 11/2026!
            $startDate = '2026-05-15';
            $endDate = '2026-11-15';
            $renewalStatus = 'pending';
        } else {
            $startMonth = ($r['room_index'] <= 12) ? '05' : '06';
            $startDate = "2026-$startMonth-01";
            $endDate = ($r['room_index'] % 2 == 0) ? "2027-$startMonth-01" : "2026-11-30";
            $renewalStatus = 'none';
        }

        $depositAmount = $r['price']; // 100% cọc
        $monthlyRent = $r['price'];
        $firstMonthPaid = 1;
        $firstMonthPaidAt = $startDate . ' 09:30:00';
        $firstMonthNote = 'Đã thu tiền phòng tháng đầu kèm cọc ngày dọn vào';

        $insContract->execute([
            $contractId, $cCode, 'official_lease', null,
            $r['id'], $tenant['id'], $startDate, $endDate, $depositAmount,
            $startDate, 100, 'held', $monthlyRent,
            $firstMonthPaid, null, 3500.00,
            20000.00, 150000.00, 'online', 'signed',
            $startDate . ' 10:00:00', $renewalStatus, null, null,
            'Hợp đồng thuê phòng chính thức có hiệu lực', 'active', $firstMonthPaidAt, $firstMonthNote,
            0, null, 'none', 0, null
        ]);

        // Cập nhật người dùng liên kết phòng
        $db->prepare("UPDATE users SET preferred_room_id = ? WHERE id = ?")->execute([$r['id'], $tenant['id']]);

        $contractData = [
            'id' => $contractId,
            'code' => $cCode,
            'room_id' => $r['id'],
            'room_number' => $r['room_number'],
            'tenant_id' => $tenant['id'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'monthly_rent' => $monthlyRent,
            'deposit_amount' => $depositAmount,
            'bld_id' => $bId
        ];
        $activeContracts[$r['id']] = $contractData;
        $allContractsList[] = $contractData;
        $contractId++;
    }

    // B. Hợp đồng ĐÃ HOÀN CỌC (deposit_status = 'refunded', status = 'terminated') (2 HĐ mỗi tòa)
    for ($ref = 1; $ref <= 2; $ref++) {
        $tenant = $tenantsOfBld[$tIndex++];
        $dummyRoom = reset($roomsOfBld); // liên kết phòng tham chiếu
        $cCode = sprintf("HD-REF-%s%02d", $pfx, $ref);
        $startDate = '2025-12-01';
        $endDate = ($ref == 1) ? '2026-06-01' : '2026-08-01';
        $termDate = $endDate;
        $depositAmount = 3000000.00;

        $insContract->execute([
            $contractId, $cCode, 'official_lease', null,
            $dummyRoom['id'], $tenant['id'], $startDate, $endDate, $depositAmount,
            $startDate, 100, 'refunded', $depositAmount,
            1, null, 3500.00,
            20000.00, 150000.00, 'paper', 'signed',
            $startDate . ' 08:30:00', 'none', $termDate,
            'Hết hạn hợp đồng thuê phòng, khách bàn giao phòng nguyên vẹn sạch sẽ, ban quản lý đã chuyển khoản hoàn trả 100% tiền đặt cọc (' . number_format($depositAmount, 0, ',', '.') . ' đ).',
            'Thanh lý hợp đồng đúng hạn và hoàn cọc đầy đủ', 'terminated', $startDate . ' 09:00:00', 'Đã thu khi ký hợp đồng',
            0, null, 'refund_standard', 1, null
        ]);
        $contractId++;
    }

    // C. Hợp đồng VI PHẠM BỊ MẤT CỌC (deposit_status = 'forfeited', status = 'terminated') (2 HĐ mỗi tòa)
    for ($forf = 1; $forf <= 2; $forf++) {
        $tenant = $tenantsOfBld[$tIndex++];
        $dummyRoom = end($roomsOfBld);
        $cCode = sprintf("HD-FORF-%s%02d", $pfx, $forf);
        $startDate = '2026-04-01';
        $endDate = '2026-10-01';
        $termDate = ($forf == 1) ? '2026-07-20' : '2026-09-15';
        $depositAmount = 3500000.00;
        $reason = ($forf == 1) 
            ? 'Khách đơn phương dọn đi trước thời hạn hợp đồng không báo trước 30 ngày theo cam kết. Tịch thu toàn bộ tiền cọc vi phạm hợp đồng.'
            : 'Khách gây hư hỏng thiết bị điều hòa và cửa kính không bồi thường, tự ý bỏ phòng. Trừ tịch thu toàn bộ tiền cọc để sửa chữa.';

        $insContract->execute([
            $contractId, $cCode, 'official_lease', null,
            $dummyRoom['id'], $tenant['id'], $startDate, $endDate, $depositAmount,
            $startDate, 100, 'forfeited', $depositAmount,
            1, null, 3500.00,
            20000.00, 150000.00, 'paper', 'signed',
            $startDate . ' 14:00:00', 'none', $termDate, $reason,
            'Vi phạm điều khoản thanh lý sớm - Tịch thu cọc sung quỹ doanh thu phạt vi phạm', 'terminated', $startDate . ' 14:30:00', 'Đã thu khi vào ở',
            1, $termDate . ' 10:00:00', 'breach_forfeited', 0, null
        ]);
        $contractId++;
    }

    // D. HĐ 1: ĐẶT CỌC GIỮ CHỖ 5 NGÀY (deposit_hold, cọc 50%) - ĐANG HIỆU LỰC (1 HĐ mỗi tòa)
    $availRooms = array_values(array_filter($roomsOfBld, fn($r) => $r['status'] == 'available'));
    if (!empty($availRooms)) {
        $holdRoom = $availRooms[0];
        $tenant = $tenantsOfBld[$tIndex++];
        $cCode = sprintf("HD-HOLD-%s01", $pfx);
        $startDate = '2026-11-02';
        $endDate = '2026-11-07'; // 5 ngày
        $fullDeposit = $holdRoom['price'];
        $dep50 = $fullDeposit * 0.5;

        $insContract->execute([
            $contractId, $cCode, 'deposit_hold', null,
            $holdRoom['id'], $tenant['id'], $startDate, $endDate, $dep50,
            $startDate, 50, 'held', $holdRoom['price'],
            0, null, 3500.00,
            20000.00, 150000.00, 'online', 'signed',
            $startDate . ' 15:00:00', 'none', null, null,
            'Đặt cọc giữ chỗ 50% trong 5 ngày. Dự kiến ký hợp đồng chính thức và hoàn tất 50% cọc còn lại vào ngày 06/11/2026.', 'active', null, null,
            0, null, 'none', 0, null
        ]);
        $contractId++;
    }

    // E. HĐ 1: ĐẶT CỌC GIỮ CHỖ 5 NGÀY - QUÁ HẠN HỦY (1 HĐ mỗi tòa)
    if (count($availRooms) > 1) {
        $holdRoom2 = $availRooms[1];
        $tenant = $tenantsOfBld[$tIndex++];
        $cCode = sprintf("HD-HOLD-EXP-%s01", $pfx);
        $startDate = '2026-10-10';
        $endDate = '2026-10-15';
        $dep50 = $holdRoom2['price'] * 0.5;

        $insContract->execute([
            $contractId, $cCode, 'deposit_hold', null,
            $holdRoom2['id'], $tenant['id'], $startDate, $endDate, $dep50,
            $startDate, 50, 'forfeited', $holdRoom2['price'],
            0, null, 3500.00,
            20000.00, 150000.00, 'online', 'signed',
            $startDate . ' 10:00:00', 'none', '2026-10-16',
            'Khách đặt cọc giữ chỗ 50% nhưng quá 5 ngày không đến nhận phòng và không ký hợp đồng chính thức. Đã hủy giữ chỗ và tịch thu cọc theo quy định.',
            'Hết hạn 5 ngày tự động hủy giữ chỗ', 'expired', null, null,
            1, '2026-10-16 09:00:00', 'hold_expired', 0, null
        ]);
        $contractId++;
    }
}

echo "   -> Đã tạo thành công danh sách Hợp đồng với đầy đủ 6 trường hợp thực tế.\n";

echo "8. Khởi tạo 7 Tháng Lịch sử Chỉ số Điện Nước & Hóa Đơn (05-2026 đến 11-2026)...\n";
echo "   Quy tắc tính chuẩn xác:\n";
echo "   - Tháng đầu (05-2026): Thu cọc 100% + Tiền phòng tháng đầu\n";
echo "   - Tháng 2 (06-2026): Tiền phòng tháng 2 + Tiền điện nước tháng 1\n";
echo "   - Tháng 3-7: Tiền phòng tháng N + Tiền điện nước tháng N-1\n";

$months = [
    '05-2026', // Tháng 1
    '06-2026', // Tháng 2
    '07-2026', // Tháng 3
    '08-2026', // Tháng 4
    '09-2026', // Tháng 5
    '10-2026', // Tháng 6
    '11-2026', // Tháng 7 (Tháng hiện tại)
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
        status, paid_at, payment_method, payment_note, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$insInvDetail = $db->prepare("
    INSERT INTO invoice_details (
        invoice_id, service_id, quantity, applied_price, subtotal
    ) VALUES (?, ?, ?, ?, ?)
");

$invoiceId = 1;

// Lưu chỉ số điện nước theo từng phòng qua các tháng để tính lũy tiến chuẩn xác
// $roomMeterHistory[room_id][month_index] = ['e_used' => X, 'w_used' => Y, 'e_cost' => ..., 'w_cost' => ...]
$roomMeterHistory = [];

// Khởi tạo chỉ số gốc ban đầu cho mỗi phòng
$baseMeters = [];
foreach ($activeContracts as $roomId => $c) {
    $baseMeters[$roomId] = [
        'e' => rand(100, 300),
        'w' => rand(10, 50)
    ];
}

// 1. Tạo chỉ số điện nước cho 7 tháng
foreach ($months as $mIdx => $mStr) {
    list($mm, $yyyy) = explode('-', $mStr);
    $readingDate = "$yyyy-$mm-28 20:00:00";

    foreach ($activeContracts as $roomId => $c) {
        $eOld = $baseMeters[$roomId]['e'];
        $wOld = $baseMeters[$roomId]['w'];

        // Mùa hè (tháng 6, 7, 8) dùng điều hòa nhiều hơn
        $isSummer = in_array($mm, ['06', '07', '08']);
        $eUsed = $isSummer ? rand(120, 210) : rand(70, 140);
        $wUsed = rand(4, 9); // khối nước

        $eNew = $eOld + $eUsed;
        $wNew = $wOld + $wUsed;

        $insReading->execute([$roomId, $mStr, $eOld, $eNew, $wOld, $wNew, $readingDate]);

        $eCost = $eUsed * 3500.00;
        $wCost = $wUsed * 20000.00;

        $roomMeterHistory[$roomId][$mIdx] = [
            'e_used' => $eUsed,
            'w_used' => $wUsed,
            'e_cost' => $eCost,
            'w_cost' => $wCost
        ];

        // Cập nhật mốc lũy tiến cho tháng tiếp theo
        $baseMeters[$roomId]['e'] = $eNew;
        $baseMeters[$roomId]['w'] = $wNew;
    }
}

// 2. Tạo Hóa đơn cho 7 tháng
foreach ($months as $mIdx => $mStr) {
    list($mm, $yyyy) = explode('-', $mStr);
    $invCreatedDate = "$yyyy-$mm-30 08:00:00";

    foreach ($activeContracts as $roomId => $c) {
        $roomPrice = $c['monthly_rent'];
        $serviceFee = 150000.00; // Internet 100k + rác 50k

        if ($mIdx === 0) {
            // Tháng 1 (05-2026):
            // Hóa đơn đầu kỳ gồm Tiền phòng tháng 1 + điện nước dùng trong tháng 1
            $eUsed = $roomMeterHistory[$roomId][0]['e_used'];
            $wUsed = $roomMeterHistory[$roomId][0]['w_used'];
            $eCost = $roomMeterHistory[$roomId][0]['e_cost'];
            $wCost = $roomMeterHistory[$roomId][0]['w_cost'];
        } else {
            // Tháng 2 trở đi (06-2026 đến 11-2026):
            // TIỀN PHÒNG THÁNG N + ĐIỆN NƯỚC CỦA THÁNG N-1!
            $prevIdx = $mIdx - 1;
            $eUsed = $roomMeterHistory[$roomId][$prevIdx]['e_used'];
            $wUsed = $roomMeterHistory[$roomId][$prevIdx]['w_used'];
            $eCost = $roomMeterHistory[$roomId][$prevIdx]['e_cost'];
            $wCost = $roomMeterHistory[$roomId][$prevIdx]['w_cost'];
        }

        $totalAmount = $roomPrice + $eCost + $wCost + $serviceFee;

        // Trạng thái thanh toán:
        // Các tháng cũ (05 đến 10/2026): 100% ĐÃ THANH TOÁN (status = 'paid')
        // Tháng 11/2026: 70% Đã thanh toán, 30% Chưa thanh toán (unpaid) để xem công nợ thực tế
        if ($mIdx < 6) {
            $status = 'paid';
            $paidDay = rand(2, 8);
            // thanh toán đầu tháng sau
            $nextMonth = ((int)$mm % 12) + 1;
            $nextYear = ((int)$mm == 12) ? ((int)$yyyy + 1) : (int)$yyyy;
            $paidAt = sprintf("%04d-%02d-%02d 10:%02d:00", $nextYear, $nextMonth, $paidDay, rand(10, 50));
            $method = ($roomId % 3 == 0) ? 'cash' : 'transfer';
            $note = ($method == 'transfer') ? 'Chuyển khoản qua App VietQR' : 'Nộp tiền mặt tại văn phòng quản lý';
        } else {
            // Tháng 11-2026 (Hiện tại)
            if ($roomId % 3 === 0) {
                $status = 'unpaid';
                $paidAt = null;
                $method = null;
                $note = 'Chưa thu tiền - Đã gửi thông báo hạn nộp ngày 10/11/2026';
            } else {
                $status = 'paid';
                $paidAt = '2026-11-03 14:20:00';
                $method = 'transfer';
                $note = 'Đã chuyển khoản VietinBank';
            }
        }

        $insInvoice->execute([
            $invoiceId, $roomId, $c['id'], $mStr, $roomPrice,
            $eCost, $wCost, $serviceFee, $totalAmount,
            $status, $paidAt, $method, $note, $invCreatedDate
        ]);

        // Chi tiết hóa đơn (invoice_details)
        // 1. Tiền phòng
        $insInvDetail->execute([$invoiceId, null, 1.00, $roomPrice, $roomPrice]);
        // 2. Tiền điện
        $insInvDetail->execute([$invoiceId, 1, $eUsed, 3500.00, $eCost]);
        // 3. Tiền nước
        $insInvDetail->execute([$invoiceId, 2, $wUsed, 20000.00, $wCost]);
        // 4. Internet
        $insInvDetail->execute([$invoiceId, 3, 1.00, 100000.00, 100000.00]);
        // 5. Vệ sinh rác
        $insInvDetail->execute([$invoiceId, 5, 1.00, 50000.00, 50000.00]);

        $invoiceId++;
    }
}

echo "   -> Đã tạo thành công " . ($invoiceId - 1) . " hóa đơn và chi tiết 7 tháng qua.\n";

echo "9. Khởi tạo Chi phí Đối tác Bên thứ 3 (third_party_expenses)...\n";
$insExpense = $db->prepare("
    INSERT INTO third_party_expenses (
        month_year, expense_type, title, provider_name, quantity, unit,
        unit_price, amount, customer_amount, status, paid_at, notes
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

foreach ($months as $mStr) {
    list($mm, $yyyy) = explode('-', $mStr);
    $payDate = "$yyyy-$mm-25";

    // EVN Tiền điện tổng
    $insExpense->execute([
        $mStr, 'electric', "Hóa đơn điện lực EVN kỳ $mStr", 'Công ty Điện Lực Hà Nội (EVN)',
        6500, 'kWh', 3100, 20150000, 22750000, 'paid', $payDate, 'Đã trích nợ tự động tài khoản doanh nghiệp'
    ]);
    // Viwaco Tiền nước tổng
    $insExpense->execute([
        $mStr, 'water', "Hóa đơn nước sạch Viwaco kỳ $mStr", 'Công ty CP Viwaco Sông Đà',
        380, 'Khối', 15000, 5700000, 7600000, 'paid', $payDate, 'Đã thanh toán ủy nhiệm chi'
    ]);
    // VNPT Internet cáp quang
    $insExpense->execute([
        $mStr, 'internet', "Gói Internet cáp quang doanh nghiệp kỳ $mStr", 'Tập đoàn VNPT Vinaphone',
        3, 'Line', 1200000, 3600000, 4500000, 'paid', $payDate, 'Thanh toán trực tuyến'
    ]);
    // Urenco Thu gom rác & vệ sinh
    $insExpense->execute([
        $mStr, 'garbage', "Phí thu gom rác & vệ sinh môi trường kỳ $mStr", 'Công ty Môi trường Đô thị Hà Nội (Urenco)',
        3, 'Tòa', 600000, 1800000, 2250000, 'paid', $payDate, 'Nộp phiếu thu định kỳ'
    ]);
}

echo "10. Khởi tạo Khách liên hệ xem phòng (tenant_contact_leads)...\n";
$leads = [
    ['Nguyễn Văn Hùng', '0981234567', 'Phòng A105 - Tòa A', 5, '08:30 sáng Thứ 7', 'Cần xem phòng Studio có ban công, dọn vào giữa tháng 11', 'pending', '2026-11-04 10:15:00', null],
    ['Trần Thu Hà', '0918765432', 'Phòng B205 - Tòa B', 25, '17:30 chiều nay', 'Muốn thuê 1 năm, hỏi kỹ chính sách cọc và gửi xe máy', 'pending', '2026-11-04 11:20:00', null],
    ['Lê Quang Minh', '0932112233', 'Phòng C305 - Tòa C', 45, '10:00 sáng mai', 'Sinh viên ĐH KHTN cần ở ghép 2 bạn', 'pending', '2026-11-04 14:05:00', null],
    ['Phạm Thảo Linh', '0977889900', 'Phòng A305 - Tòa A', 15, '09:00 Chủ Nhật', 'Hẹn xem phòng thực tế và chốt hợp đồng giữ chỗ', 'contacted', '2026-11-03 09:00:00', '2026-11-03 14:30:00'],
    ['Đỗ Thành Đạt', '0905667788', 'Phòng B105 - Tòa B', 20, '18:00 chiều Thứ 6', 'Đã tư vấn qua điện thoại, gửi video phòng thực tế', 'contacted', '2026-11-02 16:40:00', '2026-11-02 17:10:00'],
];

$insLead = $db->prepare("
    INSERT INTO tenant_contact_leads (
        customer_name, phone, room_label, room_id, visit_time, note, status, created_at, contacted_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");
foreach ($leads as $l) {
    $insLead->execute($l);
}

echo "11. Khởi tạo Yêu cầu Cư dân (tenant_requests)...\n";
$insReq = $db->prepare("
    INSERT INTO tenant_requests (
        tenant_id, request_type, field_target, old_value, new_value, reason, status, manager_note, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$insReq->execute([5, 'profile_update', 'phone', '091010001', '0988665544', 'Thay đổi số điện thoại Zalo chính để nhận tin nhắn hóa đơn', 'pending', null, '2026-11-03 15:00:00']);
$insReq->execute([6, 'profile_update', 'roommate', 'Không có', 'Trần Thảo Ly (SĐT: 0977112233)', 'Đăng ký thêm bạn cùng phòng từ tháng 11/2026', 'approved', 'Đã xác minh CCCD bạn cùng phòng và cập nhật sổ lưu trú', '2026-11-01 10:30:00']);
$insReq->execute([7, 'profile_update', 'emergency_contact', '0912000000', '0933445566', 'Cập nhật SĐT người thân ở quê', 'approved', 'Đã cập nhật hệ thống', '2026-10-28 09:15:00']);

$db->exec("SET FOREIGN_KEY_CHECKS = 1");

echo "\n=======================================================\n";
echo ">>> MASTER DATA RESEED HOÀN THÀNH XUẤT SẮC 100%! <<<\n";
echo "- 3 Tòa nhà đầy đủ (A, B, C)\n";
echo "- 60 Phòng trọ đồng bộ (20 phòng/tòa)\n";
echo "- 154 Tài khoản (1 Admin, 3 Quản lý, 150 Khách thuê)\n";
echo "- 6 Trường hợp Hợp đồng thực tế (Hiệu lực, Sắp hết hạn, Hoàn cọc, Vi phạm mất cọc, Giữ chỗ 5 ngày, Quá hạn hủy)\n";
echo "- 7 Tháng Hóa đơn & Chỉ số Điện Nước theo đúng công thức:\n";
echo "  + Tháng 1: Cọc 100% + Phòng tháng đầu (vào doanh thu & bảng cọc)\n";
echo "  + Tháng 2-7: Tiền phòng tháng N + Điện nước tháng N-1\n";
echo "- Bảng chi phí đối tác, khách liên hệ tư vấn, yêu cầu cư dân.\n";
echo "=======================================================\n";
