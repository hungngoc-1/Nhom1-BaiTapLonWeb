<?php
require_once __DIR__ . '/../Config/Database.php';

class ContractModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
        $this->ensureExtendedColumns();
    }

    /**
     * Tự động bổ sung các cột nâng cao (giống quanly-nhatro cũ) vào bảng contracts nếu chưa có,
     * đồng thời giữ nguyên cấu trúc gốc của schema.sql để không ảnh hưởng bài của các bạn khác.
     */
    private function ensureExtendedColumns() {
        try {
            $cols = $this->conn->query("SHOW COLUMNS FROM contracts")->fetchAll(PDO::FETCH_COLUMN);
            $alter = [];
            if (!in_array('contract_code', $cols))       $alter[] = "ADD COLUMN `contract_code` VARCHAR(30) NULL AFTER `id`";
            if (!in_array('deposit_date', $cols))        $alter[] = "ADD COLUMN `deposit_date` DATE NULL AFTER `deposit_amount`";
            if (!in_array('deposit_status', $cols))      $alter[] = "ADD COLUMN `deposit_status` VARCHAR(20) DEFAULT 'held' AFTER `deposit_date`";
            if (!in_array('monthly_rent', $cols))        $alter[] = "ADD COLUMN `monthly_rent` DECIMAL(10,2) DEFAULT 0 AFTER `deposit_status`";
            if (!in_array('electricity_rate', $cols))    $alter[] = "ADD COLUMN `electricity_rate` DECIMAL(10,2) DEFAULT 3500 AFTER `monthly_rent`";
            if (!in_array('water_rate', $cols))          $alter[] = "ADD COLUMN `water_rate` DECIMAL(10,2) DEFAULT 20000 AFTER `electricity_rate`";
            if (!in_array('service_fee', $cols))         $alter[] = "ADD COLUMN `service_fee` DECIMAL(10,2) DEFAULT 150000 AFTER `water_rate`";
            if (!in_array('signature_type', $cols))      $alter[] = "ADD COLUMN `signature_type` VARCHAR(20) DEFAULT 'online'";
            if (!in_array('signature_status', $cols))    $alter[] = "ADD COLUMN `signature_status` VARCHAR(20) DEFAULT 'pending'";
            if (!in_array('tenant_signature', $cols))    $alter[] = "ADD COLUMN `tenant_signature` LONGTEXT NULL";
            if (!in_array('manager_signature', $cols))   $alter[] = "ADD COLUMN `manager_signature` LONGTEXT NULL";
            if (!in_array('scan_file', $cols))           $alter[] = "ADD COLUMN `scan_file` VARCHAR(255) NULL";
            if (!in_array('signed_at', $cols))           $alter[] = "ADD COLUMN `signed_at` DATETIME NULL";
            if (!in_array('renewal_status', $cols))      $alter[] = "ADD COLUMN `renewal_status` VARCHAR(20) DEFAULT 'none'";
            if (!in_array('terminated_at', $cols))       $alter[] = "ADD COLUMN `terminated_at` DATE NULL";
            if (!in_array('termination_reason', $cols))  $alter[] = "ADD COLUMN `termination_reason` TEXT NULL";
            if (!in_array('notes', $cols))               $alter[] = "ADD COLUMN `notes` TEXT NULL";

            if (!empty($alter)) {
                $this->conn->exec("ALTER TABLE `contracts` " . implode(', ', $alter));
            }

            // Gán mã hợp đồng & giá thuê mặc định cho các bản ghi cũ chưa có
            $this->conn->exec("
                UPDATE contracts c
                LEFT JOIN rooms r ON r.id = c.room_id
                SET c.contract_code = CONCAT('HD-', YEAR(c.created_at), '-', LPAD(c.id, 3, '0')),
                    c.monthly_rent = IF(c.monthly_rent IS NULL OR c.monthly_rent = 0, COALESCE(r.price, c.deposit_amount), c.monthly_rent),
                    c.deposit_date = COALESCE(c.deposit_date, c.start_date)
                WHERE c.contract_code IS NULL OR c.contract_code = ''
            ");
        } catch (PDOException $e) {
            // Bỏ qua nếu chưa khởi tạo bảng
        }
    }

    // Thống kê nhanh cho 4 thẻ Overview trên trang Hợp đồng
    public function getContractStats() {
        $rows = $this->getAllContracts();
        $total = count($rows);
        $active = 0;
        $expiringSoon = 0;
        $terminated = 0;
        $totalDepositHeld = 0;

        foreach ($rows as $r) {
            if ($r['status'] === 'active') {
                $active++;
                if (($r['deposit_status'] ?? 'held') === 'held') {
                    $totalDepositHeld += (float)$r['deposit_amount'];
                }
                $daysLeft = (int)floor((strtotime($r['end_date']) - strtotime(date('Y-m-d'))) / 86400);
                if ($daysLeft >= 0 && $daysLeft <= 30) {
                    $expiringSoon++;
                }
            } elseif ($r['status'] === 'terminated') {
                $terminated++;
            }
        }

        return [
            'total'              => $total,
            'active'             => $active,
            'expiring_soon'      => $expiringSoon,
            'terminated'         => $terminated,
            'total_deposit_held' => $totalDepositHeld,
        ];
    }

    public function getAllContracts($keyword = '', $status = '') {
        $sql = "SELECT c.*,
                       r.room_number, r.price AS room_price, r.description AS room_description,
                       u.full_name, u.phone, u.email,
                       DATEDIFF(c.end_date, CURDATE()) AS days_left
                FROM contracts c
                LEFT JOIN rooms r ON c.room_id = r.id
                LEFT JOIN users u ON c.tenant_id = u.id
                WHERE 1=1";

        $params = [];
        if ($keyword !== '') {
            $sql .= " AND (c.contract_code LIKE :kw1 OR r.room_number LIKE :kw2 OR u.full_name LIKE :kw3 OR u.phone LIKE :kw4)";
            $params[':kw1'] = $params[':kw2'] = $params[':kw3'] = $params[':kw4'] = "%$keyword%";
        }
        if ($status !== '') {
            $sql .= " AND c.status = :status";
            $params[':status'] = $status;
        }
        $sql .= " ORDER BY (c.status = 'active') DESC, c.id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $list = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($list as &$row) {
            if (empty($row['contract_code'])) {
                $row['contract_code'] = 'HD-' . date('Y', strtotime($row['created_at'] ?? 'now')) . '-' . str_pad($row['id'], 3, '0', STR_PAD_LEFT);
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
        $sql = "SELECT c.*,
                       r.room_number, r.price AS room_price, r.description AS room_description,
                       u.full_name, u.phone, u.email,
                       DATEDIFF(c.end_date, CURDATE()) AS days_left
                FROM contracts c
                LEFT JOIN rooms r ON c.room_id = r.id
                LEFT JOIN users u ON c.tenant_id = u.id
                WHERE c.id = :id
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            if (empty($row['contract_code'])) {
                $row['contract_code'] = 'HD-' . date('Y', strtotime($row['created_at'] ?? 'now')) . '-' . str_pad($row['id'], 3, '0', STR_PAD_LEFT);
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

    public function getRooms() {
        return $this->conn->query("SELECT id, room_number, price, status, description FROM rooms ORDER BY room_number")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTenants() {
        return $this->conn->query("SELECT id, full_name, phone, email FROM users WHERE role = 'tenant' ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);
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

    /**
     * Tính tiền phòng, tiền cọc và phí dịch vụ khi khách mới vào ở (giống quanly-nhatro cũ):
     * - Tiền cọc luôn bằng 1 tháng tiền thuê phòng.
     * - Vào ở từ ngày 1 -> 15: tính 100% tiền phòng + phí dịch vụ + cọc 1 tháng.
     * - Vào ở sau ngày 15: tính 50% tiền phòng + phí dịch vụ + cọc 1 tháng.
     */
    public function calculateMoveInAmounts($monthlyRent, $serviceFee, $startDate) {
        $ts = strtotime($startDate) ?: time();
        $dayOfMonth = (int)date('j', $ts);
        $isAfter15th = ($dayOfMonth > 15);

        $firstMonthRoomFee = $isAfter15th ? round($monthlyRent * 0.5, 2) : $monthlyRent;
        $depositFee = $monthlyRent;
        $total = round($firstMonthRoomFee + $serviceFee + $depositFee, 2);

        $fmt = fn($n) => number_format((float)$n, 0, ',', '.') . ' đ';
        return [
            'day_of_month'     => $dayOfMonth,
            'is_after_15th'    => $isAfter15th,
            'room_fee'         => $firstMonthRoomFee,
            'service_fee'      => $serviceFee,
            'deposit_fee'      => $depositFee,
            'total_move_in'    => $total,
            'rule_description' => $isAfter15th
                ? "Vào ở ngày {$dayOfMonth} (sau ngày 15): Tính 50% tiền phòng tháng đầu ({$fmt($firstMonthRoomFee)}) + phí dịch vụ ({$fmt($serviceFee)}) + Tiền cọc 1 tháng ({$fmt($depositFee)}) = {$fmt($total)}."
                : "Vào ở ngày {$dayOfMonth} (trước/trong ngày 15): Tính 100% tiền phòng cả tháng ({$fmt($firstMonthRoomFee)}) + phí dịch vụ ({$fmt($serviceFee)}) + Tiền cọc 1 tháng ({$fmt($depositFee)}) = {$fmt($total)}."
        ];
    }

    public function generateContractCode() {
        $year = date('Y');
        $count = (int)$this->conn->query("SELECT COUNT(*) FROM contracts")->fetchColumn() + 1;
        return sprintf('HD-%s-%03d', $year, $count);
    }

    // Thêm hợp đồng mới + cập nhật trạng thái phòng sang 'rented'
    public function createContract($data) {
        $code = !empty($data['contract_code']) ? $data['contract_code'] : $this->generateContractCode();
        $monthlyRent = (float)($data['monthly_rent'] ?? $data['deposit_amount'] ?? 0);
        $depositAmount = (float)($data['deposit_amount'] ?? $monthlyRent);
        $depositDate = !empty($data['deposit_date']) ? $data['deposit_date'] : date('Y-m-d');
        $signatureType = in_array($data['signature_type'] ?? '', ['online', 'paper'], true) ? $data['signature_type'] : 'online';
        $status = $data['status'] ?? 'active';

        $sql = "INSERT INTO contracts (
                    contract_code, room_id, tenant_id, start_date, end_date,
                    deposit_amount, deposit_date, deposit_status, monthly_rent,
                    electricity_rate, water_rate, service_fee,
                    status, signature_type, signature_status, notes
                ) VALUES (
                    :contract_code, :room_id, :tenant_id, :start_date, :end_date,
                    :deposit_amount, :deposit_date, 'held', :monthly_rent,
                    :electricity_rate, :water_rate, :service_fee,
                    :status, :signature_type, 'pending', :notes
                )";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':contract_code'    => $code,
            ':room_id'          => (int)$data['room_id'],
            ':tenant_id'        => (int)$data['tenant_id'],
            ':start_date'       => $data['start_date'],
            ':end_date'         => $data['end_date'],
            ':deposit_amount'   => $depositAmount,
            ':deposit_date'     => $depositDate,
            ':monthly_rent'     => $monthlyRent,
            ':electricity_rate' => (float)($data['electricity_rate'] ?? 3500),
            ':water_rate'       => (float)($data['water_rate'] ?? 20000),
            ':service_fee'      => (float)($data['service_fee'] ?? 150000),
            ':status'           => $status,
            ':signature_type'   => $signatureType,
            ':notes'            => trim($data['notes'] ?? ''),
        ]);

        $newId = (int)$this->conn->lastInsertId();

        if ($status === 'active') {
            $upd = $this->conn->prepare("UPDATE rooms SET status = 'rented' WHERE id = :room_id");
            $upd->execute([':room_id' => (int)$data['room_id']]);
        }

        return $newId;
    }

    // Cập nhật hợp đồng
    public function updateContract($id, $data) {
        $old = $this->getContractById($id);
        $monthlyRent = (float)($data['monthly_rent'] ?? $data['deposit_amount'] ?? 0);
        $depositAmount = (float)($data['deposit_amount'] ?? $monthlyRent);
        $depositDate = !empty($data['deposit_date']) ? $data['deposit_date'] : ($old['deposit_date'] ?? date('Y-m-d'));
        $signatureType = in_array($data['signature_type'] ?? '', ['online', 'paper'], true)
            ? $data['signature_type']
            : ($old['signature_type'] ?? 'online');

        $sql = "UPDATE contracts SET
                    room_id = :room_id,
                    tenant_id = :tenant_id,
                    deposit_date = :deposit_date,
                    start_date = :start_date,
                    end_date = :end_date,
                    monthly_rent = :monthly_rent,
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
            ':room_id'          => (int)$data['room_id'],
            ':tenant_id'        => (int)$data['tenant_id'],
            ':deposit_date'     => $depositDate,
            ':start_date'       => $data['start_date'],
            ':end_date'         => $data['end_date'],
            ':monthly_rent'     => $monthlyRent,
            ':deposit_amount'   => $depositAmount,
            ':electricity_rate' => (float)($data['electricity_rate'] ?? 3500),
            ':water_rate'       => (float)($data['water_rate'] ?? 20000),
            ':service_fee'      => (float)($data['service_fee'] ?? 150000),
            ':signature_type'   => $signatureType,
            ':status'           => $data['status'],
            ':notes'            => trim($data['notes'] ?? ''),
            ':id'               => (int)$id,
        ]);

        if ($data['status'] === 'active') {
            $this->conn->prepare("UPDATE rooms SET status = 'rented' WHERE id = :room_id")
                       ->execute([':room_id' => (int)$data['room_id']]);
        }
        return $ok;
    }

    /**
     * Thanh lý hợp đồng (giống quanly-nhatro cũ):
     * - Ngày rời đi >= end_date: hoàn trả 100% tiền cọc (refunded)
     * - Ngày rời đi < end_date: khách mất tiền cọc (forfeited)
     * - Trả trạng thái phòng về 'available' nếu không còn hợp đồng active nào khác
     */
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

    // Lưu chữ ký điện tử (Canvas base64) của Bên A & Bên B
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

    // Gia hạn hợp đồng thêm N tháng
    public function renewContract($id, $months = 12) {
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
}
?>
