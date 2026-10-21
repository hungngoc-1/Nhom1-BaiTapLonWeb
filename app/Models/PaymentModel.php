<?php
class PaymentModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    // Lấy trạng thái hiện tại của hóa đơn
    public function getInvoiceStatus($invoiceId) {
        $sql = "SELECT trang_thai FROM hoadon WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$invoiceId]);
        return $stmt->fetchColumn(); 
    }

    // Cập nhật trạng thái thành "Đã thanh toán"
    public function markAsPaid($invoiceId, $phuongThuc) {
        $sql = "UPDATE hoadon SET trang_thai = 'Đã thanh toán', phuong_thuc = ?, ngay_thanh_toan = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$phuongThuc, $invoiceId]);
    }
}
?>