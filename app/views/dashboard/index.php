<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tổng quan - StayMate</title>
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #0F665E;       
            --primary-hover: #0B4F48;
            --bg-body: #F1F5F4;       
            --bg-card: #FFFFFF;
            --text-main: #1E293B;     
            --text-muted: #64748B;    
            --border-color: #E2E8F0;  
            --danger: #DC2626;
            --warning: #D97706;
            --success: #16A34A;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: 'Be Vietnam Pro', sans-serif; 
            background-color: var(--bg-body); 
            color: var(--text-main);
            height: 100vh;
            overflow: hidden; 
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
        }

        a { text-decoration: none; }
        
        /* ================= NAVBAR ================= */
        .top-navbar {
            background-color: var(--bg-card);
            border-radius: 12px; margin: 8px 16px; padding: 6px 18px;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.02);
            border: 1px solid var(--border-color);
            flex-shrink: 0;
        }

        .nav-brand { display: flex; align-items: center; gap: 10px; }
        .nav-brand .brand-text h1 { font-size: 1.05rem; font-weight: 700; margin: 0; color: var(--primary); letter-spacing: -0.3px; }
        .nav-brand .brand-text p { font-size: 0.68rem; margin: 0; color: var(--text-muted); font-weight: 500; }

        .nav-menu { display: flex; align-items: center; gap: 4px; }
        .nav-item {
            display: flex; align-items: center; gap: 5px;
            padding: 6px 12px; border-radius: 8px;
            color: var(--text-main); font-weight: 500; font-size: 0.82rem;
            transition: all 0.2s ease;
        }
        .nav-item:hover { background-color: #F8FAFC; color: var(--primary); }
        .nav-item.active { background-color: var(--primary); color: #fff; font-weight: 600; }
        .nav-item i { font-size: 1rem; }

        .nav-right { display: flex; align-items: center; gap: 14px; }
        .noti-btn { position: relative; color: var(--text-muted); font-size: 1.1rem; cursor: pointer; }
        .noti-btn .badge { 
            position: absolute; top: -2px; right: -4px; 
            background: var(--danger); font-size: 0.55rem; font-weight: 600;
            padding: 2px 4px; border-radius: 8px; border: 2px solid #fff;
        }
        
        .user-dropdown { position: relative; padding-left: 14px; border-left: 1px solid var(--border-color); }
        .user-profile { display: flex; align-items: center; gap: 8px; cursor: pointer; }
        .user-profile .avatar { width: 32px; height: 32px; background: var(--primary); border-radius: 50%; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.85rem; }
        .user-profile .info h6 { margin: 0; font-size: 0.8rem; font-weight: 600; color: var(--text-main); }
        .user-profile .info p { margin: 0; font-size: 0.65rem; color: var(--text-muted); }
        
        .dropdown-menu-custom {
            display: none; position: absolute; top: 100%; right: 0;
            background: #fff; box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border-radius: 10px; padding: 4px 0; min-width: 160px; z-index: 100; margin-top: 8px;
            border: 1px solid var(--border-color);
        }
        .user-dropdown:hover .dropdown-menu-custom { display: block; }
        .dropdown-item-custom {
            display: flex; align-items: center; gap: 8px; padding: 8px 14px;
            color: var(--text-main); font-size: 0.8rem; font-weight: 500; text-decoration: none;
        }
        .dropdown-item-custom:hover { background: #F8FAFC; color: var(--primary); }
        .dropdown-item-custom.logout { color: var(--danger); border-top: 1px solid var(--border-color); margin-top: 3px; padding-top: 8px;}
        .dropdown-item-custom.logout:hover { background: #FEF2F2; }

        /* ================= HEADER ================= */
        .page-header { margin: 0 16px 10px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; }
        .breadcrumb-box { display: flex; align-items: center; gap: 6px; color: var(--text-muted); font-size: 0.78rem; font-weight: 500; margin-bottom: 2px; }
        .breadcrumb-box i.home-icon { color: var(--primary); font-size: 0.85rem; }
        .page-title { font-size: 1.2rem; font-weight: 700; color: var(--text-main); margin: 0; }
        .current-date { background: #fff; padding: 5px 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 600; color: var(--primary); display: flex; align-items: center; gap: 6px; border: 1px solid var(--border-color); }

        /* ================= MAIN CONTAINER ================= */
        .dashboard-container { padding: 0 16px; flex-grow: 1; display: flex; flex-direction: column; gap: 10px; }
        
        .card-custom { background: var(--bg-card); border-radius: 12px; padding: 12px 16px; border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.01); display: flex; flex-direction: column; justify-content: flex-start; }
        .card-header-custom { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
        .card-title-custom { font-size: 0.92rem; font-weight: 600; color: var(--text-main); display: flex; align-items: center; gap: 6px; margin: 0; }
        .card-title-custom i { color: var(--primary); font-size: 0.95rem; }
        
        .btn-custom { background: var(--primary); color: #fff; font-size: 0.72rem; font-weight: 500; padding: 4px 10px; border-radius: 6px; border: none; }
        .btn-custom:hover { background: var(--primary-hover); }
        .btn-custom-outline { background: transparent; color: var(--primary); border: 1px solid var(--border-color); font-size: 0.72rem; font-weight: 500; padding: 4px 8px; border-radius: 6px; }
        .btn-custom-outline:hover { background: #F8FAFC; }
        
        /* CĂN LỀ CHỮ LÊN TRÊN (TOP) VÀ TĂNG CỠ CHỮ */
        .table-custom { width: 100%; font-size: 0.84rem; margin-bottom: 0 !important; }
        .table-custom th { color: var(--text-muted); font-weight: 600; padding: 6px 8px; border-bottom: 1px solid var(--border-color); text-transform: uppercase; font-size: 0.68rem; background-color: #F8FAFC; text-align: left; }
        .table-custom td { padding: 10px 8px; border-bottom: 1px solid #F1F5F9; vertical-align: top; color: var(--text-main); text-align: left; font-weight: 500; }
        
        .room-tag { background: #F0FDF4; color: var(--primary); padding: 2px 8px; border-radius: 4px; font-size: 0.72rem; font-weight: 600; border: 1px solid #DCFCE7; }
        .status-danger { background: #FEF2F2; color: var(--danger); padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: 600; }
        .status-warning { background: #FEFCE8; color: var(--warning); padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: 600; }
        .txt-danger { color: var(--danger); font-weight: 600; }
        .txt-primary { color: var(--primary); font-weight: 600; }
        
        /* ================= STATS CARDS ================= */
        .stat-card { background: #fff; border-radius: 12px; padding: 12px 16px; display: flex; justify-content: space-between; border: 1px solid var(--border-color); height: 100%; box-shadow: 0 1px 3px rgba(0,0,0,0.01); }
        .stat-info h5 { font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600; margin-bottom: 2px; }
        .stat-info h2 { font-size: 1.2rem; font-weight: 700; color: var(--text-main); margin-bottom: 6px; }
        .stat-info h2.red { color: var(--danger); }
        .stat-icon { width: 34px; height: 34px; background: #F0FDF4; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1rem; color: var(--primary); }
        .stat-icon.red { background: #FEF2F2; color: var(--danger); }
        .stat-footer { font-size: 0.73rem; color: var(--text-muted); display: flex; justify-content: space-between; width: 100%; align-items: center; margin-top: auto; border-top: 1px dashed var(--border-color); padding-top: 4px;}
        .stat-footer strong { color: var(--text-main); font-weight: 600; }
        
        .progress-custom { height: 4px; background: #E2E8F0; border-radius: 10px; margin-bottom: 4px; overflow: hidden; }
        .progress-custom .bar { height: 100%; background: var(--primary); border-radius: 10px; }
        
        /* ================= FOOTER ================= */
        .footer-bar { margin: 0 16px 8px; display: flex; justify-content: space-between; font-size: 0.72rem; color: var(--text-muted); font-weight: 500; flex-shrink: 0; }
    </style>
</head>
<body>

    <!-- ================= NAVBAR ================= -->
    <nav class="top-navbar">
        <div class="nav-brand">
            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" fill="var(--primary)"></path>
                <rect x="9" y="11" width="2.5" height="2.5" fill="#ffffff" stroke="none"></rect>
                <rect x="13.5" y="11" width="2.5" height="2.5" fill="#ffffff" stroke="none"></rect>
                <rect x="9" y="15" width="2.5" height="2.5" fill="#ffffff" stroke="none"></rect>
                <rect x="13.5" y="15" width="2.5" height="2.5" fill="#ffffff" stroke="none"></rect>
            </svg>
            <div class="brand-text">
                <h1>STAYMATE</h1>
                <p>Quản Lý Nhà Trọ</p>
            </div>
        </div>
        
        <div class="nav-menu">
            <a href="index.php?route=dashboard" class="nav-item active"><i class="bi bi-grid-1x2-fill"></i> Tổng quan</a>
            <a href="index.php?route=rooms" class="nav-item"><i class="bi bi-house-door-fill"></i> Phòng trọ</a>
            <a href="index.php?route=tenants" class="nav-item"><i class="bi bi-people-fill"></i> Khách thuê</a>
            <a href="index.php?route=contracts" class="nav-item"><i class="bi bi-file-earmark-text-fill"></i> Hợp đồng</a>
            <a href="index.php?route=utilities" class="nav-item"><i class="bi bi-lightning-charge-fill"></i> Điện Nước</a>
            <a href="index.php?route=invoices" class="nav-item"><i class="bi bi-receipt"></i> Hóa đơn & Thu tiền</a>
            <a href="index.php?route=reports" class="nav-item"><i class="bi bi-bar-chart-fill"></i> Báo cáo</a>
        </div>

        <div class="nav-right">
            <div class="noti-btn">
                <i class="bi bi-bell-fill"></i>
                <span class="badge">3</span>
            </div>
            
            <div class="user-dropdown">
                <div class="user-profile">
                    <div class="avatar">H</div>
                    <div class="info">
                        <h6>Nguyễn Kim Huệ</h6>
                        <p>Chủ trọ / Admin</p>
                    </div>
                    <i class="bi bi-chevron-down ms-1" style="font-size: 0.65rem; color: var(--text-muted);"></i>
                </div>
                
                <div class="dropdown-menu-custom">
                    <a href="index.php?route=profile" class="dropdown-item-custom"><i class="bi bi-person-circle"></i> Trang cá nhân</a>
                    <a href="index.php?route=settings" class="dropdown-item-custom"><i class="bi bi-gear-fill"></i> Cài đặt hệ thống</a>
                    <a href="index.php?route=auth/logout" class="dropdown-item-custom logout"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- ================= BREADCRUMB & DATE ================= -->
    <div class="page-header">
        <div>
            <div class="breadcrumb-box">
                <i class="bi bi-house-door-fill home-icon"></i>
                <span>Hệ thống Quản lý <i class="bi bi-chevron-right" style="font-size: 0.55rem; margin: 0 3px;"></i> Tổng quan Quản lý Nhà trọ</span>
            </div>
            <h2 class="page-title">Tổng quan Quản lý Nhà trọ</h2>
        </div>
        <div class="current-date">
            <i class="bi bi-calendar-event"></i> <?= date('d/m/Y') ?>
        </div>
    </div>

    <!-- ================= MAIN CONTENT ================= -->
    <div class="dashboard-container">
        
        <!-- 4 KHUNG THỐNG KÊ -->
        <div class="row g-2">
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div style="width: 100%;">
                        <div class="d-flex justify-content-between">
                            <div class="stat-info">
                                <h5>Tỷ lệ lấp đầy</h5>
                                <h2>4 / 8 <span style="font-size: 0.75rem; font-weight: 500; color:var(--text-muted);">phòng</span></h2>
                            </div>
                            <div class="stat-icon"><i class="bi bi-door-open-fill"></i></div>
                        </div>
                        <div class="progress-custom"><div class="bar" style="width: 50%;"></div></div>
                        <div class="stat-footer">
                            <span style="color: var(--primary); font-weight: 600;"><i class="bi bi-check-circle-fill"></i> 50% đang thuê</span>
                            <span style="background: #F8FAFC; padding: 1px 5px; border-radius: 4px; font-size: 0.68rem;">4 trống</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div style="width: 100%;">
                        <div class="d-flex justify-content-between">
                            <div class="stat-info">
                                <h5>Thực thu 2026</h5>
                                <h2>20.595.000 đ</h2>
                            </div>
                            <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
                        </div>
                        <div class="stat-footer mt-2">
                            <span>Tổng phát sinh:</span>
                            <strong>29.225.000 đ</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div style="width: 100%;">
                        <div class="d-flex justify-content-between">
                            <div class="stat-info">
                                <h5>Công nợ chưa thu</h5>
                                <h2 class="red">8.630.000 đ</h2>
                            </div>
                            <div class="stat-icon red"><i class="bi bi-exclamation-octagon-fill"></i></div>
                        </div>
                        <div class="stat-footer mt-2">
                            <span style="color: var(--danger); font-weight: 500;"><i class="bi bi-clock-history"></i> Tồn đọng:</span>
                            <span class="status-danger">3 hóa đơn</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div style="width: 100%;">
                        <div class="d-flex justify-content-between">
                            <div class="stat-info">
                                <h5>Khách & Hợp đồng</h5>
                                <h2>4 <span style="font-size: 0.75rem; font-weight: 500; color:var(--text-muted);">lưu trú</span></h2>
                            </div>
                            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                        </div>
                        <div class="stat-footer mt-2">
                            <span>Hợp đồng hiệu lực:</span>
                            <strong>4 hợp đồng</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BẢNG DỮ LIỆU CĂN LỀ TRÊN & CHỮ RÕ RÀNG -->
        <div class="row g-2 flex-grow-1 mb-1">
            <!-- CỘT TRÁI: THU NỢ -->
            <div class="col-lg-6 h-100">
                <div class="card-custom h-100">
                    <div class="card-header-custom">
                        <h3 class="card-title-custom"><i class="bi bi-receipt-cutoff"></i> Hóa đơn Cần Thu Nợ (3)</h3>
                        <button class="btn-custom"><i class="bi bi-list-check me-1"></i> Lập hóa đơn</button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table-custom">
                            <thead>
                                <tr>
                                    <th>Mã HĐ</th>
                                    <th>Phòng / Khách</th>
                                    <th>Kỳ</th>
                                    <th>Còn Phải Thu</th>
                                    <th>Hạn Đóng</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="txt-primary">INV-2610-P201</td>
                                    <td><span class="room-tag">P201</span> P. M. Tuấn</td>
                                    <td>T10/26</td>
                                    <td class="txt-danger">3.605.000 đ</td>
                                    <td>15/10 <span class="status-danger">Quá hạn</span></td>
                                    <td>
                                        <button class="btn-custom py-1 px-2" style="font-size: 0.7rem;">Thu</button>
                                        <button class="btn-custom-outline py-1 px-1 ms-1" style="font-size: 0.7rem;">Chi tiết</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="txt-primary">INV-2610-P101</td>
                                    <td><span class="room-tag">P101</span> N. V. An</td>
                                    <td>T10/26</td>
                                    <td class="txt-danger">1.505.000 đ</td>
                                    <td>25/10 <span class="status-warning">1 phần</span></td>
                                    <td>
                                        <button class="btn-custom py-1 px-2" style="font-size: 0.7rem;">Thu</button>
                                        <button class="btn-custom-outline py-1 px-1 ms-1" style="font-size: 0.7rem;">Chi tiết</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="txt-primary">INV-2610-P202</td>
                                    <td><span class="room-tag">P202</span> V. T. T. Hà</td>
                                    <td>T10/26</td>
                                    <td class="txt-danger">3.520.000 đ</td>
                                    <td>28/10</td>
                                    <td>
                                        <button class="btn-custom py-1 px-2" style="font-size: 0.7rem;">Thu</button>
                                        <button class="btn-custom-outline py-1 px-1 ms-1" style="font-size: 0.7rem;">Chi tiết</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- CỘT PHẢI: ĐẶT TRƯỚC & HẾT HẠN -->
            <div class="col-lg-6 d-flex flex-column gap-2 h-100">
                <div class="card-custom flex-grow-1" style="background-color: #FAFAFA;">
                    <div class="card-header-custom mb-1">
                        <h3 class="card-title-custom"><i class="bi bi-bell-fill"></i> Đặt trước Phòng <span class="badge bg-danger ms-2 rounded-pill" style="font-size:0.55rem;">3 mới</span></h3>
                        <button class="btn-custom-outline py-1 px-2" style="font-size: 0.72rem;">Lịch sử</button>
                    </div>
                    <table class="table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Phòng</th>
                                <th>Khách Đặt</th>
                                <th>Vào Ở</th>
                                <th>Xử Lý</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="room-tag" style="background:#F0FDF4;">P103</span></td>
                                <td>Nguyễn Văn B <small class="text-muted">(0987654321)</small></td>
                                <td>29/10</td>
                                <td><span class="status-warning" style="background:#F0FDF4; color:var(--success);"><i class="bi bi-check-circle-fill"></i> Đã LH</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="card-custom flex-grow-1">
                    <div class="card-header-custom mb-1">
                        <h3 class="card-title-custom"><i class="bi bi-calendar2-week-fill" style="color:var(--warning); background:#FEFCE8;"></i> Sắp hết hạn (60 ngày)</h3>
                        <button class="btn-custom-outline py-1 px-2" style="font-size: 0.72rem;">Quản lý HĐ</button>
                    </div>
                    <table class="table-custom mb-0">
                        <thead>
                            <tr>
                                <th>Phòng</th>
                                <th>Khách Thuê</th>
                                <th>Hết Hạn</th>
                                <th>Còn Lại</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="room-tag">P201</span></td>
                                <td>Phạm Tuấn <small class="text-muted">(0912345003)</small></td>
                                <td>05/11/26</td>
                                <td><span class="status-warning">Còn 9 ngày</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= FOOTER ================= -->
    <div class="footer-bar">
        <div><i class="bi bi-buildings-fill me-1"></i> © <?= date('Y') ?> STAYMATE - Quản lý Nhà trọ Chuyên nghiệp</div>
        <div>
            <i class="bi bi-shield-check me-1" style="color: var(--primary);"></i> WAMP MVC Architecture 
            <span class="mx-2">•</span> 
            <i class="bi bi-lightning-charge-fill me-1" style="color: var(--warning);"></i> Tự động hóa Điện, Nước & Hóa đơn
        </div>
    </div>

</body>
</html>