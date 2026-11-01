# Báo cáo Kiểm thử (Test Cases) - Chức năng Quản lý Phòng trọ
**Thành viên:** Nguyễn Kim Huệ - Mã SV: 224001720

| STT | Chức năng kiểm thử | Dữ liệu đầu vào (Test Data) | Thao tác thực hiện | Kết quả mong đợi (Expected Result) | Trạng thái |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **TC01** | **Thêm phòng hợp lệ** (Đúng) | Số phòng: `P103`<br>Giá: `2500000`<br>Trạng thái: `available` | Điền thông tin vào form Thêm và bấm **Lưu** | Thêm thành công, hiển thị phòng `P103` lên danh sách. | **Pass** |
| **TC02** | **Thêm phòng thiếu dữ liệu** (Sai) | Số phòng: *(Để trống)*<br>Giá: `3000000` | Bấm nút **Lưu** khi ô số phòng đang bỏ trống | Trình duyệt chặn lại và báo lỗi (do thuộc tính `required`), không lưu vào CSDL. | **Pass** |
| **TC03** | **Sửa thông tin phòng** (Đúng) | Sửa giá phòng `P101`<br>Thành `1.800.000` | Bấm **Sửa**, thay đổi giá tiền rồi bấm **Cập nhật** | Cập nhật thành công, bảng hiển thị giá mới chính xác. | **Pass** |
| **TC04** | **Tìm kiếm phòng** (Đúng) | Nhập từ khóa: `P101`<br>vào ô tìm kiếm | Gõ `P101` vào ô tìm kiếm và bấm **Tìm kiếm** | Hệ thống lọc và chỉ hiển thị đúng dòng chứa phòng `P101`. | **Pass** |
| **TC05** | **Xóa phòng bảo mật POST** (Úng) | Chọn phòng bất kỳ | Bấm **Xóa**, hộp thoại xác nhận hiện lên -> bấm **OK** | Request gửi qua **POST**, bản ghi bị xóa khỏi CSDL, bảng làm mới không còn phòng đó. | **Pass** |