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
### 4. Mô tả Cơ sở dữ liệu
Hệ thống sử dụng CSDL MySQL, được chuẩn hóa thành 7 bảng với **Engine InnoDB** để hỗ trợ khóa ngoại và đảm bảo tính toàn vẹn tham chiếu. Các ràng buộc `UNIQUE` được áp dụng chặt chẽ để chống trùng lặp dữ liệu:

* **users**: Quản lý tài khoản đăng nhập và phân quyền 3 vai trò (admin, landlord, tenant). Có ràng buộc `UNIQUE` trên `username` và `email`.
* **rooms**: Quản lý danh sách và trạng thái phòng (available/rented). Có ràng buộc `UNIQUE` trên `room_number`.
* **services**: Cấu hình các danh mục dịch vụ phát sinh (Điện, Nước, Rác, Internet...) và đơn giá.
* **contracts**: Quản lý hợp đồng thuê phòng, liên kết khách thuê với phòng.
* **utilities_readings**: Lưu lịch sử chỉ số điện nước. Khóa phức hợp `UNIQUE(room_id, month_year)` đảm bảo mỗi phòng chỉ có tối đa một bộ chỉ số trong một tháng.
* **invoices**: Lưu tổng quát hóa đơn theo kỳ và trạng thái thanh toán. Khóa `UNIQUE(room_id, month_year)` giúp ngăn chặn lập trùng hóa đơn.
* **invoice_details**: Bảng lưu chi tiết hóa đơn (tiền phòng, điện, nước). Lưu cứng đơn giá tại thời điểm lập (`applied_price`), giúp tổng tiền hóa đơn cũ không bị thay đổi nếu admin cập nhật bảng giá dịch vụ sau này.

### 5. Ảnh sơ đồ ERD
*(Sơ đồ quan hệ thực thể của cơ sở dữ liệu)*
![Sơ đồ ERD]<img width="1280" height="637" alt="image" src="https://github.com/user-attachments/assets/3bd1b7cf-e51a-42c8-8ad1-5e48d8a44762" />

### 6. Hướng dẫn chạy script cài đặt Database
Thực hiện các bước sau trên **phpMyAdmin** để khởi tạo cơ sở dữ liệu và nạp dữ liệu mẫu:

1. Mở phpMyAdmin (ví dụ: `http://localhost/phpmyadmin`). Không cần tạo database thủ công.
2. Mở tab **Import (Nhập)** ở thanh menu phía trên.
3. Nhấn **Choose File (Chọn tệp)**, trỏ tới file `database/schema.sql` trong thư mục dự án.
4. Kéo xuống dưới cùng nhấn **Go (Thực hiện)**. Script sẽ tự động tạo database `quan_ly_nha_tro` và khởi tạo cấu trúc 7 bảng.
5. Lặp lại thao tác: Vào tab **Import (Nhập)**, tải lên file `database/seed.sql` và nhấn **Go**. Script sẽ nạp dữ liệu mẫu (tài khoản, phòng, hợp đồng, hóa đơn) để sẵn sàng test hệ thống.
6. Cấu hình kết nối PDO trong file config của dự án (nếu cần):
   - Database name: `quan_ly_nha_tro`
   - Encoding: `utf8mb4`

# Nguyễn Kim Huệ
- **Chức năng phụ trách:** 
  - Đăng nhập / Đăng xuất hệ thống (Bảo mật bằng PDO Prepared Statement).
  - Quản lý giao diện và danh sách Tòa nhà.
  - Trang Tổng quan (Thống kê tỷ lệ lấp đầy phòng, doanh thu thực thu, công nợ và hợp đồng trực tiếp từ CSDL).
- **Danh sách Route:**
  - `index.php?route=auth/login`: Trang đăng nhập
  - `index.php?route=auth/logout`: Đăng xuất, hủy session
  - `index.php?route=dashboard`: Trang quản lý Tòa nhà
  - `index.php?route=overview`: Trang Tổng quan hệ thống