<?php
require_once __DIR__ . '/../Config/Database.php';

class TenantModel {
    private PDO $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function getTenantStats(): array {
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

    public function deleteTenant(int $id): bool {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = :id AND role = 'tenant'");
        return $stmt->execute([':id' => $id]);
    }
}
?>
