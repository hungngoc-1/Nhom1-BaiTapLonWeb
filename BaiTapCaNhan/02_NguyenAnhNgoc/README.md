# Báo cáo Bài tập cá nhân (Bài 6)
**Sinh viên thực hiện:** Trần Ánh Ngọc (02_NguyenAnhNgoc)

## Ghi chú quá trình Refactor CRUD sang MVC:
- **Trước khi refactor:** Toàn bộ code xử lý cơ sở dữ liệu (các câu lệnh SQL SELECT, INSERT, UPDATE, DELETE) và code hiển thị giao diện (HTML) bị gộp chung vào một chỗ. Mã nguồn lộn xộn, khó bảo trì và không đúng chuẩn MVC.
- **Sau khi refactor:** Em đã tách thành công module Quản lý Tài khoản (bảng `users`) ra 3 phần độc lập:
  1. **Model/Repository (`UserRepository.php`):** Chuyên chịu trách nhiệm kết nối CSDL và chạy các truy vấn SQL. Hoàn toàn không chứa mã HTML.
  2. **Controller (`UserController.php`):** Đóng vai trò điều hướng, nhận dữ liệu từ Repository rồi truyền sang View. Không chứa HTML dài hay SQL.
  3. **View (`views/index.php`):** Tách biệt hoàn toàn phần giao diện. Chỉ dùng PHP để in dữ liệu ra bảng, tuyệt đối không chứa bất kỳ câu lệnh SQL nào.