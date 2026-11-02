<?php
class ReportController {
    private $repo;

    public function __construct($repo) {
        $this->repo = $repo;
    }

    public function index() {
        // Lấy dữ liệu từ Repo
        $financials = $this->repo->getMonthlyFinancialStats();
        $breakdown = $this->repo->getRevenueBreakdown();
        $topDebtors = $this->repo->getTopDebtors();
        $rooms = $this->repo->getRoomOccupancyStats();

        // 1. Xử lý mảng Tài chính 6 tháng
        $months = []; $revenues = []; $debts = [];
        $totalRevenue = 0; $totalDebt = 0;

        foreach ($financials as $row) {
            $months[] = $row['month_year'];
            $revenues[] = $row['revenue'];
            $debts[] = $row['debt'];
            $totalRevenue += $row['revenue'];
            $totalDebt += $row['debt'];
        }

        // 2. Xử lý mảng Cơ cấu doanh thu
        $breakdownLabels = []; $breakdownData = [];
        foreach ($breakdown as $row) {
            $breakdownLabels[] = $row['category'];
            $breakdownData[] = $row['total'];
        }

        // 3. Xử lý mảng Phòng trọ
        $totalRooms = 0; $rentedRooms = 0;
        foreach ($rooms as $row) {
            $totalRooms += $row['count'];
            if ($row['status'] == 'rented') $rentedRooms += $row['count'];
        }
        $occupancyRate = $totalRooms > 0 ? round(($rentedRooms / $totalRooms) * 100) : 0;

        // Gọi View
        require __DIR__ . '/../View/report_dashboard.php';
    }
}
?>