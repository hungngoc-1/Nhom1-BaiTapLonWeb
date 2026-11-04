<?php
require_once __DIR__ . '/../Models/TenantPortalModel.php';

class TenantPortalController {
    private TenantPortalModel $model;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new TenantPortalModel();
    }

    /**
     * Trang chủ cổng khách thuê
     */
    public function index(): void {
        // Kiểm tra đăng nhập
        if (empty($_SESSION['user_id'])) {
            $_SESSION['flash_error'] = "Vui lòng đăng nhập để truy cập Cổng Khách Thuê!";
            header("Location: index.php?route=auth/login");
            exit;
        }

        $userRole = $_SESSION['role'] ?? 'tenant';
        $currentUserId = (int)$_SESSION['user_id'];
        $tenantId = $currentUserId;
        $isPreview = false;
        $allTenants = [];

        // Nếu là Admin hoặc Quản lý, cho phép xem thử cổng của khách thuê bất kỳ
        if ($userRole === 'admin' || $userRole === 'manager' || $userRole === 'landlord') {
            $isPreview = true;
            try {
                $db = Database::getConnection();
                $allTenants = $db->query("SELECT id, username, full_name, phone, email FROM users WHERE role = 'tenant' ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $allTenants = [];
            }

            if (!empty($_GET['tenant_id'])) {
                $tenantId = (int)$_GET['tenant_id'];
            } elseif (!empty($allTenants)) {
                $tenantId = (int)$allTenants[0]['id'];
            }
        }

        $profile = $this->model->getTenantProfile($tenantId);
        if (!$profile) {
            // Nếu không tìm thấy profile khách thuê
            if ($isPreview && !empty($allTenants)) {
                $tenantId = (int)$allTenants[0]['id'];
                $profile = $this->model->getTenantProfile($tenantId);
            }
        }

        $invoices = $this->model->getTenantInvoices($tenantId);
        $services = $this->model->getActiveServices();
        $contract = $this->model->getTenantContract($tenantId);
        $requests = $this->model->getTenantRequests($tenantId);

        // Thống kê nhanh hóa đơn
        $totalInvoices = count($invoices);
        $unpaidCount = 0;
        $unpaidAmount = 0.0;
        $paidCount = 0;
        foreach ($invoices as $inv) {
            if ($inv['status'] === 'unpaid') {
                $unpaidCount++;
                $unpaidAmount += (float)$inv['total_amount'];
            } else {
                $paidCount++;
            }
        }

        $flash_success = $_SESSION['flash_success'] ?? '';
        $flash_error   = $_SESSION['flash_error'] ?? '';
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require_once __DIR__ . '/../views/tenant/portal.php';
    }

    /**
     * Xử lý gửi yêu cầu điều chỉnh thông tin cá nhân lên Quản lý
     */
    public function submitRequest(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?route=tenant/portal");
            exit;
        }

        // Kiểm tra CSRF token
        $csrf = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            $_SESSION['flash_error'] = "Phiên làm việc không hợp lệ (CSRF), vui lòng thử lại!";
            header("Location: index.php?route=tenant/portal");
            exit;
        }

        $tenantId = (int)($_SESSION['user_id'] ?? 0);
        if (!empty($_POST['tenant_id']) && (($_SESSION['role'] ?? '') === 'admin' || ($_SESSION['role'] ?? '') === 'manager')) {
            $tenantId = (int)$_POST['tenant_id'];
        }

        if ($tenantId <= 0) {
            $_SESSION['flash_error'] = "Không xác định được tài khoản khách thuê!";
            header("Location: index.php?route=tenant/portal");
            exit;
        }

        $requestType = trim($_POST['request_type'] ?? 'profile_update');
        $fieldTarget = trim($_POST['field_target'] ?? 'phone');
        $oldValue    = trim($_POST['old_value'] ?? '');
        $newValue    = trim($_POST['new_value'] ?? '');
        $reason      = trim($_POST['reason'] ?? '');

        if ($newValue === '') {
            $_SESSION['flash_error'] = "Vui lòng nhập nội dung thông tin mới cần cập nhật!";
            header("Location: index.php?route=tenant/portal");
            exit;
        }

        $res = $this->model->createRequest($tenantId, $requestType, $fieldTarget, $oldValue, $newValue, $reason);
        if ($res) {
            $_SESSION['flash_success'] = "Đã gửi yêu cầu điều chỉnh thông tin lên Ban Quản Lý thành công! Quản lý sẽ duyệt và cập nhật cho bạn trong thời gian sớm nhất.";
        } else {
            $_SESSION['flash_error'] = "Có lỗi xảy ra khi gửi yêu cầu. Vui lòng thử lại!";
        }

        header("Location: index.php?route=tenant/portal" . (!empty($_POST['tenant_id']) ? "&tenant_id=" . $tenantId : ""));
        exit;
    }

    /**
     * Mở tab nhỏ hiển thị Hợp đồng dạng PDF chuẩn A4
     */
    public function contractPdf(): void {
        if (empty($_SESSION['user_id'])) {
            header("Location: index.php?route=auth/login");
            exit;
        }

        $tenantId = (int)$_SESSION['user_id'];
        $contractId = (int)($_GET['id'] ?? 0);

        if (!empty($_GET['tenant_id']) && (($_SESSION['role'] ?? '') === 'admin' || ($_SESSION['role'] ?? '') === 'manager')) {
            $tenantId = (int)$_GET['tenant_id'];
        }

        $contract = $this->model->getTenantContract($tenantId, $contractId);
        $profile = $this->model->getTenantProfile($tenantId);
        $services = $this->model->getActiveServices();

        if (!$contract) {
            echo "<div style='font-family:sans-serif; text-align:center; padding:50px;'>";
            echo "<h2 style='color:#e11d48;'>Không tìm thấy bản hợp đồng thuê phòng!</h2>";
            echo "<p>Vui lòng liên hệ Quản lý tòa nhà để được cấp hợp đồng mới.</p>";
            echo "<button onclick='window.close()' style='padding:10px 20px; border-radius:8px; border:none; background:#308f86; color:#fff; cursor:pointer;'>Đóng cửa sổ</button>";
            echo "</div>";
            exit;
        }

        require_once __DIR__ . '/../views/tenant/contract_pdf.php';
    }

    /**
     * API xem chi tiết hóa đơn (JSON)
     */
    public function invoiceDetail(): void {
        header('Content-Type: application/json; charset=utf-8');
        $invoiceId = (int)($_GET['id'] ?? 0);
        $tenantId = (int)($_SESSION['user_id'] ?? 0);

        if (!empty($_GET['tenant_id']) && (($_SESSION['role'] ?? '') === 'admin' || ($_SESSION['role'] ?? '') === 'manager')) {
            $tenantId = (int)$_GET['tenant_id'];
        }

        $inv = $this->model->getInvoiceDetail($invoiceId, $tenantId);
        if (!$inv) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy hóa đơn.']);
            exit;
        }

        echo json_encode(['success' => true, 'invoice' => $inv]);
        exit;
    }
}
