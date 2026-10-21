# Nhom1-BaiTapLonWeb
CHỦ ĐỀ 10: HỆ THỐNG QUẢN LÝ NHÀ TRỌ VÀ HÓA ĐƠN ĐIỆN NƯỚC
HỌ TÊN SV: Đỗ Minh Nguyệt MSSV: 224001740
HỌ TÊN SV: Dương Thị Trang MSSV: 224001754
HỌ TÊN SV: Nguyễn Kim Huệ MSSV: 224001720
HỌ TÊN SV: Nguyễn Ánh Ngọc MSSV: 224001739
HỌ TÊN SV: Hùng Văn Ngọc MSSV: 224001738
## BÁO CÁO BÀI TẬP NHÓM BUỔI 3

### 1. Phân công thành viên commit
- **Hủng Ngọc (Nhóm trưởng - Thành viên 3)**: Thiết lập Core MVC, tích hợp form đăng nhập an toàn, xử lý Validation phía server (AuthController) và cập nhật README.
- **Kim Huệ (Thành viên 1)**: Thiết kế giao diện Master Layout, hoàn thiện wireframe trang chủ Dashboard và form Báo cáo.
- **Ánh Ngọc (Thành viên 2)**: Vẽ wireframe và xây dựng giao diện (UI) cho các form Quản lý phòng, Người thuê và Hợp đồng.
- **Dương Trang (Thành viên 4)**: Cập nhật quy tắc và backend cho form ghi Chỉ số Điện/Nước và Lập Hóa đơn.
- **Minh Nguyệt (Thành viên 5)**: Rà soát tổng thể các quy tắc validation, cấu trúc Database và kiểm thử các form chính.

### 2. Danh sách Route dự kiến cho chức năng chính
- `GET /login` | `POST /login`: Hiển thị form / Xử lý đăng nhập
- `GET /rooms` | `POST /rooms/store`: Hiển thị DS phòng / Thêm phòng
- `GET /contracts` | `POST /contracts/store`: Quản lý hợp đồng
- `POST /utilities/store`: Nhập chỉ số điện nước
- `POST /invoices/store`: Lập hóa đơn

### 3. Quy tắc Validation (Phía Server) và Nghiệp vụ
Tất cả các form đều có input ẩn `csrf_token` và kiểm tra `hash_equals()` tại server để chống tấn công CSRF. Mọi chuỗi in ra View đều dùng `htmlspecialchars()` chống XSS.

**a. Form Đăng nhập**
- `username`: Bắt buộc, không rỗng, độ dài 3-50 ký tự. Giữ lại giá trị khi nhập sai mật khẩu.
- `password`: Bắt buộc, không rỗng.

**b. Form Thêm Hợp Đồng**
- `start_date`, `end_date`: Phải đúng định dạng ngày.
- **Nghiệp vụ:** `start_date` phải trước `end_date`. Không được tạo hợp đồng có thời gian chồng lấn với hợp đồng đang hiệu lực của phòng đó.

**c. Form Ghi Điện/Nước**
- `elec_new`, `water_new`: Bắt buộc là số nguyên >= 0.
- **Nghiệp vụ:** Chỉ số mới không được nhỏ hơn chỉ số của kỳ trước. Không nhập trùng kỳ.