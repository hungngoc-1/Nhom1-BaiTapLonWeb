<?php
class InvoiceModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    /**
     * PASS TEST CASE 6: Chặn tạo hóa đơn trùng lặp trong cùng tháng
     */
    public function isInvoiceExist($roomId, $thang, $nam) {
        $sql = "SELECT id FROM hoadon WHERE room_id = ? AND thang = ? AND nam = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roomId, $thang, $nam]);
        return $stmt->rowCount() > 0; // Trả về true nếu phòng này đã có hóa đơn tháng này
    }

    /**
     * Hàm lưu hóa đơn mới vào CSDL
     */
    public function createInvoice($roomId, $thang, $nam, $tienPhong, $tienDien, $tienNuoc, $tienDichVu, $tongTien) {
        // Ghi chú: Mặc định trạng thái khi mới tạo là 'Chưa thanh toán'
        $sql = "INSERT INTO hoadon (room_id, thang, nam, tien_phong, tien_dien, tien_nuoc, tien_dichvu, tong_tien, trang_thai) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Chưa thanh toán')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$roomId, $thang, $nam, $tienPhong, $tienDien, $tienNuoc, $tienDichVu, $tongTien]);
    }
}
?>