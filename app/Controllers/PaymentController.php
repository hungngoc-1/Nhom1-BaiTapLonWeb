<?php
require_once 'app/Models/PaymentModel.php';

class PaymentController {
    private $paymentModel;

    public function __construct($pdo) {
        $this->paymentModel = new PaymentModel($pdo);
    }

    // Luồng xử lý khi ấn nút "Xác nhận thu tiền"
    public function process() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $invoiceId = $_POST['invoice_id'] ?? null;
            $phuongThuc = $_POST['phuong_thuc'] ?? 'Chuyển khoản'; // Tiền mặt hoặc Chuyển khoản

            if (!$invoiceId) {
                die("Lỗi: Không tìm thấy mã hóa đơn.");
            }

            // QUY TẮC NGHIỆP VỤ: Khóa hóa đơn đã thu tiền
            $trangThai = $this->paymentModel->getInvoiceStatus($invoiceId);
            
            if ($trangThai === 'Đã thanh toán') {
                // Chặn đứng hành động nếu hóa đơn đã chốt
                die("Lỗi: Hóa đơn này ĐÃ ĐƯỢC THANH TOÁN. Hệ thống đã khóa, không thể thu tiền hoặc chỉnh sửa thêm!");
            }

            // Nếu chưa thanh toán thì tiến hành lưu vào DB
            if ($this->paymentModel->markAsPaid($invoiceId, $phuongThuc)) {
                echo "Thành công: Đã thu tiền và khóa hóa đơn số $invoiceId!";
            } else {
                echo "Lỗi hệ thống: Không thể ghi nhận thanh toán.";
            }
        }
    }
}
?>