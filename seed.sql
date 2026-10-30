USE `quan_ly_nha_tro`;

-- Thêm tài khoản
INSERT INTO `users` (`username`, `password`, `full_name`, `phone`, `email`, `role`) VALUES
('admin01', 'hashed_pass_1', 'Quản Trị Viên', '0901234567', 'admin@nhatro.com', 'admin'),
('chutro01', 'hashed_pass_2', 'Nguyễn Văn Chủ', '0912345678', 'chu@nhatro.com', 'landlord'),
('khach01', 'hashed_pass_3', 'Trần Thị Khách', '0987654321', 'khach1@gmail.com', 'tenant'),
('khach02', 'hashed_pass_4', 'Lê Văn Thuê', '0976543210', 'khach2@gmail.com', 'tenant');

-- Thêm phòng trọ
INSERT INTO `rooms` (`room_number`, `price`, `status`, `description`) VALUES
('P101', 3000000, 'rented', 'Phòng tầng 1, có điều hòa'),
('P102', 2500000, 'available', 'Phòng tầng 1, không điều hòa'),
('P201', 3500000, 'rented', 'Phòng tầng 2, ban công rộng');

-- Thêm dịch vụ
INSERT INTO `services` (`service_name`, `unit_price`, `unit`) VALUES
('Điện', 3500, 'kWh'),
('Nước', 20000, 'Khối'),
('Rác', 50000, 'Tháng'),
('Internet', 100000, 'Tháng');

-- Thêm hợp đồng (P101 và P201)
INSERT INTO `contracts` (`room_id`, `tenant_id`, `start_date`, `end_date`, `deposit_amount`, `status`) VALUES
(1, 3, '2023-01-01', '2023-12-31', 3000000, 'active'),
(3, 4, '2023-05-01', '2024-05-01', 3500000, 'active');

-- Thêm chỉ số điện nước tháng 10/2023
INSERT INTO `utilities_readings` (`room_id`, `month_year`, `electric_old`, `electric_new`, `water_old`, `water_new`) VALUES
(1, '10-2023', 100, 150, 10, 15),
(3, '10-2023', 200, 280, 20, 28);

-- Thêm hóa đơn
INSERT INTO `invoices` (`room_id`, `contract_id`, `month_year`, `total_amount`, `status`) VALUES
(1, 1, '10-2023', 3425000, 'paid'),
(3, 2, '10-2023', 4090000, 'unpaid');

-- Thêm chi tiết hóa đơn cho P101
INSERT INTO `invoice_details` (`invoice_id`, `service_id`, `quantity`, `applied_price`, `subtotal`) VALUES
(1, NULL, 1, 3000000, 3000000), -- Tiền phòng
(1, 1, 50, 3500, 175000),       -- Tiền điện (150 - 100)
(1, 2, 5, 20000, 100000),       -- Tiền nước (15 - 10)
(1, 3, 1, 50000, 50000),        -- Tiền rác
(1, 4, 1, 100000, 100000);      -- Tiền internet