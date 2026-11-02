<?php
require_once __DIR__ . '/../Models/ContractModel.php';

class ContractController {
    private $contractModel;

    public const STATUSES = [
        'active'     => 'Đang hiệu lực',
        'terminated' => 'Đã thanh lý',
        'expired'    => 'Hết hạn',
    ];

    public function __construct() {
        $this->contractModel = new ContractModel();
    }

    public function index() {
        $keyword = trim($_GET['keyword'] ?? '');
        $status = trim($_GET['status'] ?? '');
        if (!array_key_exists($status, self::STATUSES)) $status = '';

        $contracts = $this->contractModel->getAllContracts($keyword, $status);
        $stats = $this->contractModel->getContractStats();
        $rooms = $this->contractModel->getRooms();
        $tenants = $this->contractModel->getTenants();
        $statuses = self::STATUSES;

        $flash_error = $_SESSION['flash_error'] ?? '';
        $flash_success = $_SESSION['flash_success'] ?? '';
        $flash_warning = $_SESSION['flash_warning'] ?? '';
        unset($_SESSION['flash_error'], $_SESSION['flash_success'], $_SESSION['flash_warning']);

        require_once __DIR__ . '/../views/contracts/index.php';
    }

    // Lập hợp đồng mới (theo quy tắc của quanly-nhatro cũ)
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
            $moveInCalc = $this->contractModel->calculateMoveInAmounts(
                (float)$data['monthly_rent'],
                (float)$data['service_fee'],
                (string)$data['start_date']
            );
            $_SESSION['flash_success'] = 'Đã lập hợp đồng ' . ($contract['contract_code'] ?? '') . '! ' . $moveInCalc['rule_description'];
            $this->redirect('index.php?route=contracts/detail&id=' . $contractId);
        } catch (PDOException $e) {
            $this->redirect('index.php?route=contracts', 'Không thể lưu hợp đồng: ' . $e->getMessage());
        }
    }

    // Xem văn bản hợp đồng đầy đủ điều khoản + Ký điện tử (Online) + In A4 + Lưu bản Scan
    public function detail() {
        $id = (int)($_GET['id'] ?? 0);
        $contract = $id > 0 ? $this->contractModel->getContractById($id) : null;
        if (!$contract) {
            $this->redirect('index.php?route=contracts', 'Không tìm thấy hợp đồng.');
        }

        $statuses = self::STATUSES;
        $flash_error = $_SESSION['flash_error'] ?? '';
        $flash_success = $_SESSION['flash_success'] ?? '';
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        require_once __DIR__ . '/../views/contracts/detail.php';
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
            $this->redirect('index.php?route=contracts/detail&id=' . $id, 'Vui lòng chọn file ảnh hoặc PDF bản scan hợp đồng.');
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
            $_SESSION['flash_success'] = 'Đã lưu bản Scan hợp đồng ký giấy thành công!';
        } else {
            $_SESSION['flash_error'] = 'Không thể lưu file tải lên.';
        }
        $this->redirect('index.php?route=contracts/detail&id=' . $id);
    }

    // Thanh lý hợp đồng (tự động tính hoàn cọc 100% hoặc mất cọc)
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
        $months = (int)($_POST['renewal_months'] ?? 12);
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
            $_SESSION['flash_success'] = 'Cập nhật hợp đồng thành công (Tiền cọc = 1 tháng tiền phòng)!';
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = 'Không thể cập nhật hợp đồng: ' . $e->getMessage();
        }
        $this->redirect('index.php?route=contracts');
    }

    // Xóa hợp đồng (POST)
    public function delete() {
        $id = (int)($_POST['id'] ?? ($_GET['id'] ?? 0));
        if ($id > 0) {
            try {
                $this->contractModel->deleteContract($id);
                $_SESSION['flash_success'] = 'Đã xóa hợp đồng thành công.';
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = 'Không thể xóa hợp đồng đã phát sinh hóa đơn.';
            }
        }
        $this->redirect('index.php?route=contracts');
    }

    // ===== Hàm hỗ trợ =====

    private function getFormData() {
        $monthlyRent = (float)($_POST['monthly_rent'] ?? ($_POST['deposit_amount'] ?? 0));
        $depositAmount = isset($_POST['deposit_amount']) && $_POST['deposit_amount'] !== ''
            ? (float)$_POST['deposit_amount']
            : $monthlyRent;

        return [
            'room_id'          => (int)($_POST['room_id'] ?? 0),
            'tenant_id'        => (int)($_POST['tenant_id'] ?? 0),
            'deposit_date'     => trim($_POST['deposit_date'] ?? date('Y-m-d')),
            'start_date'       => trim($_POST['start_date'] ?? ''),
            'end_date'         => trim($_POST['end_date'] ?? ''),
            'monthly_rent'     => $monthlyRent,
            'deposit_amount'   => $depositAmount,
            'electricity_rate' => (float)($_POST['electricity_rate'] ?? 3500),
            'water_rate'       => (float)($_POST['water_rate'] ?? 20000),
            'service_fee'      => (float)($_POST['service_fee'] ?? 150000),
            'signature_type'   => trim($_POST['signature_type'] ?? 'online'),
            'status'           => trim($_POST['status'] ?? 'active'),
            'notes'            => trim($_POST['notes'] ?? ''),
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
        if ($data['status'] === 'active') {
            $active = $this->contractModel->findActiveByRoomId($data['room_id'], $exclude_id);
            if ($active && $this->contractModel->hasOverlap($data['room_id'], $data['start_date'], $data['end_date'], $exclude_id)) {
                return 'Phòng này đang có hợp đồng hiệu lực (' . ($active['contract_code'] ?? ('#' . $active['id'])) . '). Vui lòng thanh lý trước khi tạo mới.';
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
