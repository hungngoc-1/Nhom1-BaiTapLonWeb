<?php
// 1. KẾT NỐI CƠ SỞ DỮ LIỆU
$host = 'localhost';
$db_name = 'quan_ly_nha_tro';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. TRUY VẤN LẤY SỐ LIỆU TỪ SQL
    // Tỷ lệ lấp đầy
    $stmt = $conn->query("SELECT COUNT(*) as total FROM rooms");
    $total_rooms = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    $stmt = $conn->query("SELECT COUNT(*) as rented FROM rooms WHERE status = 'rented'");
    $rented_rooms = $stmt->fetch(PDO::FETCH_ASSOC)['rented'] ?? 0;
    $empty_rooms = $total_rooms - $rented_rooms;
    $occupancy_rate = $total_rooms > 0 ? round(($rented_rooms / $total_rooms) * 100) : 0;

    // Thực thu
    $stmt = $conn->query("SELECT SUM(total_amount) as total_paid FROM invoices WHERE status = 'paid'");
    $total_revenue = $stmt->fetch(PDO::FETCH_ASSOC)['total_paid'] ?? 0;

    // Công nợ
    $stmt = $conn->query("SELECT SUM(total_amount) as total_unpaid, COUNT(*) as unpaid_count FROM invoices WHERE status = 'unpaid'");
    $unpaid_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_debt = $unpaid_data['total_unpaid'] ?? 0;
    $unpaid_count = $unpaid_data['unpaid_count'] ?? 0;

    // Khách & Hợp đồng
    $stmt = $conn->query("SELECT COUNT(*) as active_contracts FROM contracts WHERE status = 'active'");
    $active_contracts = $stmt->fetch(PDO::FETCH_ASSOC)['active_contracts'] ?? 0;

    // Danh sách hóa đơn nợ
    $stmt = $conn->query("
        SELECT i.id, i.month_year, i.total_amount, r.room_number, u.full_name 
        FROM invoices i 
        JOIN contracts c ON i.contract_id = c.id
        JOIN rooms r ON i.room_id = r.id
        JOIN users u ON c.tenant_id = u.id
        WHERE i.status = 'unpaid' ORDER BY i.id DESC LIMIT 5
    ");
    $unpaid_invoices_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    echo "Lỗi kết nối CSDL: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayMate - Tổng quan Tòa nhà</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-teal: #36837c;
            --primary-hover: #2a6a64;
            --bg-body: #f4f8f7;
            --card-radius: 24px;
        }

        body {
            background-color: var(--bg-body);
            font-family: 'Nunito', sans-serif;
            font-size: 0.9rem;
            color: #334155;
            min-height: 100vh;
        }

        /* Navbar */
        .navbar-custom {
            background-color: #ffffff;
            padding: 10px 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-bottom: 2px solid #e2e8f0;
        }
        .brand-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            flex-shrink: 0;
        }
        .brand-logo i { color: var(--primary-teal); font-size: 2rem; }
        .brand-text { color: var(--primary-teal); font-weight: 800; font-size: 1.2rem; line-height: 1.1; }
        
        .nav-pills { gap: 2px; }
        .nav-pills .nav-link { color: #64748b; font-weight: 700; font-size: 0.8rem; padding: 7px 11px; border-radius: 16px; transition: all 0.2s; white-space: nowrap; }
        .nav-pills .nav-link i { margin-right: 4px; }
        .nav-pills .nav-link:hover { color: var(--primary-teal); background: #e6f3f1; }
        .nav-pills .nav-link.active { background-color: var(--primary-teal); color: white; box-shadow: 0 4px 12px rgba(54, 131, 124, 0.3); }

        /* CÁC THẺ CARD */
        .stat-card { border-radius: var(--card-radius); border: 2px solid; padding: 24px; box-shadow: 0 12px 35px rgba(15, 23, 42, 0.08); height: 100%; transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-4px); }
        .card-green { background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-color: #86efac; }
        .card-blue { background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-color: #7dd3fc; }
        .card-orange { background: linear-gradient(135deg, #fffbeb00 0%, #fef3c7 100%); border-color: #fde047; background-color: #fffdf5; }
        .card-purple { background: linear-gradient(135deg, #fdf4ff 0%, #fae8ff 100%); border-color: #f0abfc; background-color: #fefbff; }

        .content-card { background: #ffffff; border-radius: var(--card-radius); border: 2px solid #cbd5e1; box-shadow: 0 12px 35px rgba(15, 23, 42, 0.08); overflow: hidden; }
        .btn-teal-custom { background-color: var(--primary-teal); color: white; border-radius: 16px; font-weight: 700; font-size: 0.85rem; padding: 8px 16px; border: none; transition: 0.2s; }
        .btn-teal-custom:hover { background-color: var(--primary-hover); color: white; }
        .text-teal { color: var(--primary-teal) !important; }
        .text-xs { font-size: 0.8rem; }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container-fluid px-3 px-xl-4">
            <a class="brand-logo me-3" href="index.php?route=dashboard">
                <i class="fa-solid fa-house-chimney"></i>
                <div>
                    <div class="brand-text">StayMate</div>
                    <div class="text-muted text-xs">Quản Lý Nhà Trọ</div>
                </div>
            </a>
            <ul class="nav nav-pills me-auto d-none d-lg-flex">
                <li class="nav-item"><a class="nav-link" href="index.php?route=dashboard"><i class="fa-solid fa-city"></i> Tòa nhà</a></li>
                <li class="nav-item"><a class="nav-link active" href="index.php?route=overview"><i class="fa-solid fa-chart-pie"></i> Tổng quan</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php?route=rooms"><i class="fa-solid fa-door-open"></i> Phòng trọ</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-users"></i> Khách thuê</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-file-contract"></i> Hợp đồng</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-bolt"></i> Điện Nước</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-file-invoice-dollar"></i> Hóa đơn</a></li>
                <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-chart-column"></i> Báo cáo</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container-fluid px-4 px-xl-5 py-4">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 text-xs fw-semibold">
                        <li class="breadcrumb-item"><a href="index.php?route=dashboard" class="text-decoration-none text-teal"><i class="fa-solid fa-house me-1"></i> Hệ thống Quản lý</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Tổng quan Quản lý Nhà trọ</li>
                    </ol>
                </nav>
                <h3 class="fw-bold mb-0 text-dark">
                    Tổng quan Quản lý Nhà trọ
                    <?php if (isset($_GET['building_id']) && $_GET['building_id'] !== 'all'): ?>
                        <span class="text-teal"> - Tòa <?= htmlspecialchars($_GET['building_id']) ?></span>
                    <?php endif; ?>
                </h3>
            </div>
            <div>
                <button class="btn btn-light border bg-white shadow-sm text-muted rounded-pill px-3 py-2 fw-bold text-xs">
                    <i class="fa-regular fa-calendar me-2 text-teal"></i> Hôm nay, <?= date('d/m/Y') ?>
                </button>
            </div>
        </div>

        <!-- 4 Thẻ Thống kê -->
        <div class="row g-4 mb-4">
            <!-- Tỷ lệ lấp đầy -->
            <div class="col-md-3">
                <div class="stat-card card-green">
                    <div class="text-success text-xs fw-bold text-uppercase mb-2">Tỷ lệ lấp đầy</div>
                    <div class="d-flex justify-content-between align-items-end mb-3">
                        <h3 class="fw-bold mb-0 text-dark"><?= $rented_rooms ?> / <?= $total_rooms ?> <span class="fs-6 text-muted fw-normal">phòng</span></h3>
                        <div class="p-2 bg-success text-white rounded-circle shadow-sm"><i class="fa-solid fa-door-closed"></i></div>
                    </div>
                    <div class="progress mb-2 bg-white" style="height: 6px;">
                        <div class="progress-bar bg-success rounded" style="width: <?= $occupancy_rate ?>%"></div>
                    </div>
                    <div class="d-flex justify-content-between text-xs fw-bold text-success">
                        <span><i class="fa-solid fa-circle-check me-1"></i><?= $occupancy_rate ?>% đang thuê</span>
                        <span class="text-muted"><?= $empty_rooms ?> trống</span>
                    </div>
                </div>
            </div>

            <!-- Thực thu -->
            <div class="col-md-3">
                <div class="stat-card card-blue">
                    <div class="text-primary text-xs fw-bold text-uppercase mb-2">Thực thu <?= date('Y') ?></div>
                    <div class="d-flex justify-content-between align-items-end mb-3">
                        <h3 class="fw-bold mb-0 text-dark"><?= number_format($total_revenue, 0, ',', '.') ?> đ</h3>
                        <div class="p-2 bg-primary text-white rounded-circle shadow-sm"><i class="fa-solid fa-money-bill-wave"></i></div>
                    </div>
                </div>
            </div>

            <!-- Công nợ -->
            <div class="col-md-3">
                <div class="stat-card card-orange">
                    <div class="text-warning text-xs fw-bold text-uppercase mb-2" style="color: #b45309 !important;">Công nợ chưa thu</div>
                    <div class="d-flex justify-content-between align-items-end mb-3">
                        <h3 class="fw-bold mb-0 text-danger"><?= number_format($total_debt, 0, ',', '.') ?> đ</h3>
                        <div class="p-2 bg-warning text-white rounded-circle shadow-sm"><i class="fa-solid fa-circle-exclamation"></i></div>
                    </div>
                    <hr class="text-muted my-2 opacity-25">
                    <div class="d-flex justify-content-between text-xs">
                        <span class="text-danger fw-semibold"><i class="fa-regular fa-clock me-1"></i> Tồn đọng:</span>
                        <span class="fw-bold text-danger"><?= $unpaid_count ?> hóa đơn</span>
                    </div>
                </div>
            </div>

            <!-- Khách & Hợp đồng -->
            <div class="col-md-3">
                <div class="stat-card card-purple">
                    <div class="text-xs fw-bold text-uppercase mb-2" style="color: #a21caf !important;">Khách & Hợp đồng</div>
                    <div class="d-flex justify-content-between align-items-end mb-3">
                        <h3 class="fw-bold mb-0 text-dark"><?= $active_contracts ?> <span class="fs-6 text-muted fw-normal">hợp đồng</span></h3>
                        <div class="p-2 text-white rounded-circle shadow-sm" style="background-color: #c084fc;"><i class="fa-solid fa-users"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bảng Hóa Đơn (Tự động hiển thị nếu có nợ) -->
        <div class="row g-4">
            <div class="col-lg-12">
                <div class="content-card h-100">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-file-invoice me-2 text-teal"></i> Hóa đơn Cần Thu Nợ (<?= $unpaid_count ?>)</h6>
                        <button class="btn btn-teal-custom btn-sm"><i class="fa-solid fa-plus me-1"></i> Lập hóa đơn</button>
                    </div>
                    
                    <div class="card-body p-0">
                        <?php if($unpaid_count > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 text-sm">
                                    <thead class="bg-light text-muted" style="font-size: 0.85rem;">
                                        <tr>
                                            <th class="ps-4">PHÒNG</th>
                                            <th>KHÁCH THUÊ</th>
                                            <th>KỲ</th>
                                            <th>CÒN PHẢI THU</th>
                                            <th class="pe-4 text-end">THAO TÁC</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($unpaid_invoices_list as $inv): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-teal"><?= htmlspecialchars($inv['room_number']) ?></td>
                                            <td class="fw-semibold"><?= htmlspecialchars($inv['full_name']) ?></td>
                                            <td><?= htmlspecialchars($inv['month_year']) ?></td>
                                            <td class="fw-bold text-danger"><?= number_format($inv['total_amount'], 0, ',', '.') ?> đ</td>
                                            <td class="pe-4 text-end">
                                                <button class="btn btn-sm btn-teal-custom">Thu</button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="py-5 text-center text-muted">
                                <i class="fa-solid fa-folder-open fs-1 text-teal opacity-50 mb-3"></i>
                                <p class="fw-semibold mb-0">Chưa có dữ liệu hóa đơn công nợ trong hệ thống.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</body>
</html>