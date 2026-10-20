<?php include __DIR__ . '/../layouts/header.php'; ?>

<main class="container-fluid p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Bảng Điều Khiển & Thống Kê Công Nợ</h4>
            <p class="text-muted small mb-0">Theo dõi tình trạng phòng, biểu đồ doanh thu và các hóa đơn chưa thanh toán</p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-sm-0">
            <a href="?route=login" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-key me-1"></i> Mở trang Login UI
            </a>
        </div>
    </div>

    <!-- 4 Thẻ thống kê tổng quan -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card bg-white p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                        <i class="bi bi-houses-fill fs-3"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Tổng số phòng</div>
                        <h3 class="fw-bold mb-0"><?= (int)$stats['total_rooms'] ?> <small class="fs-6 fw-normal">phòng</small></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card bg-white p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 text-success rounded-3 p-3 me-3">
                        <i class="bi bi-person-check-fill fs-3"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Phòng đang thuê</div>
                        <h3 class="fw-bold mb-0 text-success"><?= (int)$stats['rented_rooms'] ?> <small class="fs-6 fw-normal">phòng</small></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card bg-white p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 text-info rounded-3 p-3 me-3">
                        <i class="bi bi-door-open-fill fs-3"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Phòng còn trống</div>
                        <h3 class="fw-bold mb-0 text-info"><?= (int)$stats['empty_rooms'] ?> <small class="fs-6 fw-normal">phòng</small></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card bg-white p-3">
                <div class="d-flex align-items-center">
                    <div class="bg-danger bg-opacity-10 text-danger rounded-3 p-3 me-3">
                        <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Công nợ (<?= (int)$stats['unpaid_invoices'] ?> hóa đơn)</div>
                        <h4 class="fw-bold mb-0 text-danger"><?= number_format($stats['total_debt'], 0, ',', '.') ?>đ</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Biểu đồ -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-8">
            <div class="card stat-card bg-white p-3 h-100">
                <h6 class="fw-bold mb-3"><i class="bi bi-bar-chart-line text-primary me-2"></i>Biểu đồ Doanh thu & Công nợ 6 tháng gần nhất</h6>
                <canvas id="revenueChart" height="110"></canvas>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card stat-card bg-white p-3 h-100">
                <h6 class="fw-bold mb-3"><i class="bi bi-pie-chart text-success me-2"></i>Tỷ lệ lấp đầy phòng trọ</h6>
                <div class="d-flex justify-content-center align-items-center" style="height: 220px;">
                    <canvas id="roomStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng công nợ & Lọc -->
    <div class="card stat-card bg-white p-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <h6 class="fw-bold mb-0"><i class="bi bi-table text-danger me-2"></i>Danh sách Hóa đơn & Thống kê Công nợ</h6>
            
            <form method="GET" action="index.php" class="d-flex flex-wrap gap-2">
                <input type="hidden" name="route" value="dashboard">
                <input type="text" name="keyword" class="form-control form-control-sm" style="width: 200px;"
                       placeholder="Tìm phòng, tên khách..." 
                       value="<?= htmlspecialchars($_GET['keyword'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                
                <select name="status" class="form-select form-select-sm" style="width: 170px;">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="unpaid" <?= ($_GET['status'] ?? '') === 'unpaid' ? 'selected' : '' ?>>Chưa thanh toán</option>
                    <option value="overdue" <?= ($_GET['status'] ?? '') === 'overdue' ? 'selected' : '' ?>>Quá hạn</option>
                    <option value="paid" <?= ($_GET['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Đã thanh toán</option>
                </select>

                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i>Lọc</button>
                <a href="?route=dashboard" class="btn btn-outline-secondary btn-sm">Đặt lại</a>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Mã HĐ</th>
                        <th>Phòng</th>
                        <th>Người thuê</th>
                        <th>Kỳ thu</th>
                        <th class="text-end">Tiền phòng</th>
                        <th class="text-end">Điện + Nước</th>
                        <th class="text-end">Tổng cộng</th>
                        <th class="text-center">Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($debtList)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Không tìm thấy hóa đơn nào khớp với bộ lọc.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($debtList as $item): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($item['invoice_code'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($item['room'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td><?= htmlspecialchars($item['tenant'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($item['month'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-end"><?= number_format($item['room_price'], 0, ',', '.') ?>đ</td>
                                <td class="text-end"><?= number_format($item['electric'] + $item['water'], 0, ',', '.') ?>đ</td>
                                <td class="text-end fw-bold text-danger"><?= number_format($item['total'], 0, ',', '.') ?>đ</td>
                                <td class="text-center">
                                    <?php if ($item['status'] === 'paid'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Đã thu</span>
                                    <?php elseif ($item['status'] === 'overdue'): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Quá hạn</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Chưa thu</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const labels = <?= json_encode($chartData['labels']) ?>;
    const revenues = <?= json_encode($chartData['revenues']) ?>;
    const debts = <?= json_encode($chartData['debts']) ?>;

    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'Doanh thu đã thu (VNĐ)', data: revenues, backgroundColor: '#3b82f6', borderRadius: 6 },
                { label: 'Công nợ tồn đọng (VNĐ)', data: debts, backgroundColor: '#ef4444', borderRadius: 6 }
            ]
        },
        options: { responsive: true, plugins: { legend: { position: 'top' } } }
    });

    new Chart(document.getElementById('roomStatusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Đang thuê (<?= (int)$stats['rented_rooms'] ?>)', 'Còn trống (<?= (int)$stats['empty_rooms'] ?>)'],
            datasets: [{
                data: [<?= (int)$stats['rented_rooms'] ?>, <?= (int)$stats['empty_rooms'] ?>],
                backgroundColor: ['#10b981', '#0dcaf0']
            }]
        }
    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>