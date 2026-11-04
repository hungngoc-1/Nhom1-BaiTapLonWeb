<?php
require_once __DIR__ . '/../Config/Database.php';

class TenantPortalModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->ensureTable();
    }

    /**
     * Tự động khởi tạo bảng tenant_requests nếu chưa tồn tại
     */
    private function ensureTable(): void {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `tenant_requests` (
                  `id` INT AUTO_INCREMENT PRIMARY KEY,
                  `tenant_id` INT NOT NULL,
                  `request_type` VARCHAR(50) NOT NULL DEFAULT 'profile_update',
                  `field_target` VARCHAR(100) NULL,
                  `old_value` TEXT NULL,
                  `new_value` TEXT NOT NULL,
                  `reason` TEXT NULL,
                  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
                  `manager_note` TEXT NULL,
                  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  INDEX (`tenant_id`),
                  INDEX (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        } catch (PDOException $e) {
            // bỏ qua nếu lỗi
        }
    }

    /**
     * Lấy thông tin cá nhân và hợp đồng / phòng đang thuê của khách
     */
    public function getTenantProfile(int $tenantId): ?array {
        $userCols = $this->db->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
        $roomCols = $this->db->query("SHOW COLUMNS FROM rooms")->fetchAll(PDO::FETCH_COLUMN);
        $hasBuildingId = in_array('building_id', $roomCols, true);

        $sql = "
            SELECT u.id, u.username, u.full_name, u.phone, u.email, u.role, u.created_at,
                   c.id AS contract_id, c.contract_code, c.contract_type, c.start_date, c.end_date,
                   c.deposit_amount, c.deposit_paid_percent, c.monthly_rent, c.status AS contract_status,
                   c.scan_file, c.signature_status,
                   r.id AS room_id, r.room_number, r.price AS room_price, r.description AS room_description";

        if ($hasBuildingId) {
            $sql .= ", b.id AS building_id, b.name AS building_name, b.address AS building_address,
                       mgr.full_name AS manager_name, mgr.phone AS manager_phone";
        }

        $sql .= "
            FROM users u
            LEFT JOIN contracts c ON c.id = (
                SELECT c2.id FROM contracts c2
                WHERE c2.tenant_id = u.id AND c2.status = 'active'
                ORDER BY c2.id DESC LIMIT 1
            )
            LEFT JOIN rooms r ON r.id = c.room_id";

        if ($hasBuildingId) {
            $sql .= "
            LEFT JOIN buildings b ON b.id = r.building_id
            LEFT JOIN users mgr ON mgr.id = b.manager_id";
        }

        $sql .= " WHERE u.id = :tenant_id LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        // Nếu thiếu hợp đồng active thì thử tìm hợp đồng gần nhất
        if (empty($row['contract_id'])) {
            $cStmt = $this->db->prepare("
                SELECT c.id AS contract_id, c.contract_code, c.contract_type, c.start_date, c.end_date,
                       c.deposit_amount, c.deposit_paid_percent, c.monthly_rent, c.status AS contract_status,
                       c.scan_file, c.signature_status,
                       r.id AS room_id, r.room_number, r.price AS room_price, r.description AS room_description
                FROM contracts c
                LEFT JOIN rooms r ON r.id = c.room_id
                WHERE c.tenant_id = :tenant_id
                ORDER BY c.id DESC LIMIT 1
            ");
            $cStmt->execute([':tenant_id' => $tenantId]);
            $latestC = $cStmt->fetch(PDO::FETCH_ASSOC);
            if ($latestC) {
                $row = array_merge($row, $latestC);
            }
        }

        // Tự động gán tòa nhà theo mã phòng nếu không có bảng buildings
        if (empty($row['building_name'])) {
            $rn = $row['room_number'] ?? '';
            if (str_starts_with($rn, 'A') || str_starts_with($rn, 'P1')) {
                $row['building_name'] = 'Tòa A - StayMate Sunrise';
                $row['building_address'] = 'Số 128 Cầu Giấy, Hà Nội';
            } elseif (str_starts_with($rn, 'B') || str_starts_with($rn, 'P2')) {
                $row['building_name'] = 'Tòa B - StayMate Emerald';
                $row['building_address'] = 'Số 45 Nguyễn Chí Thanh, Hà Nội';
            } elseif (str_starts_with($rn, 'C') || str_starts_with($rn, 'P3')) {
                $row['building_name'] = 'Tòa C - StayMate Horizon';
                $row['building_address'] = 'Số 88 Nguyễn Trãi, Hà Nội';
            } else {
                $row['building_name'] = 'Khu Căn Hộ StayMate';
                $row['building_address'] = 'Hà Nội';
            }
        }

        if (empty($row['manager_name'])) {
            $row['manager_name'] = 'Ban Quản Lý StayMate';
            $row['manager_phone'] = '0988 888 999';
        }

        return $row;
    }

    /**
     * Lấy danh sách các hóa đơn của riêng khách thuê
     */
    public function getTenantInvoices(int $tenantId): array {
        $sql = "
            SELECT i.*, r.room_number, r.price AS room_price,
                   c.contract_code,
                   ur.electric_old, ur.electric_new, ur.water_old, ur.water_new
            FROM invoices i
            JOIN rooms r ON r.id = i.room_id
            LEFT JOIN contracts c ON c.id = i.contract_id
            LEFT JOIN utilities_readings ur ON ur.room_id = i.room_id AND ur.month_year = i.month_year
            WHERE (c.tenant_id = :t1 OR i.contract_id IN (SELECT id FROM contracts WHERE tenant_id = :t2) OR i.room_id IN (SELECT room_id FROM contracts WHERE tenant_id = :t3))
            ORDER BY SUBSTRING(i.month_year, 4, 4) DESC, SUBSTRING(i.month_year, 1, 2) DESC, i.id DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':t1' => $tenantId, ':t2' => $tenantId, ':t3' => $tenantId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $out = [];
        foreach ($rows as $r) {
            $r['code'] = 'HD' . str_pad((string)$r['id'], 4, '0', STR_PAD_LEFT);
            // Tính hạn thanh toán (ngày 10 của tháng kế tiếp)
            $parts = explode('-', $r['month_year']);
            if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                $m = (int)$parts[0];
                $y = (int)$parts[1];
                $nextM = $m === 12 ? 1 : $m + 1;
                $nextY = $m === 12 ? $y + 1 : $y;
                $r['due_date'] = sprintf('%04d-%02d-10', $nextY, $nextM);
            } else {
                $r['due_date'] = date('Y-m-10');
            }
            $r['is_overdue'] = ($r['status'] === 'unpaid' && $r['due_date'] < date('Y-m-d'));
            
            // Chi tiết số tiền điện nước
            $eUsed = max(0, (int)($r['electric_new'] ?? 0) - (int)($r['electric_old'] ?? 0));
            $wUsed = max(0, (int)($r['water_new'] ?? 0) - (int)($r['water_old'] ?? 0));
            $r['electric_used'] = $eUsed;
            $r['water_used'] = $wUsed;

            $out[] = $r;
        }

        return $out;
    }

    /**
     * Lấy chi tiết một hóa đơn (kèm danh mục dịch vụ trong hóa đơn)
     */
    public function getInvoiceDetail(int $invoiceId, int $tenantId): ?array {
        $sql = "
            SELECT i.*, r.room_number, r.price AS room_price,
                   c.contract_code, c.tenant_id,
                   u.full_name AS tenant_name, u.phone AS tenant_phone,
                   ur.electric_old, ur.electric_new, ur.water_old, ur.water_new
            FROM invoices i
            JOIN rooms r ON r.id = i.room_id
            LEFT JOIN contracts c ON c.id = i.contract_id
            LEFT JOIN users u ON u.id = c.tenant_id
            LEFT JOIN utilities_readings ur ON ur.room_id = i.room_id AND ur.month_year = i.month_year
            WHERE i.id = :id AND (c.tenant_id = :t1 OR i.room_id IN (SELECT room_id FROM contracts WHERE tenant_id = :t2))
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $invoiceId, ':t1' => $tenantId, ':t2' => $tenantId]);
        $inv = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$inv) return null;

        $inv['code'] = 'HD' . str_pad((string)$inv['id'], 4, '0', STR_PAD_LEFT);

        // Lấy danh sách invoice_details
        $dStmt = $this->db->prepare("
            SELECT d.*, s.service_name, s.unit
            FROM invoice_details d
            LEFT JOIN services s ON s.id = d.service_id
            WHERE d.invoice_id = :id
            ORDER BY d.id ASC
        ");
        $dStmt->execute([':id' => $invoiceId]);
        $inv['details'] = $dStmt->fetchAll(PDO::FETCH_ASSOC);

        return $inv;
    }

    /**
     * Lấy danh sách bảng đơn giá dịch vụ
     */
    public function getActiveServices(): array {
        try {
            $stmt = $this->db->query("SELECT * FROM services ORDER BY id ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Lấy hợp đồng chi tiết của khách thuê
     */
    public function getTenantContract(int $tenantId, int $contractId = 0): ?array {
        $sql = "
            SELECT c.*,
                   r.room_number, r.price AS room_price, r.description AS room_description,
                   u.full_name AS tenant_name, u.phone AS tenant_phone, u.email AS tenant_email,
                   DATEDIFF(c.end_date, CURDATE()) AS days_left
            FROM contracts c
            LEFT JOIN rooms r ON r.id = c.room_id
            LEFT JOIN users u ON u.id = c.tenant_id
            WHERE c.tenant_id = :tenant_id";

        $params = [':tenant_id' => $tenantId];

        if ($contractId > 0) {
            $sql .= " AND c.id = :cid";
            $params[':cid'] = $contractId;
        }

        $sql .= " ORDER BY (c.status = 'active') DESC, c.id DESC LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $contract = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$contract) return null;

        if (empty($contract['contract_code'])) {
            $contract['contract_code'] = 'HD-' . date('Y', strtotime($contract['created_at'] ?? 'now')) . '-' . str_pad($contract['id'], 3, '0', STR_PAD_LEFT);
        }
        if (empty($contract['monthly_rent']) || (float)$contract['monthly_rent'] <= 0) {
            $contract['monthly_rent'] = $contract['room_price'] ?? $contract['deposit_amount'];
        }

        return $contract;
    }

    /**
     * Gửi yêu cầu điều chỉnh thông tin lên Ban Quản Lý
     */
    public function createRequest(int $tenantId, string $requestType, string $fieldTarget, string $oldValue, string $newValue, string $reason): bool {
        $stmt = $this->db->prepare("
            INSERT INTO tenant_requests (tenant_id, request_type, field_target, old_value, new_value, reason, status)
            VALUES (:tid, :rtype, :ftarget, :oldval, :newval, :reason, 'pending')
        ");
        return $stmt->execute([
            ':tid'     => $tenantId,
            ':rtype'   => $requestType,
            ':ftarget' => $fieldTarget,
            ':oldval'  => $oldValue,
            ':newval'  => $newValue,
            ':reason'  => $reason
        ]);
    }

    /**
     * Lấy lịch sử yêu cầu của khách thuê
     */
    public function getTenantRequests(int $tenantId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM tenant_requests
            WHERE tenant_id = :tid
            ORDER BY id DESC
        ");
        $stmt->execute([':tid' => $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy tất cả yêu cầu của toàn bộ khách thuê (dành cho Quản lý / Admin duyệt)
     */
    public function getAllRequests(?string $status = null): array {
        $sql = "
            SELECT tr.*, u.full_name AS tenant_name, u.phone AS tenant_phone, u.email AS tenant_email, u.username
            FROM tenant_requests tr
            JOIN users u ON u.id = tr.tenant_id
        ";
        if ($status !== null && $status !== '') {
            $sql .= " WHERE tr.status = :status";
        }
        $sql .= " ORDER BY tr.id DESC";

        $stmt = $this->db->prepare($sql);
        if ($status !== null && $status !== '') {
            $stmt->execute([':status' => $status]);
        } else {
            $stmt->execute();
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Quản lý phê duyệt yêu cầu và tự động cập nhật thông tin người dùng
     */
    public function approveRequest(int $requestId, string $managerNote = ''): bool {
        $stmt = $this->db->prepare("SELECT * FROM tenant_requests WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $requestId]);
        $req = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$req) return false;

        $tenantId = (int)$req['tenant_id'];
        $field = $req['field_target'];
        $newVal = $req['new_value'];

        // Cập nhật thông tin vào bảng users nếu trường hợp lệ
        if (in_array($field, ['full_name', 'phone', 'email'], true)) {
            $updUser = $this->db->prepare("UPDATE users SET `{$field}` = :val WHERE id = :uid");
            $updUser->execute([':val' => $newVal, ':uid' => $tenantId]);
        }

        // Cập nhật trạng thái yêu cầu
        $updReq = $this->db->prepare("
            UPDATE tenant_requests
            SET status = 'approved', manager_note = :note
            WHERE id = :id
        ");
        return $updReq->execute([
            ':note' => $managerNote !== '' ? $managerNote : 'Quản lý đã duyệt và cập nhật thông tin.',
            ':id'   => $requestId
        ]);
    }

    /**
     * Quản lý từ chối yêu cầu
     */
    public function rejectRequest(int $requestId, string $managerNote = ''): bool {
        $updReq = $this->db->prepare("
            UPDATE tenant_requests
            SET status = 'rejected', manager_note = :note
            WHERE id = :id
        ");
        return $updReq->execute([
            ':note' => $managerNote !== '' ? $managerNote : 'Yêu cầu không được phê duyệt.',
            ':id'   => $requestId
        ]);
    }
}
