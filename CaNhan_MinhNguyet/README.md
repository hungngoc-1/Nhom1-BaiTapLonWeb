### 5 Test Case Kiểm Thử (CRUD Bảng `services`)

**1. Test case 1 (Nhập sai - Bỏ trống tên dịch vụ):**
- **Hành động:** Để trống ô "Tên dịch vụ", nhập Đơn giá là `3500`, Đơn vị tính là `kWh` và nhấn "Thêm mới".
- **Kết quả mong đợi:** Dữ liệu không được lưu. Hệ thống hiển thị thông báo lỗi: *"Tên dịch vụ không được để trống."*

**2. Test case 2 (Nhập sai - Đơn giá bị âm):**
- **Hành động:** Nhập Tên dịch vụ là `Điện`, Đơn giá là `-5000` (số âm), Đơn vị tính là `kWh` và nhấn "Thêm mới".
- **Kết quả mong đợi:** Dữ liệu không được lưu. Hệ thống chặn lại và thông báo lỗi: *"Đơn giá phải là một số lớn hơn hoặc bằng 0."*

**3. Test case 3 (Nhập sai - Bỏ trống đơn vị tính):**
- **Hành động:** Nhập Tên dịch vụ là `Nước`, Đơn giá là `20000`, để trống ô "Đơn vị tính" và nhấn "Thêm mới".
- **Kết quả mong đợi:** Dữ liệu không được lưu. Hệ thống hiển thị thông báo lỗi: *"Đơn vị tính không được để trống."*

**4. Test case 4 (Nhập sai - Đơn giá không đúng định dạng số):**
- **Hành động:** Nhập Tên dịch vụ là `Rác`, Đơn giá là `hai mươi nghìn` (nhập chữ cái), Đơn vị tính là `Tháng` và nhấn "Thêm mới".
- **Kết quả mong đợi:** Giao diện chặn không cho submit (do thẻ input `type="number"`) hoặc Backend xử lý từ chối và báo lỗi định dạng số.

**5. Test case 5 (Nhập đúng - Dữ liệu hợp lệ):**
- **Hành động:** Nhập đầy đủ thông tin hợp lệ: Tên dịch vụ là `Internet`, Đơn giá là `100000`, Đơn vị tính là `Tháng` và nhấn "Thêm mới".
- **Kết quả mong đợi:** Lưu CSDL thành công. Hệ thống hiển thị thông báo màu xanh *"Thêm dịch vụ thành công!"* và hiển thị dịch vụ mới ở bảng danh sách bên dưới.