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
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `building_id` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'all',
  `action_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'info',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `building_id` (`building_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,'Nguyễn Tuấn Dũng','admin','all','success','Đã thu tiền hóa đơn phòng A101 (Kỳ 10-2026) - Số tiền: 3.820.000 đ','2026-11-04 10:49:31'),(2,'Nguyễn Văn Quân','manager','A','success','Đã lập hợp đồng cọc giữ chỗ cho phòng A105 (Khách: Trần Thu Nga)','2026-11-04 09:04:31'),(3,'Trần Thị Mai Lan','manager','B','info','Đã chốt chỉ số điện nước kỳ 10-2026 cho toàn bộ Tòa B (StayMate Emerald)','2026-11-03 08:04:31'),(4,'Lê Hoàng Nam','manager','C','success','Đã thu tiền phòng tháng đầu kèm cọc cho phòng C201 (Số tiền: 4.200.000 đ)','2026-11-02 10:04:31'),(5,'Nguyễn Tuấn Dũng','admin','all','info','Khách hàng Nguyễn Văn Hùng để lại yêu cầu tư vấn xem phòng A105','2026-11-01 07:04:31'),(6,'Nguyễn Văn Quân','manager','A','warning','Đã gửi thông báo nhắc nợ tiền phòng cho các phòng chưa thanh toán','2026-10-31 09:04:31'),(7,'Trần Thị Mai Lan','manager','B','success','Đã thanh toán hóa đơn đối tác Internet VNPT (Số tiền: 3.600.000 đ)','2026-10-30 06:04:31'),(8,'Lê Hoàng Nam','manager','C','success','Đã hoàn tất thủ tục thanh lý hợp đồng và hoàn cọc 100% cho phòng C105','2026-10-28 05:04:31'),(9,'Nguyễn Tuấn Dũng','admin','all','warning','Tịch thu tiền cọc phòng vi phạm hợp đồng bỏ phòng trước hạn','2026-10-25 08:04:31'),(10,'Nguyễn Văn Quân','manager','A','info','Cập nhật lại giá điện theo biểu giá nhà nước 3.500 đ/kWh và nước 20.000 đ/m3','2026-10-20 04:04:31'),(11,'Trần Thị Mai Lan','manager','B','success','Cư dân phòng B201 gửi yêu cầu bổ sung thông tin CCCD bạn cùng phòng','2026-10-17 07:04:31'),(12,'Nguyễn Tuấn Dũng','admin','all','info','Khởi tạo hệ thống quản lý 60 phòng trọ và đồng bộ số liệu 3 tòa nhà','2026-10-10 03:04:31');
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `buildings`
--

LOCK TABLES `buildings` WRITE;
/*!40000 ALTER TABLE `buildings` DISABLE KEYS */;
INSERT INTO `buildings` VALUES (1,'Tòa A - StayMate Sunrise','Số 128 Cầu Giấy, Cầu Giấy, Hà Nội',2,'2026-11-04 16:56:54'),(2,'Tòa B - StayMate Emerald','Số 45 Nguyễn Chí Thanh, Ba Đình, Hà Nội',3,'2026-11-04 16:56:54'),(3,'Tòa C - StayMate Horizon','Số 88 Nguyễn Trãi, Thanh Xuân, Hà Nội',4,'2026-11-04 16:56:54');
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
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `first_month_paid_at` datetime DEFAULT NULL,
  `first_month_paid_note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unpaid_notice_sent` tinyint(1) DEFAULT '0',
  `unpaid_notice_sent_at` datetime DEFAULT NULL,
  `termination_verification_case` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT 'none',
  `refund_confirmed_invoice` tinyint(1) DEFAULT '0',
  `unpaid_notice_resolved_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contract_code` (`contract_code`),
  KEY `room_id` (`room_id`),
  KEY `tenant_id` (`tenant_id`),
  CONSTRAINT `contracts_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`),
  CONSTRAINT `contracts_ibfk_2` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contracts`
--

LOCK TABLES `contracts` WRITE;
/*!40000 ALTER TABLE `contracts` DISABLE KEYS */;
INSERT INTO `contracts` VALUES (1,'HD-2026-001','official_lease',NULL,1,5,'2026-01-01','2026-12-31',3200000.00,NULL,100,'held',3200000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'renewed',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(2,'HD-2026-002','official_lease',NULL,2,6,'2026-02-15','2026-11-20',3200000.00,NULL,100,'held',3200000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(3,'HD-2026-003','official_lease',NULL,3,7,'2026-03-01','2027-03-01',3400000.00,NULL,100,'held',3400000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'renewed',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(4,'HD-2026-004','official_lease',NULL,4,8,'2026-04-10','2026-11-28',3500000.00,NULL,100,'held',3500000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(5,'HD-2026-005','official_lease',NULL,5,9,'2026-05-01','2027-05-01',3500000.00,NULL,100,'held',3500000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(6,'HD-2026-006','official_lease',NULL,6,10,'2026-06-01','2027-06-01',3300000.00,NULL,100,'held',3300000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'renewed',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(7,'HD-2026-007','official_lease',NULL,7,11,'2026-07-01','2026-12-15',3300000.00,NULL,100,'held',3300000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(8,'HD-2026-008','official_lease',NULL,8,12,'2026-01-10','2027-01-10',3500000.00,NULL,100,'held',3500000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(9,'HD-2026-009','official_lease',NULL,9,13,'2026-02-01','2027-02-01',3500000.00,NULL,100,'held',3500000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(10,'HD-2026-010','official_lease',NULL,11,14,'2026-03-01','2027-03-01',3400000.00,NULL,100,'held',3400000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(11,'HD-2026-011','official_lease',NULL,12,15,'2026-04-01','2026-11-18',3400000.00,NULL,100,'held',3400000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'renewed',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(12,'HD-2026-012','official_lease',NULL,13,16,'2026-05-01','2027-05-01',3600000.00,NULL,100,'held',3600000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(13,'HD-2026-013','official_lease',NULL,14,17,'2026-06-01','2027-06-01',3600000.00,NULL,100,'held',3600000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(14,'HD-2026-014','official_lease',NULL,15,18,'2026-07-01','2027-07-01',3500000.00,NULL,100,'held',3500000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(15,'HD-2026-015','official_lease',NULL,16,19,'2026-01-15','2027-01-15',3600000.00,NULL,100,'held',3600000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'renewed',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(16,'HD-2026-016','official_lease',NULL,17,20,'2026-02-15','2027-02-15',3600000.00,NULL,100,'held',3600000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(17,'HD-2026-017','official_lease',NULL,18,21,'2026-03-15','2027-03-15',3800000.00,NULL,100,'held',3800000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(18,'HD-2026-018','official_lease',NULL,19,22,'2026-04-15','2027-04-15',3800000.00,NULL,100,'held',3800000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(19,'HD-2026-019','official_lease',NULL,21,23,'2026-01-01','2027-01-01',4200000.00,NULL,100,'held',4200000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'renewed',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(20,'HD-2026-020','official_lease',NULL,22,24,'2026-02-01','2027-02-01',4200000.00,NULL,100,'held',4200000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(21,'HD-2026-021','official_lease',NULL,23,25,'2026-03-01','2027-03-01',4500000.00,NULL,100,'held',4500000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(22,'HD-2026-022','official_lease',NULL,24,26,'2026-04-01','2026-11-22',4500000.00,NULL,100,'held',4500000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(23,'HD-2026-023','official_lease',NULL,26,27,'2026-05-01','2027-05-01',4300000.00,NULL,100,'held',4300000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'renewed',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(24,'HD-2026-024','official_lease',NULL,27,28,'2026-06-01','2027-06-01',4300000.00,NULL,100,'held',4300000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(25,'HD-2026-025','official_lease',NULL,28,29,'2026-07-01','2027-07-01',4600000.00,NULL,100,'held',4600000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'renewed',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(26,'HD-2026-026','official_lease',NULL,29,30,'2026-01-10','2027-01-10',4600000.00,NULL,100,'held',4600000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(27,'HD-2026-027','official_lease',NULL,30,31,'2026-02-10','2027-02-10',4400000.00,NULL,100,'held',4400000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(28,'HD-2026-028','official_lease',NULL,31,32,'2026-03-10','2027-03-10',4500000.00,NULL,100,'held',4500000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'renewed',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(29,'HD-2026-029','official_lease',NULL,32,33,'2026-04-10','2027-04-10',4500000.00,NULL,100,'held',4500000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(30,'HD-2026-030','official_lease',NULL,33,34,'2026-05-10','2027-05-10',4800000.00,NULL,100,'held',4800000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(31,'HD-2026-031','official_lease',NULL,34,35,'2026-06-10','2027-06-10',4800000.00,NULL,100,'held',4800000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(32,'HD-2026-032','official_lease',NULL,36,36,'2026-01-20','2027-01-20',4700000.00,NULL,100,'held',4700000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(33,'HD-2026-033','official_lease',NULL,37,37,'2026-02-20','2027-02-20',4700000.00,NULL,100,'held',4700000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'renewed',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(34,'HD-2026-034','official_lease',NULL,38,38,'2026-03-20','2027-03-20',5000000.00,NULL,100,'held',5000000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(35,'HD-2026-035','official_lease',NULL,39,39,'2026-04-20','2027-04-20',5000000.00,NULL,100,'held',5000000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(36,'HD-2026-036','official_lease',NULL,41,40,'2026-01-01','2027-01-01',2700000.00,NULL,100,'held',2700000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'renewed',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(37,'HD-2026-037','official_lease',NULL,42,41,'2026-02-01','2027-02-01',2700000.00,NULL,100,'held',2700000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(38,'HD-2026-038','official_lease',NULL,43,42,'2026-03-01','2027-03-01',2900000.00,NULL,100,'held',2900000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(39,'HD-2026-039','official_lease',NULL,44,43,'2026-04-01','2027-04-01',3000000.00,NULL,100,'held',3000000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(40,'HD-2026-040','official_lease',NULL,45,44,'2026-05-01','2027-05-01',3000000.00,NULL,100,'held',3000000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(41,'HD-2026-041','official_lease',NULL,46,45,'2026-06-01','2027-06-01',2800000.00,NULL,100,'held',2800000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'renewed',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(42,'HD-2026-042','official_lease',NULL,47,46,'2026-07-01','2027-07-01',2800000.00,NULL,100,'held',2800000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(43,'HD-2026-043','official_lease',NULL,48,47,'2026-01-15','2027-01-15',3100000.00,NULL,100,'held',3100000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(44,'HD-2026-044','official_lease',NULL,49,48,'2026-02-15','2027-02-15',3100000.00,NULL,100,'held',3100000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(45,'HD-2026-045','official_lease',NULL,51,49,'2026-03-15','2027-03-15',2900000.00,NULL,100,'held',2900000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'renewed',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(46,'HD-2026-046','official_lease',NULL,52,50,'2026-04-15','2027-04-15',2900000.00,NULL,100,'held',2900000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(47,'HD-2026-047','official_lease',NULL,53,51,'2026-05-15','2027-05-15',3200000.00,NULL,100,'held',3200000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(48,'HD-2026-048','official_lease',NULL,54,52,'2026-06-15','2027-06-15',3200000.00,NULL,100,'held',3200000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(49,'HD-2026-049','official_lease',NULL,56,53,'2026-01-25','2027-01-25',3000000.00,NULL,100,'held',3000000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(50,'HD-2026-050','official_lease',NULL,57,54,'2026-02-25','2027-02-25',3000000.00,NULL,100,'held',3000000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'renewed',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(51,'HD-2026-051','official_lease',NULL,58,55,'2026-03-25','2027-03-25',3300000.00,NULL,100,'held',3300000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL),(52,'HD-2026-052','official_lease',NULL,59,56,'2026-04-25','2027-04-25',3300000.00,NULL,100,'held',3300000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 16:56:54',NULL,NULL,0,NULL,'none',0,NULL);
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
  CONSTRAINT `invoice_details_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoice_details_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_details`
--

LOCK TABLES `invoice_details` WRITE;
/*!40000 ALTER TABLE `invoice_details` DISABLE KEYS */;
INSERT INTO `invoice_details` VALUES (1,1,NULL,1.00,3200000.00,3200000.00),(2,1,1,140.00,3500.00,490000.00),(3,1,2,1.00,80000.00,80000.00),(4,1,3,1.00,50000.00,50000.00),(5,1,4,1.00,30000.00,30000.00),(6,1,5,1.00,20000.00,20000.00),(7,1,6,1.00,20000.00,20000.00),(8,2,NULL,1.00,3200000.00,3200000.00),(9,2,1,140.00,3500.00,490000.00),(10,2,2,1.00,80000.00,80000.00),(11,2,3,1.00,50000.00,50000.00),(12,2,4,1.00,30000.00,30000.00),(13,2,5,1.00,20000.00,20000.00),(14,2,6,1.00,20000.00,20000.00),(15,3,NULL,1.00,3400000.00,3400000.00),(16,3,1,135.00,3500.00,472500.00),(17,3,2,2.00,80000.00,160000.00),(18,3,3,1.00,50000.00,50000.00),(19,3,4,1.00,30000.00,30000.00),(20,3,5,2.00,20000.00,40000.00),(21,3,6,1.00,20000.00,20000.00),(22,4,NULL,1.00,3500000.00,3500000.00),(23,4,1,140.00,3500.00,490000.00),(24,4,2,2.00,80000.00,160000.00),(25,4,3,1.00,50000.00,50000.00),(26,4,4,1.00,30000.00,30000.00),(27,4,5,2.00,20000.00,40000.00),(28,4,6,1.00,20000.00,20000.00),(29,19,NULL,1.00,4200000.00,4200000.00),(30,19,1,180.00,3500.00,630000.00),(31,19,2,1.00,80000.00,80000.00),(32,19,3,1.00,50000.00,50000.00),(33,19,4,1.00,30000.00,30000.00),(34,19,5,1.00,20000.00,20000.00),(35,19,6,1.00,20000.00,20000.00),(36,20,NULL,1.00,4200000.00,4200000.00),(37,20,1,190.00,3500.00,665000.00),(38,20,2,1.00,80000.00,80000.00),(39,20,3,1.00,50000.00,50000.00),(40,20,4,1.00,30000.00,30000.00),(41,20,5,1.00,20000.00,20000.00),(42,20,6,1.00,20000.00,20000.00),(43,36,NULL,1.00,2700000.00,2700000.00),(44,36,1,120.00,3500.00,420000.00),(45,36,2,1.00,80000.00,80000.00),(46,36,3,1.00,50000.00,50000.00),(47,36,4,1.00,30000.00,30000.00),(48,36,5,1.00,20000.00,20000.00),(49,36,6,1.00,20000.00,20000.00);
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
  KEY `contract_id` (`contract_id`),
  CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`),
  CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
INSERT INTO `invoices` VALUES (1,1,1,'10-2026',3200000.00,490000.00,80000.00,120000.00,3890000.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(2,2,2,'10-2026',3200000.00,490000.00,80000.00,120000.00,3890000.00,'unpaid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(3,3,3,'10-2026',3400000.00,472500.00,160000.00,140000.00,4172500.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(4,4,4,'10-2026',3500000.00,490000.00,160000.00,140000.00,4290000.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(5,5,5,'10-2026',3500000.00,455000.00,1120000.00,-920000.00,4155000.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(6,6,6,'10-2026',3300000.00,420000.00,800000.00,-600000.00,3920000.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(7,7,7,'10-2026',3300000.00,437500.00,960000.00,-760000.00,3937500.00,'unpaid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(8,8,8,'10-2026',3500000.00,525000.00,1200000.00,-900000.00,4325000.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(9,9,9,'10-2026',3500000.00,525000.00,1200000.00,-1000000.00,4225000.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(10,11,10,'10-2026',3400000.00,490000.00,1200000.00,-1000000.00,4090000.00,'unpaid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(11,12,11,'10-2026',3400000.00,455000.00,1040000.00,-840000.00,4055000.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(12,13,12,'10-2026',3600000.00,560000.00,1440000.00,-1140000.00,4460000.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(13,14,13,'10-2026',3600000.00,542500.00,1360000.00,-1060000.00,4442500.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(14,15,14,'10-2026',3500000.00,455000.00,1120000.00,-920000.00,4155000.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(15,16,15,'10-2026',3600000.00,525000.00,1280000.00,-1080000.00,4325000.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(16,17,16,'10-2026',3600000.00,507500.00,1120000.00,-920000.00,4307500.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(17,18,17,'10-2026',3800000.00,595000.00,1520000.00,-1220000.00,4695000.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(18,19,18,'10-2026',3800000.00,577500.00,1440000.00,-1140000.00,4677500.00,'unpaid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(19,21,19,'10-2026',4200000.00,630000.00,80000.00,120000.00,5030000.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(20,22,20,'10-2026',4200000.00,665000.00,80000.00,120000.00,5065000.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(21,23,21,'10-2026',4500000.00,700000.00,1760000.00,-1560000.00,5400000.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(22,24,22,'10-2026',4500000.00,630000.00,1440000.00,-1240000.00,5330000.00,'unpaid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(23,26,23,'10-2026',4300000.00,560000.00,1280000.00,-1080000.00,5060000.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(24,27,24,'10-2026',4300000.00,595000.00,1360000.00,-1160000.00,5095000.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(25,28,25,'10-2026',4600000.00,665000.00,1600000.00,-1400000.00,5465000.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(26,29,26,'10-2026',4600000.00,665000.00,1520000.00,-1320000.00,5465000.00,'unpaid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(27,30,27,'10-2026',4400000.00,595000.00,1440000.00,-1240000.00,5195000.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(28,31,28,'10-2026',4500000.00,700000.00,1760000.00,-1560000.00,5400000.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(29,32,29,'10-2026',4500000.00,682500.00,1680000.00,-1480000.00,5382500.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(30,33,30,'10-2026',4800000.00,770000.00,2000000.00,-1800000.00,5770000.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(31,34,31,'10-2026',4800000.00,752500.00,1920000.00,-1720000.00,5752500.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(32,36,32,'10-2026',4700000.00,700000.00,1760000.00,-1560000.00,5600000.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(33,37,33,'10-2026',4700000.00,735000.00,1840000.00,-1640000.00,5635000.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(34,38,34,'10-2026',5000000.00,805000.00,2240000.00,-2040000.00,6005000.00,'unpaid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(35,39,35,'10-2026',5000000.00,805000.00,2160000.00,-1960000.00,6005000.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(36,41,36,'10-2026',2700000.00,420000.00,80000.00,120000.00,3320000.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(37,42,37,'10-2026',2700000.00,437500.00,960000.00,-760000.00,3337500.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(38,43,38,'10-2026',2900000.00,455000.00,1040000.00,-840000.00,3555000.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(39,44,39,'10-2026',3000000.00,490000.00,1120000.00,-920000.00,3690000.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(40,45,40,'10-2026',3000000.00,525000.00,1200000.00,-1000000.00,3725000.00,'unpaid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(41,46,41,'10-2026',2800000.00,420000.00,880000.00,-680000.00,3420000.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(42,47,42,'10-2026',2800000.00,437500.00,1040000.00,-840000.00,3437500.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(43,48,43,'10-2026',3100000.00,542500.00,1200000.00,-1000000.00,3842500.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(44,49,44,'10-2026',3100000.00,507500.00,1200000.00,-1000000.00,3807500.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(45,51,45,'10-2026',2900000.00,472500.00,1120000.00,-920000.00,3572500.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(46,52,46,'10-2026',2900000.00,472500.00,1120000.00,-920000.00,3572500.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(47,53,47,'10-2026',3200000.00,560000.00,1280000.00,-1080000.00,3960000.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(48,54,48,'10-2026',3200000.00,560000.00,1280000.00,-1080000.00,3960000.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(49,56,49,'10-2026',3000000.00,507500.00,1200000.00,-1000000.00,3707500.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(50,57,50,'10-2026',3000000.00,490000.00,1200000.00,-1000000.00,3690000.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(51,58,51,'10-2026',3300000.00,577500.00,1360000.00,-1160000.00,4077500.00,'unpaid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54'),(52,59,52,'10-2026',3300000.00,577500.00,1360000.00,-1160000.00,4077500.00,'paid','2026-11-10',NULL,NULL,NULL,'2026-11-04 16:56:54');
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
) ENGINE=InnoDB AUTO_INCREMENT=121 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `room_services`
--

LOCK TABLES `room_services` WRITE;
/*!40000 ALTER TABLE `room_services` DISABLE KEYS */;
INSERT INTO `room_services` VALUES (1,1,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(2,1,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(3,2,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(4,2,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(5,3,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(6,3,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(7,4,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(8,4,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(9,5,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(10,5,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(11,6,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(12,6,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(13,7,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(14,7,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(15,8,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(16,8,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(17,9,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(18,9,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(19,10,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(20,10,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(21,11,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(22,11,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(23,12,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(24,12,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(25,13,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(26,13,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(27,14,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(28,14,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(29,15,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(30,15,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(31,16,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(32,16,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(33,17,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(34,17,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(35,18,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(36,18,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(37,19,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(38,19,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(39,20,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(40,20,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(41,21,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(42,21,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(43,22,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(44,22,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(45,23,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(46,23,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(47,24,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(48,24,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(49,25,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(50,25,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(51,26,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(52,26,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(53,27,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(54,27,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(55,28,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(56,28,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(57,29,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(58,29,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(59,30,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(60,30,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(61,31,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(62,31,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(63,32,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(64,32,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(65,33,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(66,33,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(67,34,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(68,34,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(69,35,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(70,35,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(71,36,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(72,36,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(73,37,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(74,37,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(75,38,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(76,38,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(77,39,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(78,39,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(79,40,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(80,40,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(81,41,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(82,41,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(83,42,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(84,42,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(85,43,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(86,43,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(87,44,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(88,44,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(89,45,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(90,45,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(91,46,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(92,46,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(93,47,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(94,47,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(95,48,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(96,48,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(97,49,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(98,49,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(99,50,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(100,50,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(101,51,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(102,51,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(103,52,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(104,52,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(105,53,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(106,53,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(107,54,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(108,54,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(109,55,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(110,55,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(111,56,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(112,56,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(113,57,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(114,57,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(115,58,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(116,58,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(117,59,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(118,59,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01'),(119,60,'Internet / Wifi tốc độ cao',100000.00,'Phòng/tháng','2026-11-04 16:58:01'),(120,60,'Vệ sinh & Rác sinh hoạt',50000.00,'Phòng/tháng','2026-11-04 16:58:01');
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
  UNIQUE KEY `room_number` (`room_number`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rooms`
--

LOCK TABLES `rooms` WRITE;
/*!40000 ALTER TABLE `rooms` DISABLE KEYS */;
INSERT INTO `rooms` VALUES (1,1,'A101',3200000.00,'rented','Phòng tầng 1, diện tích 22m2, có gác lửng, ban công thoáng','2026-11-04 16:56:54'),(2,1,'A102',3200000.00,'rented','Phòng tầng 1, diện tích 22m2, khép kín, bình nóng lạnh','2026-11-04 16:56:54'),(3,1,'A103',3400000.00,'rented','Phòng tầng 1, diện tích 24m2, nội thất cơ bản, cửa sổ lớn','2026-11-04 16:56:54'),(4,1,'A104',3500000.00,'rented','Phòng tầng 1, diện tích 25m2, bếp riêng, máy giặt chung','2026-11-04 16:56:54'),(5,1,'A105',3500000.00,'rented','Phòng tầng 1 góc, 25m2, 2 cửa sổ thoáng mát','2026-11-04 16:56:54'),(6,1,'A201',3300000.00,'rented','Phòng tầng 2, 22m2, điều hòa inverter, tủ quần áo','2026-11-04 16:56:54'),(7,1,'A202',3300000.00,'rented','Phòng tầng 2, 22m2, ban công nhìn ra phố','2026-11-04 16:56:54'),(8,1,'A203',3500000.00,'rented','Phòng tầng 2, 24m2, full đồ giường nệm cao cấp','2026-11-04 16:56:54'),(9,1,'A204',3500000.00,'rented','Phòng tầng 2, 24m2, bếp riêng và hút mùi','2026-11-04 16:56:54'),(10,1,'A205',3300000.00,'available','Phòng tầng 2 trống, vừa sơn sửa sạch đẹp đón khách','2026-11-04 16:56:54'),(11,1,'A301',3400000.00,'rented','Phòng tầng 3, 23m2, ban công riêng phơi đồ','2026-11-04 16:56:54'),(12,1,'A302',3400000.00,'rented','Phòng tầng 3, 23m2, trang bị tủ lạnh, bàn làm việc','2026-11-04 16:56:54'),(13,1,'A303',3600000.00,'rented','Phòng tầng 3, 26m2, căn studio có vách kính ngăn bếp','2026-11-04 16:56:54'),(14,1,'A304',3600000.00,'rented','Phòng tầng 3, 26m2, căn góc 2 mặt thoáng','2026-11-04 16:56:54'),(15,1,'A305',3500000.00,'rented','Phòng tầng 3, 24m2, nội thất gỗ sồi cao cấp','2026-11-04 16:56:54'),(16,1,'A401',3600000.00,'rented','Phòng tầng 4, 25m2, thang máy êm, ban công view đẹp','2026-11-04 16:56:54'),(17,1,'A402',3600000.00,'rented','Phòng tầng 4, 25m2, khóa cửa vân tay thông minh','2026-11-04 16:56:54'),(18,1,'A403',3800000.00,'rented','Phòng tầng 4, 28m2, phòng ngủ tách biệt bếp','2026-11-04 16:56:54'),(19,1,'A404',3800000.00,'rented','Phòng tầng 4, 28m2, có máy giặt riêng trong phòng','2026-11-04 16:56:54'),(20,1,'A405',3500000.00,'maintenance','Phòng tầng 4, đang bảo dưỡng hệ thống điều hòa','2026-11-04 16:56:54'),(21,2,'B101',4200000.00,'rented','Căn hộ Studio tầng 1, 28m2, full nội thất thông minh','2026-11-04 16:56:54'),(22,2,'B102',4200000.00,'rented','Studio tầng 1, 28m2, sofa, smart tivi, bàn trà','2026-11-04 16:56:54'),(23,2,'B103',4500000.00,'rented','Studio tầng 1, 30m2, bếp từ đôi, lò vi sóng','2026-11-04 16:56:54'),(24,2,'B104',4500000.00,'rented','Studio tầng 1, 30m2, giếng trời đón sáng tự nhiên','2026-11-04 16:56:54'),(25,2,'B105',4000000.00,'available','Studio tầng 1 trống, nội thất mới 100%','2026-11-04 16:56:54'),(26,2,'B201',4300000.00,'rented','Studio tầng 2, 28m2, ban công riêng rộng rãi','2026-11-04 16:56:54'),(27,2,'B202',4300000.00,'rented','Studio tầng 2, 28m2, view công viên cây xanh','2026-11-04 16:56:54'),(28,2,'B203',4600000.00,'rented','Studio tầng 2, 32m2, 1 phòng ngủ 1 phòng khách','2026-11-04 16:56:54'),(29,2,'B204',4600000.00,'rented','Studio tầng 2, 32m2, ban công thoáng mát','2026-11-04 16:56:54'),(30,2,'B205',4400000.00,'rented','Studio tầng 2, 30m2, giường tầng cao cấp','2026-11-04 16:56:54'),(31,2,'B301',4500000.00,'rented','Studio tầng 3, 30m2, máy giặt sấy riêng','2026-11-04 16:56:54'),(32,2,'B302',4500000.00,'rented','Studio tầng 3, 30m2, tủ quần áo 4 cánh âm tường','2026-11-04 16:56:54'),(33,2,'B303',4800000.00,'rented','Căn hộ 1PN+1 tầng 3, 35m2, bếp đảo hiện đại','2026-11-04 16:56:54'),(34,2,'B304',4800000.00,'rented','Căn hộ 1PN+1 tầng 3, 35m2, ban công chạy dài','2026-11-04 16:56:54'),(35,2,'B305',4500000.00,'maintenance','Studio tầng 3, đang sơn lại tường và làm sàn gỗ','2026-11-04 16:56:54'),(36,2,'B401',4700000.00,'rented','Studio Penthouse tầng 4, 32m2, trần cao thoáng đãng','2026-11-04 16:56:54'),(37,2,'B402',4700000.00,'rented','Studio Penthouse tầng 4, 32m2, cửa kính panorama','2026-11-04 16:56:54'),(38,2,'B403',5000000.00,'rented','Căn hộ 2 phòng ngủ tầng 4, 40m2, phù hợp gia đình trẻ','2026-11-04 16:56:54'),(39,2,'B404',5000000.00,'rented','Căn hộ 2 phòng ngủ tầng 4, 40m2, full tiện nghi','2026-11-04 16:56:54'),(40,2,'B405',4600000.00,'available','Studio tầng 4 trống, chuẩn bị bàn giao khách mới','2026-11-04 16:56:54'),(41,3,'C101',2700000.00,'rented','Phòng tầng 1, 20m2, gác lửng bê tông kiên cố','2026-11-04 16:56:54'),(42,3,'C102',2700000.00,'rented','Phòng tầng 1, 20m2, khép kín, an ninh có camera','2026-11-04 16:56:54'),(43,3,'C103',2900000.00,'rented','Phòng tầng 1, 22m2, cửa sổ hành lang thoáng','2026-11-04 16:56:54'),(44,3,'C104',3000000.00,'rented','Phòng tầng 1, 24m2, có bồn rửa bát và kệ để đồ','2026-11-04 16:56:54'),(45,3,'C105',3000000.00,'rented','Phòng tầng 1, 24m2, thích hợp nhóm 2-3 bạn','2026-11-04 16:56:54'),(46,3,'C201',2800000.00,'rented','Phòng tầng 2, 20m2, có điều hòa và bình nóng lạnh','2026-11-04 16:56:54'),(47,3,'C202',2800000.00,'rented','Phòng tầng 2, 20m2, sạch sẽ, thoáng mát','2026-11-04 16:56:54'),(48,3,'C203',3100000.00,'rented','Phòng tầng 2, 23m2, ban công riêng nhìn ra phố','2026-11-04 16:56:54'),(49,3,'C204',3100000.00,'rented','Phòng tầng 2, 23m2, gác xếp đồ tiện lợi','2026-11-04 16:56:54'),(50,3,'C205',2800000.00,'available','Phòng tầng 2 trống, giá sinh viên ưu đãi','2026-11-04 16:56:54'),(51,3,'C301',2900000.00,'rented','Phòng tầng 3, 21m2, cửa sổ trời thông thoáng','2026-11-04 16:56:54'),(52,3,'C302',2900000.00,'rented','Phòng tầng 3, 21m2, nội thất cơ bản sẵn ở','2026-11-04 16:56:54'),(53,3,'C303',3200000.00,'rented','Phòng tầng 3, 24m2, gác đúc, quạt trần, điều hòa','2026-11-04 16:56:54'),(54,3,'C304',3200000.00,'rented','Phòng tầng 3, 24m2, căn góc yên tĩnh','2026-11-04 16:56:54'),(55,3,'C305',3000000.00,'maintenance','Phòng tầng 3, đang thay mới thiết bị vệ sinh','2026-11-04 16:56:54'),(56,3,'C401',3000000.00,'rented','Phòng tầng 4, 22m2, sân phơi rộng rãi ngay cạnh','2026-11-04 16:56:54'),(57,3,'C402',3000000.00,'rented','Phòng tầng 4, 22m2, giá thuê tiết kiệm','2026-11-04 16:56:54'),(58,3,'C403',3300000.00,'rented','Phòng tầng 4, 25m2, 2 cửa sổ lộng gió','2026-11-04 16:56:54'),(59,3,'C404',3300000.00,'rented','Phòng tầng 4, 25m2, đầy đủ nội thất chỉ việc xách vali đến','2026-11-04 16:56:54'),(60,3,'C405',2900000.00,'available','Phòng tầng 4 trống, hỗ trợ dọn vào ngay','2026-11-04 16:56:54');
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
INSERT INTO `services` VALUES (1,'Tiền điện sinh hoạt',3500.00,'kWh',1,'2026-11-04 16:56:54'),(2,'Tiền nước sinh hoạt',80000.00,'Người',1,'2026-11-04 16:56:54'),(3,'Internet / Wifi tốc độ cao',50000.00,'Phòng',1,'2026-11-04 16:56:54'),(4,'Rác sinh hoạt & Môi trường',30000.00,'Phòng',1,'2026-11-04 16:56:54'),(5,'Vệ sinh hành lang & Khu chung',20000.00,'Người',1,'2026-11-04 16:56:54'),(6,'Phí dịch vụ thang máy',30000.00,'Người',1,'2026-11-04 16:56:54'),(7,'Gửi xe máy tầng hầm',70000.00,'Xe',1,'2026-11-04 16:56:54'),(8,'Giặt ủi sấy chung',50000.00,'Người',1,'2026-11-04 16:56:54');
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
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `contacted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenant_contact_leads`
--

LOCK TABLES `tenant_contact_leads` WRITE;
/*!40000 ALTER TABLE `tenant_contact_leads` DISABLE KEYS */;
INSERT INTO `tenant_contact_leads` VALUES (1,'Nguyễn Văn Hùng','0981234567','Phòng A105 - Tòa A',5,'08:30 sáng Thứ 7','Cần xem phòng Studio có ban công, dọn vào giữa tháng 11','pending','2026-11-04 10:15:00',NULL),(2,'Trần Thu Hà','0918765432','Phòng B205 - Tòa B',25,'17:30 chiều nay','Muốn thuê 1 năm, hỏi kỹ chính sách cọc và gửi xe máy','pending','2026-11-04 11:20:00',NULL),(3,'Lê Quang Minh','0932112233','Phòng C305 - Tòa C',45,'10:00 sáng mai','Sinh viên ĐH KHTN cần ở ghép 2 bạn','pending','2026-11-04 14:05:00',NULL),(4,'Phạm Thảo Linh','0977889900','Phòng A305 - Tòa A',15,'09:00 Chủ Nhật','Hẹn xem phòng thực tế và chốt hợp đồng giữ chỗ','contacted','2026-11-03 09:00:00','2026-11-03 14:30:00'),(5,'Đỗ Thành Đạt','0905667788','Phòng B105 - Tòa B',20,'18:00 chiều Thứ 6','Đã tư vấn qua điện thoại, gửi video phòng thực tế','contacted','2026-11-02 16:40:00','2026-11-02 17:10:00');
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
  KEY `tenant_id` (`tenant_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenant_requests`
--

LOCK TABLES `tenant_requests` WRITE;
/*!40000 ALTER TABLE `tenant_requests` DISABLE KEYS */;
INSERT INTO `tenant_requests` VALUES (1,5,'profile_update','phone','0901001101','0988776655','Em vừa bị mất điện thoại và sim cũ, nhờ BQL cập nhật số mới để nhận thông báo.','pending',NULL,'2026-11-01 02:30:00','2026-11-04 16:56:54'),(2,6,'profile_update','email','huong.le@gmail.com','huong.le.ftu@gmail.com','Đổi sang email công việc để tiện nhận hóa đơn điện tử hàng tháng.','approved','Đã xác thực và cập nhật email mới cho bạn.','2026-10-28 07:15:00','2026-11-04 16:56:54'),(3,7,'profile_update','co_tenant','Người ở cùng','Đăng ký thêm bạn cùng phòng: Phạm Minh Hùng (CCCD: 001201012345)','Bạn học cùng trường chuyển đến ở ghép.','pending',NULL,'2026-11-02 09:40:00','2026-11-04 16:56:54'),(4,8,'profile_update','full_name','Phạm Thu Trang','Phạm Quỳnh Trang','Sửa lại tên chính xác theo căn cước công dân mới làm lại.','rejected','Vui lòng mang CCCD gốc qua văn phòng quản lý để đối chiếu.','2026-10-25 03:20:00','2026-11-04 16:56:54');
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
  `building_id` int DEFAULT NULL,
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
  PRIMARY KEY (`id`),
  KEY `month_year` (`month_year`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `third_party_expenses`
--

LOCK TABLES `third_party_expenses` WRITE;
/*!40000 ALTER TABLE `third_party_expenses` DISABLE KEYS */;
INSERT INTO `third_party_expenses` VALUES (1,'10-2026',1,'internet','Cáp quang Internet Tòa A','VNPT Cầu Giấy',1.00,'Tháng',1500000.00,1500000.00,1800000.00,'paid','2026-10-05','Gói cáp quang doanh nghiệp tốc độ 500Mbps','2026-11-04 16:56:54'),(2,'10-2026',2,'internet','Cáp quang Internet Tòa B','Viettel Đống Đa',1.00,'Tháng',1500000.00,1500000.00,1800000.00,'paid','2026-10-05','Gói internet doanh nghiệp FTTH','2026-11-04 16:56:54'),(3,'10-2026',3,'internet','Cáp quang Internet Tòa C','FPT Telecom Thanh Xuân',1.00,'Tháng',1500000.00,1500000.00,1800000.00,'paid','2026-10-05','Gói Super 500','2026-11-04 16:56:54'),(4,'10-2026',NULL,'cleaning','Dịch vụ vệ sinh hành lang & khu chung','Công ty Môi Trường Xanh',3.00,'Tòa',1200000.00,3600000.00,4200000.00,'paid','2026-10-10','Vệ sinh định kỳ thứ 2, 4, 6 hàng tuần','2026-11-04 16:56:54'),(5,'10-2026',NULL,'elevator','Bảo trì định kỳ thang máy','Thang máy MitsuLift',3.00,'Tháng',1000000.00,3000000.00,3500000.00,'paid','2026-10-15','Bảo dưỡng buồng thang, bôi trơn ray dẫn','2026-11-04 16:56:54'),(6,'10-2026',NULL,'garbage','Thu gom rác thải sinh hoạt','Hợp tác xã Vệ sinh Môi trường',3.00,'Tháng',600000.00,1800000.00,2100000.00,'paid','2026-10-08','Thu gom rác mỗi ngày lúc 19h00','2026-11-04 16:56:54');
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
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','hash','Nguyễn Tuấn Dũng','0901234567',NULL,NULL,'Nam','admin@staymate.vn',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'admin',NULL,'2026-11-04 16:56:54'),(2,'quanly_a','hash','Nguyễn Văn Quân','0912345678',NULL,NULL,'Nam','quan.nguyen@staymate.vn',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'manager',NULL,'2026-11-04 16:56:54'),(3,'quanly_b','hash','Trần Thị Mai Lan','0923456789',NULL,NULL,'Nam','lan.tran@staymate.vn',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'manager',NULL,'2026-11-04 16:56:54'),(4,'quanly_c','hash','Lê Hoàng Nam','0934567890',NULL,NULL,'Nam','nam.le@staymate.vn',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'manager',NULL,'2026-11-04 16:56:54'),(5,'kh_a101','hash','Nguyễn Tiến Đạt','0901001101',NULL,NULL,'Nam','dat.nguyen@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(6,'kh_a102','hash','Lê Thị Thu Hương','0901001102',NULL,NULL,'Nam','huong.le@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(7,'kh_a103','hash','Trần Hoàng Long','0901001103',NULL,NULL,'Nam','long.tran@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(8,'kh_a104','hash','Phạm Thu Trang','0901001104',NULL,NULL,'Nam','trang.pham@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(9,'kh_a105','hash','Vũ Minh Tuấn','0901001105',NULL,NULL,'Nam','tuan.vu@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(10,'kh_a201','hash','Đặng Kim Thủy','0901001201',NULL,NULL,'Nam','thuy.dang@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(11,'kh_a202','hash','Bùi Xuân Bách','0901001202',NULL,NULL,'Nam','bach.bui@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(12,'kh_a203','hash','Hồ Quang Hiếu','0901001203',NULL,NULL,'Nam','hieu.ho@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(13,'kh_a204','hash','Ngô Phương Lan','0901001204',NULL,NULL,'Nam','lan.ngo@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(14,'kh_a301','hash','Đinh Trọng Hưng','0901001301',NULL,NULL,'Nam','hung.dinh@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(15,'kh_a302','hash','Hoàng Thanh Tùng','0901001302',NULL,NULL,'Nam','tung.hoang@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(16,'kh_a303','hash','Đỗ Thùy Linh','0901001303',NULL,NULL,'Nam','linh.do@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(17,'kh_a304','hash','Nguyễn Hải Yến','0901001304',NULL,NULL,'Nam','yen.nguyen@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(18,'kh_a305','hash','Phạm Quốc Huy','0901001305',NULL,NULL,'Nam','huy.pham@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(19,'kh_a401','hash','Trịnh Minh Đức','0901001401',NULL,NULL,'Nam','duc.trinh@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(20,'kh_a402','hash','Nguyễn Bích Ngọc','0901001402',NULL,NULL,'Nam','ngoc.nguyen@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(21,'kh_a403','hash','Vũ Đình Trọng','0901001403',NULL,NULL,'Nam','trong.vu@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(22,'kh_a404','hash','Trần Văn Khoa','0901001404',NULL,NULL,'Nam','khoa.tran@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(23,'kh_b101','hash','Mai Thùy Dung','0902002101',NULL,NULL,'Nam','dung.mai@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(24,'kh_b102','hash','Lê Khánh An','0902002102',NULL,NULL,'Nam','an.le@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(25,'kh_b103','hash','Nguyễn Nhật Minh','0902002103',NULL,NULL,'Nam','minh.nhat@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(26,'kh_b104','hash','Phan Bảo Châu','0902002104',NULL,NULL,'Nam','chau.phan@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(27,'kh_b201','hash','Đỗ Gia Hưng','0902002201',NULL,NULL,'Nam','hung.do@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(28,'kh_b202','hash','Bùi Mai Phương','0902002202',NULL,NULL,'Nam','phuong.bui@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(29,'kh_b203','hash','Trương Tuấn Kiệt','0902002203',NULL,NULL,'Nam','kiet.truong@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(30,'kh_b204','hash','Hoàng Thảo Vy','0902002204',NULL,NULL,'Nam','vy.hoang@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(31,'kh_b205','hash','Vũ Thành Đạt','0902002205',NULL,NULL,'Nam','dat.vu@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(32,'kh_b301','hash','Phạm Thanh Hằng','0902002301',NULL,NULL,'Nam','hang.pham@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(33,'kh_b302','hash','Nguyễn Đức Phúc','0902002302',NULL,NULL,'Nam','phuc.nguyen@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(34,'kh_b303','hash','Đặng Quỳnh Anh','0902002303',NULL,NULL,'Nam','anh.dang@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(35,'kh_b304','hash','Lê Minh Khang','0902002304',NULL,NULL,'Nam','khang.le@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(36,'kh_b401','hash','Trần Thảo Linh','0902002401',NULL,NULL,'Nam','linh.tran@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(37,'kh_b402','hash','Đào Quang Huy','0902002402',NULL,NULL,'Nam','huy.dao@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(38,'kh_b403','hash','Nguyễn Lan Hương','0902002403',NULL,NULL,'Nam','huong.lan@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(39,'kh_b404','hash','Phan Quốc Toàn','0902002404',NULL,NULL,'Nam','toan.phan@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(40,'kh_c101','hash','Hoàng Thị Ngọc Bích','0903003101',NULL,NULL,'Nam','bich.hoang@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(41,'kh_c102','hash','Dương Minh Trí','0903003102',NULL,NULL,'Nam','tri.duong@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(42,'kh_c103','hash','Ngô Ánh Dương','0903003103',NULL,NULL,'Nam','duong.ngo@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(43,'kh_c104','hash','Vũ Diệu Linh','0903003104',NULL,NULL,'Nam','linh.vu@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(44,'kh_c105','hash','Trần Tuấn Anh','0903003105',NULL,NULL,'Nam','anh.tran@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(45,'kh_c201','hash','Nguyễn Phương Thảo','0903003201',NULL,NULL,'Nam','thao.nguyen@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(46,'kh_c202','hash','Bùi Đức Mạnh','0903003202',NULL,NULL,'Nam','manh.bui@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(47,'kh_c203','hash','Lê Hoài Nam','0903003203',NULL,NULL,'Nam','nam.hoai@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(48,'kh_c204','hash','Đỗ Bảo Ngọc','0903003204',NULL,NULL,'Nam','ngoc.bao@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(49,'kh_c301','hash','Trịnh Văn Quyết','0903003301',NULL,NULL,'Nam','quyet.trinh@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(50,'kh_c302','hash','Phạm Hà My','0903003302',NULL,NULL,'Nam','my.pham@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(51,'kh_c303','hash','Nguyễn Trọng Tín','0903003303',NULL,NULL,'Nam','tin.nguyen@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(52,'kh_c304','hash','Hoàng Thúy Hằng','0903003304',NULL,NULL,'Nam','hang.thuy@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(53,'kh_c401','hash','Đinh Hoàng Việt','0903003401',NULL,NULL,'Nam','viet.dinh@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(54,'kh_c402','hash','Chu Minh Châu','0903003402',NULL,NULL,'Nam','chau.chu@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(55,'kh_c403','hash','Vũ Hải Đăng','0903003403',NULL,NULL,'Nam','dang.vu@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54'),(56,'kh_c404','hash','Nguyễn Quỳnh Như','0903003404',NULL,NULL,'Nam','nhu.nguyen@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 16:56:54');
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
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `utilities_readings`
--

LOCK TABLES `utilities_readings` WRITE;
/*!40000 ALTER TABLE `utilities_readings` DISABLE KEYS */;
INSERT INTO `utilities_readings` VALUES (1,1,'10-2026',120,260,10,24,'2026-11-04 16:56:54'),(2,2,'10-2026',150,290,15,29,'2026-11-04 16:56:54'),(3,3,'10-2026',200,335,18,34,'2026-11-04 16:56:54'),(4,4,'10-2026',180,320,22,38,'2026-11-04 16:56:54'),(5,5,'10-2026',140,270,14,28,'2026-11-04 16:56:54'),(6,6,'10-2026',110,230,12,22,'2026-11-04 16:56:54'),(7,7,'10-2026',130,255,16,28,'2026-11-04 16:56:54'),(8,8,'10-2026',210,360,20,35,'2026-11-04 16:56:54'),(9,9,'10-2026',190,340,17,32,'2026-11-04 16:56:54'),(10,11,'10-2026',160,300,15,30,'2026-11-04 16:56:54'),(11,12,'10-2026',145,275,14,27,'2026-11-04 16:56:54'),(12,13,'10-2026',220,380,22,40,'2026-11-04 16:56:54'),(13,14,'10-2026',205,360,19,36,'2026-11-04 16:56:54'),(14,15,'10-2026',180,310,16,30,'2026-11-04 16:56:54'),(15,16,'10-2026',195,345,18,34,'2026-11-04 16:56:54'),(16,17,'10-2026',170,315,15,29,'2026-11-04 16:56:54'),(17,18,'10-2026',240,410,23,42,'2026-11-04 16:56:54'),(18,19,'10-2026',230,395,21,39,'2026-11-04 16:56:54'),(19,21,'10-2026',300,480,25,43,'2026-11-04 16:56:54'),(20,22,'10-2026',320,510,28,47,'2026-11-04 16:56:54'),(21,23,'10-2026',350,550,30,52,'2026-11-04 16:56:54'),(22,24,'10-2026',310,490,26,44,'2026-11-04 16:56:54'),(23,26,'10-2026',280,440,24,40,'2026-11-04 16:56:54'),(24,27,'10-2026',290,460,25,42,'2026-11-04 16:56:54'),(25,28,'10-2026',340,530,29,49,'2026-11-04 16:56:54'),(26,29,'10-2026',330,520,27,46,'2026-11-04 16:56:54'),(27,30,'10-2026',305,475,26,44,'2026-11-04 16:56:54'),(28,31,'10-2026',360,560,31,53,'2026-11-04 16:56:54'),(29,32,'10-2026',350,545,30,51,'2026-11-04 16:56:54'),(30,33,'10-2026',400,620,35,60,'2026-11-04 16:56:54'),(31,34,'10-2026',390,605,34,58,'2026-11-04 16:56:54'),(32,36,'10-2026',370,570,32,54,'2026-11-04 16:56:54'),(33,37,'10-2026',380,590,33,56,'2026-11-04 16:56:54'),(34,38,'10-2026',420,650,38,66,'2026-11-04 16:56:54'),(35,39,'10-2026',410,640,37,64,'2026-11-04 16:56:54'),(36,41,'10-2026',70,190,8,19,'2026-11-04 16:56:54'),(37,42,'10-2026',80,205,9,21,'2026-11-04 16:56:54'),(38,43,'10-2026',90,220,10,23,'2026-11-04 16:56:54'),(39,44,'10-2026',100,240,11,25,'2026-11-04 16:56:54'),(40,45,'10-2026',110,260,12,27,'2026-11-04 16:56:54'),(41,46,'10-2026',75,195,8,19,'2026-11-04 16:56:54'),(42,47,'10-2026',85,210,9,22,'2026-11-04 16:56:54'),(43,48,'10-2026',115,270,13,28,'2026-11-04 16:56:54'),(44,49,'10-2026',105,250,11,26,'2026-11-04 16:56:54'),(45,51,'10-2026',95,230,10,24,'2026-11-04 16:56:54'),(46,52,'10-2026',90,225,9,23,'2026-11-04 16:56:54'),(47,53,'10-2026',120,280,14,30,'2026-11-04 16:56:54'),(48,54,'10-2026',115,275,13,29,'2026-11-04 16:56:54'),(49,56,'10-2026',100,245,11,26,'2026-11-04 16:56:54'),(50,57,'10-2026',95,235,10,25,'2026-11-04 16:56:54'),(51,58,'10-2026',125,290,15,32,'2026-11-04 16:56:54'),(52,59,'10-2026',120,285,14,31,'2026-11-04 16:56:54');
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

-- Dump completed on 2026-11-05  1:08:55
