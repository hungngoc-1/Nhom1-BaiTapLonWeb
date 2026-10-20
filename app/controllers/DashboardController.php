<?php
require_once __DIR__ . '/../models/ReportModel.php';

class DashboardController {
    private ReportModel $reportModel;

    public function __construct() {
        $this->reportModel = new ReportModel();
    }

    // Luồng chính: GET ?route=dashboard
    public function index(): void {
        $keyword = trim($_GET['keyword'] ?? '');
        $status  = trim($_GET['status'] ?? '');

        $stats         = $this->reportModel->getSummaryStats();
        $chartData     = $this->reportModel->getMonthlyRevenue();
        $debtList      = $this->reportModel->getDebtList($keyword, $status);
        $notifications = $this->reportModel->getNotifications();

        require_once __DIR__ . '/../views/dashboard/index.php';
    }

    // Hiển thị giao diện Đăng nhập: GET ?route=login
    public function loginView(): void {
        $error = $_GET['error'] ?? '';
        require_once __DIR__ . '/../views/auth/login.php';
    }
}