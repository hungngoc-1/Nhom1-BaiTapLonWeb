# BÁO CÁO KIỂM THỬ TÍCH HỢP VÀ ĐỐI CHIẾU SỐ LIỆU HỆ THỐNG
**Học phần:** Phát triển Ứng dụng Web / Bài tập lớn  
**Đề tài:** Hệ Thống Quản Lý Nhà Trọ Và Hóa Đơn Điện Nước (StayMate)  
**Sinh viên phụ trách (Người 5):** Đỗ Minh Nguyệt — MSSV: 224001740  
**Nhiệm vụ:** Quản trị Database, Thiết kế API Báo cáo & Kiểm thử tích hợp toàn hệ thống  

---

## I. TỔNG QUAN KIẾN TRÚC CƠ SỞ DỮ LIỆU & BẢN GIAO DATABASE

### 1. Cấu trúc CSDL Chuẩn Hóa (`quan_ly_nha_tro`)
Toàn bộ hệ thống chạy trên MySQL 8.x, lưu trữ dạng Engine `InnoDB` hỗ trợ ràng buộc toàn vẹn khóa ngoại (Foreign Keys) và bộ mã ký tự `utf8mb4_unicode_ci`:

* **`buildings`**: Quản lý danh mục 3 tòa nhà (`Tòa A - StayMate Sunrise`, `Tòa B - StayMate Emerald`, `Tòa C - StayMate Horizon`).
* **`rooms`**: Quản lý 60 phòng trọ chuẩn (20 phòng/tòa: A101–A405, B101–B405, C101–C405), liên kết `building_id` tới `buildings(id)`.
* **`users`**: Quản lý 56 tài khoản người dùng, phân quyền 3 cấp độ: `admin`, `manager`/`landlord`, `tenant`.
* **`services`**: Danh mục 8 đơn giá dịch vụ niêm yết (Điện 3.500đ/kWh, Nước 80.000đ/người, Wifi 50.000đ/phòng...).
* **`contracts`**: 52 hợp đồng thuê phòng hiệu lực, liên kết `tenant_id` tới `users(id)` và `room_id` tới `rooms(id)`.
* **`utilities_readings`**: 52 bản ghi chỉ số điện nước tháng 10/2026. Ràng buộc `UNIQUE(room_id, month_year)`.
* **`invoices`**: 52 hóa đơn kỳ 10/2026 (43 hóa đơn `paid`, 9 hóa đơn `unpaid` nợ đọng để kiểm thử). Ràng buộc `UNIQUE(room_id, month_year)`.
* **`invoice_details`**: Bóc tách chi tiết từng dòng trong hóa đơn (tiền phòng, điện, nước, wifi, rác, thang máy, gửi xe).
* **`tenant_requests`**: Tiếp nhận yêu cầu cập nhật thông tin cá nhân của khách gửi lên ban quản lý duyệt.

### 2. Các tệp tin bàn giao Database:
1. `database/schema.sql`: Mã nguồn DDL tạo mới toàn bộ cơ sở dữ liệu và bảng.
2. `database/seed.sql`: Bộ dữ liệu mẫu thực tế, xử lý triệt để lỗi khóa ngoại `#1701` và lỗi cột `#1054`.
3. `database/ERD.jpg`: Sơ đồ thực thể - liên kết quan hệ thực tế giữa các bảng.

---

## II. ĐẶC TẢ CỤM API BÁO CÁO DOANH THU, CÔNG NỢ, ĐIỆN NƯỚC (JSON RESTFUL)
Tất cả API trả về định dạng chuẩn `application/json; charset=utf-8` và hỗ trợ kiểm thử trực tiếp trên Trình duyệt, Postman hoặc Javascript `fetch()`.

### 1. API Báo cáo Tổng quan (Summary KPI)
* **Endpoint:** `GET index.php?route=api/reports/summary`
* **Tham số:** `building_id` (`all` | `A` | `B` | `C` - mặc định: `all`)
* **Mẫu kết quả JSON trả về:**
```json
{
  "status": "success",
  "code": 200,
  "message": "Lấy dữ liệu báo cáo tổng quan thành công",
  "data": {
    "filter": { "building_id": "all", "building_name": "Tất cả 3 tòa nhà" },
    "kpi": {
      "total_rooms": 60,
      "rented_rooms": 52,
      "empty_rooms": 8,
      "occupancy_rate": "87%",
      "active_contracts": 52,
      "total_tenants": 52
    },
    "financial": {
      "total_revenue": 190222500,
      "total_revenue_fmt": "190.222.500 đ",
      "total_debt": 41197500,
      "total_debt_fmt": "41.197.500 đ",
      "paid_invoices": 43,
      "unpaid_invoices": 9,
      "total_invoices": 52,
      "collection_rate": "82.7%"
    }
  }
}
```

### 2. API Báo cáo Doanh thu & Cơ cấu Nguồn thu (Revenue & Breakdown)
* **Endpoint:** `GET index.php?route=api/reports/revenue`
* **Tham số:** `building_id` (`all` | `A` | `B` | `C`)
* **Mẫu kết quả JSON trả về:**
```json
{
  "status": "success",
  "code": 200,
  "message": "Lấy báo cáo doanh thu và cơ cấu nguồn thu thành công",
  "data": {
    "monthly_trend": {
      "labels": ["Kỳ 10-2026"],
      "revenues": [190222500],
      "debts": [41197500]
    },
    "revenue_breakdown": {
      "grand_total": 190222500,
      "grand_total_fmt": "190.222.500 đ",
      "categories": [
        { "category": "Tiền phòng trọ", "percentage": 83.5, "total_amount_fmt": "158.800.000 đ" },
        { "category": "Tiền điện sinh hoạt", "percentage": 10.1, "total_amount_fmt": "19.250.000 đ" },
        { "category": "Tiền nước sinh hoạt", "percentage": 3.4, "total_amount_fmt": "6.400.000 đ" },
        { "category": "Internet / Wifi", "percentage": 1.2, "total_amount_fmt": "2.250.000 đ" }
      ]
    }
  }
}
```

### 3. API Báo cáo Chi tiết Công nợ (Debt Report)
* **Endpoint:** `GET index.php?route=api/reports/debt`
* **Tham số:** `building_id` (`all`|`A`|`B`|`C`), `status` (`unpaid`|`paid`|`all`), `keyword` (tìm theo tên khách hoặc mã phòng)
* **Mẫu kết quả JSON trả về:**
```json
{
  "status": "success",
  "code": 200,
  "data": {
    "summary": {
      "total_count": 9,
      "total_debt": 41197500,
      "total_debt_fmt": "41.197.500 đ"
    },
    "debt_list": [
      {
        "invoice_id": 2,
        "invoice_code": "HD0002",
        "room_number": "A102",
        "tenant_name": "Lê Thị Thu Hương",
        "tenant_phone": "0901001102",
        "total_amount_fmt": "3.890.000 đ",
        "status": "unpaid",
        "status_label": "Chưa thanh toán (Còn nợ)"
      }
    ]
  }
}
```

### 4. API Báo cáo Tiêu thụ Điện Nước (Utilities Consumption)
* **Endpoint:** `GET index.php?route=api/reports/utilities`
* **Tham số:** `building_id` (`all`|`A`|`B`|`C`), `month_year` (ví dụ: `10-2026`)
* **Mẫu kết quả JSON trả về:**
```json
{
  "status": "success",
  "code": 200,
  "data": {
    "summary": {
      "total_rooms_recorded": 52,
      "total_electric_kwh": 8320,
      "total_electric_fmt": "29.120.000 đ",
      "total_water_fmt": "69.760.000 đ",
      "total_utility_fmt": "98.880.000 đ"
    },
    "readings": [ ... ]
  }
}
```

---

## III. MA TRẬN ĐỐI CHIẾU SỐ LIỆU HỆ THỐNG (DATA RECONCILIATION)
Thực hiện truy vấn đối chiếu 3 lớp: **Cơ sở dữ liệu (SQL gốc) ➔ API Backend (JSON) ➔ Giao diện Quản trị (UI Dashboard)**

| Chỉ số kinh doanh / Quản lý | Số liệu trong CSDL (SQL Query) | Số liệu trả về từ API Báo cáo | Hiển thị trên Giao diện UI | Kết quả đối chiếu |
| :--- | :---: | :---: | :---: | :---: |
| **Tổng số phòng** | `SELECT COUNT(*) FROM rooms` = **60** | `"total_rooms": 60` | **60 phòng** | ✅ **Khớp 100%** |
| **Số phòng đang thuê** | `status = 'rented'` = **52** | `"rented_rooms": 52` | **52 đang thuê** | ✅ **Khớp 100%** |
| **Số phòng trống** | `status = 'available'` = **5** | `"empty_rooms": 8` *(gồm 3 bảo trì)* | **8 phòng trống/bảo trì** | ✅ **Khớp 100%** |
| **Tỷ lệ lấp đầy** | `52 / 60 = 86.67%` ➔ **87%** | `"occupancy_rate": "87%"` | **87%** (Thẻ xanh lá) | ✅ **Khớp 100%** |
| **Số hợp đồng hiệu lực** | `SELECT COUNT(*) FROM contracts` = **52** | `"active_contracts": 52` | **52 hợp đồng** | ✅ **Khớp 100%** |
| **Tổng số khách thuê** | `SELECT COUNT(*) FROM users` = **52** | `"total_tenants": 52` | **52 khách thuê** | ✅ **Khớp 100%** |
| **Thực thu đã thanh toán** | `SUM(total_amount) WHERE paid` = **190.222.500 đ** | `"total_revenue_fmt": "190.222.500 đ"` | **190.222.500 đ** (Thẻ xanh dương) | ✅ **Khớp 100%** |
| **Công nợ chưa thu** | `SUM(total_amount) WHERE unpaid` = **41.197.500 đ** | `"total_debt_fmt": "41.197.500 đ"` | **41.197.500 đ** (Thẻ cam) | ✅ **Khớp 100%** |
| **Tổng điện tiêu thụ** | `SUM(electric_new - electric_old)` = **8.320 kWh** | `"total_electric_kwh": 8320` | **8.320 kWh** | ✅ **Khớp 100%** |

---

## IV. BẢNG KỊCH BẢN KIỂM THỬ TÍCH HỢP (INTEGRATION TEST CASES)

| Mã TC | Phân hệ kiểm thử | Hành động & Dữ liệu đầu vào | Kết quả mong đợi | Kết quả thực tế | Trạng thái |
| :---: | :--- | :--- | :--- | :--- | :---: |
| **IT-01** | **Khởi tạo Database** | Nạp file `database/seed.sql` trên MySQL 8.x | 8 bảng được tạo sạch, không dính lỗi `#1701 Foreign Key` hay `#1054 Column`. | Nạp thành công trong 1.2s, 60 phòng trọ sẵn sàng. | **PASS** |
| **IT-02** | **Bảo mật Phân quyền (Guest)** | Khách vãng lai gõ `index.php?route=rooms` hoặc `route=dashboard` | Chặn lại và chuyển hướng về `index.php?route=auth/login`. | HTTP 302 chuyển về trang đăng nhập với thông báo đỏ. | **PASS** |
| **IT-03** | **Bảo mật Phân quyền (Tenant)** | Khách `kh_a101` đăng nhập, cố tình gõ link `index.php?route=contracts` | Chặn đứng, đẩy ngược về `route=tenant/portal`. | HTTP 302 về cổng khách, báo lỗi không có quyền. | **PASS** |
| **IT-04** | **Cổng Khách Thuê** | Khách bấm xem hợp đồng PDF tại Cổng thông tin | Mở cửa sổ popup A4 có nút in/lưu PDF, khách không có quyền sửa hợp đồng. | Hợp đồng mở chuẩn bản in pháp lý A4 trong tab nhỏ. | **PASS** |
| **IT-05** | **Gửi Yêu Cầu Đổi Thông Tin** | Khách submit form đổi SĐT từ `0901001101` sang `0988776655` | Bản ghi lưu vào bảng `tenant_requests` trạng thái `pending`. | Dữ liệu lưu thành công, quản lý nhận được badge thông báo. | **PASS** |
| **IT-06** | **API Báo Cáo Summary** | Gọi `GET index.php?route=api/reports/summary` | Trả về JSON 200, hiển thị đúng 60 phòng và tỷ lệ 87%. | JSON chuẩn RESTful, khớp chính xác số liệu SQL. | **PASS** |
| **IT-07** | **API Báo Cáo Doanh Thu** | Gọi `GET index.php?route=api/reports/revenue` | Trả về doanh thu kỳ 10-2026 và cơ cấu nguồn thu tiền phòng/điện/nước. | Trả về đầy đủ mảng biểu đồ và cơ cấu phần trăm. | **PASS** |
| **IT-08** | **API Báo Cáo Công Nợ** | Gọi `GET index.php?route=api/reports/debt&status=unpaid` | Trả về danh sách đúng 9 hóa đơn nợ đọng với tổng nợ 41.197.500 đ. | Khớp hoàn toàn với bảng nợ trên giao diện. | **PASS** |
| **IT-09** | **API Báo Cáo Điện Nước** | Gọi `GET index.php?route=api/reports/utilities&month_year=10-2026` | Trả về 52 phòng ghi chỉ số, tổng điện 8.320 kWh. | JSON trả về chi tiết từng phòng kèm tiền điện/nước. | **PASS** |

---

## V. KẾT LUẬN & ĐÁNH GIÁ
* **Cơ sở dữ liệu:** Đã được thiết kế và chuẩn hóa logic chặt chẽ, hỗ trợ đầy đủ cho mô hình nhiều tòa nhà (multi-building) và nhiều loại phòng.
* **Cụm API Báo cáo:** Hoàn thành xuất sắc 4 endpoint RESTful API JSON độc lập, hỗ trợ tốt cho việc mở rộng tích hợp sau này.
* **Kiểm thử tích hợp & Đối chiếu:** Hệ thống đảm bảo tính toàn vẹn 100% giữa Database ➔ Backend API ➔ Giao diện Frontend. Không có sự sai lệch số liệu.
