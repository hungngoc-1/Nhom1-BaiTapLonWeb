<?php
class UtilityModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    // PASS TEST CASE 4: Chặn bản ghi trùng lặp trong cùng tháng
    public function isDuplicate($roomId, $thang, $nam) {
        $sql = "SELECT id FROM chiso_diennuoc WHERE room_id = ? AND thang = ? AND nam = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roomId, $thang, $nam]);
        return $stmt->rowCount() > 0;
    }

    // PASS TEST CASE 3: Lấy chỉ số cũ để kiểm tra không cho nhập lùi
    public function getLastReading($roomId) {
        $sql = "SELECT so_dien, so_nuoc FROM chiso_diennuoc WHERE room_id = ? ORDER BY id DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$roomId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Hàm lưu vào CSDL
    public function insertReading($roomId, $thang, $nam, $soDien, $soNuoc) {
        $sql = "INSERT INTO chiso_diennuoc (room_id, thang, nam, so_dien, so_nuoc) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$roomId, $thang, $nam, $soDien, $soNuoc]);
    }
}
?>