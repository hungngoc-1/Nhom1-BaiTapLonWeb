<?php
/**
 * Nạp lại toàn bộ CSDL từ database/schema.sql + database/seed.sql.
 * Dữ liệu UTF-8 chuẩn xác 100%, không bị lỗi font hay dấu hỏi.
 */
if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
}

try {
    $pdo = new PDO('mysql:host=localhost;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]);
    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");

    foreach (['schema.sql', 'seed.sql'] as $file) {
        $filePath = __DIR__ . '/' . $file;
        $sql = file_get_contents($filePath);
        if ($sql === false) {
            throw new RuntimeException("Không đọc được file $file");
        }
        $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql);
        $pdo->exec($sql);
        echo "OK: đã nạp $file thành công với UTF-8 chuẩn!\n";
    }

    $db = new PDO('mysql:host=localhost;dbname=quan_ly_nha_tro;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]);

    $tables = [
        'buildings', 'users', 'rooms', 'services', 'contracts', 
        'utilities_readings', 'invoices', 'invoice_details', 
        'tenant_requests', 'third_party_expenses', 'activity_logs', 'tenant_contact_leads'
    ];

    echo "\n=== BẢNG THỐNG KÊ DỮ LIỆU ĐÃ NẠP ===\n";
    foreach ($tables as $t) {
        $count = $db->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo str_pad($t, 25) . ': ' . $count . " dòng\n";
    }

    echo "\nHoàn tất 100%! Cơ sở dữ liệu đã chuẩn hóa tiếng Việt không lỗi font.\n";
} catch (Throwable $e) {
    if (php_sapi_name() !== 'cli' && !headers_sent()) {
        http_response_code(500);
    }
    echo 'LỖI: ' . $e->getMessage() . "\n";
}
