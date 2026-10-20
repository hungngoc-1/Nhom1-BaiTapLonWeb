<footer class="bg-white text-center text-muted py-3 mt-auto border-top small">
            Hệ thống Quản lý Nhà trọ và Hóa đơn Điện nước — Bài tập lớn Lập trình Web (Nhóm 10)
        </footer>
    </div>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const sidebar = document.getElementById('sidebarMenu');
    document.getElementById('sidebarToggle')?.addEventListener('click', () => sidebar.classList.toggle('show'));
    document.getElementById('closeSidebar')?.addEventListener('click', () => sidebar.classList.remove('show'));
</script>
</body>
</html>