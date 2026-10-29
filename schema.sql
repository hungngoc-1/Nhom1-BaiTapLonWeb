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
  `role` ENUM('admin', 'landlord', 'tenant') NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE `rooms` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `room_number` VARCHAR(20) NOT NULL UNIQUE,
  `price` DECIMAL(10,2) NOT NULL,
  `status` ENUM('available', 'rented') DEFAULT 'available',
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE `services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `service_name` VARCHAR(100) NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `unit` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE `contracts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `room_id` INT NOT NULL,
  `tenant_id` INT NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `deposit_amount` DECIMAL(10,2) NOT NULL,
  `status` ENUM('active', 'expired', 'terminated') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `users`(`id`)
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
  `total_amount` DECIMAL(10,2) NOT NULL,
  `status` ENUM('unpaid', 'paid') DEFAULT 'unpaid',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_invoice_month` (`room_id`, `month_year`),
  FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`),
  FOREIGN KEY (`contract_id`) REFERENCES `contracts`(`id`)
) ENGINE=InnoDB;

CREATE TABLE `invoice_details` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `invoice_id` INT NOT NULL,
  `service_id` INT NULL,
  `quantity` DECIMAL(10,2) NOT NULL DEFAULT 1,
  `applied_price` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;