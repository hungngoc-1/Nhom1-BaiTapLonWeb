<?php
require_once 'app/Models/InvoiceModel.php';
require_once 'app/Models/UtilityModel.php'; // Gọi lại file điện nước nãy bạn làm để lấy chỉ số cũ

class InvoiceController {
    private $invoiceModel;
    private $utilityModel;

    public function __construct($pdo) {
        $this->invoiceModel = new InvoiceModel($pdo);
        $this->utilityModel = new UtilityModel($pdo);
    }

    /**
     * TRẢ VỀ JSON CHO FETCH API (Xem trước tổng tiền trên màn hình)
     * Route: GET ?route=api/preview-invoice
     */
    public function previewInvoice() {
        header('Content-Type: application/json'); // Khai báo chuẩn JSON cho Javascript đọc
        
        $roomId = $_GET['room_id'] ?? 0;
        $soDienMoi = $_GET['so_dien'] ?? 0;
        $soNuocMoi = $_GET['so_nuoc'] ?? 0;
        
        // Giả sử bảng giá (Thực tế nhóm bạn có thể tạo bảng riêng, ở đây ta fix cứng theo yêu cầu cơ bản)
        $giaDien = 3500;  // 3.500đ / số
        $giaNuoc = 25000; // 25.000đ / khối
        $tienPhong = 2500000; // Tiền phòng mặc định 2.500.000đ
        $tienDichVu = 150000; // Phí rác, wifi, vệ sinh...
        
        // Lấy chỉ số tháng trước từ Database để tính số lượng tiêu thụ
        $lastReading = $this->utilityModel->getLastReading($roomId);
        $soDienCu = $lastReading ? $lastReading['so_dien'] : 0;
        $soNuocCu = $lastReading ? $lastReading['so_nuoc'] : 0;
        
        // Kiểm tra hợp lệ (Chặn nhập lùi)
        if ($soDienMoi < $soDienCu || $soNuocMoi < $soNuocCu) {
            echo json_encode(['status' => 'error', 'message' => 'Lỗi: Chỉ số mới nhỏ hơn chỉ số cũ!']);
            exit;
        }
        
        // THUẬT TOÁN TÍNH TIỀN
        $tienDien = ($soDienMoi - $soDienCu) * $giaDien;
        $tienNuoc = ($soNuocMoi - $soNuocCu) * $giaNuoc;
        $tongTien = $tienPhong + $tienDien + $tienNuoc + $tienDichVu;
        
        // Gửi kết quả JSON về cho Frontend
        echo json_encode([
            'status' => 'success',
            'data' => [
                'tien_phong' => $tienPhong,
                'tien_dien' => $tienDien,
                'tien_nuoc' => $tienNuoc,
                'tien_dichvu' => $tienDichVu,
                'tong_tien' => $tongTien
            ]
        ]);
        exit;
    }

    /**
     * LƯU HÓA ĐƠN CHÍNH THỨC VÀO DATABASE (POST)
     * Route: POST ?route=invoices/store
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $roomId = $_POST['room_id'] ?? null;
            $thang = date('m'); // Tự động lấy tháng hiện tại
            $nam = date('Y');   // Tự động lấy năm hiện tại
            
            // Nhận dữ liệu tổng tiền từ Form
            $tienPhong = $_POST['tien_phong'] ?? 0;
            $tienDien = $_POST['tien_dien'] ?? 0;
            $tienNuoc = $_POST['tien_nuoc'] ?? 0;
            $tienDichVu = $_POST['tien_dichvu'] ?? 0;
            $tongTien = $_POST['tong_tien'] ?? 0;

            if (!$roomId) {
                die("Lỗi: Không nhận diện được phòng cần lập hóa đơn.");
            }

            // PASS TEST CASE 6: Chặn lập hóa đơn trùng lặp
            if ($this->invoiceModel->isInvoiceExist($roomId, $thang, $nam)) {
                die("Lỗi: Phòng $roomId đã được lập hóa đơn tháng $thang/$nam rồi. Hệ thống từ chối tạo trùng!");
            }

            // Lưu vào Database
            if ($this->invoiceModel->createInvoice($roomId, $thang, $nam, $tienPhong, $tienDien, $tienNuoc, $tienDichVu, $tongTien)) {
                echo "Tuyệt vời: Đã chốt hóa đơn phòng $roomId thành công!";
            } else {
                echo "Lỗi hệ thống: Không thể tạo hóa đơn.";
            }
        }
    }
}
?>