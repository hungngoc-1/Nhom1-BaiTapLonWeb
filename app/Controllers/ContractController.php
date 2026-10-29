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

    // Xử lý Sửa hợp đồng (Tạo khung giữ chỗ)
    public function edit() {
        $id = $_GET['id'] ?? null;
        // Tạm thời hiển thị thông báo, vì chúng ta chưa tạo file giao diện edit.php
        echo "<div style='padding: 50px; font-family: sans-serif; text-align: center;'>";
        echo "<h2>Giao diện Sửa hợp đồng ID: $id đang được xây dựng...</h2>";
        echo "<a href='index.php?route=contracts' style='padding: 10px 20px; background: #6d28d9; color: white; text-decoration: none; border-radius: 8px;'>Quay lại trang Hợp đồng</a>";
        echo "</div>";
    }
}
?>