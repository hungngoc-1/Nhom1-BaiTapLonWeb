<?php
class ServiceController {
    private $repo;

    public function __construct($repo) {
        $this->repo = $repo;
    }

    public function index() {
        $msg = '';
        $msgType = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $name = trim($_POST['service_name']);
            $price = trim($_POST['unit_price']);
            $unit = trim($_POST['unit']);

            // Validate chung
            if (empty($name)) {
                $msg = "Tên dịch vụ không được để trống."; $msgType = "danger";
            } elseif ($price < 0) {
                $msg = "Đơn giá phải là một số lớn hoặc bằng 0."; $msgType = "danger";
            } elseif (empty($unit)) {
                $msg = "Đơn vị tính không được để trống."; $msgType = "danger";
            } else {
                if ($_POST['action'] === 'add') {
                    $this->repo->create($name, $price, $unit);
                    $msg = "Thêm dịch vụ thành công!"; $msgType = "success";
                } elseif ($_POST['action'] === 'edit') {
                    $id = $_POST['id'];
                    $this->repo->update($id, $name, $price, $unit);
                    $msg = "Cập nhật dịch vụ thành công!"; $msgType = "success";
                }
            }
        }

        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            $this->repo->delete($_GET['id']);
            header("Location: index.php?route=services");
            exit;
        }

        $services = $this->repo->getAll();
        require __DIR__ . '/../View/service_list.php';
    }
}
?>