<?php
require_once __DIR__ . '/../Config/Database.php';

class AccountModel {
    private PDO $conn;

    public function __construct() {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $this->conn = Database::getConnection();
    }

    /**
     * Lấy danh sách tài khoản theo vai trò và quyền hạn
     * - Admin: Xem được tất cả (Admin, Quản lý, Khách thuê)
     * - Quản lý (landlord): Chỉ xem danh sách tài khoản khách thuê (tenant) thuộc tòa mình quản lý
     */
    public function getAccounts(string $roleFilter = '', string $keyword = '', string $currentRole = 'admin', string $currentBuilding = 'all'): array {
        $params = [];
        $sql = "SELECT u.id, u.username, u.full_name, u.email, u.phone, u.role, u.created_at,
                       c.id AS contract_id,
                       c.status AS contract_status,
                       r.room_number,
                       r.id AS room_id
                FROM users u
                LEFT JOIN contracts c ON c.id = (
                    SELECT c2.id FROM contracts c2
                    WHERE c2.tenant_id = u.id AND c2.status = 'active'
                    ORDER BY c2.id DESC LIMIT 1
                )
                LEFT JOIN rooms r ON r.id = c.room_id
                WHERE 1=1";

        // Nếu là Quản lý: CHỈ được xem khách thuê (tenant)
        if ($currentRole === 'landlord') {
            $sql .= " AND u.role = 'tenant'";
            // Lọc theo tòa nhà mà quản lý phụ trách (dựa vào tiền tố phòng)
            if ($currentBuilding === 'A') {
                $sql .= " AND (r.room_number LIKE 'P1%' OR r.room_number IS NULL)";
            } elseif ($currentBuilding === 'B') {
                $sql .= " AND (r.room_number LIKE 'P2%' OR r.room_number IS NULL)";
            } elseif ($currentBuilding === 'C') {
                $sql .= " AND (r.room_number LIKE 'P3%' OR r.room_number IS NULL)";
            }
        } else {
            // Admin có thể lọc theo role
            if ($roleFilter !== '' && in_array($roleFilter, ['admin', 'landlord', 'tenant'])) {
                $sql .= " AND u.role = :rf";
                $params[':rf'] = $roleFilter;
            }
        }

        if ($keyword !== '') {
            $sql .= " AND (u.username LIKE :kw1 OR u.full_name LIKE :kw2 OR u.phone LIKE :kw3 OR u.email LIKE :kw4 OR r.room_number LIKE :kw5)";
            $kw = "%$keyword%";
            $params[':kw1'] = $kw;
            $params[':kw2'] = $kw;
            $params[':kw3'] = $kw;
            $params[':kw4'] = $kw;
            $params[':kw5'] = $kw;
        }

        $sql .= " ORDER BY CASE u.role WHEN 'admin' THEN 1 WHEN 'landlord' THEN 2 ELSE 3 END ASC, u.id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy thống kê số lượng tài khoản
     */
    public function getAccountStats(string $currentRole = 'admin', string $currentBuilding = 'all'): array {
        if ($currentRole === 'landlord') {
            $tenants = $this->getAccounts('', '', 'landlord', $currentBuilding);
            $total = count($tenants);
            $renting = 0;
            foreach ($tenants as $t) {
                if (!empty($t['room_number'])) {
                    $renting++;
                }
            }
            return [
                'total'    => $total,
                'renting'  => $renting,
                'waiting'  => max(0, $total - $renting),
                'managers' => 0
            ];
        }

        // Admin: thống kê toàn bộ hệ thống
        $total    = (int)$this->conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $admins   = (int)$this->conn->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
        $managers = (int)$this->conn->query("SELECT COUNT(*) FROM users WHERE role = 'landlord'")->fetchColumn();
        $tenants  = (int)$this->conn->query("SELECT COUNT(*) FROM users WHERE role = 'tenant'")->fetchColumn();

        return [
            'total'    => $total,
            'admins'   => $admins,
            'managers' => $managers,
            'tenants'  => $tenants
        ];
    }

    /**
     * Thêm tài khoản mới
     */
    public function createAccount(string $username, string $password, string $fullName, string $phone, string $email, string $role): int {
        // Kiểm tra trùng username hoặc email
        $chk = $this->conn->prepare("SELECT id FROM users WHERE username = :u OR email = :e LIMIT 1");
        $chk->execute([':u' => $username, ':e' => $email]);
        if ($chk->fetch()) {
            throw new Exception("Tên đăng nhập hoặc Email đã tồn tại trong hệ thống!");
        }

        $stmt = $this->conn->prepare("
            INSERT INTO users (username, password, full_name, phone, email, role, created_at)
            VALUES (:u, :p, :fn, :ph, :em, :r, NOW())
        ");
        $stmt->execute([
            ':u'  => $username,
            ':p'  => $password, // lưu plaintext hoặc hash hỗ trợ đăng nhập
            ':fn' => $fullName,
            ':ph' => $phone,
            ':em' => $email,
            ':r'  => $role
        ]);
        return (int)$this->conn->lastInsertId();
    }

    /**
     * Cập nhật thông tin tài khoản
     */
    public function updateAccount(int $id, string $fullName, string $phone, string $email, ?string $role = null): bool {
        // Kiểm tra email trùng với người khác
        $chk = $this->conn->prepare("SELECT id FROM users WHERE email = :e AND id != :id LIMIT 1");
        $chk->execute([':e' => $email, ':id' => $id]);
        if ($chk->fetch()) {
            throw new Exception("Email này đã được sử dụng bởi tài khoản khác!");
        }

        if ($role !== null) {
            $stmt = $this->conn->prepare("UPDATE users SET full_name = :fn, phone = :ph, email = :em, role = :r WHERE id = :id");
            return $stmt->execute([':fn' => $fullName, ':ph' => $phone, ':em' => $email, ':r' => $role, ':id' => $id]);
        } else {
            $stmt = $this->conn->prepare("UPDATE users SET full_name = :fn, phone = :ph, email = :em WHERE id = :id");
            return $stmt->execute([':fn' => $fullName, ':ph' => $phone, ':em' => $email, ':id' => $id]);
        }
    }

    /**
     * Đổi / Đặt lại mật khẩu tài khoản
     */
    public function resetPassword(int $id, string $newPassword): bool {
        $stmt = $this->conn->prepare("UPDATE users SET password = :p WHERE id = :id");
        return $stmt->execute([':p' => $newPassword, ':id' => $id]);
    }

    /**
     * Xóa tài khoản
     */
    public function deleteAccount(int $id): bool {
        // Kiểm tra xem tài khoản có hợp đồng đang hiệu lực không
        $chk = $this->conn->prepare("SELECT COUNT(*) FROM contracts WHERE tenant_id = :id AND status = 'active'");
        $chk->execute([':id' => $id]);
        if ((int)$chk->fetchColumn() > 0) {
            throw new Exception("Không thể xóa tài khoản này vì khách đang có hợp đồng thuê phòng hiệu lực!");
        }

        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Lấy 1 tài khoản theo ID
     */
    public function getAccountById(int $id): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
