<?php
require_once __DIR__ . '/../Config/Database.php';

/**
 * Model Hóa đơn & Thu tiền.
 * Dùng các bảng: invoices, invoice_details, utilities_readings, rooms, contracts, users, services.
 * Tự bổ sung các cột phục vụ thu tiền (paid_at, payment_method, payment_note) vào bảng invoices nếu chưa có.
 */
class InvoiceModel {
    private PDO $db;

    public const METHODS = [
        'cash'     => 'Tiền mặt',
        'transfer' => 'Chuyển khoản',
        'ewallet'  => 'Ví điện tử (Momo/ZaloPay)',
    ];

    public function __construct($pdo = null) {
        $this->db = $pdo instanceof PDO ? $pdo : Database::getConnection();
        $this->ensureColumns();
    }

    private function ensureColumns(): void {
        try {
            $cols = $this->db->query("SHOW COLUMNS FROM invoices")->fetchAll(PDO::FETCH_COLUMN);
            $alter = [];
            if (!in_array('paid_at', $cols, true))         $alter[] = "ADD COLUMN `paid_at` DATETIME NULL";
            if (!in_array('payment_method', $cols, true))  $alter[] = "ADD COLUMN `payment_method` VARCHAR(20) NULL";
            if (!in_array('payment_note', $cols, true))    $alter[] = "ADD COLUMN `payment_note` VARCHAR(255) NULL";
            if ($alter) {
                $this->db->exec("ALTER TABLE `invoices` " . implode(', ', $alter));
            }
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `third_party_expenses` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `month_year` VARCHAR(7) NOT NULL,
                    `expense_type` VARCHAR(30) NOT NULL,
                    `title` VARCHAR(150) NOT NULL,
                    `provider_name` VARCHAR(150) NOT NULL,
                    `quantity` DECIMAL(10,2) DEFAULT 0,
                    `unit` VARCHAR(30) DEFAULT '',
                    `unit_price` DECIMAL(10,2) DEFAULT 0,
                    `amount` DECIMAL(10,2) NOT NULL,
                    `customer_amount` DECIMAL(10,2) DEFAULT 0,
                    `status` ENUM('paid', 'pending') DEFAULT 'paid',
                    `paid_at` DATE NULL,
                    `notes` VARCHAR(255) NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB;
            ");
        } catch (PDOException $e) {
            // bỏ qua nếu bảng chưa tồn tại
        }
    }

    // ===================== ĐƠN GIÁ DỊCH VỤ =====================
    public function getRates(): array {
        $rates = ['elec' => 3500.0, 'elec_id' => null, 'water' => 20000.0, 'water_id' => null, 'others' => [], 'others_total' => 0.0];
        foreach ($this->db->query("SELECT * FROM services ORDER BY id ASC")->fetchAll() as $s) {
            $n = mb_strtolower($s['service_name'], 'UTF-8');
            if (strpos($n, 'điện') !== false || strpos($n, 'dien') !== false) {
                $rates['elec'] = (float)$s['unit_price'];
                $rates['elec_id'] = (int)$s['id'];
            } elseif (strpos($n, 'nước') !== false || strpos($n, 'nuoc') !== false) {
                $rates['water'] = (float)$s['unit_price'];
                $rates['water_id'] = (int)$s['id'];
            } else {
                $rates['others'][] = $s;
                $rates['others_total'] += (float)$s['unit_price'];
            }
        }
        return $rates;
    }

    // ===================== KỲ & DANH SÁCH =====================
    private const ORDER_PERIOD = "SUBSTRING(i.month_year, 4, 4) DESC, SUBSTRING(i.month_year, 1, 2) DESC";

    public function getPeriods(): array {
        return $this->db->query("
            SELECT DISTINCT month_year FROM invoices i
            ORDER BY " . self::ORDER_PERIOD
        )->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getInvoices(array $f = []): array {
        $sql = "
            SELECT i.*, r.room_number, r.price AS room_price,
                   u.full_name AS tenant_name, u.phone AS tenant_phone,
                   ur.electric_old, ur.electric_new, ur.water_old, ur.water_new,
                   c.id AS contract_id_val, c.deposit_amount, c.deposit_paid_percent, c.deposit_status
            FROM invoices i
            JOIN rooms r ON r.id = i.room_id
            LEFT JOIN contracts c ON c.id = i.contract_id
            LEFT JOIN users u ON u.id = c.tenant_id
            LEFT JOIN utilities_readings ur ON ur.room_id = i.room_id AND ur.month_year = i.month_year
            WHERE 1=1";
        $p = [];
        require_once __DIR__ . '/ReportModel.php';
        $mgr = ReportModel::getManagerBuildingInfo();
        if ($mgr) {
            $sql .= " AND (r.building_id = ? OR r.room_number LIKE ?)";
            $p[] = $mgr['id'];
            $p[] = $mgr['prefix'] . '%';
        }
        if (!empty($f['period']) && $f['period'] !== 'all') { $sql .= " AND i.month_year = ?"; $p[] = $f['period']; }
        if (!empty($f['room_id'])) { $sql .= " AND i.room_id = ?"; $p[] = (int)$f['room_id']; }
        if (($f['status'] ?? '') === 'paid') { $sql .= " AND i.status = 'paid'"; }
        if (in_array($f['status'] ?? '', ['unpaid', 'overdue'], true)) { $sql .= " AND i.status = 'unpaid'"; }
        if (($f['status'] ?? '') === 'recorded') { $sql .= " AND ur.id IS NOT NULL AND i.status = 'unpaid'"; }
        if (($f['status'] ?? '') === 'pending') { $sql .= " AND ur.id IS NULL"; }
        if (!empty($f['keyword'])) {
            $sql .= " AND (r.room_number LIKE ? OR u.full_name LIKE ? OR CONCAT('HD', LPAD(i.id, 4, '0')) LIKE ?)";
            $kw = '%' . $f['keyword'] . '%';
            array_push($p, $kw, $kw, $kw);
        }
        $sql .= " ORDER BY " . self::ORDER_PERIOD . ", r.room_number ASC";
        $st = $this->db->prepare($sql);
        $st->execute($p);
        $rows = $st->fetchAll();

        $rates = $this->getRates();
        $detailMap = $this->getDetailMap(array_column($rows, 'id'));
        $out = [];
        foreach ($rows as $row) {
            $row['code'] = self::code((int)$row['id']);
            $row['due_date'] = self::dueDate($row['month_year']);
            $row['is_overdue'] = $row['status'] === 'unpaid' && $row['due_date'] < date('Y-m-d');
            $row['breakdown'] = $this->breakdown($row, $rates, $detailMap[$row['id']] ?? []);
            
            // Xử lý Cột Cọc & Trạng thái cọc (Đã cọc 50%, Hoàn thành cọc, Hoàn cọc)
            $depAmount = (float)($row['deposit_amount'] ?? 0);
            $depPercent = (int)($row['deposit_paid_percent'] ?? 100);
            $depStatus = (string)($row['deposit_status'] ?? 'held');

            if ($depStatus === 'refunded') {
                $row['deposit_label'] = 'Hoàn cọc';
                $row['deposit_badge_class'] = 'bg-secondary-subtle text-secondary border border-secondary';
                $row['deposit_icon'] = 'fa-solid fa-arrow-rotate-left';
                $row['deposit_sub'] = 'Đã hoàn trả ' . number_format($depAmount, 0, ',', '.') . ' đ';
                $row['deposit_key'] = 'refunded';
            } elseif ($depPercent == 50 || ($depPercent > 0 && $depPercent < 100)) {
                $row['deposit_label'] = 'Đã cọc 50%';
                $row['deposit_badge_class'] = 'bg-warning-subtle text-warning-emphasis border border-warning';
                $row['deposit_icon'] = 'fa-solid fa-clock-rotate-left';
                $row['deposit_sub'] = number_format($depAmount * 0.5, 0, ',', '.') . ' / ' . number_format($depAmount, 0, ',', '.') . ' đ';
                $row['deposit_key'] = 'partial_50';
            } else {
                $row['deposit_label'] = 'Hoàn thành cọc';
                $row['deposit_badge_class'] = 'bg-success-subtle text-success border border-success-subtle';
                $row['deposit_icon'] = 'fa-solid fa-circle-check';
                $row['deposit_sub'] = number_format($depAmount, 0, ',', '.') . ' đ';
                $row['deposit_key'] = 'completed';
            }
            if (($f['status'] ?? '') === 'overdue' && !$row['is_overdue']) continue;
            if (($f['status'] ?? '') === 'unpaid' && $row['is_overdue']) continue;
            $out[] = $row;
        }
        return $out;
    }

    private function getDetailMap(array $ids): array {
        if (!$ids) return [];
        $in = implode(',', array_map('intval', $ids));
        $map = [];
        foreach ($this->db->query("
            SELECT d.*, s.service_name, s.unit
            FROM invoice_details d LEFT JOIN services s ON s.id = d.service_id
            WHERE d.invoice_id IN ($in) ORDER BY d.id ASC")->fetchAll() as $d) {
            $map[$d['invoice_id']][] = $d;
        }
        return $map;
    }

    /**
     * Phân tích hóa đơn thành các dòng (phòng, điện, nước, dịch vụ, điều chỉnh).
     * Nếu có invoice_details thì dùng chi tiết đã lưu; nếu không thì ước tính từ chỉ số điện nước + đơn giá hiện tại.
     */
    public function breakdown(array $inv, array $rates, array $details): array {
        $lines = [];
        $total = (float)$inv['total_amount'];

        if ($details) {
            foreach ($details as $d) {
                $type = 'service';
                if ($d['service_id'] === null) $type = 'room';
                elseif ((int)$d['service_id'] === $rates['elec_id']) $type = 'elec';
                elseif ((int)$d['service_id'] === $rates['water_id']) $type = 'water';
                $lines[] = [
                    'type' => $type,
                    'label' => $type === 'room' ? 'Tiền thuê phòng' : ($d['service_name'] ?? 'Dịch vụ'),
                    'qty' => (float)$d['quantity'],
                    'unit' => $type === 'room' ? 'tháng' : ($d['unit'] ?? ''),
                    'price' => (float)$d['applied_price'],
                    'amount' => (float)$d['subtotal'],
                ];
            }
        } else {
            $eUsed = max(0, (int)($inv['electric_new'] ?? 0) - (int)($inv['electric_old'] ?? 0));
            $wUsed = max(0, (int)($inv['water_new'] ?? 0) - (int)($inv['water_old'] ?? 0));
            $lines[] = ['type' => 'room', 'label' => 'Tiền thuê phòng', 'qty' => 1, 'unit' => 'tháng', 'price' => (float)$inv['room_price'], 'amount' => (float)$inv['room_price']];
            $lines[] = ['type' => 'elec', 'label' => 'Tiền điện', 'qty' => $eUsed, 'unit' => 'kWh', 'price' => $rates['elec'], 'amount' => $eUsed * $rates['elec']];
            $lines[] = ['type' => 'water', 'label' => 'Tiền nước', 'qty' => $wUsed, 'unit' => 'Khối', 'price' => $rates['water'], 'amount' => $wUsed * $rates['water']];
            foreach ($rates['others'] as $o) {
                $lines[] = ['type' => 'service', 'label' => $o['service_name'], 'qty' => 1, 'unit' => $o['unit'], 'price' => (float)$o['unit_price'], 'amount' => (float)$o['unit_price']];
            }
        }

        $sum = array_sum(array_column($lines, 'amount'));
        if (abs($total - $sum) > 0.5) {
            $lines[] = ['type' => 'adjust', 'label' => 'Điều chỉnh / phát sinh khác', 'qty' => 1, 'unit' => '', 'price' => $total - $sum, 'amount' => $total - $sum];
        }

        $by = ['room' => 0, 'elec' => 0, 'water' => 0, 'other' => 0];
        foreach ($lines as $l) {
            $k = in_array($l['type'], ['room', 'elec', 'water'], true) ? $l['type'] : 'other';
            $by[$k] += $l['amount'];
        }
        return ['lines' => $lines, 'room' => $by['room'], 'elec' => $by['elec'], 'water' => $by['water'], 'other' => $by['other']];
    }

    public function getInvoice(int $id): ?array {
        $rows = $this->getInvoices([]);
        foreach ($rows as $r) {
            if ((int)$r['id'] === $id) return $r;
        }
        return null;
    }

    // ===================== THỐNG KÊ =====================
    public function getStats(array $invoices): array {
        $s = ['count' => count($invoices), 'total' => 0, 'paid' => 0, 'paid_count' => 0, 'unpaid' => 0, 'unpaid_count' => 0, 'overdue' => 0, 'overdue_count' => 0];
        foreach ($invoices as $i) {
            $amt = (float)$i['total_amount'];
            $s['total'] += $amt;
            if ($i['status'] === 'paid') { $s['paid'] += $amt; $s['paid_count']++; }
            else {
                $s['unpaid'] += $amt; $s['unpaid_count']++;
                if ($i['is_overdue']) { $s['overdue'] += $amt; $s['overdue_count']++; }
            }
        }
        $s['rate'] = $s['total'] > 0 ? round($s['paid'] / $s['total'] * 100) : 0;
        return $s;
    }

    // ===================== LẬP HÓA ĐƠN =====================
    /** Các bản ghi điện nước chưa có hóa đơn (kèm hợp đồng, khách, đơn giá để xem trước). */
    public function getPendingReadings(?string $period = null): array {
        $sql = "
            SELECT ur.*, r.room_number, r.price AS room_price,
                   (SELECT c.id FROM contracts c WHERE c.room_id = r.id ORDER BY (c.status='active') DESC, c.id DESC LIMIT 1) AS contract_id,
                   (SELECT us.full_name FROM contracts c JOIN users us ON us.id = c.tenant_id
                     WHERE c.room_id = r.id ORDER BY (c.status='active') DESC, c.id DESC LIMIT 1) AS tenant_name
            FROM utilities_readings ur
            JOIN rooms r ON r.id = ur.room_id
            LEFT JOIN invoices i ON i.room_id = ur.room_id AND i.month_year = ur.month_year
            WHERE i.id IS NULL";
        $p = [];
        require_once __DIR__ . '/ReportModel.php';
        $mgr = ReportModel::getManagerBuildingInfo();
        if ($mgr) {
            $sql .= " AND (r.building_id = ? OR r.room_number LIKE ?)";
            $p[] = $mgr['id'];
            $p[] = $mgr['prefix'] . '%';
        }
        if ($period && $period !== 'all') { $sql .= " AND ur.month_year = ?"; $p[] = $period; }
        $sql .= " ORDER BY SUBSTRING(ur.month_year, 4, 4) DESC, SUBSTRING(ur.month_year, 1, 2) DESC, r.room_number";
        $st = $this->db->prepare($sql);
        $st->execute($p);
        return $st->fetchAll();
    }

    public function createFromReading(int $readingId): array {
        $st = $this->db->prepare("SELECT ur.*, r.room_number, r.price AS room_price FROM utilities_readings ur JOIN rooms r ON r.id = ur.room_id WHERE ur.id = ?");
        $st->execute([$readingId]);
        $rd = $st->fetch();
        if (!$rd) return ['ok' => false, 'msg' => 'Không tìm thấy bản ghi chỉ số điện nước.'];

        $chk = $this->db->prepare("SELECT id FROM invoices WHERE room_id = ? AND month_year = ?");
        $chk->execute([$rd['room_id'], $rd['month_year']]);
        if ($chk->fetch()) return ['ok' => false, 'msg' => "Phòng {$rd['room_number']} đã có hóa đơn kỳ {$rd['month_year']} (không được lập trùng)."];

        $cs = $this->db->prepare("SELECT id FROM contracts WHERE room_id = ? ORDER BY (status='active') DESC, id DESC LIMIT 1");
        $cs->execute([$rd['room_id']]);
        $contractId = $cs->fetchColumn();
        if (!$contractId) return ['ok' => false, 'msg' => "Phòng {$rd['room_number']} chưa có hợp đồng để lập hóa đơn."];

        $rates = $this->getRates();
        $eUsed = max(0, (int)$rd['electric_new'] - (int)$rd['electric_old']);
        $wUsed = max(0, (int)$rd['water_new'] - (int)$rd['water_old']);
        $room = (float)$rd['room_price'];
        $eCost = $eUsed * $rates['elec'];
        $wCost = $wUsed * $rates['water'];
        $total = $room + $eCost + $wCost + $rates['others_total'];

        $this->db->beginTransaction();
        try {
            $this->db->prepare("INSERT INTO invoices (room_id, contract_id, month_year, total_amount, status) VALUES (?, ?, ?, ?, 'unpaid')")
                ->execute([$rd['room_id'], $contractId, $rd['month_year'], $total]);
            $invId = (int)$this->db->lastInsertId();
            $ins = $this->db->prepare("INSERT INTO invoice_details (invoice_id, service_id, quantity, applied_price, subtotal) VALUES (?, ?, ?, ?, ?)");
            $ins->execute([$invId, null, 1, $room, $room]);
            if ($rates['elec_id']) $ins->execute([$invId, $rates['elec_id'], $eUsed, $rates['elec'], $eCost]);
            if ($rates['water_id']) $ins->execute([$invId, $rates['water_id'], $wUsed, $rates['water'], $wCost]);
            foreach ($rates['others'] as $o) {
                $ins->execute([$invId, (int)$o['id'], 1, (float)$o['unit_price'], (float)$o['unit_price']]);
            }
            $this->db->commit();
            return ['ok' => true, 'id' => $invId, 'msg' => "Đã lập hóa đơn " . self::code($invId) . " – phòng {$rd['room_number']} kỳ {$rd['month_year']} (" . number_format($total, 0, ',', '.') . " đ)."];
        } catch (Throwable $e) {
            $this->db->rollBack();
            return ['ok' => false, 'msg' => 'Không thể lập hóa đơn: ' . $e->getMessage()];
        }
    }

    public function createAllForPeriod(string $period): array {
        $ok = 0; $fail = 0;
        foreach ($this->getPendingReadings($period) as $rd) {
            $r = $this->createFromReading((int)$rd['id']);
            $r['ok'] ? $ok++ : $fail++;
        }
        if ($ok === 0 && $fail === 0) return ['ok' => false, 'msg' => "Kỳ $period không còn bản ghi điện nước nào chưa lập hóa đơn."];
        return ['ok' => $ok > 0, 'msg' => "Đã lập $ok hóa đơn kỳ $period" . ($fail ? " ($fail bản ghi lỗi do thiếu hợp đồng)." : '.')];
    }

    // ===================== THU TIỀN =====================
    public function markPaid(int $id, string $method, string $note, ?string $paidDate): array {
        $st = $this->db->prepare("SELECT status, month_year FROM invoices WHERE id = ?");
        $st->execute([$id]);
        $inv = $st->fetch();
        if (!$inv) return ['ok' => false, 'msg' => 'Không tìm thấy hóa đơn.'];
        if ($inv['status'] === 'paid') return ['ok' => false, 'msg' => 'Hóa đơn này ĐÃ THU TIỀN và bị khóa, không thể thu lần nữa.'];
        if (!isset(self::METHODS[$method])) $method = 'cash';

        $when = ($paidDate && strtotime($paidDate)) ? date('Y-m-d H:i:s', strtotime($paidDate . ' ' . date('H:i:s'))) : date('Y-m-d H:i:s');
        $this->db->prepare("UPDATE invoices SET status='paid', paid_at=?, payment_method=?, payment_note=? WHERE id=?")
            ->execute([$when, $method, mb_substr($note, 0, 255), $id]);
        return ['ok' => true, 'msg' => 'Đã thu tiền hóa đơn ' . self::code($id) . ' (' . self::METHODS[$method] . '). Hóa đơn được khóa.'];
    }

    public function delete(int $id): array {
        $st = $this->db->prepare("SELECT status FROM invoices WHERE id = ?");
        $st->execute([$id]);
        $status = $st->fetchColumn();
        if ($status === false) return ['ok' => false, 'msg' => 'Không tìm thấy hóa đơn.'];
        if ($status === 'paid') return ['ok' => false, 'msg' => 'Không thể xóa hóa đơn đã thu tiền.'];
        $this->db->prepare("DELETE FROM invoices WHERE id = ?")->execute([$id]);
        return ['ok' => true, 'msg' => 'Đã xóa hóa đơn ' . self::code($id) . '. Bạn có thể lập lại từ bản ghi điện nước.'];
    }

    // ===================== TIỆN ÍCH =====================
    public static function code(int $id): string {
        return 'HD' . str_pad((string)$id, 4, '0', STR_PAD_LEFT);
    }

    /** Hạn thanh toán: ngày 10 của tháng kế tiếp kỳ hóa đơn (kỳ dạng MM-YYYY). */
    public static function dueDate(string $period): string {
        if (!preg_match('/^(\d{2})-(\d{4})$/', $period, $m)) return date('Y-m-d');
        return date('Y-m-d', strtotime("{$m[2]}-{$m[1]}-01 +1 month +9 days"));
    }

    // ===================== CẬP NHẬT TRẠNG THÁI CỌC =====================
    public function updateDeposit(int $contractId, string $stateKey, float $amount): array {
        if ($contractId <= 0) {
            return ['ok' => false, 'msg' => 'Không tìm thấy hợp đồng của phòng này để cập nhật cọc.'];
        }
        $status = 'held';
        $percent = 100;
        if ($stateKey === 'partial_50') {
            $percent = 50;
            $status = 'held';
        } elseif ($stateKey === 'refunded') {
            $percent = 100;
            $status = 'refunded';
        } else {
            $percent = 100;
            $status = 'held';
        }

        $st = $this->db->prepare("UPDATE contracts SET deposit_paid_percent = ?, deposit_status = ?, deposit_amount = ? WHERE id = ?");
        $st->execute([$percent, $status, $amount, $contractId]);
        return ['ok' => true, 'msg' => 'Đã cập nhật trạng thái cọc thành công!'];
    }

    // ===================== KHOẢN CHI CHO BÊN THỨ 3 (ĐIỆN, NƯỚC, DỊCH VỤ) =====================
    public function getThirdPartyFinance(?string $period = null): array {
        $where = "WHERE 1=1";
        $p = [];
        if ($period && $period !== 'all') {
            $where .= " AND month_year = ?";
            $p[] = $period;
        }

        // Lấy sản lượng điện nước tiêu thụ thực tế
        $stU = $this->db->prepare("
            SELECT 
                SUM(GREATEST(0, electric_new - electric_old)) as total_elec,
                SUM(GREATEST(0, water_new - water_old)) as total_water
            FROM utilities_readings
            $where
        ");
        $stU->execute($p);
        $uData = $stU->fetch() ?: ['total_elec' => 0, 'total_water' => 0];

        $totalElec = (float)($uData['total_elec'] ?? 0);
        $totalWater = (float)($uData['total_water'] ?? 0);

        // Số tòa nhà & phòng
        $buildingCount = (int)$this->db->query("SELECT COUNT(*) FROM buildings")->fetchColumn() ?: 3;
        $roomCount = (int)$this->db->query("SELECT COUNT(*) FROM rooms WHERE status = 'rented'")->fetchColumn() ?: 20;

        // Đơn giá bán cho khách
        $rates = $this->getRates();
        $elecCustomerPrice = $rates['elec'];   // 3500
        $waterCustomerPrice = $rates['water']; // 20000

        // Đơn giá mua vào từ bên thứ 3 (EVN, Cấp nước)
        $elecCostRate = 2500.0;    // Giá mua điện EVN
        $waterCostRate = 14000.0;  // Giá mua nước sạch
        $internetCostRate = 350000.0; // Gói cước mạng FPT/Viettel 1 tòa
        $garbageCostRate = 200000.0;  // Phí thu gom rác 1 tòa
        $maintCostRate = 500000.0;    // Bảo trì thang máy

        // Kiểm tra trong bảng third_party_expenses
        $items = [];
        $hasCustom = false;
        try {
            $stExp = $this->db->prepare("SELECT * FROM third_party_expenses $where ORDER BY id ASC");
            $stExp->execute($p);
            $customRows = $stExp->fetchAll();
            if ($customRows) {
                $hasCustom = true;
                foreach ($customRows as $cr) {
                    $items[] = [
                        'id' => (int)$cr['id'],
                        'type' => $cr['expense_type'],
                        'title' => $cr['title'],
                        'provider' => $cr['provider_name'],
                        'qty' => (float)$cr['quantity'],
                        'unit' => $cr['unit'],
                        'cost_price' => (float)$cr['unit_price'],
                        'cost_amount' => (float)$cr['amount'],
                        'customer_amount' => (float)$cr['customer_amount'],
                        'profit' => (float)$cr['customer_amount'] - (float)$cr['amount'],
                        'status' => $cr['status'],
                        'paid_at' => $cr['paid_at'],
                        'notes' => $cr['notes'] ?? '',
                    ];
                }
            }
        } catch (Exception $e) {}

        if (!$hasCustom) {
            $elecCost = $totalElec * $elecCostRate;
            $elecRevenue = $totalElec * $elecCustomerPrice;

            $waterCost = $totalWater * $waterCostRate;
            $waterRevenue = $totalWater * $waterCustomerPrice;

            $netCost = $buildingCount * $internetCostRate;
            $netRevenue = $roomCount * 100000;

            $garbCost = $buildingCount * $garbageCostRate;
            $garbRevenue = $roomCount * 30000;

            $items = [
                [
                    'id' => 1,
                    'type' => 'electric',
                    'title' => 'Tiền Điện Lực Trả Cho EVN',
                    'provider' => 'Tổng Công ty Điện lực Việt Nam (EVN)',
                    'qty' => $totalElec,
                    'unit' => 'kWh',
                    'cost_price' => $elecCostRate,
                    'cost_amount' => $elecCost,
                    'customer_price' => $elecCustomerPrice,
                    'customer_amount' => $elecRevenue,
                    'profit' => $elecRevenue - $elecCost,
                    'status' => 'paid',
                    'paid_at' => date('Y-m-15'),
                    'notes' => 'Thanh toán tự động hóa đơn điện EVN qua ngân hàng',
                ],
                [
                    'id' => 2,
                    'type' => 'water',
                    'title' => 'Tiền Nước Trả Công Ty Cấp Nước Sạch',
                    'provider' => 'Công ty Cổ phần Cấp Nước Sạch Hà Nội',
                    'qty' => $totalWater,
                    'unit' => 'm³',
                    'cost_price' => $waterCostRate,
                    'cost_amount' => $waterCost,
                    'customer_price' => $waterCustomerPrice,
                    'customer_amount' => $waterRevenue,
                    'profit' => $waterRevenue - $waterCost,
                    'status' => 'paid',
                    'paid_at' => date('Y-m-16'),
                    'notes' => 'Chuyển khoản thanh toán theo mã đồng hồ nước',
                ],
                [
                    'id' => 3,
                    'type' => 'internet',
                    'title' => 'Cước Internet Cáp Quang Các Tòa',
                    'provider' => 'Tập đoàn Viễn thông FPT / Viettel',
                    'qty' => $buildingCount,
                    'unit' => 'Đường truyền',
                    'cost_price' => $internetCostRate,
                    'cost_amount' => $netCost,
                    'customer_price' => 100000,
                    'customer_amount' => $netRevenue,
                    'profit' => $netRevenue - $netCost,
                    'status' => 'paid',
                    'paid_at' => date('Y-m-10'),
                    'notes' => '3 đường truyền cáp quang băng thông cao phục vụ toàn bộ nhà trọ',
                ],
                [
                    'id' => 4,
                    'type' => 'garbage',
                    'title' => 'Phí Thu Gom Rác & Vệ Sinh Môi Trường',
                    'provider' => 'Công ty Môi Trường Đô Thị URENCO',
                    'qty' => $buildingCount,
                    'unit' => 'Tòa nhà',
                    'cost_price' => $garbageCostRate,
                    'cost_amount' => $garbCost,
                    'customer_price' => 30000,
                    'customer_amount' => $garbRevenue,
                    'profit' => $garbRevenue - $garbCost,
                    'status' => 'paid',
                    'paid_at' => date('Y-m-05'),
                    'notes' => 'Hợp đồng thu gom rác định kỳ hàng ngày',
                ],
                [
                    'id' => 5,
                    'type' => 'maintenance',
                    'title' => 'Bảo Trì Thang Máy & Hệ Thống Kỹ Thuật',
                    'provider' => 'Công ty Cơ Điện & Thang Máy Thăng Long',
                    'qty' => 1,
                    'unit' => 'Gói tháng',
                    'cost_price' => $maintCostRate,
                    'cost_amount' => $maintCostRate,
                    'customer_price' => 0,
                    'customer_amount' => 0,
                    'profit' => -$maintCostRate,
                    'status' => 'paid',
                    'paid_at' => date('Y-m-12'),
                    'notes' => 'Bảo dưỡng định kỳ thang máy và kiểm định PCCC',
                ],
            ];
        }

        $totalCost = array_sum(array_column($items, 'cost_amount'));
        $totalCustomerServices = array_sum(array_column($items, 'customer_amount'));
        $serviceProfit = $totalCustomerServices - $totalCost;

        return [
            'items' => $items,
            'total_cost' => $totalCost,
            'total_elec_cost' => $items[0]['cost_amount'] ?? 0,
            'total_water_cost' => $items[1]['cost_amount'] ?? 0,
            'total_elec_revenue' => $items[0]['customer_amount'] ?? 0,
            'total_water_revenue' => $items[1]['customer_amount'] ?? 0,
            'elec_profit' => ($items[0]['customer_amount'] ?? 0) - ($items[0]['cost_amount'] ?? 0),
            'water_profit' => ($items[1]['customer_amount'] ?? 0) - ($items[1]['cost_amount'] ?? 0),
            'service_profit' => $serviceProfit,
            'elec_rate_cost' => $elecCostRate,
            'water_rate_cost' => $waterCostRate,
        ];
    }

    public function addThirdPartyExpense(array $post): array {
        $period = trim($post['month_year'] ?? date('m-Y'));
        $type = trim($post['expense_type'] ?? 'other');
        $title = trim($post['title'] ?? 'Chi phí vận hành');
        $provider = trim($post['provider_name'] ?? 'Bên thứ 3');
        $qty = (float)($post['quantity'] ?? 1);
        $unit = trim($post['unit'] ?? 'Lần');
        $unitPrice = (float)($post['unit_price'] ?? 0);
        $amount = (float)($post['amount'] ?? ($qty * $unitPrice));
        $customerAmount = (float)($post['customer_amount'] ?? 0);
        $status = in_array($post['status'] ?? '', ['paid', 'pending'], true) ? $post['status'] : 'paid';
        $paidAt = !empty($post['paid_at']) ? $post['paid_at'] : date('Y-m-d');
        $notes = trim($post['notes'] ?? '');

        if ($amount <= 0 && $qty > 0 && $unitPrice > 0) {
            $amount = $qty * $unitPrice;
        }

        $st = $this->db->prepare("
            INSERT INTO third_party_expenses 
            (month_year, expense_type, title, provider_name, quantity, unit, unit_price, amount, customer_amount, status, paid_at, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $st->execute([$period, $type, $title, $provider, $qty, $unit, $unitPrice, $amount, $customerAmount, $status, $paidAt, $notes]);

        return ['ok' => true, 'msg' => 'Đã ghi nhận khoản chi cho bên thứ 3 (' . $title . ') thành công!'];
    }

}
