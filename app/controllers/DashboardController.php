<?php
require_once __DIR__ . '/../Models/ReportModel.php';

class DashboardController {
    private ReportModel $reportModel;

    public function __construct() {
        $this->reportModel = new ReportModel();
    }

    // Luồng chính: GET/POST ?route=dashboard (Trang Quản lý Tòa nhà)
    public function index(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Xử lý các thao tác POST ngay trên trang Tòa nhà (Thêm/Sửa/Xóa Tòa nhà, Thu tiền nhanh)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'add_building') {
                $code       = trim($_POST['code'] ?? '');
                $name       = trim($_POST['name'] ?? '');
                $address    = trim($_POST['address'] ?? '');
                $manager    = trim($_POST['manager'] ?? '');
                $managerAcc = trim($_POST['manager_acc'] ?? '');
                $roomPrefix = trim($_POST['room_prefix'] ?? '');
                $theme      = trim($_POST['theme'] ?? 'teal');

                if ($name !== '') {
                    $this->reportModel->addBuilding($code, $name, $address, $manager, $managerAcc, $roomPrefix, $theme);
                    $this->reportModel->logActivity("Đã thêm mới tòa nhà '{$name}' ({$code})", 'success');
                    $_SESSION['flash_success'] = "Đã thêm tòa nhà '{$name}' thành công!";
                } else {
                    $_SESSION['flash_error'] = "Vui lòng nhập tên tòa nhà.";
                }
                header("Location: index.php?route=dashboard");
                exit;
            } elseif ($action === 'edit_building') {
                $id         = trim($_POST['building_id'] ?? '');
                $code       = trim($_POST['code'] ?? '');
                $name       = trim($_POST['name'] ?? '');
                $address    = trim($_POST['address'] ?? '');
                $manager    = trim($_POST['manager'] ?? '');
                $managerAcc = trim($_POST['manager_acc'] ?? '');
                $roomPrefix = trim($_POST['room_prefix'] ?? '');

                if ($id !== '' && $name !== '') {
                    $this->reportModel->updateBuilding($id, $code, $name, $address, $manager, $managerAcc, $roomPrefix);
                    $this->reportModel->logActivity("Đã cập nhật thông tin tòa nhà '{$name}'", 'info');
                    $_SESSION['flash_success'] = "Đã cập nhật thông tin '{$name}' thành công!";
                }
                header("Location: index.php?route=dashboard");
                exit;
            } elseif ($action === 'delete_building') {
                $id = trim($_POST['building_id'] ?? '');
                if ($id !== '' && $this->reportModel->deleteBuilding($id)) {
                    $this->reportModel->logActivity("Đã xóa tòa nhà mã '{$id}' khỏi danh sách", 'warning');
                    $_SESSION['flash_success'] = "Đã xóa tòa nhà thành công!";
                } else {
                    $_SESSION['flash_error'] = "Không thể xóa tòa nhà mặc định cuối cùng.";
                }
                header("Location: index.php?route=dashboard");
                exit;
            } elseif ($action === 'pay_invoice') {
                $invoiceId = (int)($_POST['invoice_id'] ?? 0);
                if ($invoiceId > 0 && $this->reportModel->markInvoiceStatus($invoiceId, 'paid')) {
                    $_SESSION['flash_success'] = "Đã xác nhận thu tiền hóa đơn #{$invoiceId} thành công!";
                }
                header("Location: index.php?route=dashboard");
                exit;
            }
        }

        $filterBuilding = trim($_GET['building_id'] ?? 'all');
        $keyword        = trim($_GET['keyword'] ?? '');
        $status         = trim($_GET['status'] ?? '');

        $buildings     = $this->reportModel->getBuildingsWithStats();
        $stats         = $this->reportModel->getSummaryStats($filterBuilding);
        $chartData     = $this->reportModel->getMonthlyRevenue($filterBuilding);
        $debtList      = $this->reportModel->getDebtList($keyword, $status, $filterBuilding);
        $roomsList     = $this->reportModel->getRoomsList($filterBuilding);
        $contractsList = $this->reportModel->getContractsList($filterBuilding);
        $notifications = $this->reportModel->getNotifications($filterBuilding);

        $flashSuccess = $_SESSION['flash_success'] ?? '';
        $flashError   = $_SESSION['flash_error'] ?? '';
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require_once __DIR__ . '/../views/dashboard/index.php';
    }

    // Luồng mới: GET/POST ?route=overview (Trang Tổng quan Tòa nhà & Hệ thống)
    public function overview(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $buildings = $this->reportModel->getBuildingsWithStats();
        $firstBld  = !empty($buildings) ? (string)array_key_first($buildings) : 'A';

        $reqBld = trim($_GET['building_id'] ?? ($_POST['building_id'] ?? ''));
        if ($reqBld !== '' && $reqBld !== 'all' && isset($buildings[$reqBld])) {
            $_SESSION['selected_building_id'] = $reqBld;
            $building_id = $reqBld;
            $this->reportModel->logActivity("Đã chọn xem Tổng quan: " . ($buildings[$reqBld]['name'] ?? "Tòa {$reqBld}"), 'info');
        } elseif (!empty($_SESSION['selected_building_id']) && isset($buildings[$_SESSION['selected_building_id']])) {
            $building_id = $_SESSION['selected_building_id'];
        } else {
            $building_id = $firstBld;
            $_SESSION['selected_building_id'] = $building_id;
        }

        // Xử lý các thao tác POST ngay trên trang Tổng quan (Lập hóa đơn mới, Thu tiền, Hoàn tác, Xóa hóa đơn)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $redirectUrl = "index.php?route=overview&building_id=" . urlencode($building_id);

            if ($action === 'create_invoice') {
                $roomId    = (int)($_POST['room_id'] ?? 0);
                $monthYear = trim($_POST['month_year'] ?? date('m-Y'));
                $elecOld   = (int)($_POST['electric_old'] ?? 0);
                $elecNew   = (int)($_POST['electric_new'] ?? 0);
                $waterOld  = (int)($_POST['water_old'] ?? 0);
                $waterNew  = (int)($_POST['water_new'] ?? 0);

                $res = $this->reportModel->createNewInvoice($roomId, $monthYear, $elecOld, $elecNew, $waterOld, $waterNew);
                if ($res['ok']) {
                    $_SESSION['flash_success'] = $res['msg'];
                } else {
                    $_SESSION['flash_error'] = $res['msg'];
                }
                header("Location: " . $redirectUrl . "#invoice-section");
                exit;
            } elseif ($action === 'pay_invoice') {
                $invoiceId = (int)($_POST['invoice_id'] ?? 0);
                if ($invoiceId > 0 && $this->reportModel->markInvoiceStatus($invoiceId, 'paid')) {
                    $_SESSION['flash_success'] = "Đã thu tiền hóa đơn #{$invoiceId} thành công! Doanh thu thực thu đã được cập nhật.";
                }
                header("Location: " . $redirectUrl . "#invoice-section");
                exit;
            } elseif ($action === 'unpay_invoice') {
                $invoiceId = (int)($_POST['invoice_id'] ?? 0);
                if ($invoiceId > 0 && $this->reportModel->markInvoiceStatus($invoiceId, 'unpaid')) {
                    $_SESSION['flash_success'] = "Đã chuyển trạng thái hóa đơn #{$invoiceId} về Chưa thu.";
                }
                header("Location: " . $redirectUrl . "#invoice-section");
                exit;
            } elseif ($action === 'delete_invoice') {
                $invoiceId = (int)($_POST['invoice_id'] ?? 0);
                if ($invoiceId > 0 && $this->reportModel->deleteInvoice($invoiceId)) {
                    $_SESSION['flash_success'] = "Đã xóa hóa đơn #{$invoiceId} khỏi CSDL.";
                }
                header("Location: " . $redirectUrl . "#invoice-section");
                exit;
            }
        }

        $keyword   = trim($_GET['keyword'] ?? '');
        $status    = trim($_GET['status'] ?? '');
        $monthYear = trim($_GET['month_year'] ?? '');

        $buildings     = $this->reportModel->getBuildingsWithStats();
        $stats         = $this->reportModel->getSummaryStats($building_id);
        $chartData     = $this->reportModel->getMonthlyRevenue($building_id);
        $debtList      = $this->reportModel->getDebtList($keyword, $status, $building_id, $monthYear);
        $roomsList     = $this->reportModel->getRoomsList($building_id);
        $contractsList = $this->reportModel->getContractsList($building_id);
        $servicesMap   = $this->reportModel->getServicesMap();
        $notifications = $this->reportModel->getNotifications($building_id);

        $flashSuccess = $_SESSION['flash_success'] ?? '';
        $flashError   = $_SESSION['flash_error'] ?? '';
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require_once __DIR__ . '/../views/dashboard/overview.php';
    }

    // Hiển thị giao diện Đăng nhập: GET ?route=login
    public function loginView(): void {
        $error = $_GET['error'] ?? '';
        require_once __DIR__ . '/../views/auth/login.php';
    }
}
