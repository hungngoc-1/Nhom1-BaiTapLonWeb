<?php
// 1. Nạp file kết nối CSDL
require_once 'app/Config/Database.php';

// 2. Nạp các Controllers của Trang
require_once 'app/Controllers/UtilityController.php';
require_once 'app/Controllers/InvoiceController.php';
require_once 'app/Controllers/PaymentController.php';

// 3. Khởi tạo kết nối DB
$database = new Database();
$db = $database->getConnection();

// 4. Bắt URL để điều hướng (Routing)
// Ví dụ Frontend gọi: localhost/Nhom1-BaiTapLonWeb/index.php?route=api/invoices/preview
$route = $_GET['route'] ?? '';

switch ($route) {
    // ---- MODULE CỦA TRANG (ĐIỆN NƯỚC, HÓA ĐƠN, THANH TOÁN) ---- //
    
    case 'api/utilities/store':
        $controller = new UtilityController($db);
        $controller->store();
        break;

    case 'api/invoices/preview':
        $controller = new InvoiceController($db);
        $controller->previewInvoice();
        break;

    case 'api/invoices/store':
        $controller = new InvoiceController($db);
        $controller->store();
        break;

    case 'api/payments/process':
        $controller = new PaymentController($db);
        $controller->process();
        break;

    // Các bạn khác (Người 1,2,3,5) sau này sẽ viết thêm case của họ ở dưới đây
    
    default:
        echo "Chào mừng đến với Hệ thống Quản lý nhà trọ (API Backend).";
        break;
}
?>