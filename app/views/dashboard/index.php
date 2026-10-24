<?php include __DIR__ . '/../layouts/header.php'; ?>

<style>
    .dashboard-banner {
        background: linear-gradient(rgba(27, 61, 89, 0.35), rgba(21, 32, 38, 0.55)), url('background.png') no-repeat center center;
        background-size: cover;
        border-radius: 18px;
        color: white;
        padding: 36px 40px;
        margin-bottom: 25px;
        box-shadow: 0 10px 30px rgba(27, 61, 89, 0.15);
    }
    .stat-card-custom {
        border: none;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 4px 20px rgba(27, 61, 89, 0.04);
        transition: transform 0.2s;
        padding: 24px;
        height: 100%;
        border: 1px solid #d4eef8;
    }
    .stat-card-custom:hover { transform: translateY(-3px); }
    .card-box {
        border: none;
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 4px 20px rgba(27, 61, 89, 0.04);
        padding: 24px;
        margin-bottom: 25px;
        border: 1px solid #d4eef8;
    }
    .table-custom th {
        font-weight: 600;
        color: #1B3D59;
        font-size: 0.85rem;
        border-bottom: 2px solid #d4eef8;
        background: transparent !important;
    }
    .table-custom td {
        vertical-align: middle;
        font-size: 0.9rem;
        border-bottom: 1px solid #f0f7fc;
        padding: 14px 10px;
    }
</style>

<div class="p-4 flex-grow-1">
    <!-- Banner Chào mừng đúng tên Nguyễn Kim Huệ và ngày giờ thực tế máy tính -->
    <div class="dashboard-banner">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <p class="mb-1 opacity-75 small fw-semibold text-uppercase tracking-wider">Chào mừng bạn,</p>
                <h1 class="fw-bold mb-0 display-6" style="font-family: serif;">Nguyễn Kim Huệ</h1>
            </div>
            <div class="bg-white bg-opacity-25 px-3 py-2 rounded-3 text-white small fw-medium shadow-sm">
                <i class="bi bi-calendar-event me-2"></i> Thứ Bảy, 24/10/2026
            </div>
        </div>
    </div>

    <!-- 4 Thẻ Thống Kê Tổng Quan -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card-custom">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-3 p-3 me-3 shadow-sm" style="background-color: #d4eef8; color: #1B3D59;">
                        <i class="bi bi-houses-fill fs-4"></i>
                    </div>
                    <span class="text-muted fw-semibold small">Tổng số phòng</span>
                </div>
                <h2 class="fw-bold mb-2 text-dark">12</h2>
                <div class="text-muted small">Đang cho thuê: <span class="fw-semibold text-dark">9</span> | Trống: <span class="fw-semibold text-dark">3</span></div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card-custom">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-3 p-3 me-3 shadow-sm" style="background-color: #d4eef8; color: #1B3D59;">
                        <i class="bi bi-people-fill fs-4"></i>
                    </div>
                    <span class="text-muted fw-semibold small">Tổng số khách thuê</span>
                </div>
                <h2 class="fw-bold mb-2 text-dark">9</h2>
                <div class="text-muted small">Nam: <span class="fw-semibold text-dark">4</span> | Nữ: <span class="fw-semibold text-dark">5</span></div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card-custom">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-3 p-3 me-3 shadow-sm" style="background-color: #d4eef8; color: #1B3D59;">
                        <i class="bi bi-file-earmark-text-fill fs-4"></i>
                    </div>
                    <span class="text-muted fw-semibold small">Hợp đồng sắp hết hạn</span>
                </div>
                <h2 class="fw-bold mb-2 text-dark">2</h2>
                <div class="text-muted small">Trong 30 ngày tới</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card-custom">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-3 p-3 me-3 shadow-sm" style="background-color: #d4eef8; color: #1B3D59;">
                        <i class="bi bi-coin fs-4"></i>
                    </div>
                    <span class="text-muted fw-semibold small">Doanh thu tháng này</span>
                </div>
                <h3 class="fw-bold mb-2" style="color: #1B3D59;">18.500.000đ</h3>
                <div class="text-success small fw-medium"><i class="bi bi-arrow-up-short"></i> 12% so với tháng trước</div>
            </div>
        </div>
    </div>

    <!-- Bố cục chuẩn mẫu -->
    <div class="row g-4 mb-4">
        <!-- Cột Trái -->
        <div class="col-12 col-lg-7">
            <div class="card-box mb-4">
                <h5 class="fw-bold text-dark mb-4" style="color: #1B3D59 !important;">Doanh thu 6 tháng gần đây</h5>
                <div style="height: 260px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <div class="card-box">
                <h5 class="fw-bold text-dark mb-3" style="color: #1B3D59 !important;">Tình trạng phòng trọ</h5>
                <div class="d-flex align-items-center justify-content-around py-2">
                    <div style="position: relative; width: 170px; height: 170px;">
                        <canvas id="roomStatusChart"></canvas>
                        <div class="position-absolute top-50 start-50 translate-middle text-center">
                            <span class="fs-4 fw-bold text-dark">12</span><br>
                            <small class="text-muted" style="font-size: 0.75rem;">Tổng số phòng</small>
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <div><span class="badge rounded-pill me-2" style="background-color: #1B3D59;">●</span> Đang thuê <span class="fw-bold ms-3">9</span></div>
                        <div><span class="badge rounded-pill me-2" style="background-color: #6A97C0;">●</span> Trống <span class="fw-bold ms-3">3</span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cột Phải -->
        <div class="col-12 col-lg-5">
            <div class="card-box mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-dark mb-0" style="color: #1B3D59 !important;"><i class="bi bi-clock-history me-2"></i>Hoạt động gần đây</h5>
                    <a href="#" class="text-decoration-none small fw-semibold" style="color: #1B3D59 !important;">Xem tất cả <i class="bi bi-chevron-right"></i></a>
                </div>
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex justify-content-between align-items-start pb-2 border-bottom">
                        <div>
                            <span class="text-success me-1">●</span>
                            <span class="text-dark small fw-medium">Khách thuê Nguyễn Văn A thanh toán hóa đơn phòng P101</span>
                        </div>
                        <small class="text-muted text-nowrap ms-2" style="font-size: 0.75rem;">24/10/2026 09:24</small>
                    </div>
                    <div class="d-flex justify-content-between align-items-start pb-2 border-bottom">
                        <div>
                            <span class="text-secondary me-1">●</span>
                            <span class="text-dark small fw-medium">Thêm mới hợp đồng cho phòng P104</span>
                        </div>
                        <small class="text-muted text-nowrap ms-2" style="font-size: 0.75rem;">23/10/2026 08:17</small>
                    </div>
                    <div class="d-flex justify-content-between align-items-start pb-2 border-bottom">
                        <div>
                            <span class="text-secondary me-1">●</span>
                            <span class="text-dark small fw-medium">Khách thuê Trần Thị B đăng ký thuê phòng P106</span>
                        </div>
                        <small class="text-muted text-nowrap ms-2" style="font-size: 0.75rem;">23/10/2026 16:43</small>
                    </div>
                    <div class="d-flex justify-content-between align-items-start pb-2 border-bottom">
                        <div>
                            <span class="text-secondary me-1">●</span>
                            <span class="text-dark small fw-medium">Thanh toán hóa đơn HD003 (Lê Văn C)</span>
                        </div>
                        <small class="text-muted text-nowrap ms-2" style="font-size: 0.75rem;">22/10/2026 14:20</small>
                    </div>
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="text-success me-1">●</span>
                            <span class="text-dark small fw-medium">Sửa thông tin phòng P105</span>
                        </div>
                        <small class="text-muted text-nowrap ms-2" style="font-size: 0.75rem;">21/10/2026 11:03</small>
                    </div>
                </div>
            </div>

            <div class="card-box">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-dark mb-0" style="color: #1B3D59 !important;"><i class="bi bi-bell me-2"></i>Thông báo</h5>
                    <a href="#" class="text-decoration-none small fw-semibold" style="color: #1B3D59 !important;">Xem tất cả <i class="bi bi-chevron-right"></i></a>
                </div>
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex justify-content-between align-items-start pb-2 border-bottom">
                        <div>
                            <span class="badge bg-danger rounded-circle p-1 me-2"> </span>
                            <span class="text-dark small fw-medium">Hóa đơn HD003 chưa thanh toán quá hạn 3 ngày</span>
                        </div>
                        <small class="text-muted text-nowrap ms-2" style="font-size: 0.75rem;">23/10/2026</small>
                    </div>
                    <div class="d-flex justify-content-between align-items-start pb-2 border-bottom">
                        <div>
                            <span class="badge bg-warning rounded-circle p-1 me-2"> </span>
                            <span class="text-dark small fw-medium">Hợp đồng phòng P104 sắp hết hạn (trong 5 ngày)</span>
                        </div>
                        <small class="text-muted text-nowrap ms-2" style="font-size: 0.75rem;">22/10/2026</small>
                    </div>
                    <div class="d-flex justify-content-between align-items-start pb-2 border-bottom">
                        <div>
                            <span class="badge bg-primary rounded-circle p-1 me-2"> </span>
                            <span class="text-dark small fw-medium">Có 1 phòng trống (P106)</span>
                        </div>
                        <small class="text-muted text-nowrap ms-2" style="font-size: 0.75rem;">21/10/2026</small>
                    </div>
                    <div class="d-flex justify-content-between align-items-start pb-2 border-bottom">
                        <div>
                            <span class="badge bg-info rounded-circle p-1 me-2"> </span>
                            <span class="text-dark small fw-medium">Đã có khách thuê mới đăng ký (Trần Thị B)</span>
                        </div>
                        <small class="text-muted text-nowrap ms-2" style="font-size: 0.75rem;">20/10/2026</small>
                    </div>
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="badge bg-success rounded-circle p-1 me-2"> </span>
                            <span class="text-dark small fw-medium">Hệ thống đã cập nhật thành công dữ liệu hóa đơn</span>
                        </div>
                        <small class="text-muted text-nowrap ms-2" style="font-size: 0.75rem;">19/10/2026</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const ctxBar = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: ['T5', 'T6', 'T7', 'T8', 'T9', 'T10'],
            datasets: [{
                label: 'Doanh thu (triệu VNĐ)',
                data: [12.5, 14.2, 16.8, 18.5, 20.1, 18.5],
                backgroundColor: function(context) {
                    return context.dataIndex === 5 ? '#1B3D59' : '#b3d5f1';
                },
                borderRadius: 8,
                barThickness: 34
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 25,
                    ticks: { callback: function(value) { return value + 'tr'; } },
                    grid: { borderDash: [4, 4] }
                },
                x: { grid: { display: false } }
            }
        }
    });

    const ctxDoughnut = document.getElementById('roomStatusChart').getContext('2d');
    new Chart(ctxDoughnut, {
        type: 'doughnut',
        data: {
            labels: ['Đang thuê', 'Trống'],
            datasets: [{
                data: [9, 3],
                backgroundColor: ['#1B3D59', '#6A97C0'],
                borderWidth: 0,
                cutout: '75%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>