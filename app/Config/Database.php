<?php
/**
 * Cấu hình kết nối CSDL dùng chung cho toàn bộ hệ thống.
 * Tự động khởi tạo CSDL, tạo đủ 7 bảng (nếu thiếu) và nạp dữ liệu mẫu ban đầu.
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
              `role` ENUM('admin', 'landlord', 'tenant') NOT NULL,
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;
        ");

        self::safeExec($conn, "
            CREATE TABLE IF NOT EXISTS `rooms` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `room_number` VARCHAR(20) NOT NULL UNIQUE,
              `price` DECIMAL(10,2) NOT NULL,
              `status` ENUM('available', 'rented') DEFAULT 'available',
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
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;
        ");

        self::safeExec($conn, "
            CREATE TABLE IF NOT EXISTS `contracts` (
              `id` INT AUTO_INCREMENT PRIMARY KEY,
              `room_id` INT NOT NULL,
              `tenant_id` INT NOT NULL,
              `start_date` DATE NOT NULL,
              `end_date` DATE NOT NULL,
              `deposit_amount` DECIMAL(10,2) NOT NULL,
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
              `total_amount` DECIMAL(10,2) NOT NULL,
              `status` ENUM('unpaid', 'paid') DEFAULT 'unpaid',
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

        // 2. Nạp dữ liệu mẫu cho các bảng đang trống
        try {
            if ((int)$conn->query("SELECT COUNT(*) FROM `users`")->fetchColumn() === 0) {
                self::safeExec($conn, "
                    INSERT IGNORE INTO `users` (`id`, `username`, `password`, `full_name`, `phone`, `email`, `role`) VALUES
                    (1, 'admin01', 'hashed_pass_1', 'Quản Trị Viên', '0901234567', 'admin@nhatro.com', 'admin'),
                    (2, 'khach01', 'hashed_pass_3', 'Trần Thị Khách (P101)', '0987654321', 'khach1@gmail.com', 'tenant'),
                    (3, 'khach02', 'hashed_pass_4', 'Lê Văn Thuê (P201)', '0976543210', 'khach2@gmail.com', 'tenant'),
                    (4, 'khach03', 'hashed_pass_5', 'Phạm Khách Cũ (P102)', '0965432109', 'khach3@gmail.com', 'tenant'),
                    (5, 'khach04', 'hashed_pass_6', 'Võ Khách Mới (P301)', '0954321098', 'khach4@gmail.com', 'tenant'),
                    (6, 'khach05', 'hashed_pass_7', 'Đinh Khách Vip (P302)', '0943210987', 'khach5@gmail.com', 'tenant');
                ");
            }
        } catch (PDOException $e) {}

        try {
            if ((int)$conn->query("SELECT COUNT(*) FROM `rooms`")->fetchColumn() === 0) {
                self::safeExec($conn, "
                    INSERT IGNORE INTO `rooms` (`id`, `room_number`, `price`, `status`, `description`) VALUES
                    (1, 'P101', 3000000, 'rented', 'Phòng tầng 1, tiêu chuẩn'),
                    (2, 'P102', 2500000, 'rented', 'Phòng tầng 1, giá rẻ'),
                    (3, 'P201', 3500000, 'rented', 'Phòng tầng 2, ban công'),
                    (4, 'P202', 3200000, 'available', 'Phòng tầng 2, trống sửa chữa'),
                    (5, 'P301', 4000000, 'rented', 'Phòng tầng 3, studio full nội thất'),
                    (6, 'P302', 4500000, 'rented', 'Phòng tầng 3, 2 phòng ngủ mini');
                ");
            }
        } catch (PDOException $e) {}

        try {
            if ((int)$conn->query("SELECT COUNT(*) FROM `services`")->fetchColumn() === 0) {
                self::safeExec($conn, "
                    INSERT INTO `services` (`service_name`, `unit_price`, `unit`) VALUES
                    ('Điện', 3500, 'kWh'),
                    ('Nước', 20000, 'Khối'),
                    ('Rác', 50000, 'Tháng'),
                    ('Internet', 100000, 'Tháng');
                ");
            }
        } catch (PDOException $e) {}

        try {
            if ((int)$conn->query("SELECT COUNT(*) FROM `contracts`")->fetchColumn() === 0) {
                self::safeExec($conn, "
                    INSERT IGNORE INTO `contracts` (`id`, `room_id`, `tenant_id`, `start_date`, `end_date`, `deposit_amount`, `status`) VALUES
                    (1, 1, 2, '2023-01-01', '2023-12-31', 3000000, 'active'),
                    (2, 3, 3, '2023-05-01', '2024-05-01', 3500000, 'active'),
                    (3, 2, 4, '2022-01-01', '2023-06-30', 2500000, 'terminated'),
                    (4, 2, 5, '2023-08-01', '2024-08-01', 2500000, 'active'),
                    (5, 5, 6, '2023-03-01', '2024-03-01', 4000000, 'active');
                ");
            }
        } catch (PDOException $e) {}

        try {
            if ((int)$conn->query("SELECT COUNT(*) FROM `utilities_readings`")->fetchColumn() === 0) {
                self::safeExec($conn, "
                    INSERT IGNORE INTO `utilities_readings` (`room_id`, `month_year`, `electric_old`, `electric_new`, `water_old`, `water_new`) VALUES
                    (1, '05-2023', 0, 50, 0, 5), (3, '05-2023', 0, 60, 0, 6), (2, '05-2023', 0, 40, 0, 4), (5, '05-2023', 0, 80, 0, 8),
                    (1, '06-2023', 50, 110, 5, 11), (3, '06-2023', 60, 130, 6, 13), (2, '06-2023', 40, 70, 4, 7), (5, '06-2023', 80, 170, 8, 17),
                    (1, '07-2023', 110, 200, 11, 18), (3, '07-2023', 130, 240, 13, 21), (5, '07-2023', 170, 300, 17, 28),
                    (1, '08-2023', 200, 270, 18, 24), (3, '08-2023', 240, 330, 21, 28), (2, '08-2023', 70, 100, 7, 10), (5, '08-2023', 300, 410, 28, 38),
                    (1, '09-2023', 270, 330, 24, 29), (3, '09-2023', 330, 410, 28, 34), (2, '09-2023', 100, 140, 10, 14), (5, '09-2023', 410, 510, 38, 47),
                    (1, '10-2023', 330, 380, 29, 34), (3, '10-2023', 410, 480, 34, 40), (2, '10-2023', 140, 190, 14, 19), (5, '10-2023', 510, 600, 47, 55);
                ");
            }
        } catch (PDOException $e) {}

        try {
            if ((int)$conn->query("SELECT COUNT(*) FROM `invoices`")->fetchColumn() === 0) {
                self::safeExec($conn, "
                    INSERT IGNORE INTO `invoices` (`room_id`, `contract_id`, `month_year`, `total_amount`, `status`) VALUES
                    (1, 1, '05-2023', 3425000, 'paid'), (3, 2, '05-2023', 3980000, 'paid'), (2, 3, '05-2023', 2870000, 'paid'), (5, 5, '05-2023', 4590000, 'paid'),
                    (1, 1, '06-2023', 3480000, 'paid'), (3, 2, '06-2023', 4035000, 'paid'), (2, 3, '06-2023', 2815000, 'paid'), (5, 5, '06-2023', 4645000, 'paid'),
                    (1, 1, '07-2023', 3605000, 'paid'), (3, 2, '07-2023', 4195000, 'paid'), (5, 5, '07-2023', 4825000, 'paid'),
                    (1, 1, '08-2023', 3515000, 'paid'), (3, 2, '08-2023', 4105000, 'paid'), (2, 4, '08-2023', 2815000, 'paid'), (5, 5, '08-2023', 4735000, 'unpaid'),
                    (1, 1, '09-2023', 3460000, 'paid'), (3, 2, '09-2023', 4050000, 'unpaid'), (2, 4, '09-2023', 2870000, 'paid'), (5, 5, '09-2023', 4680000, 'unpaid'),
                    (1, 1, '10-2023', 3425000, 'unpaid'), (3, 2, '10-2023', 4015000, 'unpaid'), (2, 4, '10-2023', 2925000, 'unpaid'), (5, 5, '10-2023', 4625000, 'unpaid');
                ");
            }
        } catch (PDOException $e) {}
    }
}
?>
