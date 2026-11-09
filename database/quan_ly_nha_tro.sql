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
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `actor_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `actor_role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_title` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_type` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'info',
  `building_id` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,'Quản Trị Viên','Admin','Đã thanh lý hợp đồng HD-2026-A202 (Phòng A202 — Khách thuê: Trần Quốc Bảo) đúng hạn, hoàn trả 100% cọc.','info','BLD-A','2026-09-30 16:45:00'),(2,'Quản Trị Viên','Admin','Đã chốt chỉ số Điện Nước và lập hóa đơn Kỳ 09-2026 cho 9 phòng đang thuê thuộc Tòa A.','info','BLD-A','2026-10-01 09:00:00'),(3,'Quản Trị Viên','Admin','Đã xác nhận thu tiền thành công cho phòng A301 (Kỳ 09-2026) — Số tiền: 4.918.000 đ.','success','BLD-A','2026-10-05 10:15:00'),(4,'Quản Trị Viên','Admin','Đã lập Hợp đồng 1 (Đặt cọc giữ chỗ 5 ngày) HD-2026-A203 cho phòng A203 — Khách thuê: Ngô Thị Bích Ngọc (Cọc 50%: 2.100.000 đ).','info','BLD-A','2026-11-08 15:20:00'),(5,'Quản Trị Viên','Admin','Đã thêm phòng P401 vào hệ thống thành công!','success','BLD-A','2026-11-09 18:25:00'),(6,'Quản Trị Viên','Admin','Đã thêm khách thuê \'Hùng Văn Ngọc\' (0862040328) vào hệ thống.','success','BLD-A','2026-11-09 18:28:15'),(7,'Quản Trị Viên','Admin','Đã lập HĐ Thuê chính thức HD-2026-015 cho phòng P401 — Khách thuê: Hùng Văn Ngọc (Đã thu đủ 100% cọc 1.500.000 đ).','success','BLD-A','2026-11-09 18:30:40'),(8,'Quản Trị Viên','Admin','Đã tải lên bản Scan hợp đồng ký giấy HD-2026-015 (Phòng P401 — Khách thuê: Hùng Văn Ngọc).','success','BLD-A','2026-11-09 18:32:05'),(9,'Quản Trị Viên','Admin','Đã thu tiền thành công hóa đơn tháng đầu tiên của hợp đồng HD-2026-015 (Phòng P401 — Hùng Văn Ngọc): 1.500.000 đ.','success','BLD-A','2026-11-09 18:35:10');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `buildings`
--

LOCK TABLES `buildings` WRITE;
/*!40000 ALTER TABLE `buildings` DISABLE KEYS */;
INSERT INTO `buildings` VALUES (1,'Tòa A - StayMate Sunrise','Số 128 Cầu Giấy, Quận Cầu Giấy, TP. Hà Nội',2,'2026-11-09 11:23:08');
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
  `deposit_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `deposit_date` date DEFAULT NULL,
  `deposit_paid_percent` int DEFAULT '100',
  `deposit_status` enum('held','paid','refunded','forfeited') COLLATE utf8mb4_unicode_ci DEFAULT 'held',
  `monthly_rent` decimal(10,2) DEFAULT '0.00',
  `first_month_rent_paid` tinyint(1) DEFAULT '0',
  `first_month_invoice_id` int DEFAULT NULL,
  `first_month_paid_at` datetime DEFAULT NULL,
  `first_month_paid_note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `unpaid_notice_sent` tinyint(1) DEFAULT '0',
  `unpaid_notice_sent_at` datetime DEFAULT NULL,
  `termination_verification_case` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'none',
  `refund_confirmed_invoice` tinyint(1) DEFAULT '0',
  `unpaid_notice_resolved_at` datetime DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `status` enum('active','expired','terminated') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contract_code` (`contract_code`),
  KEY `idx_contracts_room` (`room_id`),
  KEY `idx_contracts_tenant` (`tenant_id`),
  CONSTRAINT `fk_contracts_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`),
  CONSTRAINT `fk_contracts_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contracts`
--

LOCK TABLES `contracts` WRITE;
/*!40000 ALTER TABLE `contracts` DISABLE KEYS */;
INSERT INTO `contracts` VALUES (1,'HD-2026-A101','official_lease',NULL,1,5,'2026-08-01','2027-02-01',3000000.00,'2026-08-01',100,'held',3000000.00,1,NULL,'2026-08-01 09:15:00','Đã thu tiền mặt khi nhận phòng',3500.00,25000.00,360000.00,'paper','signed',NULL,NULL,NULL,'2026-08-01 09:00:00','none',NULL,NULL,0,NULL,'none',0,NULL,'Hợp đồng chính thức 6 tháng phòng A101. Ở cùng: Trần Quốc Bảo (0971000111).','active','2026-08-01 02:00:00'),(2,'HD-2026-A102','official_lease',NULL,2,6,'2026-08-01','2027-02-01',3200000.00,'2026-08-01',100,'held',3200000.00,1,NULL,'2026-08-01 09:40:00','Khách chuyển khoản Vietcombank',3500.00,25000.00,360000.00,'paper','signed',NULL,NULL,NULL,'2026-08-01 09:30:00','none',NULL,NULL,0,NULL,'none',0,NULL,'Hợp đồng chính thức 6 tháng phòng A102. Ở cùng: Nguyễn Mai Chi (0971000112).','active','2026-08-01 02:30:00'),(3,'HD-2026-A104','official_lease',NULL,4,7,'2026-08-01','2027-02-01',3500000.00,'2026-08-01',100,'held',3500000.00,1,NULL,'2026-08-01 10:15:00','Đã thu đủ tiền phòng tháng đầu',3500.00,25000.00,360000.00,'paper','signed',NULL,NULL,NULL,'2026-08-01 10:00:00','none',NULL,NULL,0,NULL,'none',0,NULL,'Hợp đồng chính thức 6 tháng phòng Studio A104.','active','2026-08-01 03:00:00'),(4,'HD-2026-A105','official_lease',NULL,5,8,'2026-08-01','2027-02-01',3500000.00,'2026-08-01',100,'held',3500000.00,1,NULL,'2026-08-01 10:45:00','Đã thu tiền mặt',3500.00,25000.00,360000.00,'paper','signed',NULL,NULL,NULL,'2026-08-01 10:30:00','none',NULL,NULL,0,NULL,'none',0,NULL,'Hợp đồng chính thức 6 tháng phòng góc A105.','active','2026-08-01 03:30:00'),(5,'HD-2026-A201','official_lease',NULL,7,9,'2026-08-01','2027-02-01',3000000.00,'2026-08-01',100,'held',3000000.00,1,NULL,'2026-08-01 11:10:00','Khách chuyển khoản MB Bank',3500.00,25000.00,360000.00,'paper','signed',NULL,NULL,NULL,'2026-08-01 11:00:00','none',NULL,NULL,0,NULL,'none',0,NULL,'Hợp đồng chính thức 6 tháng phòng A201.','active','2026-08-01 04:00:00'),(6,'HD-2026-A202','official_lease',NULL,8,10,'2026-08-01','2027-02-01',3200000.00,'2026-08-01',100,'held',3200000.00,1,NULL,'2026-08-01 14:15:00','Đã thu đủ tháng đầu',3500.00,25000.00,360000.00,'paper','signed',NULL,NULL,NULL,'2026-08-01 14:00:00','none',NULL,NULL,0,NULL,'none',0,NULL,'Hợp đồng chính thức 6 tháng phòng A202.','active','2026-08-01 07:00:00'),(7,'HD-2026-A204','official_lease',NULL,10,11,'2026-08-01','2027-02-01',3500000.00,'2026-08-01',100,'held',3500000.00,1,NULL,'2026-08-01 15:10:00','Đã thu chuyển khoản',3500.00,25000.00,360000.00,'paper','signed',NULL,NULL,NULL,'2026-08-01 15:00:00','none',NULL,NULL,0,NULL,'none',0,NULL,'Hợp đồng chính thức 6 tháng phòng VIP A204.','active','2026-08-01 08:00:00'),(8,'HD-2026-A301','official_lease',NULL,12,12,'2026-05-20','2026-11-21',3200000.00,'2026-05-20',100,'held',3200000.00,1,NULL,'2026-05-20 09:15:00','Đã thu tiền mặt',3500.00,25000.00,360000.00,'paper','signed',NULL,NULL,NULL,'2026-05-20 09:00:00','none',NULL,NULL,0,NULL,'none',0,NULL,'Hợp đồng sắp đến hạn gia hạn (còn 12 ngày).','active','2026-05-20 02:00:00'),(9,'HD-2026-A302','official_lease',NULL,13,13,'2026-08-01','2027-02-01',3000000.00,'2026-08-01',100,'held',3000000.00,1,NULL,'2026-08-01 16:15:00','Đã thu tiền mặt',3500.00,25000.00,360000.00,'paper','signed',NULL,NULL,NULL,'2026-08-01 16:00:00','none',NULL,NULL,0,NULL,'none',0,NULL,'Hợp đồng chính thức 6 tháng phòng A302.','active','2026-08-01 09:00:00'),(10,'HD-2026-A303','official_lease',NULL,14,14,'2026-11-01','2027-05-01',3500000.00,'2026-11-01',100,'held',3500000.00,0,NULL,NULL,NULL,3500.00,25000.00,360000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,0,NULL,'none',0,NULL,'Khách đã qua 5 ngày đầu nhưng chưa đóng tiền phòng tháng đầu (cần gửi xác nhận sang trang Hợp đồng).','active','2026-11-01 02:00:00'),(11,'HD-HOLD-A304','deposit_hold',NULL,15,15,'2026-11-08','2026-11-13',1500000.00,'2026-11-08',50,'held',3000000.00,0,NULL,NULL,NULL,3500.00,25000.00,360000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,0,NULL,'none',0,NULL,'[HĐ 1 - Đặt cọc giữ chỗ 50% tiền cọc (1.500.000đ). Chờ ký tiếp HĐ 2 chính thức]','active','2026-11-08 03:00:00'),(12,'HD-TERM-A103','official_lease',NULL,3,16,'2026-02-01','2026-08-01',3200000.00,'2026-02-01',100,'refunded',3200000.00,1,NULL,'2026-02-01 08:45:00','Đã thu đủ',3500.00,25000.00,360000.00,'paper','signed',NULL,NULL,NULL,'2026-02-01 08:30:00','none','2026-08-01','Hết thời hạn thuê 6 tháng, khách không gia hạn và bàn giao phòng tốt - Đã hoàn 100% tiền cọc (3.200.000đ).',1,'2026-08-01 09:00:00','expired_no_renew',0,'2026-08-01 10:00:00','Biên bản thanh lý tốt, không hư hại tài sản.','terminated','2026-02-01 01:30:00'),(13,'HD-TERM-A203','official_lease',NULL,9,17,'2026-04-01','2026-10-01',3000000.00,'2026-04-01',100,'forfeited',3000000.00,1,NULL,'2026-04-01 09:15:00','Đã thu đủ',3500.00,25000.00,360000.00,'paper','signed',NULL,NULL,NULL,'2026-04-01 09:00:00','none','2026-07-25','Khách tự ý trả phòng trước hạn hợp đồng (01/10/2026) - Không được hoàn tiền cọc (Mất cọc 3.000.000đ).',1,'2026-07-25 14:00:00','early_move_out',1,'2026-07-25 15:00:00','Vi phạm thời hạn cam kết thuê 6 tháng.','terminated','2026-04-01 02:00:00'),(14,'HD-HOLD-A205','deposit_hold',NULL,11,18,'2026-09-10','2026-09-15',1600000.00,'2026-09-10',50,'forfeited',3200000.00,0,NULL,NULL,NULL,3500.00,25000.00,360000.00,'paper','pending',NULL,NULL,NULL,NULL,'none','2026-09-16','Quá hạn 5 ngày đặt cọc giữ chỗ mà khách không đến ký Hợp đồng chính thức - Tự động hủy.',0,NULL,'none',0,NULL,'Khách báo bận công tác xa không nhận phòng.','expired','2026-09-10 04:00:00'),(15,'HD-2026-015','official_lease',NULL,16,22,'2026-11-09','2027-05-09',3500000.00,'2026-11-09',100,'held',3500000.00,0,NULL,NULL,NULL,3500.00,20000.00,210000.00,'paper','signed',NULL,NULL,'public/uploads/contracts/scan_HD-2026-015_1794223913.png','2026-11-09 18:31:53','none',NULL,NULL,0,NULL,'none',0,NULL,'','active','2026-11-09 11:31:42');
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
  KEY `idx_invoice_details_invoice` (`invoice_id`),
  KEY `idx_invoice_details_service` (`service_id`),
  CONSTRAINT `fk_invoice_details_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_invoice_details_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=161 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_details`
--

LOCK TABLES `invoice_details` WRITE;
/*!40000 ALTER TABLE `invoice_details` DISABLE KEYS */;
INSERT INTO `invoice_details` VALUES (1,1,NULL,1.00,3000000.00,3000000.00),(2,1,1,75.00,3500.00,262500.00),(3,1,2,5.00,25000.00,125000.00),(4,1,3,1.00,100000.00,100000.00),(5,1,4,1.00,80000.00,80000.00),(6,1,5,1.00,30000.00,30000.00),(7,1,6,1.00,100000.00,100000.00),(8,1,7,1.00,50000.00,50000.00),(9,2,NULL,1.00,3200000.00,3200000.00),(10,2,1,80.00,3500.00,280000.00),(11,2,2,5.00,25000.00,125000.00),(12,2,3,1.00,100000.00,100000.00),(13,2,4,1.00,80000.00,80000.00),(14,2,5,1.00,30000.00,30000.00),(15,2,6,1.00,100000.00,100000.00),(16,2,7,1.00,50000.00,50000.00),(17,3,NULL,1.00,3500000.00,3500000.00),(18,3,1,95.00,3500.00,332500.00),(19,3,2,7.00,25000.00,175000.00),(20,3,3,1.00,100000.00,100000.00),(21,3,4,1.00,80000.00,80000.00),(22,3,5,1.00,30000.00,30000.00),(23,3,6,1.00,100000.00,100000.00),(24,3,7,1.00,50000.00,50000.00),(25,4,NULL,1.00,3500000.00,3500000.00),(26,4,1,80.00,3500.00,280000.00),(27,4,2,5.00,25000.00,125000.00),(28,4,3,1.00,100000.00,100000.00),(29,4,4,1.00,80000.00,80000.00),(30,4,5,1.00,30000.00,30000.00),(31,4,6,1.00,100000.00,100000.00),(32,4,7,1.00,50000.00,50000.00),(33,5,NULL,1.00,3000000.00,3000000.00),(34,5,1,70.00,3500.00,245000.00),(35,5,2,5.00,25000.00,125000.00),(36,5,3,1.00,100000.00,100000.00),(37,5,4,1.00,80000.00,80000.00),(38,5,5,1.00,30000.00,30000.00),(39,5,6,1.00,100000.00,100000.00),(40,5,7,1.00,50000.00,50000.00),(41,6,NULL,1.00,3200000.00,3200000.00),(42,6,1,75.00,3500.00,262500.00),(43,6,2,5.00,25000.00,125000.00),(44,6,3,1.00,100000.00,100000.00),(45,6,4,1.00,80000.00,80000.00),(46,6,5,1.00,30000.00,30000.00),(47,6,6,1.00,100000.00,100000.00),(48,6,7,1.00,50000.00,50000.00),(49,7,NULL,1.00,3500000.00,3500000.00),(50,7,1,100.00,3500.00,350000.00),(51,7,2,7.00,25000.00,175000.00),(52,7,3,1.00,100000.00,100000.00),(53,7,4,1.00,80000.00,80000.00),(54,7,5,1.00,30000.00,30000.00),(55,7,6,1.00,100000.00,100000.00),(56,7,7,1.00,50000.00,50000.00),(57,8,NULL,1.00,3200000.00,3200000.00),(58,8,1,70.00,3500.00,245000.00),(59,8,2,5.00,25000.00,125000.00),(60,8,3,1.00,100000.00,100000.00),(61,8,4,1.00,80000.00,80000.00),(62,8,5,1.00,30000.00,30000.00),(63,8,6,1.00,100000.00,100000.00),(64,8,7,1.00,50000.00,50000.00),(65,9,NULL,1.00,3000000.00,3000000.00),(66,9,1,68.00,3500.00,238000.00),(67,9,2,4.00,25000.00,100000.00),(68,9,3,1.00,100000.00,100000.00),(69,9,4,1.00,80000.00,80000.00),(70,9,5,1.00,30000.00,30000.00),(71,9,6,1.00,100000.00,100000.00),(72,9,7,1.00,50000.00,50000.00),(73,10,NULL,1.00,3000000.00,3000000.00),(74,10,1,80.00,3500.00,280000.00),(75,10,2,6.00,25000.00,150000.00),(76,10,3,1.00,100000.00,100000.00),(77,10,4,1.00,80000.00,80000.00),(78,10,5,1.00,30000.00,30000.00),(79,10,6,1.00,100000.00,100000.00),(80,10,7,1.00,50000.00,50000.00),(81,11,NULL,1.00,3200000.00,3200000.00),(82,11,1,88.00,3500.00,308000.00),(83,11,2,6.00,25000.00,150000.00),(84,11,3,1.00,100000.00,100000.00),(85,11,4,1.00,80000.00,80000.00),(86,11,5,1.00,30000.00,30000.00),(87,11,6,1.00,100000.00,100000.00),(88,11,7,1.00,50000.00,50000.00),(89,12,NULL,1.00,3500000.00,3500000.00),(90,12,1,95.00,3500.00,332500.00),(91,12,2,7.00,25000.00,175000.00),(92,12,3,1.00,100000.00,100000.00),(93,12,4,1.00,80000.00,80000.00),(94,12,5,1.00,30000.00,30000.00),(95,12,6,1.00,100000.00,100000.00),(96,12,7,1.00,50000.00,50000.00),(97,13,NULL,1.00,3500000.00,3500000.00),(98,13,1,82.00,3500.00,287000.00),(99,13,2,5.00,25000.00,125000.00),(100,13,3,1.00,100000.00,100000.00),(101,13,4,1.00,80000.00,80000.00),(102,13,5,1.00,30000.00,30000.00),(103,13,6,1.00,100000.00,100000.00),(104,13,7,1.00,50000.00,50000.00),(105,14,NULL,1.00,3000000.00,3000000.00),(106,14,1,74.00,3500.00,259000.00),(107,14,2,5.00,25000.00,125000.00),(108,14,3,1.00,100000.00,100000.00),(109,14,4,1.00,80000.00,80000.00),(110,14,5,1.00,30000.00,30000.00),(111,14,6,1.00,100000.00,100000.00),(112,14,7,1.00,50000.00,50000.00),(113,15,NULL,1.00,3200000.00,3200000.00),(114,15,1,80.00,3500.00,280000.00),(115,15,2,6.00,25000.00,150000.00),(116,15,3,1.00,100000.00,100000.00),(117,15,4,1.00,80000.00,80000.00),(118,15,5,1.00,30000.00,30000.00),(119,15,6,1.00,100000.00,100000.00),(120,15,7,1.00,50000.00,50000.00),(121,16,NULL,1.00,3500000.00,3500000.00),(122,16,1,105.00,3500.00,367500.00),(123,16,2,7.00,25000.00,175000.00),(124,16,3,1.00,100000.00,100000.00),(125,16,4,1.00,80000.00,80000.00),(126,16,5,1.00,30000.00,30000.00),(127,16,6,1.00,100000.00,100000.00),(128,16,7,1.00,50000.00,50000.00),(129,17,NULL,1.00,3200000.00,3200000.00),(130,17,1,76.00,3500.00,266000.00),(131,17,2,5.00,25000.00,125000.00),(132,17,3,1.00,100000.00,100000.00),(133,17,4,1.00,80000.00,80000.00),(134,17,5,1.00,30000.00,30000.00),(135,17,6,1.00,100000.00,100000.00),(136,17,7,1.00,50000.00,50000.00),(137,18,NULL,1.00,3000000.00,3000000.00),(138,18,1,74.00,3500.00,259000.00),(139,18,2,5.00,25000.00,125000.00),(140,18,3,1.00,100000.00,100000.00),(141,18,4,1.00,80000.00,80000.00),(142,18,5,1.00,30000.00,30000.00),(143,18,6,1.00,100000.00,100000.00),(144,18,7,1.00,50000.00,50000.00);
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
  `due_date` date DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `payment_method` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_invoice_month` (`room_id`,`month_year`),
  KEY `idx_invoices_contract` (`contract_id`),
  CONSTRAINT `fk_invoices_contract` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`),
  CONSTRAINT `fk_invoices_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
INSERT INTO `invoices` VALUES (1,1,1,'08-2026',3000000.00,262500.00,125000.00,360000.00,3747500.00,'paid','2026-09-10','2026-09-05 10:30:00','transfer','Đã thanh toán đầy đủ hóa đơn kỳ 08-2026','2026-08-28 02:30:00'),(2,2,2,'08-2026',3200000.00,280000.00,125000.00,360000.00,3965000.00,'paid','2026-09-10','2026-09-05 10:30:00','transfer','Đã thanh toán đầy đủ hóa đơn kỳ 08-2026','2026-08-28 02:30:00'),(3,4,3,'08-2026',3500000.00,332500.00,175000.00,360000.00,4367500.00,'paid','2026-09-10','2026-09-05 10:30:00','transfer','Đã thanh toán đầy đủ hóa đơn kỳ 08-2026','2026-08-28 02:30:00'),(4,5,4,'08-2026',3500000.00,280000.00,125000.00,360000.00,4265000.00,'paid','2026-09-10','2026-09-05 10:30:00','transfer','Đã thanh toán đầy đủ hóa đơn kỳ 08-2026','2026-08-28 02:30:00'),(5,7,5,'08-2026',3000000.00,245000.00,125000.00,360000.00,3730000.00,'paid','2026-09-10','2026-09-05 10:30:00','transfer','Đã thanh toán đầy đủ hóa đơn kỳ 08-2026','2026-08-28 02:30:00'),(6,8,6,'08-2026',3200000.00,262500.00,125000.00,360000.00,3947500.00,'paid','2026-09-10','2026-09-05 10:30:00','transfer','Đã thanh toán đầy đủ hóa đơn kỳ 08-2026','2026-08-28 02:30:00'),(7,10,7,'08-2026',3500000.00,350000.00,175000.00,360000.00,4385000.00,'paid','2026-09-10','2026-09-05 10:30:00','transfer','Đã thanh toán đầy đủ hóa đơn kỳ 08-2026','2026-08-28 02:30:00'),(8,12,8,'08-2026',3200000.00,245000.00,125000.00,360000.00,3930000.00,'paid','2026-09-10','2026-09-05 10:30:00','transfer','Đã thanh toán đầy đủ hóa đơn kỳ 08-2026','2026-08-28 02:30:00'),(9,13,9,'08-2026',3000000.00,238000.00,100000.00,360000.00,3698000.00,'paid','2026-09-10','2026-09-05 10:30:00','transfer','Đã thanh toán đầy đủ hóa đơn kỳ 08-2026','2026-08-28 02:30:00'),(10,1,1,'09-2026',3000000.00,280000.00,150000.00,360000.00,3790000.00,'paid','2026-10-10','2026-10-05 14:20:00','transfer','Khách Nguyễn Tiến Đạt chuyển khoản Vietcombank đúng hạn','2026-09-28 02:30:00'),(11,2,2,'09-2026',3200000.00,308000.00,150000.00,360000.00,4018000.00,'unpaid','2026-10-10',NULL,NULL,'Chờ khách Lê Thị Thu Hương thanh toán kỳ 09-2026','2026-09-28 02:30:00'),(12,4,3,'09-2026',3500000.00,332500.00,175000.00,360000.00,4367500.00,'paid','2026-10-10','2026-10-05 14:20:00','transfer','Khách Phạm Minh Hoàng thanh toán qua QR Banking','2026-09-28 02:30:00'),(13,5,4,'09-2026',3500000.00,287000.00,125000.00,360000.00,4272000.00,'unpaid','2026-10-10',NULL,NULL,'Đã gửi thông báo nhắc thanh toán kỳ 09-2026','2026-09-28 02:30:00'),(14,7,5,'09-2026',3000000.00,259000.00,125000.00,360000.00,3744000.00,'paid','2026-10-10','2026-10-05 14:20:00','cash','Thu tiền mặt trực tiếp tại văn phòng Tòa A','2026-09-28 02:30:00'),(15,8,6,'09-2026',3200000.00,280000.00,150000.00,360000.00,3990000.00,'paid','2026-10-10','2026-10-05 14:20:00','transfer','Khách Đỗ Quang Huy chuyển khoản Techcombank','2026-09-28 02:30:00'),(16,10,7,'09-2026',3500000.00,367500.00,175000.00,360000.00,4402500.00,'paid','2026-10-10','2026-10-05 14:20:00','transfer','Khách Ngô Thanh Tùng thanh toán chuyển khoản','2026-09-28 02:30:00'),(17,12,8,'09-2026',3200000.00,266000.00,125000.00,360000.00,3951000.00,'paid','2026-10-10','2026-10-05 14:20:00','cash','Khách Bùi Thị Lan Anh đóng tiền mặt','2026-09-28 02:30:00'),(18,13,9,'09-2026',3000000.00,259000.00,125000.00,360000.00,3744000.00,'unpaid','2026-10-10',NULL,NULL,'Chưa thu tiền kỳ 09-2026 (còn nợ)','2026-09-28 02:30:00');
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
  KEY `idx_room_services_room` (`room_id`),
  CONSTRAINT `fk_room_services_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `room_services`
--

LOCK TABLES `room_services` WRITE;
/*!40000 ALTER TABLE `room_services` DISABLE KEYS */;
INSERT INTO `room_services` VALUES (1,1,'Tiền điện',3500.00,'kWh','2026-11-09 11:23:08'),(2,1,'Tiền nước',25000.00,'m³','2026-11-09 11:23:08'),(3,1,'Internet cáp quang',100000.00,'Phòng','2026-11-09 11:23:08'),(4,1,'Thang máy',80000.00,'Phòng','2026-11-09 11:23:08'),(5,1,'Vệ sinh hành lang',30000.00,'Phòng','2026-11-09 11:23:08'),(6,1,'Gửi xe máy',100000.00,'Xe','2026-11-09 11:23:08'),(7,1,'Máy giặt chung',50000.00,'Phòng','2026-11-09 11:23:08'),(8,2,'Tiền điện',3500.00,'kWh','2026-11-09 11:23:08'),(9,2,'Tiền nước',25000.00,'m³','2026-11-09 11:23:08'),(10,2,'Internet cáp quang',100000.00,'Phòng','2026-11-09 11:23:08'),(11,2,'Thang máy',80000.00,'Phòng','2026-11-09 11:23:08'),(12,2,'Vệ sinh hành lang',30000.00,'Phòng','2026-11-09 11:23:08'),(13,2,'Gửi xe máy',100000.00,'Xe','2026-11-09 11:23:08'),(14,2,'Máy giặt chung',50000.00,'Phòng','2026-11-09 11:23:08'),(15,3,'Tiền điện',3500.00,'kWh','2026-11-09 11:23:08'),(16,3,'Tiền nước',25000.00,'m³','2026-11-09 11:23:08'),(17,3,'Internet cáp quang',100000.00,'Phòng','2026-11-09 11:23:08'),(18,3,'Thang máy',80000.00,'Phòng','2026-11-09 11:23:08'),(19,3,'Vệ sinh hành lang',30000.00,'Phòng','2026-11-09 11:23:08'),(20,3,'Gửi xe máy',100000.00,'Xe','2026-11-09 11:23:08'),(21,3,'Máy giặt chung',50000.00,'Phòng','2026-11-09 11:23:08'),(22,4,'Tiền điện',3500.00,'kWh','2026-11-09 11:23:08'),(23,4,'Tiền nước',25000.00,'m³','2026-11-09 11:23:08'),(24,4,'Internet cáp quang',100000.00,'Phòng','2026-11-09 11:23:08'),(25,4,'Thang máy',80000.00,'Phòng','2026-11-09 11:23:08'),(26,4,'Vệ sinh hành lang',30000.00,'Phòng','2026-11-09 11:23:08'),(27,4,'Gửi xe máy',100000.00,'Xe','2026-11-09 11:23:08'),(28,4,'Máy giặt chung',50000.00,'Phòng','2026-11-09 11:23:08'),(29,5,'Tiền điện',3500.00,'kWh','2026-11-09 11:23:08'),(30,5,'Tiền nước',25000.00,'m³','2026-11-09 11:23:08'),(31,5,'Internet cáp quang',100000.00,'Phòng','2026-11-09 11:23:08'),(32,5,'Thang máy',80000.00,'Phòng','2026-11-09 11:23:08'),(33,5,'Vệ sinh hành lang',30000.00,'Phòng','2026-11-09 11:23:08'),(34,5,'Gửi xe máy',100000.00,'Xe','2026-11-09 11:23:08'),(35,5,'Máy giặt chung',50000.00,'Phòng','2026-11-09 11:23:08'),(36,6,'Tiền điện',3500.00,'kWh','2026-11-09 11:23:08'),(37,6,'Tiền nước',25000.00,'m³','2026-11-09 11:23:08'),(38,6,'Internet cáp quang',100000.00,'Phòng','2026-11-09 11:23:08'),(39,6,'Thang máy',80000.00,'Phòng','2026-11-09 11:23:08'),(40,6,'Vệ sinh hành lang',30000.00,'Phòng','2026-11-09 11:23:08'),(41,6,'Gửi xe máy',100000.00,'Xe','2026-11-09 11:23:08'),(42,6,'Máy giặt chung',50000.00,'Phòng','2026-11-09 11:23:08'),(43,7,'Tiền điện',3500.00,'kWh','2026-11-09 11:23:08'),(44,7,'Tiền nước',25000.00,'m³','2026-11-09 11:23:08'),(45,7,'Internet cáp quang',100000.00,'Phòng','2026-11-09 11:23:08'),(46,7,'Thang máy',80000.00,'Phòng','2026-11-09 11:23:08'),(47,7,'Vệ sinh hành lang',30000.00,'Phòng','2026-11-09 11:23:08'),(48,7,'Gửi xe máy',100000.00,'Xe','2026-11-09 11:23:08'),(49,7,'Máy giặt chung',50000.00,'Phòng','2026-11-09 11:23:08'),(50,8,'Tiền điện',3500.00,'kWh','2026-11-09 11:23:08'),(51,8,'Tiền nước',25000.00,'m³','2026-11-09 11:23:08'),(52,8,'Internet cáp quang',100000.00,'Phòng','2026-11-09 11:23:08'),(53,8,'Thang máy',80000.00,'Phòng','2026-11-09 11:23:08'),(54,8,'Vệ sinh hành lang',30000.00,'Phòng','2026-11-09 11:23:08'),(55,8,'Gửi xe máy',100000.00,'Xe','2026-11-09 11:23:08'),(56,8,'Máy giặt chung',50000.00,'Phòng','2026-11-09 11:23:08'),(57,9,'Tiền điện',3500.00,'kWh','2026-11-09 11:23:08'),(58,9,'Tiền nước',25000.00,'m³','2026-11-09 11:23:08'),(59,9,'Internet cáp quang',100000.00,'Phòng','2026-11-09 11:23:08'),(60,9,'Thang máy',80000.00,'Phòng','2026-11-09 11:23:08'),(61,9,'Vệ sinh hành lang',30000.00,'Phòng','2026-11-09 11:23:08'),(62,9,'Gửi xe máy',100000.00,'Xe','2026-11-09 11:23:08'),(63,9,'Máy giặt chung',50000.00,'Phòng','2026-11-09 11:23:08'),(64,10,'Tiền điện',3500.00,'kWh','2026-11-09 11:23:08'),(65,10,'Tiền nước',25000.00,'m³','2026-11-09 11:23:08'),(66,10,'Internet cáp quang',100000.00,'Phòng','2026-11-09 11:23:08'),(67,10,'Thang máy',80000.00,'Phòng','2026-11-09 11:23:08'),(68,10,'Vệ sinh hành lang',30000.00,'Phòng','2026-11-09 11:23:08'),(69,10,'Gửi xe máy',100000.00,'Xe','2026-11-09 11:23:08'),(70,10,'Máy giặt chung',50000.00,'Phòng','2026-11-09 11:23:08'),(71,11,'Tiền điện',3500.00,'kWh','2026-11-09 11:23:08'),(72,11,'Tiền nước',25000.00,'m³','2026-11-09 11:23:08'),(73,11,'Internet cáp quang',100000.00,'Phòng','2026-11-09 11:23:08'),(74,11,'Thang máy',80000.00,'Phòng','2026-11-09 11:23:08'),(75,11,'Vệ sinh hành lang',30000.00,'Phòng','2026-11-09 11:23:08'),(76,11,'Gửi xe máy',100000.00,'Xe','2026-11-09 11:23:08'),(77,11,'Máy giặt chung',50000.00,'Phòng','2026-11-09 11:23:08'),(78,12,'Tiền điện',3500.00,'kWh','2026-11-09 11:23:08'),(79,12,'Tiền nước',25000.00,'m³','2026-11-09 11:23:08'),(80,12,'Internet cáp quang',100000.00,'Phòng','2026-11-09 11:23:08'),(81,12,'Thang máy',80000.00,'Phòng','2026-11-09 11:23:08'),(82,12,'Vệ sinh hành lang',30000.00,'Phòng','2026-11-09 11:23:08'),(83,12,'Gửi xe máy',100000.00,'Xe','2026-11-09 11:23:08'),(84,12,'Máy giặt chung',50000.00,'Phòng','2026-11-09 11:23:08'),(85,13,'Tiền điện',3500.00,'kWh','2026-11-09 11:23:08'),(86,13,'Tiền nước',25000.00,'m³','2026-11-09 11:23:08'),(87,13,'Internet cáp quang',100000.00,'Phòng','2026-11-09 11:23:08'),(88,13,'Thang máy',80000.00,'Phòng','2026-11-09 11:23:08'),(89,13,'Vệ sinh hành lang',30000.00,'Phòng','2026-11-09 11:23:08'),(90,13,'Gửi xe máy',100000.00,'Xe','2026-11-09 11:23:08'),(91,13,'Máy giặt chung',50000.00,'Phòng','2026-11-09 11:23:08'),(92,14,'Tiền điện',3500.00,'kWh','2026-11-09 11:23:08'),(93,14,'Tiền nước',25000.00,'m³','2026-11-09 11:23:08'),(94,14,'Internet cáp quang',100000.00,'Phòng','2026-11-09 11:23:08'),(95,14,'Thang máy',80000.00,'Phòng','2026-11-09 11:23:08'),(96,14,'Vệ sinh hành lang',30000.00,'Phòng','2026-11-09 11:23:08'),(97,14,'Gửi xe máy',100000.00,'Xe','2026-11-09 11:23:08'),(98,14,'Máy giặt chung',50000.00,'Phòng','2026-11-09 11:23:08'),(99,15,'Tiền điện',3500.00,'kWh','2026-11-09 11:23:08'),(100,15,'Tiền nước',25000.00,'m³','2026-11-09 11:23:08'),(101,15,'Internet cáp quang',100000.00,'Phòng','2026-11-09 11:23:08'),(102,15,'Thang máy',80000.00,'Phòng','2026-11-09 11:23:08'),(103,15,'Vệ sinh hành lang',30000.00,'Phòng','2026-11-09 11:23:08'),(104,15,'Gửi xe máy',100000.00,'Xe','2026-11-09 11:23:08'),(105,15,'Máy giặt chung',50000.00,'Phòng','2026-11-09 11:23:08');
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
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'available',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_number` (`room_number`),
  KEY `idx_rooms_building` (`building_id`),
  CONSTRAINT `fk_rooms_building` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rooms`
--

LOCK TABLES `rooms` WRITE;
/*!40000 ALTER TABLE `rooms` DISABLE KEYS */;
INSERT INTO `rooms` VALUES (1,1,'A101',3000000.00,'rented','Phòng tiêu chuẩn tầng 1 | Diện tích: 22m2 | Tối đa: 2 người | Điều hòa, nóng lạnh, giường tủ, khóa vân tay.','2026-11-09 11:23:08'),(2,1,'A102',3200000.00,'rented','Phòng ban công thoáng tầng 1 | Diện tích: 25m2 | Tối đa: 2 người | Ban công đón nắng, bàn làm việc, kệ bếp.','2026-11-09 11:23:08'),(3,1,'A103',3200000.00,'available','Phòng trống sạch sẽ tầng 1 | Diện tích: 25m2 | Tối đa: 2 người | Vừa sơn mới, sẵn sàng bàn giao ngay.','2026-11-09 11:23:08'),(4,1,'A104',3500000.00,'rented','Phòng Studio full nội thất tầng 1 | Diện tích: 28m2 | Tối đa: 3 người | Bếp từ, hút mùi, tủ lạnh, sofa.','2026-11-09 11:23:08'),(5,1,'A105',3500000.00,'rented','Phòng góc 2 mặt thoáng tầng 1 | Diện tích: 30m2 | Tối đa: 3 người | View sân vườn, đầy đủ tiện nghi cao cấp.','2026-11-09 11:23:08'),(6,1,'A106',2800000.00,'maintenance','Phòng tiêu chuẩn tầng 1 | Diện tích: 20m2 | Đang bảo trì đường ống nước và bảo dưỡng điều hòa định kỳ.','2026-11-09 11:23:08'),(7,1,'A201',3000000.00,'rented','Phòng tiêu chuẩn tầng 2 | Diện tích: 22m2 | Tối đa: 2 người | Điều hòa Inverter, giường đệm cao cấp.','2026-11-09 11:23:08'),(8,1,'A202',3200000.00,'rented','Phòng ban công tầng 2 | Diện tích: 25m2 | Tối đa: 2 người | Có máy giặt riêng ngoài ban công.','2026-11-09 11:23:08'),(9,1,'A203',3000000.00,'available','Phòng trống tầng 2 | Diện tích: 22m2 | Tối đa: 2 người | Nội thất cơ bản đầy đủ, cửa khóa vân tay.','2026-11-09 11:23:08'),(10,1,'A204',3500000.00,'rented','Phòng VIP Studio tầng 2 | Diện tích: 30m2 | Tối đa: 3 người | Sofa thông minh, bếp từ đôi, tủ lạnh 2 cánh.','2026-11-09 11:23:08'),(11,1,'A205',3200000.00,'available','Phòng trống ban công tầng 2 | Diện tích: 25m2 | Tối đa: 2 người | Thoáng mát, yên tĩnh, sẵn sàng đón khách.','2026-11-09 11:23:08'),(12,1,'A301',3200000.00,'rented','Phòng ban công tầng 3 | Diện tích: 25m2 | Tối đa: 2 người | View toàn cảnh Cầu Giấy, ánh sáng tự nhiên.','2026-11-09 11:23:08'),(13,1,'A302',3000000.00,'rented','Phòng tiêu chuẩn tầng 3 | Diện tích: 22m2 | Tối đa: 2 người | Đầy đủ điều hòa, nóng lạnh, giường tủ.','2026-11-09 11:23:08'),(14,1,'A303',3500000.00,'rented','Phòng Studio tầng 3 | Diện tích: 28m2 | Tối đa: 3 người | Full nội thất cao cấp, mới nhận bàn giao.','2026-11-09 11:23:08'),(15,1,'A304',3000000.00,'rented','Phòng tiêu chuẩn tầng 3 | Diện tích: 22m2 | Tối đa: 2 người | Khách vừa đặt cọc giữ chỗ 5 ngày.','2026-11-09 11:23:08'),(16,1,'P401',3500000.00,'rented','Tòa: Tòa A - Smart Stay Sunrise | Loại: Phòng Tiêu chuẩn (Standard) (2.600.000đ) | Tầng 4 | Diện tích: 22.00 m² | Tối đa: 3 người | Tiện nghi: Máy lạnh, Nước nóng, Wifi, Kệ bếp, WC riêng | Ảnh: https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=900&q=85','2026-11-09 11:29:29');
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
INSERT INTO `services` VALUES (1,'Tiền điện',3500.00,'kWh',1,'2026-11-09 11:23:08'),(2,'Tiền nước',25000.00,'m³',1,'2026-11-09 11:23:08'),(3,'Internet cáp quang',100000.00,'Phòng',1,'2026-11-09 11:23:08'),(4,'Thang máy',80000.00,'Phòng',1,'2026-11-09 11:23:08'),(5,'Vệ sinh hành lang',30000.00,'Phòng',1,'2026-11-09 11:23:08'),(6,'Gửi xe máy',100000.00,'Xe',1,'2026-11-09 11:23:08'),(7,'Máy giặt chung',50000.00,'Phòng',1,'2026-11-09 11:23:08');
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenant_contact_leads`
--

DROP TABLE IF EXISTS `tenant_contact_leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenant_contact_leads` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `room_label` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `room_id` int DEFAULT NULL,
  `visit_time` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','contacted') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `contacted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenant_contact_leads`
--

LOCK TABLES `tenant_contact_leads` WRITE;
/*!40000 ALTER TABLE `tenant_contact_leads` DISABLE KEYS */;
INSERT INTO `tenant_contact_leads` VALUES (1,'Trần Thu Thủy','0977889900','Phòng A103 - Ban công tầng 1',3,'2026-10-05 15:00:00','Muốn xem phòng A103 vào chiều thứ 7 lúc 15h.','contacted','2026-10-01 16:00:00','2026-10-01 16:30:00'),(2,'Nguyễn Văn Đạt','0966554433','Phòng A203 - Tiêu chuẩn tầng 2',9,'2026-10-06 10:00:00','Hỏi chỗ để xe máy và hẹn xem phòng A203.','','2026-10-02 10:30:00',NULL);
/*!40000 ALTER TABLE `tenant_contact_leads` ENABLE KEYS */;
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
  KEY `idx_tenant_requests_tenant` (`tenant_id`),
  KEY `idx_tenant_requests_status` (`status`),
  CONSTRAINT `fk_tenant_requests_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenant_requests`
--

LOCK TABLES `tenant_requests` WRITE;
/*!40000 ALTER TABLE `tenant_requests` DISABLE KEYS */;
INSERT INTO `tenant_requests` VALUES (1,5,'profile_update','phone','0901000001','0988112233','Em đổi sang số điện thoại mới để nhận thông báo hóa đơn phòng A101.','pending',NULL,'2026-10-01 08:30:00','2026-11-09 11:23:09'),(2,6,'profile_update','email','huong.lt@gmail.com','huong.le.mkt@gmail.com','Cập nhật email công việc mới.','approved','BQL Tòa A đã duyệt cập nhật.','2026-09-25 02:15:00','2026-11-09 11:23:09');
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `third_party_expenses`
--

LOCK TABLES `third_party_expenses` WRITE;
/*!40000 ALTER TABLE `third_party_expenses` DISABLE KEYS */;
INSERT INTO `third_party_expenses` VALUES (1,'08-2026','electricity','Tiền điện tổng trạm biến áp Tòa A (Kỳ 08/2026)','Công ty Điện lực Cầu Giấy (EVN HANOI)',693.00,'kWh',2500.00,1732500.00,2425500.00,'paid','2026-08-30','Thanh toán hóa đơn điện tổng công tơ 3 pha Tòa A','2026-08-30 02:00:00'),(2,'08-2026','water','Tiền nước sạch sinh hoạt Tòa A (Kỳ 08/2026)','Công ty Nước sạch Hà Nội (HAWACO)',47.00,'m³',15000.00,705000.00,1175000.00,'paid','2026-08-30','Thanh toán tiền nước sạch đầu nguồn Tòa A','2026-08-30 02:15:00'),(3,'08-2026','internet','Cước cáp quang doanh nghiệp FTTH Tòa A','VNPT Cầu Giấy - Hà Nội',1.00,'Gói',650000.00,650000.00,900000.00,'paid','2026-08-28','Gói cáp quang tốc độ cao 500Mbps cho 3 tầng Tòa A','2026-08-28 03:00:00'),(4,'08-2026','elevator','Bảo trì định kỳ thang máy & chiếu sáng Tòa A','Công ty Thang máy MitsuLift Việt Nam',1.00,'Tháng',500000.00,500000.00,720000.00,'paid','2026-08-29','Bảo dưỡng cáp tải, tra dầu ray dẫn thang máy Tòa A','2026-08-29 07:00:00'),(5,'08-2026','cleaning','Dịch vụ vệ sinh hành lang & khu chung Tòa A','Công ty Môi Trường Xanh Cầu Giấy',1.00,'Tháng',450000.00,450000.00,720000.00,'paid','2026-08-29','Lau dọn hành lang, cầu thang, sân để xe Tòa A','2026-08-29 08:00:00'),(6,'09-2026','electricity','Tiền điện tổng trạm biến áp Tòa A (Kỳ 09/2026)','Công ty Điện lực Cầu Giấy (EVN HANOI)',792.00,'kWh',2500.00,1980000.00,2772000.00,'paid','2026-09-30','Thanh toán hóa đơn điện tổng công tơ 3 pha Tòa A','2026-09-30 02:00:00'),(7,'09-2026','water','Tiền nước sạch sinh hoạt Tòa A (Kỳ 09/2026)','Công ty Nước sạch Hà Nội (HAWACO)',56.00,'m³',15000.00,840000.00,1400000.00,'paid','2026-09-30','Thanh toán tiền nước sạch đầu nguồn Tòa A','2026-09-30 02:15:00'),(8,'09-2026','internet','Cước cáp quang doanh nghiệp FTTH Tòa A','VNPT Cầu Giấy - Hà Nội',1.00,'Gói',650000.00,650000.00,900000.00,'paid','2026-09-28','Gói cáp quang tốc độ cao 500Mbps cho 3 tầng Tòa A','2026-09-28 03:00:00'),(9,'09-2026','elevator','Bảo trì định kỳ thang máy & chiếu sáng Tòa A','Công ty Thang máy MitsuLift Việt Nam',1.00,'Tháng',500000.00,500000.00,720000.00,'paid','2026-09-29','Bảo dưỡng định kỳ thang máy Tòa A tháng 09/2026','2026-09-29 07:00:00'),(10,'09-2026','cleaning','Dịch vụ vệ sinh hành lang & thu gom rác Tòa A','Công ty Môi Trường Xanh Cầu Giấy',1.00,'Tháng',450000.00,450000.00,720000.00,'paid','2026-09-29','Vệ sinh định kỳ thứ 2, 4, 6 và thu gom rác mỗi tối','2026-09-29 08:00:00');
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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$10$CUmu473.Tnp0wiZkjUAOfuf092Yn8ZUQ6Uzfbdp5BvyvXuRwKOZJm','Quản Trị Viên Hệ Thống','0901234567','001090000001','1990-01-15','Nam','admin@staymate.vn','Quản trị hệ thống','Hà Nội','0988776655','active',NULL,NULL,'admin',NULL,'2026-11-09 11:23:09'),(2,'quanly_a','$2y$10$9f3O8GtIsTRevhmrwTvbiONWAURKjW/GCHnTaqL6HPz0nCJ0m0r4y','Nguyễn Văn Quân','0912345678','001092000002','1992-05-20','Nam','quanly_a@staymate.vn','Quản lý Tòa A','Bắc Ninh','0912345679','active',NULL,NULL,'manager',NULL,'2026-11-09 11:23:09'),(5,'0901000001','$2y$10$j2gJl/hPCl5LnrhM6uTApu9lB6yit3xb4Iy3h75fcQlKvoc4bEgR.','Nguyễn Tiến Đạt','0901000001','001201001001','2001-03-12','Nam','dat.nt@gmail.com','Kỹ sư Phần mềm FPT','Hà Nội','0981111001','Đang thuê','Trần Quốc Bảo','0971000111','tenant',1,'2026-11-09 11:23:09'),(6,'0901000002','$2y$10$j2gJl/hPCl5LnrhM6uTApu9lB6yit3xb4Iy3h75fcQlKvoc4bEgR.','Lê Thị Thu Hương','0901000002','001202001002','2002-07-24','Nữ','huong.lt@gmail.com','Chuyên viên Marketing','Hải Dương','0981111002','Đang thuê','Nguyễn Mai Chi','0971000112','tenant',2,'2026-11-09 11:23:09'),(7,'0901000004','$2y$10$j2gJl/hPCl5LnrhM6uTApu9lB6yit3xb4Iy3h75fcQlKvoc4bEgR.','Phạm Minh Hoàng','0901000004','001200001004','2000-11-05','Nam','hoang.pm@gmail.com','Kiến trúc sư nội thất','Nam Định','0981111004','Đang thuê','Lê Văn Tùng','0971000114','tenant',4,'2026-11-09 11:23:09'),(8,'0901000005','$2y$10$j2gJl/hPCl5LnrhM6uTApu9lB6yit3xb4Iy3h75fcQlKvoc4bEgR.','Vũ Minh Tuấn','0901000005','001201001005','2001-09-18','Nam','tuan.vm@gmail.com','Chuyên viên Tài chính','Thái Bình','0981111005','Đang thuê',NULL,NULL,'tenant',5,'2026-11-09 11:23:09'),(9,'0901000006','$2y$10$j2gJl/hPCl5LnrhM6uTApu9lB6yit3xb4Iy3h75fcQlKvoc4bEgR.','Hoàng Thị Mai','0901000006','001203001006','2003-02-14','Nữ','mai.ht@gmail.com','Sinh viên ĐH Quốc Gia','Nghệ An','0981111006','Đang thuê','Hoàng Thị Lan','0971000116','tenant',7,'2026-11-09 11:23:09'),(10,'0901000007','$2y$10$j2gJl/hPCl5LnrhM6uTApu9lB6yit3xb4Iy3h75fcQlKvoc4bEgR.','Đỗ Quang Huy','0901000007','001201001007','2001-06-30','Nam','huy.dq@gmail.com','Thiết kế đồ họa UI/UX','Hải Phòng','0981111007','Đang thuê',NULL,NULL,'tenant',8,'2026-11-09 11:23:09'),(11,'0901000009','$2y$10$j2gJl/hPCl5LnrhM6uTApu9lB6yit3xb4Iy3h75fcQlKvoc4bEgR.','Ngô Thanh Tùng','0901000009','001199001009','1999-12-20','Nam','tung.nt@gmail.com','Trưởng nhóm Kinh doanh','Hà Nam','0981111009','Đang thuê','Ngô Minh Đức','0971000119','tenant',10,'2026-11-09 11:23:09'),(12,'0901000010','$2y$10$j2gJl/hPCl5LnrhM6uTApu9lB6yit3xb4Iy3h75fcQlKvoc4bEgR.','Bùi Thị Lan Anh','0901000010','001202001010','2002-04-08','Nữ','lananh.bt@gmail.com','Giáo viên Tiếng Anh','Vĩnh Phúc','0981111010','Đang thuê',NULL,NULL,'tenant',12,'2026-11-09 11:23:09'),(13,'0901000011','$2y$10$j2gJl/hPCl5LnrhM6uTApu9lB6yit3xb4Iy3h75fcQlKvoc4bEgR.','Đặng Quốc Khánh','0901000011','001201001011','2001-08-19','Nam','khanh.dq@gmail.com','Lập trình viên Mobile','Phú Thọ','0981111011','Đang thuê',NULL,NULL,'tenant',13,'2026-11-09 11:23:09'),(14,'0901000012','$2y$10$j2gJl/hPCl5LnrhM6uTApu9lB6yit3xb4Iy3h75fcQlKvoc4bEgR.','Trương Mỹ Linh','0901000012','001203001012','2003-10-10','Nữ','linh.tm@gmail.com','Nhân viên Ngân hàng','Thanh Hóa','0981111012','Đang thuê',NULL,NULL,'tenant',14,'2026-11-09 11:23:09'),(15,'0901000013','$2y$10$j2gJl/hPCl5LnrhM6uTApu9lB6yit3xb4Iy3h75fcQlKvoc4bEgR.','Lý Hoàng Nam','0901000013','001202001013','2002-01-25','Nam','nam.lh@gmail.com','Kỹ sư Cầu đường','Bắc Giang','0981111013','Chờ nhận phòng',NULL,NULL,'tenant',15,'2026-11-09 11:23:09'),(16,'0901000021','$2y$10$j2gJl/hPCl5LnrhM6uTApu9lB6yit3xb4Iy3h75fcQlKvoc4bEgR.','Phan Văn Trị','0901000021','001198001021','1998-05-15','Nam','tri.pv@gmail.com','Kế toán trưởng','Thừa Thiên Huế','0981111021','Đã chuyển đi',NULL,NULL,'tenant',3,'2026-11-09 11:23:09'),(17,'0901000022','$2y$10$j2gJl/hPCl5LnrhM6uTApu9lB6yit3xb4Iy3h75fcQlKvoc4bEgR.','Hồ Xuân Hương','0901000022','001200001022','2000-09-02','Nữ','huong.hx@gmail.com','Biên tập viên Báo chí','Nghệ An','0981111022','Đã chuyển đi',NULL,NULL,'tenant',9,'2026-11-09 11:23:09'),(18,'0901000023','$2y$10$j2gJl/hPCl5LnrhM6uTApu9lB6yit3xb4Iy3h75fcQlKvoc4bEgR.','Đoàn Thị Điểm','0901000023','001201001023','2001-11-30','Nữ','diem.dt@gmail.com','Dược sĩ lâm sàng','Hưng Yên','0981111023','Đã chuyển đi',NULL,NULL,'tenant',11,'2026-11-09 11:23:09'),(19,'0902000001','$2y$10$j2gJl/hPCl5LnrhM6uTApu9lB6yit3xb4Iy3h75fcQlKvoc4bEgR.','Trần Đình Khang','0902000001','001204002001','2004-03-22','Nam','khang.td@gmail.com','Sinh viên ĐH Bách Khoa','Ninh Bình','0982222001','Chờ nhận phòng',NULL,NULL,'tenant',3,'2026-11-09 11:23:09'),(20,'0902000002','$2y$10$j2gJl/hPCl5LnrhM6uTApu9lB6yit3xb4Iy3h75fcQlKvoc4bEgR.','Phạm Thảo Vy','0902000002','001204002002','2004-07-14','Nữ','vy.pt@gmail.com','Sinh viên ĐH Ngoại Thương','Quảng Ninh','0982222002','Chờ nhận phòng',NULL,NULL,'tenant',9,'2026-11-09 11:23:09'),(21,'0902000003','$2y$10$j2gJl/hPCl5LnrhM6uTApu9lB6yit3xb4Iy3h75fcQlKvoc4bEgR.','Đoàn Hữu Phước','0902000003','001203002003','2003-12-01','Nam','phuoc.dh@gmail.com','Chuyên viên QA/Tester','Đà Nẵng','0982222003','Chờ nhận phòng',NULL,NULL,'tenant',11,'2026-11-09 11:23:09'),(22,'0399527235','$2y$10$7FDvUTtrEBv4iEKRD.rqQ.l4lOHPLL98ugalEqPSY8lueUJDGMOA6','Hủng Văn Ngọc','0399527235','001306070809','2006-04-02','Nam','ngoc@gmail.com','sinh viên','Hà Giang','','Đang thuê','','','tenant',16,'2026-11-09 11:31:23');
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
  CONSTRAINT `fk_utilities_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `utilities_readings`
--

LOCK TABLES `utilities_readings` WRITE;
/*!40000 ALTER TABLE `utilities_readings` DISABLE KEYS */;
INSERT INTO `utilities_readings` VALUES (1,1,'08-2026',210,285,18,23,'2026-08-28 02:00:00'),(2,2,'08-2026',180,260,15,20,'2026-08-28 02:00:00'),(3,4,'08-2026',320,415,24,31,'2026-08-28 02:00:00'),(4,5,'08-2026',150,230,12,17,'2026-08-28 02:00:00'),(5,7,'08-2026',240,310,20,25,'2026-08-28 02:00:00'),(6,8,'08-2026',195,270,16,21,'2026-08-28 02:00:00'),(7,10,'08-2026',290,390,22,29,'2026-08-28 02:00:00'),(8,12,'08-2026',410,480,32,37,'2026-08-28 02:00:00'),(9,13,'08-2026',160,228,14,18,'2026-08-28 02:00:00'),(10,1,'09-2026',285,365,23,29,'2026-09-28 02:00:00'),(11,2,'09-2026',260,348,20,26,'2026-09-28 02:00:00'),(12,4,'09-2026',415,510,31,38,'2026-09-28 02:00:00'),(13,5,'09-2026',230,312,17,22,'2026-09-28 02:00:00'),(14,7,'09-2026',310,384,25,30,'2026-09-28 02:00:00'),(15,8,'09-2026',270,350,21,27,'2026-09-28 02:00:00'),(16,10,'09-2026',390,495,29,36,'2026-09-28 02:00:00'),(17,12,'09-2026',480,556,37,42,'2026-09-28 02:00:00'),(18,13,'09-2026',228,302,18,23,'2026-09-28 02:00:00'),(19,14,'09-2026',100,165,10,14,'2026-09-29 03:00:00');
/*!40000 ALTER TABLE `utilities_readings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'quan_ly_nha_tro'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-09 19:11:07
