<?php
require_once 'app/Models/UtilityModel.php';

class UtilityController {
    private $utilityModel;

    public function __construct($pdo) {
        $this->utilityModel = new UtilityModel($pdo);
    }

    // Luồng xử lý khi người dùng ấn nút "Lưu chỉ số điện nước"
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $roomId = $_POST['room_id'] ?? null;
            $thang = $_POST['thang'] ?? null;
            $nam = $_POST['nam'] ?? null;
            $soDienMoi = $_POST['so_dien'] ?? null;
            $soNuocMoi = $_POST['so_nuoc'] ?? null;

            if (!$roomId || !$thang || !$nam || $soDienMoi === null || $soNuocMoi === null) {
                die("Lỗi: Vui lòng nhập đầy đủ thông tin.");
            }

            // PASS TEST CASE 4: Kiểm tra trùng lặp
            if ($this->utilityModel->isDuplicate($roomId, $thang, $nam)) {
                die("Lỗi: Phòng $roomId đã chốt điện nước tháng $thang/$nam. Không được nhập trùng!");
            }

            // PASS TEST CASE 3: Kiểm tra chỉ số lùi
            $lastReading = $this->utilityModel->getLastReading($roomId);
            if ($lastReading) {
                if ($soDienMoi < $lastReading['so_dien'] || $soNuocMoi < $lastReading['so_nuoc']) {
                    die("Lỗi dữ liệu: Chỉ số điện/nước mới không được nhỏ hơn kỳ trước.");
                }
            }

            // Lưu dữ liệu nếu thỏa mãn mọi điều kiện
            if ($this->utilityModel->insertReading($roomId, $thang, $nam, $soDienMoi, $soNuocMoi)) {
                echo "Thành công: Đã lưu chỉ số điện nước!";
            } else {
                echo "Lỗi hệ thống: Không thể lưu dữ liệu.";
            }
        }
    }
}
?>