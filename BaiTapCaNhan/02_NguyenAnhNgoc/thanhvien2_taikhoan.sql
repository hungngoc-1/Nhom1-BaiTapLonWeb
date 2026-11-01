-- 1. Lấy danh sách tất cả người thuê (tenant)
SELECT full_name, phone, email FROM users WHERE role = 'tenant';

-- 2. Tìm kiếm thông tin khách hàng bằng số điện thoại
SELECT username, full_name, role FROM users WHERE phone = '0987654321';

-- 3. Lọc danh sách quản trị viên và chủ trọ
SELECT full_name, email, role FROM users WHERE role IN ('admin', 'landlord');