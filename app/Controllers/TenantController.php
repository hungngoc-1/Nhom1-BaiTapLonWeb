<?php
require_once __DIR__ . '/../Models/TenantModel.php';

class TenantController {
    private $model;

    public function __construct() {
        $this->model = new TenantModel();
    }

    public function index() {
        $keyword = trim($_GET['keyword'] ?? '');
        
        // Lấy danh sách khách thuê từ CSDL
        $tenants = $this->model->getTenants($keyword);
        
        // Gọi file View
        require_once __DIR__ . '/../views/tenants/index.php';
    }
}
?>