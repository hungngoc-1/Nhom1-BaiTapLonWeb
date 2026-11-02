<?php
class UtilitiesController {
    public function index() {
        require_once __DIR__ . '/../views/utilities/index.php';
    }

    // Hàm trả về dữ liệu JSON cho Fetch API
    public function searchApi() {
        header('Content-Type: application/json; charset=utf-8');
        $keyword = trim($_GET['q'] ?? '');
        
        try {
          require_once __DIR__ . '/../Config/Database.php';
            $db = (new Database())->getConnection();
            
            $stmt = $db->prepare("
                SELECT u.id, u.month_year, u.electric_old, u.electric_new, u.water_old, u.water_new, r.room_number 
                FROM utilities_readings u
                JOIN rooms r ON u.room_id = r.id
                WHERE r.room_number LIKE ?
                ORDER BY u.id DESC
            ");
            $stmt->execute(['%' . $keyword . '%']);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'ok' => true,
                'data' => $data
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'message' => 'Lỗi kết nối CSDL']);
        }
        exit;
    }
}
?>