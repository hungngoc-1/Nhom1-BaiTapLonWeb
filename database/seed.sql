USE `quan_ly_nha_tro`;

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `users`;
TRUNCATE TABLE `buildings`;
TRUNCATE TABLE `services`;
TRUNCATE TABLE `rooms`;
TRUNCATE TABLE `contracts`;
TRUNCATE TABLE `utilities_readings`;
TRUNCATE TABLE `invoices`;
TRUNCATE TABLE `invoice_details`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. TÀI KHOẢN (1 Admin, 3 Quản lý và 20 Khách thuê thuần Việt)
INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `phone`, `email`, `role`) VALUES
(1, 'admin', 'hash', 'Quản Trị Viên Hệ Thống', '0900000000', 'admin@staymate.vn', 'admin'),
(2, 'quanly_a', 'hash', 'Nguyễn Văn Quân', '0911111111', 'quana@staymate.vn', 'manager'),
(3, 'quanly_b', 'hash', 'Trần Thị Bích', '0922222222', 'bichb@staymate.vn', 'manager'),
(4, 'quanly_c', 'hash', 'Lê Hoàng Nam', '0933333333', 'namc@staymate.vn', 'manager'),
(5, 'kh_01', 'hash', 'Nguyễn Tiến Đạt', '0901000001', 'dat@gmail.com', 'tenant'),
(6, 'kh_02', 'hash', 'Lê Thị Thu Hương', '0901000002', 'huong@gmail.com', 'tenant'),
(7, 'kh_03', 'hash', 'Trần Hoàng Long', '0901000003', 'long@gmail.com', 'tenant'),
(8, 'kh_04', 'hash', 'Phạm Thu Trang', '0901000004', 'trang@gmail.com', 'tenant'),
(9, 'kh_05', 'hash', 'Vũ Minh Tuấn', '0901000005', 'tuan@gmail.com', 'tenant'),
(10, 'kh_06', 'hash', 'Đặng Kim Thủy', '0901000006', 'thuy@gmail.com', 'tenant'),
(11, 'kh_07', 'hash', 'Bùi Xuân Bách', '0901000007', 'bach@gmail.com', 'tenant'),
(12, 'kh_08', 'hash', 'Hồ Quang Hiếu', '0901000008', 'hieu@gmail.com', 'tenant'),
(13, 'kh_09', 'hash', 'Ngô Phương Lan', '0901000009', 'lan@gmail.com', 'tenant'),
(14, 'kh_10', 'hash', 'Đinh Trọng Thủy', '0901000010', 'trongthuy@gmail.com', 'tenant'),
(15, 'kh_11', 'hash', 'Hoàng Thanh Tùng', '0901000011', 'tung@gmail.com', 'tenant'),
(16, 'kh_12', 'hash', 'Đặng Thùy Trâm', '0901000012', 'tram@gmail.com', 'tenant'),
(17, 'kh_13', 'hash', 'Lý Tự Trọng', '0901000013', 'trong@gmail.com', 'tenant'),
(18, 'kh_14', 'hash', 'Phan Châu Trinh', '0901000014', 'trinh@gmail.com', 'tenant'),
(19, 'kh_15', 'hash', 'Võ Thị Sáu', '0901000015', 'sau@gmail.com', 'tenant'),
(20, 'kh_16', 'hash', 'Trương Định', '0901000016', 'dinh@gmail.com', 'tenant'),
(21, 'kh_17', 'hash', 'Nguyễn Thái Học', '0901000017', 'hoc@gmail.com', 'tenant'),
(22, 'kh_18', 'hash', 'Kim Đồng', '0901000018', 'dong@gmail.com', 'tenant'),
(23, 'kh_19', 'hash', 'Lê Hồng Phong', '0901000019', 'phong@gmail.com', 'tenant'),
(24, 'kh_20', 'hash', 'Nguyễn Thị Minh Khai', '0901000020', 'khai@gmail.com', 'tenant');

-- 2. TÒA NHÀ (Đúng 3 tòa nhà)
INSERT INTO `buildings` (`id`, `name`, `address`, `manager_id`) VALUES
(1, 'Tòa A - StayMate Sunrise', 'Số 128 Cầu Giấy, Hà Nội', 2),
(2, 'Tòa B - StayMate Emerald', 'Số 45 Nguyễn Chí Thanh, Hà Nội', 3),
(3, 'Tòa C - StayMate Horizon', 'Số 88 Nguyễn Trãi, Hà Nội', 4);

-- 3. DỊCH VỤ (Giữ 12 dịch vụ phong phú để test Bill)
INSERT INTO `services` (`id`, `service_name`, `unit_price`, `unit`, `is_active`) VALUES
(1, 'Tiền điện', 3500, 'kWh', 1), (2, 'Tiền nước', 80000, 'Người', 1),
(3, 'Internet', 100000, 'Phòng', 1), (4, 'Thang máy', 150000, 'Phòng', 1),
(5, 'Vệ sinh', 30000, 'Phòng', 1), (6, 'Gửi xe máy', 100000, 'Xe', 1),
(7, 'Máy giặt chung', 50000, 'Người', 0), (8, 'Gửi ô tô', 1200000, 'Xe', 1),
(9, 'Truyền hình cáp', 50000, 'Phòng', 1), (10, 'Phí quản lý vận hành', 200000, 'Phòng', 1),
(11, 'Dọn phòng', 50000, 'Lượt', 1), (12, 'Phí thú cưng', 300000, 'Tháng', 0);

-- 4. PHÒNG TRỌ (Mô phỏng 60 phòng, insert 24 phòng để map với 20 khách)
INSERT INTO `rooms` (`id`, `building_id`, `room_number`, `price`, `status`) VALUES
(1, 1, 'A101', 3000000, 'rented'), (2, 1, 'A102', 3200000, 'rented'),
(3, 1, 'A103', 3200000, 'rented'), (4, 1, 'A104', 3500000, 'rented'),
(5, 1, 'A105', 3500000, 'rented'), (6, 1, 'A201', 3000000, 'rented'),
(7, 1, 'A202', 3000000, 'rented'), (8, 1, 'A203', 3000000, 'available'),
(9, 2, 'B101', 4000000, 'rented'), (10, 2, 'B102', 4000000, 'rented'),
(11, 2, 'B103', 4200000, 'rented'), (12, 2, 'B104', 4500000, 'rented'),
(13, 2, 'B105', 4500000, 'rented'), (14, 2, 'B201', 4000000, 'rented'),
(15, 2, 'B202', 4000000, 'rented'), (16, 2, 'B203', 4000000, 'available'),
(17, 3, 'C101', 2500000, 'rented'), (18, 3, 'C102', 2500000, 'rented'),
(19, 3, 'C103', 2800000, 'rented'), (20, 3, 'C104', 3000000, 'rented'),
(21, 3, 'C105', 3000000, 'rented'), (22, 3, 'C201', 2500000, 'rented'),
(23, 3, 'C202', 2500000, 'maintenance'), (24, 3, 'C203', 2500000, 'available');

-- 5. HỢP ĐỒNG (20 hợp đồng cho 20 khách, có gài các hợp đồng SẮP HẾT HẠN)
INSERT INTO `contracts` (`id`, `room_id`, `tenant_id`, `start_date`, `end_date`, `deposit_amount`, `status`) VALUES
(1, 1, 5, '2026-01-01', '2026-12-31', 3000000, 'active'),
(2, 2, 6, '2026-02-15', '2026-11-20', 3200000, 'active'), -- Sắp hết hạn (Để test Báo cáo BĐS)
(3, 3, 7, '2026-03-01', '2027-03-01', 3200000, 'active'),
(4, 4, 8, '2026-04-10', '2026-11-25', 3500000, 'active'), -- Sắp hết hạn
(5, 5, 9, '2026-05-01', '2027-05-01', 3500000, 'active'),
(6, 6, 10, '2026-06-01', '2027-06-01', 3000000, 'active'),
(7, 7, 11, '2026-07-01', '2026-12-10', 3000000, 'active'), -- Sắp hết hạn
(8, 9, 12, '2026-01-10', '2027-01-10', 4000000, 'active'),
(9, 10, 13, '2026-02-01', '2027-02-01', 4000000, 'active'),
(10, 11, 14, '2026-03-01', '2027-03-01', 4200000, 'active'),
(11, 12, 15, '2026-04-01', '2026-11-15', 4500000, 'active'), -- Sắp hết hạn
(12, 13, 16, '2026-05-01', '2027-05-01', 4500000, 'active'),
(13, 14, 17, '2026-06-01', '2027-06-01', 4000000, 'active'),
(14, 15, 18, '2026-07-01', '2027-07-01', 4000000, 'active'),
(15, 17, 19, '2026-01-15', '2027-01-15', 2500000, 'active'),
(16, 18, 20, '2026-02-15', '2026-11-30', 2500000, 'active'), -- Sắp hết hạn
(17, 19, 21, '2026-03-15', '2027-03-15', 2800000, 'active'),
(18, 20, 22, '2026-04-15', '2027-04-15', 3000000, 'active'),
(19, 21, 23, '2026-05-15', '2027-05-15', 3000000, 'active'),
(20, 22, 24, '2026-06-15', '2027-06-15', 2500000, 'active');

-- 6. CHỈ SỐ ĐIỆN NƯỚC (20 bản ghi cho kỳ tháng 10-2026)
INSERT INTO `utilities_readings` (`room_id`, `month_year`, `electric_old`, `electric_new`, `water_old`, `water_new`) VALUES
(1, '10-2026', 100, 250, 10, 25), (2, '10-2026', 150, 280, 15, 28),
(3, '10-2026', 200, 310, 20, 35), (4, '10-2026', 220, 390, 25, 40),
(5, '10-2026', 180, 290, 18, 30), (6, '10-2026', 110, 210, 12, 22),
(7, '10-2026', 130, 260, 14, 29), (9, '10-2026', 300, 500, 30, 45),
(10, '10-2026', 320, 480, 32, 48), (11, '10-2026', 280, 410, 28, 41),
(12, '10-2026', 350, 520, 35, 52), (13, '10-2026', 310, 460, 31, 46),
(14, '10-2026', 290, 430, 29, 43), (15, '10-2026', 270, 390, 27, 39),
(17, '10-2026', 50, 120, 5, 12), (18, '10-2026', 60, 140, 6, 14),
(19, '10-2026', 80, 170, 8, 17), (20, '10-2026', 90, 190, 9, 19),
(21, '10-2026', 70, 150, 7, 15), (22, '10-2026', 55, 135, 5, 13);

-- 7. HÓA ĐƠN & CÔNG NỢ (20 hóa đơn, gài 5 hóa đơn UNPAID để test Báo cáo Tài chính)
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
(1, NULL, 1, 3000000, 3000000), (1, 1, 150, 3500, 525000), 
(2, NULL, 1, 3200000, 3200000), (2, 1, 130, 3500, 455000),
(3, NULL, 1, 3200000, 3200000), (3, 1, 110, 3500, 385000),
(4, NULL, 1, 3500000, 3500000), (4, 1, 170, 3500, 595000),
(5, NULL, 1, 3500000, 3500000), (5, 1, 110, 3500, 385000),
(6, NULL, 1, 3000000, 3000000), (6, 1, 100, 3500, 350000),
(7, NULL, 1, 3000000, 3000000), (7, 1, 130, 3500, 455000),
(8, NULL, 1, 4000000, 4000000), (8, 1, 200, 3500, 700000),
(9, NULL, 1, 4000000, 4000000), (9, 1, 160, 3500, 560000),
(10, NULL, 1, 4200000, 4200000), (10, 1, 130, 3500, 455000);