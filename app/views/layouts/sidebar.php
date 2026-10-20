<?php $currentRoute = $_GET['route'] ?? 'dashboard'; ?>
<aside class="sidebar d-flex flex-column flex-shrink-0" id="sidebarMenu">
    <div class="p-3 d-flex align-items-center justify-content-between border-bottom border-secondary">
        <a href="?route=dashboard" class="text-white text-decoration-none d-flex align-items-center">
            <i class="bi bi-buildings-fill fs-3 text-info me-2"></i>
            <span class="fs-5 fw-bold">NHÀ TRỌ N10</span>
        </a>
        <button class="btn btn-sm btn-outline-light d-lg-none" id="closeSidebar"><i class="bi bi-x-lg"></i></button>
    </div>

    <div class="px-3 py-2 text-uppercase text-secondary fw-bold" style="font-size: 0.72rem;">Quản lý chung</div>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="?route=dashboard" class="nav-link <?= $currentRoute === 'dashboard' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i> Tổng quan & Công nợ
            </a>
        </li>
        <li class="nav-item">
            <a href="?route=rooms" class="nav-link <?= $currentRoute === 'rooms' ? 'active' : '' ?>">
                <i class="bi bi-door-open"></i> Quản lý Phòng
            </a>
        </li>
        <li class="nav-item">
            <a href="?route=tenants" class="nav-link <?= $currentRoute === 'tenants' ? 'active' : '' ?>">
                <i class="bi bi-people"></i> Hồ sơ Người thuê
            </a>
        </li>
        <li class="nav-item">
            <a href="?route=contracts" class="nav-link <?= $currentRoute === 'contracts' ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-text"></i> Hợp đồng thuê
            </a>
        </li>

        <div class="px-3 py-2 mt-2 text-uppercase text-secondary fw-bold" style="font-size: 0.72rem;">Tài chính & Dịch vụ</div>
        <li class="nav-item">
            <a href="?route=utilities" class="nav-link <?= $currentRoute === 'utilities' ? 'active' : '' ?>">
                <i class="bi bi-lightning-charge"></i> Chỉ số Điện / Nước
            </a>
        </li>
        <li class="nav-item">
            <a href="?route=invoices" class="nav-link <?= $currentRoute === 'invoices' ? 'active' : '' ?>">
                <i class="bi bi-receipt"></i> Lập & Tra cứu Hóa đơn
            </a>
        </li>
        <li class="nav-item">
            <a href="?route=my-invoices" class="nav-link <?= $currentRoute === 'my-invoices' ? 'active' : '' ?>">
                <i class="bi bi-person-badge"></i> Hóa đơn của tôi
            </a>
        </li>
    </ul>

    <div class="p-3 border-top border-secondary text-center text-secondary small">
        PHP 8.x • MySQL • PDO • MVC
    </div>
</aside>