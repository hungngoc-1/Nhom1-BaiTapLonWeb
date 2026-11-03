<?php
require_once __DIR__ . '/../Models/ReportModel.php';
require_once __DIR__ . '/../Config/Database.php';

/**
 * ReportApiController - Phân hệ API Báo cáo & Thống kê dữ liệu (JSON RESTful)
 * Tác giả phụ trách: Người 5 — Đỗ Minh Nguyệt
 * Định dạng phản hồi: application/json chuẩn RESTful API
 */
class ReportApiController {
    private ReportModel $reportModel;
    private PDO $db;

    public function __construct() {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $this->reportModel = new ReportModel();
        $this->db = Database::getConnection();
    }

    /**
     * API 1: Báo cáo Thống kê Tổng quan (Summary KPI)
     * Endpoint: GET index.php?route=api/reports/summary&building_id=all|A|B|C
     */
    public function summary(): void {
        try {
            $mgr = ReportModel::getManagerBuildingInfo();
            $buildingId = $mgr ? $mgr['code'] : trim($_GET['building_id'] ?? 'all');
            $stats = $this->reportModel->getSummaryStats($buildingId);
            $buildings = $this->reportModel->getBuildingsWithStats();

            $this->responseSuccess([
                'filter' => [
                    'building_id' => $buildingId,
                    'building_name' => $buildingId === 'all' ? 'Tất cả 3 tòa nhà' : ($buildings[$buildingId]['name'] ?? ('Tòa ' . $buildingId))
                ],
                'kpi' => [
                    'total_rooms'       => $stats['total_rooms'],
                    'rented_rooms'      => $stats['rented_rooms'],
                    'empty_rooms'       => $stats['empty_rooms'],
                    'occupancy_rate'    => $stats['occupancy_rate'] . '%',
                    'occupancy_rate_raw'=> $stats['occupancy_rate'],
                    'active_contracts'  => $stats['active_contracts'],
                    'total_tenants'     => $stats['total_tenants']
                ],
                'financial' => [
                    'total_revenue'     => $stats['total_revenue'],
                    'total_revenue_fmt' => number_format($stats['total_revenue'], 0, ',', '.') . ' đ',
                    'total_debt'        => $stats['total_debt'],
                    'total_debt_fmt'    => number_format($stats['total_debt'], 0, ',', '.') . ' đ',
                    'paid_invoices'     => $stats['paid_invoices'],
                    'unpaid_invoices'   => $stats['unpaid_invoices'],
                    'total_invoices'    => $stats['total_invoices'],
                    'collection_rate'   => ($stats['total_invoices'] > 0)
                        ? round(($stats['paid_invoices'] / $stats['total_invoices']) * 100, 1) . '%'
                        : '0%'
                ]
            ], "Lấy dữ liệu báo cáo tổng quan thành công");
        } catch (Exception $e) {
            $this->responseError(500, "Lỗi truy vấn báo cáo tổng quan: " . $e->getMessage());
        }
    }

    /**
     * API 2: Báo cáo Doanh thu & Cơ cấu nguồn thu theo kỳ
     * Endpoint: GET index.php?route=api/reports/revenue&building_id=all|A|B|C
     */
    public function revenue(): void {
        try {
            $mgr = ReportModel::getManagerBuildingInfo();
            $buildingId = $mgr ? $mgr['code'] : trim($_GET['building_id'] ?? 'all');
            $chartData = $this->reportModel->getMonthlyRevenue($buildingId);

            // Bóc tách cơ cấu nguồn thu từ invoice_details
            $breakdownSql = "
                SELECT 
                    COALESCE(s.service_name, 'Tiền phòng trọ') AS category,
                    SUM(id.subtotal) AS total_amount,
                    COUNT(id.id) AS item_count
                FROM invoice_details id
                JOIN invoices i ON id.invoice_id = i.id
                JOIN rooms r ON i.room_id = r.id
                LEFT JOIN services s ON id.service_id = s.id
                WHERE i.status = 'paid'
            ";
            $prefix = $this->reportModel->getRoomPrefixByBuilding($buildingId);
            $params = [];
            if ($prefix !== '') {
                $breakdownSql .= " AND r.room_number LIKE :p ";
                $params[':p'] = $prefix . '%';
            }
            $breakdownSql .= " GROUP BY category ORDER BY total_amount DESC";

            $stmt = $this->db->prepare($breakdownSql);
            $stmt->execute($params);
            $breakdownRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $grandTotal = 0;
            foreach ($breakdownRows as $row) {
                $grandTotal += (float)$row['total_amount'];
            }

            $categories = [];
            foreach ($breakdownRows as $row) {
                $amount = (float)$row['total_amount'];
                $pct = $grandTotal > 0 ? round(($amount / $grandTotal) * 100, 1) : 0;
                $categories[] = [
                    'category'         => $row['category'],
                    'total_amount'     => $amount,
                    'total_amount_fmt' => number_format($amount, 0, ',', '.') . ' đ',
                    'percentage'       => $pct,
                    'item_count'       => (int)$row['item_count']
                ];
            }

            $this->responseSuccess([
                'filter' => ['building_id' => $buildingId],
                'monthly_trend' => [
                    'labels'   => $chartData['labels'],
                    'revenues' => $chartData['revenues'],
                    'debts'    => $chartData['debts'],
                    'periods'  => $chartData['rows']
                ],
                'revenue_breakdown' => [
                    'grand_total'     => $grandTotal,
                    'grand_total_fmt' => number_format($grandTotal, 0, ',', '.') . ' đ',
                    'categories'      => $categories
                ]
            ], "Lấy báo cáo doanh thu và cơ cấu nguồn thu thành công");
        } catch (Exception $e) {
            $this->responseError(500, "Lỗi truy vấn báo cáo doanh thu: " . $e->getMessage());
        }
    }

    /**
     * API 3: Báo cáo Chi tiết Công nợ Khách thuê
     * Endpoint: GET index.php?route=api/reports/debt&building_id=all|A|B|C&status=unpaid|paid|all&keyword=...
     */
    public function debt(): void {
        try {
            $mgr = ReportModel::getManagerBuildingInfo();
            $buildingId = $mgr ? $mgr['code'] : trim($_GET['building_id'] ?? 'all');
            $status     = trim($_GET['status'] ?? 'unpaid');
            $keyword    = trim($_GET['keyword'] ?? '');
            $monthYear  = trim($_GET['month_year'] ?? '');

            $debtList = $this->reportModel->getDebtList($keyword, $status, $buildingId, $monthYear);

            $totalDebt = 0;
            $items = [];
            foreach ($debtList as $inv) {
                $amt = (float)$inv['total_amount'];
                if ($inv['status'] === 'unpaid') {
                    $totalDebt += $amt;
                }
                $items[] = [
                    'invoice_id'     => (int)$inv['id'],
                    'invoice_code'   => 'HD' . str_pad((string)$inv['id'], 4, '0', STR_PAD_LEFT),
                    'month_year'     => $inv['month_year'],
                    'room_number'    => $inv['room_number'],
                    'tenant_name'    => $inv['tenant_name'],
                    'tenant_phone'   => $inv['tenant_phone'],
                    'total_amount'   => $amt,
                    'total_amount_fmt' => number_format($amt, 0, ',', '.') . ' đ',
                    'status'         => $inv['status'],
                    'status_label'   => $inv['status'] === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán (Còn nợ)',
                    'electric_used'  => (int)$inv['electric_used'],
                    'water_used'     => (int)$inv['water_used'],
                    'building_name'  => $inv['building_name'] ?? 'StayMate'
                ];
            }

            $this->responseSuccess([
                'filter' => [
                    'building_id' => $buildingId,
                    'status'      => $status,
                    'keyword'     => $keyword,
                    'month_year'  => $monthYear
                ],
                'summary' => [
                    'total_count'    => count($items),
                    'total_debt'     => $totalDebt,
                    'total_debt_fmt' => number_format($totalDebt, 0, ',', '.') . ' đ'
                ],
                'debt_list' => $items
            ], "Lấy danh sách báo cáo công nợ thành công");
        } catch (Exception $e) {
            $this->responseError(500, "Lỗi truy vấn báo cáo công nợ: " . $e->getMessage());
        }
    }

    /**
     * API 4: Báo cáo Tiêu thụ Điện Nước theo Kỳ & Tòa nhà
     * Endpoint: GET index.php?route=api/reports/utilities&building_id=all|A|B|C&month_year=10-2026
     */
    public function utilities(): void {
        try {
            $mgr = ReportModel::getManagerBuildingInfo();
            $buildingId = $mgr ? $mgr['code'] : trim($_GET['building_id'] ?? 'all');
            $monthYear  = trim($_GET['month_year'] ?? '10-2026');

            $sql = "
                SELECT 
                    ur.*,
                    r.room_number,
                    r.building_id,
                    b.name AS building_name,
                    u.full_name AS tenant_name,
                    u.phone AS tenant_phone,
                    i.total_amount,
                    i.status AS invoice_status
                FROM utilities_readings ur
                JOIN rooms r ON ur.room_id = r.id
                LEFT JOIN buildings b ON r.building_id = b.id
                LEFT JOIN contracts c ON c.room_id = r.id AND c.status = 'active'
                LEFT JOIN users u ON c.tenant_id = u.id
                LEFT JOIN invoices i ON i.room_id = r.id AND i.month_year = ur.month_year
                WHERE 1=1
            ";
            $params = [];
            if ($monthYear !== '') {
                $sql .= " AND ur.month_year = :my ";
                $params[':my'] = $monthYear;
            }

            $prefix = $this->reportModel->getRoomPrefixByBuilding($buildingId);
            if ($prefix !== '') {
                $sql .= " AND r.room_number LIKE :p ";
                $params[':p'] = $prefix . '%';
            }
            $sql .= " ORDER BY r.room_number ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Bảng giá điện nước
            $srv = $this->reportModel->getServicesMap();
            $elecPrice = $srv['elec_price'];
            $waterPrice = $srv['water_price'];

            $totalElecKwh = 0;
            $totalWaterM3 = 0;
            $totalElecMoney = 0;
            $totalWaterMoney = 0;

            $items = [];
            foreach ($rows as $r) {
                $eUsed = max(0, (int)$r['electric_new'] - (int)$r['electric_old']);
                $wUsed = max(0, (int)$r['water_new'] - (int)$r['water_old']);
                $eMoney = $eUsed * $elecPrice;
                $wMoney = $wUsed * $waterPrice;

                $totalElecKwh   += $eUsed;
                $totalWaterM3   += $wUsed;
                $totalElecMoney += $eMoney;
                $totalWaterMoney+= $wMoney;

                $items[] = [
                    'reading_id'    => (int)$r['id'],
                    'month_year'    => $r['month_year'],
                    'room_number'   => $r['room_number'],
                    'building_name' => $r['building_name'] ?? 'StayMate',
                    'tenant_name'   => $r['tenant_name'] ?? 'Chưa xác định',
                    'tenant_phone'  => $r['tenant_phone'] ?? '',
                    'electric' => [
                        'old'       => (int)$r['electric_old'],
                        'new'       => (int)$r['electric_new'],
                        'used_kwh'  => $eUsed,
                        'unit_price'=> $elecPrice,
                        'subtotal'  => $eMoney,
                        'subtotal_fmt' => number_format($eMoney, 0, ',', '.') . ' đ'
                    ],
                    'water' => [
                        'old'       => (int)$r['water_old'],
                        'new'       => (int)$r['water_new'],
                        'used'      => $wUsed,
                        'unit_price'=> $waterPrice,
                        'subtotal'  => $wMoney,
                        'subtotal_fmt' => number_format($wMoney, 0, ',', '.') . ' đ'
                    ],
                    'invoice_status' => $r['invoice_status'] ?? 'unknown'
                ];
            }

            $this->responseSuccess([
                'filter' => [
                    'building_id' => $buildingId,
                    'month_year'  => $monthYear
                ],
                'summary' => [
                    'total_rooms_recorded' => count($items),
                    'total_electric_kwh'   => $totalElecKwh,
                    'total_electric_amount'=> $totalElecMoney,
                    'total_electric_fmt'   => number_format($totalElecMoney, 0, ',', '.') . ' đ',
                    'total_water_used'     => $totalWaterM3,
                    'total_water_amount'   => $totalWaterMoney,
                    'total_water_fmt'      => number_format($totalWaterMoney, 0, ',', '.') . ' đ',
                    'total_utility_amount' => $totalElecMoney + $totalWaterMoney,
                    'total_utility_fmt'    => number_format($totalElecMoney + $totalWaterMoney, 0, ',', '.') . ' đ'
                ],
                'readings' => $items
            ], "Lấy báo cáo tiêu thụ điện nước thành công");
        } catch (Exception $e) {
            $this->responseError(500, "Lỗi truy vấn báo cáo điện nước: " . $e->getMessage());
        }
    }

    /**
     * Hàm helper trả về JSON thành công
     */
    private function responseSuccess(array $data, string $message = "Thành công"): void {
        echo json_encode([
            'status'    => 'success',
            'code'      => 200,
            'message'   => $message,
            'timestamp' => date('Y-m-d H:i:s'),
            'author'    => 'Người 5 — Đỗ Minh Nguyệt (Database & Báo cáo)',
            'data'      => $data
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Hàm helper trả về JSON lỗi
     */
    private function responseError(int $code, string $message): void {
        http_response_code($code);
        echo json_encode([
            'status'    => 'error',
            'code'      => $code,
            'message'   => $message,
            'timestamp' => date('Y-m-d H:i:s'),
            'author'    => 'Người 5 — Đỗ Minh Nguyệt (Database & Báo cáo)'
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}
