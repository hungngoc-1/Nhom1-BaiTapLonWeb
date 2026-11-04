<?php
/**
 * Cấu hình kết nối CSDL dùng chung cho toàn bộ hệ thống.
 * Tự động khởi tạo CSDL, tạo đủ các bảng và nạp dữ liệu từ database/seed.sql nếu chưa có.
 */
class Database {
    private static $host = 'localhost';
    private static $db_name = 'quan_ly_nha_tro';
    private static $username = 'root';
    private static $password = '';
    private static $conn = null;

    public static function getConnection() {
        if (self::$conn === null) {
            try {
                // Kết nối MySQL và tự động tạo database nếu chưa có
                $pdoInit = new PDO(
                    "mysql:host=" . self::$host . ";charset=utf8mb4",
                    self::$username,
                    self::$password
                );
                $pdoInit->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $pdoInit->exec("CREATE DATABASE IF NOT EXISTS `" . self::$db_name . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

                self::$conn = new PDO(
                    "mysql:host=" . self::$host . ";dbname=" . self::$db_name . ";charset=utf8mb4",
                    self::$username,
                    self::$password
                );
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                self::ensureSchemaAndSeed(self::$conn);
            } catch (PDOException $exception) {
                die("Lỗi kết nối CSDL: " . htmlspecialchars($exception->getMessage()));
            }
        }
        return self::$conn;
    }

    private static function safeExec(PDO $conn, string $sql): void {
        try {
            $conn->exec($sql);
        } catch (PDOException $e) {
            // Bỏ qua lỗi đơn lẻ để không chặn các bảng khác
        }
    }

    private static function ensureSchemaAndSeed(PDO $conn): void {
        // 1. Tạo từng bảng độc lập nếu chưa tồn tại
        self::safeExec($conn, "
            CREATE TABLE IF NOT EXISTS `users` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `username` VARCHAR(50) NOT NULL UNIQUE,
              `password` VARCHAR(255) NOT NULL,
              `full_name` VARCHAR(100) NOT NULL,
              `phone` VARCHAR(20) NOT NULL,
              `email` VARCHAR(100) NOT NULL UNIQUE,
              `role` ENUM('admin', 'landlord', 'tenant', 'manager') NOT NULL DEFAULT 'tenant',
              `preferred_room_id` INT NULL,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;
        ");

        self::safeExec($conn, "
            CREATE TABLE IF NOT EXISTS `buildings` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `name` VARCHAR(100) NOT NULL,
              `address` VARCHAR(255) NOT NULL,
              `manager_id` INT NULL,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;
        ");

        self::safeExec($conn, "
            CREATE TABLE IF NOT EXISTS `rooms` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `building_id` INT NULL,
              `room_number` VARCHAR(20) NOT NULL UNIQUE,
              `price` DECIMAL(10,2) NOT NULL,
              `status` ENUM('available', 'rented', 'maintenance') DEFAULT 'available',
              `description` TEXT,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;
        ");

        self::safeExec($conn, "
            CREATE TABLE IF NOT EXISTS `services` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `service_name` VARCHAR(100) NOT NULL,
              `unit_price` DECIMAL(10,2) NOT NULL,
              `unit` VARCHAR(50) NOT NULL,
              `is_active` TINYINT(1) DEFAULT 1,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;
        ");

        self::safeExec($conn, "
            CREATE TABLE IF NOT EXISTS `contracts` (
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
              `deposit_status` ENUM('held', 'refunded', 'forfeited') DEFAULT 'held',
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
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;
        ");

        self::safeExec($conn, "
            CREATE TABLE IF NOT EXISTS `utilities_readings` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `room_id` INT NOT NULL,
              `month_year` VARCHAR(7) NOT NULL,
              `electric_old` INT NOT NULL,
              `electric_new` INT NOT NULL,
              `water_old` INT NOT NULL,
              `water_new` INT NOT NULL,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              UNIQUE KEY `unique_room_month` (`room_id`, `month_year`)
            ) ENGINE=InnoDB;
        ");

        self::safeExec($conn, "
            CREATE TABLE IF NOT EXISTS `invoices` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `room_id` INT NOT NULL,
              `contract_id` INT NOT NULL,
              `month_year` VARCHAR(7) NOT NULL,
              `room_price` DECIMAL(10,2) DEFAULT 0,
              `electric_amount` DECIMAL(10,2) DEFAULT 0,
              `water_amount` DECIMAL(10,2) DEFAULT 0,
              `service_fee` DECIMAL(10,2) DEFAULT 0,
              `total_amount` DECIMAL(10,2) NOT NULL,
              `status` ENUM('unpaid', 'paid') DEFAULT 'unpaid',
              `paid_at` DATETIME NULL,
              `payment_method` VARCHAR(20) NULL,
              `payment_note` VARCHAR(255) NULL,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              UNIQUE KEY `unique_invoice_month` (`room_id`, `month_year`)
            ) ENGINE=InnoDB;
        ");

        self::safeExec($conn, "
            CREATE TABLE IF NOT EXISTS `invoice_details` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `invoice_id` INT NOT NULL,
              `service_id` INT NULL,
              `quantity` DECIMAL(10,2) NOT NULL DEFAULT 1,
              `applied_price` DECIMAL(10,2) NOT NULL,
              `subtotal` DECIMAL(10,2) NOT NULL
            ) ENGINE=InnoDB;
        ");

        // 2. Nạp dữ liệu mẫu từ seed.sql nếu bảng users đang trống
        try {
            if ((int)$conn->query("SELECT COUNT(*) FROM `users`")->fetchColumn() === 0) {
                $seedFile = dirname(__DIR__, 2) . '/database/seed.sql';
                if (file_exists($seedFile)) {
                    $sql = file_get_contents($seedFile);
                    if ($sql !== false) {
                        $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql);
                        $conn->exec($sql);
                    }
                }
            }
        } catch (PDOException $e) {}
    }
}
?>