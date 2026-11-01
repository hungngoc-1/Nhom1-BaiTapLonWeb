-- 1. Lấy danh sách hóa đơn chưa thanh toán kèm mã phòng (Dashboard công nợ)
SELECT r.room_number, i.month_year, i.total_amount 
FROM invoices i
JOIN rooms r ON i.room_id = r.id
WHERE i.status = 'unpaid';

-- 2. Lấy chi tiết các khoản thu của một hóa đơn cụ thể (vd: hóa đơn id = 1)
SELECT s.service_name, d.quantity, d.applied_price, d.subtotal 
FROM invoice_details d
LEFT JOIN services s ON d.service_id = s.id
WHERE d.invoice_id = 1;

-- 3. Tính tổng doanh thu từ các hóa đơn đã thu tiền trong tháng 10-2023
SELECT SUM(total_amount) AS tong_doanh_thu 
FROM invoices 
WHERE status = 'paid' AND month_year = '10-2023';