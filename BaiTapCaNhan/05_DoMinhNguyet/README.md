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
- # Báo Cáo Bài Tập 6 - PHP Hướng Đối Tượng, Composer và MVC

**Link Repository:** `[Điền link GitHub của nhóm vào đây]`

---

## I. PHẦN CÁ NHÂN: Đỗ Minh Nguyệt (Người 5)
**Nhiệm vụ:** Quản lý Đơn giá Dịch vụ (Tích hợp vào module Điện Nước)

### 1. Quá trình Refactor sang mô hình MVC
Theo yêu cầu bài tập, chức năng CRUD bảng `services` đã được bóc tách hoàn toàn:
*   **Trước khi Refactor:** Toàn bộ HTML giao diện, kết nối CSDL (PDO) và logic xử lý (Thêm/Sửa/Xóa/Validate) bị viết gộp chung vào một file duy nhất là `services.php`.
*   **Sau khi Refactor:** Mã nguồn được rã thành 3 phần riêng biệt chuẩn MVC:
    *   **Repository (`src/Repository/ServiceRepository.php`):** Chỉ chứa PDO và các câu lệnh SQL truy vấn CSDL.
    *   **Controller (`src/Controller/ServiceController.php`):** Chịu trách nhiệm nhận request, validate dữ liệu, điều phối logic và nạp View.
    *   **View (`src/View/service_list.php`):** Hoàn toàn sạch bóng code SQL, chỉ chứa HTML/CSS giao diện StayMate và hiển thị biến do Controller truyền sang.
    *   **Route:** Truy cập qua điểm vào duy nhất `public/index.php?route=services`.

<img width="1920" height="1080" alt="image" src="https://github.com/user-attachments/assets/7bcca4c6-8e65-4bd8-ba22-c78b3ccc5387" />


### 2. Kịch bản Kiểm thử (Test Cases)
Thực hiện chạy thử luồng thêm mới Dịch vụ qua Controller:
1. **Bỏ trống tên dịch vụ:** Nhập Giá `3500`, Đơn vị `kWh` -> Báo lỗi: *"Tên dịch vụ không được để trống."*
2. **Đơn giá âm:** Tên `Điện`, Giá `-5000`, Đơn vị `kWh` -> Báo lỗi: *"Đơn giá phải là một số lớn hơn hoặc bằng 0."*
3. **Bỏ trống đơn vị tính:** Tên `Nước`, Giá `20000`, Đơn vị (trống) -> Báo lỗi: *"Đơn vị tính không được để trống."*
4. **Đơn giá không phải số:** Nhập chữ vào ô giá -> Giao diện (HTML5) chặn submit, Controller từ chối.
5. **Dữ liệu hợp lệ:** Tên `Internet`, Giá `100000`, Đơn vị `Tháng` -> Lưu thành công, hiển thị thông báo xanh và reload bảng danh sách.
│   ├── Repository/     # Chứa các Repository class
│   └── views/          # Chứa giao diện HTML
└── README.md
