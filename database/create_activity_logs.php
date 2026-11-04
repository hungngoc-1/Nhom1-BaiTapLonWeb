<?php
require_once __DIR__ . '/../app/Config/Database.php';

$db = Database::getConnection();

// Tạo bảng activity_logs nếu chưa có
$db->exec("
    CREATE TABLE IF NOT EXISTS `activity_logs` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `user_name` VARCHAR(100) NOT NULL,
      `role` VARCHAR(50) NOT NULL,
      `building_id` VARCHAR(10) NULL DEFAULT 'all',
      `action_type` VARCHAR(50) NOT NULL DEFAULT 'info',
      `title` VARCHAR(255) NOT NULL,
      `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
      INDEX (`building_id`),
      INDEX (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

// Xóa dữ liệu cũ nếu có
$db->exec("TRUNCATE TABLE `activity_logs`");

// Thêm các hoạt động gần đây (trong vòng 1 đến 20 ngày qua)
$logs = [
    ['Nguyễn Tuấn Dũng', 'admin', 'all', 'success', 'Đã thu tiền hóa đơn phòng A101 (Kỳ 11-2026) - Số tiền: 3.820.000 đ', date('Y-m-d H:i:s', strtotime('-15 minutes'))],
    ['Nguyễn Văn Quân', 'manager', 'A', 'success', 'Đã lập hợp đồng cọc giữ chỗ 5 ngày cho phòng A105 (Khách: Trần Thu Nga)', date('Y-m-d H:i:s', strtotime('-2 hours'))],
    ['Trần Thị Mai Lan', 'manager', 'B', 'info', 'Đã chốt chỉ số điện nước kỳ 11-2026 cho toàn bộ Tòa B (StayMate Emerald)', date('Y-m-d H:i:s', strtotime('-1 day -3 hours'))],
    ['Lê Hoàng Nam', 'manager', 'C', 'success', 'Đã thu tiền phòng tháng đầu kèm cọc cho phòng C201 (Số tiền: 4.200.000 đ)', date('Y-m-d H:i:s', strtotime('-2 days -1 hour'))],
    ['Nguyễn Tuấn Dũng', 'admin', 'all', 'info', 'Khách hàng Nguyễn Văn Hùng để lại yêu cầu tư vấn xem phòng A105', date('Y-m-d H:i:s', strtotime('-3 days -4 hours'))],
    ['Nguyễn Văn Quân', 'manager', 'A', 'warning', 'Đã gửi thông báo nhắc nợ tiền phòng tháng 11 cho các phòng chưa thanh toán', date('Y-m-d H:i:s', strtotime('-4 days -2 hours'))],
    ['Trần Thị Mai Lan', 'manager', 'B', 'success', 'Đã thanh toán hóa đơn đối tác Internet VNPT kỳ 10-2026 (Số tiền: 3.600.000 đ)', date('Y-m-d H:i:s', strtotime('-5 days -5 hours'))],
    ['Lê Hoàng Nam', 'manager', 'C', 'success', 'Đã hoàn tất thủ tục thanh lý hợp đồng và hoàn cọc 100% cho phòng C105', date('Y-m-d H:i:s', strtotime('-7 days -6 hours'))],
    ['Nguyễn Tuấn Dũng', 'admin', 'all', 'warning', 'Tịch thu tiền cọc phòng vi phạm hợp đồng bỏ phòng trước hạn (HD-FORF-A01)', date('Y-m-d H:i:s', strtotime('-10 days -3 hours'))],
    ['Nguyễn Văn Quân', 'manager', 'A', 'info', 'Cập nhật lại giá điện theo biểu giá nhà nước 3.500 đ/kWh và nước 20.000 đ/m3', date('Y-m-d H:i:s', strtotime('-15 days -7 hours'))],
    ['Trần Thị Mai Lan', 'manager', 'B', 'success', 'Cư dân phòng B201 gửi yêu cầu bổ sung thông tin CCCD bạn cùng phòng', date('Y-m-d H:i:s', strtotime('-18 days -4 hours'))],
    ['Nguyễn Tuấn Dũng', 'admin', 'all', 'info', 'Khởi tạo hệ thống quản lý 60 phòng trọ và đồng bộ số liệu 3 tòa nhà', date('Y-m-d H:i:s', strtotime('-25 days -8 hours'))],
];

$ins = $db->prepare("INSERT INTO activity_logs (user_name, role, building_id, action_type, title, created_at) VALUES (?, ?, ?, ?, ?, ?)");
foreach ($logs as $l) {
    $ins->execute($l);
}

echo "SUCCESS: Created and seeded activity_logs with " . count($logs) . " records within 30 days.\n";
