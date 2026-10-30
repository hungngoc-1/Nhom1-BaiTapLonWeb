<?php
require_once __DIR__ . '/../Models/ContractModel.php';

class ContractController {
    private $contractModel;

    public function __construct() {
        $this->contractModel = new ContractModel();
    }

    public function index() {
        $keyword = trim($_GET['keyword'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $contracts = $this->contractModel->getAllContracts($keyword, $status);
        require_once __DIR__ . '/../views/contracts/index.php';
    }

    // Xử lý Thêm hợp đồng
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'room_id' => $_POST['room_id'],
                'tenant_id' => $_POST['tenant_id'],
                'start_date' => $_POST['start_date'],
                'end_date' => $_POST['end_date'],
                'deposit_amount' => $_POST['deposit_amount'],
                'status' => $_POST['status']
            ];
            // Gọi Model để lưu vào SQL
            $this->contractModel->createContract($data);
            
            // Xong việc thì quay về trang danh sách
            header("Location: index.php?route=contracts");
            exit;
        }
    }

    // Xử lý Xóa hợp đồng
    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->contractModel->deleteContract($id);
        }
        header("Location: index.php?route=contracts");
        exit;
    }

    // Xử lý Sửa hợp đồng (Lấy dữ liệu cũ và gọi giao diện edit.php)
    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: index.php?route=contracts");
            exit;
        }

        // Gọi Model đi lấy dữ liệu của hợp đồng này
        $contract = $this->contractModel->getContractById($id);
        
        // Gọi giao diện form sửa và truyền biến $contract sang
        require_once __DIR__ . '/../views/contracts/edit.php';
    }

    // Xử lý khi người dùng bấm nút "Lưu thay đổi" trên form Sửa
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $data = [
                'room_id' => $_POST['room_id'],
                'tenant_id' => $_POST['tenant_id'],
                'start_date' => $_POST['start_date'],
                'end_date' => $_POST['end_date'],
                'deposit_amount' => $_POST['deposit_amount'],
                'status' => $_POST['status']
            ];
            
            // Ra lệnh Model cập nhật vào CSDL
            $this->contractModel->updateContract($id, $data);
            
            // Quay về trang danh sách
            header("Location: index.php?route=contracts");
            exit;
        }
    }
}
?>