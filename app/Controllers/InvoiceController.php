<?php
require_once __DIR__ . '/../Models/InvoiceModel.php';

/**
 * Controller Hóa đơn & Thu tiền.
 * Routes: invoices, invoices/store, invoices/store-all, invoices/pay, invoices/delete, invoices/detail, invoices/export
 */
class InvoiceController {
    private InvoiceModel $model;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->model = new InvoiceModel();
    }

    private function filters(): array {
        $periods = $this->model->getPeriods();
        $period = $_GET['period'] ?? ($periods[0] ?? 'all');
        $status = $_GET['status'] ?? '';
        if (!in_array($status, ['', 'paid', 'unpaid', 'overdue'], true)) $status = '';
        return [
            'period'  => $period,
            'status'  => $status,
            'room_id' => $_GET['room_id'] ?? '',
            'keyword' => trim($_GET['keyword'] ?? ''),
        ];
    }

    private function flash(array $res): void {
        $_SESSION[$res['ok'] ? 'flash_success' : 'flash_error'] = $res['msg'];
    }

    private function back(string $url = 'index.php?route=invoices'): void {
        header('Location: ' . $url);
        exit;
    }

    private function checkPost(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->back();
        $token = $_POST['csrf_token'] ?? '';
        if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            $_SESSION['flash_error'] = 'Phiên làm việc không hợp lệ (CSRF). Vui lòng thử lại.';
            $this->back();
        }
    }

    private function returnUrl(): string {
        $q = $_POST['return_query'] ?? '';
        return 'index.php?route=invoices' . ($q !== '' ? '&' . ltrim($q, '&') : '');
    }

    public function index(): void {
        $filters = $this->filters();
        $invoices = $this->model->getInvoices($filters);
        $stats = $this->model->getStats($invoices);
        $periods = $this->model->getPeriods();
        $pending = $this->model->getPendingReadings();
        $rates = $this->model->getRates();
        $rooms = Database::getConnection()->query("SELECT id, room_number FROM rooms ORDER BY room_number")->fetchAll();
        $methods = InvoiceModel::METHODS;

        $flash_success = $_SESSION['flash_success'] ?? '';
        $flash_error = $_SESSION['flash_error'] ?? '';
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require __DIR__ . '/../views/invoices/index.php';
    }

    public function detail(): void {
        $inv = $this->model->getInvoice((int)($_GET['id'] ?? 0));
        if (!$inv) {
            $_SESSION['flash_error'] = 'Không tìm thấy hóa đơn.';
            $this->back();
        }
        $methods = InvoiceModel::METHODS;
        $flash_success = $_SESSION['flash_success'] ?? '';
        $flash_error = $_SESSION['flash_error'] ?? '';
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);
        require __DIR__ . '/../views/invoices/detail.php';
    }

    public function store(): void {
        $this->checkPost();
        $res = $this->model->createFromReading((int)($_POST['reading_id'] ?? 0));
        $this->flash($res);
        $this->back($this->returnUrl());
    }

    public function storeAll(): void {
        $this->checkPost();
        $this->flash($this->model->createAllForPeriod(trim($_POST['period'] ?? '')));
        $this->back($this->returnUrl());
    }

    public function pay(): void {
        $this->checkPost();
        $res = $this->model->markPaid(
            (int)($_POST['id'] ?? 0),
            $_POST['method'] ?? 'cash',
            trim($_POST['note'] ?? ''),
            $_POST['paid_date'] ?? null
        );
        $this->flash($res);
        $this->back(($_POST['from'] ?? '') === 'detail' ? 'index.php?route=invoices/detail&id=' . (int)$_POST['id'] : $this->returnUrl());
    }

    public function delete(): void {
        $this->checkPost();
        $this->flash($this->model->delete((int)($_POST['id'] ?? 0)));
        $this->back($this->returnUrl());
    }

    public function export(): void {
        $rows = $this->model->getInvoices($this->filters());
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="hoa_don_' . date('Ymd_His') . '.csv"');
        echo "\xEF\xBB\xBF";
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Ma HD', 'Phong', 'Khach thue', 'Ky', 'Tien phong', 'Tien dien', 'Tien nuoc', 'Dich vu khac', 'Tong cong', 'Han thanh toan', 'Trang thai', 'Ngay thu', 'Hinh thuc']);
        foreach ($rows as $r) {
            $b = $r['breakdown'];
            $st = $r['status'] === 'paid' ? 'Da thu' : ($r['is_overdue'] ? 'Qua han' : 'Chua thu');
            fputcsv($out, [
                $r['code'], $r['room_number'], $r['tenant_name'], $r['month_year'],
                round($b['room']), round($b['elec']), round($b['water']), round($b['other']), round($r['total_amount']),
                $r['due_date'], $st, $r['paid_at'] ?? '', InvoiceModel::METHODS[$r['payment_method'] ?? ''] ?? '',
            ]);
        }
        fclose($out);
        exit;
    }
}
