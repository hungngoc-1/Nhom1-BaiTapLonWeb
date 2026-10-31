<?php
session_start();

// Tạo sẵn CSRF token cho phiên làm việc
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$route = $_GET['route'] ?? 'dashboard';

// ==========================================
// 1. XỬ LÝ NÚT ĐĂNG XUẤT NGAY TẠI ROUTER
// ==========================================
if ($route === 'auth/logout') {
    session_destroy(); // Xóa phiên đăng nhập
    header("Location: index.php?route=auth/login"); // Đẩy về trang đăng nhập
    exit;
}

// Nạp các Controller cần thiết
require_once __DIR__ . '/app/Controllers/DashboardController.php';
require_once __DIR__ . '/app/Controllers/AuthController.php';
require_once __DIR__ . '/app/Controllers/UtilitiesController.php'; // Nạp Controller Điện Nước chuẩn MVC

// ==========================================
// 2. BỘ ĐIỀU HƯỚNG CÁC TRANG (ROUTER)
// ==========================================
switch ($route) {
    case 'dashboard':
        $dashboardController = new DashboardController();
        $dashboardController->index();
        break;

    case 'overview':
        $dashboardController = new DashboardController();
        $dashboardController->overview();
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

    // --- CÁC TRANG MENU CHƯA CÓ CONTROLLER VÀ VIEW CHÍNH THỨC (HIỂN THỊ TẠM) ---
    case 'rooms':
        $roomController = new RoomController();
        $roomController->index();
        break;

    // --- CÁC TRANG MENU CHƯA CÓ CONTROLLER VÀ VIEW CHÍNH THỨC (HIỂN THỊ TẠM) ---
    case 'tenants':
        showPlaceholder('Quản lý Khách thuê');
        break;
        
    // ==========================================
    // PHÂN HỆ QUẢN LÝ HỢP ĐỒNG
    // ==========================================
    case 'contracts':
        $contractController = new ContractController();
        $contractController->index();
        break;
        
    case 'contracts/store': // Xử lý khi form Thêm mới submit
        $contractController = new ContractController();
        $contractController->store();
        break;
        
    case 'contracts/edit': // Xử lý khi bấm nút Sửa
        $contractController = new ContractController();
        $contractController->edit();
        break;
        
    case 'contracts/delete': // Xử lý khi bấm nút Xóa
        $contractController = new ContractController();
        $contractController->delete();
        break;
    // --- TRANG ĐIỆN NƯỚC (Chuẩn MVC) ---
    case 'utilities':
        $utilitiesController = new UtilitiesController();
        $utilitiesController->index();
        break;
    case 'invoices':
        showPlaceholder('Hóa đơn & Thu tiền');
        break;
    case 'reports':
        showPlaceholder('Báo cáo thống kê');
        break;

    default:
        $dashboardController = new DashboardController();
        $dashboardController->index();
        break;
}

// ==========================================
// HÀM TẠO GIAO DIỆN TẠM CHO CÁC TRANG MENU KHÁC
// ==========================================
function showPlaceholder($title) {
    echo '<div style="padding: 60px; font-family: sans-serif; text-align: center; background: #F0F7F6; height: 100vh; box-sizing: border-box;">
            <h1 style="color: #12746B; margin-bottom: 15px;">' . htmlspecialchars($title) . '</h1>
            <p style="color: #7F8C8D; margin-bottom: 30px;">Giao diện trang này đang được các bạn Backend xây dựng...</p>
            <a href="index.php?route=dashboard" style="display: inline-block; background: #12746B; color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: bold; box-shadow: 0 4px 10px rgba(18,116,107,0.2);">← Quay lại trang Tổng quan</a>
          </div>';
}
?>