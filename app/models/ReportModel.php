<?php
class ReportModel {
    // 1. Thống kê tổng quan số lượng phòng & công nợ
    public function getSummaryStats(): array {
        return [
            'total_rooms'     => 24,
            'empty_rooms'     => 6,
            'rented_rooms'    => 18,
            'unpaid_invoices' => 5,
            'total_debt'      => 8450000
        ];
    }

    // 2. Dữ liệu biểu đồ doanh thu 6 tháng gần nhất
    public function getMonthlyRevenue(): array {
        return [
            'labels' => ['Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10'],
            'revenues' => [32000000, 35500000, 34000000, 38000000, 41000000, 39500000],
            'debts'    => [2500000, 1800000, 3200000, 1500000, 4200000, 8450000]
        ];
    }

    // 3. Danh sách hóa đơn công nợ (có hỗ trợ lọc theo từ khóa và trạng thái)
    public function getDebtList(string $keyword = '', string $status = ''): array {
        $data = [
            ['invoice_code' => 'HD202610-01', 'room' => 'P101', 'tenant' => 'Nguyễn Văn An', 'month' => '10/2026', 'electric' => 350000, 'water' => 100000, 'room_price' => 2500000, 'total' => 2950000, 'status' => 'unpaid'],
            ['invoice_code' => 'HD202610-02', 'room' => 'P104', 'tenant' => 'Trần Thị Bình', 'month' => '10/2026', 'electric' => 420000, 'water' => 120000, 'room_price' => 2800000, 'total' => 3340000, 'status' => 'overdue'],
            ['invoice_code' => 'HD202610-03', 'room' => 'P202', 'tenant' => 'Lê Hoàng Nam', 'month' => '10/2026', 'electric' => 280000, 'water' => 80000, 'room_price' => 1800000, 'total' => 2160000, 'status' => 'unpaid'],
            ['invoice_code' => 'HD202609-08', 'room' => 'P301', 'tenant' => 'Phạm Minh Tuấn', 'month' => '09/2026', 'electric' => 310000, 'water' => 90000, 'room_price' => 2500000, 'total' => 2900000, 'status' => 'paid'],
            ['invoice_code' => 'HD202610-05', 'room' => 'P205', 'tenant' => 'Hoàng Thu Trang', 'month' => '10/2026', 'electric' => 390000, 'water' => 110000, 'room_price' => 2700000, 'total' => 3200000, 'status' => 'paid'],
        ];

        return array_filter($data, function ($item) use ($keyword, $status) {
            $matchKeyword = empty($keyword) || 
                            stripos($item['room'], $keyword) !== false || 
                            stripos($item['tenant'], $keyword) !== false ||
                            stripos($item['invoice_code'], $keyword) !== false;
            $matchStatus = empty($status) || $item['status'] === $status;
            return $matchKeyword && $matchStatus;
        });
    }

    // 4. Thông báo hệ thống trên Navbar
    public function getNotifications(): array {
        return [
            ['title' => 'P104 quá hạn thanh toán hóa đơn T10/2026', 'time' => '10 phút trước', 'type' => 'danger'],
            ['title' => 'P202 vừa cập nhật chỉ số điện nước mới', 'time' => '1 giờ trước', 'type' => 'info'],
            ['title' => 'Hợp đồng phòng P305 sắp hết hạn trong 7 ngày', 'time' => 'Hôm qua', 'type' => 'warning'],
        ];
    }
}