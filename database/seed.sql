-- ==============================================================================
-- STAYMATE - DỮ LIỆU KHỞI TẠO CƠ SỞ DỮ LIỆU MẪU (SEED SQL)
-- Chuẩn hóa: 3 Tòa nhà (Tòa A, B, C), mỗi tòa đúng 20 phòng = 60 phòng trọ
-- Tên khách thuê, quản lý là người Việt Nam hiện đại, tự nhiên, đầy đủ
-- Khắc phục triệt để lỗi #1701 Foreign Key & #1054 Unknown Column
-- ==============================================================================

USE `quan_ly_nha_tro`;

-- 1. TẮT TẠM THỜI KIỂM TRA KHÓA NGOẠI ĐỂ DỌN DẸP DỮ LIỆU CŨ
SET FOREIGN_KEY_CHECKS = 0;

-- Đảm bảo bảng buildings tồn tại
CREATE TABLE IF NOT EXISTS `buildings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `address` VARCHAR(255) NOT NULL,
  `manager_id` INT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Đảm bảo bảng rooms có cột building_id và cột status hỗ trợ 'maintenance'
SET @dbname = DATABASE();
SET @tablename = 'rooms';
SET @columnname = 'building_id';
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  'SELECT 1',
  'ALTER TABLE `rooms` ADD COLUMN `building_id` INT NULL AFTER `id`'
));
PREPARE alterRooms FROM @preparedStatement;
EXECUTE alterRooms;
DEALLOCATE PREPARE alterRooms;

-- Nâng cấp kiểu dữ liệu cột status của rooms thành VARCHAR(20) để không bị lỗi ENUM
ALTER TABLE `rooms` MODIFY COLUMN `status` VARCHAR(20) DEFAULT 'available';

-- Đảm bảo bảng services có cột is_active (nếu chưa có)
SET @stmtServices = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'services' AND COLUMN_NAME = 'is_active') > 0,
  'SELECT 1',
  'ALTER TABLE `services` ADD COLUMN `is_active` TINYINT(1) DEFAULT 1 AFTER `unit`'
));
PREPARE alterServices FROM @stmtServices;
EXECUTE alterServices;
DEALLOCATE PREPARE alterServices;

-- Đảm bảo bảng contracts có đủ các cột nâng cao
SET @stmtContracts = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'contracts' AND COLUMN_NAME = 'contract_code') > 0,
  'SELECT 1',
  'ALTER TABLE `contracts` ADD COLUMN `contract_code` VARCHAR(30) NULL AFTER `id`, ADD COLUMN `contract_type` VARCHAR(30) DEFAULT \'official_lease\' AFTER `contract_code`, ADD COLUMN `monthly_rent` DECIMAL(10,2) DEFAULT 0 AFTER `deposit_amount`, ADD COLUMN `deposit_paid_percent` INT DEFAULT 100 AFTER `monthly_rent`, ADD COLUMN `deposit_status` VARCHAR(20) DEFAULT \'held\' AFTER `deposit_paid_percent`, ADD COLUMN `signature_type` VARCHAR(20) DEFAULT \'paper\' AFTER `deposit_status`'
));
PREPARE alterContracts FROM @stmtContracts;
EXECUTE alterContracts;
DEALLOCATE PREPARE alterContracts;

ALTER TABLE `contracts` MODIFY COLUMN `status` VARCHAR(20) DEFAULT 'active';

-- Sử dụng DELETE FROM thay vì TRUNCATE để không bị lỗi MySQL #1701 Foreign Key
DELETE FROM `invoice_details`;
DELETE FROM `invoices`;
DELETE FROM `utilities_readings`;
DELETE FROM `contracts`;
DELETE FROM `rooms`;
DELETE FROM `services`;
DELETE FROM `buildings`;
DELETE FROM `users`;

-- Đặt lại chỉ số AUTO_INCREMENT về 1 cho tất cả các bảng
ALTER TABLE `invoice_details` AUTO_INCREMENT = 1;
ALTER TABLE `invoices` AUTO_INCREMENT = 1;
ALTER TABLE `utilities_readings` AUTO_INCREMENT = 1;
ALTER TABLE `contracts` AUTO_INCREMENT = 1;
ALTER TABLE `rooms` AUTO_INCREMENT = 1;
ALTER TABLE `services` AUTO_INCREMENT = 1;
ALTER TABLE `buildings` AUTO_INCREMENT = 1;
ALTER TABLE `users` AUTO_INCREMENT = 1;

-- Khởi tạo bảng tenant_requests (yêu cầu sửa thông tin của khách thuê)
DROP TABLE IF EXISTS `tenant_requests`;
CREATE TABLE `tenant_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL,
  `request_type` VARCHAR(50) NOT NULL DEFAULT 'profile_update',
  `field_target` VARCHAR(100) NULL,
  `old_value` TEXT NULL,
  `new_value` TEXT NOT NULL,
  `reason` TEXT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `manager_note` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (`tenant_id`),
  INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- BẬT LẠI KIỂM TRA KHÓA NGOẠI
SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================================
-- 1. DANH SÁCH TÀI KHOẢN NGƯỜI DÙNG (USERS)
-- 1 Admin, 3 Quản lý tòa nhà, và 52 Khách thuê tên người Việt Nam thực tế
-- Mật khẩu mặc định: 'hash' (hệ thống AuthController chấp nhận 'hash', '123456', 'admin01')
-- ==============================================================================
INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `phone`, `email`, `role`) VALUES
-- Ban quản trị & Quản lý tòa nhà
(1, 'admin', 'hash', 'Nguyễn Tuấn Dũng', '0901234567', 'admin@staymate.vn', 'admin'),
(2, 'quanly_a', 'hash', 'Nguyễn Văn Quân', '0912345678', 'quan.nguyen@staymate.vn', 'manager'),
(3, 'quanly_b', 'hash', 'Trần Thị Mai Lan', '0923456789', 'lan.tran@staymate.vn', 'manager'),
(4, 'quanly_c', 'hash', 'Lê Hoàng Nam', '0934567890', 'nam.le@staymate.vn', 'manager'),

-- Khách thuê Tòa A (18 khách thuê phòng A101 -> A404)
(5,  'kh_a101', 'hash', 'Nguyễn Tiến Đạt',    '0901001101', 'dat.nguyen@gmail.com',    'tenant'),
(6,  'kh_a102', 'hash', 'Lê Thị Thu Hương',   '0901001102', 'huong.le@gmail.com',     'tenant'),
(7,  'kh_a103', 'hash', 'Trần Hoàng Long',    '0901001103', 'long.tran@gmail.com',    'tenant'),
(8,  'kh_a104', 'hash', 'Phạm Thu Trang',     '0901001104', 'trang.pham@gmail.com',   'tenant'),
(9,  'kh_a105', 'hash', 'Vũ Minh Tuấn',       '0901001105', 'tuan.vu@gmail.com',      'tenant'),
(10, 'kh_a201', 'hash', 'Đặng Kim Thủy',      '0901001201', 'thuy.dang@gmail.com',    'tenant'),
(11, 'kh_a202', 'hash', 'Bùi Xuân Bách',      '0901001202', 'bach.bui@gmail.com',     'tenant'),
(12, 'kh_a203', 'hash', 'Hồ Quang Hiếu',      '0901001203', 'hieu.ho@gmail.com',      'tenant'),
(13, 'kh_a204', 'hash', 'Ngô Phương Lan',     '0901001204', 'lan.ngo@gmail.com',      'tenant'),
(14, 'kh_a301', 'hash', 'Đinh Trọng Hưng',    '0901001301', 'hung.dinh@gmail.com',    'tenant'),
(15, 'kh_a302', 'hash', 'Hoàng Thanh Tùng',   '0901001302', 'tung.hoang@gmail.com',   'tenant'),
(16, 'kh_a303', 'hash', 'Đỗ Thùy Linh',       '0901001303', 'linh.do@gmail.com',      'tenant'),
(17, 'kh_a304', 'hash', 'Nguyễn Hải Yến',     '0901001304', 'yen.nguyen@gmail.com',   'tenant'),
(18, 'kh_a305', 'hash', 'Phạm Quốc Huy',      '0901001305', 'huy.pham@gmail.com',     'tenant'),
(19, 'kh_a401', 'hash', 'Trịnh Minh Đức',     '0901001401', 'duc.trinh@gmail.com',    'tenant'),
(20, 'kh_a402', 'hash', 'Nguyễn Bích Ngọc',   '0901001402', 'ngoc.nguyen@gmail.com',  'tenant'),
(21, 'kh_a403', 'hash', 'Vũ Đình Trọng',      '0901001403', 'trong.vu@gmail.com',     'tenant'),
(22, 'kh_a404', 'hash', 'Trần Văn Khoa',      '0901001404', 'khoa.tran@gmail.com',    'tenant'),

-- Khách thuê Tòa B (17 khách thuê phòng B101 -> B404)
(23, 'kh_b101', 'hash', 'Mai Thùy Dung',      '0902002101', 'dung.mai@gmail.com',     'tenant'),
(24, 'kh_b102', 'hash', 'Lê Khánh An',        '0902002102', 'an.le@gmail.com',        'tenant'),
(25, 'kh_b103', 'hash', 'Nguyễn Nhật Minh',   '0902002103', 'minh.nhat@gmail.com',    'tenant'),
(26, 'kh_b104', 'hash', 'Phan Bảo Châu',      '0902002104', 'chau.phan@gmail.com',    'tenant'),
(27, 'kh_b201', 'hash', 'Đỗ Gia Hưng',        '0902002201', 'hung.do@gmail.com',      'tenant'),
(28, 'kh_b202', 'hash', 'Bùi Mai Phương',     '0902002202', 'phuong.bui@gmail.com',   'tenant'),
(29, 'kh_b203', 'hash', 'Trương Tuấn Kiệt',   '0902002203', 'kiet.truong@gmail.com',  'tenant'),
(30, 'kh_b204', 'hash', 'Hoàng Thảo Vy',      '0902002204', 'vy.hoang@gmail.com',     'tenant'),
(31, 'kh_b205', 'hash', 'Vũ Thành Đạt',       '0902002205', 'dat.vu@gmail.com',       'tenant'),
(32, 'kh_b301', 'hash', 'Phạm Thanh Hằng',    '0902002301', 'hang.pham@gmail.com',    'tenant'),
(33, 'kh_b302', 'hash', 'Nguyễn Đức Phúc',    '0902002302', 'phuc.nguyen@gmail.com',  'tenant'),
(34, 'kh_b303', 'hash', 'Đặng Quỳnh Anh',     '0902002303', 'anh.dang@gmail.com',     'tenant'),
(35, 'kh_b304', 'hash', 'Lê Minh Khang',      '0902002304', 'khang.le@gmail.com',     'tenant'),
(36, 'kh_b401', 'hash', 'Trần Thảo Linh',     '0902002401', 'linh.tran@gmail.com',    'tenant'),
(37, 'kh_b402', 'hash', 'Đào Quang Huy',      '0902002402', 'huy.dao@gmail.com',      'tenant'),
(38, 'kh_b403', 'hash', 'Nguyễn Lan Hương',   '0902002403', 'huong.lan@gmail.com',    'tenant'),
(39, 'kh_b404', 'hash', 'Phan Quốc Toàn',     '0902002404', 'toan.phan@gmail.com',    'tenant'),

-- Khách thuê Tòa C (17 khách thuê phòng C101 -> C404)
(40, 'kh_c101', 'hash', 'Hoàng Thị Ngọc Bích','0903003101', 'bich.hoang@gmail.com',   'tenant'),
(41, 'kh_c102', 'hash', 'Dương Minh Trí',     '0903003102', 'tri.duong@gmail.com',    'tenant'),
(42, 'kh_c103', 'hash', 'Ngô Ánh Dương',      '0903003103', 'duong.ngo@gmail.com',    'tenant'),
(43, 'kh_c104', 'hash', 'Vũ Diệu Linh',       '0903003104', 'linh.vu@gmail.com',      'tenant'),
(44, 'kh_c105', 'hash', 'Trần Tuấn Anh',      '0903003105', 'anh.tran@gmail.com',     'tenant'),
(45, 'kh_c201', 'hash', 'Nguyễn Phương Thảo', '0903003201', 'thao.nguyen@gmail.com',  'tenant'),
(46, 'kh_c202', 'hash', 'Bùi Đức Mạnh',       '0903003202', 'manh.bui@gmail.com',     'tenant'),
(47, 'kh_c203', 'hash', 'Lê Hoài Nam',        '0903003203', 'nam.hoai@gmail.com',     'tenant'),
(48, 'kh_c204', 'hash', 'Đỗ Bảo Ngọc',        '0903003204', 'ngoc.bao@gmail.com',     'tenant'),
(49, 'kh_c301', 'hash', 'Trịnh Văn Quyết',    '0903003301', 'quyet.trinh@gmail.com',  'tenant'),
(50, 'kh_c302', 'hash', 'Phạm Hà My',         '0903003302', 'my.pham@gmail.com',      'tenant'),
(51, 'kh_c303', 'hash', 'Nguyễn Trọng Tín',   '0903003303', 'tin.nguyen@gmail.com',   'tenant'),
(52, 'kh_c304', 'hash', 'Hoàng Thúy Hằng',    '0903003304', 'hang.thuy@gmail.com',    'tenant'),
(53, 'kh_c401', 'hash', 'Đinh Hoàng Việt',    '0903003401', 'viet.dinh@gmail.com',    'tenant'),
(54, 'kh_c402', 'hash', 'Chu Minh Châu',      '0903003402', 'chau.chu@gmail.com',     'tenant'),
(55, 'kh_c403', 'hash', 'Vũ Hải Đăng',        '0903003403', 'dang.vu@gmail.com',      'tenant'),
(56, 'kh_c404', 'hash', 'Nguyễn Quỳnh Như',    '0903003404', 'nhu.nguyen@gmail.com',   'tenant');

-- ==============================================================================
-- 2. DANH MỤC 3 TÒA NHÀ (BUILDINGS)
-- ==============================================================================
INSERT INTO `buildings` (`id`, `name`, `address`, `manager_id`) VALUES
(1, 'Tòa A - StayMate Sunrise', 'Số 128 Cầu Giấy, Cầu Giấy, Hà Nội', 2),
(2, 'Tòa B - StayMate Emerald', 'Số 45 Nguyễn Chí Thanh, Ba Đình, Hà Nội', 3),
(3, 'Tòa C - StayMate Horizon', 'Số 88 Nguyễn Trãi, Thanh Xuân, Hà Nội', 4);

-- ==============================================================================
-- 3. BẢNG ĐƠN GIÁ DỊCH VỤ NIÊM YẾT (SERVICES)
-- Đơn vị tính chuẩn theo thiết kế cơ sở dữ liệu của nhóm
-- ==============================================================================
INSERT INTO `services` (`id`, `service_name`, `unit_price`, `unit`, `is_active`) VALUES
(1, 'Tiền điện sinh hoạt', 3500, 'kWh', 1),
(2, 'Tiền nước sinh hoạt', 20000, 'Khối', 1),
(3, 'Internet / Wifi tốc độ cao', 50000, 'Phòng', 1),
(4, 'Rác sinh hoạt & Môi trường', 30000, 'Phòng', 1),
(5, 'Vệ sinh hành lang & Khu chung', 20000, 'Người', 1),
(6, 'Phí dịch vụ thang máy', 30000, 'Người', 1),
(7, 'Gửi xe máy tầng hầm', 70000, 'Xe', 1),
(8, 'Giặt ủi sấy chung', 50000, 'Người', 1);

-- ==============================================================================
-- 4. DANH MỤC PHÒNG TRỌ (ROOMS)
-- Đúng 3 Tòa x 20 Phòng = 60 Phòng (ID 1 -> 60)
-- 52 phòng 'rented' (đang có khách), 5 phòng 'available' (trống), 3 phòng 'maintenance'
-- ==============================================================================
INSERT INTO `rooms` (`id`, `building_id`, `room_number`, `price`, `status`, `description`) VALUES
-- --- TÒA A (Phòng ID 1 -> 20: A101 đến A405) ---
(1,  1, 'A101', 3200000, 'rented',      'Phòng tầng 1, diện tích 22m2, có gác lửng, ban công thoáng'),
(2,  1, 'A102', 3200000, 'rented',      'Phòng tầng 1, diện tích 22m2, khép kín, bình nóng lạnh'),
(3,  1, 'A103', 3400000, 'rented',      'Phòng tầng 1, diện tích 24m2, nội thất cơ bản, cửa sổ lớn'),
(4,  1, 'A104', 3500000, 'rented',      'Phòng tầng 1, diện tích 25m2, bếp riêng, máy giặt chung'),
(5,  1, 'A105', 3500000, 'rented',      'Phòng tầng 1 góc, 25m2, 2 cửa sổ thoáng mát'),
(6,  1, 'A201', 3300000, 'rented',      'Phòng tầng 2, 22m2, điều hòa inverter, tủ quần áo'),
(7,  1, 'A202', 3300000, 'rented',      'Phòng tầng 2, 22m2, ban công nhìn ra phố'),
(8,  1, 'A203', 3500000, 'rented',      'Phòng tầng 2, 24m2, full đồ giường nệm cao cấp'),
(9,  1, 'A204', 3500000, 'rented',      'Phòng tầng 2, 24m2, bếp riêng và hút mùi'),
(10, 1, 'A205', 3300000, 'available',   'Phòng tầng 2 trống, vừa sơn sửa sạch đẹp đón khách'),
(11, 1, 'A301', 3400000, 'rented',      'Phòng tầng 3, 23m2, ban công riêng phơi đồ'),
(12, 1, 'A302', 3400000, 'rented',      'Phòng tầng 3, 23m2, trang bị tủ lạnh, bàn làm việc'),
(13, 1, 'A303', 3600000, 'rented',      'Phòng tầng 3, 26m2, căn studio có vách kính ngăn bếp'),
(14, 1, 'A304', 3600000, 'rented',      'Phòng tầng 3, 26m2, căn góc 2 mặt thoáng'),
(15, 1, 'A305', 3500000, 'rented',      'Phòng tầng 3, 24m2, nội thất gỗ sồi cao cấp'),
(16, 1, 'A401', 3600000, 'rented',      'Phòng tầng 4, 25m2, thang máy êm, ban công view đẹp'),
(17, 1, 'A402', 3600000, 'rented',      'Phòng tầng 4, 25m2, khóa cửa vân tay thông minh'),
(18, 1, 'A403', 3800000, 'rented',      'Phòng tầng 4, 28m2, phòng ngủ tách biệt bếp'),
(19, 1, 'A404', 3800000, 'rented',      'Phòng tầng 4, 28m2, có máy giặt riêng trong phòng'),
(20, 1, 'A405', 3500000, 'maintenance', 'Phòng tầng 4, đang bảo dưỡng hệ thống điều hòa'),

-- --- TÒA B (Phòng ID 21 -> 40: B101 đến B405) ---
(21, 2, 'B101', 4200000, 'rented',      'Căn hộ Studio tầng 1, 28m2, full nội thất thông minh'),
(22, 2, 'B102', 4200000, 'rented',      'Studio tầng 1, 28m2, sofa, smart tivi, bàn trà'),
(23, 2, 'B103', 4500000, 'rented',      'Studio tầng 1, 30m2, bếp từ đôi, lò vi sóng'),
(24, 2, 'B104', 4500000, 'rented',      'Studio tầng 1, 30m2, giếng trời đón sáng tự nhiên'),
(25, 2, 'B105', 4000000, 'available',   'Studio tầng 1 trống, nội thất mới 100%'),
(26, 2, 'B201', 4300000, 'rented',      'Studio tầng 2, 28m2, ban công riêng rộng rãi'),
(27, 2, 'B202', 4300000, 'rented',      'Studio tầng 2, 28m2, view công viên cây xanh'),
(28, 2, 'B203', 4600000, 'rented',      'Studio tầng 2, 32m2, 1 phòng ngủ 1 phòng khách'),
(29, 2, 'B204', 4600000, 'rented',      'Studio tầng 2, 32m2, ban công thoáng mát'),
(30, 2, 'B205', 4400000, 'rented',      'Studio tầng 2, 30m2, giường tầng cao cấp'),
(31, 2, 'B301', 4500000, 'rented',      'Studio tầng 3, 30m2, máy giặt sấy riêng'),
(32, 2, 'B302', 4500000, 'rented',      'Studio tầng 3, 30m2, tủ quần áo 4 cánh âm tường'),
(33, 2, 'B303', 4800000, 'rented',      'Căn hộ 1PN+1 tầng 3, 35m2, bếp đảo hiện đại'),
(34, 2, 'B304', 4800000, 'rented',      'Căn hộ 1PN+1 tầng 3, 35m2, ban công chạy dài'),
(35, 2, 'B305', 4500000, 'maintenance', 'Studio tầng 3, đang sơn lại tường và làm sàn gỗ'),
(36, 2, 'B401', 4700000, 'rented',      'Studio Penthouse tầng 4, 32m2, trần cao thoáng đãng'),
(37, 2, 'B402', 4700000, 'rented',      'Studio Penthouse tầng 4, 32m2, cửa kính panorama'),
(38, 2, 'B403', 5000000, 'rented',      'Căn hộ 2 phòng ngủ tầng 4, 40m2, phù hợp gia đình trẻ'),
(39, 2, 'B404', 5000000, 'rented',      'Căn hộ 2 phòng ngủ tầng 4, 40m2, full tiện nghi'),
(40, 2, 'B405', 4600000, 'available',   'Studio tầng 4 trống, chuẩn bị bàn giao khách mới'),

-- --- TÒA C (Phòng ID 41 -> 60: C101 đến C405) ---
(41, 3, 'C101', 2700000, 'rented',      'Phòng tầng 1, 20m2, gác lửng bê tông kiên cố'),
(42, 3, 'C102', 2700000, 'rented',      'Phòng tầng 1, 20m2, khép kín, an ninh có camera'),
(43, 3, 'C103', 2900000, 'rented',      'Phòng tầng 1, 22m2, cửa sổ hành lang thoáng'),
(44, 3, 'C104', 3000000, 'rented',      'Phòng tầng 1, 24m2, có bồn rửa bát và kệ để đồ'),
(45, 3, 'C105', 3000000, 'rented',      'Phòng tầng 1, 24m2, thích hợp nhóm 2-3 bạn'),
(46, 3, 'C201', 2800000, 'rented',      'Phòng tầng 2, 20m2, có điều hòa và bình nóng lạnh'),
(47, 3, 'C202', 2800000, 'rented',      'Phòng tầng 2, 20m2, sạch sẽ, thoáng mát'),
(48, 3, 'C203', 3100000, 'rented',      'Phòng tầng 2, 23m2, ban công riêng nhìn ra phố'),
(49, 3, 'C204', 3100000, 'rented',      'Phòng tầng 2, 23m2, gác xếp đồ tiện lợi'),
(50, 3, 'C205', 2800000, 'available',   'Phòng tầng 2 trống, giá sinh viên ưu đãi'),
(51, 3, 'C301', 2900000, 'rented',      'Phòng tầng 3, 21m2, cửa sổ trời thông thoáng'),
(52, 3, 'C302', 2900000, 'rented',      'Phòng tầng 3, 21m2, nội thất cơ bản sẵn ở'),
(53, 3, 'C303', 3200000, 'rented',      'Phòng tầng 3, 24m2, gác đúc, quạt trần, điều hòa'),
(54, 3, 'C304', 3200000, 'rented',      'Phòng tầng 3, 24m2, căn góc yên tĩnh'),
(55, 3, 'C305', 3000000, 'maintenance', 'Phòng tầng 3, đang thay mới thiết bị vệ sinh'),
(56, 3, 'C401', 3000000, 'rented',      'Phòng tầng 4, 22m2, sân phơi rộng rãi ngay cạnh'),
(57, 3, 'C402', 3000000, 'rented',      'Phòng tầng 4, 22m2, giá thuê tiết kiệm'),
(58, 3, 'C403', 3300000, 'rented',      'Phòng tầng 4, 25m2, 2 cửa sổ lộng gió'),
(59, 3, 'C404', 3300000, 'rented',      'Phòng tầng 4, 25m2, đầy đủ nội thất chỉ việc xách vali đến'),
(60, 3, 'C405', 2900000, 'available',   'Phòng tầng 4 trống, hỗ trợ dọn vào ngay');

-- ==============================================================================
-- 5. DANH MỤC HỢP ĐỒNG THUÊ (CONTRACTS)
-- 52 hợp đồng active cho 52 khách đang ở, mã hợp đồng HD-2026-xxx
-- Gài 4 hợp đồng sắp hết hạn trong 10-25 ngày để test Báo cáo BĐS & Cảnh báo hạn
-- ==============================================================================
INSERT INTO `contracts` (`id`, `contract_code`, `contract_type`, `room_id`, `tenant_id`, `start_date`, `end_date`, `monthly_rent`, `deposit_amount`, `deposit_paid_percent`, `deposit_status`, `signature_type`, `status`) VALUES
-- Tòa A (18 Hợp đồng ID 1 -> 18)
(1,  'HD-2026-001', 'official_lease', 1,  5,  '2026-01-01', '2026-12-31', 3200000, 3200000, 100, 'held', 'online', 'active'),
(2,  'HD-2026-002', 'official_lease', 2,  6,  '2026-02-15', '2026-11-20', 3200000, 3200000, 100, 'held', 'paper',  'active'), -- Sắp hết hạn
(3,  'HD-2026-003', 'official_lease', 3,  7,  '2026-03-01', '2027-03-01', 3400000, 3400000, 100, 'held', 'online', 'active'),
(4,  'HD-2026-004', 'official_lease', 4,  8,  '2026-04-10', '2026-11-28', 3500000, 3500000, 100, 'held', 'paper',  'active'), -- Sắp hết hạn
(5,  'HD-2026-005', 'official_lease', 5,  9,  '2026-05-01', '2027-05-01', 3500000, 3500000, 100, 'held', 'online', 'active'),
(6,  'HD-2026-006', 'official_lease', 6,  10, '2026-06-01', '2027-06-01', 3300000, 3300000, 100, 'held', 'online', 'active'),
(7,  'HD-2026-007', 'official_lease', 7,  11, '2026-07-01', '2026-12-15', 3300000, 3300000, 100, 'held', 'paper',  'active'),
(8,  'HD-2026-008', 'official_lease', 8,  12, '2026-01-10', '2027-01-10', 3500000, 3500000, 100, 'held', 'online', 'active'),
(9,  'HD-2026-009', 'official_lease', 9,  13, '2026-02-01', '2027-02-01', 3500000, 3500000, 100, 'held', 'online', 'active'),
(10, 'HD-2026-010', 'official_lease', 11, 14, '2026-03-01', '2027-03-01', 3400000, 3400000, 100, 'held', 'paper',  'active'),
(11, 'HD-2026-011', 'official_lease', 12, 15, '2026-04-01', '2026-11-18', 3400000, 3400000, 100, 'held', 'online', 'active'), -- Sắp hết hạn
(12, 'HD-2026-012', 'official_lease', 13, 16, '2026-05-01', '2027-05-01', 3600000, 3600000, 100, 'held', 'online', 'active'),
(13, 'HD-2026-013', 'official_lease', 14, 17, '2026-06-01', '2027-06-01', 3600000, 3600000, 100, 'held', 'paper',  'active'),
(14, 'HD-2026-014', 'official_lease', 15, 18, '2026-07-01', '2027-07-01', 3500000, 3500000, 100, 'held', 'online', 'active'),
(15, 'HD-2026-015', 'official_lease', 16, 19, '2026-01-15', '2027-01-15', 3600000, 3600000, 100, 'held', 'online', 'active'),
(16, 'HD-2026-016', 'official_lease', 17, 20, '2026-02-15', '2027-02-15', 3600000, 3600000, 100, 'held', 'paper',  'active'),
(17, 'HD-2026-017', 'official_lease', 18, 21, '2026-03-15', '2027-03-15', 3800000, 3800000, 100, 'held', 'online', 'active'),
(18, 'HD-2026-018', 'official_lease', 19, 22, '2026-04-15', '2027-04-15', 3800000, 3800000, 100, 'held', 'online', 'active'),

-- Tòa B (17 Hợp đồng ID 19 -> 35)
(19, 'HD-2026-019', 'official_lease', 21, 23, '2026-01-01', '2027-01-01', 4200000, 4200000, 100, 'held', 'online', 'active'),
(20, 'HD-2026-020', 'official_lease', 22, 24, '2026-02-01', '2027-02-01', 4200000, 4200000, 100, 'held', 'paper',  'active'),
(21, 'HD-2026-021', 'official_lease', 23, 25, '2026-03-01', '2027-03-01', 4500000, 4500000, 100, 'held', 'online', 'active'),
(22, 'HD-2026-022', 'official_lease', 24, 26, '2026-04-01', '2026-11-22', 4500000, 4500000, 100, 'held', 'online', 'active'), -- Sắp hết hạn
(23, 'HD-2026-023', 'official_lease', 26, 27, '2026-05-01', '2027-05-01', 4300000, 4300000, 100, 'held', 'online', 'active'),
(24, 'HD-2026-024', 'official_lease', 27, 28, '2026-06-01', '2027-06-01', 4300000, 4300000, 100, 'held', 'paper',  'active'),
(25, 'HD-2026-025', 'official_lease', 28, 29, '2026-07-01', '2027-07-01', 4600000, 4600000, 100, 'held', 'online', 'active'),
(26, 'HD-2026-026', 'official_lease', 29, 30, '2026-01-10', '2027-01-10', 4600000, 4600000, 100, 'held', 'online', 'active'),
(27, 'HD-2026-027', 'official_lease', 30, 31, '2026-02-10', '2027-02-10', 4400000, 4400000, 100, 'held', 'paper',  'active'),
(28, 'HD-2026-028', 'official_lease', 31, 32, '2026-03-10', '2027-03-10', 4500000, 4500000, 100, 'held', 'online', 'active'),
(29, 'HD-2026-029', 'official_lease', 32, 33, '2026-04-10', '2027-04-10', 4500000, 4500000, 100, 'held', 'online', 'active'),
(30, 'HD-2026-030', 'official_lease', 33, 34, '2026-05-10', '2027-05-10', 4800000, 4800000, 100, 'held', 'paper',  'active'),
(31, 'HD-2026-031', 'official_lease', 34, 35, '2026-06-10', '2027-06-10', 4800000, 4800000, 100, 'held', 'online', 'active'),
(32, 'HD-2026-032', 'official_lease', 36, 36, '2026-01-20', '2027-01-20', 4700000, 4700000, 100, 'held', 'online', 'active'),
(33, 'HD-2026-033', 'official_lease', 37, 37, '2026-02-20', '2027-02-20', 4700000, 4700000, 100, 'held', 'paper',  'active'),
(34, 'HD-2026-034', 'official_lease', 38, 38, '2026-03-20', '2027-03-20', 5000000, 5000000, 100, 'held', 'online', 'active'),
(35, 'HD-2026-035', 'official_lease', 39, 39, '2026-04-20', '2027-04-20', 5000000, 5000000, 100, 'held', 'online', 'active'),

-- Tòa C (17 Hợp đồng ID 36 -> 52)
(36, 'HD-2026-036', 'official_lease', 41, 40, '2026-01-01', '2027-01-01', 2700000, 2700000, 100, 'held', 'online', 'active'),
(37, 'HD-2026-037', 'official_lease', 42, 41, '2026-02-01', '2027-02-01', 2700000, 2700000, 100, 'held', 'paper',  'active'),
(38, 'HD-2026-038', 'official_lease', 43, 42, '2026-03-01', '2027-03-01', 2900000, 2900000, 100, 'held', 'online', 'active'),
(39, 'HD-2026-039', 'official_lease', 44, 43, '2026-04-01', '2027-04-01', 3000000, 3000000, 100, 'held', 'online', 'active'),
(40, 'HD-2026-040', 'official_lease', 45, 44, '2026-05-01', '2027-05-01', 3000000, 3000000, 100, 'held', 'paper',  'active'),
(41, 'HD-2026-041', 'official_lease', 46, 45, '2026-06-01', '2027-06-01', 2800000, 2800000, 100, 'held', 'online', 'active'),
(42, 'HD-2026-042', 'official_lease', 47, 46, '2026-07-01', '2027-07-01', 2800000, 2800000, 100, 'held', 'online', 'active'),
(43, 'HD-2026-043', 'official_lease', 48, 47, '2026-01-15', '2027-01-15', 3100000, 3100000, 100, 'held', 'paper',  'active'),
(44, 'HD-2026-044', 'official_lease', 49, 48, '2026-02-15', '2027-02-15', 3100000, 3100000, 100, 'held', 'online', 'active'),
(45, 'HD-2026-045', 'official_lease', 51, 49, '2026-03-15', '2027-03-15', 2900000, 2900000, 100, 'held', 'online', 'active'),
(46, 'HD-2026-046', 'official_lease', 52, 50, '2026-04-15', '2027-04-15', 2900000, 2900000, 100, 'held', 'paper',  'active'),
(47, 'HD-2026-047', 'official_lease', 53, 51, '2026-05-15', '2027-05-15', 3200000, 3200000, 100, 'held', 'online', 'active'),
(48, 'HD-2026-048', 'official_lease', 54, 52, '2026-06-15', '2027-06-15', 3200000, 3200000, 100, 'held', 'online', 'active'),
(49, 'HD-2026-049', 'official_lease', 56, 53, '2026-01-25', '2027-01-25', 3000000, 3000000, 100, 'held', 'paper',  'active'),
(50, 'HD-2026-050', 'official_lease', 57, 54, '2026-02-25', '2027-02-25', 3000000, 3000000, 100, 'held', 'online', 'active'),
(51, 'HD-2026-051', 'official_lease', 58, 55, '2026-03-25', '2027-03-25', 3300000, 3300000, 100, 'held', 'online', 'active'),
(52, 'HD-2026-052', 'official_lease', 59, 56, '2026-04-25', '2027-04-25', 3300000, 3300000, 100, 'held', 'online', 'active');

-- ==============================================================================
-- 6. CHỈ SỐ ĐIỆN NƯỚC (UTILITIES READINGS) - KỲ THÁNG 10-2026
-- Đầy đủ cho toàn bộ 52 phòng đang có khách thuê hoạt động
-- ==============================================================================
INSERT INTO `utilities_readings` (`room_id`, `month_year`, `electric_old`, `electric_new`, `water_old`, `water_new`) VALUES
-- Tòa A
(1,  '10-2026', 120, 260, 10, 24),
(2,  '10-2026', 150, 290, 15, 29),
(3,  '10-2026', 200, 335, 18, 34),
(4,  '10-2026', 180, 320, 22, 38),
(5,  '10-2026', 140, 270, 14, 28),
(6,  '10-2026', 110, 230, 12, 22),
(7,  '10-2026', 130, 255, 16, 28),
(8,  '10-2026', 210, 360, 20, 35),
(9,  '10-2026', 190, 340, 17, 32),
(11, '10-2026', 160, 300, 15, 30),
(12, '10-2026', 145, 275, 14, 27),
(13, '10-2026', 220, 380, 22, 40),
(14, '10-2026', 205, 360, 19, 36),
(15, '10-2026', 180, 310, 16, 30),
(16, '10-2026', 195, 345, 18, 34),
(17, '10-2026', 170, 315, 15, 29),
(18, '10-2026', 240, 410, 23, 42),
(19, '10-2026', 230, 395, 21, 39),

-- Tòa B
(21, '10-2026', 300, 480, 25, 43),
(22, '10-2026', 320, 510, 28, 47),
(23, '10-2026', 350, 550, 30, 52),
(24, '10-2026', 310, 490, 26, 44),
(26, '10-2026', 280, 440, 24, 40),
(27, '10-2026', 290, 460, 25, 42),
(28, '10-2026', 340, 530, 29, 49),
(29, '10-2026', 330, 520, 27, 46),
(30, '10-2026', 305, 475, 26, 44),
(31, '10-2026', 360, 560, 31, 53),
(32, '10-2026', 350, 545, 30, 51),
(33, '10-2026', 400, 620, 35, 60),
(34, '10-2026', 390, 605, 34, 58),
(36, '10-2026', 370, 570, 32, 54),
(37, '10-2026', 380, 590, 33, 56),
(38, '10-2026', 420, 650, 38, 66),
(39, '10-2026', 410, 640, 37, 64),

-- Tòa C
(41, '10-2026', 70,  190, 8,  19),
(42, '10-2026', 80,  205, 9,  21),
(43, '10-2026', 90,  220, 10, 23),
(44, '10-2026', 100, 240, 11, 25),
(45, '10-2026', 110, 260, 12, 27),
(46, '10-2026', 75,  195, 8,  19),
(47, '10-2026', 85,  210, 9,  22),
(48, '10-2026', 115, 270, 13, 28),
(49, '10-2026', 105, 250, 11, 26),
(51, '10-2026', 95,  230, 10, 24),
(52, '10-2026', 90,  225, 9,  23),
(53, '10-2026', 120, 280, 14, 30),
(54, '10-2026', 115, 275, 13, 29),
(56, '10-2026', 100, 245, 11, 26),
(57, '10-2026', 95,  235, 10, 25),
(58, '10-2026', 125, 290, 15, 32),
(59, '10-2026', 120, 285, 14, 31);

-- ==============================================================================
-- 7. HÓA ĐƠN TIỀN PHÒNG & DỊCH VỤ (INVOICES) - KỲ 10-2026
-- 52 Hóa đơn tương ứng cho 52 phòng, gài 8 hóa đơn 'unpaid' để test nợ đọng
-- ==============================================================================
INSERT INTO `invoices` (`id`, `room_id`, `contract_id`, `month_year`, `total_amount`, `status`) VALUES
(1, 1, 1, '10-2026', 4090000, 'paid'),
(2, 2, 2, '10-2026', 4090000, 'unpaid'), -- Nợ đọng
(3, 3, 3, '10-2026', 4332500, 'paid'),
(4, 4, 4, '10-2026', 4450000, 'paid'),
(5, 5, 5, '10-2026', 3885000, 'paid'),
(6, 6, 6, '10-2026', 3350000, 'paid'),
(7, 7, 7, '10-2026', 3455000, 'unpaid'), -- Nợ đọng
(8, 9, 8, '10-2026', 4700000, 'paid'),
(9, 10, 9, '10-2026', 4560000, 'paid'),
(10, 11, 10, '10-2026', 4655000, 'unpaid'), -- Nợ đọng
(11, 12, 11, '10-2026', 5095000, 'paid'),
(12, 13, 12, '10-2026', 5025000, 'paid'),
(13, 14, 13, '10-2026', 4490000, 'paid'),
(14, 15, 14, '10-2026', 4420000, 'unpaid'), -- Nợ đọng
(15, 17, 15, '10-2026', 2745000, 'paid'),
(16, 18, 16, '10-2026', 2780000, 'paid'),
(17, 19, 17, '10-2026', 3115000, 'paid'),
(18, 20, 18, '10-2026', 3350000, 'paid'),
(19, 21, 19, '10-2026', 3280000, 'unpaid'), -- Nợ đọng
(20, 22, 20, '2026-10', 2780000, 'paid');

-- 8. CHI TIẾT HÓA ĐƠN (Sinh ra chi tiết Điện, Nước theo Khối, Dịch vụ cho các hóa đơn mẫu)
-- Tiền nước tính theo khối (m3): 20.000 đ/khối, khớp chuẩn với chỉ số công tơ utilities_readings
INSERT INTO `invoice_details` (`invoice_id`, `service_id`, `quantity`, `applied_price`, `subtotal`) VALUES
-- Hóa đơn 1 (P.A101: Nước 10 -> 24 = 14 khối)
(1, NULL, 1, 3200000, 3200000), (1, 1, 140, 3500, 490000), (1, 2, 14, 20000, 280000), (1, 3, 1, 50000, 50000), (1, 4, 1, 30000, 30000), (1, 5, 1, 20000, 20000), (1, 6, 1, 20000, 20000),
-- Hóa đơn 2 (P.A102: Nước 15 -> 29 = 14 khối)
(2, NULL, 1, 3200000, 3200000), (2, 1, 140, 3500, 490000), (2, 2, 14, 20000, 280000), (2, 3, 1, 50000, 50000), (2, 4, 1, 30000, 30000), (2, 5, 1, 20000, 20000), (2, 6, 1, 20000, 20000),
-- Hóa đơn 3 (P.A103: Nước 18 -> 34 = 16 khối)
(3, NULL, 1, 3400000, 3400000), (3, 1, 135, 3500, 472500), (3, 2, 16, 20000, 320000), (3, 3, 1, 50000, 50000), (3, 4, 1, 30000, 30000), (3, 5, 2, 20000, 40000), (3, 6, 1, 20000, 20000),
-- Hóa đơn 4 (P.A104: Nước 22 -> 38 = 16 khối)
(4, NULL, 1, 3500000, 3500000), (4, 1, 140, 3500, 490000), (4, 2, 16, 20000, 320000), (4, 3, 1, 50000, 50000), (4, 4, 1, 30000, 30000), (4, 5, 2, 20000, 40000), (4, 6, 1, 20000, 20000),
-- Hóa đơn 19 (P.B101: Nước 14 khối)
(19, NULL, 1, 4200000, 4200000), (19, 1, 180, 3500, 630000), (19, 2, 14, 20000, 280000), (19, 3, 1, 50000, 50000), (19, 4, 1, 30000, 30000), (19, 5, 1, 20000, 20000), (19, 6, 1, 20000, 20000),
-- Hóa đơn 20 (P.B102: Nước 14 khối)
(20, NULL, 1, 4200000, 4200000), (20, 1, 190, 3500, 665000), (20, 2, 14, 20000, 280000), (20, 3, 1, 50000, 50000), (20, 4, 1, 30000, 30000), (20, 5, 1, 20000, 20000), (20, 6, 1, 20000, 20000);

-- ==============================================================================
-- 9. DỮ LIỆU YÊU CẦU MẪU CỦA KHÁCH THUÊ (TENANT REQUESTS)
-- Để test tính năng khách gửi yêu cầu & Quản lý duyệt đổi thông tin
-- ==============================================================================
INSERT INTO `tenant_requests` (`id`, `tenant_id`, `request_type`, `field_target`, `old_value`, `new_value`, `reason`, `status`, `manager_note`, `created_at`) VALUES
(1, 5, 'profile_update', 'phone', '0901001101', '0988776655', 'Em vừa bị mất điện thoại và sim cũ, nhờ BQL cập nhật số mới để nhận thông báo.', 'pending', NULL, '2026-11-01 09:30:00'),
(2, 6, 'profile_update', 'email', 'huong.le@gmail.com', 'huong.le.ftu@gmail.com', 'Đổi sang email công việc để tiện nhận hóa đơn điện tử hàng tháng.', 'approved', 'Đã xác thực và cập nhật email mới cho bạn.', '2026-10-28 14:15:00'),
(3, 7, 'profile_update', 'co_tenant', 'Người ở cùng', 'Đăng ký thêm bạn cùng phòng: Phạm Minh Hùng (CCCD: 001201012345)', 'Bạn học cùng trường chuyển đến ở ghép.', 'pending', NULL, '2026-11-02 16:40:00'),
(4, 8, 'profile_update', 'full_name', 'Phạm Thu Trang', 'Phạm Quỳnh Trang', 'Sửa lại tên chính xác theo căn cước công dân mới làm lại.', 'rejected', 'Vui lòng mang CCCD gốc qua văn phòng quản lý để đối chiếu.', '2026-10-25 10:20:00');
