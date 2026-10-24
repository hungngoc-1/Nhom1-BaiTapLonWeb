<?php $currentRoute = $_GET['route'] ?? 'dashboard'; ?>
<aside class="sidebar d-flex flex-column flex-shrink-0">
    <div class="p-4 text-center border-bottom border-light border-opacity-10 mb-2">
        <!-- Logo ngôi nhà chuẩn màu Deep Ocean & Avalanche -->
        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2 shadow-sm" style="width: 52px; height: 52px; background: rgba(212,238,248,0.15);">
            <i class="bi bi-houses-fill fs-4 text-white"></i>
        </div>
        <h5 class="fw-bold mb-0 text-white" style="letter-spacing: -0.5px; line-height: 1.3;">Quản lý<br>phòng trọ</h5>
    </div>

    <ul class="nav nav-pills flex-column mb-auto gap-1">
        <li class="nav-item">
            <a href="?route=dashboard" class="nav-link <?= $currentRoute === 'dashboard' ? 'active bg-white bg-opacity-25 fw-semibold' : '' ?>">
                <i class="bi bi-house-door me-2"></i> Trang chủ
            </a>
        </li>
        <li class="nav-item">
            <a href="?route=rooms" class="nav-link <?= $currentRoute === 'rooms' ? 'active bg-white bg-opacity-25 fw-semibold' : '' ?>">
                <i class="bi bi-door-open me-2"></i> Phòng trọ
            </a>
        </li>
        <li class="nav-item">
            <a href="?route=tenants" class="nav-link <?= $currentRoute === 'tenants' ? 'active bg-white bg-opacity-25 fw-semibold' : '' ?>">
                <i class="bi bi-people me-2"></i> Khách thuê
            </a>
        </li>
        <li class="nav-item">
            <a href="?route=contracts" class="nav-link <?= $currentRoute === 'contracts' ? 'active bg-white bg-opacity-25 fw-semibold' : '' ?>">
                <i class="bi bi-file-earmark-text me-2"></i> Hợp đồng
            </a>
        </li>
        <li class="nav-item">
            <a href="?route=invoices" class="nav-link <?= $currentRoute === 'invoices' ? 'active bg-white bg-opacity-25 fw-semibold' : '' ?>">
                <i class="bi bi-receipt me-2"></i> Hóa đơn
            </a>
        </li>
        <li class="nav-item">
            <a href="?route=payments" class="nav-link <?= $currentRoute === 'payments' ? 'active bg-white bg-opacity-25 fw-semibold' : '' ?>">
                <i class="bi bi-credit-card me-2"></i> Thanh toán
            </a>
        </li>
        <li class="nav-item">
            <a href="?route=reports" class="nav-link <?= $currentRoute === 'reports' ? 'active bg-white bg-opacity-25 fw-semibold' : '' ?>">
                <i class="bi bi-bar-chart-fill me-2"></i> Báo cáo
            </a>
        </li>
        <li class="nav-item">
            <a href="?route=settings" class="nav-link <?= $currentRoute === 'settings' ? 'active bg-white bg-opacity-25 fw-semibold' : '' ?>">
                <i class="bi bi-gear me-2"></i> Cài đặt
            </a>
        </li>
    </ul>

    <div class="p-3 border-top border-light border-opacity-10 mt-auto">
        <a href="?route=login" class="nav-link text-white opacity-75 d-flex align-items-center">
            <i class="bi bi-box-arrow-left me-2"></i> Đăng xuất
        </a>
    </div>
</aside>