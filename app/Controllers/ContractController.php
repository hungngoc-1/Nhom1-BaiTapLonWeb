<?php
require_once __DIR__ . '/../Models/ContractModel.php';

class ContractController {
    private $contractModel;

    // Trạng thái lưu trong CSDL (ENUM) => nhãn hiển thị tiếng Việt
    public const STATUSES = [
        'active'     => 'Đang thuê',
        'expired'    => 'Hết hạn',
        'terminated' => 'Đã thanh lý',
    ];

    public function __construct() {
        $this->contractModel = new ContractModel();
    }

    public function index() {
        $keyword = trim($_GET['keyword'] ?? '');
        $status = trim($_GET['status'] ?? '');
        if (!array_key_exists($status, self::STATUSES)) $status = '';

        $contracts = $this->contractModel->getAllContracts($keyword, $status);
        $rooms = $this->contractModel->getRooms();
        $tenants = $this->contractModel->getTenants();
        $statuses = self::STATUSES;

        $flash_error = $_SESSION['flash_error'] ?? '';
        $flash_success = $_SESSION['flash_success'] ?? '';
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        require_once __DIR__ . '/../views/contracts/index.php';
    }

    // Xử lý Thêm hợp đồng
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
            $this->contractModel->createContract($data);
            $_SESSION['flash_success'] = 'Lập hợp đồng thành công!';
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = 'Không thể lưu hợp đồng: phòng hoặc khách thuê không tồn tại.';
        }
        $this->redirect('index.php?route=contracts');
    }

    // Xử lý Xóa hợp đồng (POST để tránh xóa nhầm qua link)
    public function delete() {
        $id = (int)($_POST['id'] ?? 0);
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id > 0) {
            try {
                $this->contractModel->deleteContract($id);
                $_SESSION['flash_success'] = 'Đã xóa hợp đồng.';
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = 'Không thể xóa: hợp đồng này đã có hóa đơn liên quan.';
            }
        }
        $this->redirect('index.php?route=contracts');
    }

    // Xử lý Sửa hợp đồng (Lấy dữ liệu cũ và gọi giao diện edit.php)
    public function edit() {
        $id = (int)($_GET['id'] ?? 0);

        // Gọi Model đi lấy dữ liệu của hợp đồng này
        $contract = $id > 0 ? $this->contractModel->getContractById($id) : null;
        if (!$contract) {
            $this->redirect('index.php?route=contracts', 'Không tìm thấy hợp đồng.');
        }

        $rooms = $this->contractModel->getRooms();
        $tenants = $this->contractModel->getTenants();
        $statuses = self::STATUSES;
        $flash_error = $_SESSION['flash_error'] ?? '';
        unset($_SESSION['flash_error']);

        // Gọi giao diện form sửa và truyền biến $contract sang
        require_once __DIR__ . '/../views/contracts/edit.php';
    }

    // Xử lý khi người dùng bấm nút "Lưu thay đổi" trên form Sửa
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
            // Ra lệnh Model cập nhật vào CSDL
            $this->contractModel->updateContract($id, $data);
            $_SESSION['flash_success'] = 'Cập nhật hợp đồng thành công!';
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = 'Không thể cập nhật: phòng hoặc khách thuê không tồn tại.';
        }
        // Quay về trang danh sách
        $this->redirect('index.php?route=contracts');
    }

    // ===== Hàm hỗ trợ =====

    private function getFormData() {
        return [
            'room_id'        => (int)($_POST['room_id'] ?? 0),
            'tenant_id'      => (int)($_POST['tenant_id'] ?? 0),
            'start_date'     => trim($_POST['start_date'] ?? ''),
            'end_date'       => trim($_POST['end_date'] ?? ''),
            'deposit_amount' => trim($_POST['deposit_amount'] ?? ''),
            'status'         => trim($_POST['status'] ?? 'active'),
        ];
    }

    // Quy tắc validation (theo README mục 3b)
    private function validate($data, $exclude_id = 0) {
        if ($data['room_id'] <= 0 || $data['tenant_id'] <= 0) {
            return 'Vui lòng chọn phòng và khách thuê.';
        }
        if (!$this->isDate($data['start_date']) || !$this->isDate($data['end_date'])) {
            return 'Ngày bắt đầu / kết thúc không đúng định dạng.';
        }
        if ($data['start_date'] >= $data['end_date']) {
            return 'Ngày bắt đầu phải trước ngày kết thúc.';
        }
        if (!is_numeric($data['deposit_amount']) || $data['deposit_amount'] < 0) {
            return 'Tiền cọc phải là số >= 0.';
        }
        if (!array_key_exists($data['status'], self::STATUSES)) {
            return 'Trạng thái hợp đồng không hợp lệ.';
        }
        if ($data['status'] === 'active' &&
            $this->contractModel->hasOverlap($data['room_id'], $data['start_date'], $data['end_date'], $exclude_id)) {
            return 'Phòng này đã có hợp đồng đang hiệu lực trùng thời gian.';
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
