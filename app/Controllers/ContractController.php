<?php
require_once __DIR__ . '/../Models/ContractModel.php';

class ContractController {
    private $contractModel;

    public const STATUSES = [
        'active'     => 'Đang hiệu lực',
        'terminated' => 'Đã thanh lý',
        'expired'    => 'Hết hạn (Đã hủy)',
    ];

    public const CONTRACT_TYPES = [
        'deposit_hold'   => 'HĐ 1: Đặt cọc giữ chỗ (5 ngày - 50% cọc)',
        'official_lease' => 'HĐ 2: Thuê chính thức (6 tháng - 100% cọc & thu trước 1 tháng phòng)',
    ];

    public function __construct() {
        $this->contractModel = new ContractModel();
    }

    public function index() {
        $keyword = trim($_GET['keyword'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $contractType = trim($_GET['contract_type'] ?? '');
        if (!array_key_exists($status, self::STATUSES)) $status = '';
        if (!array_key_exists($contractType, self::CONTRACT_TYPES)) $contractType = '';

        $contracts = $this->contractModel->getAllContracts($keyword, $status, $contractType);
        $stats = $this->contractModel->getContractStats();
        $rooms = $this->contractModel->getRooms();
        $tenants = $this->contractModel->getTenants();
        $statuses = self::STATUSES;
        $contractTypes = self::CONTRACT_TYPES;

        // Xử lý pre-fill nếu được chuyển từ trang Khách thuê hoặc trang Xem phòng
        $preTenantId = (int)($_GET['tenant_id'] ?? 0);
        $preRoomId = (int)($_GET['room_id'] ?? 0);
        $preParentId = (int)($_GET['upgrade_from'] ?? 0);
        $preContractType = trim($_GET['type'] ?? '');

        $flash_error = $_SESSION['flash_error'] ?? '';
        $flash_success = $_SESSION['flash_success'] ?? '';
        $flash_warning = $_SESSION['flash_warning'] ?? '';
        unset($_SESSION['flash_error'], $_SESSION['flash_success'], $_SESSION['flash_warning']);

        require_once __DIR__ . '/../views/contracts/index.php';
    }

    // Lập hợp đồng mới (HĐ 1 hoặc HĐ 2)
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?route=contracts');
        }

        $data = $this->getFormData();
        $error = $this->validate($data);
        if ($error) {
            $this->redirect('index.php?route=contracts', $error);
        }

        try {
            $contractId = $this->contractModel->createContract($data);
            $contract = $this->contractModel->getContractById($contractId);
            $code = $contract['contract_code'] ?? ('#' . $contractId);

            if (($data['contract_type'] ?? '') === 'deposit_hold') {
                $depFmt = number_format((float)$contract['deposit_amount'], 0, ',', '.') . ' đ';
                $_SESSION['flash_success'] = "Đã lập Hợp đồng 1 (Đặt cọc giữ chỗ 5 ngày) {$code}! Khách đã đóng 50% tiền cọc ({$depFmt}). Hợp đồng có hiệu lực trong 5 ngày và sẽ tự động hủy nếu quá hạn.";
            } else {
                $depFmt = number_format((float)$contract['deposit_amount'], 0, ',', '.') . ' đ';
                $rentFmt = number_format((float)$contract['monthly_rent'], 0, ',', '.') . ' đ';
                $msg = "Đã lập Hợp đồng 2 (Thuê chính thức 6 tháng) {$code}! Đã hoàn thành 100% tiền cọc ({$depFmt}) và thu trước 01 tháng tiền phòng ({$rentFmt}) tự động cộng vào Doanh thu.";
                if (!empty($data['parent_contract_id'])) {
                    $msg .= " (Kế thừa từ HĐ giữ chỗ #" . (int)$data['parent_contract_id'] . ")";
                }
                $_SESSION['flash_success'] = $msg;
            }

            $this->redirect('index.php?route=contracts/detail&id=' . $contractId);
        } catch (PDOException $e) {
            $this->redirect('index.php?route=contracts', 'Không thể lưu hợp đồng: ' . $e->getMessage());
        }
    }

    // Xem văn bản hợp đồng đầy đủ điều khoản + Ký giấy in A4 + Ký online + Lưu bản Scan
    public function detail() {
        $id = (int)($_GET['id'] ?? 0);
        $contract = $id > 0 ? $this->contractModel->getContractById($id) : null;
        if (!$contract) {
            $this->redirect('index.php?route=contracts', 'Không tìm thấy hợp đồng.');
        }

        $statuses = self::STATUSES;
        $contractTypes = self::CONTRACT_TYPES;
        $flash_error = $_SESSION['flash_error'] ?? '';
        $flash_success = $_SESSION['flash_success'] ?? '';
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        require_once __DIR__ . '/../views/contracts/detail.php';
    }

    // Trang dành riêng cho phân quyền khách thuê xem hợp đồng
    public function tenantContract() {
        $contractId = (int)($_GET['id'] ?? 0);
        $tenantId = (int)($_GET['tenant_id'] ?? 0);

        if ($contractId > 0) {
            $contract = $this->contractModel->getContractById($contractId);
        } elseif ($tenantId > 0) {
            $contract = $this->contractModel->getContractByTenantId($tenantId);
        } elseif (!empty($_SESSION['user']['id']) && $_SESSION['user']['role'] === 'tenant') {
            $contract = $this->contractModel->getContractByTenantId((int)$_SESSION['user']['id']);
        } else {
            $contract = null;
        }

        require_once __DIR__ . '/../views/contracts/tenant_view.php';
    }

    // REST API endpoint cho bạn làm phân quyền khách thuê gọi lấy dữ liệu hợp đồng
    public function apiTenantContract() {
        header('Content-Type: application/json; charset=utf-8');
        $contractId = (int)($_GET['id'] ?? 0);
        $tenantId = (int)($_GET['tenant_id'] ?? 0);

        if ($contractId > 0) {
            $contract = $this->contractModel->getContractById($contractId);
        } elseif ($tenantId > 0) {
            $contract = $this->contractModel->getContractByTenantId($tenantId);
        } else {
            $contract = null;
        }

        if (!$contract) {
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy hợp đồng cho khách thuê này.'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        echo json_encode([
            'success'               => true,
            'contract_id'           => (int)$contract['id'],
            'contract_code'         => $contract['contract_code'],
            'contract_type'         => $contract['contract_type'],
            'contract_type_label'   => $contract['contract_type'] === 'deposit_hold' ? 'HĐ 1 (Đặt cọc giữ chỗ 5 ngày)' : 'HĐ 2 (Thuê chính thức 6 tháng)',
            'room_id'               => (int)$contract['room_id'],
            'room_number'           => $contract['room_number'],
            'tenant_id'             => (int)$contract['tenant_id'],
            'tenant_name'           => $contract['full_name'],
            'tenant_phone'          => $contract['phone'],
            'tenant_email'          => $contract['email'],
            'start_date'            => $contract['start_date'],
            'end_date'              => $contract['end_date'],
            'monthly_rent'          => (float)$contract['monthly_rent'],
            'deposit_amount'        => (float)$contract['deposit_amount'],
            'deposit_paid_percent'  => (int)$contract['deposit_paid_percent'],
            'deposit_status'        => $contract['deposit_status'],
            'first_month_rent_paid' => (int)($contract['first_month_rent_paid'] ?? 0),
            'first_month_invoice_id'=> $contract['first_month_invoice_id'],
            'signature_type'        => $contract['signature_type'],
            'signature_status'      => $contract['signature_status'],
            'scan_file'             => $contract['scan_file'],
            'has_scan'              => !empty($contract['scan_file']),
            'status'                => $contract['status'],
            'days_left'             => (int)($contract['days_left'] ?? 0),
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    // Ký hợp đồng điện tử (Online Canvas)
    public function signOnline() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?route=contracts');
        }
        $id = (int)($_POST['id'] ?? 0);
        $contract = $this->contractModel->getContractById($id);
        if (!$contract) {
            $this->redirect('index.php?route=contracts', 'Không tìm thấy hợp đồng.');
        }

        $tenantSig = !empty($_POST['tenant_signature']) ? $_POST['tenant_signature'] : null;
        $managerSig = !empty($_POST['manager_signature']) ? $_POST['manager_signature'] : null;

        if ($this->contractModel->saveOnlineSignature($id, $tenantSig, $managerSig)) {
            $_SESSION['flash_success'] = 'Đã lưu chữ ký hợp đồng điện tử thành công!';
        } else {
            $_SESSION['flash_error'] = 'Không thể lưu chữ ký điện tử.';
        }
        $this->redirect('index.php?route=contracts/detail&id=' . $id);
    }

    // Tải lên và lưu bản Scan hợp đồng ký giấy
    public function uploadScan() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?route=contracts');
        }
        $id = (int)($_POST['id'] ?? 0);
        $contract = $this->contractModel->getContractById($id);
        if (!$contract) {
            $this->redirect('index.php?route=contracts', 'Không tìm thấy hợp đồng.');
        }

        if (empty($_FILES['scan_file']['tmp_name']) || $_FILES['scan_file']['error'] !== UPLOAD_ERR_OK) {
            $this->redirect('index.php?route=contracts/detail&id=' . $id, 'Vui lòng chọn file ảnh hoặc PDF bản scan hợp đồng đã ký.');
        }

        $ext = strtolower(pathinfo($_FILES['scan_file']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
        if (!in_array($ext, $allowed, true)) {
            $this->redirect('index.php?route=contracts/detail&id=' . $id, 'Chỉ hỗ trợ file ảnh (JPG, PNG, WEBP) hoặc PDF.');
        }

        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/contracts';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $safeCode = preg_replace('/[^A-Za-z0-9_-]/', '_', $contract['contract_code'] ?? ('HD_' . $id));
        $fileName = 'scan_' . $safeCode . '_' . time() . '.' . $ext;
        $targetPath = $uploadDir . '/' . $fileName;

        if (move_uploaded_file($_FILES['scan_file']['tmp_name'], $targetPath)) {
            $this->contractModel->saveScanFile($id, 'public/uploads/contracts/' . $fileName);
            $_SESSION['flash_success'] = 'Đã lưu bản Scan hợp đồng ký giấy thành công! Khách thuê có thể xem ngay trên cổng khách thuê.';
        } else {
            $_SESSION['flash_error'] = 'Không thể lưu file tải lên vào máy chủ.';
        }
        $this->redirect('index.php?route=contracts/detail&id=' . $id);
    }

    // Thanh lý hợp đồng
    public function terminate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?route=contracts');
        }
        $id = (int)($_POST['id'] ?? 0);
        $moveOutDate = !empty($_POST['move_out_date']) ? $_POST['move_out_date'] : date('Y-m-d');
        $reason = trim($_POST['termination_reason'] ?? '');

        $res = $this->contractModel->terminateContract($id, $moveOutDate, $reason);
        if ($res) {
            if ($res['is_completed_term']) {
                $_SESSION['flash_success'] = 'Đã thanh lý hợp đồng! ' . $res['reason'];
            } else {
                $_SESSION['flash_warning'] = 'Đã thanh lý hợp đồng trước hạn! ' . $res['reason'];
            }
        } else {
            $_SESSION['flash_error'] = 'Không thể thanh lý hợp đồng.';
        }
        $this->redirect('index.php?route=contracts');
    }

    // Gia hạn hợp đồng
    public function renew() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?route=contracts');
        }
        $id = (int)($_POST['id'] ?? 0);
        $months = (int)($_POST['renewal_months'] ?? 6);
        if ($this->contractModel->renewContract($id, $months)) {
            $c = $this->contractModel->getContractById($id);
            $_SESSION['flash_success'] = 'Đã gia hạn hợp đồng ' . ($c['contract_code'] ?? '') . ' thêm ' . $months . ' tháng (đến ngày ' . date('d/m/Y', strtotime($c['end_date'])) . ')!';
        } else {
            $_SESSION['flash_error'] = 'Không thể gia hạn hợp đồng.';
        }
        $this->redirect('index.php?route=contracts');
    }

    // Form sửa hợp đồng
    public function edit() {
        $id = (int)($_GET['id'] ?? 0);
        $contract = $id > 0 ? $this->contractModel->getContractById($id) : null;
        if (!$contract) {
            $this->redirect('index.php?route=contracts', 'Không tìm thấy hợp đồng.');
        }

        $rooms = $this->contractModel->getRooms();
        $tenants = $this->contractModel->getTenants();
        $statuses = self::STATUSES;
        $contractTypes = self::CONTRACT_TYPES;
        $flash_error = $_SESSION['flash_error'] ?? '';
        unset($_SESSION['flash_error']);

        require_once __DIR__ . '/../views/contracts/edit.php';
    }

    // Lưu chỉnh sửa hợp đồng
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?route=contracts');
        }

        $id = (int)($_POST['id'] ?? 0);
        $data = $this->getFormData();
        $error = $this->validate($data, $id);
        if ($error) {
            $this->redirect('index.php?route=contracts/edit&id=' . $id, $error);
        }

        try {
            $this->contractModel->updateContract($id, $data);
            $_SESSION['flash_success'] = 'Cập nhật thông tin hợp đồng thành công!';
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = 'Không thể cập nhật hợp đồng: ' . $e->getMessage();
        }
        $this->redirect('index.php?route=contracts');
    }

    // Xóa hợp đồng
    public function delete() {
        $id = (int)($_POST['id'] ?? ($_GET['id'] ?? 0));
        if ($id > 0) {
            try {
                $this->contractModel->deleteContract($id);
                $_SESSION['flash_success'] = 'Đã xóa hợp đồng thành công.';
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = 'Không thể xóa hợp đồng đã phát sinh hóa đơn liên quan.';
            }
        }
        $this->redirect('index.php?route=contracts');
    }

    // ===== Hàm hỗ trợ =====

    private function getFormData() {
        $contractType = trim($_POST['contract_type'] ?? 'official_lease');
        $monthlyRent = (float)($_POST['monthly_rent'] ?? 0);
        $parentContractId = !empty($_POST['parent_contract_id']) ? (int)$_POST['parent_contract_id'] : null;

        $startDate = trim($_POST['start_date'] ?? date('Y-m-d'));
        if ($contractType === 'deposit_hold') {
            $endDate = date('Y-m-d', strtotime($startDate . ' + 5 days'));
            $depositAmount = round($monthlyRent * 0.5, 2);
            $depositPaidPercent = 50;
        } else {
            // Hợp đồng chính thức cố định thời hạn đúng 6 tháng theo yêu cầu
            $endDate = date('Y-m-d', strtotime($startDate . ' + 6 months'));
            $depositAmount = $monthlyRent;
            $depositPaidPercent = 100;
        }

        return [
            'contract_code'        => trim($_POST['contract_code'] ?? ''),
            'contract_type'        => $contractType,
            'parent_contract_id'   => $parentContractId,
            'room_id'              => (int)($_POST['room_id'] ?? 0),
            'tenant_id'            => (int)($_POST['tenant_id'] ?? 0),
            'deposit_date'         => trim($_POST['deposit_date'] ?? date('Y-m-d')),
            'start_date'           => $startDate,
            'end_date'             => $endDate,
            'monthly_rent'         => $monthlyRent,
            'deposit_amount'       => $depositAmount,
            'deposit_paid_percent' => $depositPaidPercent,
            'electricity_rate'     => (float)($_POST['electricity_rate'] ?? 3500),
            'water_rate'           => (float)($_POST['water_rate'] ?? 20000),
            'service_fee'          => (float)($_POST['service_fee'] ?? 150000),
            'signature_type'       => trim($_POST['signature_type'] ?? 'paper'),
            'status'               => trim($_POST['status'] ?? 'active'),
            'notes'                => trim($_POST['notes'] ?? ''),
        ];
    }

    private function validate($data, $exclude_id = 0) {
        if ($data['room_id'] <= 0 || $data['tenant_id'] <= 0) {
            return 'Vui lòng chọn phòng trọ và khách thuê.';
        }
        if (!$this->isDate($data['start_date']) || !$this->isDate($data['end_date'])) {
            return 'Ngày bắt đầu / kết thúc không đúng định dạng.';
        }
        if ($data['start_date'] >= $data['end_date']) {
            return 'Ngày bắt đầu phải trước ngày kết thúc.';
        }
        if ($data['monthly_rent'] <= 0) {
            return 'Giá thuê hàng tháng phải lớn hơn 0.';
        }
        if ($data['deposit_amount'] < 0) {
            return 'Tiền đặt cọc không được âm.';
        }
        if (!array_key_exists($data['status'], self::STATUSES)) {
            return 'Trạng thái hợp đồng không hợp lệ.';
        }

        // Kiểm tra trùng lặp phòng nếu hợp đồng đang active
        if ($data['status'] === 'active') {
            $parentContractId = (int)($data['parent_contract_id'] ?? 0);
            $effectiveExcludeId = $exclude_id ?: $parentContractId;

            $active = $this->contractModel->findActiveByRoomId($data['room_id'], $effectiveExcludeId);
            if ($active && $this->contractModel->hasOverlap($data['room_id'], $data['start_date'], $data['end_date'], $effectiveExcludeId)) {
                return 'Phòng này đang có hợp đồng hiệu lực (' . ($active['contract_code'] ?? ('#' . $active['id'])) . '). Vui lòng thanh lý hoặc nâng cấp hợp đồng trước khi tạo mới.';
            }
        }
        return '';
    }

    private function isDate($value) {
        $d = DateTime::createFromFormat('Y-m-d', $value);
        return $d && $d->format('Y-m-d') === $value;
    }

    private function redirect($url, $error = '') {
        if ($error) $_SESSION['flash_error'] = $error;
        header("Location: $url");
        exit;
    }
}
?>