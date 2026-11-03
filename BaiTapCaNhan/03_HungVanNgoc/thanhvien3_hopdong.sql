-- =========================================================================
-- BÀI TẬP CÁ NHÂN & PHÂN HỆ BÀI TẬP LỚN - THÀNH VIÊN 3: HỦNG VĂN NGỌC
-- MSSV: 224001738 | Phân hệ phụ trách: Quản lý Hợp đồng (Contracts) & Xem phòng
-- =========================================================================

USE `quan_ly_nha_tro`;

-- -------------------------------------------------------------------------
-- PHẦN 1: CẤU TRÚC BẢNG HỢP ĐỒNG MỚI TRONG BÀI TẬP LỚN (contracts)
-- Hỗ trợ: Đặt cọc = 1 tháng tiền phòng, Tính phí đầu vào (<=15 thu 100%, >15 thu 50%),
--         Ký hợp đồng Điện tử (Online Canvas) hoặc Ký giấy (Upload Scan),
--         Gia hạn hợp đồng & Thanh lý hợp đồng (Hoàn cọc / Thu giữ cọc).
-- -------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contracts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `contract_code` VARCHAR(30) UNIQUE,
  `room_id` INT NOT NULL,
  `tenant_id` INT NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `deposit_amount` DECIMAL(10,2) NOT NULL,
  `deposit_date` DATE NULL,
  `deposit_status` ENUM('held', 'refunded', 'forfeited') DEFAULT 'held',
  `monthly_rent` DECIMAL(10,2) DEFAULT 0,
  `electricity_rate` DECIMAL(10,2) DEFAULT 3500,
  `water_rate` DECIMAL(10,2) DEFAULT 20000,
  `service_fee` DECIMAL(10,2) DEFAULT 150000,
  `signature_type` ENUM('online', 'paper') DEFAULT 'online',
  `signature_status` ENUM('pending', 'signed') DEFAULT 'pending',
  `tenant_signature` LONGTEXT NULL,
  `manager_signature` LONGTEXT NULL,
  `scan_file` VARCHAR(255) NULL,
  `signed_at` DATETIME NULL,
  `renewal_status` VARCHAR(20) DEFAULT 'none',
  `terminated_at` DATE NULL,
  `termination_reason` TEXT NULL,
  `notes` TEXT NULL,
  `status` ENUM('active', 'expired', 'terminated') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB;

-- -------------------------------------------------------------------------
-- PHẦN 2: DỮ LIỆU MẪU HỢP ĐỒNG (SEED DATA)
-- -------------------------------------------------------------------------
-- 1. HD-2026-001: Phòng P101 - Khách vào ngày 01 (<=15: thu 100% phí dịch vụ), Đã ký Online
-- 2. HD-2026-002: Phòng P201 - Hợp đồng ký giấy đã lưu hồ sơ
-- 3. HD-2025-003: Phòng P102 - Hợp đồng cũ đã thanh lý và hoàn trả 100% tiền cọc
-- 4. HD-2026-004: Phòng P102 - Khách mới vào ngày 18 (>15: thu 50% phí dịch vụ), Chờ ký
-- 5. HD-2026-005: Phòng P301 - Hợp đồng căn hộ Studio đang hiệu lực

-- -------------------------------------------------------------------------
-- PHẦN 3: CÁC CÂU TRUY VẤN SQL NGHIỆP VỤ HỢP ĐỒNG (DÙNG TRONG CONTRACT MODEL)
-- -------------------------------------------------------------------------

-- 1. Lấy danh sách toàn bộ hợp đồng kèm thông tin phòng và khách thuê (JOIN 3 bảng)
SELECT 
    c.id,
    c.contract_code,
    r.room_number,
    r.price AS room_price,
    u.full_name AS tenant_name,
    u.phone AS tenant_phone,
    c.start_date,
    c.end_date,
    c.monthly_rent,
    c.deposit_amount,
    c.deposit_date,
    c.deposit_status,
    c.signature_type,
    c.signature_status,
    c.status
FROM contracts c
JOIN rooms r ON c.room_id = r.id
JOIN users u ON c.tenant_id = u.id
ORDER BY c.id DESC;

-- 2. Lọc các hợp đồng đang có hiệu lực (active) và tính số ngày còn lại
SELECT 
    c.contract_code,
    r.room_number,
    u.full_name AS tenant_name,
    c.start_date,
    c.end_date,
    c.deposit_amount,
    DATEDIFF(c.end_date, CURDATE()) AS days_remaining
FROM contracts c
JOIN rooms r ON c.room_id = r.id
JOIN users u ON c.tenant_id = u.id
WHERE c.status = 'active';

-- 3. Thống kê tổng quan Hợp đồng & Tiền cọc đang giữ (Overview Cards)
SELECT 
    COUNT(*) AS tong_hop_dong,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS dang_hieu_luc,
    SUM(CASE WHEN status = 'active' AND DATEDIFF(end_date, CURDATE()) BETWEEN 0 AND 30 THEN 1 ELSE 0 END) AS sap_het_han_30_ngay,
    SUM(CASE WHEN status = 'terminated' THEN 1 ELSE 0 END) AS da_thanh_ly,
    SUM(CASE WHEN status = 'active' AND deposit_status = 'held' THEN deposit_amount ELSE 0 END) AS tong_tien_coc_dang_giu
FROM contracts;

-- 4. Lấy lịch sử hợp đồng của một phòng cụ thể (ví dụ phòng P102 có room_id = 2)
SELECT 
    c.contract_code,
    u.full_name AS tenant_name,
    c.start_date,
    c.end_date,
    c.deposit_amount,
    c.deposit_status,
    c.status,
    c.termination_reason
FROM contracts c
JOIN users u ON c.tenant_id = u.id
WHERE c.room_id = 2
ORDER BY c.start_date DESC;

-- 5. Lấy danh sách các phòng ĐANG TRỐNG để hiển thị trên trang Xem phòng cho khách (TrangXemPhong)
SELECT id, room_number, price, status, description
FROM rooms
WHERE status = 'available'
ORDER BY price ASC;
