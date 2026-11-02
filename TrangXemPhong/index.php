<?php
/**
 * TRANG WEB XEM PHÒNG CHO KHÁCH THUÊ THAM KHẢO
 * Người phụ trách: Hủng Văn Ngọc (Nhóm trưởng)
 * 
 * Đây là trang web công khai độc lập, không yêu cầu đăng nhập.
 * Khách có thể xem danh sách phòng còn trống, giá cả, tiện ích và tìm kiếm.
 */

require_once __DIR__ . '/../app/Config/Database.php';

$keyword = trim($_GET['keyword'] ?? '');
$maxPrice = trim($_GET['max_price'] ?? '');

$conn = Database::getConnection();

// Lọc các phòng trống (available)
$sql = "SELECT * FROM rooms WHERE status = 'available'";
$params = [];

if ($keyword !== '') {
    $sql .= " AND (room_number LIKE :kw OR description LIKE :kw2)";
    $params[':kw'] = "%$keyword%";
    $params[':kw2'] = "%$keyword%";
}

if ($maxPrice !== '' && is_numeric($maxPrice)) {
    $sql .= " AND price <= :max_price";
    $params[':max_price'] = (float)$maxPrice;
}

$sql .= " ORDER BY price ASC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy thêm danh mục dịch vụ tham khảo
$services = $conn->query("SELECT * FROM services ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayMate - Tìm Phòng Trọ Nhanh Chóng, Tiện Nghi</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #12746B;
            --primary-light: #E0F2F1;
            --accent: #E07A5F;
            --bg-light: #F7FAF9;
        }
        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--bg-light);
            color: #2D3748;
        }
        .hero-banner {
            background: linear-gradient(135deg, #12746B 0%, #0B4F49 100%);
            color: white;
            padding: 60px 0 70px;
            border-radius: 0 0 30px 30px;
            box-shadow: 0 10px 30px rgba(18, 116, 107, 0.15);
        }
        .filter-card {
            background: white;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
            margin-top: -35px;
        }
        .room-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid #E2E8F0;
            transition: transform 0.25s, box-shadow 0.25s;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .room-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 28px rgba(0,0,0,0.1);
        }
        .room-header {
            background: var(--primary-light);
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .badge-available {
            background: #D1FAE5;
            color: #059669;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
        }
        .price-tag {
            color: var(--accent);
            font-size: 1.35rem;
            font-weight: 800;
        }
        .btn-contact {
            background: var(--primary);
            color: white;
            font-weight: 700;
            border-radius: 12px;
            padding: 10px 16px;
            border: none;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .btn-contact:hover {
            background: #0D5650;
            color: white;
        }
        .service-badge {
            background: #EDF2F7;
            color: #4A5568;
            border-radius: 10px;
            padding: 6px 12px;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
    </style>
</head>
<body>

    <!-- Header / Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="index.php" style="color: var(--primary);">
                <i class="fa-solid fa-hotel fs-3"></i>
                <span class="fs-4">SMART STAY</span>
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="../index.php?route=auth/login" class="btn btn-outline-secondary btn-sm px-3 rounded-pill">
                    <i class="fa-solid fa-lock me-1"></i> Đăng nhập quản lý
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Banner -->
    <div class="hero-banner text-center">
        <div class="container">
            <h1 class="fw-bold mb-3">Tìm Phòng Trọ Tiện Nghi, Giá Tốt</h1>
            <p class="lead opacity-90 mb-0">Hệ thống phòng trọ an ninh, sạch sẽ, đầy đủ tiện ích dành cho bạn</p>
        </div>
    </div>

    <!-- Container Chính -->
    <div class="container mb-5">
        
        <!-- Khung Tìm Kiếm & Lọc -->
        <div class="filter-card mb-5">
            <form method="GET" action="index.php" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-bold text-muted small">TÌM KIẾM THEO SỐ PHÒNG / TIỆN ÍCH</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                        <input type="text" name="keyword" class="form-control bg-light border-start-0" 
                               placeholder="VD: P101, ban công, điều hòa..." value="<?= htmlspecialchars($keyword) ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold text-muted small">GIÁ TỐI ĐA (VNĐ)</label>
                    <select name="max_price" class="form-select bg-light">
                        <option value="">Tất cả các mức giá</option>
                        <option value="2000000" <?= $maxPrice === '2000000' ? 'selected' : '' ?>>Dưới 2.000.000 đ</option>
                        <option value="3000000" <?= $maxPrice === '3000000' ? 'selected' : '' ?>>Dưới 3.000.000 đ</option>
                        <option value="4000000" <?= $maxPrice === '4000000' ? 'selected' : '' ?>>Dưới 4.000.000 đ</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-contact flex-fill">
                        <i class="fa-solid fa-filter me-1"></i> Lọc Phòng
                    </button>
                    <?php if ($keyword !== '' || $maxPrice !== ''): ?>
                        <a href="index.php" class="btn btn-outline-secondary rounded-3">Reset</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Tiện ích chung -->
        <div class="mb-4">
            <h6 class="fw-bold text-muted text-uppercase mb-3 small"><i class="fa-solid fa-concierge-bell me-1"></i> Bảng giá dịch vụ áp dụng chung</h6>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($services as $srv): ?>
                    <span class="service-badge">
                        <i class="fa-solid fa-check text-success"></i>
                        <strong><?= htmlspecialchars($srv['service_name']) ?>:</strong> 
                        <?= number_format($srv['unit_price'], 0, ',', '.') ?> đ/<?= htmlspecialchars($srv['unit']) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Danh sách Phòng Trống -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0 text-dark">
                Phòng Đang Trống <span class="badge bg-secondary ms-2 fs-6"><?= count($rooms) ?></span>
            </h4>
        </div>

        <?php if (!empty($rooms)): ?>
            <div class="row g-4">
                <?php foreach ($rooms as $r): ?>
                    <div class="col-md-4">
                        <div class="room-card">
                            <div class="room-header">
                                <h5 class="fw-bold mb-0 text-dark">
                                    <i class="fa-solid fa-door-open me-2" style="color: var(--primary);"></i>
                                    Phòng <?= htmlspecialchars($r['room_number']) ?>
                                </h5>
                                <span class="badge-available"><i class="fa-solid fa-circle-check me-1"></i> Còn trống</span>
                            </div>
                            <div class="p-4 flex-fill d-flex flex-direction-column justify-content-between">
                                <div>
                                    <div class="mb-3">
                                        <div class="text-muted small">GIÁ THUÊ THÁNG</div>
                                        <div class="price-tag"><?= number_format($r['price'], 0, ',', '.') ?> <span class="fs-6 text-muted fw-normal">đ/tháng</span></div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="text-muted small mb-1">MÔ TẢ & TIỆN NGHI</div>
                                        <p class="mb-0 text-secondary" style="font-size: 0.9rem;">
                                            <?= nl2br(htmlspecialchars($r['description'] ?: 'Phòng sạch sẽ, thoáng mát, giờ giấc tự do, có chỗ để xe an toàn.')) ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="pt-3 border-top mt-3 d-flex justify-content-between align-items-center">
                                    <span class="text-muted small"><i class="fa-solid fa-shield-halved text-success me-1"></i> An ninh 24/7</span>
                                    <a href="tel:0912345678" class="btn btn-contact btn-sm">
                                        <i class="fa-solid fa-phone me-1"></i> Liên hệ thuê
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5 bg-white rounded-4 border">
                <i class="fa-regular fa-face-frown text-muted" style="font-size: 3rem;"></i>
                <h5 class="fw-bold mt-3 text-muted">Hiện tại không tìm thấy phòng trống phù hợp</h5>
                <p class="text-muted mb-3">Vui lòng thử lại với mức lọc khác hoặc liên hệ hotline để được tư vấn.</p>
                <a href="index.php" class="btn btn-outline-secondary">Xem tất cả phòng trống</a>
            </div>
        <?php endif; ?>

    </div>

    <!-- Footer -->
    <footer class="bg-white border-top py-4 text-center text-muted small">
        <div class="container">
            <p class="mb-1 fw-bold">SMART STAY - HỆ THỐNG PHÒNG TRỌ CAO CẤP</p>
            <p class="mb-0">Hotline tư vấn xem phòng: 0912.345.678 | Địa chỉ: Hà Nội</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
