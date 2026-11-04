<?php
/**
 * COMPONENT THÔNG BÁO TOAST NỔI (UNIVERSAL TOAST NOTIFICATION)
 * Dùng chung cho toàn bộ hệ thống StayMate (Admin, Quản lý, Khách thuê)
 * Tự động bắt flash message khi Đăng nhập, Thêm, Sửa, Xóa, Cập nhật...
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bắt thông báo THÀNH CÔNG (Success)
$cToastSuccess = '';
foreach ([
    $_SESSION['flash_success'] ?? null,
    $_SESSION['room_flash_success'] ?? null,
    $_SESSION['tenant_flash_success'] ?? null,
    $_SESSION['acc_flash_success'] ?? null,
    $flash_success ?? null,
    $flashSuccess ?? null,
    $success ?? null,
    (isset($msgType) && $msgType === 'success') ? ($msg ?? null) : null,
    (isset($_GET['msg']) && $_GET['msg'] === 'created') ? 'Thao tác thêm mới thành công!' : null,
    (isset($_GET['msg']) && $_GET['msg'] === 'updated') ? 'Cập nhật dữ liệu thành công!' : null,
    (isset($_GET['msg']) && $_GET['msg'] === 'deleted') ? 'Xóa bản ghi thành công!' : null,
] as $candidate) {
    if (!empty($candidate) && is_string($candidate) && trim($candidate) !== '') {
        $cToastSuccess = trim($candidate);
        break;
    }
}

// Bắt thông báo LỖI (Error)
$cToastError = '';
foreach ([
    $_SESSION['flash_error'] ?? null,
    $_SESSION['room_flash_error'] ?? null,
    $_SESSION['tenant_flash_error'] ?? null,
    $_SESSION['acc_flash_error'] ?? null,
    $flash_error ?? null,
    $flashError ?? null,
    $error ?? null,
    (isset($msgType) && ($msgType === 'danger' || $msgType === 'error')) ? ($msg ?? null) : null,
] as $candidate) {
    if (!empty($candidate) && is_string($candidate) && trim($candidate) !== '') {
        $cToastError = trim($candidate);
        break;
    }
}

// Bắt thông báo CẢNH BÁO / LƯU Ý (Warning)
$cToastWarning = '';
foreach ([
    $_SESSION['flash_warning'] ?? null,
    $flash_warning ?? null,
    (isset($msgType) && $msgType === 'warning') ? ($msg ?? null) : null,
] as $candidate) {
    if (!empty($candidate) && is_string($candidate) && trim($candidate) !== '') {
        $cToastWarning = trim($candidate);
        break;
    }
}

// Xóa flash session sau khi đã nạp vào component để không lặp lại khi refresh
unset(
    $_SESSION['flash_success'], 
    $_SESSION['room_flash_success'], 
    $_SESSION['tenant_flash_success'], 
    $_SESSION['acc_flash_success'],
    $_SESSION['flash_error'], 
    $_SESSION['room_flash_error'], 
    $_SESSION['tenant_flash_error'], 
    $_SESSION['acc_flash_error'],
    $_SESSION['flash_warning']
);
?>
<!-- STYLES CHO UNIVERSAL TOAST NOTIFICATION -->
<style>
#appToastContainer {
    position: fixed;
    top: 22px;
    right: 24px;
    z-index: 999999;
    display: flex;
    flex-direction: column;
    gap: 12px;
    pointer-events: none;
    max-width: 440px;
    width: calc(100vw - 32px);
}
.app-toast-card {
    pointer-events: auto;
    background: #ffffff;
    border-radius: 18px;
    padding: 14px 18px;
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.14), 0 4px 12px rgba(0, 0, 0, 0.06);
    display: flex;
    align-items: flex-start;
    gap: 14px;
    position: relative;
    overflow: hidden;
    animation: appToastSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    transition: all 0.3s ease;
    border: 1.5px solid #e2e8f0;
    font-family: inherit;
}
.app-toast-card.toast-success {
    border-color: #a7f3d0;
    background: linear-gradient(135deg, #ffffff 0%, #f0fdf4 100%);
    box-shadow: 0 16px 40px rgba(16, 185, 129, 0.22), 0 4px 12px rgba(0, 0, 0, 0.05);
}
.app-toast-card.toast-error {
    border-color: #fecdd3;
    background: linear-gradient(135deg, #ffffff 0%, #fff1f2 100%);
    box-shadow: 0 16px 40px rgba(239, 68, 68, 0.22), 0 4px 12px rgba(0, 0, 0, 0.05);
}
.app-toast-card.toast-warning {
    border-color: #fde68a;
    background: linear-gradient(135deg, #ffffff 0%, #fffbeb 100%);
    box-shadow: 0 16px 40px rgba(245, 158, 11, 0.22), 0 4px 12px rgba(0, 0, 0, 0.05);
}
.app-toast-card.toast-info {
    border-color: #bae6fd;
    background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 100%);
    box-shadow: 0 16px 40px rgba(14, 165, 233, 0.22), 0 4px 12px rgba(0, 0, 0, 0.05);
}
.app-toast-icon-box {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.toast-success .app-toast-icon-box { background: #dcfce7; color: #15803d; }
.toast-error .app-toast-icon-box { background: #fee2e2; color: #b91c1c; }
.toast-warning .app-toast-icon-box { background: #fef3c7; color: #b45309; }
.toast-info .app-toast-icon-box { background: #e0f2fe; color: #0369a1; }

.app-toast-content {
    flex-grow: 1;
    padding-top: 2px;
}
.app-toast-title {
    font-weight: 800;
    font-size: 0.95rem;
    line-height: 1.2;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.toast-success .app-toast-title { color: #166534; }
.toast-error .app-toast-title { color: #991b1b; }
.toast-warning .app-toast-title { color: #92400e; }
.toast-info .app-toast-title { color: #075985; }

.app-toast-message {
    font-size: 0.86rem;
    color: #334155;
    line-height: 1.45;
    font-weight: 600;
}
.app-toast-close {
    background: transparent;
    border: none;
    color: #94a3b8;
    font-size: 1.2rem;
    line-height: 1;
    cursor: pointer;
    padding: 4px 6px;
    border-radius: 8px;
    transition: all 0.2s;
    align-self: flex-start;
}
.app-toast-close:hover {
    color: #334155;
    background: rgba(0, 0, 0, 0.06);
}
.app-toast-progress-bar {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3.5px;
    width: 100%;
    animation: appToastProgress 4.5s linear forwards;
}
.toast-success .app-toast-progress-bar { background: #22c55e; }
.toast-error .app-toast-progress-bar { background: #ef4444; }
.toast-warning .app-toast-progress-bar { background: #f59e0b; }
.toast-info .app-toast-progress-bar { background: #0ea5e9; }

@keyframes appToastSlideIn {
    from {
        transform: translateX(110%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
@keyframes appToastSlideOut {
    from {
        transform: translateX(0);
        opacity: 1;
        max-height: 160px;
        margin-bottom: 0;
    }
    to {
        transform: translateX(120%);
        opacity: 0;
        max-height: 0;
        padding-top: 0;
        padding-bottom: 0;
        margin-bottom: -12px;
    }
}
@keyframes appToastProgress {
    from { width: 100%; }
    to { width: 0%; }
}
</style>

<div id="appToastContainer"></div>

<script>
window.showAppToast = function(type, message, title = '') {
    if (!message) return;
    const container = document.getElementById('appToastContainer');
    if (!container) return;

    const validTypes = ['success', 'error', 'warning', 'info'];
    if (!validTypes.includes(type)) type = 'info';

    let defaultTitle = 'Thông báo hệ thống';
    let iconSvg = '';
    if (type === 'success') {
        defaultTitle = 'Thành công!';
        iconSvg = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
    } else if (type === 'error') {
        defaultTitle = 'Có lỗi xảy ra!';
        iconSvg = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';
    } else if (type === 'warning') {
        defaultTitle = 'Cảnh báo!';
        iconSvg = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>';
    } else {
        defaultTitle = 'Thông tin';
        iconSvg = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>';
    }

    const toastCard = document.createElement('div');
    toastCard.className = `app-toast-card toast-${type}`;
    toastCard.innerHTML = `
        <div class="app-toast-icon-box">
            ${iconSvg}
        </div>
        <div class="app-toast-content">
            <div class="app-toast-title">
                <span>${title || defaultTitle}</span>
            </div>
            <div class="app-toast-message">${message}</div>
        </div>
        <button type="button" class="app-toast-close" title="Đóng">&times;</button>
        <div class="app-toast-progress-bar"></div>
    `;

    const closeBtn = toastCard.querySelector('.app-toast-close');
    const dismissToast = () => {
        toastCard.style.animation = 'appToastSlideOut 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards';
        setTimeout(() => {
            if (toastCard.parentNode) {
                toastCard.parentNode.removeChild(toastCard);
            }
        }, 300);
    };

    closeBtn.addEventListener('click', dismissToast);

    // Tự động đóng sau 4.5 giây
    const autoDismissTimer = setTimeout(dismissToast, 4500);

    // Tạm dừng timer khi di chuột vào
    toastCard.addEventListener('mouseenter', () => {
        clearTimeout(autoDismissTimer);
        const bar = toastCard.querySelector('.app-toast-progress-bar');
        if (bar) bar.style.animationPlayState = 'paused';
    });

    container.appendChild(toastCard);
};

// Tự động hiển thị khi trang tải xong nếu server có flash message
(function() {
    function fireToasts() {
        <?php if (!empty($cToastSuccess)): ?>
            window.showAppToast('success', <?= json_encode($cToastSuccess, JSON_UNESCAPED_UNICODE) ?>, 'Thành công!');
        <?php endif; ?>
        <?php if (!empty($cToastError)): ?>
            window.showAppToast('error', <?= json_encode($cToastError, JSON_UNESCAPED_UNICODE) ?>, 'Có lỗi xảy ra');
        <?php endif; ?>
        <?php if (!empty($cToastWarning)): ?>
            window.showAppToast('warning', <?= json_encode($cToastWarning, JSON_UNESCAPED_UNICODE) ?>, 'Lưu ý');
        <?php endif; ?>
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fireToasts);
    } else {
        fireToasts();
    }
})();
</script>
