<?php
session_start();

// Tạo sẵn CSRF token giả lập cho phiên làm việc (đáp ứng yêu cầu bảo mật)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$route = $_GET['route'] ?? 'dashboard';

require_once __DIR__ . '/app/controllers/DashboardController.php';
$controller = new DashboardController();

switch ($route) {
    case 'dashboard':
        $controller->index();
        break;
    case 'login':
        $controller->loginView();
        break;
    default:
        $controller->index();
        break;
}