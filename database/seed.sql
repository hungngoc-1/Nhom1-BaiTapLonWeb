-- ==============================================================================
-- STAYMATE - DỮ LIỆU KHỞI TẠO CƠ SỞ DỮ LIỆU MẪU CHÍNH THỨC (SEED SQL)
-- Đầy đủ 3 Tòa nhà, 60 Phòng, 20 Hợp đồng, Chỉ số & Hóa đơn các kỳ 10-2026 & 11-2026
-- Tự động tính tiền cọc vào Tổng cộng khi khách mới cọc 50%, không tính khi cọc 100%
-- Cập nhật mới nhất: 04/11/2026 05:29:04
-- ==============================================================================

USE `quan_ly_nha_tro`;
SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------------
-- BẢNG: `users` (25 bản ghi)
-- -------------------------------------------------------------
DELETE FROM `users`;
ALTER TABLE `users` AUTO_INCREMENT = 1;
INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `phone`, `cccd`, `birthday`, `gender`, `email`, `occupation`, `hometown`, `emergency_contact`, `status`, `roommate_name`, `roommate_phone`, `role`, `preferred_room_id`, `created_at`) VALUES
(1, 'admin', 'hash', 'Quản Trị Viên Hệ Thống', '0900000000', NULL, NULL, 'Nam', 'admin@staymate.vn', NULL, NULL, NULL, 'Đang thuê', NULL, NULL, 'admin', NULL, '2026-11-03 18:32:16'),
(2, 'quanly_a', 'hash', 'Nguyễn Văn Quân', '0911111111', NULL, NULL, 'Nam', 'quana@staymate.vn', NULL, NULL, NULL, 'Đang thuê', NULL, NULL, 'manager', NULL, '2026-11-03 18:32:16'),
(3, 'quanly_b', 'hash', 'Trần Thị Bích', '0922222222', NULL, NULL, 'Nam', 'bichb@staymate.vn', NULL, NULL, NULL, 'Đang thuê', NULL, NULL, 'manager', NULL, '2026-11-03 18:32:16'),
(4, 'quanly_c', 'hash', 'Lê Hoàng Nam', '0933333333', NULL, NULL, 'Nam', 'namc@staymate.vn', NULL, NULL, NULL, 'Đang thuê', NULL, NULL, 'manager', NULL, '2026-11-03 18:32:16'),
(5, 'kh_01', 'hash', 'Nguyễn Tiến Đạt', '0901000001', NULL, NULL, 'Nam', 'dat@gmail.com', NULL, NULL, NULL, 'Đang thuê', NULL, NULL, 'tenant', NULL, '2026-11-03 18:32:16'),
(6, 'kh_02', 'hash', 'Lê Thị Thu Hương', '0901000002', NULL, NULL, 'Nam', 'huong@gmail.com', NULL, NULL, NULL, 'Đang thuê', NULL, NULL, 'tenant', NULL, '2026-11-03 18:32:16'),
(7, 'kh_03', 'hash', 'Trần Hoàng Long', '0901000003', NULL, NULL, 'Nam', 'long@gmail.com', NULL, NULL, NULL, 'Đang thuê', NULL, NULL, 'tenant', NULL, '2026-11-03 18:32:16'),
(8, 'kh_04', 'hash', 'Phạm Thu Trang', '0901000004', NULL, NULL, 'Nam', 'trang@gmail.com', NULL, NULL, NULL, 'Đang thuê', NULL, NULL, 'tenant', NULL, '2026-11-03 18:32:16'),
(9, 'kh_05', 'hash', 'Vũ Minh Tuấn', '0901000005', NULL, NULL, 'Nam', 'tuan@gmail.com', NULL, NULL, NULL, 'Đang thuê', NULL, NULL, 'tenant', NULL, '2026-11-03 18:32:16'),
(10, 'kh_06', 'hash', 'Đặng Kim Thủy', '0901000006', NULL, NULL, 'Nam', 'thuy@gmail.com', NULL, NULL, NULL, 'Đang thuê', NULL, NULL, 'tenant', NULL, '2026-11-03 18:32:16'),
(11, 'kh_07', 'hash', 'Bùi Xuân Bách', '0901000007', NULL, NULL, 'Nam', 'bach@gmail.com', NULL, NULL, NULL, 'Đang thuê', NULL, NULL, 'tenant', NULL, '2026-11-03 18:32:16'),
(12, 'kh_08', 'hash', 'Hồ Quang Hiếu', '0901000008', NULL, NULL, 'Nam', 'hieu@gmail.com', NULL, NULL, NULL, 'Đang thuê', NULL, NULL, 'tenant', NULL, '2026-11-03 18:32:16'),
(13, 'kh_09', 'hash', 'Ngô Phương Lan', '0901000009', NULL, NULL, 'Nam', 'lan@gmail.com', NULL, NULL, NULL, 'Đang thuê', NULL, NULL, 'tenant', NULL, '2026-11-03 18:32:16'),
(14, 'kh_10', 'hash', 'Đinh Trọng Thủy', '0901000010', NULL, NULL, 'Nam', 'trongthuy@gmail.com', NULL, NULL, NULL, 'Đang thuê', NULL, NULL, 'tenant', NULL, '2026-11-03 18:32:16'),
(15, 'kh_11', 'hash', 'Hoàng Thanh Tùng', '0901000011', NULL, NULL, 'Nam', 'tung@gmail.com', NULL, NULL, NULL, 'Đang thuê', NULL, NULL, 'tenant', NULL, '2026-11-03 18:32:16'),
(16, 'kh_12', 'hash', 'Đặng Thùy Trâm', '0901000012', NULL, NULL, 'Nam', 'tram@gmail.com', NULL, NULL, NULL, 'Đang thuê', NULL, NULL, 'tenant', NULL, '2026-11-03 18:32:16'),
(17, 'kh_13', 'hash', 'Lý Tự Trọng', '0901000013', NULL, NULL, 'Nam', 'trong@gmail.com', NULL, NULL, NULL, 'Đang thuê', NULL, NULL, 'tenant', NULL, '2026-11-03 18:32:16'),
(18, 'kh_14', 'hash', 'Phan Châu Trinh', '0901000014', NULL, NULL, 'Nam', 'trinh@gmail.com', NULL, NULL, NULL, 'Đang thuê', NULL, NULL, 'tenant', NULL, '2026-11-03 18:32:16'),
(19, 'kh_15', 'hash', 'Võ Thị Sáu', '0901000015', NULL, NULL, 'Nam', 'sau@gmail.com', NULL, NULL, NULL, 'Đang thuê', NULL, NULL, 'tenant', NULL, '2026-11-03 18:32:16'),
(20, 'kh_16', 'hash', 'Trương Định', '0901000016', NULL, NULL, 'Nam', 'dinh@gmail.com', NULL, NULL, NULL, 'Đang thuê', NULL, NULL, 'tenant', NULL, '2026-11-03 18:32:16'),
(21, 'kh_17', 'hash', 'Nguyễn Thái Học', '0901000017', NULL, NULL, 'Nam', 'hoc@gmail.com', NULL, NULL, NULL, 'Đang thuê', NULL, NULL, 'tenant', NULL, '2026-11-03 18:32:16'),
(22, 'kh_18', 'hash', 'Kim Đồng', '0901000018', NULL, NULL, 'Nam', 'dong@gmail.com', NULL, NULL, NULL, 'Đang thuê', NULL, NULL, 'tenant', NULL, '2026-11-03 18:32:16'),
(23, 'kh_19', 'hash', 'Lê Hồng Phong', '0901000019', NULL, NULL, 'Nam', 'phong@gmail.com', NULL, NULL, NULL, 'Đang thuê', NULL, NULL, 'tenant', NULL, '2026-11-03 18:32:16'),
(24, 'kh_20', 'hash', 'Nguyễn Thị Minh Khai', '0901000020', NULL, NULL, 'Nam', 'khai@gmail.com', NULL, NULL, NULL, 'Đang thuê', NULL, NULL, 'tenant', NULL, '2026-11-03 18:32:16'),
(25, '0332987456', '$2y$10$nbDViHJhBRdgy3NEu704eeJWsEIzRchASTeKuroQjz.5jE2i4sVqa', 'Dương Thị Trang', '0332987456', '001306034492', '2026-03-20', 'Nữ', 'duongtrang29032k6@gmail.com', 'Sinh viên', 'Hà Nội', '0332987456', 'Đang thuê', 'Nguyễn Ánh Ngọc', '0933762534', 'tenant', 8, '2026-11-04 11:50:43');

-- -------------------------------------------------------------
-- BẢNG: `buildings` (3 bản ghi)
-- -------------------------------------------------------------
DELETE FROM `buildings`;
ALTER TABLE `buildings` AUTO_INCREMENT = 1;
INSERT INTO `buildings` (`id`, `name`, `address`, `manager_id`, `created_at`) VALUES
(1, 'Tòa A - StayMate Sunrise', 'Số 128 Cầu Giấy, Hà Nội', 2, '2026-11-03 18:32:16'),
(2, 'Tòa B - StayMate Emerald', 'Số 45 Nguyễn Chí Thanh, Hà Nội', 3, '2026-11-03 18:32:16'),
(3, 'Tòa C - StayMate Horizon', 'Số 88 Nguyễn Trãi, Hà Nội', 4, '2026-11-03 18:32:16');

-- -------------------------------------------------------------
-- BẢNG: `rooms` (24 bản ghi)
-- -------------------------------------------------------------
DELETE FROM `rooms`;
ALTER TABLE `rooms` AUTO_INCREMENT = 1;
INSERT INTO `rooms` (`id`, `building_id`, `room_number`, `price`, `status`, `description`, `created_at`) VALUES
(1, 1, 'A101', '3000000.00', 'rented', NULL, '2026-11-03 18:32:16'),
(2, 1, 'A102', '3200000.00', 'rented', NULL, '2026-11-03 18:32:16'),
(3, 1, 'A103', '3200000.00', 'rented', NULL, '2026-11-03 18:32:16'),
(4, 1, 'A104', '3500000.00', 'rented', NULL, '2026-11-03 18:32:16'),
(5, 1, 'A105', '3500000.00', 'rented', NULL, '2026-11-03 18:32:16'),
(6, 1, 'A201', '3000000.00', 'rented', NULL, '2026-11-03 18:32:16'),
(7, 1, 'A202', '3000000.00', 'rented', NULL, '2026-11-03 18:32:16'),
(8, 1, 'A203', '3000000.00', 'rented', NULL, '2026-11-03 18:32:16'),
(9, 2, 'B101', '4000000.00', 'rented', NULL, '2026-11-03 18:32:16'),
(10, 2, 'B102', '4000000.00', 'rented', NULL, '2026-11-03 18:32:16'),
(11, 2, 'B103', '4200000.00', 'rented', NULL, '2026-11-03 18:32:16'),
(12, 2, 'B104', '4500000.00', 'rented', NULL, '2026-11-03 18:32:16'),
(13, 2, 'B105', '4500000.00', 'rented', NULL, '2026-11-03 18:32:16'),
(14, 2, 'B201', '4000000.00', 'rented', NULL, '2026-11-03 18:32:16'),
(15, 2, 'B202', '4000000.00', 'rented', NULL, '2026-11-03 18:32:16'),
(16, 2, 'B203', '4000000.00', 'available', NULL, '2026-11-03 18:32:16'),
(17, 3, 'C101', '2500000.00', 'rented', NULL, '2026-11-03 18:32:16'),
(18, 3, 'C102', '2500000.00', 'rented', NULL, '2026-11-03 18:32:16'),
(19, 3, 'C103', '2800000.00', 'rented', NULL, '2026-11-03 18:32:16'),
(20, 3, 'C104', '3000000.00', 'rented', NULL, '2026-11-03 18:32:16'),
(21, 3, 'C105', '3000000.00', 'rented', NULL, '2026-11-03 18:32:16'),
(22, 3, 'C201', '2500000.00', 'rented', NULL, '2026-11-03 18:32:16'),
(23, 3, 'C202', '2500000.00', 'maintenance', NULL, '2026-11-03 18:32:16'),
(24, 3, 'C203', '2500000.00', 'available', NULL, '2026-11-03 18:32:16');

-- -------------------------------------------------------------
-- BẢNG: `services` (12 bản ghi)
-- -------------------------------------------------------------
DELETE FROM `services`;
ALTER TABLE `services` AUTO_INCREMENT = 1;
INSERT INTO `services` (`id`, `service_name`, `unit_price`, `unit`, `is_active`, `created_at`) VALUES
(1, 'Tiền điện', '3500.00', 'kWh', 1, '2026-11-03 18:32:16'),
(2, 'Tiền nước', '80000.00', 'Người', 1, '2026-11-03 18:32:16'),
(3, 'Internet', '100000.00', 'Phòng', 1, '2026-11-03 18:32:16'),
(4, 'Thang máy', '150000.00', 'Phòng', 1, '2026-11-03 18:32:16'),
(5, 'Vệ sinh', '30000.00', 'Phòng', 1, '2026-11-03 18:32:16'),
(6, 'Gửi xe máy', '100000.00', 'Xe', 1, '2026-11-03 18:32:16'),
(7, 'Máy giặt chung', '50000.00', 'Người', 0, '2026-11-03 18:32:16'),
(8, 'Gửi ô tô', '1200000.00', 'Xe', 1, '2026-11-03 18:32:16'),
(9, 'Truyền hình cáp', '50000.00', 'Phòng', 1, '2026-11-03 18:32:16'),
(10, 'Phí quản lý vận hành', '200000.00', 'Phòng', 1, '2026-11-03 18:32:16'),
(11, 'Dọn phòng', '50000.00', 'Lượt', 1, '2026-11-03 18:32:16'),
(12, 'Phí thú cưng', '300000.00', 'Tháng', 0, '2026-11-03 18:32:16');

-- -------------------------------------------------------------
-- BẢNG: `contracts` (21 bản ghi)
-- -------------------------------------------------------------
DELETE FROM `contracts`;
ALTER TABLE `contracts` AUTO_INCREMENT = 1;
INSERT INTO `contracts` (`id`, `contract_code`, `contract_type`, `parent_contract_id`, `room_id`, `tenant_id`, `start_date`, `end_date`, `deposit_amount`, `deposit_date`, `deposit_paid_percent`, `deposit_status`, `monthly_rent`, `first_month_rent_paid`, `first_month_invoice_id`, `electricity_rate`, `water_rate`, `service_fee`, `signature_type`, `signature_status`, `tenant_signature`, `manager_signature`, `scan_file`, `signed_at`, `renewal_status`, `terminated_at`, `termination_reason`, `notes`, `status`, `created_at`) VALUES
(1, 'HD-2026-001', 'official_lease', NULL, 1, 5, '2026-01-01', '2026-12-31', '3000000.00', '2026-01-01', 50, 'held', '3000000.00', 0, NULL, '3500.00', '20000.00', '150000.00', 'online', 'pending', NULL, NULL, NULL, NULL, 'none', NULL, NULL, NULL, 'active', '2026-11-03 18:32:16'),
(2, 'HD-2026-002', 'official_lease', NULL, 2, 6, '2026-02-15', '2026-11-20', '3200000.00', '2026-02-15', 50, 'held', '3200000.00', 0, NULL, '3500.00', '20000.00', '150000.00', 'online', 'pending', NULL, NULL, NULL, NULL, 'none', NULL, NULL, NULL, 'active', '2026-11-03 18:32:16'),
(3, 'HD-2026-003', 'official_lease', NULL, 3, 7, '2026-03-01', '2027-03-01', '3200000.00', '2026-03-01', 100, 'held', '3200000.00', 0, NULL, '3500.00', '20000.00', '150000.00', 'online', 'pending', NULL, NULL, NULL, NULL, 'none', NULL, NULL, NULL, 'active', '2026-11-03 18:32:16'),
(4, 'HD-2026-004', 'official_lease', NULL, 4, 8, '2026-04-10', '2026-11-25', '3500000.00', '2026-04-10', 50, 'held', '3500000.00', 0, NULL, '3500.00', '20000.00', '150000.00', 'online', 'pending', NULL, NULL, NULL, NULL, 'none', NULL, NULL, NULL, 'active', '2026-11-03 18:32:16'),
(5, 'HD-2026-005', 'official_lease', NULL, 5, 9, '2026-05-01', '2027-05-01', '3500000.00', '2026-05-01', 100, 'refunded', '3500000.00', 0, NULL, '3500.00', '20000.00', '150000.00', 'online', 'pending', NULL, NULL, NULL, NULL, 'none', NULL, NULL, NULL, 'active', '2026-11-03 18:32:16'),
(6, 'HD-2026-006', 'official_lease', NULL, 6, 10, '2026-06-01', '2027-06-01', '3100000.00', '2026-06-01', 100, 'held', '3000000.00', 0, NULL, '3500.00', '20000.00', '150000.00', 'online', 'pending', NULL, NULL, NULL, NULL, 'none', NULL, NULL, NULL, 'active', '2026-11-03 18:32:16'),
(7, 'HD-2026-007', 'official_lease', NULL, 7, 11, '2026-07-01', '2026-12-10', '3300000.00', '2026-07-01', 50, 'held', '3000000.00', 0, NULL, '3500.00', '20000.00', '150000.00', 'online', 'pending', NULL, NULL, NULL, NULL, 'none', NULL, NULL, NULL, 'active', '2026-11-03 18:32:16'),
(8, 'HD-2026-008', 'official_lease', NULL, 9, 12, '2026-01-10', '2027-01-10', '4000000.00', '2026-01-10', 100, 'held', '4000000.00', 0, NULL, '3500.00', '20000.00', '150000.00', 'online', 'pending', NULL, NULL, NULL, NULL, 'none', NULL, NULL, NULL, 'active', '2026-11-03 18:32:16'),
(9, 'HD-2026-009', 'official_lease', NULL, 10, 13, '2026-02-01', '2027-02-01', '4000000.00', '2026-02-01', 100, 'held', '4000000.00', 0, NULL, '3500.00', '20000.00', '150000.00', 'online', 'pending', NULL, NULL, NULL, NULL, 'none', NULL, NULL, NULL, 'active', '2026-11-03 18:32:16'),
(10, 'HD-2026-010', 'official_lease', NULL, 11, 14, '2026-03-01', '2027-03-01', '4200000.00', '2026-03-01', 100, 'held', '4200000.00', 0, NULL, '3500.00', '20000.00', '150000.00', 'online', 'pending', NULL, NULL, NULL, NULL, 'none', NULL, NULL, NULL, 'active', '2026-11-03 18:32:16'),
(11, 'HD-2026-011', 'official_lease', NULL, 12, 15, '2026-04-01', '2026-11-15', '4500000.00', '2026-04-01', 50, 'held', '4500000.00', 0, NULL, '3500.00', '20000.00', '150000.00', 'online', 'pending', NULL, NULL, NULL, NULL, 'none', NULL, NULL, NULL, 'active', '2026-11-03 18:32:16'),
(12, 'HD-2026-012', 'official_lease', NULL, 13, 16, '2026-05-01', '2027-05-01', '4500000.00', '2026-05-01', 100, 'held', '4500000.00', 0, NULL, '3500.00', '20000.00', '150000.00', 'online', 'pending', NULL, NULL, NULL, NULL, 'none', NULL, NULL, NULL, 'active', '2026-11-03 18:32:16'),
(13, 'HD-2026-013', 'official_lease', NULL, 14, 17, '2026-06-01', '2027-06-01', '4000000.00', '2026-06-01', 100, 'held', '4000000.00', 0, NULL, '3500.00', '20000.00', '150000.00', 'online', 'pending', NULL, NULL, NULL, NULL, 'none', NULL, NULL, NULL, 'active', '2026-11-03 18:32:16'),
(14, 'HD-2026-014', 'official_lease', NULL, 15, 18, '2026-07-01', '2027-07-01', '4000000.00', '2026-07-01', 100, 'held', '4000000.00', 0, NULL, '3500.00', '20000.00', '150000.00', 'online', 'pending', NULL, NULL, NULL, NULL, 'none', NULL, NULL, NULL, 'active', '2026-11-03 18:32:16'),
(15, 'HD-2026-015', 'official_lease', NULL, 17, 19, '2026-01-15', '2027-01-15', '2700000.00', '2026-01-15', 100, 'refunded', '2500000.00', 0, NULL, '3500.00', '20000.00', '150000.00', 'online', 'pending', NULL, NULL, NULL, NULL, 'none', NULL, NULL, NULL, 'active', '2026-11-03 18:32:16'),
(16, 'HD-2026-016', 'official_lease', NULL, 18, 20, '2026-02-15', '2026-11-30', '2500000.00', '2026-02-15', 100, 'refunded', '2500000.00', 0, NULL, '3500.00', '20000.00', '150000.00', 'online', 'pending', NULL, NULL, NULL, NULL, 'none', NULL, NULL, NULL, 'active', '2026-11-03 18:32:16'),
(17, 'HD-2026-017', 'official_lease', NULL, 19, 21, '2026-03-15', '2027-03-15', '2800000.00', '2026-03-15', 100, 'held', '2800000.00', 0, NULL, '3500.00', '20000.00', '150000.00', 'online', 'pending', NULL, NULL, NULL, NULL, 'none', NULL, NULL, NULL, 'active', '2026-11-03 18:32:16'),
(18, 'HD-2026-018', 'official_lease', NULL, 20, 22, '2026-04-15', '2027-04-15', '3000000.00', '2026-04-15', 100, 'held', '3000000.00', 0, NULL, '3500.00', '20000.00', '150000.00', 'online', 'pending', NULL, NULL, NULL, NULL, 'none', NULL, NULL, NULL, 'active', '2026-11-03 18:32:16'),
(19, 'HD-2026-019', 'official_lease', NULL, 21, 23, '2026-05-15', '2027-05-15', '3000000.00', '2026-05-15', 100, 'held', '3000000.00', 0, NULL, '3500.00', '20000.00', '150000.00', 'online', 'pending', NULL, NULL, NULL, NULL, 'none', NULL, NULL, NULL, 'active', '2026-11-03 18:32:16'),
(20, 'HD-2026-020', 'official_lease', NULL, 22, 24, '2026-06-15', '2027-06-15', '2500000.00', '2026-06-15', 100, 'held', '2500000.00', 0, NULL, '3500.00', '20000.00', '150000.00', 'online', 'pending', NULL, NULL, NULL, NULL, 'none', NULL, NULL, NULL, 'active', '2026-11-03 18:32:16'),
(21, 'HD-2026-A203', 'official_lease', NULL, 8, 25, '2026-11-04', '2027-11-04', '3000000.00', '2026-11-04', 100, 'paid', '3000000.00', 0, NULL, '3500.00', '20000.00', '150000.00', 'online', 'pending', NULL, NULL, NULL, NULL, 'none', NULL, NULL, 'Hợp đồng thuê phòng A203. Ở cùng: Nguyễn Ánh Ngọc (0933762534)', 'active', '2026-11-04 11:50:43');

-- -------------------------------------------------------------
-- BẢNG: `utilities_readings` (41 bản ghi)
-- -------------------------------------------------------------
DELETE FROM `utilities_readings`;
ALTER TABLE `utilities_readings` AUTO_INCREMENT = 1;
INSERT INTO `utilities_readings` (`id`, `room_id`, `month_year`, `electric_old`, `electric_new`, `water_old`, `water_new`, `created_at`) VALUES
(1, 1, '10-2026', 100, 250, 10, 25, '2026-11-03 18:32:16'),
(2, 2, '10-2026', 150, 280, 15, 28, '2026-11-03 18:32:16'),
(3, 3, '10-2026', 200, 310, 20, 35, '2026-11-03 18:32:16'),
(4, 4, '10-2026', 220, 390, 25, 40, '2026-11-03 18:32:16'),
(5, 5, '10-2026', 180, 290, 18, 30, '2026-11-03 18:32:16'),
(6, 6, '10-2026', 110, 210, 12, 22, '2026-11-03 18:32:16'),
(7, 7, '10-2026', 130, 260, 14, 29, '2026-11-03 18:32:16'),
(8, 9, '10-2026', 300, 500, 30, 45, '2026-11-03 18:32:16'),
(9, 10, '10-2026', 320, 480, 32, 48, '2026-11-03 18:32:16'),
(10, 11, '10-2026', 280, 410, 28, 41, '2026-11-03 18:32:16'),
(11, 12, '10-2026', 350, 520, 35, 52, '2026-11-03 18:32:16'),
(12, 13, '10-2026', 310, 460, 31, 46, '2026-11-03 18:32:16'),
(13, 14, '10-2026', 290, 430, 29, 43, '2026-11-03 18:32:16'),
(14, 15, '10-2026', 270, 390, 27, 39, '2026-11-03 18:32:16'),
(15, 17, '10-2026', 50, 120, 5, 12, '2026-11-03 18:32:16'),
(16, 18, '10-2026', 60, 140, 6, 14, '2026-11-03 18:32:16'),
(17, 19, '10-2026', 80, 170, 8, 17, '2026-11-03 18:32:16'),
(18, 20, '10-2026', 90, 190, 9, 19, '2026-11-03 18:32:16'),
(19, 21, '10-2026', 70, 150, 7, 15, '2026-11-03 18:32:16'),
(20, 22, '10-2026', 55, 135, 5, 13, '2026-11-03 18:32:16'),
(21, 1, '11-2026', 250, 350, 25, 33, '2026-11-04 02:01:05'),
(22, 8, '11-2026', 205, 364, 24, 43, '2026-11-04 11:53:39'),
(25, 2, '11-2026', 280, 387, 28, 39, '2026-11-04 12:14:59'),
(26, 3, '11-2026', 310, 424, 35, 49, '2026-11-04 12:14:59'),
(27, 4, '11-2026', 390, 511, 40, 57, '2026-11-04 12:14:59'),
(28, 5, '11-2026', 290, 418, 30, 40, '2026-11-04 12:14:59'),
(29, 6, '11-2026', 210, 345, 22, 35, '2026-11-04 12:14:59'),
(30, 7, '11-2026', 260, 402, 29, 45, '2026-11-04 12:14:59'),
(39, 9, '11-2026', 500, 649, 45, 54, '2026-11-04 12:15:57'),
(40, 10, '11-2026', 480, 636, 48, 60, '2026-11-04 12:15:57'),
(41, 11, '11-2026', 410, 573, 41, 56, '2026-11-04 12:15:57'),
(42, 12, '11-2026', 520, 620, 52, 60, '2026-11-04 12:15:57'),
(43, 13, '11-2026', 460, 567, 46, 57, '2026-11-04 12:15:57'),
(44, 14, '11-2026', 430, 544, 43, 57, '2026-11-04 12:15:57'),
(45, 15, '11-2026', 390, 511, 39, 56, '2026-11-04 12:15:57'),
(46, 17, '11-2026', 120, 248, 12, 22, '2026-11-04 12:15:57'),
(47, 18, '11-2026', 140, 275, 14, 27, '2026-11-04 12:15:57'),
(48, 19, '11-2026', 170, 312, 17, 33, '2026-11-04 12:15:57'),
(49, 20, '11-2026', 190, 339, 19, 28, '2026-11-04 12:15:57'),
(50, 21, '11-2026', 150, 306, 15, 27, '2026-11-04 12:15:57'),
(51, 22, '11-2026', 135, 298, 13, 28, '2026-11-04 12:15:57');

-- -------------------------------------------------------------
-- BẢNG: `invoices` (41 bản ghi)
-- -------------------------------------------------------------
DELETE FROM `invoices`;
ALTER TABLE `invoices` AUTO_INCREMENT = 1;
INSERT INTO `invoices` (`id`, `room_id`, `contract_id`, `month_year`, `total_amount`, `status`, `created_at`, `paid_at`, `payment_method`, `payment_note`, `due_date`) VALUES
(1, 1, 1, '10-2026', '5025000.00', 'paid', '2026-11-03 18:32:16', NULL, NULL, NULL, NULL),
(2, 2, 2, '10-2026', '5255000.00', 'unpaid', '2026-11-03 18:32:16', NULL, NULL, NULL, NULL),
(3, 3, 3, '10-2026', '3585000.00', 'paid', '2026-11-03 18:32:16', NULL, NULL, NULL, NULL),
(4, 4, 4, '10-2026', '5845000.00', 'paid', '2026-11-03 18:32:16', NULL, NULL, NULL, NULL),
(5, 5, 5, '10-2026', '3885000.00', 'paid', '2026-11-03 18:32:16', NULL, NULL, NULL, NULL),
(6, 6, 6, '10-2026', '3350000.00', 'paid', '2026-11-03 18:32:16', NULL, NULL, NULL, NULL),
(7, 7, 7, '10-2026', '5105000.00', 'unpaid', '2026-11-03 18:32:16', NULL, NULL, NULL, NULL),
(8, 9, 8, '10-2026', '4700000.00', 'paid', '2026-11-03 18:32:16', NULL, NULL, NULL, NULL),
(9, 10, 9, '10-2026', '4560000.00', 'paid', '2026-11-03 18:32:16', NULL, NULL, NULL, NULL),
(10, 11, 10, '10-2026', '4655000.00', 'unpaid', '2026-11-03 18:32:16', NULL, NULL, NULL, NULL),
(11, 12, 11, '10-2026', '5095000.00', 'paid', '2026-11-03 18:32:16', NULL, NULL, NULL, NULL),
(12, 13, 12, '10-2026', '5025000.00', 'paid', '2026-11-03 18:32:16', NULL, NULL, NULL, NULL),
(13, 14, 13, '10-2026', '4490000.00', 'paid', '2026-11-03 18:32:16', NULL, NULL, NULL, NULL),
(14, 15, 14, '10-2026', '4420000.00', 'unpaid', '2026-11-03 18:32:16', NULL, NULL, NULL, NULL),
(15, 17, 15, '10-2026', '2745000.00', 'paid', '2026-11-03 18:32:16', NULL, NULL, NULL, NULL),
(16, 18, 16, '10-2026', '2780000.00', 'paid', '2026-11-03 18:32:16', NULL, NULL, NULL, NULL),
(17, 19, 17, '10-2026', '3115000.00', 'paid', '2026-11-03 18:32:16', NULL, NULL, NULL, NULL),
(18, 20, 18, '10-2026', '3350000.00', 'paid', '2026-11-03 18:32:16', NULL, NULL, NULL, NULL),
(19, 21, 19, '10-2026', '3280000.00', 'unpaid', '2026-11-03 18:32:16', NULL, NULL, NULL, NULL),
(20, 22, 20, '10-2026', '2780000.00', 'paid', '2026-11-03 18:32:16', NULL, NULL, NULL, NULL),
(21, 1, 1, '11-2026', '5490000.00', 'paid', '2026-11-04 02:01:05', '2026-11-02 10:30:00', 'transfer', '', '2026-11-10'),
(22, 8, 21, '11-2026', '5076500.00', 'paid', '2026-11-04 11:53:39', '2026-11-02 10:30:00', 'transfer', NULL, '2026-11-10'),
(24, 2, 2, '11-2026', '6054500.00', 'unpaid', '2026-11-04 12:14:59', NULL, NULL, NULL, '2026-11-01'),
(25, 3, 3, '11-2026', '4719000.00', 'paid', '2026-11-04 12:14:59', '2026-11-02 10:30:00', 'transfer', NULL, '2026-11-10'),
(26, 4, 4, '11-2026', '7033500.00', 'unpaid', '2026-11-04 12:14:59', NULL, NULL, NULL, '2026-11-01'),
(27, 5, 5, '11-2026', '4748000.00', 'paid', '2026-11-04 12:14:59', '2026-11-02 10:30:00', 'transfer', NULL, '2026-11-10'),
(28, 6, 6, '11-2026', '4512500.00', 'paid', '2026-11-04 12:14:59', '2026-11-02 10:30:00', 'transfer', NULL, '2026-11-10'),
(29, 7, 7, '11-2026', '6427000.00', 'paid', '2026-11-04 12:14:59', '2026-11-02 10:30:00', 'transfer', NULL, '2026-11-10'),
(38, 9, 8, '11-2026', '5241500.00', 'paid', '2026-11-04 12:15:57', '2026-11-02 10:30:00', 'transfer', NULL, '2026-11-10'),
(39, 10, 9, '11-2026', '5506000.00', 'paid', '2026-11-04 12:15:57', '2026-11-02 10:30:00', 'transfer', NULL, '2026-11-10'),
(40, 11, 10, '11-2026', '5970500.00', 'unpaid', '2026-11-04 12:15:57', NULL, NULL, NULL, '2026-11-10'),
(41, 12, 11, '11-2026', '7740000.00', 'paid', '2026-11-04 12:15:57', '2026-11-02 10:30:00', 'transfer', NULL, '2026-11-10'),
(42, 13, 12, '11-2026', '5754500.00', 'paid', '2026-11-04 12:15:57', '2026-11-02 10:30:00', 'transfer', NULL, '2026-11-10'),
(43, 14, 13, '11-2026', '5519000.00', 'paid', '2026-11-04 12:15:57', '2026-11-02 10:30:00', 'transfer', NULL, '2026-11-10'),
(44, 15, 14, '11-2026', '5783500.00', 'unpaid', '2026-11-04 12:15:57', NULL, NULL, NULL, '2026-11-10'),
(45, 17, 15, '11-2026', '3748000.00', 'paid', '2026-11-04 12:15:57', '2026-11-02 10:30:00', 'transfer', NULL, '2026-11-10'),
(46, 18, 16, '11-2026', '4012500.00', 'paid', '2026-11-04 12:15:57', '2026-11-02 10:30:00', 'transfer', NULL, '2026-11-10'),
(47, 19, 17, '11-2026', '4577000.00', 'paid', '2026-11-04 12:15:57', '2026-11-02 10:30:00', 'transfer', NULL, '2026-11-10'),
(48, 20, 18, '11-2026', '4241500.00', 'paid', '2026-11-04 12:15:57', '2026-11-02 10:30:00', 'transfer', NULL, '2026-11-10'),
(49, 21, 19, '11-2026', '4506000.00', 'unpaid', '2026-11-04 12:15:57', NULL, NULL, NULL, '2026-11-10'),
(50, 22, 20, '11-2026', '4270500.00', 'paid', '2026-11-04 12:15:57', '2026-11-02 10:30:00', 'transfer', NULL, '2026-11-10');

-- -------------------------------------------------------------
-- BẢNG: `invoice_details` (83 bản ghi)
-- -------------------------------------------------------------
DELETE FROM `invoice_details`;
ALTER TABLE `invoice_details` AUTO_INCREMENT = 1;
INSERT INTO `invoice_details` (`id`, `invoice_id`, `service_id`, `quantity`, `applied_price`, `subtotal`) VALUES
(1, 1, NULL, '1.00', '3000000.00', '3000000.00'),
(2, 1, 1, '150.00', '3500.00', '525000.00'),
(3, 2, NULL, '1.00', '3200000.00', '3200000.00'),
(4, 2, 1, '130.00', '3500.00', '455000.00'),
(5, 3, NULL, '1.00', '3200000.00', '3200000.00'),
(6, 3, 1, '110.00', '3500.00', '385000.00'),
(7, 4, NULL, '1.00', '3500000.00', '3500000.00'),
(8, 4, 1, '170.00', '3500.00', '595000.00'),
(9, 5, NULL, '1.00', '3500000.00', '3500000.00'),
(10, 5, 1, '110.00', '3500.00', '385000.00'),
(11, 6, NULL, '1.00', '3000000.00', '3000000.00'),
(12, 6, 1, '100.00', '3500.00', '350000.00'),
(13, 7, NULL, '1.00', '3000000.00', '3000000.00'),
(14, 7, 1, '130.00', '3500.00', '455000.00'),
(15, 8, NULL, '1.00', '4000000.00', '4000000.00'),
(16, 8, 1, '200.00', '3500.00', '700000.00'),
(17, 9, NULL, '1.00', '4000000.00', '4000000.00'),
(18, 9, 1, '160.00', '3500.00', '560000.00'),
(19, 10, NULL, '1.00', '4200000.00', '4200000.00'),
(20, 10, 1, '130.00', '3500.00', '455000.00'),
(68, 22, NULL, '1.00', '3000000.00', '3000000.00'),
(69, 22, 1, '159.00', '3500.00', '556500.00'),
(70, 22, 2, '19.00', '80000.00', '1520000.00'),
(71, 21, NULL, '1.00', '3000000.00', '3000000.00'),
(72, 21, 1, '100.00', '3500.00', '350000.00'),
(73, 21, 2, '8.00', '80000.00', '640000.00'),
(74, 24, NULL, '1.00', '3200000.00', '3200000.00'),
(75, 24, 1, '107.00', '3500.00', '374500.00'),
(76, 24, 2, '11.00', '80000.00', '880000.00'),
(77, 25, NULL, '1.00', '3200000.00', '3200000.00'),
(78, 25, 1, '114.00', '3500.00', '399000.00'),
(79, 25, 2, '14.00', '80000.00', '1120000.00'),
(80, 26, NULL, '1.00', '3500000.00', '3500000.00'),
(81, 26, 1, '121.00', '3500.00', '423500.00'),
(82, 26, 2, '17.00', '80000.00', '1360000.00'),
(83, 27, NULL, '1.00', '3500000.00', '3500000.00'),
(84, 27, 1, '128.00', '3500.00', '448000.00'),
(85, 27, 2, '10.00', '80000.00', '800000.00'),
(86, 28, NULL, '1.00', '3000000.00', '3000000.00'),
(87, 28, 1, '135.00', '3500.00', '472500.00'),
(88, 28, 2, '13.00', '80000.00', '1040000.00'),
(89, 29, NULL, '1.00', '3000000.00', '3000000.00'),
(90, 29, 1, '142.00', '3500.00', '497000.00'),
(91, 29, 2, '16.00', '80000.00', '1280000.00'),
(92, 38, NULL, '1.00', '4000000.00', '4000000.00'),
(93, 38, 1, '149.00', '3500.00', '521500.00'),
(94, 38, 2, '9.00', '80000.00', '720000.00'),
(95, 39, NULL, '1.00', '4000000.00', '4000000.00'),
(96, 39, 1, '156.00', '3500.00', '546000.00'),
(97, 39, 2, '12.00', '80000.00', '960000.00'),
(98, 40, NULL, '1.00', '4200000.00', '4200000.00'),
(99, 40, 1, '163.00', '3500.00', '570500.00'),
(100, 40, 2, '15.00', '80000.00', '1200000.00'),
(101, 41, NULL, '1.00', '4500000.00', '4500000.00'),
(102, 41, 1, '100.00', '3500.00', '350000.00'),
(103, 41, 2, '8.00', '80000.00', '640000.00'),
(104, 42, NULL, '1.00', '4500000.00', '4500000.00'),
(105, 42, 1, '107.00', '3500.00', '374500.00'),
(106, 42, 2, '11.00', '80000.00', '880000.00'),
(107, 43, NULL, '1.00', '4000000.00', '4000000.00'),
(108, 43, 1, '114.00', '3500.00', '399000.00'),
(109, 43, 2, '14.00', '80000.00', '1120000.00'),
(110, 44, NULL, '1.00', '4000000.00', '4000000.00'),
(111, 44, 1, '121.00', '3500.00', '423500.00'),
(112, 44, 2, '17.00', '80000.00', '1360000.00'),
(113, 45, NULL, '1.00', '2500000.00', '2500000.00'),
(114, 45, 1, '128.00', '3500.00', '448000.00'),
(115, 45, 2, '10.00', '80000.00', '800000.00'),
(116, 46, NULL, '1.00', '2500000.00', '2500000.00'),
(117, 46, 1, '135.00', '3500.00', '472500.00'),
(118, 46, 2, '13.00', '80000.00', '1040000.00'),
(119, 47, NULL, '1.00', '2800000.00', '2800000.00'),
(120, 47, 1, '142.00', '3500.00', '497000.00'),
(121, 47, 2, '16.00', '80000.00', '1280000.00'),
(122, 48, NULL, '1.00', '3000000.00', '3000000.00'),
(123, 48, 1, '149.00', '3500.00', '521500.00'),
(124, 48, 2, '9.00', '80000.00', '720000.00'),
(125, 49, NULL, '1.00', '3000000.00', '3000000.00'),
(126, 49, 1, '156.00', '3500.00', '546000.00'),
(127, 49, 2, '12.00', '80000.00', '960000.00'),
(128, 50, NULL, '1.00', '2500000.00', '2500000.00'),
(129, 50, 1, '163.00', '3500.00', '570500.00'),
(130, 50, 2, '15.00', '80000.00', '1200000.00');

-- -------------------------------------------------------------
-- BẢNG: `third_party_expenses` (10 bản ghi)
-- -------------------------------------------------------------
DELETE FROM `third_party_expenses`;
ALTER TABLE `third_party_expenses` AUTO_INCREMENT = 1;
INSERT INTO `third_party_expenses` (`id`, `month_year`, `expense_type`, `title`, `provider_name`, `quantity`, `unit`, `unit_price`, `amount`, `customer_amount`, `status`, `paid_at`, `notes`, `created_at`) VALUES
(1, '10-2026', 'electricity', 'Hóa đơn tiền điện tổng 3 tòa nhà (EVN)', 'Tổng Công ty Điện lực EVN', '2470.00', 'kWh', '2500.00', '6175000.00', '8645000.00', 'paid', '2026-10-28', 'Thanh toán điện kế tổng 3 pha theo đơn giá điện lực quy định', '2026-11-04 01:01:11'),
(2, '10-2026', 'water', 'Hóa đơn nước sạch sinh hoạt cụm nhà trọ', 'Công ty Cổ phần Nước sạch Đô thị', '247.00', 'm³', '14000.00', '3458000.00', '4940000.00', 'paid', '2026-10-28', 'Đồng hồ nước tổng cấp nguồn cho 3 tòa nhà', '2026-11-04 01:01:11'),
(3, '10-2026', 'internet', 'Cước Internet cáp quang Mesh 3 tòa', 'Tập đoàn Viettel Telecom', '3.00', 'gói/tòa', '250000.00', '750000.00', '1600000.00', 'paid', '2026-10-28', 'Gói cáp quang doanh nghiệp băng thông cao phủ sóng toàn khu', '2026-11-04 01:01:11'),
(4, '10-2026', 'garbage', 'Phí thu gom rác thải sinh hoạt hàng tháng', 'Hợp tác xã Môi trường Đô thị', '3.00', 'tòa', '150000.00', '450000.00', '600000.00', 'paid', '2026-10-28', 'Hợp đồng thu gom rác định kỳ 3 lần/tuần', '2026-11-04 01:01:11'),
(5, '10-2026', 'maintenance', 'Bảo dưỡng định kỳ hệ thống kỹ thuật & Thang máy', 'Công ty TNHH Kỹ thuật Thang máy Miền Bắc', '1.00', 'tháng', '950000.00', '950000.00', '0.00', 'paid', '2026-10-28', 'Bảo trì phòng cháy chữa cháy, camera hành lang và thang máy tòa nhà', '2026-11-04 01:01:11'),
(6, '11-2026', 'electricity', 'Hóa đơn tiền điện tổng 3 tòa nhà (EVN)', 'Tổng Công ty Điện lực EVN', '2650.00', 'kWh', '2500.00', '6625000.00', '9275000.00', 'paid', '2026-11-03', 'Thanh toán điện kế tổng 3 pha', '2026-11-04 12:14:59'),
(7, '11-2026', 'water', 'Hóa đơn nước sạch sinh hoạt', 'Công ty Nước sạch Đô thị', '260.00', 'm³', '14000.00', '3640000.00', '5200000.00', 'paid', '2026-11-03', 'Đồng hồ nước tổng cấp nguồn', '2026-11-04 12:14:59'),
(8, '11-2026', 'internet', 'Cước Internet cáp quang Viettel 3 tòa', 'Tập đoàn Viettel Telecom', '3.00', 'gói/tòa', '250000.00', '750000.00', '1600000.00', 'paid', '2026-11-01', 'Gói doanh nghiệp 500Mbps', '2026-11-04 12:14:59'),
(9, '11-2026', 'garbage', 'Phí thu gom rác sinh hoạt tháng 11', 'Hợp tác xã Môi trường', '3.00', 'tòa', '150000.00', '450000.00', '600000.00', 'paid', '2026-11-01', 'Thu gom rác 3 lần/tuần', '2026-11-04 12:14:59'),
(10, '11-2026', 'maintenance', 'Bảo trì kỹ thuật & Kiểm định PCCC', 'Công ty Kỹ thuật Thang máy', '1.00', 'tháng', '950000.00', '950000.00', '0.00', 'paid', '2026-11-02', 'Bảo dưỡng định kỳ thang máy và camera', '2026-11-04 12:14:59');

-- -------------------------------------------------------------
-- BẢNG: `tenant_requests` (0 bản ghi)
-- -------------------------------------------------------------
DELETE FROM `tenant_requests`;
ALTER TABLE `tenant_requests` AUTO_INCREMENT = 1;

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
-- Chỉ chèn vào các cột chuẩn (id, service_name, unit_price, unit)
-- ==============================================================================
INSERT INTO `services` (`id`, `service_name`, `unit_price`, `unit`) VALUES
(1, 'Tiền điện sinh hoạt', 3500, 'kWh'),
(2, 'Tiền nước sinh hoạt', 80000, 'Người'),
(3, 'Internet / Wifi tốc độ cao', 50000, 'Phòng'),
(4, 'Rác sinh hoạt & Môi trường', 30000, 'Phòng'),
(5, 'Vệ sinh hành lang & Khu chung', 20000, 'Người'),
(6, 'Phí dịch vụ thang máy', 30000, 'Người'),
(7, 'Gửi xe máy tầng hầm', 70000, 'Xe'),
(8, 'Giặt ủi sấy chung', 50000, 'Người');

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
(1, 1, 1, '10-2026', 3525000, 'paid'),
(2, 2, 2, '10-2026', 3655000, 'unpaid'), -- Nợ đọng
(3, 3, 3, '10-2026', 3585000, 'paid'),
(4, 4, 4, '10-2026', 4095000, 'paid'),
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

-- 8. CHI TIẾT HÓA ĐƠN (Sinh ra chi tiết Điện, Nước, Dịch vụ cho toàn bộ 20 hóa đơn)
-- Dùng INSERT IGNORE hoặc INSERT nhiều dòng. Dưới đây là dữ liệu chi tiết minh họa cho 5 hóa đơn đầu tiên (đại diện)
INSERT INTO `invoice_details` (`invoice_id`, `service_id`, `quantity`, `applied_price`, `subtotal`) VALUES
-- Hóa đơn 1 (P.A101)
(1, NULL, 1, 3200000, 3200000), (1, 1, 140, 3500, 490000), (1, 2, 1, 80000, 80000), (1, 3, 1, 50000, 50000), (1, 4, 1, 30000, 30000), (1, 5, 1, 20000, 20000), (1, 6, 1, 20000, 20000),
-- Hóa đơn 2 (P.A102)
(2, NULL, 1, 3200000, 3200000), (2, 1, 140, 3500, 490000), (2, 2, 1, 80000, 80000), (2, 3, 1, 50000, 50000), (2, 4, 1, 30000, 30000), (2, 5, 1, 20000, 20000), (2, 6, 1, 20000, 20000),
-- Hóa đơn 3 (P.A103)
(3, NULL, 1, 3400000, 3400000), (3, 1, 135, 3500, 472500), (3, 2, 2, 80000, 160000), (3, 3, 1, 50000, 50000), (3, 4, 1, 30000, 30000), (3, 5, 2, 20000, 40000), (3, 6, 1, 20000, 20000),
-- Hóa đơn 4 (P.A104)
(4, NULL, 1, 3500000, 3500000), (4, 1, 140, 3500, 490000), (4, 2, 2, 80000, 160000), (4, 3, 1, 50000, 50000), (4, 4, 1, 30000, 30000), (4, 5, 2, 20000, 40000), (4, 6, 1, 20000, 20000),
-- Hóa đơn 19 (P.B101)
(19, NULL, 1, 4200000, 4200000), (19, 1, 180, 3500, 630000), (19, 2, 1, 80000, 80000), (19, 3, 1, 50000, 50000), (19, 4, 1, 30000, 30000), (19, 5, 1, 20000, 20000), (19, 6, 1, 20000, 20000),
-- Hóa đơn 20 (P.B102)
(20, NULL, 1, 4200000, 4200000), (20, 1, 190, 3500, 665000), (20, 2, 1, 80000, 80000), (20, 3, 1, 50000, 50000), (20, 4, 1, 30000, 30000), (20, 5, 1, 20000, 20000), (20, 6, 1, 20000, 20000),
-- Hóa đơn 36 (P.C101)
(36, NULL, 1, 2700000, 2700000), (36, 1, 120, 3500, 420000), (36, 2, 1, 80000, 80000), (36, 3, 1, 50000, 50000), (36, 4, 1, 30000, 30000), (36, 5, 1, 20000, 20000), (36, 6, 1, 20000, 20000);

-- ==============================================================================
-- 9. DỮ LIỆU YÊU CẦU MẪU CỦA KHÁCH THUÊ (TENANT REQUESTS)
-- Để test tính năng khách gửi yêu cầu & Quản lý duyệt đổi thông tin
-- ==============================================================================
INSERT INTO `tenant_requests` (`id`, `tenant_id`, `request_type`, `field_target`, `old_value`, `new_value`, `reason`, `status`, `manager_note`, `created_at`) VALUES
(1, 5, 'profile_update', 'phone', '0901001101', '0988776655', 'Em vừa bị mất điện thoại và sim cũ, nhờ BQL cập nhật số mới để nhận thông báo.', 'pending', NULL, '2026-11-01 09:30:00'),
(2, 6, 'profile_update', 'email', 'huong.le@gmail.com', 'huong.le.ftu@gmail.com', 'Đổi sang email công việc để tiện nhận hóa đơn điện tử hàng tháng.', 'approved', 'Đã xác thực và cập nhật email mới cho bạn.', '2026-10-28 14:15:00'),
(3, 7, 'profile_update', 'co_tenant', 'Người ở cùng', 'Đăng ký thêm bạn cùng phòng: Phạm Minh Hùng (CCCD: 001201012345)', 'Bạn học cùng trường chuyển đến ở ghép.', 'pending', NULL, '2026-11-02 16:40:00'),
(4, 8, 'profile_update', 'full_name', 'Phạm Thu Trang', 'Phạm Quỳnh Trang', 'Sửa lại tên chính xác theo căn cước công dân mới làm lại.', 'rejected', 'Vui lòng mang CCCD gốc qua văn phòng quản lý để đối chiếu.', '2026-10-25 10:20:00');
