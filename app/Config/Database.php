<?php
/**
 * Cấu hình kết nối CSDL dùng chung cho toàn bộ hệ thống.
 * Dùng được cả 2 cách:
 *   $conn = Database::getConnection();
 *   $db = new Database(); $conn = $db->getConnection();
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
                self::$conn = new PDO(
                    "mysql:host=" . self::$host . ";dbname=" . self::$db_name . ";charset=utf8mb4",
                    self::$username,
                    self::$password
                );
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $exception) {
                die("Lỗi kết nối CSDL: " . htmlspecialchars($exception->getMessage()));
            }
        }
        return self::$conn;
    }
}
?>
