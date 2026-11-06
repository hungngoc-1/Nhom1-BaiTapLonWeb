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
        $this->ensureContractColumns();
    }

    private function ensureContractColumns(): void {
        try {
            $cols = $this->db->query("SHOW COLUMNS FROM contracts")->fetchAll(PDO::FETCH_COLUMN);
            $alter = [];
            if (!in_array('deposit_paid_percent', $cols, true))  $alter[] = "ADD COLUMN `deposit_paid_percent` INT DEFAULT 100";
            if (!in_array('deposit_status', $cols, true))        $alter[] = "ADD COLUMN `deposit_status` ENUM('held', 'refunded', 'forfeited') DEFAULT 'held'";
            if (!in_array('monthly_rent', $cols, true))          $alter[] = "ADD COLUMN `monthly_rent` DECIMAL(10,2) DEFAULT 0";
            if (!in_array('first_month_rent_paid', $cols, true)) $alter[] = "ADD COLUMN `first_month_rent_paid` TINYINT(1) DEFAULT 0";
            if (!in_array('first_month_paid_at', $cols, true))   $alter[] = "ADD COLUMN `first_month_paid_at` DATETIME NULL";
            if (!in_array('first_month_paid_note', $cols, true)) $alter[] = "ADD COLUMN `first_month_paid_note` VARCHAR(255) NULL";
            if (!in_array('unpaid_notice_sent', $cols, true))            $alter[] = "ADD COLUMN `unpaid_notice_sent` TINYINT(1) DEFAULT 0";
            if (!in_array('unpaid_notice_sent_at', $cols, true))         $alter[] = "ADD COLUMN `unpaid_notice_sent_at` DATETIME NULL";
            if (!in_array('termination_verification_case', $cols, true)) $alter[] = "ADD COLUMN `termination_verification_case` VARCHAR(30) DEFAULT 'none'";
            if (!in_array('refund_confirmed_invoice', $cols, true))      $alter[] = "ADD COLUMN `refund_confirmed_invoice` TINYINT(1) DEFAULT 0";
            if (!in_array('unpaid_notice_resolved_at', $cols, true))     $alter[] = "ADD COLUMN `unpaid_notice_resolved_at` DATETIME NULL";
            if ($alter) {
                $this->db->exec("ALTER TABLE `contracts` " . implode(', ', $alter));
            }
        } catch (PDOException $e) {}
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

            // Tính số tiền cọc 50% còn thiếu (nếu khách mới cọc 50%)
            $depositRemainingDue = 0;
            if ($depStatus !== 'refunded' && $depStatus !== 'forfeited' && $depPercent > 0 && $depPercent < 100) {
                $depositRemainingDue = $depAmount * ((100 - $depPercent) / 100);
            }

            if ($depStatus === 'refunded') {
                $row['deposit_label'] = 'Hoàn cọc';
                $row['deposit_badge_class'] = 'bg-secondary-subtle text-secondary border border-secondary';
                $row['deposit_icon'] = 'fa-solid fa-arrow-rotate-left';
                $row['deposit_sub'] = 'Đã hoàn trả ' . number_format($depAmount, 0, ',', '.') . ' đ';
                $row['deposit_key'] = 'refunded';
            } elseif ($depStatus === 'forfeited') {
                $row['deposit_label'] = 'Mất cọc';
                $row['deposit_badge_class'] = 'bg-danger-subtle text-danger border border-danger';
                $row['deposit_icon'] = 'fa-solid fa-ban';
                $row['deposit_sub'] = 'Tịch thu do bỏ phòng trước hạn';
                $row['deposit_key'] = 'forfeited';
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

            // Tiền phòng, điện, nước, dịch vụ cơ sở
            $baseServicesTotal = (float)$row['breakdown']['room'] + (float)$row['breakdown']['elec'] + (float)$row['breakdown']['water'] + (float)$row['breakdown']['other'];

            // Nếu khách đã đóng cọc 100% (hoặc hoàn cọc): Tổng cộng KHÔNG tính tiền cọc vào
            // Nếu khách mới đóng 50% cọc: Tổng cộng TÍNH CẢ số tiền cọc 50% còn lại vào đó
            if ($depositRemainingDue > 0) {
                $row['total_amount'] = $baseServicesTotal + $depositRemainingDue;
                $row['deposit_remaining_due'] = $depositRemainingDue;
            } else {
                $row['total_amount'] = $baseServicesTotal;
                $row['deposit_remaining_due'] = 0;
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

    // ===================== FETCH API PREVIEW & LẬP HÓA ĐƠN THỦ CÔNG =====================
    public function previewInvoiceJson(int $roomId, string $monthYear): array {
        if ($roomId <= 0 || $monthYear === '') {
            return ['ok' => false, 'msg' => 'Vui lòng chọn phòng trọ và kỳ hóa đơn!'];
        }

        // 1. Kiểm tra phòng tồn tại
        $stRoom = $this->db->prepare("SELECT id, room_number, price FROM rooms WHERE id = ?");
        $stRoom->execute([$roomId]);
        $room = $stRoom->fetch(PDO::FETCH_ASSOC);
        if (!$room) {
            return ['ok' => false, 'msg' => 'Không tìm thấy phòng trọ.'];
        }

        // 2. Kiểm tra hóa đơn đã tồn tại chưa (Quy tắc D & Kiểm thử 6: Không lập trùng hóa đơn)
        $chkInv = $this->db->prepare("SELECT id, status, total_amount FROM invoices WHERE room_id = ? AND month_year = ?");
        $chkInv->execute([$roomId, $monthYear]);
        $existingInv = $chkInv->fetch(PDO::FETCH_ASSOC);
        if ($existingInv) {
            return [
                'ok' => false,
                'is_duplicate' => true,
                'invoice_id' => (int)$existingInv['id'],
                'invoice_code' => self::code((int)$existingInv['id']),
                'room_number' => $room['room_number'],
                'month_year' => $monthYear,
                'msg' => "Phòng {$room['room_number']} đã được lập hóa đơn kỳ {$monthYear} (" . self::code((int)$existingInv['id']) . ")! Không được phép lập trùng."
            ];
        }

        // 3. Kiểm tra hợp đồng của phòng
        $stContract = $this->db->prepare("
            SELECT c.*, u.full_name AS tenant_name, u.phone AS tenant_phone
            FROM contracts c
            JOIN users u ON u.id = c.tenant_id
            WHERE c.room_id = ? AND c.status = 'active'
            ORDER BY c.id DESC LIMIT 1
        ");
        $stContract->execute([$roomId]);
        $contract = $stContract->fetch(PDO::FETCH_ASSOC);

        if (!$contract) {
            return [
                'ok' => false,
                'has_contract' => false,
                'room_number' => $room['room_number'],
                'month_year' => $monthYear,
                'msg' => "Phòng {$room['room_number']} hiện chưa có hợp đồng hiệu lực! Vui lòng lập hợp đồng trước."
            ];
        }

        // 4. Lấy chỉ số điện nước của phòng trong kỳ này
        $stRead = $this->db->prepare("SELECT * FROM utilities_readings WHERE room_id = ? AND month_year = ?");
        $stRead->execute([$roomId, $monthYear]);
        $reading = $stRead->fetch(PDO::FETCH_ASSOC);

        $hasReading = (bool)$reading;
        $rates = $this->getRates();

        if ($reading) {
            $eOld = (int)$reading['electric_old'];
            $eNew = (int)$reading['electric_new'];
            $wOld = (int)$reading['water_old'];
            $wNew = (int)$reading['water_new'];
        } else {
            $stPrev = $this->db->prepare("
                SELECT electric_new, water_new FROM utilities_readings 
                WHERE room_id = ? 
                ORDER BY SUBSTRING(month_year, 4, 4) DESC, SUBSTRING(month_year, 1, 2) DESC, id DESC LIMIT 1
            ");
            $stPrev->execute([$roomId]);
            $prev = $stPrev->fetch(PDO::FETCH_ASSOC);
            $eOld = $prev ? (int)$prev['electric_new'] : 0;
            $eNew = $eOld;
            $wOld = $prev ? (int)$prev['water_new'] : 0;
            $wNew = $wOld;
        }

        $eUsage = max(0, $eNew - $eOld);
        $wUsage = max(0, $wNew - $wOld);
        $eCost = $eUsage * $rates['elec'];
        $wCost = $wUsage * $rates['water'];

        $roomPrice = (float)$room['price'];

        $servicesList = [];
        $servicesTotal = 0;
        foreach ($rates['others'] as $o) {
            $price = (float)$o['unit_price'];
            $servicesList[] = [
                'name' => $o['service_name'],
                'price' => $price,
                'unit' => $o['unit']
            ];
            $servicesTotal += $price;
        }

        $depositRemaining = 0;
        $depositStatus = $contract['deposit_status'] ?? 'held';
        $depositPercent = (int)($contract['deposit_paid_percent'] ?? 100);
        $depositNote = '';

        if ($depositPercent === 50 && $depositStatus === 'held') {
            $depositRemaining = round((float)$contract['monthly_rent'] * 0.5, 2);
            $depositNote = 'Bao gồm thu bổ sung 50% tiền cọc còn thiếu (' . number_format($depositRemaining, 0, ',', '.') . ' đ)';
        } elseif ($depositStatus === 'refunded') {
            $depositNote = 'Hợp đồng đã hoàn cọc (không tính cọc)';
        } elseif ($depositStatus === 'forfeited') {
            $depositNote = 'Mất cọc do bỏ phòng trước hạn (không hoàn cọc)';
        } else {
            $depositNote = 'Đã hoàn thành 100% tiền cọc';
        }

        $totalAmount = $roomPrice + $eCost + $wCost + $servicesTotal + $depositRemaining;

        return [
            'ok' => true,
            'is_duplicate' => false,
            'has_contract' => true,
            'has_reading' => $hasReading,
            'reading_id' => $reading ? (int)$reading['id'] : null,
            'room_id' => $roomId,
            'room_number' => $room['room_number'],
            'tenant_name' => $contract['tenant_name'],
            'tenant_phone' => $contract['tenant_phone'],
            'contract_id' => (int)$contract['id'],
            'contract_code' => $contract['contract_code'],
            'month_year' => $monthYear,
            'room_price' => $roomPrice,
            'electric' => [
                'old' => $eOld,
                'new' => $eNew,
                'usage' => $eUsage,
                'rate' => $rates['elec'],
                'amount' => $eCost
            ],
            'water' => [
                'old' => $wOld,
                'new' => $wNew,
                'usage' => $wUsage,
                'rate' => $rates['water'],
                'amount' => $wCost
            ],
            'services' => $servicesList,
            'services_total' => $servicesTotal,
            'deposit_remaining' => $depositRemaining,
            'deposit_note' => $depositNote,
            'total_amount' => $totalAmount
        ];
    }

    public function createInvoiceManual(int $roomId, string $monthYear, ?string $dueDate, string $notes): array {
        $preview = $this->previewInvoiceJson($roomId, $monthYear);
        if (!$preview['ok']) {
            return ['ok' => false, 'msg' => $preview['msg'] ?? 'Không thể lập hóa đơn!'];
        }

        $rates = $this->getRates();
        $roomPrice = (float)$preview['room_price'];
        $eUsed = (int)$preview['electric']['usage'];
        $eCost = (float)$preview['electric']['amount'];
        $wUsed = (int)$preview['water']['usage'];
        $wCost = (float)$preview['water']['amount'];
        $total = (float)$preview['total_amount'];
        $contractId = (int)$preview['contract_id'];
        $dueDateVal = !empty($dueDate) ? $dueDate : self::calcDueDate($monthYear);

        $this->db->beginTransaction();
        try {
            if (empty($preview['reading_id'])) {
                $stInsUr = $this->db->prepare("
                    INSERT INTO utilities_readings (room_id, month_year, electric_old, electric_new, water_old, water_new)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stInsUr->execute([
                    $roomId,
                    $monthYear,
                    $preview['electric']['old'],
                    $preview['electric']['new'],
                    $preview['water']['old'],
                    $preview['water']['new']
                ]);
            }

            $stInv = $this->db->prepare("
                INSERT INTO invoices (room_id, contract_id, month_year, total_amount, status, due_date, payment_note)
                VALUES (?, ?, ?, ?, 'unpaid', ?, ?)
            ");
            $stInv->execute([$roomId, $contractId, $monthYear, $total, $dueDateVal, $notes]);
            $invId = (int)$this->db->lastInsertId();

            $ins = $this->db->prepare("INSERT INTO invoice_details (invoice_id, service_id, quantity, applied_price, subtotal) VALUES (?, ?, ?, ?, ?)");
            $ins->execute([$invId, null, 1, $roomPrice, $roomPrice]);
            if ($rates['elec_id']) $ins->execute([$invId, $rates['elec_id'], $eUsed, $rates['elec'], $eCost]);
            if ($rates['water_id']) $ins->execute([$invId, $rates['water_id'], $wUsed, $rates['water'], $wCost]);
            foreach ($rates['others'] as $o) {
                $ins->execute([$invId, (int)$o['id'], 1, (float)$o['unit_price'], (float)$o['unit_price']]);
            }
            if ($preview['deposit_remaining'] > 0) {
                $ins->execute([$invId, null, 1, $preview['deposit_remaining'], $preview['deposit_remaining']]);
            }

            $this->db->commit();
            return [
                'ok' => true,
                'id' => $invId,
                'msg' => "Đã lập thành công hóa đơn " . self::code($invId) . " cho phòng {$preview['room_number']} kỳ {$monthYear} (" . number_format($total, 0, ',', '.') . " đ)!"
            ];
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

        // Cập nhật lại tổng tiền hóa đơn:
        // Nếu 50% => tổng cộng cộng thêm tiền cọc còn thiếu
        // Nếu 100% (hoặc hoàn cọc) => tổng cộng KHÔNG tính tiền cọc
        $depRemaining = ($status !== 'refunded' && $percent < 100) ? ($amount * (100 - $percent) / 100) : 0;

        $invs = $this->db->prepare("SELECT id FROM invoices WHERE contract_id = ?");
        $invs->execute([$contractId]);
        $invoiceIds = $invs->fetchAll(PDO::FETCH_COLUMN);

        foreach ($invoiceIds as $invId) {
            $stSum = $this->db->prepare("SELECT COALESCE(SUM(subtotal), 0) FROM invoice_details WHERE invoice_id = ?");
            $stSum->execute([$invId]);
            $baseTotal = (float)$stSum->fetchColumn();

            if ($baseTotal > 0) {
                $newTotal = $baseTotal + $depRemaining;
                $this->db->prepare("UPDATE invoices SET total_amount = ? WHERE id = ?")->execute([$newTotal, $invId]);
            }
        }

        return ['ok' => true, 'msg' => 'Đã cập nhật trạng thái cọc thành công! Tổng cộng hóa đơn đã được tự động tính toán lại.'];
    }

    // ===================== CHỈNH SỬA THÔNG TIN HÓA ĐƠN =====================
    public function updateInvoice(int $id, array $data): array {
        $inv = $this->getInvoice($id);
        if (!$inv) {
            return ['ok' => false, 'msg' => 'Không tìm thấy hóa đơn cần cập nhật.'];
        }
        // QUY TẮC BẮT BUỘC ĐỀ BÀI (Quy tắc D & Kiểm thử 8):
        // Hóa đơn đã thanh toán không được sửa trực tiếp; từ chối sửa hóa đơn đã thanh toán!
        if ($inv['status'] === 'paid') {
            return ['ok' => false, 'msg' => 'Từ chối cập nhật: Hóa đơn ' . self::code($id) . ' đã thanh toán thành công, không được phép chỉnh sửa!'];
        }
        $dueDate = !empty($data['due_date']) ? $data['due_date'] : $inv['due_date'];
        $status = in_array($data['status'] ?? '', ['paid', 'unpaid'], true) ? $data['status'] : $inv['status'];
        $note = trim($data['payment_note'] ?? '');
        $method = trim($data['payment_method'] ?? '');
        $paidAt = ($status === 'paid') ? (!empty($data['paid_at']) ? $data['paid_at'] : ($inv['paid_at'] ?: date('Y-m-d H:i:s'))) : null;
        if ($status === 'unpaid') {
            $method = null;
        }

        $st = $this->db->prepare("
            UPDATE invoices 
            SET due_date = ?, status = ?, payment_note = ?, payment_method = ?, paid_at = ?
            WHERE id = ?
        ");
        $st->execute([$dueDate, $status, $note, $method, $paidAt, $id]);
        return ['ok' => true, 'msg' => 'Đã cập nhật thông tin hóa đơn ' . self::code($id) . ' thành công!'];
    }

    // ===================== QUẢN LÝ TIỀN CỌC & TIỀN PHÒNG THÁNG ĐẦU (BẢNG 1) =====================
    public function getDepositAndFirstMonthContracts(array $f = []): array {
        $this->ensureContractColumns();
        $sql = "
            SELECT c.*, r.room_number, r.price AS room_price, r.building_id,
                   u.full_name AS tenant_name, u.phone AS tenant_phone, u.email AS tenant_email
            FROM contracts c
            JOIN rooms r ON r.id = c.room_id
            LEFT JOIN users u ON u.id = c.tenant_id
            WHERE c.status = 'active'";
        $p = [];
        require_once __DIR__ . '/ReportModel.php';
        $mgr = ReportModel::getManagerBuildingInfo();
        if ($mgr) {
            $sql .= " AND (r.building_id = ? OR r.room_number LIKE ?)";
            $p[] = $mgr['id'];
            $p[] = $mgr['prefix'] . '%';
        }
        if (!empty($f['room_id'])) {
            $sql .= " AND c.room_id = ?";
            $p[] = (int)$f['room_id'];
        }
        if (!empty($f['deposit_filter'])) {
            if ($f['deposit_filter'] === 'partial_50') {
                $sql .= " AND c.deposit_paid_percent = 50 AND c.deposit_status != 'refunded'";
            } elseif ($f['deposit_filter'] === 'completed') {
                $sql .= " AND c.deposit_paid_percent = 100 AND c.deposit_status != 'refunded'";
            } elseif ($f['deposit_filter'] === 'refunded') {
                $sql .= " AND c.deposit_status = 'refunded'";
            }
        }
        if (isset($f['first_month_filter']) && $f['first_month_filter'] !== '') {
            $sql .= " AND c.first_month_rent_paid = ?";
            $p[] = (int)$f['first_month_filter'];
        }
        if (!empty($f['keyword'])) {
            $sql .= " AND (r.room_number LIKE ? OR u.full_name LIKE ? OR c.contract_code LIKE ?)";
            $kw = '%' . $f['keyword'] . '%';
            array_push($p, $kw, $kw, $kw);
        }
        $sql .= " ORDER BY r.room_number ASC";
        $st = $this->db->prepare($sql);
        $st->execute($p);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        $out = [];
        foreach ($rows as $row) {
            $depAmount = (float)($row['deposit_amount'] ?? 0);
            $depPercent = (int)($row['deposit_paid_percent'] ?? 100);
            $depStatus = (string)($row['deposit_status'] ?? 'held');

            if ($depStatus === 'refunded') {
                $row['deposit_key'] = 'refunded';
                $row['deposit_label'] = 'Hoàn cọc';
                $row['deposit_badge_class'] = 'bg-secondary-subtle text-secondary border border-secondary';
                $row['deposit_icon'] = 'fa-solid fa-arrow-rotate-left';
                $row['deposit_paid_amount'] = 0;
                $row['deposit_remaining_amount'] = 0;
                $row['deposit_sub'] = 'Đã hoàn ' . number_format($depAmount, 0, ',', '.') . ' đ';
            } elseif ($depStatus === 'forfeited') {
                $row['deposit_key'] = 'forfeited';
                $row['deposit_label'] = 'Mất cọc';
                $row['deposit_badge_class'] = 'bg-danger-subtle text-danger border border-danger';
                $row['deposit_icon'] = 'fa-solid fa-ban';
                $row['deposit_paid_amount'] = $depAmount;
                $row['deposit_remaining_amount'] = 0;
                $row['deposit_sub'] = 'Tịch thu do bỏ phòng trước hạn';
            } elseif ($depPercent == 50 || ($depPercent > 0 && $depPercent < 100)) {
                $row['deposit_key'] = 'partial_50';
                $row['deposit_label'] = 'Đã cọc 50%';
                $row['deposit_badge_class'] = 'bg-warning-subtle text-warning-emphasis border border-warning';
                $row['deposit_icon'] = 'fa-solid fa-clock-rotate-left';
                $row['deposit_paid_amount'] = $depAmount * 0.5;
                $row['deposit_remaining_amount'] = $depAmount * 0.5;
                $row['deposit_sub'] = number_format($depAmount * 0.5, 0, ',', '.') . ' / ' . number_format($depAmount, 0, ',', '.') . ' đ';
            } else {
                $row['deposit_key'] = 'completed';
                $row['deposit_label'] = 'Hoàn thành cọc (100%)';
                $row['deposit_badge_class'] = 'bg-success-subtle text-success border border-success-subtle';
                $row['deposit_icon'] = 'fa-solid fa-circle-check';
                $row['deposit_paid_amount'] = $depAmount;
                $row['deposit_remaining_amount'] = 0;
                $row['deposit_sub'] = number_format($depAmount, 0, ',', '.') . ' đ';
            }

            // Tiền phòng tháng đầu & kiểm tra chưa đóng trong 5 ngày -> ngày thứ 6 trở đi
            $rent = (float)($row['monthly_rent'] > 0 ? $row['monthly_rent'] : ($row['room_price'] ?? 0));
            $row['first_month_rent'] = $rent;
            $row['first_month_rent_paid'] = (int)($row['first_month_rent_paid'] ?? 0);

            $startTs = strtotime($row['start_date']);
            $todayTs = strtotime(date('Y-m-d'));
            $daysDiff = max(1, (int)floor(($todayTs - $startTs) / 86400) + 1);
            $row['days_from_start'] = $daysDiff;
            $row['is_first_month_overdue'] = ($row['first_month_rent_paid'] == 0 && $daysDiff >= 6);
            $row['unpaid_notice_sent'] = (int)($row['unpaid_notice_sent'] ?? 0);
            $row['termination_verification_case'] = (string)($row['termination_verification_case'] ?? 'none');
            $row['refund_confirmed_invoice'] = (int)($row['refund_confirmed_invoice'] ?? 0);

            if ($row['first_month_rent_paid'] == 1) {
                $row['first_month_label'] = 'Đã thu';
                $row['first_month_badge_class'] = 'bg-success-subtle text-success border border-success-subtle';
                $row['first_month_icon'] = 'fa-solid fa-circle-check';
                $row['first_month_note_text'] = $row['first_month_paid_at'] ? date('d/m/Y', strtotime($row['first_month_paid_at'])) : 'Đã thu';
            } elseif ($row['is_first_month_overdue']) {
                $row['first_month_label'] = 'Chưa thanh toán (Quá hạn 5 ngày)';
                $row['first_month_badge_class'] = 'bg-danger text-white border border-danger';
                $row['first_month_icon'] = 'fa-solid fa-triangle-exclamation';
                $row['first_month_note_text'] = 'Ngày thứ ' . $daysDiff . ' chưa nộp tiền';
            } else {
                $row['first_month_label'] = 'Chưa thu';
                $row['first_month_badge_class'] = 'bg-warning-subtle text-warning-emphasis border border-warning';
                $row['first_month_icon'] = 'fa-regular fa-clock';
                $row['first_month_note_text'] = 'Trong hạn 5 ngày đầu (Ngày ' . $daysDiff . '/5)';
            }

            $out[] = $row;
        }
        return $out;
    }

    public function getDepositAndFirstMonthStats(array $contracts): array {
        $stats = [
            'total_contracts' => count($contracts),
            'total_deposit_held' => 0.0,
            'count_deposit_100' => 0,
            'count_deposit_50' => 0,
            'count_deposit_refunded' => 0,
            'count_first_month_paid' => 0,
            'count_first_month_unpaid' => 0,
            'total_first_month_paid' => 0.0,
            'total_first_month_unpaid' => 0.0,
        ];
        foreach ($contracts as $c) {
            $stats['total_deposit_held'] += (float)$c['deposit_paid_amount'];
            if ($c['deposit_key'] === 'completed') $stats['count_deposit_100']++;
            elseif ($c['deposit_key'] === 'partial_50') $stats['count_deposit_50']++;
            elseif ($c['deposit_key'] === 'refunded') $stats['count_deposit_refunded']++;

            if ($c['first_month_rent_paid'] == 1) {
                $stats['count_first_month_paid']++;
                $stats['total_first_month_paid'] += (float)$c['first_month_rent'];
            } else {
                $stats['count_first_month_unpaid']++;
                $stats['total_first_month_unpaid'] += (float)$c['first_month_rent'];
            }
        }
        return $stats;
    }

    public function updateFirstMonthRent(int $contractId, int $isPaid, ?string $note = null): array {
        if ($contractId <= 0) return ['ok' => false, 'msg' => 'Không tìm thấy hợp đồng để cập nhật.'];
        try {
            $paidAt = $isPaid ? date('Y-m-d H:i:s') : null;
            $st = $this->db->prepare("UPDATE contracts SET first_month_rent_paid = ?, first_month_paid_at = ?, first_month_paid_note = ? WHERE id = ?");
            $st->execute([$isPaid, $paidAt, $note, $contractId]);
            return ['ok' => true, 'msg' => $isPaid ? 'Đã xác nhận thu tiền phòng tháng đầu thành công!' : 'Đã chuyển tiền phòng tháng đầu về trạng thái Chưa thu.'];
        } catch (PDOException $e) {
            return ['ok' => false, 'msg' => 'Lỗi cập nhật: ' . $e->getMessage()];
        }
    }

    public function sendContractVerification(int $contractId): array {
        if ($contractId <= 0) return ['ok' => false, 'msg' => 'Không tìm thấy hợp đồng.'];
        $c = $this->db->query("SELECT c.*, r.room_number, u.full_name FROM contracts c JOIN rooms r ON r.id = c.room_id LEFT JOIN users u ON u.id = c.tenant_id WHERE c.id = $contractId")->fetch(PDO::FETCH_ASSOC);
        if (!$c) return ['ok' => false, 'msg' => 'Hợp đồng không tồn tại.'];

        $st = $this->db->prepare("UPDATE contracts SET unpaid_notice_sent = 1, unpaid_notice_sent_at = NOW() WHERE id = ?");
        $st->execute([$contractId]);
        return ['ok' => true, 'msg' => "Đã gửi thông báo quá hạn tiền phòng tháng đầu của phòng {$c['room_number']} ({$c['full_name']}) sang trang Hợp đồng để xác minh lý do!"];
    }

    public function confirmRefundInvoice(int $contractId): array {
        if ($contractId <= 0) return ['ok' => false, 'msg' => 'Không tìm thấy hợp đồng.'];
        $st = $this->db->prepare("UPDATE contracts SET refund_confirmed_invoice = 1 WHERE id = ?");
        $st->execute([$contractId]);
        return ['ok' => true, 'msg' => 'Đã xác nhận đối soát hoàn cọc thành công!'];
    }

    public function getRefundNotifications(): array {
        $sql = "SELECT c.*, r.room_number, u.full_name AS tenant_name, u.phone AS tenant_phone 
                FROM contracts c 
                JOIN rooms r ON r.id = c.room_id 
                LEFT JOIN users u ON u.id = c.tenant_id 
                WHERE (c.termination_verification_case = 'expired_no_renew' AND c.refund_confirmed_invoice = 0)
                   OR (c.termination_verification_case = 'early_move_out' AND c.refund_confirmed_invoice = 0)";
        require_once __DIR__ . '/ReportModel.php';
        $mgr = ReportModel::getManagerBuildingInfo();
        if ($mgr) {
            $sql .= " AND (r.building_id = {$mgr['id']} OR r.room_number LIKE '{$mgr['prefix']}%')";
        }
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
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
