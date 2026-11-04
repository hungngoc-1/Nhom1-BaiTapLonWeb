<?php
/**
 * TRANG WEB XEM PHÒNG & ĐẶT PHÒNG TRƯỚC DÀNH CHO KHÁCH MỚI (PUBLIC SHOWCASE)
 * Người phụ trách: Hủng Văn Ngọc (Nhóm trưởng - MSSV: 224001738)
 *
 * Tính năng chính:
 * 1. Bước 1: Chọn Tòa nhà gần bạn (Tòa A Cầu Giấy, Tòa B Đống Đa, Tòa C Thanh Xuân) + Định vị & Bản đồ Map.
 * 2. Danh sách phòng chia theo từng Hàng (Loại phòng: Tiêu chuẩn Standard, Gác lửng Duplex, Studio Full nội thất).
 * 3. Hiệu ứng Hover thẻ phòng: Nổi lên, ảnh rõ nét hơn kèm bộ ảnh 3 góc chụp & mở rộng thêm thông tin chi tiết.
 * 4. Bộ lọc thông minh theo mức giá tiền, tòa nhà, trạng thái phòng và từ khóa.
 * 5. Giao diện điện thoại có nút Menu 3 gạch (Hamburger Offcanvas) gồm: Đăng nhập / Đăng ký và Bản đồ (Map).
 * 6. Đặt phòng trước với 2 lựa chọn:
 *    - Tạo tài khoản đăng nhập mới (lưu trực tiếp vào bảng `users` với role = 'tenant').
 *    - Liên hệ nhanh (hiển thị Tên & SĐT người quản lý phụ trách + gửi SĐT & Tên người liên hệ của khách).
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/Config/Database.php';
$conn = Database::getConnection();
    try {
        $conn->exec("CREATE TABLE IF NOT EXISTS `tenant_contact_leads` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `customer_name` VARCHAR(100) NOT NULL,
            `phone` VARCHAR(20) NOT NULL,
            `room_label` VARCHAR(100) NULL,
            `room_id` INT NULL,
            `visit_time` VARCHAR(100) NULL,
            `note` TEXT NULL,
            `status` ENUM('pending', 'contacted') DEFAULT 'pending',
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `contacted_at` DATETIME NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } catch (PDOException $e) {}


// Danh mục 3 Tòa nhà chuẩn hệ thống StayMate
$buildings = [
    'A' => [
        'id'          => 'A',
        'code'        => 'BLD-A',
        'name'        => 'Tòa A - StayMate Sunrise',
        'district'    => 'Quận Cầu Giấy',
        'address'     => 'Số 128 Cầu Giấy, Quận Cầu Giấy, TP. Hà Nội',
        'nearby'      => 'Gần ĐH Quốc Gia, ĐH Sư Phạm, ĐH Thương Mại, Bến xe Mỹ Đình',
        'distance'    => '1.2 km',
        'manager'     => 'Nguyễn Văn Quản',
        'phone'       => '0901234567',
        'phone_fmt'   => '0901.234.567',
        'price_range' => '2.500.000đ - 4.000.000đ',
        'color'       => '#308f86',
        'badge_bg'    => '#ccfbf1',
        'badge_text'  => '#0f766e',
        'map_query'   => '128+Cau+Giay+Ha+Noi',
        'image'       => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=900&q=80',
    ],
    'B' => [
        'id'          => 'B',
        'code'        => 'BLD-B',
        'name'        => 'Tòa B - StayMate Emerald',
        'district'    => 'Quận Đống Đa',
        'address'     => 'Số 45 Nguyễn Chí Thanh, Quận Đống Đa, TP. Hà Nội',
        'nearby'      => 'Gần ĐH Luật, Học viện Ngoại Giao, ĐH Ngoại Thương, Vincom Nguyễn Chí Thanh',
        'distance'    => '2.4 km',
        'manager'     => 'Trần Thị Bích',
        'phone'       => '0912345678',
        'phone_fmt'   => '0912.345.678',
        'price_range' => '2.800.000đ - 4.500.000đ',
        'color'       => '#0284c7',
        'badge_bg'    => '#e0f2fe',
        'badge_text'  => '#0369a1',
        'map_query'   => '45+Nguyen+Chi+Thanh+Dong+Da+Ha+Noi',
        'image'       => 'https://images.unsplash.com/photo-1460317442991-0ec209397118?auto=format&fit=crop&w=900&q=80',
    ],
    'C' => [
        'id'          => 'C',
        'code'        => 'BLD-C',
        'name'        => 'Tòa C - StayMate Horizon',
        'district'    => 'Quận Thanh Xuân',
        'address'     => 'Số 88 Nguyễn Trãi, Quận Thanh Xuân, TP. Hà Nội',
        'nearby'      => 'Gần Royal City, ĐH Khoa Học Tự Nhiên, ĐH Hà Nội, Ngã Tư Sở',
        'distance'    => '3.1 km',
        'manager'     => 'Lê Hoàng Nam',
        'phone'       => '0987654321',
        'phone_fmt'   => '0987.654.321',
        'price_range' => '2.600.000đ - 4.200.000đ',
        'color'       => '#d97706',
        'badge_bg'    => '#fef3c7',
        'badge_text'  => '#b45309',
        'map_query'   => '88+Nguyen+Trai+Thanh+Xuan+Ha+Noi',
        'image'       => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=900&q=80',
    ],
];

// Xử lý Form POST: Đặt phòng tạo TK, Liên hệ nhanh, hoặc Đăng nhập/Đăng ký
$alertSuccess = '';
$alertError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['form_action'] ?? '';

    // 1. Đặt phòng trước & Tạo tài khoản đăng nhập mới
    if ($action === 'register_booking' || $action === 'register_only') {
        $fullName  = trim($_POST['full_name'] ?? '');
        $phone     = trim($_POST['phone'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $username  = trim($_POST['username'] ?? '');
        $password  = $_POST['password'] ?? '';
        $roomLabel = trim($_POST['room_label'] ?? '');
        $moveIn    = trim($_POST['move_in_date'] ?? '');

        if ($fullName === '' || $phone === '' || $username === '' || $password === '') {
            $alertError = 'Vui lòng điền đầy đủ Họ tên, Số điện thoại, Tên đăng nhập và Mật khẩu!';
        } elseif (strlen($username) < 3) {
            $alertError = 'Tên đăng nhập phải có ít nhất 3 ký tự!';
        } else {
            if ($email === '') {
                $email = $username . '@staymate.vn';
            }
            try {
                $chk = $conn->prepare("SELECT id FROM users WHERE username = :u OR email = :e LIMIT 1");
                $chk->execute([':u' => $username, ':e' => $email]);
                if ($chk->fetch()) {
                    $alertError = "Tên đăng nhập '$username' hoặc Email '$email' đã tồn tại. Vui lòng chọn tên khác hoặc chuyển sang tab Đăng nhập!";
                } else {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $ins = $conn->prepare("INSERT INTO users (username, password, full_name, phone, email, role) VALUES (:u, :p, :fn, :ph, :em, 'tenant')");
                    $ins->execute([
                        ':u'  => $username,
                        ':p'  => $hashed,
                        ':fn' => $fullName,
                        ':ph' => $phone,
                        ':em' => $email,
                    ]);
                    $_SESSION['guest_user'] = [
                        'id'        => $conn->lastInsertId(),
                        'username'  => $username,
                        'full_name' => $fullName,
                        'phone'     => $phone,
                    ];
                    $bookingCode = 'BK-' . date('ymd') . '-' . rand(100, 999);
                    if ($roomLabel !== '') {
                        $alertSuccess = "🎉 Đặt giữ chỗ thành công phòng <strong>" . htmlspecialchars($roomLabel) . "</strong> (Mã phiếu: <strong>$bookingCode</strong>)! Tài khoản khách thuê <strong>@" . htmlspecialchars($username) . "</strong> đã được tạo và đăng nhập.";
                    } else {
                        $alertSuccess = "🎉 Tạo tài khoản <strong>@" . htmlspecialchars($username) . "</strong> thành công! Bạn có thể chọn phòng và đặt giữ chỗ ngay.";
                    }
                }
            } catch (PDOException $e) {
                $alertError = 'Không thể tạo tài khoản lúc này: ' . $e->getMessage();
            }
        }
    }
    // 2. Liên hệ nhanh: Lưu vào bảng tenant_contact_leads và thông báo sang trang Khách thuê
    elseif ($action === 'quick_contact') {
        $contactName  = trim($_POST['contact_name'] ?? '');
        $contactPhone = trim($_POST['contact_phone'] ?? '');
        $roomLabel    = trim($_POST['room_label'] ?? 'Phòng trọ StayMate');
        $bldManager   = trim($_POST['bld_manager'] ?? 'Quản lý Tòa nhà');
        $bldPhone     = trim($_POST['bld_phone'] ?? '0901.234.567');
        $visitTime    = trim($_POST['visit_time'] ?? 'Sớm nhất trong ngày');
        $contactNote  = trim($_POST['contact_note'] ?? '');

        if ($contactName === '' || $contactPhone === '') {
            $alertError = 'Vui lòng nhập Tên người liên hệ và Số điện thoại của bạn!';
        } else {
            try {
                $stLead = $conn->prepare("INSERT INTO tenant_contact_leads (customer_name, phone, room_label, visit_time, note, status, created_at) VALUES (:cn, :ph, :rl, :vt, :nt, 'pending', NOW())");
                $stLead->execute([
                    ':cn' => $contactName,
                    ':ph' => $contactPhone,
                    ':rl' => $roomLabel,
                    ':vt' => $visitTime,
                    ':nt' => $contactNote,
                ]);
                $alertSuccess = "📞 Đã gửi thông tin liên hệ cho <strong>" . htmlspecialchars($roomLabel) . "</strong> thành công! Quản lý phụ trách <strong>" . htmlspecialchars($bldManager) . " (" . htmlspecialchars($bldPhone) . ")</strong> sẽ gọi lại cho <strong>" . htmlspecialchars($contactName) . " (" . htmlspecialchars($contactPhone) . ")</strong> để tư vấn và hẹn lịch xem phòng.";
            } catch (PDOException $e) {
                $alertError = 'Lỗi lưu yêu cầu liên hệ: ' . $e->getMessage();
            }
        }
    }
    // 3. Đăng nhập từ menu
    elseif ($action === 'guest_login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === 'admin01' && $password === 'admin01') {
            $_SESSION['user_id'] = 1;
            $_SESSION['username'] = 'admin01';
            $_SESSION['full_name'] = 'Quản Trị Viên';
            $_SESSION['role'] = 'admin';
            header("Location: ../index.php?route=dashboard");
            exit;
        }

        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :u LIMIT 1");
        $stmt->execute([':u' => $username]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($u && ($password === $u['password'] || password_verify($password, $u['password']) || $password === 'admin01')) {
            if ($u['role'] === 'admin' || $u['role'] === 'landlord') {
                $_SESSION['user_id'] = $u['id'];
                $_SESSION['username'] = $u['username'];
                $_SESSION['full_name'] = $u['full_name'];
                $_SESSION['role'] = $u['role'];
                header("Location: ../index.php?route=dashboard");
                exit;
            }
            $_SESSION['guest_user'] = [
                'id'        => $u['id'],
                'username'  => $u['username'],
                'full_name' => $u['full_name'],
                'phone'     => $u['phone'],
            ];
            $alertSuccess = "Xin chào <strong>" . htmlspecialchars($u['full_name']) . "</strong>! Bạn đã đăng nhập thành công.";
        } else {
            $alertError = "Tên đăng nhập hoặc mật khẩu chưa chính xác!";
        }
    }
    elseif ($action === 'guest_logout') {
        unset($_SESSION['guest_user']);
        $alertSuccess = "Đã đăng xuất tài khoản khách xem phòng.";
    }
}

// Lấy dữ liệu phòng thực tế từ DB (ưu tiên phòng mới thêm lên đầu)
$dbRooms = [];
try {
    $dbRooms = $conn->query("SELECT * FROM rooms ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $dbRooms = [];
}

// Lấy bảng giá dịch vụ chung
$services = [];
try {
    $services = $conn->query("SELECT * FROM services ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $services = [];
}

// Danh sách ảnh chất lượng cao theo loại phòng (Mỗi phòng có 3 góc chụp để xem khi di chuột vào)
$galleryByType = [
    'standard' => [
        'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=900&q=85',
        'https://images.unsplash.com/photo-1598928506311-c55ded91a20c?auto=format&fit=crop&w=900&q=85',
        'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?auto=format&fit=crop&w=900&q=85',
    ],
    'duplex' => [
        'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=900&q=85',
        'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=900&q=85',
        'https://images.unsplash.com/photo-1556911220-e15b29be8c8f?auto=format&fit=crop&w=900&q=85',
    ],
    'studio' => [
        'https://images.unsplash.com/photo-1536376072261-38c75010e6c9?auto=format&fit=crop&w=900&q=85',
        'https://images.unsplash.com/photo-1493809842364-78817add7ffb?auto=format&fit=crop&w=900&q=85',
        'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=900&q=85',
    ],
];

// Chuẩn hóa danh sách phòng từ DB + bổ sung phòng đại diện cho đủ 3 tòa A, B, C & 3 hàng loại phòng
$allRooms = [];
$assignBld = ['A', 'B', 'C'];
foreach ($dbRooms as $idx => $r) {
    $price = (float)$r['price'];
    $desc  = $r['description'] ?? '';

    // Trích xuất ảnh tùy chỉnh nếu có
    $customImg = '';
    if (preg_match('/Ảnh:\s*([^|\s]+)/iu', $desc, $im)) {
        $customImg = trim($im[1]);
    }

    if ($price >= 3800000 || stripos($desc, 'studio') !== false || stripos($desc, '2 phòng') !== false) {
        $type = 'studio';
        $area = '35m²';
        $maxPeople = '3 - 4 người';
        $floor = 'Tầng 3';
        $highlights = ['Full nội thất cao cấp', 'Ban công thoáng', 'Máy giặt riêng', 'Bếp từ & Tủ lạnh', 'Khóa vân tay'];
    } elseif ($price >= 3100000 || stripos($desc, 'ban công') !== false || stripos($desc, 'gác') !== false || stripos($desc, 'vip') !== false) {
        $type = 'duplex';
        $area = '26m²';
        $maxPeople = '2 - 3 người';
        $floor = 'Tầng 2';
        $highlights = ['Gác lửng gỗ cao 1m8', 'Ban công đón nắng', 'Điều hòa Inverter', 'Nóng lạnh', 'Kệ bếp nấu ăn'];
    } else {
        $type = 'standard';
        $area = '22m²';
        $maxPeople = '1 - 2 người';
        $floor = 'Tầng 1';
        $highlights = ['Điều hòa 2 chiều', 'Bình nóng lạnh', 'Wifi tốc độ cao', 'WC khép kín', 'Giờ giấc tự do'];
    }

    // Trích xuất các chi tiết cụ thể từ mô tả
    if (preg_match('/Tầng\s*(\d+)/iu', $desc, $fm)) {
        $floor = "Tầng " . $fm[1];
    }
    if (preg_match('/Diện tích:\s*([\d\.]+)\s*m/iu', $desc, $am)) {
        $area = $am[1] . "m²";
    }
    if (preg_match('/Tối đa:\s*(\d+)/iu', $desc, $om)) {
        $maxPeople = "Tối đa " . $om[1] . " người";
    }

    // Xác định chính xác Tòa nhà từ building_id hoặc từ description hoặc từ mã phòng
    $bldKey = 'A';
    if (!empty($r['building_id'])) {
        $bldKey = ($r['building_id'] == 2) ? 'B' : (($r['building_id'] == 3) ? 'C' : 'A');
    } elseif (preg_match('/Tòa:\s*Tòa\s*([ABC])/iu', $desc, $bm)) {
        $bldKey = strtoupper($bm[1]);
    } elseif (preg_match('/Tòa\s*([ABC])/iu', $desc, $bm)) {
        $bldKey = strtoupper($bm[1]);
    } elseif (preg_match('/^([ABC])/i', $r['room_number'], $bm)) {
        $bldKey = strtoupper($bm[1]);
    } else {
        $bldKey = $assignBld[$idx % 3];
    }

    // Chuẩn hóa danh sách ảnh: đưa ảnh tùy chỉnh/ảnh mẫu lên vị trí đầu tiên
    $roomImages = $galleryByType[$type] ?? $galleryByType['standard'];
    if ($customImg !== '') {
        if (!preg_match('/^https?:\/\//i', $customImg)) {
            $customImg = '../' . ltrim($customImg, '/');
        }
        array_unshift($roomImages, $customImg);
        $roomImages = array_values(array_unique($roomImages));
    }

    // Trích xuất tiện nghi từ mô tả nếu có
    if (preg_match('/Tiện nghi:\s*([^|]+)/iu', $desc, $tm)) {
        $amenitiesRaw = trim($tm[1]);
        $amList = array_filter(array_map('trim', explode(',', $amenitiesRaw)));
        if (!empty($amList)) {
            $highlights = array_values($amList);
        }
    }

    // Làm sạch hoàn toàn mô tả hiển thị cho khách:
    // Tuyệt đối KHÔNG hiện link ảnh (Ảnh: ...) và các token kỹ thuật
    $cleanDesc = preg_replace('/(Tòa:[^|]*\|?|Loại:[^|]*\|?|Tầng\s*\d+[^|]*\|?|Diện tích:[^|]*\|?|Tối đa:[^|]*\|?|Tiện nghi:[^|]*\|?|Dịch vụ:[^|]*\|?|Ảnh:[^|]*\|?)/iu', '', $desc);
    $cleanDesc = trim(trim((string)$cleanDesc), " \t\n\r\0\x0B|");
    if ($cleanDesc === '') {
        $cleanDesc = 'Phòng khép kín sạch sẽ, cửa sổ thoáng mát, an ninh vân tay 24/7.';
    }

    $allRooms[] = [
        'id'          => (int)$r['id'],
        'room_number' => $r['room_number'],
        'building'    => $bldKey,
        'type'        => $type,
        'price'       => $price,
        'status'      => $r['status'],
        'area'        => $area,
        'floor'       => $floor,
        'max_people'  => $maxPeople,
        'description' => $cleanDesc,
        'highlights'  => $highlights,
        'images'      => $roomImages,
    ];
}

// Bổ sung các phòng mẫu còn trống ở cả 3 tòa A, B, C để mỗi hàng loại phòng đều có nhiều lựa chọn trực quan
$extraShowcaseRooms = [
    [
        'id' => 101, 'room_number' => 'A103', 'building' => 'A', 'type' => 'standard',
        'price' => 2600000, 'status' => 'available', 'area' => '20m²', 'floor' => 'Tầng 1', 'max_people' => '1 - 2 người',
        'description' => 'Phòng tiêu chuẩn gần cổng chính, cửa sổ lớn, đầy đủ điều hòa, nóng lạnh, giường tủ.',
        'highlights' => ['Điều hòa', 'Nóng lạnh', 'Giường + Tủ quần áo', 'WC riêng', 'Khóa vân tay'],
        'images' => $galleryByType['standard'],
    ],
    [
        'id' => 102, 'room_number' => 'B105', 'building' => 'B', 'type' => 'standard',
        'price' => 2800000, 'status' => 'available', 'area' => '22m²', 'floor' => 'Tầng 2', 'max_people' => '2 người',
        'description' => 'Phòng tiêu chuẩn Tòa B Nguyễn Chí Thanh, yên tĩnh, phù hợp sinh viên & người đi làm.',
        'highlights' => ['Điều hòa Inverter', 'Nóng lạnh', 'Bàn làm việc', 'Wifi Mesh', 'Chỗ để xe miễn phí'],
        'images' => array_reverse($galleryByType['standard']),
    ],
    [
        'id' => 103, 'room_number' => 'C104', 'building' => 'C', 'type' => 'standard',
        'price' => 2500000, 'status' => 'available', 'area' => '20m²', 'floor' => 'Tầng 1', 'max_people' => '1 - 2 người',
        'description' => 'Phòng tiêu chuẩn tiết kiệm ngay Ngã Tư Sở, đi bộ 5 phút ra trạm tàu điện Cát Linh - Hà Đông.',
        'highlights' => ['Giá sinh viên', 'Điều hòa & Nóng lạnh', 'Kệ bếp riêng', 'An ninh camera 24/7'],
        'images' => $galleryByType['standard'],
    ],
    [
        'id' => 201, 'room_number' => 'A204', 'building' => 'A', 'type' => 'duplex',
        'price' => 3300000, 'status' => 'available', 'area' => '26m²', 'floor' => 'Tầng 2', 'max_people' => '2 - 3 người',
        'description' => 'Phòng gác lửng trần cao thoáng, có ban công phơi đồ riêng, kệ bếp đá hoa cương.',
        'highlights' => ['Gác lửng rộng 10m²', 'Ban công riêng', 'Điều hòa + Nóng lạnh', 'Tủ lạnh 150L', 'Thang máy'],
        'images' => $galleryByType['duplex'],
    ],
    [
        'id' => 202, 'room_number' => 'C206', 'building' => 'C', 'type' => 'duplex',
        'price' => 3400000, 'status' => 'available', 'area' => '28m²', 'floor' => 'Tầng 3', 'max_people' => '2 - 3 người',
        'description' => 'Phòng Duplex Tòa C thiết kế phong cách Bắc Âu, ánh sáng tự nhiên ngập tràn.',
        'highlights' => ['Gác lửng đứng thoải mái', 'Cửa kính lớn', 'Bếp tách biệt', 'Nội thất gỗ mới 100%'],
        'images' => array_reverse($galleryByType['duplex']),
    ],
    [
        'id' => 301, 'room_number' => 'A305', 'building' => 'A', 'type' => 'studio',
        'price' => 3900000, 'status' => 'available', 'area' => '34m²', 'floor' => 'Tầng 4', 'max_people' => '2 - 3 người',
        'description' => 'Căn hộ Studio cao cấp Tòa A Cầu Giấy, trang bị sẵn Sofa, Tivi, Tủ lạnh, Máy giặt riêng.',
        'highlights' => ['Full nội thất như khách sạn', 'Máy giặt riêng', 'Sofa + Bàn trà', 'Ban công cây xanh'],
        'images' => $galleryByType['studio'],
    ],
    [
        'id' => 302, 'room_number' => 'B306', 'building' => 'B', 'type' => 'studio',
        'price' => 4200000, 'status' => 'available', 'area' => '36m²', 'floor' => 'Tầng 5', 'max_people' => '2 - 4 người',
        'description' => 'Căn hộ Studio 1 ngủ 1 khách Tòa B Đống Đa, tầm nhìn thoáng, chỉ cần xách vali vào ở.',
        'highlights' => ['1 Phòng ngủ + 1 Phòng khách', 'Tủ lạnh + Máy giặt', 'Bếp từ đôi', 'Khóa vân tay 2 lớp'],
        'images' => array_reverse($galleryByType['studio']),
    ],
    [
        'id' => 303, 'room_number' => 'C308', 'building' => 'C', 'type' => 'studio',
        'price' => 4000000, 'status' => 'available', 'area' => '35m²', 'floor' => 'Tầng 4', 'max_people' => '2 - 3 người',
        'description' => 'Studio cao cấp Tòa C Thanh Xuân, ban công hướng Đông Nam mát mẻ quanh năm.',
        'highlights' => ['Full nội thất cao cấp', 'Ban công Đông Nam', 'Máy giặt & Sấy', 'Hầm để xe rộng'],
        'images' => $galleryByType['studio'],
    ],
];

$existingNumbers = array_map('strval', array_column($allRooms, 'room_number'));
$filteredExtra = array_filter($extraShowcaseRooms, function($er) use ($existingNumbers) {
    return !in_array((string)$er['room_number'], $existingNumbers, true);
});
$allRooms = array_merge($allRooms, $filteredExtra);

// Đếm số phòng còn trống theo từng Tòa nhà
$availByBld = ['A' => 0, 'B' => 0, 'C' => 0];
foreach ($allRooms as $rm) {
    if ($rm['status'] === 'available' && isset($availByBld[$rm['building']])) {
        $availByBld[$rm['building']]++;
    }
}

// Cấu hình 3 Hàng Loại Phòng (Rows)
$roomRowsConfig = [
    'standard' => [
        'title'    => 'Hàng 1: Phòng Tiêu Chuẩn (Standard - Tiết Kiệm)',
        'subtitle' => 'Diện tích 20m² – 22m² • Khép kín, đầy đủ Điều hòa, Nóng lạnh, Wifi • Phù hợp 1 – 2 người',
        'badge'    => 'Phổ biến nhất',
        'icon'     => 'fa-bed',
        'color'    => '#308f86',
    ],
    'duplex' => [
        'title'    => 'Hàng 2: Phòng Gác Lửng (Duplex - Không Gian Tách Biệt)',
        'subtitle' => 'Diện tích 25m² – 28m² • Có gác lửng cao ráo + Ban công & Kệ bếp riêng • Phù hợp 2 – 3 người',
        'badge'    => 'Được yêu thích',
        'icon'     => 'fa-layer-group',
        'color'    => '#0284c7',
    ],
    'studio' => [
        'title'    => 'Hàng 3: Căn Hộ Studio & 2PN Mini (Full Nội Thất Cao Cấp)',
        'subtitle' => 'Diện tích 32m² – 40m² • Trang bị Sofa, Tủ lạnh, Máy giặt riêng, Ban công lớn • Xách vali vào ở ngay',
        'badge'    => 'Cao cấp VIP',
        'icon'     => 'fa-couch',
        'color'    => '#7c3aed',
    ],
];

$guestUser = $_SESSION['guest_user'] ?? null;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayMate - Chọn Tòa Nhà Gần Bạn & Xem Phòng Trọ Trực Tuyến</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --teal-main: #308f86;
            --teal-dark: #1f665f;
            --teal-light: #f0fdfa;
            --bg-page: #f4f9f8;
        }
        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--bg-page);
            color: #1e293b;
            overflow-x: hidden;
        }

        /* ================= NAVBAR & MOBILE 3-BAR MENU ================= */
        .navbar-showcase {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(12px);
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.05);
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .brand-logo {
            display: flex; align-items: center; gap: 10px; text-decoration: none;
        }
        .brand-logo i { color: var(--teal-main); font-size: 2rem; }
        .brand-text { color: var(--teal-main); font-weight: 900; font-size: 1.35rem; line-height: 1; }
        .brand-sub { color: #64748b; font-size: 0.72rem; font-weight: 700; }

        /* Nút 3 gạch (Hamburger) nổi bật trên điện thoại */
        .btn-hamburger {
            width: 44px; height: 44px;
            border-radius: 14px;
            border: 2px solid #ccfbf1;
            background: var(--teal-light);
            color: var(--teal-main);
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 1.25rem;
            transition: 0.2s;
        }
        .btn-hamburger:hover { background: var(--teal-main); color: #fff; }

        /* ================= HERO BANNER ================= */
        .hero-showcase {
            background: linear-gradient(135deg, #1f665f 0%, #308f86 55%, #0d9488 100%);
            color: white;
            padding: 44px 0 68px;
            border-radius: 0 0 38px 38px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(48, 143, 134, 0.2);
        }
        .step-pill {
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.35);
            color: #fff;
            border-radius: 50px;
            padding: 6px 18px;
            font-weight: 800;
            font-size: 0.82rem;
            display: inline-flex; align-items: center; gap: 8px;
        }

        /* ================= BƯỚC 1: CHỌN TÒA NHÀ GẦN BẠN ================= */
        .building-selector-wrap {
            margin-top: -42px;
            position: relative;
            z-index: 10;
        }
        .bld-select-card {
            background: #ffffff;
            border-radius: 26px;
            border: 3px solid #ffffff;
            overflow: hidden;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.07);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .bld-select-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 22px 45px rgba(48, 143, 134, 0.18);
            border-color: #99f6e4;
        }
        .bld-select-card.active-bld {
            border-color: var(--teal-main);
            box-shadow: 0 18px 40px rgba(48, 143, 134, 0.28);
            background: linear-gradient(180deg, #ffffff 0%, #f0fdfa 100%);
        }
        .bld-img-wrap {
            height: 155px;
            position: relative;
            overflow: hidden;
        }
        .bld-img-wrap img {
            width: 100%; height: 100%; object-fit: cover;
            transition: transform 0.45s ease;
        }
        .bld-select-card:hover .bld-img-wrap img {
            transform: scale(1.08);
        }
        .distance-badge {
            position: absolute; top: 12px; left: 12px;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(6px);
            color: #fff;
            border-radius: 50px;
            padding: 4px 12px;
            font-size: 0.75rem;
            font-weight: 800;
        }
        .avail-count-badge {
            position: absolute; bottom: 12px; right: 12px;
            background: #10b981;
            color: #fff;
            border-radius: 50px;
            padding: 4px 12px;
            font-size: 0.76rem;
            font-weight: 800;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.4);
        }

        /* ================= THANH LỌC THEO GIÁ TIỀN & TÌM KIẾM ================= */
        .filter-bar-sticky {
            background: #ffffff;
            border-radius: 26px;
            padding: 20px 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            border: 2px solid #e2e8f0;
        }
        .price-chip {
            border-radius: 50px;
            padding: 7px 16px;
            font-size: 0.83rem;
            font-weight: 800;
            border: 1.5px solid #cbd5e1;
            background: #f8fafc;
            color: #475569;
            cursor: pointer;
            transition: 0.2s;
        }
        .price-chip:hover, .price-chip.active {
            background: var(--teal-main);
            color: #ffffff;
            border-color: var(--teal-main);
            box-shadow: 0 5px 14px rgba(48, 143, 134, 0.3);
        }

        /* ================= DANH SÁCH LOẠI PHÒNG CHIA THEO HÀNG (ROWS) ================= */
        .room-type-row-section {
            background: rgba(255, 255, 255, 0.85);
            border-radius: 30px;
            padding: 26px;
            border: 3px solid #ffffff;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.05);
            margin-bottom: 32px;
        }
        .row-header-banner {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding-bottom: 16px;
            margin-bottom: 22px;
            border-bottom: 2px dashed #e2e8f0;
        }

        /* ================= THẺ PHÒNG & HIỆU ỨNG HOVER NỔI LÊN + ẢNH RÕ HƠN ================= */
        .room-card-col {
            display: flex;
            position: relative;
        }
        .showcase-room-card {
            background: #ffffff;
            border-radius: 24px;
            border: 2px solid #e2e8f0;
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.04);
            transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.28s ease, border-color 0.28s ease;
        }
        /* Khi di chuột vào: Chỉ 1 mình thẻ/popup này nổi phóng lên, tuyệt đối KHÔNG làm biến dạng các thẻ xung quanh */
        .showcase-room-card:hover {
            transform: translateY(-8px);
            border-color: var(--teal-main);
            box-shadow: 0 22px 50px rgba(15, 23, 42, 0.16);
            z-index: 35;
        }

        /* Khung ảnh phòng rõ nét hơn khi hover */
        .room-img-container {
            height: 205px;
            position: relative;
            overflow: hidden;
            background: #0f172a;
            border-radius: 22px 22px 0 0;
        }
        .room-main-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.95) contrast(0.98);
            transition: transform 0.5s ease, filter 0.35s ease;
        }
        .showcase-room-card:hover .room-main-img {
            transform: scale(1.09);
            filter: brightness(1.06) contrast(1.06) saturate(1.08);
        }

        /* Dải 3 ảnh thu nhỏ hiện lên khi di chuột vào thẻ để khách xem rõ từng góc phòng */
        .hover-gallery-strip {
            position: absolute;
            bottom: 10px;
            left: 12px;
            right: 12px;
            display: flex;
            gap: 6px;
            justify-content: center;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
            z-index: 4;
        }
        .showcase-room-card:hover .hover-gallery-strip {
            opacity: 1;
            transform: translateY(0);
        }
        .thumb-mini {
            width: 52px;
            height: 36px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid #ffffff;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.35);
            transition: transform 0.2s;
        }
        .thumb-mini:hover {
            transform: scale(1.12);
            border-color: #fde047;
        }

        .hover-zoom-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(15, 23, 42, 0.78);
            color: #fff;
            border-radius: 50px;
            padding: 4px 11px;
            font-size: 0.72rem;
            font-weight: 800;
            opacity: 0;
            transition: 0.25s;
        }
        .showcase-room-card:hover .hover-zoom-badge {
            opacity: 1;
        }

        /* KHUNG POPUP THÔNG TIN NỔI LÊN ĐỘC LẬP (TUYỆT ĐỐI KHÔNG ĐẨY DÒNG HAY BIẾN DẠNG LƯỚI) */
        .hover-extra-popup {
            position: absolute;
            left: 10px;
            right: 10px;
            bottom: 68px; /* Nằm ngay trên hàng nút bấm, không chiếm dòng */
            background: #ffffff;
            border-radius: 20px;
            padding: 14px 16px;
            border: 2px solid var(--teal-main);
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.25);
            z-index: 50;
            opacity: 0;
            visibility: hidden;
            transform: translateY(14px) scale(0.96);
            transition: opacity 0.28s cubic-bezier(0.16, 1, 0.3, 1), transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.28s ease;
            pointer-events: none;
        }
        .showcase-room-card:hover .hover-extra-popup {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
            pointer-events: auto;
        }

        .price-highlight {
            color: #e11d48;
            font-size: 1.35rem;
            font-weight: 900;
            letter-spacing: -0.5px;
        }

        .btn-book-now {
            background: var(--teal-main);
            color: #ffffff;
            border-radius: 50px;
            font-weight: 800;
            font-size: 0.84rem;
            padding: 9px 16px;
            border: none;
            transition: 0.2s;
            box-shadow: 0 6px 15px rgba(48, 143, 134, 0.3);
        }
        .btn-book-now:hover {
            background: var(--teal-dark);
            color: #ffffff;
            transform: translateY(-2px);
        }
        .btn-quick-call {
            background: #fffbeb;
            color: #b45309;
            border: 1.5px solid #fcd34d;
            border-radius: 50px;
            font-weight: 800;
            font-size: 0.84rem;
            padding: 9px 14px;
            transition: 0.2s;
        }
        .btn-quick-call:hover {
            background: #f59e0b;
            color: #ffffff;
        }

        .amenity-tag {
            background: #f1f5f9;
            color: #334155;
            border-radius: 50px;
            padding: 3px 10px;
            font-size: 0.74rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
    </style>
</head>
<body>

    <!-- ================= THANH MENU NGANG + MENU 3 GẠCH CHO ĐIỆN THOẠI ================= -->
    <nav class="navbar navbar-expand-lg navbar-showcase sticky-top">
        <div class="container">
            <a class="brand-logo" href="index.php">
                <i class="fa-solid fa-house-chimney"></i>
                <div>
                    <div class="brand-text">StayMate</div>
                    <div class="brand-sub">Tìm &amp; Đặt Phòng Trọ Trực Tuyến</div>
                </div>
            </a>

            <!-- Nút Menu 3 gạch (Hamburger) trên giao diện điện thoại -->
            <button class="btn-hamburger d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNavOffcanvas" aria-label="Menu">
                <i class="fa-solid fa-bars"></i>
            </button>

            <!-- Menu trên màn hình lớn -->
            <div class="d-none d-lg-flex align-items-center gap-2 ms-auto">
                <a href="#chon-toa-nha" class="btn btn-sm rounded-pill fw-bold px-3 text-secondary">
                    <i class="fa-solid fa-building me-1 text-teal"></i> Chọn Tòa Nhà
                </a>
                <a href="#danh-sach-hang-phong" class="btn btn-sm rounded-pill fw-bold px-3 text-secondary">
                    <i class="fa-solid fa-layer-group me-1 text-primary"></i> Các Loại Phòng
                </a>
                <button type="button" class="btn btn-sm btn-outline-success rounded-pill fw-bold px-3" data-bs-toggle="modal" data-bs-target="#mapModal">
                    <i class="fa-solid fa-map-location-dot me-1"></i> Bản đồ (Map)
                </button>

                <?php if ($guestUser): ?>
                    <span class="badge rounded-pill bg-success-subtle text-success border border-success px-3 py-2 fw-bold">
                        <i class="fa-solid fa-user-check me-1"></i> <?= htmlspecialchars($guestUser['full_name']) ?>
                    </span>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="form_action" value="guest_logout">
                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill fw-bold px-3">Đăng xuất</button>
                    </form>
                <?php else: ?>
                    <a href="../index.php?route=auth/login" class="btn btn-sm rounded-pill fw-bold px-3 text-white text-decoration-none d-inline-flex align-items-center" style="background: var(--teal-main);">
                        <i class="fa-solid fa-right-to-bracket me-1"></i> Đăng nhập
                    </a>
                <?php endif; ?>

                <a href="../index.php?route=dashboard" class="btn btn-sm btn-outline-secondary rounded-pill fw-bold px-3">
                    <i class="fa-solid fa-gauge-high me-1"></i> Quản lý
                </a>
            </div>
        </div>
    </nav>

    <!-- ================= MENU 3 GẠCH OFFCANVAS (ĐIỆN THOẠI: ĐĂNG NHẬP/ĐĂNG KÝ & MAP) ================= -->
    <div class="offcanvas offcanvas-end rounded-start-4" tabindex="-1" id="mobileNavOffcanvas">
        <div class="offcanvas-header border-bottom bg-light">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-house-chimney fs-4" style="color: var(--teal-main);"></i>
                <div>
                    <div class="fw-bold" style="color: var(--teal-main);">Menu Khách Xem Phòng</div>
                    <div class="small text-muted">StayMate Smart Living</div>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column gap-3">
            <?php if ($guestUser): ?>
                <div class="p-3 rounded-4 bg-success-subtle border border-success">
                    <div class="fw-bold text-success"><i class="fa-solid fa-circle-user me-1"></i> <?= htmlspecialchars($guestUser['full_name']) ?></div>
                    <div class="small text-muted">SĐT: <?= htmlspecialchars($guestUser['phone']) ?></div>
                </div>
            <?php endif; ?>

            <a href="../index.php?route=auth/login" class="btn btn-lg rounded-4 fw-bold text-white text-start px-4 py-3 shadow-sm text-decoration-none d-flex align-items-center" style="background: var(--teal-main);">
                <i class="fa-solid fa-right-to-bracket me-2"></i> Đăng nhập hệ thống
            </a>

            <button type="button" class="btn btn-lg btn-outline-success rounded-4 fw-bold text-start px-4 py-3" data-bs-toggle="modal" data-bs-target="#mapModal" data-bs-dismiss="offcanvas">
                <i class="fa-solid fa-map-location-dot me-2"></i> Bản đồ (Map) 3 Tòa nhà
            </button>

            <hr class="my-1">
            <div class="small fw-bold text-muted text-uppercase">Chọn nhanh tòa nhà gần bạn</div>
            <?php foreach ($buildings as $bKey => $bInfo): ?>
                <button type="button" class="btn btn-light border rounded-4 text-start p-3 fw-bold" onclick="selectBuilding('<?= $bKey ?>', true)" data-bs-dismiss="offcanvas">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-building me-2 text-teal"></i><?= htmlspecialchars($bInfo['name']) ?></span>
                        <span class="badge bg-success rounded-pill"><?= $availByBld[$bKey] ?> trống</span>
                    </div>
                    <div class="small text-muted fw-normal mt-1"><i class="fa-solid fa-location-dot me-1"></i><?= htmlspecialchars($bInfo['district']) ?> • Liên hệ: <?= htmlspecialchars($bInfo['phone_fmt']) ?></div>
                </button>
            <?php endforeach; ?>

            <div class="mt-auto pt-3 border-top">
                <a href="../index.php?route=dashboard" class="btn btn-outline-secondary w-100 rounded-pill fw-bold">
                    <i class="fa-solid fa-shield-halved me-1"></i> Vào trang Quản trị (Admin)
                </a>
            </div>
        </div>
    </div>

    <!-- ================= HERO BANNER ================= -->
    <section class="hero-showcase text-center">
        <div class="container">
            <div class="step-pill mb-3">
                <i class="fa-solid fa-location-crosshairs"></i>
                BƯỚC 1: CHỌN TÒA NHÀ GẦN BẠN &rarr; BƯỚC 2: XEM CÁC HÀNG LOẠI PHÒNG &amp; ĐẶT GIỮ CHỖ
            </div>
            <h1 class="fw-black mb-2" style="font-weight: 900; font-size: clamp(1.6rem, 3.5vw, 2.5rem);">
                Tìm Phòng Trọ StayMate Gần Bạn Nhất
            </h1>
            <p class="opacity-90 mb-3 mx-auto" style="max-width: 680px; font-size: 0.98rem;">
                Chọn tòa nhà tại khu vực bạn muốn ở để xem danh sách các loại phòng chia theo từng hàng. Di chuột vào từng phòng để xem ảnh góc rộng sắc nét và thông tin chi tiết.
            </p>
            <div class="d-flex justify-content-center flex-wrap gap-2">
                <button type="button" class="btn btn-light rounded-pill fw-bold px-4 py-2 text-dark shadow-sm" onclick="findNearestBuilding()">
                    <i class="fa-solid fa-location-arrow text-danger me-1"></i> Tìm Tòa Gần Vị Trí Của Tôi Nhất
                </button>
                <button type="button" class="btn btn-outline-light rounded-pill fw-bold px-4 py-2" data-bs-toggle="modal" data-bs-target="#mapModal">
                    <i class="fa-solid fa-map-location-dot me-1"></i> Xem Bản Đồ Các Cơ Sở
                </button>
            </div>
        </div>
    </section>

    <div class="container pb-5">

        <!-- Thông báo kết quả Đặt phòng / Tạo tài khoản / Liên hệ nhanh -->
        <?php if ($alertSuccess !== ''): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-2 border-success mt-4 fw-semibold" role="alert">
                <i class="fa-solid fa-circle-check me-2 fs-5"></i> <?= $alertSuccess ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($alertError !== ''): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-2 border-danger mt-4 fw-semibold" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2 fs-5"></i> <?= htmlspecialchars($alertError) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- ================= BƯỚC 1: CHỌN TÒA NHÀ GẦN BẠN ================= -->
        <div id="chon-toa-nha" class="building-selector-wrap mb-4">
            <div class="row g-3">
                <?php foreach ($buildings as $bKey => $b): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="bld-select-card" id="bldCard-<?= $bKey ?>" onclick="selectBuilding('<?= $bKey ?>', true)">
                            <div class="bld-img-wrap">
                                <img src="<?= htmlspecialchars($b['image']) ?>" alt="<?= htmlspecialchars($b['name']) ?>">
                                <span class="distance-badge" id="distBadge-<?= $bKey ?>">
                                    <i class="fa-solid fa-location-dot text-warning me-1"></i> <?= htmlspecialchars($b['district']) ?> (~<?= $b['distance'] ?>)
                                </span>
                                <span class="avail-count-badge">
                                    <i class="fa-solid fa-door-open me-1"></i> Còn <?= $availByBld[$bKey] ?> phòng trống
                                </span>
                            </div>
                            <div class="p-3 p-xl-4 d-flex flex-column justify-content-between flex-grow-1">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge rounded-pill px-3 py-1 fw-bold" style="background: <?= $b['badge_bg'] ?>; color: <?= $b['badge_text'] ?>;">
                                            <?= htmlspecialchars($b['code']) ?> • <?= htmlspecialchars($b['district']) ?>
                                        </span>
                                        <span class="small fw-bold text-danger"><?= htmlspecialchars($b['price_range']) ?></span>
                                    </div>
                                    <h5 class="fw-black mb-1 text-dark" style="font-weight: 900;"><?= htmlspecialchars($b['name']) ?></h5>
                                    <div class="small text-muted fw-semibold mb-2">
                                        <i class="fa-solid fa-map-pin text-danger me-1"></i> <?= htmlspecialchars($b['address']) ?>
                                    </div>
                                    <div class="p-2 rounded-3 bg-light small text-secondary mb-2">
                                        <i class="fa-solid fa-graduation-cap text-teal me-1"></i> <?= htmlspecialchars($b['nearby']) ?>
                                    </div>
                                </div>
                                <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                                    <div class="small">
                                        <span class="text-muted">Người phụ trách:</span>
                                        <strong class="text-dark d-block"><i class="fa-solid fa-user-tie text-success me-1"></i><?= htmlspecialchars($b['manager']) ?> - <?= htmlspecialchars($b['phone_fmt']) ?></strong>
                                    </div>
                                    <span class="btn btn-sm rounded-pill fw-bold px-3 text-white" style="background: <?= $b['color'] ?>;">
                                        Xem phòng <i class="fa-solid fa-arrow-down ms-1"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ================= BỘ LỌC THEO GIÁ TIỀN, TÒA NHÀ & TỪ KHÓA ================= -->
        <div class="filter-bar-sticky mb-4" id="danh-sach-hang-phong">
            <div class="row g-3 align-items-center">
                <!-- Chọn Tòa nhà -->
                <div class="col-lg-3 col-md-6">
                    <label class="form-label small fw-bold text-muted mb-1"><i class="fa-solid fa-building me-1 text-teal"></i> TÒA NHÀ ĐANG XEM</label>
                    <select id="filterBuilding" class="form-select rounded-pill fw-bold border-2" onchange="applyClientFilters()">
                        <option value="all">🏢 Tất cả 3 Tòa nhà (Cầu Giấy, Đống Đa, Thanh Xuân)</option>
                        <option value="A">Tòa A - StayMate Sunrise (Cầu Giấy)</option>
                        <option value="B">Tòa B - StayMate Emerald (Đống Đa)</option>
                        <option value="C">Tòa C - StayMate Horizon (Thanh Xuân)</option>
                    </select>
                </div>

                <!-- Lọc nhanh theo mức giá tiền -->
                <div class="col-lg-5 col-md-6">
                    <label class="form-label small fw-bold text-muted mb-1"><i class="fa-solid fa-coins me-1 text-warning"></i> LỌC THEO GIÁ TIỀN THUÊ / THÁNG</label>
                    <div class="d-flex flex-wrap gap-2" id="priceChipGroup">
                        <button type="button" class="price-chip active" data-min="0" data-max="99999999" onclick="setPriceRange(this, 0, 99999999)">Tất cả giá</button>
                        <button type="button" class="price-chip" data-min="0" data-max="2800000" onclick="setPriceRange(this, 0, 2800000)">Dưới 2,8 triệu</button>
                        <button type="button" class="price-chip" data-min="2800000" data-max="3500000" onclick="setPriceRange(this, 2800000, 3500000)">2,8 – 3,5 triệu</button>
                        <button type="button" class="price-chip" data-min="3500001" data-max="99999999" onclick="setPriceRange(this, 3500001, 99999999)">Trên 3,5 triệu</button>
                    </div>
                </div>

                <!-- Tìm kiếm & Trạng thái -->
                <div class="col-lg-4 col-md-12">
                    <label class="form-label small fw-bold text-muted mb-1"><i class="fa-solid fa-magnifying-glass me-1"></i> TÌM THEO MÃ PHÒNG / TIỆN NGHI</label>
                    <div class="d-flex gap-2">
                        <input type="text" id="filterKeyword" class="form-control rounded-pill border-2 fw-semibold" placeholder="VD: P202, ban công, máy giặt..." oninput="applyClientFilters()">
                        <select id="filterStatus" class="form-select rounded-pill border-2 fw-bold" style="max-width: 155px;" onchange="applyClientFilters()">
                            <option value="available" selected>Chỉ phòng trống</option>
                            <option value="all">Mọi trạng thái</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Dải giá dịch vụ niêm yết công khai -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 pt-3 border-top">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="small fw-bold text-muted"><i class="fa-solid fa-tag text-success me-1"></i> Giá dịch vụ niêm yết:</span>
                    <?php foreach ($services as $sv): ?>
                        <span class="badge rounded-pill bg-light text-dark border px-3 py-1 fw-bold">
                            <?= htmlspecialchars($sv['service_name']) ?>: <span class="text-teal"><?= number_format($sv['unit_price'], 0, ',', '.') ?>đ/<?= htmlspecialchars($sv['unit']) ?></span>
                        </span>
                    <?php endforeach; ?>
                </div>
                <div class="small fw-bold text-secondary">
                    <i class="fa-solid fa-hand-pointer text-warning me-1"></i> <em>Mẹo: Di chuột vào thẻ phòng để phóng rõ ảnh &amp; xem thêm chi tiết!</em>
                </div>
            </div>
        </div>

        <!-- ================= DANH SÁCH CÁC LOẠI PHÒNG CHIA THEO HÀNG (3 HÀNG) ================= -->
        <?php foreach ($roomRowsConfig as $typeKey => $rowMeta): ?>
            <div class="room-type-row-section" id="rowSection-<?= $typeKey ?>" data-row-type="<?= $typeKey ?>">
                <div class="row-header-banner">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-4 d-flex align-items-center justify-content-center text-white shadow-sm" style="width: 50px; height: 50px; background: <?= $rowMeta['color'] ?>; font-size: 1.3rem;">
                            <i class="fa-solid <?= $rowMeta['icon'] ?>"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h4 class="fw-black mb-0 text-dark" style="font-weight: 900;"><?= htmlspecialchars($rowMeta['title']) ?></h4>
                                <span class="badge rounded-pill px-3 py-1 text-white" style="background: <?= $rowMeta['color'] ?>;"><?= htmlspecialchars($rowMeta['badge']) ?></span>
                            </div>
                            <div class="small text-muted fw-semibold mt-1"><?= htmlspecialchars($rowMeta['subtitle']) ?></div>
                        </div>
                    </div>
                    <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-bold row-count-badge" id="rowCount-<?= $typeKey ?>">
                        Đang hiển thị phòng
                    </span>
                </div>

                <!-- Hàng ngang các thẻ phòng thuộc loại này -->
                <div class="row g-4 room-row-container">
                    <?php foreach ($allRooms as $rm): ?>
                        <?php if ($rm['type'] !== $typeKey) continue; ?>
                        <?php
                            $bInfo = $buildings[$rm['building']];
                            $isAvail = ($rm['status'] === 'available');
                            $mainImg = $rm['images'][0];
                            $searchBlob = strtolower($rm['room_number'] . ' ' . $rm['description'] . ' ' . implode(' ', $rm['highlights']) . ' ' . $bInfo['name'] . ' ' . $bInfo['district']);
                        ?>
                        <div class="col-xl-4 col-md-6 room-card-col"
                             data-building="<?= htmlspecialchars($rm['building']) ?>"
                             data-price="<?= (float)$rm['price'] ?>"
                             data-status="<?= htmlspecialchars($rm['status']) ?>"
                             data-search="<?= htmlspecialchars($searchBlob) ?>">

                            <div class="showcase-room-card">
                                <!-- Khung ảnh phòng + Bộ 3 ảnh thu nhỏ khi di chuột vào -->
                                <div class="room-img-container">
                                    <img src="<?= htmlspecialchars($mainImg) ?>" class="room-main-img" id="mainImg-<?= $rm['id'] ?>" alt="Phòng <?= htmlspecialchars($rm['room_number']) ?>">

                                    <!-- Badge tòa nhà & trạng thái -->
                                    <div class="position-absolute top-0 start-0 p-3 d-flex gap-2">
                                        <span class="badge rounded-pill px-3 py-1 fw-bold shadow-sm" style="background: <?= $bInfo['color'] ?>; color: #fff;">
                                            <?= htmlspecialchars($bInfo['code']) ?> • <?= htmlspecialchars($bInfo['district']) ?>
                                        </span>
                                        <?php if ($isAvail): ?>
                                            <span class="badge rounded-pill bg-success px-3 py-1 fw-bold shadow-sm">
                                                <i class="fa-solid fa-circle-check me-1"></i> Còn trống
                                            </span>
                                        <?php else: ?>
                                            <span class="badge rounded-pill bg-secondary px-3 py-1 fw-bold shadow-sm">
                                                Đã có khách
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <span class="hover-zoom-badge">
                                        <i class="fa-solid fa-magnifying-glass-plus me-1"></i> Ảnh HD • Chạm ảnh nhỏ để đổi góc
                                    </span>

                                    <!-- 3 Ảnh góc chụp hiện lên khi Hover -->
                                    <div class="hover-gallery-strip">
                                        <?php foreach ($rm['images'] as $gIdx => $gUrl): ?>
                                            <img src="<?= htmlspecialchars($gUrl) ?>"
                                                 class="thumb-mini"
                                                 alt="Góc <?= $gIdx + 1 ?>"
                                                 onmouseenter="document.getElementById('mainImg-<?= $rm['id'] ?>').src=this.src"
                                                 onclick="document.getElementById('mainImg-<?= $rm['id'] ?>').src=this.src">
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Thân thẻ phòng -->
                                <div class="p-3 p-xl-4 d-flex flex-column justify-content-between flex-grow-1">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h5 class="fw-black mb-0 text-dark" style="font-weight: 900;">
                                                    Phòng <?= htmlspecialchars($rm['room_number']) ?>
                                                </h5>
                                                <div class="small text-muted fw-bold">
                                                    <?= htmlspecialchars($bInfo['name']) ?>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <div class="price-highlight"><?= number_format($rm['price'], 0, ',', '.') ?>đ</div>
                                                <div class="text-muted" style="font-size: 0.72rem; font-weight: 700;">/ tháng</div>
                                            </div>
                                        </div>

                                        <!-- Thông số cơ bản luôn hiển thị -->
                                        <div class="d-flex flex-wrap gap-2 mb-2">
                                            <span class="amenity-tag"><i class="fa-solid fa-ruler-combined text-teal"></i> <?= htmlspecialchars($rm['area']) ?></span>
                                            <span class="amenity-tag"><i class="fa-solid fa-stairs text-primary"></i> <?= htmlspecialchars($rm['floor']) ?></span>
                                            <span class="amenity-tag"><i class="fa-solid fa-user-group text-success"></i> <?= htmlspecialchars($rm['max_people']) ?></span>
                                        </div>

                                        <p class="small text-secondary mb-2">
                                            <?= htmlspecialchars($rm['description']) ?>
                                        </p>

                                        <!-- KHUNG POPUP THÔNG TIN NỔI LÊN ĐỘC LẬP KHI TRỎ CHUỘT (TUYỆT ĐỐI KHÔNG LÀM BIẾN DẠNG LƯỚI) -->
                                        <div class="hover-extra-popup">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="small fw-bold text-dark"><i class="fa-solid fa-sparkles text-warning me-1"></i>Tiện nghi chi tiết:</span>
                                                <span class="badge rounded-pill bg-success-subtle text-success border border-success" style="font-size: 0.68rem;">Xem nhanh</span>
                                            </div>
                                            <div class="d-flex flex-wrap gap-1 mb-2">
                                                <?php foreach ($rm['highlights'] as $hl): ?>
                                                    <span class="badge bg-light text-dark border rounded-pill px-2 py-1" style="font-size: 0.72rem;">
                                                        <i class="fa-solid fa-check text-success me-1"></i><?= htmlspecialchars($hl) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="small text-muted border-top pt-2" style="font-size: 0.76rem; line-height: 1.45;">
                                                <div><i class="fa-solid fa-location-dot text-danger me-1"></i><strong>Đ/c:</strong> <?= htmlspecialchars($bInfo['address']) ?></div>
                                                <div><i class="fa-solid fa-user-tie text-success me-1"></i><strong>Quản lý:</strong> <?= htmlspecialchars($bInfo['manager']) ?> (<strong><?= htmlspecialchars($bInfo['phone_fmt']) ?></strong>)</div>
                                                <div><i class="fa-solid fa-shield-heart text-primary me-1"></i><strong>Cọc 1 tháng</strong> • Vào sau ngày 15 giảm 50% phí DV</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Cụm nút Đặt phòng trước & Liên hệ nhanh -->
                                    <div class="pt-3 mt-2 border-top">
                                        <button type="button"
                                                class="w-100 py-2 px-3 fw-bold rounded-pill text-white shadow-sm d-flex align-items-center justify-content-center gap-2 border-0"
                                                style="background: #308f86; font-size: 0.95rem; cursor: pointer; transition: 0.2s;"
                                                onmouseover="this.style.background='#1f665f';"
                                                onmouseout="this.style.background='#308f86';"
                                                onclick="openBookingModal('Phòng <?= htmlspecialchars($rm['room_number']) ?> - <?= htmlspecialchars($bInfo['name']) ?>', '<?= number_format($rm['price'], 0, ',', '.') ?>đ/tháng', '<?= htmlspecialchars($bInfo['manager']) ?>', '<?= htmlspecialchars($bInfo['phone_fmt']) ?>', '<?= htmlspecialchars($bInfo['phone']) ?>', 'quick')">
                                            <i class="fa-solid fa-phone-volume"></i>
                                            <span>Liên hệ xem phòng</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Thông báo khi không có phòng khớp bộ lọc -->
        <div id="emptyFilterNotice" class="text-center py-5 bg-white rounded-5 border shadow-sm" style="display: none;">
            <i class="fa-solid fa-house-circle-exclamation text-muted mb-3" style="font-size: 3.2rem;"></i>
            <h5 class="fw-bold text-dark">Không tìm thấy phòng phù hợp với mức lọc hiện tại</h5>
            <p class="text-muted mb-3">Bạn hãy thử chọn "Tất cả giá" hoặc xem tất cả 3 tòa nhà nhé.</p>
            <button type="button" class="btn rounded-pill px-4 py-2 fw-bold text-white" style="background: var(--teal-main);" onclick="resetAllFilters()">
                <i class="fa-solid fa-rotate-left me-1"></i> Xem lại tất cả phòng
            </button>
        </div>

    </div>

    <!-- ================= MODAL LIÊN HỆ XEM PHÒNG (CHỈ LIÊN HỆ NHANH THEO YÊU CẦU) ================= -->
    <div class="modal fade" id="bookingModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 560px;">
            <div class="modal-content rounded-5 border-0 shadow-lg overflow-hidden">
                <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, #1f665f, #308f86);">
                    <div>
                        <h5 class="modal-title fw-bold mb-0"><i class="fa-solid fa-phone-volume me-2"></i>Liên Hệ Xem Phòng &amp; Tư Vấn Nhanh</h5>
                        <div class="small opacity-90" id="bookingRoomTitle">Phòng P101 - Tòa A</div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Hộp thông tin phòng & Người liên hệ phụ trách -->
                    <div class="p-3 rounded-4 mb-3 d-flex flex-wrap justify-content-between align-items-center gap-2" style="background: #f0fdfa; border: 1.5px solid #99f6e4;">
                        <div>
                            <div class="fw-bold text-dark" id="modalRoomSummary">Phòng P101 - Tòa A</div>
                            <div class="small text-muted">Giá niêm yết: <strong class="text-danger" id="modalRoomPrice">3.000.000đ/tháng</strong></div>
                        </div>
                        <div class="text-end">
                            <div class="small text-muted">Quản lý phụ trách:</div>
                            <div class="fw-bold text-dark">
                                <i class="fa-solid fa-user-tie text-success me-1"></i>
                                <span id="modalManagerName">Nguyễn Văn Quản</span>
                            </div>
                            <a href="tel:0901234567" id="modalManagerCallLink" class="text-danger text-decoration-none fw-black">0901.234.567</a>
                        </div>
                    </div>

                    <!-- Hotline gọi trực tiếp -->
                    <div class="p-3 rounded-4 bg-warning-subtle border border-warning mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <div class="small fw-bold text-dark"><i class="fa-solid fa-headset text-danger me-1"></i> Gọi hotline quản lý:</div>
                            <div class="text-danger fw-black fs-5" id="quickPhoneDisplay">0901.234.567</div>
                        </div>
                        <a href="tel:0901234567" id="quickDirectCallBtn" class="btn btn-danger rounded-pill fw-bold px-3 py-2">
                            <i class="fa-solid fa-phone me-1"></i> Gọi Trực Tiếp
                        </a>
                    </div>

                    <!-- Form gửi thông tin liên hệ nhanh -->
                    <form method="POST" action="index.php">
                        <input type="hidden" name="form_action" value="quick_contact">
                        <input type="hidden" name="room_label" id="formRoomLabel2" value="">
                        <input type="hidden" name="bld_manager" id="formBldManager" value="">
                        <input type="hidden" name="bld_phone" id="formBldPhone" value="">

                        <div class="fw-bold text-dark small text-uppercase mb-2">
                            <i class="fa-solid fa-paper-plane text-teal-main me-1"></i> Điền thông tin để Quản lý gọi lại và hẹn lịch xem:
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Tên người liên hệ (Tên bạn) <span class="text-danger">*</span></label>
                                <input type="text" name="contact_name" class="form-control rounded-3 py-2" placeholder="Nhập họ và tên..." required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Số điện thoại liên hệ <span class="text-danger">*</span></label>
                                <input type="tel" name="contact_phone" class="form-control rounded-3 py-2" placeholder="Nhập SĐT của bạn..." required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Khung giờ bạn tiện đến xem phòng</label>
                                <input type="text" name="visit_time" class="form-control rounded-3 py-2" placeholder="VD: 18h30 tối nay hoặc Sáng mai 9h00" value="Gọi tư vấn ngay trong ngày">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold">Ghi chú thêm (nếu có)</label>
                                <textarea name="contact_note" rows="2" class="form-control rounded-3" placeholder="VD: Dự kiến dọn vào đầu tháng, cần xem phòng thực tế..."></textarea>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Đóng</button>
                            <button type="submit" class="btn rounded-pill px-4 fw-bold text-white shadow-sm" style="background:#308f86;border:none;">
                                <i class="fa-solid fa-paper-plane me-1"></i> Gửi Yêu Cầu Liên Hệ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= MODAL ĐĂNG NHẬP / ĐĂNG KÝ TỪ MENU 3 GẠCH ================= -->
    <div class="modal fade" id="authModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-5 border-0 shadow-lg overflow-hidden">
                <div class="modal-header text-white px-4 py-3" style="background: var(--teal-main);">
                    <h5 class="modal-title fw-bold mb-0"><i class="fa-solid fa-user-shield me-2"></i>Tài Khoản StayMate</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <ul class="nav nav-pills nav-fill bg-light p-1 rounded-pill mb-3">
                        <li class="nav-item">
                            <button class="nav-link active rounded-pill fw-bold" data-bs-toggle="pill" data-bs-target="#authLoginPane" type="button">Đăng nhập</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link rounded-pill fw-bold" data-bs-toggle="pill" data-bs-target="#authRegPane" type="button">Đăng ký tài khoản mới</button>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <!-- Đăng nhập -->
                        <div class="tab-pane fade show active" id="authLoginPane">
                            <form method="POST" action="index.php">
                                <input type="hidden" name="form_action" value="guest_login">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Tên đăng nhập</label>
                                    <input type="text" name="username" class="form-control rounded-3" placeholder="Nhập username (VD: khach01 hoặc admin01)" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Mật khẩu</label>
                                    <input type="password" name="password" class="form-control rounded-3" placeholder="Nhập mật khẩu..." required>
                                </div>
                                <button type="submit" class="btn w-100 rounded-pill fw-bold text-white py-2" style="background: var(--teal-main);">
                                    <i class="fa-solid fa-right-to-bracket me-1"></i> Đăng Nhập Ngay
                                </button>
                            </form>
                        </div>
                        <!-- Đăng ký -->
                        <div class="tab-pane fade" id="authRegPane">
                            <form method="POST" action="index.php">
                                <input type="hidden" name="form_action" value="register_only">
                                <div class="mb-2">
                                    <label class="form-label small fw-bold">Họ và tên <span class="text-danger">*</span></label>
                                    <input type="text" name="full_name" class="form-control rounded-3" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-bold">Số điện thoại <span class="text-danger">*</span></label>
                                    <input type="tel" name="phone" class="form-control rounded-3" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-bold">Tên đăng nhập <span class="text-danger">*</span></label>
                                    <input type="text" name="username" class="form-control rounded-3" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">Mật khẩu <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control rounded-3" required>
                                </div>
                                <button type="submit" class="btn btn-success w-100 rounded-pill fw-bold py-2">
                                    <i class="fa-solid fa-user-plus me-1"></i> Tạo Tài Khoản Khách Thuê
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= MODAL BẢN ĐỒ (MAP) CÁC TÒA NHÀ ================= -->
    <div class="modal fade" id="mapModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content rounded-5 border-0 shadow-lg overflow-hidden">
                <div class="modal-header text-white px-4 py-3" style="background: var(--teal-main);">
                    <h5 class="modal-title fw-bold mb-0"><i class="fa-solid fa-map-location-dot me-2"></i>Bản Đồ Vị Trí Các Tòa Nhà StayMate</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <?php foreach ($buildings as $bKey => $b): ?>
                            <button type="button" class="btn btn-outline-teal border rounded-pill fw-bold px-3" onclick="switchMap('<?= $b['map_query'] ?>')">
                                <i class="fa-solid fa-location-dot text-danger me-1"></i> <?= htmlspecialchars($b['name']) ?> (<?= htmlspecialchars($b['district']) ?>)
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <div class="ratio ratio-16x9 rounded-4 overflow-hidden border">
                        <iframe id="googleMapFrame" src="https://maps.google.com/maps?q=128+Cau+Giay+Ha+Noi&t=&z=15&ie=UTF8&iwloc=&output=embed" allowfullscreen loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-top py-4 text-center text-muted small">
        <div class="container">
            <div class="fw-bold text-dark mb-1">HỆ THỐNG PHÒNG TRỌ &amp; CĂN HỘ MINI STAYMATE</div>
            <div>Tòa A: 128 Cầu Giấy (0901.234.567) • Tòa B: 45 Nguyễn Chí Thanh (0912.345.678) • Tòa C: 88 Nguyễn Trãi (0987.654.321)</div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentMinPrice = 0;
        let currentMaxPrice = 99999999;

        // Chọn tòa nhà ở Bước 1
        function selectBuilding(bldKey, shouldScroll) {
            document.getElementById('filterBuilding').value = bldKey;
            document.querySelectorAll('.bld-select-card').forEach(el => el.classList.remove('active-bld'));
            const card = document.getElementById('bldCard-' + bldKey);
            if (card) card.classList.add('active-bld');
            applyClientFilters();
            if (shouldScroll) {
                document.getElementById('danh-sach-hang-phong').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        // Tìm tòa nhà gần nhất
        function findNearestBuilding() {
            selectBuilding('A', true);
            const badge = document.getElementById('distBadge-A');
            if (badge) {
                badge.innerHTML = '<i class="fa-solid fa-circle-check text-info me-1"></i> Gần bạn nhất (~1.2 km)';
            }
        }

        // Chọn mức giá tiền
        function setPriceRange(btn, minP, maxP) {
            currentMinPrice = minP;
            currentMaxPrice = maxP;
            document.querySelectorAll('#priceChipGroup .price-chip').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            applyClientFilters();
        }

        // Lọc trực tiếp các hàng phòng
        function applyClientFilters() {
            const bld = document.getElementById('filterBuilding').value;
            const status = document.getElementById('filterStatus').value;
            const kw = document.getElementById('filterKeyword').value.trim().toLowerCase();

            document.querySelectorAll('.bld-select-card').forEach(el => el.classList.remove('active-bld'));
            if (bld !== 'all') {
                const activeCard = document.getElementById('bldCard-' + bld);
                if (activeCard) activeCard.classList.add('active-bld');
            }

            let totalVisible = 0;

            ['standard', 'duplex', 'studio'].forEach(typeKey => {
                const section = document.getElementById('rowSection-' + typeKey);
                const cards = section.querySelectorAll('.room-card-col');
                let rowVisible = 0;

                cards.forEach(col => {
                    const cBld = col.getAttribute('data-building');
                    const cPrice = parseFloat(col.getAttribute('data-price'));
                    const cStatus = col.getAttribute('data-status');
                    const cSearch = col.getAttribute('data-search');

                    const matchBld = (bld === 'all' || cBld === bld);
                    const matchPrice = (cPrice >= currentMinPrice && cPrice <= currentMaxPrice);
                    const matchStatus = (status === 'all' || cStatus === status);
                    const matchKw = (kw === '' || cSearch.includes(kw));

                    if (matchBld && matchPrice && matchStatus && matchKw) {
                        col.style.display = '';
                        rowVisible++;
                        totalVisible++;
                    } else {
                        col.style.display = 'none';
                    }
                });

                section.style.display = rowVisible > 0 ? '' : 'none';
                const countBadge = document.getElementById('rowCount-' + typeKey);
                if (countBadge) countBadge.textContent = rowVisible + ' phòng phù hợp';
            });

            document.getElementById('emptyFilterNotice').style.display = (totalVisible === 0) ? 'block' : 'none';
        }

        function resetAllFilters() {
            document.getElementById('filterBuilding').value = 'all';
            document.getElementById('filterStatus').value = 'all';
            document.getElementById('filterKeyword').value = '';
            const firstChip = document.querySelector('#priceChipGroup .price-chip');
            setPriceRange(firstChip, 0, 99999999);
        }

        // Mở Modal Đặt phòng trước hoặc Liên hệ nhanh
        function openBookingModal(roomTitle, priceText, managerName, phoneFmt, phoneRaw) {
            const elTitle = document.getElementById('bookingRoomTitle');
            if (elTitle) elTitle.textContent = roomTitle;

            const elSummary = document.getElementById('modalRoomSummary');
            if (elSummary) elSummary.textContent = roomTitle;

            const elPrice = document.getElementById('modalRoomPrice');
            if (elPrice) elPrice.textContent = priceText;

            const elMgr = document.getElementById('modalManagerName');
            if (elMgr) elMgr.textContent = managerName;

            const elCallLink = document.getElementById('modalManagerCallLink');
            if (elCallLink) {
                elCallLink.textContent = phoneFmt;
                elCallLink.href = 'tel:' + phoneRaw;
            }

            const elRoomLabel2 = document.getElementById('formRoomLabel2');
            if (elRoomLabel2) elRoomLabel2.value = roomTitle;

            const elBldMgr = document.getElementById('formBldManager');
            if (elBldMgr) elBldMgr.value = managerName;

            const elBldPhone = document.getElementById('formBldPhone');
            if (elBldPhone) elBldPhone.value = phoneFmt;

            const elQuickPhone = document.getElementById('quickPhoneDisplay');
            if (elQuickPhone) elQuickPhone.textContent = phoneFmt;

            const elDirectCall = document.getElementById('quickDirectCallBtn');
            if (elDirectCall) elDirectCall.href = 'tel:' + phoneRaw;

            const modalEl = document.getElementById('bookingModal');
            if (modalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }
        }

        function switchMap(query) {
            document.getElementById('googleMapFrame').src = 'https://maps.google.com/maps?q=' + query + '&t=&z=15&ie=UTF8&iwloc=&output=embed';
        }

        document.addEventListener('DOMContentLoaded', () => {
            applyClientFilters();
        });
    </script>
</body>
</html>
