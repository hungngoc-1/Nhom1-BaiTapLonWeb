-- 1. Lấy danh sách hợp đồng kèm tên phòng và tên người thuê (Dùng JOIN)
SELECT c.id, r.room_number, u.full_name, c.start_date, c.end_date 
FROM contracts c
JOIN rooms r ON c.room_id = r.id
JOIN users u ON c.tenant_id = u.id;

-- 2. Lọc các hợp đồng đang có hiệu lực (active)
SELECT * FROM contracts 
WHERE status = 'active';

-- 3. Lấy danh sách hợp đồng của một phòng cụ thể (ví dụ phòng P101 có id = 1)
SELECT u.full_name, c.start_date, c.end_date, c.deposit_amount 
FROM contracts c
JOIN users u ON c.tenant_id = u.id
WHERE c.room_id = 1;