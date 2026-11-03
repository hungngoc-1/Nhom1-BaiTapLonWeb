<?php
/**
 * Nạp lại toàn bộ CSDL từ database/schema.sql + database/seed.sql.
 * CẢNH BÁO: schema.sql có lệnh DROP DATABASE nên dữ liệu cũ sẽ bị thay thế hoàn toàn.
 *
 * Chạy bằng trình duyệt: http://localhost/Nhom1-BaiTapLonWeb/database/import.php
 * Hoặc dòng lệnh:        php database/import.php
 */
header('Content-Type: text/plain; charset=utf-8');

try {
    $pdo = new PDO('mysql:host=localhost;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    foreach (['schema.sql', 'seed.sql'] as $file) {
        $sql = file_get_contents(__DIR__ . '/' . $file);
        if ($sql === false) {
            throw new RuntimeException("Không đọc được file $file");
        }
        // Bỏ BOM nếu có
        $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql);
        $pdo->exec($sql);
        echo "OK: đã nạp $file\n";
    }

    $db = new PDO('mysql:host=localhost;dbname=quan_ly_nha_tro;charset=utf8mb4', 'root', '');
    foreach (['users', 'rooms', 'services', 'contracts', 'utilities_readings', 'invoices', 'invoice_details'] as $t) {
        echo str_pad($t, 20) . ': ' . $db->query("SELECT COUNT(*) FROM `$t`")->fetchColumn() . " dòng\n";
    }
    echo "\nHoàn tất! Dữ liệu đã được thay bằng seed.sql.\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo 'LỖI: ' . $e->getMessage() . "\n";
}
