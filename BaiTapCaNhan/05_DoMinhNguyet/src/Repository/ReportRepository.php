<?php
class ReportRepository {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // 1. Thống kê Doanh thu & Công nợ theo tháng (Biểu đồ cột)
    public function getMonthlyFinancialStats() {
        $sql = "SELECT month_year,
                       SUM(CASE WHEN status = 'paid' THEN total_amount ELSE 0 END) as revenue,
                       SUM(CASE WHEN status = 'unpaid' THEN total_amount ELSE 0 END) as debt
                FROM invoices
                GROUP BY month_year
                ORDER BY RIGHT(month_year, 4) ASC, LEFT(month_year, 2) ASC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. Cơ cấu nguồn thu: Tiền phòng vs Tiền dịch vụ (Biểu đồ tròn)
    public function getRevenueBreakdown() {
        $sql = "SELECT 
                    COALESCE(s.service_name, 'Tiền Phòng') as category,
                    SUM(id.subtotal) as total
                FROM invoice_details id
                LEFT JOIN services s ON id.service_id = s.id
                JOIN invoices i ON id.invoice_id = i.id
                WHERE i.status = 'paid'
                GROUP BY category";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. Danh sách khách nợ đọng cao nhất (Bảng chi tiết)
    public function getTopDebtors() {
        $sql = "SELECT r.room_number, u.full_name, i.month_year, i.total_amount 
                FROM invoices i
                JOIN contracts c ON i.contract_id = c.id
                JOIN users u ON c.tenant_id = u.id
                JOIN rooms r ON i.room_id = r.id
                WHERE i.status = 'unpaid'
                ORDER BY i.total_amount DESC LIMIT 5";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // 4. Tỷ lệ lấp đầy phòng
    public function getRoomOccupancyStats() {
        $sql = "SELECT status, COUNT(*) as count FROM rooms GROUP BY status";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>