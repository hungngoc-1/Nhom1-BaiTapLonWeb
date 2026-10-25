

-- 1. Xem lịch sử ghi điện nước chi tiết (Bảng đầy đủ 7 cột: gồm trạng thái phòng, chỉ số cũ và mới)
SELECT r.room_number, r.status, u.month_year, u.electric_old, u.electric_new, u.water_old, u.water_new 
FROM utilities_readings u 
JOIN rooms r ON u.room_id = r.id;

-- 2. Tính số lượng điện và nước đã tiêu thụ trong tháng (Sử dụng phép trừ)
SELECT r.room_number, u.month_year, 
       (u.electric_new - u.electric_old) AS so_dien_tieu_thu, 
       (u.water_new - u.water_old) AS so_nuoc_tieu_thu 
FROM utilities_readings u 
JOIN rooms r ON u.room_id = r.id;

-- 3. Lọc danh sách chốt điện nước của một tháng cụ thể (Sử dụng điều kiện WHERE)
SELECT r.room_number, u.month_year, u.electric_new, u.water_new
FROM utilities_readings u 
JOIN rooms r ON u.room_id = r.id
WHERE u.month_year = '10-2023';