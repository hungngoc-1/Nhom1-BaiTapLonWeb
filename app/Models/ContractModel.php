<?php
require_once __DIR__ . '/../Config/Database.php';

class ContractModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
        $this->ensureExtendedColumns();
        $this->checkAndCancelExpiredHoldContracts();
    }

    /**
     * Tự động bổ sung các cột nâng cao vào bảng contracts và users nếu chưa có.
     */
    private function ensureExtendedColumns() {
        try {
            $cols = $this->conn->query("SHOW COLUMNS FROM contracts")->fetchAll(PDO::FETCH_COLUMN);
            $alter = [];
            if (!in_array('contract_code', $cols))          $alter[] = "ADD COLUMN `contract_code` VARCHAR(30) NULL AFTER `id`";
            if (!in_array('contract_type', $cols))          $alter[] = "ADD COLUMN `contract_type` VARCHAR(30) DEFAULT 'official_lease' AFTER `contract_code`";
            if (!in_array('parent_contract_id', $cols))     $alter[] = "ADD COLUMN `parent_contract_id` INT NULL AFTER `contract_type`";
            if (!in_array('deposit_date', $cols))           $alter[] = "ADD COLUMN `deposit_date` DATE NULL AFTER `deposit_amount`";
            if (!in_array('deposit_paid_percent', $cols))   $alter[] = "ADD COLUMN `deposit_paid_percent` INT DEFAULT 100 AFTER `deposit_date`";
            if (!in_array('deposit_status', $cols))         $alter[] = "ADD COLUMN `deposit_status` VARCHAR(20) DEFAULT 'held' AFTER `deposit_paid_percent`";
            if (!in_array('monthly_rent', $cols))           $alter[] = "ADD COLUMN `monthly_rent` DECIMAL(10,2) DEFAULT 0 AFTER `deposit_status`";
            if (!in_array('first_month_rent_paid', $cols))  $alter[] = "ADD COLUMN `first_month_rent_paid` TINYINT(1) DEFAULT 0 AFTER `monthly_rent`";
            if (!in_array('first_month_invoice_id', $cols)) $alter[] = "ADD COLUMN `first_month_invoice_id` INT NULL AFTER `first_month_rent_paid`";
            if (!in_array('electricity_rate', $cols))       $alter[] = "ADD COLUMN `electricity_rate` DECIMAL(10,2) DEFAULT 3500 AFTER `first_month_invoice_id`";
            if (!in_array('water_rate', $cols))             $alter[] = "ADD COLUMN `water_rate` DECIMAL(10,2) DEFAULT 20000 AFTER `electricity_rate`";
            if (!in_array('service_fee', $cols))            $alter[] = "ADD COLUMN `service_fee` DECIMAL(10,2) DEFAULT 150000 AFTER `water_rate`";
            if (!in_array('signature_type', $cols))         $alter[] = "ADD COLUMN `signature_type` VARCHAR(20) DEFAULT 'paper'";
            if (!in_array('signature_status', $cols))       $alter[] = "ADD COLUMN `signature_status` VARCHAR(20) DEFAULT 'pending'";
            if (!in_array('tenant_signature', $cols))       $alter[] = "ADD COLUMN `tenant_signature` LONGTEXT NULL";
            if (!in_array('manager_signature', $cols))      $alter[] = "ADD COLUMN `manager_signature` LONGTEXT NULL";
            if (!in_array('scan_file', $cols))              $alter[] = "ADD COLUMN `scan_file` VARCHAR(255) NULL";
            if (!in_array('signed_at', $cols))              $alter[] = "ADD COLUMN `signed_at` DATETIME NULL";
            if (!in_array('renewal_status', $cols))         $alter[] = "ADD COLUMN `renewal_status` VARCHAR(20) DEFAULT 'none'";
            if (!in_array('terminated_at', $cols))          $alter[] = "ADD COLUMN `terminated_at` DATE NULL";
            if (!in_array('termination_reason', $cols))     $alter[] = "ADD COLUMN `termination_reason` TEXT NULL";
            if (!in_array('notes', $cols))                  $alter[] = "ADD COLUMN `notes` TEXT NULL";

            if (!empty($alter)) {
                $this->conn->exec("ALTER TABLE `contracts` " . implode(', ', $alter));
            }

            // Bổ sung preferred_room_id cho bảng users nếu chưa có
            $userCols = $this->conn->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('preferred_room_id', $userCols)) {
                $this->conn->exec("ALTER TABLE `users` ADD COLUMN `preferred_room_id` INT NULL AFTER `role`");
            }

            // Gán mã hợp đồng & giá thuê mặc định cho các bản ghi cũ chưa có
            $this->conn->exec("
                UPDATE contracts c
                LEFT JOIN rooms r ON r.id = c.room_id
                SET c.contract_code = CONCAT('HD-', YEAR(c.created_at), '-', LPAD(c.id, 3, '0')),
                    c.contract_type = COALESCE(c.contract_type, 'official_lease'),
                    c.monthly_rent = IF(c.monthly_rent IS NULL OR c.monthly_rent = 0, COALESCE(r.price, c.deposit_amount), c.monthly_rent),
                    c.deposit_date = COALESCE(c.deposit_date, c.start_date)
                WHERE c.contract_code IS NULL OR c.contract_code = ''
            ");
        } catch (PDOException $e) {
            // Bỏ qua nếu chưa khởi tạo bảng
        }
    }

    /**
     * Tự động kiểm tra và hủy các Hợp đồng 1 (Đặt cọc giữ chỗ 5 ngày) nếu quá hạn 5 ngày.
     * Lưu trạng thái 'expired' vào lịch sử hợp đồng và giải phóng phòng về 'available'.
     */
    public function checkAndCancelExpiredHoldContracts() {
        try {
            $expiredHolds = $this->conn->query("
                SELECT c.id, c.room_id, c.contract_code, c.deposit_amount, r.room_number, u.full_name
                FROM contracts c
                LEFT JOIN rooms r ON r.id = c.room_id
                LEFT JOIN users u ON u.id = c.tenant_id
                WHERE (c.contract_type = 'deposit_hold' OR c.deposit_paid_percent = 50)
                  AND c.status = 'active'
                  AND CURDATE() > c.end_date
            ")->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($expiredHolds)) {
                $upd = $this->conn->prepare("
                    UPDATE contracts
                    SET status = 'expired',
                        deposit_status = 'forfeited',
                        termination_reason = 'Hợp đồng đặt cọc giữ chỗ (HĐ 1) quá hạn 5 ngày chưa chuyển sang Hợp đồng thuê chính thức (HĐ 2). Hệ thống đã tự động hủy và lưu vào lịch sử.',
                        terminated_at = CURDATE()
                    WHERE id = :id
                ");

                foreach ($expiredHolds as $h) {
                    $upd->execute([':id' => $h['id']]);

                    // Kiểm tra xem phòng còn HĐ active nào khác không, nếu không thì đưa về available
                    $chk = $this->conn->prepare("SELECT COUNT(*) FROM contracts WHERE room_id = :room_id AND status = 'active' AND id <> :id");
                    $chk->execute([':room_id' => $h['room_id'], ':id' => $h['id']]);
                    if ((int)$chk->fetchColumn() === 0) {
                        $this->conn->prepare("UPDATE rooms SET status = 'available' WHERE id = :room_id")
                                   ->execute([':room_id' => $h['room_id']]);
                    }
                }
            }
        } catch (PDOException $e) {
            // Bỏ qua nếu lỗi kết nối
        }
    }

    // Thống kê nhanh cho các thẻ Overview trên trang Hợp đồng
    public function getContractStats() {
        $this->checkAndCancelExpiredHoldContracts();
        $rows = $this->getAllContracts();
        $total = count($rows);
        $activeOfficial = 0;
        $activeHold = 0;
        $expiredHold = 0;
        $expiringSoon = 0;
        $terminated = 0;
        $totalDepositHeld = 0;
        $totalFirstMonthCollected = 0;

        foreach ($rows as $r) {
            $cType = $r['contract_type'] ?? 'official_lease';
            if ($r['status'] === 'active') {
                if ($cType === 'deposit_hold') {
                    $activeHold++;
                } else {
                    $activeOfficial++;
                }

                if (($r['deposit_status'] ?? 'held') === 'held') {
                    $totalDepositHeld += (float)$r['deposit_amount'];
                }
                $daysLeft = (int)floor((strtotime($r['end_date']) - strtotime(date('Y-m-d'))) / 86400);
                if ($daysLeft >= 0 && $daysLeft <= 30) {
                    $expiringSoon++;
                }
            } elseif ($r['status'] === 'expired') {
                if ($cType === 'deposit_hold') {
                    $expiredHold++;
                }
            } elseif ($r['status'] === 'terminated') {
                $terminated++;
            }

            if (!empty($r['first_month_rent_paid'])) {
                $totalFirstMonthCollected += (float)$r['monthly_rent'];
            }
        }

        return [
            'total'                      => $total,
            'active'                     => $activeOfficial + $activeHold,
            'active_official'            => $activeOfficial,
            'active_hold'                => $activeHold,
            'expired_hold'               => $expiredHold,
            'expiring_soon'              => $expiringSoon,
            'terminated'                 => $terminated,
            'total_deposit_held'         => $totalDepositHeld,
            'total_first_month_collected'=> $totalFirstMonthCollected,
        ];
    }

    public function getAllContracts($keyword = '', $status = '', $contractType = '') {
        $this->checkAndCancelExpiredHoldContracts();
        $sql = "SELECT c.*,
                       r.room_number, r.price AS room_price, r.description AS room_description,
                       u.full_name, u.phone, u.email,
                       parent.contract_code AS parent_contract_code,
                       DATEDIFF(c.end_date, CURDATE()) AS days_left
                FROM contracts c
                LEFT JOIN rooms r ON c.room_id = r.id
                LEFT JOIN users u ON c.tenant_id = u.id
                LEFT JOIN contracts parent ON c.parent_contract_id = parent.id
                WHERE 1=1";

        $params = [];
        require_once __DIR__ . '/ReportModel.php';
        $mgr = ReportModel::getManagerBuildingInfo();
        if ($mgr) {
            $sql .= " AND (r.building_id = :mgrBId OR r.room_number LIKE :mgrPfx)";
            $params[':mgrBId'] = $mgr['id'];
            $params[':mgrPfx'] = $mgr['prefix'] . '%';
        }
        if ($keyword !== '') {
            $sql .= " AND (c.contract_code LIKE :kw1 OR r.room_number LIKE :kw2 OR u.full_name LIKE :kw3 OR u.phone LIKE :kw4)";
            $params[':kw1'] = $params[':kw2'] = $params[':kw3'] = $params[':kw4'] = "%$keyword%";
        }
        if ($status !== '') {
            $sql .= " AND c.status = :status";
            $params[':status'] = $status;
        }
        if ($contractType !== '') {
            $sql .= " AND c.contract_type = :contract_type";
            $params[':contract_type'] = $contractType;
        }
        $sql .= " ORDER BY (c.status = 'active') DESC, c.id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $list = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($list as &$row) {
            if (empty($row['contract_code'])) {
                $row['contract_code'] = 'HD-' . date('Y', strtotime($row['created_at'] ?? 'now')) . '-' . str_pad($row['id'], 3, '0', STR_PAD_LEFT);
            }
            if (empty($row['contract_type'])) {
                $row['contract_type'] = 'official_lease';
            }
            if (empty($row['monthly_rent']) || (float)$row['monthly_rent'] <= 0) {
                $row['monthly_rent'] = $row['room_price'] ?? $row['deposit_amount'];
            }
            if (empty($row['deposit_date'])) {
                $row['deposit_date'] = $row['start_date'];
            }
        }
        return $list;
    }

    public function getContractById($id) {
        $this->checkAndCancelExpiredHoldContracts();
        $sql = "SELECT c.*,
                       r.room_number, r.price AS room_price, r.description AS room_description,
                       u.full_name, u.phone, u.email,
                       parent.contract_code AS parent_contract_code,
                       inv.id AS invoice_number, inv.status AS invoice_status, inv.total_amount AS invoice_total,
                       DATEDIFF(c.end_date, CURDATE()) AS days_left
                FROM contracts c
                LEFT JOIN rooms r ON c.room_id = r.id
                LEFT JOIN users u ON c.tenant_id = u.id
                LEFT JOIN contracts parent ON c.parent_contract_id = parent.id
                LEFT JOIN invoices inv ON c.first_month_invoice_id = inv.id
                WHERE c.id = :id
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            if (empty($row['contract_code'])) {
                $row['contract_code'] = 'HD-' . date('Y', strtotime($row['created_at'] ?? 'now')) . '-' . str_pad($row['id'], 3, '0', STR_PAD_LEFT);
            }
            if (empty($row['contract_type'])) {
                $row['contract_type'] = 'official_lease';
            }
            if (empty($row['monthly_rent']) || (float)$row['monthly_rent'] <= 0) {
                $row['monthly_rent'] = $row['room_price'] ?? $row['deposit_amount'];
            }
            if (empty($row['deposit_date'])) {
                $row['deposit_date'] = $row['start_date'];
            }
        }
        return $row ?: null;
    }

    /**
     * Lấy hợp đồng mới nhất của khách thuê để tích hợp phân quyền khách thuê.
     */
    public function getContractByTenantId($tenantId) {
        $this->checkAndCancelExpiredHoldContracts();
        $sql = "SELECT c.*,
                       r.room_number, r.price AS room_price, r.description AS room_description,
                       u.full_name, u.phone, u.email,
                       DATEDIFF(c.end_date, CURDATE()) AS days_left
                FROM contracts c
                LEFT JOIN rooms r ON c.room_id = r.id
                LEFT JOIN users u ON c.tenant_id = u.id
                WHERE c.tenant_id = :tenant_id
                ORDER BY (c.status = 'active') DESC, c.id DESC
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            if (empty($row['contract_code'])) {
                $row['contract_code'] = 'HD-' . date('Y', strtotime($row['created_at'] ?? 'now')) . '-' . str_pad($row['id'], 3, '0', STR_PAD_LEFT);
            }
            if (empty($row['contract_type'])) {
                $row['contract_type'] = 'official_lease';
            }
        }
        return $row ?: null;
    }

    public function getRoomServices(int $roomId): array {
        try {
            $stmt = $this->conn->prepare("SELECT service_name, unit_price, unit FROM room_services WHERE room_id = :room_id ORDER BY id ASC");
            $stmt->execute([':room_id' => $roomId]);
            $svcs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($svcs)) {
                return $svcs;
            }
        } catch (PDOException $e) {}

        // Kiểm tra trong description của phòng nếu có chuỗi "Dịch vụ: ..."
        try {
            $stmtR = $this->conn->prepare("SELECT description FROM rooms WHERE id = :id");
            $stmtR->execute([':id' => $roomId]);
            $desc = (string)$stmtR->fetchColumn();
            if (preg_match('/Dịch vụ:\s*([^|]+)/iu', $desc, $m)) {
                $rawList = explode(',', $m[1]);
                $parsed = [];
                foreach ($rawList as $item) {
                    $item = trim($item);
                    if (preg_match('/^(.*?)\s*\(([\d\.]+)\s*đ?\s*\/?\s*([^)]*)\)$/u', $item, $sm)) {
                        $parsed[] = [
                            'service_name' => trim($sm[1]),
                            'unit_price'   => (float)str_replace('.', '', $sm[2]),
                            'unit'         => trim($sm[3]) ?: 'Tháng',
                        ];
                    } elseif ($item !== '') {
                        $parsed[] = [
                            'service_name' => $item,
                            'unit_price'   => 50000,
                            'unit'         => 'Tháng',
                        ];
                    }
                }
                if (!empty($parsed)) {
                    return $parsed;
                }
            }
        } catch (PDOException $e) {}

        // Nếu phòng chưa được cấu hình dịch vụ riêng lẻ: lấy các dịch vụ cơ bản từ bảng services
        try {
            $std = $this->conn->query("
                SELECT service_name, unit_price, unit 
                FROM services 
                WHERE is_active = 1 
                  AND service_name NOT LIKE '%điện%' 
                  AND service_name NOT LIKE '%nước%'
                  AND id IN (3, 4, 5)
                ORDER BY id ASC
            ")->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($std)) {
                return $std;
            }
        } catch (PDOException $e) {}

        return [
            ['service_name' => 'Internet / Wifi tốc độ cao', 'unit_price' => 100000, 'unit' => 'Phòng'],
            ['service_name' => 'Vệ sinh & Rác sinh hoạt', 'unit_price' => 50000, 'unit' => 'Phòng'],
        ];
    }

    public function getRooms() {
        require_once __DIR__ . '/ReportModel.php';
        $mgr = ReportModel::getManagerBuildingInfo();
        if ($mgr) {
            $st = $this->conn->prepare("SELECT id, room_number, price, status, description FROM rooms WHERE building_id = :bId OR room_number LIKE :pfx ORDER BY room_number");
            $st->execute([':bId' => $mgr['id'], ':pfx' => $mgr['prefix'] . '%']);
            $rooms = $st->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $rooms = $this->conn->query("SELECT id, room_number, price, status, description FROM rooms ORDER BY room_number")->fetchAll(PDO::FETCH_ASSOC);
        }

        foreach ($rooms as &$r) {
            $rServices = $this->getRoomServices((int)$r['id']);
            $r['services'] = $rServices;
            $totalServiceFee = 0;
            foreach ($rServices as $s) {
                $totalServiceFee += (float)($s['unit_price'] ?? 0);
            }
            $r['total_service_fee'] = $totalServiceFee;
            $r['electricity_rate'] = 3500;
            $r['water_rate'] = 20000;
        }
        unset($r);

        return $rooms;
    }

    /**
     * Lấy danh sách khách thuê kèm phòng đã chọn trước đó (để tự động nhảy phòng khi chọn khách).
     */
    public function getTenants() {
        return $this->conn->query("
            SELECT u.id, u.full_name, u.phone, u.email, u.preferred_room_id,
                   COALESCE(
                       u.preferred_room_id,
                       (SELECT c.room_id FROM contracts c WHERE c.tenant_id = u.id AND c.status = 'active' ORDER BY c.id DESC LIMIT 1),
                       (SELECT c2.room_id FROM contracts c2 WHERE c2.tenant_id = u.id ORDER BY c2.id DESC LIMIT 1)
                   ) AS assigned_room_id,
                   r.room_number AS assigned_room_number,
                   r.price AS assigned_room_price
            FROM users u
            LEFT JOIN rooms r ON r.id = COALESCE(
                u.preferred_room_id,
                (SELECT c.room_id FROM contracts c WHERE c.tenant_id = u.id AND c.status = 'active' ORDER BY c.id DESC LIMIT 1),
                (SELECT c2.room_id FROM contracts c2 WHERE c2.tenant_id = u.id ORDER BY c2.id DESC LIMIT 1)
            )
            WHERE u.role = 'tenant'
            ORDER BY u.full_name
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findActiveByRoomId($room_id, $exclude_id = 0) {
        $stmt = $this->conn->prepare("
            SELECT c.*, r.room_number
            FROM contracts c
            LEFT JOIN rooms r ON r.id = c.room_id
            WHERE c.room_id = :room_id AND c.status = 'active' AND c.id <> :exclude_id
            ORDER BY c.id DESC LIMIT 1
        ");
        $stmt->execute([':room_id' => $room_id, ':exclude_id' => $exclude_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function hasOverlap($room_id, $start_date, $end_date, $exclude_id = 0) {
        $sql = "SELECT COUNT(*) FROM contracts
                WHERE room_id = :room_id AND status = 'active' AND id <> :exclude_id
                  AND start_date <= :end_date AND end_date >= :start_date";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':room_id'    => $room_id,
            ':exclude_id' => $exclude_id,
            ':start_date' => $start_date,
            ':end_date'   => $end_date
        ]);
        return $stmt->fetchColumn() > 0;
    }

    public function generateContractCode() {
        $year = date('Y');
        $count = (int)$this->conn->query("SELECT COUNT(*) FROM contracts")->fetchColumn() + 1;
        return sprintf('HD-%s-%03d', $year, $count);
    }

    /**
     * Tạo hợp đồng mới theo luồng nghiệp vụ:
     * - Hợp đồng 1 (deposit_hold): Có hiệu lực 5 ngày, khách đóng 1/2 tiền cọc (50%).
     *   Quá 5 ngày sẽ tự động hủy và lưu vào lịch sử.
     * - Hợp đồng 2 (official_lease): Hoàn thành 100% tiền cọc, có hiệu lực 6 tháng.
     *   Nếu chọn HĐ 1 thì sẽ kèm theo HĐ 2; nếu chỉ ký HĐ 2 thì ký độc lập.
     *   Đồng thời THU LUÔN 1 THÁNG TIỀN PHÒNG đầu tiên (bất kể thời điểm nào),
     *   tự động tạo hóa đơn status 'paid' cộng vào doanh thu và cộng tiền cọc vào doanh thu cọc.
     */
    public function createContract($data) {
        $contractType = in_array($data['contract_type'] ?? '', ['deposit_hold', 'official_lease'], true)
            ? $data['contract_type']
            : 'official_lease';

        $code = !empty($data['contract_code']) ? $data['contract_code'] : $this->generateContractCode();
        $monthlyRent = (float)($data['monthly_rent'] ?? 0);
        $signatureType = in_array($data['signature_type'] ?? '', ['online', 'paper'], true) ? $data['signature_type'] : 'paper';
        $status = $data['status'] ?? 'active';
        $parentContractId = !empty($data['parent_contract_id']) ? (int)$data['parent_contract_id'] : null;

        $startDate = !empty($data['start_date']) ? $data['start_date'] : date('Y-m-d');
        $depositDate = !empty($data['deposit_date']) ? $data['deposit_date'] : date('Y-m-d');

        if ($contractType === 'deposit_hold') {
            // HỢP ĐỒNG 1: ĐẶT CỌC GIỮ CHỖ 5 NGÀY (CỌC 50%)
            $endDate = date('Y-m-d', strtotime($startDate . ' + 5 days'));
            $depositPaidPercent = 50;
            $depositAmount = round($monthlyRent * 0.5, 2);
            $notes = trim($data['notes'] ?? '');
            $notes = trim($notes . ' [HĐ 1 - Đặt cọc giữ chỗ 50% tiền cọc. Hiệu lực 5 ngày đến ' . date('d/m/Y', strtotime($endDate)) . ']');
            $firstMonthPaid = 0;
            $firstMonthInvoiceId = null;
        } else {
            // HỢP ĐỒNG 2: THUÊ CHÍNH THỨC 6 THÁNG (CỌC 100% + THU TRƯỚC 1 THÁNG TIỀN PHÒNG)
            $endDate = !empty($data['end_date']) ? $data['end_date'] : date('Y-m-d', strtotime($startDate . ' + 6 months'));
            $depositPaidPercent = 100;
            $depositAmount = $monthlyRent; // 100% tiền cọc = 1 tháng tiền phòng
            $firstMonthPaid = 1;
            $firstMonthInvoiceId = null;
            $notes = trim($data['notes'] ?? '');
            if ($parentContractId > 0) {
                $notes = trim($notes . ' [Kế thừa và hoàn tất nốt 50% cọc từ HĐ giữ chỗ #' . $parentContractId . ']');
            }
        }

        $sql = "INSERT INTO contracts (
                    contract_code, contract_type, parent_contract_id,
                    room_id, tenant_id, start_date, end_date,
                    deposit_amount, deposit_date, deposit_paid_percent, deposit_status,
                    monthly_rent, first_month_rent_paid, first_month_invoice_id,
                    electricity_rate, water_rate, service_fee,
                    status, signature_type, signature_status, notes
                ) VALUES (
                    :contract_code, :contract_type, :parent_contract_id,
                    :room_id, :tenant_id, :start_date, :end_date,
                    :deposit_amount, :deposit_date, :deposit_paid_percent, 'held',
                    :monthly_rent, :first_month_rent_paid, NULL,
                    :electricity_rate, :water_rate, :service_fee,
                    :status, :signature_type, 'pending', :notes
                )";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':contract_code'        => $code,
            ':contract_type'        => $contractType,
            ':parent_contract_id'   => $parentContractId,
            ':room_id'              => (int)$data['room_id'],
            ':tenant_id'            => (int)$data['tenant_id'],
            ':start_date'           => $startDate,
            ':end_date'             => $endDate,
            ':deposit_amount'       => $depositAmount,
            ':deposit_date'         => $depositDate,
            ':deposit_paid_percent' => $depositPaidPercent,
            ':monthly_rent'         => $monthlyRent,
            ':first_month_rent_paid'=> $firstMonthPaid,
            ':electricity_rate'     => (float)($data['electricity_rate'] ?? 3500),
            ':water_rate'           => (float)($data['water_rate'] ?? 20000),
            ':service_fee'          => (float)($data['service_fee'] ?? 150000),
            ':status'               => $status,
            ':signature_type'       => $signatureType,
            ':notes'                => $notes,
        ]);

        $newContractId = (int)$this->conn->lastInsertId();

        // Nếu là Hợp đồng 2: Thu luôn 1 tháng tiền phòng đầu tiên & tự động cộng vào Doanh thu
        if ($contractType === 'official_lease' && $monthlyRent > 0) {
            $invoiceId = $this->createFirstMonthRentInvoice($newContractId, (int)$data['room_id'], $monthlyRent, $startDate, $code);
            if ($invoiceId > 0) {
                $this->conn->prepare("UPDATE contracts SET first_month_rent_paid = 1, first_month_invoice_id = :inv_id WHERE id = :id")
                           ->execute([':inv_id' => $invoiceId, ':id' => $newContractId]);
            }

            // Nếu nâng cấp từ HĐ 1: Chuyển trạng thái HĐ 1 sang đã thanh lý/chuyển tiếp
            if ($parentContractId > 0) {
                $this->conn->prepare("
                    UPDATE contracts
                    SET status = 'terminated',
                        notes = CONCAT(COALESCE(notes, ''), ' | Đã chuyển đổi hoàn tất 100% cọc sang HĐ chính thức ', :new_code),
                        terminated_at = CURDATE()
                    WHERE id = :parent_id
                ")->execute([':new_code' => $code, ':parent_id' => $parentContractId]);
            }
        }

        // Cập nhật trạng thái phòng sang 'rented'
        if ($status === 'active') {
            $upd = $this->conn->prepare("UPDATE rooms SET status = 'rented' WHERE id = :room_id");
            $upd->execute([':room_id' => (int)$data['room_id']]);
        }

        return $newContractId;
    }

    /**
     * Tự động tạo Hóa đơn thu trước 01 tháng tiền phòng khi ký Hợp đồng 2,
     * đánh dấu trạng thái 'paid' để lập tức cộng vào Doanh thu thực thu của hệ thống.
     */
    private function createFirstMonthRentInvoice($contractId, $roomId, $monthlyRent, $startDate, $contractCode) {
        try {
            $monthYear = date('m/Y', strtotime($startDate));
            // Kiểm tra xem kỳ này đã có hóa đơn chưa
            $chk = $this->conn->prepare("SELECT id FROM invoices WHERE room_id = :room_id AND month_year = :month_year LIMIT 1");
            $chk->execute([':room_id' => $roomId, ':month_year' => $monthYear]);
            $existingId = $chk->fetchColumn();

            if ($existingId) {
                // Đã có thì cập nhật trạng thái paid và total
                $upd = $this->conn->prepare("
                    UPDATE invoices
                    SET contract_id = :contract_id,
                        total_amount = :total_amount,
                        status = 'paid',
                        paid_at = NOW(),
                        payment_method = 'cash',
                        payment_note = :note
                    WHERE id = :id
                ");
                $upd->execute([
                    ':contract_id' => $contractId,
                    ':total_amount'=> $monthlyRent,
                    ':note'        => 'Thu trước 01 tháng tiền phòng khi ký Hợp đồng ' . $contractCode,
                    ':id'          => $existingId
                ]);
                return (int)$existingId;
            }

            // Thêm mới hóa đơn tiền phòng đã thu
            $stmt = $this->conn->prepare("
                INSERT INTO invoices (
                    room_id, contract_id, month_year, total_amount, status,
                    paid_at, payment_method, payment_note
                ) VALUES (
                    :room_id, :contract_id, :month_year, :total_amount, 'paid',
                    NOW(), 'cash', :note
                )
            ");
            $stmt->execute([
                ':room_id'      => $roomId,
                ':contract_id'  => $contractId,
                ':month_year'   => $monthYear,
                ':total_amount' => $monthlyRent,
                ':note'         => 'Thu trước 01 tháng tiền phòng khi ký Hợp đồng ' . $contractCode,
            ]);

            $invoiceId = (int)$this->conn->lastInsertId();

            // Thêm chi tiết hóa đơn vào invoice_details
            if ($invoiceId > 0) {
                $det = $this->conn->prepare("
                    INSERT INTO invoice_details (invoice_id, service_id, quantity, applied_price, subtotal)
                    VALUES (:invoice_id, NULL, 1, :price, :price)
                ");
                $det->execute([
                    ':invoice_id' => $invoiceId,
                    ':price'      => $monthlyRent
                ]);
            }

            return $invoiceId;
        } catch (PDOException $e) {
            return 0;
        }
    }

    // Cập nhật hợp đồng
    public function updateContract($id, $data) {
        $old = $this->getContractById($id);
        $monthlyRent = (float)($data['monthly_rent'] ?? 0);
        $contractType = $data['contract_type'] ?? ($old['contract_type'] ?? 'official_lease');
        $depositPaidPercent = (int)($data['deposit_paid_percent'] ?? ($contractType === 'deposit_hold' ? 50 : 100));
        $depositAmount = (float)($data['deposit_amount'] ?? ($depositPaidPercent === 50 ? round($monthlyRent * 0.5, 2) : $monthlyRent));
        $depositDate = !empty($data['deposit_date']) ? $data['deposit_date'] : ($old['deposit_date'] ?? date('Y-m-d'));
        $signatureType = in_array($data['signature_type'] ?? '', ['online', 'paper'], true)
            ? $data['signature_type']
            : ($old['signature_type'] ?? 'paper');

        $sql = "UPDATE contracts SET
                    contract_type = :contract_type,
                    room_id = :room_id,
                    tenant_id = :tenant_id,
                    deposit_date = :deposit_date,
                    start_date = :start_date,
                    end_date = :end_date,
                    monthly_rent = :monthly_rent,
                    deposit_paid_percent = :deposit_paid_percent,
                    deposit_amount = :deposit_amount,
                    electricity_rate = :electricity_rate,
                    water_rate = :water_rate,
                    service_fee = :service_fee,
                    signature_type = :signature_type,
                    status = :status,
                    notes = :notes
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $ok = $stmt->execute([
            ':contract_type'        => $contractType,
            ':room_id'              => (int)$data['room_id'],
            ':tenant_id'            => (int)$data['tenant_id'],
            ':deposit_date'         => $depositDate,
            ':start_date'           => $data['start_date'],
            ':end_date'             => $data['end_date'],
            ':monthly_rent'         => $monthlyRent,
            ':deposit_paid_percent' => $depositPaidPercent,
            ':deposit_amount'       => $depositAmount,
            ':electricity_rate'     => (float)($data['electricity_rate'] ?? 3500),
            ':water_rate'           => (float)($data['water_rate'] ?? 20000),
            ':service_fee'          => (float)($data['service_fee'] ?? 150000),
            ':signature_type'       => $signatureType,
            ':status'               => $data['status'],
            ':notes'                => trim($data['notes'] ?? ''),
            ':id'                   => (int)$id,
        ]);

        if ($data['status'] === 'active') {
            $this->conn->prepare("UPDATE rooms SET status = 'rented' WHERE id = :room_id")
                       ->execute([':room_id' => (int)$data['room_id']]);
        }
        return $ok;
    }

    // Thanh lý hợp đồng
    public function terminateContract($id, $moveOutDate = null, $customNote = '') {
        $contract = $this->getContractById($id);
        if (!$contract) return false;

        $actualMoveOut = !empty($moveOutDate) ? $moveOutDate : date('Y-m-d');
        $isCompletedTerm = (strtotime($actualMoveOut) >= strtotime($contract['end_date']));
        $depositAmount = (float)$contract['deposit_amount'];
        $depositStatus = $isCompletedTerm ? 'refunded' : 'forfeited';

        $fmtDeposit = number_format($depositAmount, 0, ',', '.') . ' đ';
        $reason = $isCompletedTerm
            ? "Trả phòng đúng/sau hạn hợp đồng — Hoàn trả 100% tiền cọc ({$fmtDeposit})."
            : "Trả phòng trước hạn hợp đồng (" . date('d/m/Y', strtotime($contract['end_date'])) . ") — Khách mất tiền cọc ({$fmtDeposit}).";
        if ($customNote !== '') {
            $reason .= ' Ghi chú: ' . $customNote;
        }

        $stmt = $this->conn->prepare("
            UPDATE contracts
            SET status = 'terminated',
                deposit_status = :deposit_status,
                terminated_at = :terminated_at,
                termination_reason = :termination_reason
            WHERE id = :id
        ");
        $stmt->execute([
            ':deposit_status'     => $depositStatus,
            ':terminated_at'      => $actualMoveOut,
            ':termination_reason' => $reason,
            ':id'                 => (int)$id,
        ]);

        // Nếu phòng không còn hợp đồng active nào thì chuyển về 'available'
        $chk = $this->conn->prepare("SELECT COUNT(*) FROM contracts WHERE room_id = :room_id AND status = 'active' AND id <> :id");
        $chk->execute([':room_id' => $contract['room_id'], ':id' => $id]);
        if ((int)$chk->fetchColumn() === 0) {
            $this->conn->prepare("UPDATE rooms SET status = 'available' WHERE id = :room_id")
                       ->execute([':room_id' => $contract['room_id']]);
        }

        return [
            'is_completed_term' => $isCompletedTerm,
            'deposit_status'    => $depositStatus,
            'deposit_amount'    => $depositAmount,
            'reason'            => $reason,
        ];
    }

    // Lưu chữ ký điện tử
    public function saveOnlineSignature($id, $tenantSig = null, $managerSig = null) {
        $contract = $this->getContractById($id);
        if (!$contract) return false;

        $newTenantSig = !empty($tenantSig) ? $tenantSig : ($contract['tenant_signature'] ?? null);
        $newManagerSig = !empty($managerSig) ? $managerSig : ($contract['manager_signature'] ?? null);
        $sigStatus = (!empty($newTenantSig) || !empty($newManagerSig)) ? 'signed' : 'pending';

        $stmt = $this->conn->prepare("
            UPDATE contracts
            SET signature_type = 'online',
                signature_status = :sig_status,
                tenant_signature = :tenant_sig,
                manager_signature = :manager_sig,
                signed_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([
            ':sig_status'  => $sigStatus,
            ':tenant_sig'  => $newTenantSig,
            ':manager_sig' => $newManagerSig,
            ':id'          => (int)$id,
        ]);
    }

    // Lưu bản Scan hợp đồng ký giấy
    public function saveScanFile($id, $scanFilePath) {
        $stmt = $this->conn->prepare("
            UPDATE contracts
            SET signature_type = 'paper',
                signature_status = 'signed',
                scan_file = :scan_file,
                signed_at = NOW()
            WHERE id = :id
        ");
        return $stmt->execute([
            ':scan_file' => $scanFilePath,
            ':id'        => (int)$id,
        ]);
    }

    // Gia hạn hợp đồng
    public function renewContract($id, $months = 6) {
        $contract = $this->getContractById($id);
        if (!$contract) return false;

        $months = max(1, min(36, (int)$months));
        $newEndDate = date('Y-m-d', strtotime($contract['end_date'] . " +{$months} months"));
        $noteAppend = trim(($contract['notes'] ?? '') . " | Đã gia hạn thêm {$months} tháng đến " . date('d/m/Y', strtotime($newEndDate)), ' |');

        $stmt = $this->conn->prepare("
            UPDATE contracts
            SET end_date = :end_date,
                status = 'active',
                renewal_status = 'renewed',
                notes = :notes
            WHERE id = :id
        ");
        return $stmt->execute([
            ':end_date' => $newEndDate,
            ':notes'    => $noteAppend,
            ':id'       => (int)$id,
        ]);
    }

    public function deleteContract($id) {
        $contract = $this->getContractById($id);
        $stmt = $this->conn->prepare("DELETE FROM contracts WHERE id = :id");
        $ok = $stmt->execute([':id' => $id]);

        if ($ok && $contract) {
            $chk = $this->conn->prepare("SELECT COUNT(*) FROM contracts WHERE room_id = :room_id AND status = 'active'");
            $chk->execute([':room_id' => $contract['room_id']]);
            if ((int)$chk->fetchColumn() === 0) {
                $this->conn->prepare("UPDATE rooms SET status = 'available' WHERE id = :room_id")
                           ->execute([':room_id' => $contract['room_id']]);
            }
        }
        return $ok;
    }

    /**
     * Lấy danh sách hợp đồng có yêu cầu xác minh lý do không đóng tiền phòng tháng đầu từ bên Hóa đơn
     */
    public function getPendingOverdueVerifications(): array {
        $sql = "
            SELECT c.*, r.room_number, r.price AS room_price,
                   u.full_name AS tenant_name, u.phone AS tenant_phone, u.email AS tenant_email
            FROM contracts c
            JOIN rooms r ON r.id = c.room_id
            LEFT JOIN users u ON u.id = c.tenant_id
            WHERE c.unpaid_notice_sent = 1 AND (c.termination_verification_case IS NULL OR c.termination_verification_case = 'none')
        ";
        require_once __DIR__ . '/ReportModel.php';
        $mgr = ReportModel::getManagerBuildingInfo();
        if ($mgr) {
            $sql .= " AND (r.building_id = {$mgr['id']} OR r.room_number LIKE '{$mgr['prefix']}%')";
        }
        $sql .= " ORDER BY c.unpaid_notice_sent_at DESC, r.room_number ASC";
        try {
            return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Xác nhận lý do không đóng tiền:
     * - Case 'expired_no_renew': Hết thời gian thuê và không muốn gia hạn -> Đã hoàn cọc
     * - Case 'early_move_out': Bỏ phòng trước khi hết hợp đồng -> Không hoàn cọc (Mất cọc)
     */
    public function resolveOverdueVerification(int $contractId, string $caseKey, string $note = ''): array {
        if ($contractId <= 0) return ['ok' => false, 'msg' => 'Không tìm thấy hợp đồng.'];
        
        $c = $this->conn->query("SELECT c.*, r.room_number, u.full_name FROM contracts c JOIN rooms r ON r.id = c.room_id LEFT JOIN users u ON u.id = c.tenant_id WHERE c.id = $contractId")->fetch(PDO::FETCH_ASSOC);
        if (!$c) return ['ok' => false, 'msg' => 'Hợp đồng không tồn tại.'];

        $this->conn->beginTransaction();
        try {
            if ($caseKey === 'expired_no_renew') {
                // Trường hợp 1: Khách đã hết thời gian thuê và không muốn gia hạn -> Đã hoàn cọc
                $termReason = 'Hết thời gian thuê và không muốn gia hạn (Đã hoàn lại tiền cọc)';
                if ($note !== '') $termReason .= ' - ' . $note;

                $st = $this->conn->prepare("
                    UPDATE contracts 
                    SET deposit_status = 'refunded',
                        termination_verification_case = 'expired_no_renew',
                        status = 'terminated',
                        terminated_at = CURDATE(),
                        termination_reason = ?,
                        unpaid_notice_resolved_at = NOW(),
                        refund_confirmed_invoice = 0
                    WHERE id = ?
                ");
                $st->execute([$termReason, $contractId]);

                // Trả phòng về available
                $this->conn->prepare("UPDATE rooms SET status = 'available' WHERE id = ?")->execute([$c['room_id']]);

                // Cập nhật lại tổng tiền các hóa đơn liên quan (không tính cọc)
                $stInv = $this->conn->prepare("SELECT id FROM invoices WHERE contract_id = ?");
                $stInv->execute([$contractId]);
                $invIds = $stInv->fetchAll(PDO::FETCH_COLUMN);
                foreach ($invIds as $invId) {
                    $stSum = $this->conn->prepare("SELECT COALESCE(SUM(subtotal), 0) FROM invoice_details WHERE invoice_id = ?");
                    $stSum->execute([$invId]);
                    $baseTotal = (float)$stSum->fetchColumn();
                    if ($baseTotal > 0) {
                        $this->conn->prepare("UPDATE invoices SET total_amount = ? WHERE id = ?")->execute([$baseTotal, $invId]);
                    }
                }

                $this->conn->commit();
                return ['ok' => true, 'msg' => "Đã xác nhận phòng {$c['room_number']}: Khách hết hạn hợp đồng và không gia hạn. Trạng thái cọc chuyển thành ĐÃ HOÀN CỌC và đã gửi thông báo xác nhận về trang Hóa đơn!"];

            } elseif ($caseKey === 'early_move_out') {
                // Trường hợp 2: Bỏ phòng trước khi hết hợp đồng -> Không được hoàn cọc (Mất cọc)
                $termReason = 'Bỏ phòng / chuyển đi trước khi hết hợp đồng (Tịch thu cọc, không hoàn cọc)';
                if ($note !== '') $termReason .= ' - ' . $note;

                $st = $this->conn->prepare("
                    UPDATE contracts 
                    SET deposit_status = 'forfeited',
                        termination_verification_case = 'early_move_out',
                        status = 'terminated',
                        terminated_at = CURDATE(),
                        termination_reason = ?,
                        unpaid_notice_resolved_at = NOW(),
                        refund_confirmed_invoice = 1
                    WHERE id = ?
                ");
                $st->execute([$termReason, $contractId]);

                // Trả phòng về available
                $this->conn->prepare("UPDATE rooms SET status = 'available' WHERE id = ?")->execute([$c['room_id']]);

                $this->conn->commit();
                return ['ok' => true, 'msg' => "Đã xác nhận phòng {$c['room_number']}: Khách bỏ phòng trước hạn hợp đồng. Trạng thái cọc chuyển thành MẤT CỌC (Không hoàn cọc)!"];
            } else {
                $this->conn->rollBack();
                return ['ok' => false, 'msg' => 'Trường hợp xác nhận không hợp lệ.'];
            }
        } catch (Throwable $e) {
            $this->conn->rollBack();
            return ['ok' => false, 'msg' => 'Lỗi xử lý: ' . $e->getMessage()];
        }
    }
}
?>