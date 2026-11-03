USE `quan_ly_nha_tro`;

-- 1. TÀI KHOẢN (Thêm nhiều khách để luân chuyển phòng)
INSERT INTO `users` (`username`, `password`, `full_name`, `phone`, `email`, `role`) VALUES
('admin01', 'hashed_pass_1', 'Quản Trị Viên', '0901234567', 'admin@nhatro.com', 'admin'),
('khach01', 'hashed_pass_3', 'Trần Thị Khách (P101)', '0987654321', 'khach1@gmail.com', 'tenant'),
('khach02', 'hashed_pass_4', 'Lê Văn Thuê (P201)', '0976543210', 'khach2@gmail.com', 'tenant'),
('khach03', 'hashed_pass_5', 'Phạm Khách Cũ (P102)', '0965432109', 'khach3@gmail.com', 'tenant'),
('khach04', 'hashed_pass_6', 'Võ Khách Mới (P301)', '0954321098', 'khach4@gmail.com', 'tenant'),
('khach05', 'hashed_pass_7', 'Đinh Khách Vip (P302)', '0943210987', 'khach5@gmail.com', 'tenant');

-- 2. PHÒNG TRỌ (Thêm tầng 3, đa dạng loại phòng)
INSERT INTO `rooms` (`room_number`, `price`, `status`, `description`) VALUES
('P101', 3000000, 'rented', 'Phòng tầng 1, tiêu chuẩn'),
('P102', 2500000, 'rented', 'Phòng tầng 1, giá rẻ'),
('P201', 3500000, 'rented', 'Phòng tầng 2, ban công'),
('P202', 3200000, 'available', 'Phòng tầng 2, trống sửa chữa'),
('P301', 4000000, 'rented', 'Phòng tầng 3, studio full nội thất'),
('P302', 4500000, 'rented', 'Phòng tầng 3, 2 phòng ngủ mini');

-- 3. DỊCH VỤ (Đã cập nhật đa dạng và thêm trạng thái Bật/Tắt)
-- Lưu ý: Nếu báo lỗi trùng lặp khi chạy, bạn hãy làm trống bảng services trước (TRUNCATE TABLE services;)
INSERT INTO `services` (`service_name`, `unit_price`, `unit`, `is_active`) VALUES
('Tiền điện', 3500, 'kWh', 1),
('Tiền nước', 80000, 'Người', 1),             -- Đã đổi thành tính theo Người
('Internet', 100000, 'Phòng', 1),
('Phí dịch vụ chung (Thang máy)', 150000, 'Phòng', 1),
('Vệ sinh', 30000, 'Phòng', 1),
('Gửi xe', 100000, 'Xe', 0),                  -- Trạng thái tắt (0) giống thiết kế
('Máy giặt', 50000, 'Người', 0);              -- Trạng thái tắt (0) giống thiết kế

-- 4. HỢP ĐỒNG (Tạo kịch bản khách thuê dài, khách vừa vào, khách đã đi)
INSERT INTO `contracts` (`room_id`, `tenant_id`, `start_date`, `end_date`, `deposit_amount`, `status`) VALUES
(1, 2, '2023-01-01', '2023-12-31', 3000000, 'active'),       -- P101 ở lâu dài
(3, 3, '2023-05-01', '2024-05-01', 3500000, 'active'),       -- P201 ở từ tháng 5
(2, 4, '2022-01-01', '2023-06-30', 2500000, 'terminated'),   -- Khách cũ P102 đi hồi tháng 6
(2, 5, '2023-08-01', '2024-08-01', 2500000, 'active'),       -- Khách mới P102 vào từ tháng 8
(5, 6, '2023-03-01', '2024-03-01', 4000000, 'active');       -- P301 ở lâu dài

-- 5. CHỈ SỐ ĐIỆN NƯỚC (Trải dài 6 tháng: T5, T6, T7, T8, T9, T10)
INSERT INTO `utilities_readings` (`room_id`, `month_year`, `electric_old`, `electric_new`, `water_old`, `water_new`) VALUES
-- Tháng 05
(1, '05-2023', 0, 50, 0, 5), (3, '05-2023', 0, 60, 0, 6), (2, '05-2023', 0, 40, 0, 4), (5, '05-2023', 0, 80, 0, 8),
-- Tháng 06 (Khách P102 chuẩn bị dọn đi)
(1, '06-2023', 50, 110, 5, 11), (3, '06-2023', 60, 130, 6, 13), (2, '06-2023', 40, 70, 4, 7), (5, '06-2023', 80, 170, 8, 17),
-- Tháng 07 (Mùa hè, điện tăng vọt, P102 trống không có dữ liệu)
(1, '07-2023', 110, 200, 11, 18), (3, '07-2023', 130, 240, 13, 21), (5, '07-2023', 170, 300, 17, 28),
-- Tháng 08 (Khách mới P102 vào, điện giảm nhẹ)
(1, '08-2023', 200, 270, 18, 24), (3, '08-2023', 240, 330, 21, 28), (2, '08-2023', 70, 100, 7, 10), (5, '08-2023', 300, 410, 28, 38),
-- Tháng 09 
(1, '09-2023', 270, 330, 24, 29), (3, '09-2023', 330, 410, 28, 34), (2, '09-2023', 100, 140, 10, 14), (5, '09-2023', 410, 510, 38, 47),
-- Tháng 10
(1, '10-2023', 330, 380, 29, 34), (3, '10-2023', 410, 480, 34, 40), (2, '10-2023', 140, 190, 14, 19), (5, '10-2023', 510, 600, 47, 55);

-- 6. HÓA ĐƠN & CÔNG NỢ (Tạo số liệu doanh thu cực lớn)
INSERT INTO `invoices` (`room_id`, `contract_id`, `month_year`, `total_amount`, `status`) VALUES
-- Tháng 05 (Đóng đủ)
(1, 1, '05-2023', 3425000, 'paid'), (3, 2, '05-2023', 3980000, 'paid'), (2, 3, '05-2023', 2870000, 'paid'), (5, 5, '05-2023', 4590000, 'paid'),
-- Tháng 06 (Khách P102 trả phòng, đóng tiền cuối)
(1, 1, '06-2023', 3480000, 'paid'), (3, 2, '06-2023', 4035000, 'paid'), (2, 3, '06-2023', 2815000, 'paid'), (5, 5, '06-2023', 4645000, 'paid'),
-- Tháng 07 (Tháng hè doanh thu cao)
(1, 1, '07-2023', 3605000, 'paid'), (3, 2, '07-2023', 4195000, 'paid'), (5, 5, '07-2023', 4825000, 'paid'),
-- Tháng 08 (Bắt đầu xuất hiện công nợ P301)
(1, 1, '08-2023', 3515000, 'paid'), (3, 2, '08-2023', 4105000, 'paid'), (2, 4, '08-2023', 2815000, 'paid'), (5, 5, '08-2023', 4735000, 'unpaid'),
-- Tháng 09 (P301 tiếp tục nợ, P201 cũng nợ)
(1, 1, '09-2023', 3460000, 'paid'), (3, 2, '09-2023', 4050000, 'unpaid'), (2, 4, '09-2023', 2870000, 'paid'), (5, 5, '09-2023', 4680000, 'unpaid'),
-- Tháng 10 (Chưa ai đóng tiền vì hóa đơn vừa xuất)
(1, 1, '10-2023', 3425000, 'unpaid'), (3, 2, '10-2023', 4015000, 'unpaid'), (2, 4, '10-2023', 2925000, 'unpaid'), (5, 5, '10-2023', 4625000, 'unpaid');