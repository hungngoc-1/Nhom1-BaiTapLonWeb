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
  PRIMARY KEY (`id`),
  KEY `idx_buildings_manager` (`manager_id`),
  CONSTRAINT `buildings_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `buildings`
--

LOCK TABLES `buildings` WRITE;
/*!40000 ALTER TABLE `buildings` DISABLE KEYS */;
INSERT INTO `buildings` VALUES (1,'Tòa A - StayMate Sunrise','Số 128 Cầu Giấy, Cầu Giấy, Hà Nội',2,'2026-11-04 05:47:26'),(2,'Tòa B - StayMate Emerald','Số 45 Nguyễn Chí Thanh, Ba Đình, Hà Nội',3,'2026-11-04 05:47:26'),(3,'Tòa C - StayMate Horizon','Số 88 Nguyễn Trãi, Thanh Xuân, Hà Nội',4,'2026-11-04 05:47:26');
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
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contract_code` (`contract_code`),
  KEY `idx_contracts_room` (`room_id`),
  KEY `idx_contracts_tenant` (`tenant_id`),
  KEY `idx_contracts_status` (`status`),
  KEY `parent_contract_id` (`parent_contract_id`),
  CONSTRAINT `contracts_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `contracts_ibfk_2` FOREIGN KEY (`tenant_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `contracts_ibfk_3` FOREIGN KEY (`parent_contract_id`) REFERENCES `contracts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contracts`
--

LOCK TABLES `contracts` WRITE;
/*!40000 ALTER TABLE `contracts` DISABLE KEYS */;
INSERT INTO `contracts` VALUES (1,'HD-2026-001','official_lease',NULL,1,5,'2026-01-01','2026-12-31',3200000.00,NULL,100,'held',3200000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(2,'HD-2026-002','official_lease',NULL,2,6,'2026-02-15','2026-11-20',3200000.00,NULL,100,'held',3200000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(3,'HD-2026-003','official_lease',NULL,3,7,'2026-03-01','2027-03-01',3400000.00,NULL,100,'held',3400000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(4,'HD-2026-004','official_lease',NULL,4,8,'2026-04-10','2026-11-28',3500000.00,NULL,100,'held',3500000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(5,'HD-2026-005','official_lease',NULL,5,9,'2026-05-01','2027-05-01',3500000.00,NULL,100,'held',3500000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(6,'HD-2026-006','official_lease',NULL,6,10,'2026-06-01','2027-06-01',3300000.00,NULL,100,'held',3300000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(7,'HD-2026-007','official_lease',NULL,7,11,'2026-07-01','2026-12-15',3300000.00,NULL,100,'held',3300000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(8,'HD-2026-008','official_lease',NULL,8,12,'2026-01-10','2027-01-10',3500000.00,NULL,100,'held',3500000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(9,'HD-2026-009','official_lease',NULL,9,13,'2026-02-01','2027-02-01',3500000.00,NULL,100,'held',3500000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(10,'HD-2026-010','official_lease',NULL,11,14,'2026-03-01','2027-03-01',3400000.00,NULL,100,'held',3400000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(11,'HD-2026-011','official_lease',NULL,12,15,'2026-04-01','2026-11-18',3400000.00,NULL,100,'held',3400000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(12,'HD-2026-012','official_lease',NULL,13,16,'2026-05-01','2027-05-01',3600000.00,NULL,100,'held',3600000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(13,'HD-2026-013','official_lease',NULL,14,17,'2026-06-01','2027-06-01',3600000.00,NULL,100,'held',3600000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(14,'HD-2026-014','official_lease',NULL,15,18,'2026-07-01','2027-07-01',3500000.00,NULL,100,'held',3500000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(15,'HD-2026-015','official_lease',NULL,16,19,'2026-01-15','2027-01-15',3600000.00,NULL,100,'held',3600000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(16,'HD-2026-016','official_lease',NULL,17,20,'2026-02-15','2027-02-15',3600000.00,NULL,100,'held',3600000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(17,'HD-2026-017','official_lease',NULL,18,21,'2026-03-15','2027-03-15',3800000.00,NULL,100,'held',3800000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(18,'HD-2026-018','official_lease',NULL,19,22,'2026-04-15','2027-04-15',3800000.00,NULL,100,'held',3800000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(19,'HD-2026-019','official_lease',NULL,21,23,'2026-01-01','2027-01-01',4200000.00,NULL,100,'held',4200000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(20,'HD-2026-020','official_lease',NULL,22,24,'2026-02-01','2027-02-01',4200000.00,NULL,100,'held',4200000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(21,'HD-2026-021','official_lease',NULL,23,25,'2026-03-01','2027-03-01',4500000.00,NULL,100,'held',4500000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(22,'HD-2026-022','official_lease',NULL,24,26,'2026-04-01','2026-11-22',4500000.00,NULL,100,'held',4500000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(23,'HD-2026-023','official_lease',NULL,26,27,'2026-05-01','2027-05-01',4300000.00,NULL,100,'held',4300000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(24,'HD-2026-024','official_lease',NULL,27,28,'2026-06-01','2027-06-01',4300000.00,NULL,100,'held',4300000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(25,'HD-2026-025','official_lease',NULL,28,29,'2026-07-01','2027-07-01',4600000.00,NULL,100,'held',4600000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(26,'HD-2026-026','official_lease',NULL,29,30,'2026-01-10','2027-01-10',4600000.00,NULL,100,'held',4600000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(27,'HD-2026-027','official_lease',NULL,30,31,'2026-02-10','2027-02-10',4400000.00,NULL,100,'held',4400000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(28,'HD-2026-028','official_lease',NULL,31,32,'2026-03-10','2027-03-10',4500000.00,NULL,100,'held',4500000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(29,'HD-2026-029','official_lease',NULL,32,33,'2026-04-10','2027-04-10',4500000.00,NULL,100,'held',4500000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(30,'HD-2026-030','official_lease',NULL,33,34,'2026-05-10','2027-05-10',4800000.00,NULL,100,'held',4800000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(31,'HD-2026-031','official_lease',NULL,34,35,'2026-06-10','2027-06-10',4800000.00,NULL,100,'held',4800000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(32,'HD-2026-032','official_lease',NULL,36,36,'2026-01-20','2027-01-20',4700000.00,NULL,100,'held',4700000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(33,'HD-2026-033','official_lease',NULL,37,37,'2026-02-20','2027-02-20',4700000.00,NULL,100,'held',4700000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(34,'HD-2026-034','official_lease',NULL,38,38,'2026-03-20','2027-03-20',5000000.00,NULL,100,'held',5000000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(35,'HD-2026-035','official_lease',NULL,39,39,'2026-04-20','2027-04-20',5000000.00,NULL,100,'held',5000000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(36,'HD-2026-036','official_lease',NULL,41,40,'2026-01-01','2027-01-01',2700000.00,NULL,100,'held',2700000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(37,'HD-2026-037','official_lease',NULL,42,41,'2026-02-01','2027-02-01',2700000.00,NULL,100,'held',2700000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(38,'HD-2026-038','official_lease',NULL,43,42,'2026-03-01','2027-03-01',2900000.00,NULL,100,'held',2900000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(39,'HD-2026-039','official_lease',NULL,44,43,'2026-04-01','2027-04-01',3000000.00,NULL,100,'held',3000000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(40,'HD-2026-040','official_lease',NULL,45,44,'2026-05-01','2027-05-01',3000000.00,NULL,100,'held',3000000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(41,'HD-2026-041','official_lease',NULL,46,45,'2026-06-01','2027-06-01',2800000.00,NULL,100,'held',2800000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(42,'HD-2026-042','official_lease',NULL,47,46,'2026-07-01','2027-07-01',2800000.00,NULL,100,'held',2800000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(43,'HD-2026-043','official_lease',NULL,48,47,'2026-01-15','2027-01-15',3100000.00,NULL,100,'held',3100000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(44,'HD-2026-044','official_lease',NULL,49,48,'2026-02-15','2027-02-15',3100000.00,NULL,100,'held',3100000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(45,'HD-2026-045','official_lease',NULL,51,49,'2026-03-15','2027-03-15',2900000.00,NULL,100,'held',2900000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(46,'HD-2026-046','official_lease',NULL,52,50,'2026-04-15','2027-04-15',2900000.00,NULL,100,'held',2900000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(47,'HD-2026-047','official_lease',NULL,53,51,'2026-05-15','2027-05-15',3200000.00,NULL,100,'held',3200000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(48,'HD-2026-048','official_lease',NULL,54,52,'2026-06-15','2027-06-15',3200000.00,NULL,100,'held',3200000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(49,'HD-2026-049','official_lease',NULL,56,53,'2026-01-25','2027-01-25',3000000.00,NULL,100,'held',3000000.00,0,NULL,3500.00,20000.00,150000.00,'paper','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(50,'HD-2026-050','official_lease',NULL,57,54,'2026-02-25','2027-02-25',3000000.00,NULL,100,'held',3000000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(51,'HD-2026-051','official_lease',NULL,58,55,'2026-03-25','2027-03-25',3300000.00,NULL,100,'held',3300000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26'),(52,'HD-2026-052','official_lease',NULL,59,56,'2026-04-25','2027-04-25',3300000.00,NULL,100,'held',3300000.00,0,NULL,3500.00,20000.00,150000.00,'online','pending',NULL,NULL,NULL,NULL,'none',NULL,NULL,NULL,'active','2026-11-04 05:47:26');
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
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_details`
--

LOCK TABLES `invoice_details` WRITE;
/*!40000 ALTER TABLE `invoice_details` DISABLE KEYS */;
INSERT INTO `invoice_details` VALUES (1,1,NULL,1.00,3200000.00,3200000.00),(2,1,1,140.00,3500.00,490000.00),(3,1,2,1.00,80000.00,80000.00),(4,1,3,1.00,50000.00,50000.00),(5,1,4,1.00,30000.00,30000.00),(6,1,5,1.00,20000.00,20000.00),(7,1,6,1.00,20000.00,20000.00),(8,2,NULL,1.00,3200000.00,3200000.00),(9,2,1,140.00,3500.00,490000.00),(10,2,2,1.00,80000.00,80000.00),(11,2,3,1.00,50000.00,50000.00),(12,2,4,1.00,30000.00,30000.00),(13,2,5,1.00,20000.00,20000.00),(14,2,6,1.00,20000.00,20000.00),(15,3,NULL,1.00,3400000.00,3400000.00),(16,3,1,135.00,3500.00,472500.00),(17,3,2,2.00,80000.00,160000.00),(18,3,3,1.00,50000.00,50000.00),(19,3,4,1.00,30000.00,30000.00),(20,3,5,2.00,20000.00,40000.00),(21,3,6,1.00,20000.00,20000.00),(22,4,NULL,1.00,3500000.00,3500000.00),(23,4,1,140.00,3500.00,490000.00),(24,4,2,2.00,80000.00,160000.00),(25,4,3,1.00,50000.00,50000.00),(26,4,4,1.00,30000.00,30000.00),(27,4,5,2.00,20000.00,40000.00),(28,4,6,1.00,20000.00,20000.00),(29,19,NULL,1.00,4200000.00,4200000.00),(30,19,1,180.00,3500.00,630000.00),(31,19,2,1.00,80000.00,80000.00),(32,19,3,1.00,50000.00,50000.00),(33,19,4,1.00,30000.00,30000.00),(34,19,5,1.00,20000.00,20000.00),(35,19,6,1.00,20000.00,20000.00),(36,20,NULL,1.00,4200000.00,4200000.00),(37,20,1,190.00,3500.00,665000.00),(38,20,2,1.00,80000.00,80000.00),(39,20,3,1.00,50000.00,50000.00),(40,20,4,1.00,30000.00,30000.00),(41,20,5,1.00,20000.00,20000.00),(42,20,6,1.00,20000.00,20000.00);
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
  KEY `idx_invoices_room` (`room_id`),
  KEY `idx_invoices_contract` (`contract_id`),
  KEY `idx_invoices_status` (`status`),
  CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
INSERT INTO `invoices` VALUES (1,1,1,'10-2026',0.00,0.00,0.00,0.00,3525000.00,'paid',NULL,NULL,NULL,'2026-11-04 05:47:26'),(2,2,2,'10-2026',0.00,0.00,0.00,0.00,3655000.00,'unpaid',NULL,NULL,NULL,'2026-11-04 05:47:26'),(3,3,3,'10-2026',0.00,0.00,0.00,0.00,3585000.00,'paid',NULL,NULL,NULL,'2026-11-04 05:47:26'),(4,4,4,'10-2026',0.00,0.00,0.00,0.00,4095000.00,'paid',NULL,NULL,NULL,'2026-11-04 05:47:26'),(5,5,5,'10-2026',0.00,0.00,0.00,0.00,3885000.00,'paid',NULL,NULL,NULL,'2026-11-04 05:47:26'),(6,6,6,'10-2026',0.00,0.00,0.00,0.00,3350000.00,'paid',NULL,NULL,NULL,'2026-11-04 05:47:26'),(7,7,7,'10-2026',0.00,0.00,0.00,0.00,3455000.00,'unpaid',NULL,NULL,NULL,'2026-11-04 05:47:26'),(8,9,8,'10-2026',0.00,0.00,0.00,0.00,4700000.00,'paid',NULL,NULL,NULL,'2026-11-04 05:47:26'),(9,10,9,'10-2026',0.00,0.00,0.00,0.00,4560000.00,'paid',NULL,NULL,NULL,'2026-11-04 05:47:26'),(10,11,10,'10-2026',0.00,0.00,0.00,0.00,4655000.00,'unpaid',NULL,NULL,NULL,'2026-11-04 05:47:26'),(11,12,11,'10-2026',0.00,0.00,0.00,0.00,5095000.00,'paid',NULL,NULL,NULL,'2026-11-04 05:47:26'),(12,13,12,'10-2026',0.00,0.00,0.00,0.00,5025000.00,'paid',NULL,NULL,NULL,'2026-11-04 05:47:26'),(13,14,13,'10-2026',0.00,0.00,0.00,0.00,4490000.00,'paid',NULL,NULL,NULL,'2026-11-04 05:47:26'),(14,15,14,'10-2026',0.00,0.00,0.00,0.00,4420000.00,'unpaid',NULL,NULL,NULL,'2026-11-04 05:47:26'),(15,17,15,'10-2026',0.00,0.00,0.00,0.00,2745000.00,'paid',NULL,NULL,NULL,'2026-11-04 05:47:26'),(16,18,16,'10-2026',0.00,0.00,0.00,0.00,2780000.00,'paid',NULL,NULL,NULL,'2026-11-04 05:47:26'),(17,19,17,'10-2026',0.00,0.00,0.00,0.00,3115000.00,'paid',NULL,NULL,NULL,'2026-11-04 05:47:26'),(18,20,18,'10-2026',0.00,0.00,0.00,0.00,3350000.00,'paid',NULL,NULL,NULL,'2026-11-04 05:47:26'),(19,21,19,'10-2026',0.00,0.00,0.00,0.00,3280000.00,'unpaid',NULL,NULL,NULL,'2026-11-04 05:47:26'),(20,22,20,'2026-10',0.00,0.00,0.00,0.00,2780000.00,'paid',NULL,NULL,NULL,'2026-11-04 05:47:26');
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
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'available',
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_number` (`room_number`),
  KEY `idx_rooms_building` (`building_id`),
  KEY `idx_rooms_status` (`status`),
  CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rooms`
--

LOCK TABLES `rooms` WRITE;
/*!40000 ALTER TABLE `rooms` DISABLE KEYS */;
INSERT INTO `rooms` VALUES (1,1,'A101',3200000.00,'rented','Phòng tầng 1, diện tích 22m2, có gác lửng, ban công thoáng','2026-11-04 05:47:26'),(2,1,'A102',3200000.00,'rented','Phòng tầng 1, diện tích 22m2, khép kín, bình nóng lạnh','2026-11-04 05:47:26'),(3,1,'A103',3400000.00,'rented','Phòng tầng 1, diện tích 24m2, nội thất cơ bản, cửa sổ lớn','2026-11-04 05:47:26'),(4,1,'A104',3500000.00,'rented','Phòng tầng 1, diện tích 25m2, bếp riêng, máy giặt chung','2026-11-04 05:47:26'),(5,1,'A105',3500000.00,'rented','Phòng tầng 1 góc, 25m2, 2 cửa sổ thoáng mát','2026-11-04 05:47:26'),(6,1,'A201',3300000.00,'rented','Phòng tầng 2, 22m2, điều hòa inverter, tủ quần áo','2026-11-04 05:47:26'),(7,1,'A202',3300000.00,'rented','Phòng tầng 2, 22m2, ban công nhìn ra phố','2026-11-04 05:47:26'),(8,1,'A203',3500000.00,'rented','Phòng tầng 2, 24m2, full đồ giường nệm cao cấp','2026-11-04 05:47:26'),(9,1,'A204',3500000.00,'rented','Phòng tầng 2, 24m2, bếp riêng và hút mùi','2026-11-04 05:47:26'),(10,1,'A205',3300000.00,'available','Phòng tầng 2 trống, vừa sơn sửa sạch đẹp đón khách','2026-11-04 05:47:26'),(11,1,'A301',3400000.00,'rented','Phòng tầng 3, 23m2, ban công riêng phơi đồ','2026-11-04 05:47:26'),(12,1,'A302',3400000.00,'rented','Phòng tầng 3, 23m2, trang bị tủ lạnh, bàn làm việc','2026-11-04 05:47:26'),(13,1,'A303',3600000.00,'rented','Phòng tầng 3, 26m2, căn studio có vách kính ngăn bếp','2026-11-04 05:47:26'),(14,1,'A304',3600000.00,'rented','Phòng tầng 3, 26m2, căn góc 2 mặt thoáng','2026-11-04 05:47:26'),(15,1,'A305',3500000.00,'rented','Phòng tầng 3, 24m2, nội thất gỗ sồi cao cấp','2026-11-04 05:47:26'),(16,1,'A401',3600000.00,'rented','Phòng tầng 4, 25m2, thang máy êm, ban công view đẹp','2026-11-04 05:47:26'),(17,1,'A402',3600000.00,'rented','Phòng tầng 4, 25m2, khóa cửa vân tay thông minh','2026-11-04 05:47:26'),(18,1,'A403',3800000.00,'rented','Phòng tầng 4, 28m2, phòng ngủ tách biệt bếp','2026-11-04 05:47:26'),(19,1,'A404',3800000.00,'rented','Phòng tầng 4, 28m2, có máy giặt riêng trong phòng','2026-11-04 05:47:26'),(20,1,'A405',3500000.00,'maintenance','Phòng tầng 4, đang bảo dưỡng hệ thống điều hòa','2026-11-04 05:47:26'),(21,2,'B101',4200000.00,'rented','Căn hộ Studio tầng 1, 28m2, full nội thất thông minh','2026-11-04 05:47:26'),(22,2,'B102',4200000.00,'rented','Studio tầng 1, 28m2, sofa, smart tivi, bàn trà','2026-11-04 05:47:26'),(23,2,'B103',4500000.00,'rented','Studio tầng 1, 30m2, bếp từ đôi, lò vi sóng','2026-11-04 05:47:26'),(24,2,'B104',4500000.00,'rented','Studio tầng 1, 30m2, giếng trời đón sáng tự nhiên','2026-11-04 05:47:26'),(25,2,'B105',4000000.00,'available','Studio tầng 1 trống, nội thất mới 100%','2026-11-04 05:47:26'),(26,2,'B201',4300000.00,'rented','Studio tầng 2, 28m2, ban công riêng rộng rãi','2026-11-04 05:47:26'),(27,2,'B202',4300000.00,'rented','Studio tầng 2, 28m2, view công viên cây xanh','2026-11-04 05:47:26'),(28,2,'B203',4600000.00,'rented','Studio tầng 2, 32m2, 1 phòng ngủ 1 phòng khách','2026-11-04 05:47:26'),(29,2,'B204',4600000.00,'rented','Studio tầng 2, 32m2, ban công thoáng mát','2026-11-04 05:47:26'),(30,2,'B205',4400000.00,'rented','Studio tầng 2, 30m2, giường tầng cao cấp','2026-11-04 05:47:26'),(31,2,'B301',4500000.00,'rented','Studio tầng 3, 30m2, máy giặt sấy riêng','2026-11-04 05:47:26'),(32,2,'B302',4500000.00,'rented','Studio tầng 3, 30m2, tủ quần áo 4 cánh âm tường','2026-11-04 05:47:26'),(33,2,'B303',4800000.00,'rented','Căn hộ 1PN+1 tầng 3, 35m2, bếp đảo hiện đại','2026-11-04 05:47:26'),(34,2,'B304',4800000.00,'rented','Căn hộ 1PN+1 tầng 3, 35m2, ban công chạy dài','2026-11-04 05:47:26'),(35,2,'B305',4500000.00,'maintenance','Studio tầng 3, đang sơn lại tường và làm sàn gỗ','2026-11-04 05:47:26'),(36,2,'B401',4700000.00,'rented','Studio Penthouse tầng 4, 32m2, trần cao thoáng đãng','2026-11-04 05:47:26'),(37,2,'B402',4700000.00,'rented','Studio Penthouse tầng 4, 32m2, cửa kính panorama','2026-11-04 05:47:26'),(38,2,'B403',5000000.00,'rented','Căn hộ 2 phòng ngủ tầng 4, 40m2, phù hợp gia đình trẻ','2026-11-04 05:47:26'),(39,2,'B404',5000000.00,'rented','Căn hộ 2 phòng ngủ tầng 4, 40m2, full tiện nghi','2026-11-04 05:47:26'),(40,2,'B405',4600000.00,'available','Studio tầng 4 trống, chuẩn bị bàn giao khách mới','2026-11-04 05:47:26'),(41,3,'C101',2700000.00,'rented','Phòng tầng 1, 20m2, gác lửng bê tông kiên cố','2026-11-04 05:47:26'),(42,3,'C102',2700000.00,'rented','Phòng tầng 1, 20m2, khép kín, an ninh có camera','2026-11-04 05:47:26'),(43,3,'C103',2900000.00,'rented','Phòng tầng 1, 22m2, cửa sổ hành lang thoáng','2026-11-04 05:47:26'),(44,3,'C104',3000000.00,'rented','Phòng tầng 1, 24m2, có bồn rửa bát và kệ để đồ','2026-11-04 05:47:26'),(45,3,'C105',3000000.00,'rented','Phòng tầng 1, 24m2, thích hợp nhóm 2-3 bạn','2026-11-04 05:47:26'),(46,3,'C201',2800000.00,'rented','Phòng tầng 2, 20m2, có điều hòa và bình nóng lạnh','2026-11-04 05:47:26'),(47,3,'C202',2800000.00,'rented','Phòng tầng 2, 20m2, sạch sẽ, thoáng mát','2026-11-04 05:47:26'),(48,3,'C203',3100000.00,'rented','Phòng tầng 2, 23m2, ban công riêng nhìn ra phố','2026-11-04 05:47:26'),(49,3,'C204',3100000.00,'rented','Phòng tầng 2, 23m2, gác xếp đồ tiện lợi','2026-11-04 05:47:26'),(50,3,'C205',2800000.00,'available','Phòng tầng 2 trống, giá sinh viên ưu đãi','2026-11-04 05:47:26'),(51,3,'C301',2900000.00,'rented','Phòng tầng 3, 21m2, cửa sổ trời thông thoáng','2026-11-04 05:47:26'),(52,3,'C302',2900000.00,'rented','Phòng tầng 3, 21m2, nội thất cơ bản sẵn ở','2026-11-04 05:47:26'),(53,3,'C303',3200000.00,'rented','Phòng tầng 3, 24m2, gác đúc, quạt trần, điều hòa','2026-11-04 05:47:26'),(54,3,'C304',3200000.00,'rented','Phòng tầng 3, 24m2, căn góc yên tĩnh','2026-11-04 05:47:26'),(55,3,'C305',3000000.00,'maintenance','Phòng tầng 3, đang thay mới thiết bị vệ sinh','2026-11-04 05:47:26'),(56,3,'C401',3000000.00,'rented','Phòng tầng 4, 22m2, sân phơi rộng rãi ngay cạnh','2026-11-04 05:47:26'),(57,3,'C402',3000000.00,'rented','Phòng tầng 4, 22m2, giá thuê tiết kiệm','2026-11-04 05:47:26'),(58,3,'C403',3300000.00,'rented','Phòng tầng 4, 25m2, 2 cửa sổ lộng gió','2026-11-04 05:47:26'),(59,3,'C404',3300000.00,'rented','Phòng tầng 4, 25m2, đầy đủ nội thất chỉ việc xách vali đến','2026-11-04 05:47:26'),(60,3,'C405',2900000.00,'available','Phòng tầng 4 trống, hỗ trợ dọn vào ngay','2026-11-04 05:47:26');
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
INSERT INTO `services` VALUES (1,'Tiền điện sinh hoạt',3500.00,'kWh',1,'2026-11-04 05:47:26'),(2,'Tiền nước sinh hoạt',80000.00,'Người',1,'2026-11-04 05:47:26'),(3,'Internet / Wifi tốc độ cao',50000.00,'Phòng',1,'2026-11-04 05:47:26'),(4,'Rác sinh hoạt & Môi trường',30000.00,'Phòng',1,'2026-11-04 05:47:26'),(5,'Vệ sinh hành lang & Khu chung',20000.00,'Người',1,'2026-11-04 05:47:26'),(6,'Phí dịch vụ thang máy',30000.00,'Người',1,'2026-11-04 05:47:26'),(7,'Gửi xe máy tầng hầm',70000.00,'Xe',1,'2026-11-04 05:47:26'),(8,'Giặt ủi sấy chung',50000.00,'Người',1,'2026-11-04 05:47:26');
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
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenant_requests`
--

LOCK TABLES `tenant_requests` WRITE;
/*!40000 ALTER TABLE `tenant_requests` DISABLE KEYS */;
INSERT INTO `tenant_requests` VALUES (1,5,'profile_update','phone','0901001101','0988776655','Em vừa bị mất điện thoại và sim cũ, nhờ BQL cập nhật số mới để nhận thông báo.','pending',NULL,'2026-11-01 02:30:00','2026-11-04 05:47:26'),(2,6,'profile_update','email','huong.le@gmail.com','huong.le.ftu@gmail.com','Đổi sang email công việc để tiện nhận hóa đơn điện tử hàng tháng.','approved','Đã xác thực và cập nhật email mới cho bạn.','2026-10-28 07:15:00','2026-11-04 05:47:26'),(3,7,'profile_update','co_tenant','Người ở cùng','Đăng ký thêm bạn cùng phòng: Phạm Minh Hùng (CCCD: 001201012345)','Bạn học cùng trường chuyển đến ở ghép.','pending',NULL,'2026-11-02 09:40:00','2026-11-04 05:47:26'),(4,8,'profile_update','full_name','Phạm Thu Trang','Phạm Quỳnh Trang','Sửa lại tên chính xác theo căn cước công dân mới làm lại.','rejected','Vui lòng mang CCCD gốc qua văn phòng quản lý để đối chiếu.','2026-10-25 03:20:00','2026-11-04 05:47:26');
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
  PRIMARY KEY (`id`),
  KEY `idx_expenses_month` (`month_year`),
  KEY `idx_expenses_type` (`expense_type`),
  KEY `idx_expenses_status` (`status`)
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
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_phone` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','hash','Nguyễn Tuấn Dũng','0901234567',NULL,NULL,'Nam','admin@staymate.vn',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'admin',NULL,'2026-11-04 05:47:26'),(2,'quanly_a','hash','Nguyễn Văn Quân','0912345678',NULL,NULL,'Nam','quan.nguyen@staymate.vn',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'manager',NULL,'2026-11-04 05:47:26'),(3,'quanly_b','hash','Trần Thị Mai Lan','0923456789',NULL,NULL,'Nam','lan.tran@staymate.vn',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'manager',NULL,'2026-11-04 05:47:26'),(4,'quanly_c','hash','Lê Hoàng Nam','0934567890',NULL,NULL,'Nam','nam.le@staymate.vn',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'manager',NULL,'2026-11-04 05:47:26'),(5,'kh_a101','hash','Nguyễn Tiến Đạt','0901001101',NULL,NULL,'Nam','dat.nguyen@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(6,'kh_a102','hash','Lê Thị Thu Hương','0901001102',NULL,NULL,'Nam','huong.le@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(7,'kh_a103','hash','Trần Hoàng Long','0901001103',NULL,NULL,'Nam','long.tran@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(8,'kh_a104','hash','Phạm Thu Trang','0901001104',NULL,NULL,'Nam','trang.pham@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(9,'kh_a105','hash','Vũ Minh Tuấn','0901001105',NULL,NULL,'Nam','tuan.vu@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(10,'kh_a201','hash','Đặng Kim Thủy','0901001201',NULL,NULL,'Nam','thuy.dang@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(11,'kh_a202','hash','Bùi Xuân Bách','0901001202',NULL,NULL,'Nam','bach.bui@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(12,'kh_a203','hash','Hồ Quang Hiếu','0901001203',NULL,NULL,'Nam','hieu.ho@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(13,'kh_a204','hash','Ngô Phương Lan','0901001204',NULL,NULL,'Nam','lan.ngo@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(14,'kh_a301','hash','Đinh Trọng Hưng','0901001301',NULL,NULL,'Nam','hung.dinh@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(15,'kh_a302','hash','Hoàng Thanh Tùng','0901001302',NULL,NULL,'Nam','tung.hoang@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(16,'kh_a303','hash','Đỗ Thùy Linh','0901001303',NULL,NULL,'Nam','linh.do@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(17,'kh_a304','hash','Nguyễn Hải Yến','0901001304',NULL,NULL,'Nam','yen.nguyen@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(18,'kh_a305','hash','Phạm Quốc Huy','0901001305',NULL,NULL,'Nam','huy.pham@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(19,'kh_a401','hash','Trịnh Minh Đức','0901001401',NULL,NULL,'Nam','duc.trinh@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(20,'kh_a402','hash','Nguyễn Bích Ngọc','0901001402',NULL,NULL,'Nam','ngoc.nguyen@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(21,'kh_a403','hash','Vũ Đình Trọng','0901001403',NULL,NULL,'Nam','trong.vu@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(22,'kh_a404','hash','Trần Văn Khoa','0901001404',NULL,NULL,'Nam','khoa.tran@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(23,'kh_b101','hash','Mai Thùy Dung','0902002101',NULL,NULL,'Nam','dung.mai@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(24,'kh_b102','hash','Lê Khánh An','0902002102',NULL,NULL,'Nam','an.le@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(25,'kh_b103','hash','Nguyễn Nhật Minh','0902002103',NULL,NULL,'Nam','minh.nhat@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(26,'kh_b104','hash','Phan Bảo Châu','0902002104',NULL,NULL,'Nam','chau.phan@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(27,'kh_b201','hash','Đỗ Gia Hưng','0902002201',NULL,NULL,'Nam','hung.do@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(28,'kh_b202','hash','Bùi Mai Phương','0902002202',NULL,NULL,'Nam','phuong.bui@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(29,'kh_b203','hash','Trương Tuấn Kiệt','0902002203',NULL,NULL,'Nam','kiet.truong@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(30,'kh_b204','hash','Hoàng Thảo Vy','0902002204',NULL,NULL,'Nam','vy.hoang@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(31,'kh_b205','hash','Vũ Thành Đạt','0902002205',NULL,NULL,'Nam','dat.vu@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(32,'kh_b301','hash','Phạm Thanh Hằng','0902002301',NULL,NULL,'Nam','hang.pham@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(33,'kh_b302','hash','Nguyễn Đức Phúc','0902002302',NULL,NULL,'Nam','phuc.nguyen@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(34,'kh_b303','hash','Đặng Quỳnh Anh','0902002303',NULL,NULL,'Nam','anh.dang@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(35,'kh_b304','hash','Lê Minh Khang','0902002304',NULL,NULL,'Nam','khang.le@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(36,'kh_b401','hash','Trần Thảo Linh','0902002401',NULL,NULL,'Nam','linh.tran@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(37,'kh_b402','hash','Đào Quang Huy','0902002402',NULL,NULL,'Nam','huy.dao@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(38,'kh_b403','hash','Nguyễn Lan Hương','0902002403',NULL,NULL,'Nam','huong.lan@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(39,'kh_b404','hash','Phan Quốc Toàn','0902002404',NULL,NULL,'Nam','toan.phan@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(40,'kh_c101','hash','Hoàng Thị Ngọc Bích','0903003101',NULL,NULL,'Nam','bich.hoang@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(41,'kh_c102','hash','Dương Minh Trí','0903003102',NULL,NULL,'Nam','tri.duong@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(42,'kh_c103','hash','Ngô Ánh Dương','0903003103',NULL,NULL,'Nam','duong.ngo@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(43,'kh_c104','hash','Vũ Diệu Linh','0903003104',NULL,NULL,'Nam','linh.vu@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(44,'kh_c105','hash','Trần Tuấn Anh','0903003105',NULL,NULL,'Nam','anh.tran@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(45,'kh_c201','hash','Nguyễn Phương Thảo','0903003201',NULL,NULL,'Nam','thao.nguyen@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(46,'kh_c202','hash','Bùi Đức Mạnh','0903003202',NULL,NULL,'Nam','manh.bui@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(47,'kh_c203','hash','Lê Hoài Nam','0903003203',NULL,NULL,'Nam','nam.hoai@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(48,'kh_c204','hash','Đỗ Bảo Ngọc','0903003204',NULL,NULL,'Nam','ngoc.bao@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(49,'kh_c301','hash','Trịnh Văn Quyết','0903003301',NULL,NULL,'Nam','quyet.trinh@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(50,'kh_c302','hash','Phạm Hà My','0903003302',NULL,NULL,'Nam','my.pham@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(51,'kh_c303','hash','Nguyễn Trọng Tín','0903003303',NULL,NULL,'Nam','tin.nguyen@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(52,'kh_c304','hash','Hoàng Thúy Hằng','0903003304',NULL,NULL,'Nam','hang.thuy@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(53,'kh_c401','hash','Đinh Hoàng Việt','0903003401',NULL,NULL,'Nam','viet.dinh@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(54,'kh_c402','hash','Chu Minh Châu','0903003402',NULL,NULL,'Nam','chau.chu@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(55,'kh_c403','hash','Vũ Hải Đăng','0903003403',NULL,NULL,'Nam','dang.vu@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26'),(56,'kh_c404','hash','Nguyễn Quỳnh Như','0903003404',NULL,NULL,'Nam','nhu.nguyen@gmail.com',NULL,NULL,NULL,'Đang thuê',NULL,NULL,'tenant',NULL,'2026-11-04 05:47:26');
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
  KEY `idx_readings_room` (`room_id`),
  CONSTRAINT `utilities_readings_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `utilities_readings`
--

LOCK TABLES `utilities_readings` WRITE;
/*!40000 ALTER TABLE `utilities_readings` DISABLE KEYS */;
INSERT INTO `utilities_readings` VALUES (1,1,'10-2026',120,260,10,24,'2026-11-04 05:47:26'),(2,2,'10-2026',150,290,15,29,'2026-11-04 05:47:26'),(3,3,'10-2026',200,335,18,34,'2026-11-04 05:47:26'),(4,4,'10-2026',180,320,22,38,'2026-11-04 05:47:26'),(5,5,'10-2026',140,270,14,28,'2026-11-04 05:47:26'),(6,6,'10-2026',110,230,12,22,'2026-11-04 05:47:26'),(7,7,'10-2026',130,255,16,28,'2026-11-04 05:47:26'),(8,8,'10-2026',210,360,20,35,'2026-11-04 05:47:26'),(9,9,'10-2026',190,340,17,32,'2026-11-04 05:47:26'),(10,11,'10-2026',160,300,15,30,'2026-11-04 05:47:26'),(11,12,'10-2026',145,275,14,27,'2026-11-04 05:47:26'),(12,13,'10-2026',220,380,22,40,'2026-11-04 05:47:26'),(13,14,'10-2026',205,360,19,36,'2026-11-04 05:47:26'),(14,15,'10-2026',180,310,16,30,'2026-11-04 05:47:26'),(15,16,'10-2026',195,345,18,34,'2026-11-04 05:47:26'),(16,17,'10-2026',170,315,15,29,'2026-11-04 05:47:26'),(17,18,'10-2026',240,410,23,42,'2026-11-04 05:47:26'),(18,19,'10-2026',230,395,21,39,'2026-11-04 05:47:26'),(19,21,'10-2026',300,480,25,43,'2026-11-04 05:47:26'),(20,22,'10-2026',320,510,28,47,'2026-11-04 05:47:26'),(21,23,'10-2026',350,550,30,52,'2026-11-04 05:47:26'),(22,24,'10-2026',310,490,26,44,'2026-11-04 05:47:26'),(23,26,'10-2026',280,440,24,40,'2026-11-04 05:47:26'),(24,27,'10-2026',290,460,25,42,'2026-11-04 05:47:26'),(25,28,'10-2026',340,530,29,49,'2026-11-04 05:47:26'),(26,29,'10-2026',330,520,27,46,'2026-11-04 05:47:26'),(27,30,'10-2026',305,475,26,44,'2026-11-04 05:47:26'),(28,31,'10-2026',360,560,31,53,'2026-11-04 05:47:26'),(29,32,'10-2026',350,545,30,51,'2026-11-04 05:47:26'),(30,33,'10-2026',400,620,35,60,'2026-11-04 05:47:26'),(31,34,'10-2026',390,605,34,58,'2026-11-04 05:47:26'),(32,36,'10-2026',370,570,32,54,'2026-11-04 05:47:26'),(33,37,'10-2026',380,590,33,56,'2026-11-04 05:47:26'),(34,38,'10-2026',420,650,38,66,'2026-11-04 05:47:26'),(35,39,'10-2026',410,640,37,64,'2026-11-04 05:47:26'),(36,41,'10-2026',70,190,8,19,'2026-11-04 05:47:26'),(37,42,'10-2026',80,205,9,21,'2026-11-04 05:47:26'),(38,43,'10-2026',90,220,10,23,'2026-11-04 05:47:26'),(39,44,'10-2026',100,240,11,25,'2026-11-04 05:47:26'),(40,45,'10-2026',110,260,12,27,'2026-11-04 05:47:26'),(41,46,'10-2026',75,195,8,19,'2026-11-04 05:47:26'),(42,47,'10-2026',85,210,9,22,'2026-11-04 05:47:26'),(43,48,'10-2026',115,270,13,28,'2026-11-04 05:47:26'),(44,49,'10-2026',105,250,11,26,'2026-11-04 05:47:26'),(45,51,'10-2026',95,230,10,24,'2026-11-04 05:47:26'),(46,52,'10-2026',90,225,9,23,'2026-11-04 05:47:26'),(47,53,'10-2026',120,280,14,30,'2026-11-04 05:47:26'),(48,54,'10-2026',115,275,13,29,'2026-11-04 05:47:26'),(49,56,'10-2026',100,245,11,26,'2026-11-04 05:47:26'),(50,57,'10-2026',95,235,10,25,'2026-11-04 05:47:26'),(51,58,'10-2026',125,290,15,32,'2026-11-04 05:47:26'),(52,59,'10-2026',120,285,14,31,'2026-11-04 05:47:26');
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

-- Dump completed on 2026-11-04 12:47:57
