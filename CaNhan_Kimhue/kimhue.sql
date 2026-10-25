-- 1. Lấy danh sách các phòng đang trống để hiển thị cho khách thuê
SELECT room_number, price, description 
FROM rooms 
WHERE status = 'available';

-- 2. Tìm kiếm phòng theo khoảng giá (dưới 3 triệu)
SELECT room_number, price 
FROM rooms 
WHERE price <= 3000000;

-- 3. Lấy danh sách các dịch vụ tính theo 'Tháng'
SELECT service_name, unit_price 
FROM services 
WHERE unit = 'Tháng';