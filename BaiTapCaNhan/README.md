# BÀI TẬP CÁ NHÂN – NHÓM 1

> Thư mục này chứa **toàn bộ bài tập cá nhân** của các thành viên, tách riêng khỏi mã nguồn bài tập nhóm (`app/`, `database/`, `index.php`).
> Mỗi bài cá nhân là một mini-project độc lập (có `db.php` / `index.php` riêng), **không ảnh hưởng** tới hệ thống của nhóm.

| STT | Thành viên | MSSV | Thư mục | Chức năng cá nhân |
| :-: | :--- | :--- | :--- | :--- |
| 1 | Nguyễn Kim Huệ | 224001720 | [01_NguyenKimHue](01_NguyenKimHue/) | CRUD Quản lý phòng trọ |
| 2 | Nguyễn Ánh Ngọc | 224001739 | [02_NguyenAnhNgoc](02_NguyenAnhNgoc/) | CRUD Quản lý tài khoản người dùng |
| 3 | Hủng Văn Ngọc | 224001738 | [03_HungVanNgoc](03_HungVanNgoc/) | Truy vấn Hợp đồng thuê phòng |
| 4 | Dương Thị Trang | 224001754 | [04_DuongThiTrang](04_DuongThiTrang/) | Truy vấn Chỉ số điện nước |
| 5 | Đỗ Minh Nguyệt | 224001740 | [05_DoMinhNguyet](05_DoMinhNguyet/) | CRUD Danh mục dịch vụ + truy vấn Hóa đơn |

Mỗi thư mục đều có file `DiemDanh.txt` (file điểm danh của thành viên, trước đây nằm ở thư mục gốc).

---

## 1. Nguyễn Kim Huệ – Quản lý phòng trọ
| File | Nội dung |
| :--- | :--- |
| `index.php`, `db.php` | Điểm chạy & kết nối CSDL |
| `controllers/RoomController.php`, `models/RoomModel.php`, `views/rooms/*` | CRUD phòng theo mô hình MVC (thêm / sửa / xóa bằng POST / tìm kiếm) |
| `kimhue.sql` | 3 truy vấn: phòng trống, phòng theo khoảng giá, dịch vụ tính theo tháng |
| `Baocao_TestCases.md` | 5 test case (TC01–TC05) |
| `Screenshot *.png` | Ảnh kết quả chạy |

## 2. Nguyễn Ánh Ngọc – Quản lý tài khoản
| File | Nội dung |
| :--- | :--- |
| `index.php`, `UserController.php`, `UserModel.php` | Hiển thị / xóa tài khoản (MVC, xóa bằng POST) |
| `thanhvien2_taikhoan.sql` | 3 truy vấn: DS người thuê, tìm theo SĐT, DS admin/chủ trọ |
| `test_case_nguoi2.txt` | 5 test case |
| `ERD.jpeg`, `Ảnh chụp màn hình (6–8).png` | ERD cá nhân & ảnh kết quả |

## 3. Hủng Văn Ngọc – Hợp đồng
| File | Nội dung |
| :--- | :--- |
| `thanhvien3_hopdong.sql` | 3 truy vấn: DS hợp đồng (JOIN phòng + người thuê), hợp đồng đang hiệu lực, hợp đồng theo phòng |
| `ERD.png`, `Screenshot (294–296).png` | ERD cá nhân & ảnh kết quả |

## 4. Dương Thị Trang – Điện nước
| File | Nội dung |
| :--- | :--- |
| `nv4_DienNuoc.sql` | 3 truy vấn: lịch sử ghi điện nước, số tiêu thụ trong tháng, lọc theo tháng |
| `ERD.jpg`, `erd_nv4.png` | ERD cá nhân |
| `kq_select1..3_nv4.jpg` | Ảnh kết quả 3 truy vấn |

## 5. Đỗ Minh Nguyệt – Dịch vụ & Hóa đơn
| File | Nội dung |
| :--- | :--- |
| `services.php`, `db.php` | CRUD danh mục dịch vụ (Điện, Nước, Internet…) |
| `BT4.sql` | 3 truy vấn: hóa đơn chưa thanh toán, chi tiết hóa đơn, tổng doanh thu tháng |
| `README.md` | 5 test case chi tiết |
| `ERD ca nhan.jpg`, `SQL1_B4.jpg`, `SQL2_B4.jpg` | ERD cá nhân & ảnh kết quả |
