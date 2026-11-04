-- ==============================================================================
-- STAYMATE - CƠ SỞ DỮ LIỆU HỆ THỐNG QUẢN LÝ NHÀ TRỌ & HÓA ĐƠN ĐIỆN NƯỚC
-- File: database/schema.sql
-- Mô tả: Khởi tạo hoàn chỉnh CSDL và toàn bộ 11 bảng với Engine InnoDB,
--       khóa chính, khóa ngoại, ràng buộc toàn vẹn và chỉ mục tối ưu.
-- ==============================================================================

DROP DATABASE IF EXISTS `quan_ly_nha_tro`;
CREATE DATABASE `quan_ly_nha_tro` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `quan_ly_nha_tro`;

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------------------------
-- 1. BẢNG users (Tài khoản người dùng: Quản trị viên, Chủ trọ, Quản lý, Khách thuê)
-- ------------------------------------------------------------------------------
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `cccd` VARCHAR(20) NULL,
  `birthday` DATE NULL,
  `gender` VARCHAR(10) NULL DEFAULT 'Nam',
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `occupation` VARCHAR(100) NULL,
  `hometown` VARCHAR(255) NULL,
  `emergency_contact` VARCHAR(100) NULL,
  `status` VARCHAR(50) NULL DEFAULT 'Đang thuê',
  `roommate_name` VARCHAR(100) NULL,
  `roommate_phone` VARCHAR(20) NULL,
  `role` ENUM('admin', 'landlord', 'tenant', 'manager') NOT NULL DEFAULT 'tenant',
  `preferred_room_id` INT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_users_role` (`role`),
  INDEX `idx_users_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 2. BẢNG buildings (Khu nhà / Tòa nhà trọ)
-- ------------------------------------------------------------------------------
CREATE TABLE `buildings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `address` VARCHAR(255) NOT NULL,
  `manager_id` INT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_buildings_manager` (`manager_id`),
  FOREIGN KEY (`manager_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 3. BẢNG rooms (Danh sách phòng trọ)
-- ------------------------------------------------------------------------------
CREATE TABLE `rooms` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `building_id` INT NULL,
  `room_number` VARCHAR(20) NOT NULL UNIQUE,
  `price` DECIMAL(10,2) NOT NULL,
  `status` ENUM('available', 'rented', 'maintenance') DEFAULT 'available',
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_rooms_building` (`building_id`),
  INDEX `idx_rooms_status` (`status`),
  FOREIGN KEY (`building_id`) REFERENCES `buildings`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 4. BẢNG services (Danh mục đơn giá dịch vụ hệ thống: Điện, Nước, Rác, Wifi...)
-- ------------------------------------------------------------------------------
CREATE TABLE `services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `service_name` VARCHAR(100) NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `unit` VARCHAR(50) NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 5. BẢNG room_services (Dịch vụ riêng gắn theo từng phòng)
-- ------------------------------------------------------------------------------
CREATE TABLE `room_services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `room_id` INT NOT NULL,
  `service_name` VARCHAR(100) NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `unit` VARCHAR(50) NOT NULL DEFAULT 'Tháng',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_room_services_room` (`room_id`),
  FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 6. BẢNG contracts (Hợp đồng thuê phòng / Giữ cọc)
-- ------------------------------------------------------------------------------
CREATE TABLE `contracts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `contract_code` VARCHAR(30) UNIQUE,
  `contract_type` ENUM('deposit_hold', 'official_lease') DEFAULT 'official_lease',
  `parent_contract_id` INT NULL,
  `room_id` INT NOT NULL,
  `tenant_id` INT NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `deposit_amount` DECIMAL(10,2) NOT NULL,
  `deposit_date` DATE NULL,
  `deposit_paid_percent` INT DEFAULT 100,
  `deposit_status` ENUM('held', 'paid', 'refunded', 'forfeited') DEFAULT 'held',
  `monthly_rent` DECIMAL(10,2) DEFAULT 0,
  `first_month_rent_paid` TINYINT(1) DEFAULT 0,
  `first_month_invoice_id` INT NULL,
  `electricity_rate` DECIMAL(10,2) DEFAULT 3500.00,
  `water_rate` DECIMAL(10,2) DEFAULT 20000.00,
  `service_fee` DECIMAL(10,2) DEFAULT 150000.00,
  `signature_type` ENUM('online', 'paper') DEFAULT 'online',
  `signature_status` ENUM('pending', 'signed') DEFAULT 'pending',
  `tenant_signature` LONGTEXT NULL,
  `manager_signature` LONGTEXT NULL,
  `scan_file` VARCHAR(255) NULL,
  `signed_at` DATETIME NULL,
  `renewal_status` VARCHAR(20) DEFAULT 'none',
  `terminated_at` DATE NULL,
  `termination_reason` TEXT NULL,
  `notes` TEXT NULL,
  `status` ENUM('active', 'expired', 'terminated') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_contracts_room` (`room_id`),
  INDEX `idx_contracts_tenant` (`tenant_id`),
  INDEX `idx_contracts_status` (`status`),
  FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`tenant_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`parent_contract_id`) REFERENCES `contracts`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 7. BẢNG utilities_readings (Lịch sử chỉ số điện nước hàng tháng theo phòng)
-- ------------------------------------------------------------------------------
CREATE TABLE `utilities_readings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `room_id` INT NOT NULL,
  `month_year` VARCHAR(7) NOT NULL,
  `electric_old` INT NOT NULL,
  `electric_new` INT NOT NULL,
  `water_old` INT NOT NULL,
  `water_new` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_room_month` (`room_id`, `month_year`),
  INDEX `idx_readings_room` (`room_id`),
  FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 8. BẢNG invoices (Hóa đơn tiền phòng và sinh hoạt phí hàng tháng)
-- ------------------------------------------------------------------------------
CREATE TABLE `invoices` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `room_id` INT NOT NULL,
  `contract_id` INT NOT NULL,
  `month_year` VARCHAR(7) NOT NULL,
  `room_price` DECIMAL(10,2) DEFAULT 0.00,
  `electric_amount` DECIMAL(10,2) DEFAULT 0.00,
  `water_amount` DECIMAL(10,2) DEFAULT 0.00,
  `service_fee` DECIMAL(10,2) DEFAULT 0.00,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `status` ENUM('unpaid', 'paid') DEFAULT 'unpaid',
  `paid_at` DATETIME NULL,
  `payment_method` VARCHAR(20) NULL,
  `payment_note` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_invoice_month` (`room_id`, `month_year`),
  INDEX `idx_invoices_room` (`room_id`),
  INDEX `idx_invoices_contract` (`contract_id`),
  INDEX `idx_invoices_status` (`status`),
  FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`contract_id`) REFERENCES `contracts`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 9. BẢNG invoice_details (Chi tiết các hạng mục trong hóa đơn)
-- ------------------------------------------------------------------------------
CREATE TABLE `invoice_details` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `invoice_id` INT NOT NULL,
  `service_id` INT NULL,
  `quantity` DECIMAL(10,2) NOT NULL DEFAULT 1.00,
  `applied_price` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  INDEX `idx_invoice_details_invoice` (`invoice_id`),
  INDEX `idx_invoice_details_service` (`service_id`),
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 10. BẢNG tenant_requests (Yêu cầu chỉnh sửa thông tin từ khách thuê)
-- ------------------------------------------------------------------------------
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
  INDEX `idx_tenant_requests_tenant` (`tenant_id`),
  INDEX `idx_tenant_requests_status` (`status`),
  FOREIGN KEY (`tenant_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 11. BẢNG third_party_expenses (Chi phí bên ngoài / Nhà cung cấp dịch vụ)
-- ------------------------------------------------------------------------------
CREATE TABLE `third_party_expenses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `month_year` VARCHAR(7) NOT NULL,
  `expense_type` VARCHAR(30) NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `provider_name` VARCHAR(150) NOT NULL,
  `quantity` DECIMAL(10,2) DEFAULT 0.00,
  `unit` VARCHAR(30) DEFAULT '',
  `unit_price` DECIMAL(10,2) DEFAULT 0.00,
  `amount` DECIMAL(10,2) NOT NULL,
  `customer_amount` DECIMAL(10,2) DEFAULT 0.00,
  `status` ENUM('paid', 'pending') DEFAULT 'paid',
  `paid_at` DATE NULL,
  `notes` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_expenses_month` (`month_year`),
  INDEX `idx_expenses_type` (`expense_type`),
  INDEX `idx_expenses_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
