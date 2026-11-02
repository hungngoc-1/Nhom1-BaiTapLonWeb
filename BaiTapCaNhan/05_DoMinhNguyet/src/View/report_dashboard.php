<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo Chuyên sâu - StayMate</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --bg-body: #f8fafc; --radius: 24px; }
        body { background-color: var(--bg-body); font-family: 'Nunito', sans-serif; color: #1e293b; }
        
        /* Navbar giống ảnh gốc */
        .navbar-custom { background: #fff; box-shadow: 0 2px 15px rgba(0,0,0,0.03); border-bottom: 1px solid #f1f5f9; }
        
        /* Thẻ chỉ số lấy cảm hứng từ hình ảnh Tòa B */
        .metric-card { 
            border-radius: var(--radius); border: none; padding: 25px; position: relative; overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.03); transition: 0.3s;
        }
        .metric-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.06); }
        .metric-card::after { 
            content: ''; position: absolute; top: -20px; right: -20px; width: 120px; height: 120px; 
            border-radius: 50%; opacity: 0.2; 
        }
        
        .card-green { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); }
        .card-green::after { background: #22c55e; }
        .card-blue { background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); }
        .card-blue::after { background: #0ea5e9; }
        .card-yellow { background: linear-gradient(135deg, #fef9c3 0%, #fef08a 100%); }
        .card-yellow::after { background: #eab308; }
        .card-purple { background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%); }
        .card-purple::after { background: #a855f7; }

        .chart-box { background: #fff; border-radius: var(--radius); padding: 25px; box-shadow: 0 10px 25px rgba(0,0,0,0.02); border: 1px solid #f1f5f9; }
        .table-custom th { background: #f8fafc; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; color: #64748b; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top mb-4 py-3">
        <div class="container-fluid px-4 px-xl-5 d-flex justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <a href="index.php?route=dashboard" class="text-decoration-none text-muted fw-bold text-sm bg-light px-3 py-2 rounded-pill hover-bg">
                    <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
                </a>
                <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-chart-pie me-2 text-primary"></i>Phân tích & Báo cáo Chuyên sâu</h5>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill"><i class="fa-regular fa-calendar me-2"></i>Thống kê 6 tháng gần nhất</span>
            </div>
        </div>
    </nav>

    <div class="container-fluid px-4 px-xl-5 pb-5">
        <!-- 4 Thẻ Chỉ Số (Màu sắc chuẩn StayMate Emerald) -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="metric-card card-green h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h6 class="fw-bold text-success text-uppercase opacity-75 mb-0"><i class="fa-solid fa-house-circle-check me-2"></i>Tỷ Lệ Lấp Đầy</h6>
                        <div class="bg-white text-success rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;"><i class="fa-solid fa-percent"></i></div>
                    </div>
                    <h2 class="fw-bold text-success mb-1"><?= $occupancyRate ?>%</h2>
                    <span class="text-success fw-bold opacity-75 text-sm"><?= $rentedRooms ?> / <?= $totalRooms ?> phòng đang thuê</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card card-blue h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h6 class="fw-bold text-primary text-uppercase opacity-75 mb-0"><i class="fa-solid fa-wallet me-2"></i>Thực Thu Toàn Kỳ</h6>
                        <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;"><i class="fa-solid fa-check"></i></div>
                    </div>
                    <h2 class="fw-bold text-primary mb-1"><?= number_format($totalRevenue) ?> đ</h2>
                    <span class="text-primary fw-bold opacity-75 text-sm">Doanh thu đã vào két</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card card-yellow h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h6 class="fw-bold text-warning text-uppercase opacity-75 mb-0" style="color: #b45309 !important;"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Công Nợ Tồn Đọng</h6>
                        <div class="bg-white text-warning rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    </div>
                    <h2 class="fw-bold mb-1" style="color: #b45309;"><?= number_format($totalDebt) ?> đ</h2>
                    <span class="fw-bold opacity-75 text-sm" style="color: #b45309;">Tiền khách chưa thanh toán</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card card-purple h-100">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h6 class="fw-bold text-purple text-uppercase opacity-75 mb-0" style="color: #6b21a8 !important;"><i class="fa-solid fa-users me-2"></i>Lượt Đóng Trễ</h6>
                        <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px; color: #a855f7;"><i class="fa-solid fa-clock-rotate-left"></i></div>
                    </div>
                    <h2 class="fw-bold mb-1" style="color: #6b21a8;"><?= count($topDebtors) ?> khách</h2>
                    <span class="fw-bold opacity-75 text-sm" style="color: #6b21a8;">Cần nhắc nhở thu tiền</span>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- Biểu đồ biến động (Bar + Line) -->
            <div class="col-lg-8">
                <div class="chart-box h-100">
                    <h5 class="fw-bold mb-4"><i class="fa-solid fa-chart-line text-primary me-2"></i>Biến động Doanh Thu & Công Nợ (6 Tháng)</h5>
                    <canvas id="trendChart" height="100"></canvas>
                </div>
            </div>
            <!-- Biểu đồ cơ cấu (Doughnut) -->
            <div class="col-lg-4">
                <div class="chart-box h-100 d-flex flex-column">
                    <h5 class="fw-bold mb-4"><i class="fa-solid fa-chart-pie text-warning me-2"></i>Cơ cấu Nguồn Thu</h5>
                    <div class="flex-grow-1 d-flex align-items-center justify-content-center">
                        <canvas id="breakdownChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bảng actionable data -->
        <div class="row">
            <div class="col-12">
                <div class="chart-box">
                    <h5 class="fw-bold mb-4 text-danger"><i class="fa-solid fa-clipboard-list me-2"></i>Danh sách Khách nợ Tồn đọng Cao nhất</h5>
                    <div class="table-responsive">
                        <table class="table table-hover table-custom align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Phòng</th>
                                    <th>Khách Thuê</th>
                                    <th>Kỳ Nợ</th>
                                    <th class="text-end pe-3">Số Tiền Nợ</th>
                                    <th class="text-center">Hành Động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($topDebtors) > 0): ?>
                                    <?php foreach($topDebtors as $debtor): ?>
                                    <tr>
                                        <td class="ps-3 fw-bold text-primary"><?= htmlspecialchars($debtor['room_number']) ?></td>
                                        <td class="fw-bold text-dark"><?= htmlspecialchars($debtor['full_name']) ?></td>
                                        <td><span class="badge bg-light text-danger border border-danger-subtle px-2 py-1"><?= $debtor['month_year'] ?></span></td>
                                        <td class="text-end pe-3 fw-bold text-danger"><?= number_format($debtor['total_amount']) ?> đ</td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-danger fw-bold rounded-pill px-3"><i class="fa-regular fa-bell me-1"></i> Gửi nhắc nợ</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center py-4 text-muted fw-bold">Tuyệt vời! Không có khách nào đang nợ tiền.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        Chart.defaults.font.family = "'Nunito', sans-serif";
        Chart.defaults.color = '#64748b';

        // Biểu đồ Mix (Cột Doanh thu + Đường Công nợ)
        new Chart(document.getElementById('trendChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($months) ?>,
                datasets: [
                    {
                        type: 'line',
                        label: 'Đường Công Nợ',
                        data: <?= json_encode($debts) ?>,
                        borderColor: '#ef4444',
                        backgroundColor: '#ef4444',
                        borderWidth: 3,
                        tension: 0.4,
                        yAxisID: 'y'
                    },
                    {
                        type: 'bar',
                        label: 'Cột Doanh Thu',
                        data: <?= json_encode($revenues) ?>,
                        backgroundColor: 'rgba(14, 165, 233, 0.8)',
                        borderRadius: 6,
                        yAxisID: 'y'
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true } }
            }
        });

        // Biểu đồ Cơ cấu doanh thu (Tròn)
        new Chart(document.getElementById('breakdownChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($breakdownLabels) ?>,
                datasets: [{
                    data: <?= json_encode($breakdownData) ?>,
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: { responsive: true, cutout: '70%', plugins: { legend: { position: 'bottom' } } }
        });
    </script>
</body>
</html>