<?php
session_start();

// Tạo sẵn CSRF token cho phiên làm việc
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$route = $_GET['route'] ?? 'dashboard';

// Nạp các Controller cần thiết
require_once __DIR__ . '/app/Controllers/DashboardController.php';
require_once __DIR__ . '/app/Controllers/AuthController.php';

switch ($route) {
    case 'dashboard':
        $dashboardController = new DashboardController();
        $dashboardController->index();
        break;

    case 'login':
    case 'auth/login':
        $authController = new AuthController();
        $authController->showLoginForm();
        break;

    case 'auth/process':
        $authController = new AuthController();
        $authController->login();
        break;

    default:
        $dashboardController = new DashboardController();
        $dashboardController->index();
        break;
}
?>