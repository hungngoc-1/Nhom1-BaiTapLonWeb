<?php
class UtilitiesController {
    public function index() {
        require_once __DIR__ . '/../views/utilities/index.php';
    }

    // Hàm trả về dữ liệu JSON cho Fetch API (hỗ trợ tìm kiếm theo phòng/khách, lọc theo kỳ và trạng thái hóa đơn)
    public function searchApi() {
        header('Content-Type: application/json; charset=utf-8');
        $keyword = trim($_GET['q'] ?? '');
        $monthYear = trim($_GET['month_year'] ?? '');
        $roomFilter = trim($_GET['room_id'] ?? '');
        $invoiceStatus = trim($_GET['invoice_status'] ?? '');

        try {
            require_once __DIR__ . '/../Config/Database.php';
            $db = Database::getConnection();

            // Lấy đơn giá điện nước hiện tại
            $services = $db->query("SELECT service_name, unit_price, unit FROM services")->fetchAll(PDO::FETCH_ASSOC);
            $elecPrice = 3500;
            $waterPrice = 20000;
            foreach ($services as $srv) {
                $nameLower = mb_strtolower($srv['service_name'], 'UTF-8');
                if (strpos($nameLower, 'điện') !== false || strpos($nameLower, 'dien') !== false) {
                    $elecPrice = (float)$srv['unit_price'];
                } elseif (strpos($nameLower, 'nước') !== false || strpos($nameLower, 'nuoc') !== false) {
                    $waterPrice = (float)$srv['unit_price'];
                }
            }

            $sql = "
                SELECT 
                    u.id,
                    u.room_id,
                    u.month_year,
                    u.electric_old,
                    u.electric_new,
                    u.water_old,
                    u.water_new,
                    r.room_number,
                    r.price AS room_price,
                    (
                        SELECT usr.full_name 
                        FROM contracts c 
                        JOIN users usr ON c.tenant_id = usr.id 
                        WHERE c.room_id = r.id 
                        ORDER BY (c.status = 'active') DESC, c.id DESC LIMIT 1
                    ) AS tenant_name,
                    inv.id AS invoice_id,
                    inv.status AS invoice_status,
                    inv.total_amount AS invoice_total
                FROM utilities_readings u
                JOIN rooms r ON u.room_id = r.id
                LEFT JOIN invoices inv ON inv.room_id = u.room_id AND inv.month_year = u.month_year
                WHERE 1=1
            ";

            $params = [];
            require_once __DIR__ . '/../Models/ReportModel.php';
            $mgr = ReportModel::getManagerBuildingInfo();
            if ($mgr) {
                $sql .= " AND (r.building_id = ? OR r.room_number LIKE ?) ";
                $params[] = $mgr['id'];
                $params[] = $mgr['prefix'] . '%';
            }

            if ($keyword !== '') {
                $cleanRoom = preg_replace('/^[pP]/', '', $keyword);
                $sql .= " AND (
                    r.room_number LIKE ? 
                    OR r.room_number LIKE ? 
                    OR u.month_year LIKE ? 
                    OR EXISTS (
                        SELECT 1 FROM contracts c2 JOIN users usr2 ON c2.tenant_id = usr2.id
                        WHERE c2.room_id = r.id AND usr2.full_name LIKE ?
                    )
                )";
                $params[] = '%' . $keyword . '%';
                $params[] = '%' . $cleanRoom . '%';
                $params[] = '%' . $keyword . '%';
                $params[] = '%' . $keyword . '%';
            }

            if ($monthYear !== '') {
                $sql .= " AND u.month_year = ?";
                $params[] = $monthYear;
            }

            if ($roomFilter !== '' && is_numeric($roomFilter)) {
                $sql .= " AND u.room_id = ?";
                $params[] = (int)$roomFilter;
            }

            if ($invoiceStatus === 'pending') {
                $checkPeriod = $monthYear !== '' ? $monthYear : date('m-Y');
                $sql = "
                    SELECT 
                        0 AS id,
                        r.id AS room_id,
                        ? AS month_year,
                        COALESCE((
                            SELECT u2.electric_new FROM utilities_readings u2 WHERE u2.room_id = r.id ORDER BY SUBSTRING(u2.month_year, 4, 4) DESC, SUBSTRING(u2.month_year, 1, 2) DESC, u2.id DESC LIMIT 1
                        ), 0) AS electric_old,
                        COALESCE((
                            SELECT u2.electric_new FROM utilities_readings u2 WHERE u2.room_id = r.id ORDER BY SUBSTRING(u2.month_year, 4, 4) DESC, SUBSTRING(u2.month_year, 1, 2) DESC, u2.id DESC LIMIT 1
                        ), 0) AS electric_new,
                        COALESCE((
                            SELECT u2.water_new FROM utilities_readings u2 WHERE u2.room_id = r.id ORDER BY SUBSTRING(u2.month_year, 4, 4) DESC, SUBSTRING(u2.month_year, 1, 2) DESC, u2.id DESC LIMIT 1
                        ), 0) AS water_old,
                        COALESCE((
                            SELECT u2.water_new FROM utilities_readings u2 WHERE u2.room_id = r.id ORDER BY SUBSTRING(u2.month_year, 4, 4) DESC, SUBSTRING(u2.month_year, 1, 2) DESC, u2.id DESC LIMIT 1
                        ), 0) AS water_new,
                        r.room_number,
                        r.price AS room_price,
                        (
                            SELECT usr.full_name FROM contracts c JOIN users usr ON c.tenant_id = usr.id WHERE c.room_id = r.id AND c.status = 'active' ORDER BY c.id DESC LIMIT 1
                        ) AS tenant_name,
                        NULL AS invoice_id,
                        NULL AS invoice_status,
                        0 AS invoice_total,
                        1 AS is_pending_unrecorded
                    FROM rooms r
                    WHERE r.status = 'rented'
                    AND NOT EXISTS (
                        SELECT 1 FROM utilities_readings u WHERE u.room_id = r.id AND u.month_year = ?
                    )
                ";
                $params = [$checkPeriod, $checkPeriod];
                if ($roomFilter !== '' && is_numeric($roomFilter)) {
                    $sql .= " AND r.id = ?";
                    $params[] = (int)$roomFilter;
                }
                if ($keyword !== '') {
                    $cleanRoom = preg_replace('/^[pP]/', '', $keyword);
                    $sql .= " AND (r.room_number LIKE ? OR r.room_number LIKE ?)";
                    $params[] = '%' . $keyword . '%';
                    $params[] = '%' . $cleanRoom . '%';
                }
                $sql .= " ORDER BY r.room_number ASC";
            } elseif ($invoiceStatus === 'paid') {
                $sql .= " AND inv.id IS NOT NULL AND inv.status = 'paid'";
            } elseif ($invoiceStatus === 'unpaid') {
                $sql .= " AND inv.id IS NOT NULL AND inv.status = 'unpaid'";
            } elseif ($invoiceStatus === 'recorded') {
                $sql .= " AND u.id IS NOT NULL AND (inv.id IS NULL OR inv.status = 'unpaid')";
            } elseif ($invoiceStatus === 'created') {
                $sql .= " AND inv.id IS NOT NULL";
            } elseif ($invoiceStatus === 'not_created') {
                $sql .= " AND inv.id IS NULL";
            }

            $sql .= " ORDER BY SUBSTRING(u.month_year, 4, 4) DESC, SUBSTRING(u.month_year, 1, 2) DESC, r.room_number ASC";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($data as &$row) {
                $elecUsed = max(0, (int)$row['electric_new'] - (int)$row['electric_old']);
                $waterUsed = max(0, (int)$row['water_new'] - (int)$row['water_old']);
                $row['electric_used'] = $elecUsed;
                $row['water_used'] = $waterUsed;
                $row['electric_cost'] = $elecUsed * $elecPrice;
                $row['water_cost'] = $waterUsed * $waterPrice;
                $row['total_utility_cost'] = $row['electric_cost'] + $row['water_cost'];
            }
            unset($row);

            echo json_encode([
                'ok' => true,
                'elec_price' => $elecPrice,
                'water_price' => $waterPrice,
                'data' => $data
            ], JSON_UNESCAPED_UNICODE);
            exit;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'msg' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}
