<?php
require_once __DIR__ . '/../Config/Database.php';

class TenantModel {
    private PDO $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function getTenantStats(): array {
        require_once __DIR__ . '/ReportModel.php';
        $mgr = ReportModel::getManagerBuildingInfo();
        if ($mgr) {
            $stTotal = $this->conn->prepare("SELECT COUNT(DISTINCT u.id) FROM users u JOIN contracts c ON c.tenant_id = u.id JOIN rooms r ON r.id = c.room_id WHERE u.role = 'tenant' AND (r.building_id = :bId OR r.room_number LIKE :pfx)");
            $stTotal->execute([':bId' => $mgr['id'], ':pfx' => $mgr['prefix'] . '%']);
            $staying = (int)$stTotal->fetchColumn();
            $stDebt = $this->conn->prepare("SELECT COUNT(DISTINCT c.tenant_id) FROM invoices i JOIN contracts c ON c.id = i.contract_id JOIN rooms r ON r.id = c.room_id WHERE i.status = 'unpaid' AND (r.building_id = :bId OR r.room_number LIKE :pfx)");
            $stDebt->execute([':bId' => $mgr['id'], ':pfx' => $mgr['prefix'] . '%']);
            $debtTenants = (int)$stDebt->fetchColumn();
            return ['total' => $staying, 'staying' => $staying, 'unassigned' => 0, 'debt_tenants' => $debtTenants];
        }
        $total = (int)$this->conn->query("SELECT COUNT(*) FROM users WHERE role = 'tenant'")->fetchColumn();
        $staying = (int)$this->conn->query("
            SELECT COUNT(DISTINCT u.id)
            FROM users u
            JOIN contracts c ON c.tenant_id = u.id AND c.status = 'active'
            WHERE u.role = 'tenant'
        ")->fetchColumn();
        $unassigned = max(0, $total - $staying);
        $debtTenants = (int)$this->conn->query("
            SELECT COUNT(DISTINCT c.tenant_id)
            FROM invoices i
            JOIN contracts c ON c.id = i.contract_id
            WHERE i.status = 'unpaid'
        ")->fetchColumn();

        return [
            'total'        => $total,
            'staying'      => $staying,
            'unassigned'   => $unassigned,
            'debt_tenants' => $debtTenants,
        ];
    }

    public function getTenants(string $keyword = '', string $stayStatus = ''): array {
        $sql = "SELECT u.*,
                       c.id AS contract_id,
                       c.room_id,
                       c.start_date,
                       c.end_date,
                       c.deposit_amount,
                       r.room_number,
                       r.price AS room_price
                FROM users u
                LEFT JOIN contracts c ON c.id = (
                    SELECT c2.id FROM contracts c2
                    WHERE c2.tenant_id = u.id AND c2.status = 'active'
                    ORDER BY c2.id DESC LIMIT 1
                )
                LEFT JOIN rooms r ON r.id = c.room_id
                WHERE u.role = 'tenant'";

        $params = [];
        require_once __DIR__ . '/ReportModel.php';
        $mgr = ReportModel::getManagerBuildingInfo();
        if ($mgr) {
            $sql .= " AND (r.building_id = :mgrBId OR r.room_number LIKE :mgrPfx)";
            $params[':mgrBId'] = $mgr['id'];
            $params[':mgrPfx'] = $mgr['prefix'] . '%';
        }
        if ($keyword !== '') {
            $sql .= " AND (u.full_name LIKE :kw1 OR u.phone LIKE :kw2 OR u.email LIKE :kw3 OR u.username LIKE :kw4 OR r.room_number LIKE :kw5)";
            $kw = "%$keyword%";
            $params[':kw1'] = $kw;
            $params[':kw2'] = $kw;
            $params[':kw3'] = $kw;
            $params[':kw4'] = $kw;
            $params[':kw5'] = $kw;
        }

        if ($stayStatus === 'staying') {
            $sql .= " AND c.id IS NOT NULL";
        } elseif ($stayStatus === 'unassigned') {
            $sql .= " AND c.id IS NULL";
        }

        $sql .= " ORDER BY (c.id IS NOT NULL) DESC, u.id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createTenant(string $username, string $password, string $fullName, string $phone, string $email): bool {
        $sql = "INSERT INTO users (username, password, full_name, phone, email, role)
                VALUES (:username, :password, :full_name, :phone, :email, 'tenant')";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':username'  => $username,
            ':password'  => password_hash($password, PASSWORD_DEFAULT),
            ':full_name' => $fullName,
            ':phone'     => $phone,
            ':email'     => $email,
        ]);
    }

    public function createTenantFull(
        string $fullName,
        string $cccd,
        string $phone,
        ?string $birthday,
        string $gender,
        string $email,
        string $occupation,
        string $hometown,
        string $emergencyContact,
        string $status,
        ?string $roommateName = null,
        ?string $roommatePhone = null
    ): int {
        $username = $phone;
        $hashedPassword = password_hash('123', PASSWORD_DEFAULT);

        // Kiểm tra xem số điện thoại / username đã tồn tại chưa
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE phone = :phone OR username = :username LIMIT 1");
        $stmt->execute([':phone' => $phone, ':username' => $username]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $userId = (int)$existing['id'];
            $sql = "UPDATE users SET 
                        full_name = :full_name,
                        cccd = :cccd,
                        phone = :phone,
                        birthday = :birthday,
                        gender = :gender,
                        email = :email,
                        occupation = :occupation,
                        hometown = :hometown,
                        emergency_contact = :emergency_contact,
                        status = :status,
                        roommate_name = :roommate_name,
                        roommate_phone = :roommate_phone,
                        role = 'tenant'
                    WHERE id = :id";
            $uStmt = $this->conn->prepare($sql);
            $uStmt->execute([
                ':full_name'         => $fullName,
                ':cccd'              => $cccd,
                ':phone'             => $phone,
                ':birthday'          => $birthday,
                ':gender'            => $gender,
                ':email'             => $email,
                ':occupation'        => $occupation,
                ':hometown'          => $hometown,
                ':emergency_contact' => $emergencyContact,
                ':status'            => $status,
                ':roommate_name'     => $roommateName,
                ':roommate_phone'    => $roommatePhone,
                ':id'                => $userId,
            ]);
            return $userId;
        }

        $sql = "INSERT INTO users (
                    username, password, full_name, cccd, phone, birthday, 
                    gender, email, occupation, hometown, emergency_contact, 
                    status, roommate_name, roommate_phone, role
                ) VALUES (
                    :username, :password, :full_name, :cccd, :phone, :birthday,
                    :gender, :email, :occupation, :hometown, :emergency_contact,
                    :status, :roommate_name, :roommate_phone, 'tenant'
                )";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':username'          => $username,
            ':password'          => $hashedPassword,
            ':full_name'         => $fullName,
            ':cccd'              => $cccd,
            ':phone'             => $phone,
            ':birthday'          => $birthday,
            ':gender'            => $gender,
            ':email'             => $email,
            ':occupation'        => $occupation,
            ':hometown'          => $hometown,
            ':emergency_contact' => $emergencyContact,
            ':status'            => $status,
            ':roommate_name'     => $roommateName,
            ':roommate_phone'    => $roommatePhone,
        ]);

        return (int)$this->conn->lastInsertId();
    }

    public function updateTenant(int $id, string $fullName, string $phone, string $email): bool {
        $sql = "UPDATE users SET full_name = :full_name, phone = :phone, email = :email WHERE id = :id AND role = 'tenant'";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':full_name' => $fullName,
            ':phone'     => $phone,
            ':email'     => $email,
            ':id'        => $id,
        ]);
    }

    public function updateTenantFull(
        int $id,
        string $fullName,
        string $cccd,
        string $phone,
        ?string $birthday,
        string $gender,
        string $email,
        string $occupation,
        string $hometown,
        string $emergencyContact,
        string $status,
        ?string $roommateName = null,
        ?string $roommatePhone = null
    ): bool {
        $sql = "UPDATE users SET 
                    full_name = :full_name,
                    cccd = :cccd,
                    phone = :phone,
                    birthday = :birthday,
                    gender = :gender,
                    email = :email,
                    occupation = :occupation,
                    hometown = :hometown,
                    emergency_contact = :emergency_contact,
                    status = :status,
                    roommate_name = :roommate_name,
                    roommate_phone = :roommate_phone
                WHERE id = :id AND role = 'tenant'";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':full_name'         => $fullName,
            ':cccd'              => $cccd,
            ':phone'             => $phone,
            ':birthday'          => $birthday,
            ':gender'            => $gender,
            ':email'             => $email,
            ':occupation'        => $occupation,
            ':hometown'          => $hometown,
            ':emergency_contact' => $emergencyContact,
            ':status'            => $status,
            ':roommate_name'     => $roommateName,
            ':roommate_phone'    => $roommatePhone,
            ':id'                => $id,
        ]);
    }

    
    public function updateContractRoommate(int $tenantId, string $roommateName, string $roommatePhone = ''): void {
        $note = "Ở cùng: {$roommateName}" . ($roommatePhone !== '' ? " ({$roommatePhone})" : '');
        try {
            $stmt = $this->conn->prepare("
                UPDATE contracts 
                SET notes = CASE 
                    WHEN notes IS NULL OR notes = '' THEN :note 
                    WHEN notes LIKE '%Ở cùng:%' THEN REGEXP_REPLACE(notes, 'Ở cùng:[^.]*', :note)
                    ELSE CONCAT(notes, '. ', :note)
                END
                WHERE tenant_id = :tenant_id AND status = 'active'
            ");
            $stmt->execute([':note' => $note, ':tenant_id' => $tenantId]);
        } catch (PDOException $e) {}
    }

    public function assignRoomToTenant(int $tenantId, int $roomId, ?string $roommateName = null, ?string $roommatePhone = null): void {
        // Cập nhật thông tin phòng ưu tiên và bạn cùng phòng trong users
        $stmt = $this->conn->prepare("
            UPDATE users SET 
                preferred_room_id = :room_id,
                roommate_name = COALESCE(:roommate_name, roommate_name),
                roommate_phone = COALESCE(:roommate_phone, roommate_phone)
            WHERE id = :tenant_id
        ");
        $stmt->execute([
            ':room_id'        => $roomId,
            ':roommate_name'  => $roommateName,
            ':roommate_phone' => $roommatePhone,
            ':tenant_id'      => $tenantId,
        ]);

        // Đổi trạng thái phòng thành 'rented'
        $stmt = $this->conn->prepare("UPDATE rooms SET status = 'rented' WHERE id = :room_id");
        $stmt->execute([':room_id' => $roomId]);

        // Lấy thông tin phòng để ghi vào hợp đồng
        $rStmt = $this->conn->prepare("SELECT room_number, price FROM rooms WHERE id = :room_id");
        $rStmt->execute([':room_id' => $roomId]);
        $room = $rStmt->fetch(PDO::FETCH_ASSOC);
        $roomPrice = $room ? (float)$room['price'] : 0.0;
        $roomNumber = $room ? $room['room_number'] : (string)$roomId;

        // Chấm dứt các hợp đồng đang active trước đó của phòng này và của khách này
        $stmt = $this->conn->prepare("UPDATE contracts SET status = 'terminated' WHERE room_id = :room_id AND status = 'active'");
        $stmt->execute([':room_id' => $roomId]);

        $stmt = $this->conn->prepare("UPDATE contracts SET status = 'terminated' WHERE tenant_id = :tenant_id AND status = 'active'");
        $stmt->execute([':tenant_id' => $tenantId]);

        // Tạo hợp đồng mới
        $contractCode = 'HD-' . date('Y') . '-' . strtoupper(str_replace(' ', '', (string)$roomNumber));
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime('+1 year'));

        $note = "Hợp đồng thuê phòng {$roomNumber}";
        if ($roommateName) {
            $note .= ". Ở cùng: {$roommateName}" . ($roommatePhone ? " ({$roommatePhone})" : '');
        }

        $cSql = "INSERT INTO contracts (
                    contract_code, contract_type, room_id, tenant_id, start_date, end_date,
                    deposit_amount, deposit_date, deposit_paid_percent, deposit_status,
                    monthly_rent, status, notes
                 ) VALUES (
                    :code, 'official_lease', :room_id, :tenant_id, :start_date, :end_date,
                    :deposit, :deposit_date, 100, 'paid',
                    :rent, 'active', :notes
                 )";
        $cStmt = $this->conn->prepare($cSql);
        $cStmt->execute([
            ':code'         => $contractCode,
            ':room_id'      => $roomId,
            ':tenant_id'    => $tenantId,
            ':start_date'   => $startDate,
            ':end_date'     => $endDate,
            ':deposit'      => $roomPrice,
            ':deposit_date' => $startDate,
            ':rent'         => $roomPrice,
            ':notes'        => $note,
        ]);
    }

    public function deleteTenant(int $id): bool {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = :id AND role = 'tenant'");
        return $stmt->execute([':id' => $id]);
    }
}
?>