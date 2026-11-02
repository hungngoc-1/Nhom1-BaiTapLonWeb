<?php
// Nạp file kết nối CSDL (nằm ở thư mục gốc)
require_once __DIR__ . '/../db.php';

// Nạp Repository và Controller - Module Dịch vụ
require_once __DIR__ . '/../src/Repository/ServiceRepository.php';
require_once __DIR__ . '/../src/Controller/ServiceController.php';

// Nạp Repository và Controller - Module Báo cáo
require_once __DIR__ . '/../src/Repository/ReportRepository.php';
require_once __DIR__ . '/../src/Controller/ReportController.php';

$route = $_GET['route'] ?? 'services';

if ($route === 'services') {
    // Luồng xử lý cho module Đơn giá Dịch vụ
    $repository = new ServiceRepository($pdo);
    $controller = new ServiceController($repository);
    $controller->index();
    
} elseif ($route === 'reports') {
    // Luồng xử lý cho module Báo cáo Thống kê
    $reportRepo = new ReportRepository($pdo);
    $reportController = new ReportController($reportRepo);
    $reportController->index();
    
} else {
    echo "<h1>404 - Không tìm thấy trang</h1>";
}
?>