-- MySQL dump 10.13  Distrib 8.4.7, for Win64 (x86_64)
--
-- Host: localhost    Database: quan_ly_nha_tro
-- ------------------------------------------------------
-- Server version	8.4.7

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `quan_ly_nha_tro`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `quan_ly_nha_tro` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `quan_ly_nha_tro`;

--
-- Table structure for table `buildings`
--

DROP TABLE IF EXISTS `buildings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `buildings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `manager_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `buildings`
--

LOCK TABLES `buildings` WRITE;
/*!40000 ALTER TABLE `buildings` DISABLE KEYS */;
INSERT INTO `buildings` VALUES (1,'Tòa A - Smart Stay Sunrise','Số 128 Cầu Giấy, Hà Nội',NULL,'2026-11-04 04:55:36'),(2,'Tòa B - StayMate Emerald','Số 45 Nguyễn Chí Thanh, Hà Nội',NULL,'2026-11-04 04:55:36'),(3,'Tòa C - StayMate Horizon','Số 88 Nguyễn Trãi, Hà Nội',NULL,'2026-11-04 04:55:36');
/*!40000 ALTER TABLE `buildings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contracts`
--

DROP TABLE IF EXISTS `contracts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contracts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `contract_code` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contract_type` enum('deposit_hold','official_lease') COLLATE utf8mb4_unicode_ci DEFAULT 'official_lease',
  `parent_contract_id` int DEFAULT NULL,
  `room_id` int NOT NULL,
  `tenant_id` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `deposit_amount` decimal(10,2) NOT NULL,
  `deposit_date` date DEFAULT NULL,
  `deposit_paid_percent` int DEFAULT '100',
  `deposit_status` enum('held','paid','refunded','forfeited') COLLATE utf8mb4_unicode_ci DEFAULT 'held',
  `monthly_rent` decimal(10,2) DEFAULT '0.00',
  `first_month_rent_paid` tinyint(1) DEFAULT '0',
  `first_month_invoice_id` int DEFAULT NULL,
  `electricity_rate` decimal(10,2) DEFAULT '3500.00',
  `water_rate` decimal(10,2) DEFAULT '20000.00',
  `service_fee` decimal(10,2) DEFAULT '150000.00',
  `signature_type` enum('online','paper') COLLATE utf8mb4_unicode_ci DEFAULT 'online',
  `signature_status` enum('pending','signed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `tenant_signature` longtext COLLATE utf8mb4_unicode_ci,
  `manager_signature` longtext COLLATE utf8mb4_unicode_ci,
  `scan_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signed_at` datetime DEFAULT NULL,
  `renewal_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'none',
  `terminated_at` date DEFAULT NULL,
  `termination_reason` text COLLATE utf8mb4_unicode_ci,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','expired','terminated') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contract_code` (`contract_code`),
  KEY `room_id` (`room_id`),
  KEY `tenant_id` (`tenant_id`),
  CONSTRAINT `contracts_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`),
  CONSTRAINT `contracts_ibfk_2` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contracts`
--

LOCK TABLES `contracts` WRITE;
/*!40000 ALTER TABLE `contracts` DISABLE KEYS */;
INSERT INTO `contracts` VALUES (1,'HD-2026-001','official_lease',NULL,1,2,'2023-01-01','2023-12-31',3000000.00,'2023-01-01',100,'held',3000000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-03 03:31:25'),(2,'HD-2026-002','official_lease',NULL,3,3,'2023-05-01','2024-05-01',3500000.00,'2023-05-01',100,'held',3500000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-03 03:31:25'),(3,'HD-2026-003','official_lease',NULL,2,4,'2022-01-01','2023-06-30',2500000.00,'2022-01-01',100,'held',2500000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'terminated','2026-11-03 03:31:25'),(4,'HD-2026-004','official_lease',NULL,2,5,'2023-08-01','2024-08-01',2500000.00,'2023-08-01',100,'held',2500000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-03 03:31:25'),(5,'HD-2026-005','official_lease',NULL,5,6,'2023-03-01','2024-03-01',4000000.00,'2023-03-01',100,'held',4000000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-03 03:31:25'),(6,'HD-2026-P202','official_lease',NULL,4,7,'2026-11-04','2027-11-04',3200000.00,'2026-11-04',100,'paid',3200000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,'Hợp đồng thuê phòng P202. Ở cùng: hà (5678909876)','active','2026-11-04 02:44:25');
/*!40000 ALTER TABLE `contracts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_details`
--

DROP TABLE IF EXISTS `invoice_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_id` int NOT NULL,
  `service_id` int DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT '1.00',
  `applied_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `service_id` (`service_id`),
  CONSTRAINT `invoice_details_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoice_details_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_details`
--

LOCK TABLES `invoice_details` WRITE;
/*!40000 ALTER TABLE `invoice_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoice_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `room_id` int NOT NULL,
  `contract_id` int NOT NULL,
  `month_year` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `room_price` decimal(10,2) DEFAULT '0.00',
  `electric_amount` decimal(10,2) DEFAULT '0.00',
  `water_amount` decimal(10,2) DEFAULT '0.00',
  `service_fee` decimal(10,2) DEFAULT '0.00',
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('unpaid','paid') COLLATE utf8mb4_unicode_ci DEFAULT 'unpaid',
  `paid_at` datetime DEFAULT NULL,
  `payment_method` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_invoice_month` (`room_id`,`month_year`),
  KEY `contract_id` (`contract_id`),
  CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`),
  CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
INSERT INTO `invoices` VALUES (1,1,1,'05-2023',0.00,0.00,0.00,0.00,3425000.00,'paid',NULL,NULL,NULL,'2026-11-03 03:31:25'),(2,3,2,'05-2023',0.00,0.00,0.00,0.00,3980000.00,'paid',NULL,NULL,NULL,'2026-11-03 03:31:25'),(3,2,3,'05-2023',0.00,0.00,0.00,0.00,2870000.00,'paid',NULL,NULL,NULL,'2026-11-03 03:31:25'),(4,5,5,'05-2023',0.00,0.00,0.00,0.00,4590000.00,'paid',NULL,NULL,NULL,'2026-11-03 03:31:25'),(5,1,1,'06-2023',0.00,0.00,0.00,0.00,3480000.00,'paid',NULL,NULL,NULL,'2026-11-03 03:31:25'),(6,3,2,'06-2023',0.00,0.00,0.00,0.00,4035000.00,'paid',NULL,NULL,NULL,'2026-11-03 03:31:25'),(7,2,3,'06-2023',0.00,0.00,0.00,0.00,2815000.00,'paid',NULL,NULL,NULL,'2026-11-03 03:31:25'),(8,5,5,'06-2023',0.00,0.00,0.00,0.00,4645000.00,'paid',NULL,NULL,NULL,'2026-11-03 03:31:25'),(9,1,1,'07-2023',0.00,0.00,0.00,0.00,3605000.00,'paid',NULL,NULL,NULL,'2026-11-03 03:31:25'),(10,3,2,'07-2023',0.00,0.00,0.00,0.00,4195000.00,'paid',NULL,NULL,NULL,'2026-11-03 03:31:25'),(11,5,5,'07-2023',0.00,0.00,0.00,0.00,4825000.00,'paid',NULL,NULL,NULL,'2026-11-03 03:31:25'),(12,1,1,'08-2023',0.00,0.00,0.00,0.00,3515000.00,'paid',NULL,NULL,NULL,'2026-11-03 03:31:25'),(13,3,2,'08-2023',0.00,0.00,0.00,0.00,4105000.00,'paid',NULL,NULL,NULL,'2026-11-03 03:31:25'),(14,2,4,'08-2023',0.00,0.00,0.00,0.00,2815000.00,'paid',NULL,NULL,NULL,'2026-11-03 03:31:25'),(15,5,5,'08-2023',0.00,0.00,0.00,0.00,4735000.00,'unpaid',NULL,NULL,NULL,'2026-11-03 03:31:25'),(16,1,1,'09-2023',0.00,0.00,0.00,0.00,3460000.00,'paid',NULL,NULL,NULL,'2026-11-03 03:31:25'),(17,3,2,'09-2023',0.00,0.00,0.00,0.00,4050000.00,'unpaid',NULL,NULL,NULL,'2026-11-03 03:31:25'),(18,2,4,'09-2023',0.00,0.00,0.00,0.00,2870000.00,'paid',NULL,NULL,NULL,'2026-11-03 03:31:25'),(19,5,5,'09-2023',0.00,0.00,0.00,0.00,4680000.00,'unpaid',NULL,NULL,NULL,'2026-11-03 03:31:25'),(20,1,1,'10-2023',0.00,0.00,0.00,0.00,3425000.00,'unpaid',NULL,NULL,NULL,'2026-11-03 03:31:25'),(21,3,2,'10-2023',0.00,0.00,0.00,0.00,4015000.00,'unpaid',NULL,NULL,NULL,'2026-11-03 03:31:25'),(22,2,4,'10-2023',0.00,0.00,0.00,0.00,2925000.00,'unpaid',NULL,NULL,NULL,'2026-11-03 03:31:25'),(23,5,5,'10-2023',0.00,0.00,0.00,0.00,4625000.00,'unpaid',NULL,NULL,NULL,'2026-11-03 03:31:25');
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `room_services`
--

DROP TABLE IF EXISTS `room_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `room_services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `room_id` int NOT NULL,
  `service_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Tháng',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `room_id` (`room_id`),
  CONSTRAINT `room_services_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `room_services`
--

LOCK TABLES `room_services` WRITE;
/*!40000 ALTER TABLE `room_services` DISABLE KEYS */;
/*!40000 ALTER TABLE `room_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rooms`
--

DROP TABLE IF EXISTS `rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rooms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `building_id` int DEFAULT NULL,
  `room_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` enum('available','rented','maintenance') COLLATE utf8mb4_unicode_ci DEFAULT 'available',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_number` (`room_number`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rooms`
--

LOCK TABLES `rooms` WRITE;
/*!40000 ALTER TABLE `rooms` DISABLE KEYS */;
INSERT INTO `rooms` VALUES (1,1,'P101',3000000.00,'rented','Phòng tầng 1, tiêu chuẩn','2026-11-03 03:31:25'),(2,1,'P102',2500000.00,'rented','Phòng tầng 1, giá rẻ','2026-11-03 03:31:25'),(3,1,'P201',3500000.00,'rented','Phòng tầng 2, ban công','2026-11-03 03:31:25'),(4,1,'P202',3200000.00,'rented','Phòng tầng 2, trống sửa chữa','2026-11-03 03:31:25'),(5,1,'P301',4000000.00,'rented','Phòng tầng 3, studio full nội thất','2026-11-03 03:31:25'),(6,1,'P302',4500000.00,'rented','Phòng tầng 3, 2 phòng ngủ mini','2026-11-03 03:31:25');
/*!40000 ALTER TABLE `rooms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `service_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
INSERT INTO `services` VALUES (1,'Điện',3500.00,'kWh',1,'2026-11-03 03:31:25'),(2,'Nước',20000.00,'Khối',1,'2026-11-03 03:31:25'),(3,'Rác',50000.00,'Tháng',1,'2026-11-03 03:31:25'),(4,'Internet',100000.00,'Tháng',1,'2026-11-03 03:31:25');
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenant_requests`
--

DROP TABLE IF EXISTS `tenant_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenant_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tenant_id` int NOT NULL,
  `request_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'profile_update',
  `field_target` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `old_value` text COLLATE utf8mb4_unicode_ci,
  `new_value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `manager_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `status` (`status`),
  CONSTRAINT `tenant_requests_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenant_requests`
--

LOCK TABLES `tenant_requests` WRITE;
/*!40000 ALTER TABLE `tenant_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `tenant_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `third_party_expenses`
--

DROP TABLE IF EXISTS `third_party_expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `third_party_expenses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `month_year` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expense_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` decimal(10,2) DEFAULT '0.00',
  `unit` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `unit_price` decimal(10,2) DEFAULT '0.00',
  `amount` decimal(10,2) NOT NULL,
  `customer_amount` decimal(10,2) DEFAULT '0.00',
  `status` enum('paid','pending') COLLATE utf8mb4_unicode_ci DEFAULT 'paid',
  `paid_at` date DEFAULT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `third_party_expenses`
--

LOCK TABLES `third_party_expenses` WRITE;
/*!40000 ALTER TABLE `third_party_expenses` DISABLE KEYS */;
/*!40000 ALTER TABLE `third_party_expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cccd` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `gender` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'Nam',
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `occupation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hometown` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Đang thuê',
  `roommate_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `roommate_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('admin','landlord','tenant','manager') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tenant',
  `preferred_room_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin01','hashed_pass_1','Quản Trị Viên','0901234567',NULL,NULL,'Nam','admin@nhatro.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'admin',NULL,'2026-11-03 03:31:25'),(2,'khach01','hashed_pass_3','Trần Thị Khách (P101)','0987654321',NULL,NULL,'Nam','khach1@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-03 03:31:25'),(3,'khach02','hashed_pass_4','Lê Văn Thuê (P201)','0976543210',NULL,NULL,'Nam','khach2@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-03 03:31:25'),(4,'khach03','hashed_pass_5','Phạm Khách Cũ (P102)','0965432109',NULL,NULL,'Nam','khach3@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-03 03:31:25'),(5,'khach04','hashed_pass_6','Võ Khách Mới (P301)','0954321098',NULL,NULL,'Nam','khach4@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-03 03:31:25'),(6,'khach05','hashed_pass_7','Đinh Khách Vip (P302)','0943210987',NULL,NULL,'Nam','khach5@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-03 03:31:25'),(7,'08976543213','$2y$10$iJlasX9yoSpGbKlLE65cGea8x/SH66SawiCKX03MasRgIi3D5c4mG','Trang','08976543213','01234567889','2001-06-15','Nữ','ngoctrang3426@gmail.com','','','','Đang thuê','hà','5678909876','tenant',4,'2026-11-04 02:44:25');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `utilities_readings`
--

DROP TABLE IF EXISTS `utilities_readings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `utilities_readings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `room_id` int NOT NULL,
  `month_year` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `electric_old` int NOT NULL,
  `electric_new` int NOT NULL,
  `water_old` int NOT NULL,
  `water_new` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_room_month` (`room_id`,`month_year`),
  CONSTRAINT `utilities_readings_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `utilities_readings`
--

LOCK TABLES `utilities_readings` WRITE;
/*!40000 ALTER TABLE `utilities_readings` DISABLE KEYS */;
INSERT INTO `utilities_readings` VALUES (1,1,'05-2023',0,50,0,5,'2026-11-03 03:31:25'),(2,3,'05-2023',0,60,0,6,'2026-11-03 03:31:25'),(3,2,'05-2023',0,40,0,4,'2026-11-03 03:31:25'),(4,5,'05-2023',0,80,0,8,'2026-11-03 03:31:25'),(5,1,'06-2023',50,110,5,11,'2026-11-03 03:31:25'),(6,3,'06-2023',60,130,6,13,'2026-11-03 03:31:25'),(7,2,'06-2023',40,70,4,7,'2026-11-03 03:31:25'),(8,5,'06-2023',80,170,8,17,'2026-11-03 03:31:25'),(9,1,'07-2023',110,200,11,18,'2026-11-03 03:31:25'),(10,3,'07-2023',130,240,13,21,'2026-11-03 03:31:25'),(11,5,'07-2023',170,300,17,28,'2026-11-03 03:31:25'),(12,1,'08-2023',200,270,18,24,'2026-11-03 03:31:25'),(13,3,'08-2023',240,330,21,28,'2026-11-03 03:31:25'),(14,2,'08-2023',70,100,7,10,'2026-11-03 03:31:25'),(15,5,'08-2023',300,410,28,38,'2026-11-03 03:31:25'),(16,1,'09-2023',270,330,24,29,'2026-11-03 03:31:25'),(17,3,'09-2023',330,410,28,34,'2026-11-03 03:31:25'),(18,2,'09-2023',100,140,10,14,'2026-11-03 03:31:25'),(19,5,'09-2023',410,510,38,47,'2026-11-03 03:31:25'),(20,1,'10-2023',330,380,29,34,'2026-11-03 03:31:25'),(21,3,'10-2023',410,480,34,40,'2026-11-03 03:31:25'),(22,2,'10-2023',140,190,14,19,'2026-11-03 03:31:25'),(23,5,'10-2023',510,600,47,55,'2026-11-03 03:31:25');
/*!40000 ALTER TABLE `utilities_readings` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-04 11:59:26
