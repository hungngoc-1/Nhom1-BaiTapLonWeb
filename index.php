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
require_once __DIR__ . '/app/Controllers/RoomController.php';
require_once __DIR__ . '/app/Controllers/TenantController.php';
require_once __DIR__ . '/app/Controllers/ContractController.php';
require_once __DIR__ . '/app/Controllers/UtilitiesController.php';
require_once __DIR__ . '/app/Controllers/InvoiceController.php';
require_once __DIR__ . '/app/Controllers/AccountController.php';

// ==========================================
// 2. BỘ ĐIỀU HƯỚNG CÁC TRANG (ROUTER)
// ==========================================
switch ($route) {
    // --- QUẢN LÝ TÀI KHOẢN (ADMIN & QUẢN LÝ) ---
    case 'accounts':
    case 'users':
        (new AccountController())->index();
        break;

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

    // --- PHÂN HỆ QUẢN LÝ PHÒNG TRỌ ---
    case 'rooms':
        $roomController = new RoomController();
        $roomController->index();
        break;

    // --- PHÂN HỆ QUẢN LÝ KHÁCH THUÊ (ĐANG XÂY DỰNG) ---
    case 'tenants':
        (new TenantController())->index();
        break;
        
    // ==========================================
    // PHÂN HỆ QUẢN LÝ HỢP ĐỒNG (HỦNG NGỌC PHỤ TRÁCH)
    // ==========================================
    case 'contracts':
        $contractController = new ContractController();
        $contractController->index();
        break;
        
    case 'contracts/store': // Xử lý khi form Thêm mới submit
        $contractController = new ContractController();
        $contractController->store();
        break;

    case 'contracts/detail': // Xem bản Hợp đồng A4, Ký Online, In & Lưu bản Scan
        $contractController = new ContractController();
        $contractController->detail();
        break;

    case 'contracts/sign-online': // Lưu chữ ký điện tử (Online)
        $contractController = new ContractController();
        $contractController->signOnline();
        break;

    case 'contracts/upload-scan': // Lưu bản Scan hợp đồng ký giấy
        $contractController = new ContractController();
        $contractController->uploadScan();
        break;

    case 'contracts/terminate': // Thanh lý hợp đồng (hoàn cọc / mất cọc)
        $contractController = new ContractController();
        $contractController->terminate();
        break;

    case 'contracts/renew': // Gia hạn hợp đồng
        $contractController = new ContractController();
        $contractController->renew();
        break;
        
    case 'contracts/edit': // Xử lý khi bấm nút Sửa
        $contractController = new ContractController();
        $contractController->edit();
        break;

    case 'contracts/update': // Xử lý khi bấm nút Lưu thay đổi ở form Sửa
        $contractController = new ContractController();
        $contractController->update();
        break;
        
    case 'contracts/delete': // Xử lý khi bấm nút Xóa (POST)
        $contractController = new ContractController();
        $contractController->delete();
        break;

    case 'tenant/contract': // Xem hợp đồng dành cho phân quyền khách thuê
        $contractController = new ContractController();
        $contractController->tenantContract();
        break;

    case 'api/tenant/contract': // API JSON dành cho phân quyền khách thuê
        $contractController = new ContractController();
        $contractController->apiTenantContract();
        break;

    // --- PHÂN HỆ ĐIỆN NƯỚC ---
    case 'utilities':
        $utilitiesController = new UtilitiesController();
        $utilitiesController->index();
        break;

    // --- XEM PHÒNG ---
    case 'showcase':
    case 'web360':
    case 'xemphong':
        header('Location: TrangXemPhong/index.php');
        exit;

    case 'api/utilities/search':
        $utilitiesController = new UtilitiesController();
        $utilitiesController->searchApi();
        break;

    // --- CÁC PHÂN HỆ ĐANG XÂY DỰNG ---
    case 'invoices':
        (new InvoiceController())->index();
        break;

    case 'invoices/detail':
        (new InvoiceController())->detail();
        break;

    case 'invoices/store':
        (new InvoiceController())->store();
        break;

    case 'invoices/store-all':
        (new InvoiceController())->storeAll();
        break;

    case 'invoices/pay':
        (new InvoiceController())->pay();
        break;

    case 'invoices/delete':
        (new InvoiceController())->delete();
        break;

    case 'invoices/update-deposit':
        (new InvoiceController())->updateDeposit();
        break;

    case 'invoices/add-expense':
        (new InvoiceController())->addExpense();
        break;

    case 'invoices/export':
        (new InvoiceController())->export();
        break;

    case 'reports':
        header("Location: index.php?route=overview");
        exit;

    case 'web360':
    case 'xemphong':
        header("Location: TrangXemPhong/");
        exit;
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
            <p style="color: #7F8C8D; margin-bottom: 30px;">Giao diện trang này đang được xây dựng...</p>
            <a href="index.php?route=dashboard" style="display: inline-block; background: #12746B; color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: bold; box-shadow: 0 4px 10px rgba(18,116,107,0.2);">← Quay lại trang Tổng quan</a>
          </div>';
}
?>
