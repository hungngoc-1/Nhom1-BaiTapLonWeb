<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý phòng trọ - Nhóm 10</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f4f8fb; font-family: 'Be Vietnam Pro', sans-serif; color: #152026; }
        .sidebar { width: 260px; background: #1B3D59; min-height: 100vh; color: white; position: fixed; top: 0; left: 0; z-index: 1000; box-shadow: 4px 0 15px rgba(27,61,89,0.08); }
        .main-content { margin-left: 260px; width: calc(100% - 260px); min-height: 100vh; display: flex; flex-direction: column; }
        
        /* Top Navbar chuẩn hàng ngang: Tìm kiếm bên trái, Thông báo & User bên phải */
        .top-navbar { background: #ffffff; border-bottom: 1px solid #d4eef8; padding: 12px 35px; height: 75px; display: flex; align-items: center; justify-content: space-between; }
        .search-box-top { background: #f0f7fc; border-radius: 50px; padding: 7px 18px; width: 420px; border: 1px solid #b3d5f1; transition: all 0.2s; }
        .search-box-top:focus-within { background: #fff; border-color: #1B3D59; box-shadow: 0 0 0 3px rgba(27,61,89,0.12); }
        
        .sidebar .nav-link { color: rgba(255,255,255,0.75); padding: 12px 20px; border-radius: 12px; margin: 4px 16px; font-weight: 500; transition: all 0.2s; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: rgba(212, 238, 248, 0.2); color: #fff; }
    </style>
</head>
<body>

<div class="d-flex">
    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">
        <!-- Header đặt thanh tìm kiếm và tài khoản chung 1 hàng -->
        <header class="top-navbar shadow-sm">
            <form class="d-flex align-items-center search-box-top" method="GET" action="index.php">
                <input type="hidden" name="route" value="dashboard">
                <i class="bi bi-search text-muted me-2"></i>
                <input type="text" name="keyword" class="form-control border-0 bg-transparent p-0 shadow-none small" 
                       placeholder="Tìm phòng, khách thuê, hợp đồng, hóa đơn..." 
                       value="<?= htmlspecialchars($_GET['keyword'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </form>

            <div class="d-flex align-items-center gap-3">
                <div class="position-relative">
                    <button class="btn btn-light rounded-circle shadow-sm border-0 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: #f0f7fc;">
                        <i class="bi bi-bell text-secondary"></i>
                    </button>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                </div>

                <div class="dropdown">
                    <a class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" href="#" data-bs-toggle="dropdown">
                        <div class="text-white rounded-circle d-flex align-items-center justify-content-center me-2 fw-bold shadow-sm" style="width: 38px; height: 38px; background-color: #1B3D59 !important;">
                            H
                        </div>
                        <span class="fw-semibold small">Nguyễn Kim Huệ</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 p-2">
                        <li><a class="dropdown-item text-danger small rounded-2 py-2" href="?route=login"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</a></li>
                    </ul>
                </div>
            </div>
        </header>