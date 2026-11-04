DROP DATABASE IF EXISTS `quan_ly_nha_tro`;
CREATE DATABASE `quan_ly_nha_tro` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `quan_ly_nha_tro`;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `role` VARCHAR(30) NOT NULL DEFAULT 'tenant',
  `preferred_room_id` INT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE `buildings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `address` VARCHAR(255) NOT NULL,
  `manager_id` INT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE `rooms` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `building_id` INT NULL,
  `room_number` VARCHAR(20) NOT NULL UNIQUE,
  `price` DECIMAL(10,2) NOT NULL,
  `status` ENUM('available', 'rented', 'maintenance') DEFAULT 'available',
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE `room_services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `room_id` INT NOT NULL,
  `service_name` VARCHAR(100) NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `unit` VARCHAR(50) NOT NULL DEFAULT 'Tháng',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`room_id`),
  FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `service_name` VARCHAR(100) NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `unit` VARCHAR(50) NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE `contracts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `contract_code` VARCHAR(30) UNIQUE,
  `contract_type` ENUM('deposit_hold', 'official_lease') DEFAULT 'official_lease',
  `parent_contract_id` INT NULL,
  `room_id` INT NOT NULL,
  `tenant_id` INT NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `deposit_amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `deposit_date` DATE NULL,
  `deposit_paid_percent` INT DEFAULT 100, -- 50: Đã cọc 50%, 100: Hoàn thành cọc
  `deposit_status` ENUM('held', 'refunded', 'forfeited') DEFAULT 'held', -- held: Đang giữ cọc, refunded: Hoàn cọc, forfeited: Mất cọc
  `monthly_rent` DECIMAL(10,2) DEFAULT 0,
  `first_month_rent_paid` TINYINT(1) DEFAULT 0,
  `first_month_invoice_id` INT NULL,
  `electricity_rate` DECIMAL(10,2) DEFAULT 3500,
  `water_rate` DECIMAL(10,2) DEFAULT 20000,
  `service_fee` DECIMAL(10,2) DEFAULT 150000,
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
  FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB;

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
  INDEX (`status`),
  FOREIGN KEY (`tenant_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

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
  FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`)
) ENGINE=InnoDB;

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
  `due_date` DATE NULL,
  `paid_at` DATETIME NULL,
  `payment_method` VARCHAR(20) NULL,
  `payment_note` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_invoice_month` (`room_id`, `month_year`),
  FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`),
  FOREIGN KEY (`contract_id`) REFERENCES `contracts`(`id`)
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS `third_party_expenses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `month_year` VARCHAR(7) NOT NULL,
  `building_id` INT NULL DEFAULT NULL,
  `expense_type` VARCHAR(30) NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `provider_name` VARCHAR(150) NOT NULL,
  `quantity` DECIMAL(10,2) DEFAULT 0,
  `unit` VARCHAR(30) DEFAULT '',
  `unit_price` DECIMAL(10,2) DEFAULT 0,
  `amount` DECIMAL(10,2) NOT NULL,
  `customer_amount` DECIMAL(10,2) DEFAULT 0,
  `status` ENUM('paid', 'pending') DEFAULT 'paid',
  `paid_at` DATE NULL,
  `notes` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`month_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_name` VARCHAR(100) NOT NULL,
  `role` VARCHAR(50) NOT NULL,
  `building_id` VARCHAR(10) NULL DEFAULT 'all',
  `action_type` VARCHAR(50) NOT NULL DEFAULT 'info',
  `title` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (`building_id`),
  INDEX (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tenant_contact_leads` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_name` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `room_label` VARCHAR(100) NULL,
  `room_id` INT NULL,
  `visit_time` VARCHAR(100) NULL,
  `note` TEXT NULL,
  `status` VARCHAR(20) DEFAULT 'pending',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `contacted_at` DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
