<?php
if (!function_exists('h')) {
    function h($s): string {
        return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('money')) {
    function money($n): string {
        return number_format((float)$n, 0, ',', '.') . ' đ';
    }
}

$csrf          = $_SESSION['csrf_token'] ?? '';
$currentStatus = $filters['status'] ?? '';
$currentFloor  = $filters['floor'] ?? '';
$currentPrice  = $filters['price_range'] ?? '';
$keyword       = $filters['keyword'] ?? '';
$hasFilter     = ($keyword !== '' || $currentStatus !== '' || $currentFloor !== '' || $currentPrice !== '');

$totalRooms    = (int)($stats['total'] ?? 0);
$rentedRooms   = (int)($stats['rented'] ?? 0);
$availRooms    = (int)($stats['available'] ?? 0);
$occupancyRate = (int)($stats['occupancy_rate'] ?? 0);
$monthlyPot    = (float)($stats['monthly_potential'] ?? 0);
$avgPrice      = (float)($stats['avg_price'] ?? 0);
$activeCtr     = (int)($stats['active_contracts'] ?? 0);
$unpaidInv     = (int)($stats['unpaid_invoices'] ?? 0);
$unpaidRooms   = (int)($stats['unpaid_rooms'] ?? 0);

$adminName = $_SESSION['full_name'] ?? 'Quản trị viên Hệ thống';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StayMate - Quản lý Phòng trọ</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --teal-main: #308f86;
            --teal-hover: #25756d;
            --bg-page: #f8fafc;
        }

        body {
            background-color: var(--bg-page);
            font-family: 'Nunito', sans-serif;
            font-size: 0.9rem;
            color: #334155;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-image:
                url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23e6f4f1" fill-opacity="0.8" d="M0,160L48,170.7C96,181,192,203,288,197.3C384,192,480,160,576,149.3C672,139,768,149,864,170.7C960,192,1056,224,1152,213.3C1248,203,1344,149,1392,122.7L1440,96L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0C192,0,96,0,48,0L0,0Z"></path></svg>'),
                url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 300"><path d="M0,150 Q50,100 100,250" stroke="%2334d399" stroke-width="4" fill="none"/><path d="M10,120 Q40,90 60,110 Q30,140 10,120" fill="%236ee7b7"/><path d="M30,180 Q80,140 90,170 Q50,200 30,180" fill="%2310b981"/><path d="M60,220 Q100,190 110,220 Q70,250 60,220" fill="%23059669"/></svg>'),
                url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 300"><path d="M200,150 Q150,100 100,250" stroke="%2334d399" stroke-width="4" fill="none"/><path d="M190,120 Q160,90 140,110 Q170,140 190,120" fill="%236ee7b7"/><path d="M170,180 Q120,140 110,170 Q150,200 170,180" fill="%2310b981"/><path d="M140,220 Q100,190 90,220 Q130,250 140,220" fill="%23059669"/></svg>');
            background-position: top center, left 20%, right bottom;
            background-repeat: no-repeat;
            background-size: 100% auto, 150px, 150px;
            background-attachment: fixed;
        }

        .text-teal-main { color: var(--teal-main) !important; }

        /* ================= HOẠT ẢNH (ANIMATIONS) ================= */
        @keyframes fadeUpIn {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes sparkleTwinkle {
            0%, 100% { transform: scale(1) rotate(20deg); opacity: 1; }
            50%      { transform: scale(1.25) rotate(28deg); opacity: 0.7; }
        }
        @keyframes floatHouse {
            0%, 100% { transform: translateY(0); }
            50%      { transform: translateY(-6px); }
        }
        @keyframes pulseDot {
            0%, 100% { transform: scale(1); opacity: 1; }
            50%      { transform: scale(1.35); opacity: 0.65; }
        }

        .anim-fade-up {
            animation: fadeUpIn 0.45s cubic-bezier(0.22, 1, 0.36, 1) both;
        }
        .delay-1 { animation-delay: 0.05s; }
        .delay-2 { animation-delay: 0.10s; }
        .delay-3 { animation-delay: 0.15s; }
        .delay-4 { animation-delay: 0.20s; }

        @media (prefers-reduced-motion: reduce) {
            .anim-fade-up, .header-house-graphic, .title-sparkle, .status-dot-live {
                animation: none !important;
            }
        }

        /* ================= NAVBAR CHUẨN TRANG TỔNG QUAN ================= */
        .navbar-custom {
            background: #ffffff;
            padding: 12px 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            z-index: 100;
            border-bottom: 1px solid #f1f5f9;
        }
        .brand-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; transition: transform 0.2s ease; }
        .brand-logo:hover { transform: scale(1.02); }
        .brand-logo i { color: var(--teal-main); font-size: 2.2rem; }
        .brand-text { color: var(--teal-main); font-weight: 900; font-size: 1.4rem; line-height: 1; letter-spacing: -0.5px; }
        .brand-sub { color: #64748b; font-size: 0.75rem; font-weight: 600; }

        .nav-pills { gap: 10px; flex-wrap: nowrap; }
        .nav-pills .nav-link {
            color: #475569;
            font-weight: 800;
            font-size: 0.95rem;
            padding: 10px 20px;
            border-radius: 50px;
            transition: all 0.22s ease;
            white-space: nowrap;
        }
        .nav-pills .nav-link i { margin-right: 6px; }
        .nav-pills .nav-link:hover {
            color: var(--teal-main);
            background: #f0fdfa;
            transform: translateY(-1px);
        }
        .nav-pills .nav-link.active {
            background-color: var(--teal-main);
            color: #ffffff;
            box-shadow: 0 6px 15px rgba(48, 143, 134, 0.35);
        }

        .avatar-circle {
            width: 46px;
            height: 46px;
            background-color: var(--teal-main);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 900;
            font-size: 1rem;
            border: 3px solid #ffffff;
            box-shadow: 0 6px 15px rgba(48, 143, 134, 0.4);
            transition: transform 0.2s ease;
        }
        .avatar-circle:hover { transform: scale(1.06); }

        .btn-hamburger {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: #f0fdfa;
            border: 2px solid #99f6e4;
            color: var(--teal-main);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            transition: all 0.2s;
        }
        .btn-hamburger:hover, .btn-hamburger:focus {
            background: var(--teal-main);
            color: #ffffff;
            outline: none;
        }
        @media (max-width: 991.98px) {
            .mobile-nav-collapse {
                background: #ffffff;
                border-radius: 22px;
                padding: 14px;
                margin-top: 12px;
                border: 2px solid #ccfbf1;
                box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
            }
            .mobile-nav-collapse .nav-pills {
                flex-direction: column;
                gap: 6px;
            }
            .mobile-nav-collapse .nav-link {
                border-radius: 16px;
                padding: 11px 16px;
            }
        }

        /* ================= NÚT QUAY LẠI & HEADER TÒA NHÀ ================= */
        .btn-back-content {
            background: white;
            border: 3px solid #ffffff;
            color: #334155;
            font-weight: 800;
            font-size: 0.9rem;
            padding: 10px 26px;
            border-radius: 50px;
            transition: all 0.25s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 22px;
            position: relative;
            z-index: 2;
        }
        .btn-back-content:hover {
            background: #f8fafc;
            border-color: var(--teal-main);
            color: var(--teal-main);
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.12);
        }

        .header-title-wrapper {
            position: relative;
            z-index: 2;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 28px;
            flex-wrap: wrap;
            gap: 16px;
        }
        .main-title {
            font-weight: 900;
            color: #0f172a;
            font-size: 2rem;
            letter-spacing: -0.5px;
            display: inline-flex;
            align-items: center;
            flex-wrap: wrap;
        }
        .bld-highlight { color: var(--teal-main); margin-left: 8px; position: relative; }

        .title-sparkle {
            position: absolute;
            background-color: #fde047;
            border-radius: 2px;
            animation: sparkleTwinkle 2.4s ease-in-out infinite;
        }
        .ts-1 { width: 4px; height: 12px; top: -10px; right: -15px; transform: rotate(20deg); }
        .ts-2 { width: 12px; height: 4px; top: 15px; right: -25px; transform: rotate(15deg); animation-delay: 0.6s; }

        .bld-address-txt { font-size: 0.92rem; font-weight: 700; color: #64748b; margin-top: 6px; }

        .date-badge {
            background: white;
            border-radius: 50px;
            padding: 11px 24px;
            font-weight: 800;
            color: #475569;
            font-size: 0.88rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            border: 3px solid #ffffff;
        }

        .header-house-graphic {
            position: absolute;
            right: 0;
            top: -50px;
            width: 300px;
            height: 200px;
            z-index: 1;
            pointer-events: none;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 200"><path d="M0,180 Q150,150 300,180 L300,200 L0,200 Z" fill="%23dcfce7"/><rect x="120" y="80" width="80" height="100" fill="%23e0f2fe"/><polygon points="100,80 160,30 220,80" fill="%230284c7"/><polygon points="160,30 220,80 250,80 190,30" fill="%2338bdf8"/><rect x="140" y="100" width="15" height="20" fill="%23ffffff"/><rect x="165" y="100" width="15" height="20" fill="%23ffffff"/><rect x="140" y="140" width="15" height="40" fill="%23ffffff"/><circle cx="50" cy="140" r="30" fill="%234ade80"/><circle cx="80" cy="150" r="40" fill="%2322c55e"/><circle cx="240" cy="140" r="35" fill="%2322c55e"/><circle cx="270" cy="150" r="25" fill="%2316a34a"/></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: bottom right;
            animation: floatHouse 5s ease-in-out infinite;
        }

        .rate-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 50px;
            padding: 6px 14px;
            font-weight: 800;
            font-size: 0.8rem;
            color: #334155;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
            transition: all 0.2s;
        }
        .rate-chip:hover {
            border-color: var(--teal-main);
            transform: translateY(-1px);
        }

        /* ================= 4 THẺ OVERVIEW GIỐNG HỆT TRANG TỔNG QUAN ================= */
        .overview-card {
            border-radius: 30px;
            padding: 24px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
            border: 4px solid #ffffff;
            height: 100%;
            transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1), box-shadow 0.3s ease;
            backdrop-filter: blur(5px);
        }
        .overview-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.18);
        }

        .card-green  { background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); }
        .card-blue   { background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); }
        .card-yellow { background: linear-gradient(135deg, #fef9c3 0%, #fef08a 100%); }
        .card-purple { background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%); }

        .card-bg-pattern {
            position: absolute;
            right: 0;
            bottom: 0;
            width: 140px;
            height: 140px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: bottom right;
            opacity: 0.88;
            pointer-events: none;
        }
        .pattern-house { background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect x="30" y="50" width="50" height="40" fill="%23bae6fd"/><polygon points="20,50 55,20 90,50" fill="%233b82f6"/><rect x="40" y="60" width="10" height="10" fill="%23ffffff"/><rect x="60" y="60" width="10" height="10" fill="%23ffffff"/><circle cx="15" cy="80" r="10" fill="%234ade80"/><circle cx="95" cy="75" r="12" fill="%2322c55e"/></svg>'); }
        .pattern-wave { background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path d="M0,70 Q25,40 50,70 T100,70 L100,100 L0,100 Z" fill="%23bae6fd" opacity="0.5"/><path d="M0,90 Q25,60 50,90 T100,90 L100,100 L0,100 Z" fill="%237dd3fc" opacity="0.5"/></svg>'); }
        .pattern-wave-yellow { background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path d="M0,70 Q25,40 50,70 T100,70 L100,100 L0,100 Z" fill="%23fde047" opacity="0.5"/><path d="M0,90 Q25,60 50,90 T100,90 L100,100 L0,100 Z" fill="%23facc15" opacity="0.5"/></svg>'); }
        .pattern-people { background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="30" cy="40" r="12" fill="%23c084fc" opacity="0.8"/><circle cx="50" cy="30" r="15" fill="%23a855f7"/><circle cx="70" cy="40" r="12" fill="%23c084fc" opacity="0.8"/><path d="M10,90 Q30,60 50,60 Q70,60 90,90" fill="%23a855f7" opacity="0.8"/></svg>'); }

        .overview-title {
            font-size: 0.8rem;
            font-weight: 900;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
            text-transform: uppercase;
            position: relative;
            z-index: 2;
        }
        .overview-val {
            font-size: 2.3rem;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -1px;
            position: relative;
            z-index: 2;
        }
        .overview-icon-bg {
            position: absolute;
            right: 22px;
            top: 22px;
            width: 52px;
            height: 52px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.35rem;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            border: 3px solid #ffffff;
            z-index: 2;
        }
        .icon-green  { background: linear-gradient(135deg, #16a34a, #4ade80); }
        .icon-blue   { background: linear-gradient(135deg, #0284c7, #38bdf8); }
        .icon-yellow { background: linear-gradient(135deg, #f59e0b, #eab308); }
        .icon-purple { background: linear-gradient(135deg, #9333ea, #a855f7); }

        /* ================= KHUNG CHÍNH BO TRÒN 30PX (SECTION BOX) ================= */
        .section-box {
            background: rgba(255, 255, 255, 0.96);
            border-radius: 30px;
            border: 4px solid #ffffff;
            padding: 26px 28px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
            position: relative;
            z-index: 2;
            margin-bottom: 24px;
        }

        /* ================= NÚT BẤM & BỘ LỌC VIÊN THUỐC ================= */
        .btn-teal-custom {
            background: var(--teal-main);
            border-radius: 50px;
            font-weight: 800;
            color: white;
            padding: 11px 26px;
            border: 2px solid var(--teal-hover);
            box-shadow: 0 8px 20px rgba(48, 143, 134, 0.38);
            font-size: 0.92rem;
            transition: all 0.22s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-teal-custom:hover {
            background: var(--teal-hover);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 12px 26px rgba(48, 143, 134, 0.45);
        }

        .btn-pill-white {
            background: #ffffff;
            border-radius: 50px;
            font-weight: 800;
            color: #334155;
            padding: 10px 20px;
            border: 2px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
            font-size: 0.88rem;
            transition: all 0.22s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-pill-white:hover {
            border-color: var(--teal-main);
            color: var(--teal-main);
            background: #f0fdfa;
            transform: translateY(-2px);
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 20px;
            border-radius: 50px;
            font-size: 0.88rem;
            font-weight: 800;
            text-decoration: none;
            transition: all 0.22s ease;
            border: 2px solid transparent;
        }
        .status-pill:hover { transform: translateY(-2px); }
        .pill-all    { background: #f1f5f9; color: #334155; border-color: #cbd5e1; }
        .pill-empty  { background: #f0fdfa; color: var(--teal-main); border-color: #99f6e4; }
        .pill-rented { background: #dcfce7; color: #15803d; border-color: #86efac; }

        .status-pill.active-filter {
            background: var(--teal-main);
            color: #ffffff;
            border-color: var(--teal-hover);
            box-shadow: 0 6px 16px rgba(48, 143, 134, 0.32);
        }

        .filter-control {
            border-radius: 50px;
            border: 2px solid #e2e8f0;
            padding: 10px 18px;
            font-weight: 700;
            font-size: 0.88rem;
            color: #334155;
            background-color: #f8fafc;
            transition: all 0.2s;
        }
        .filter-control:focus {
            background-color: #ffffff;
            border-color: var(--teal-main);
            box-shadow: 0 0 0 4px rgba(48, 143, 134, 0.15);
            outline: none;
        }

        /* ================= THẺ PHÒNG TRỌ (ROOM CARD) CHUẨN STAYMATE ================= */
        .room-card {
            background: #ffffff;
            border-radius: 26px;
            border: 2.5px solid #e2e8f0;
            padding: 22px;
            transition: all 0.3s cubic-bezier(0.22, 1, 0.36, 1);
            position: relative;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.05);
            overflow: hidden;
        }
        .room-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--teal-main), #34d399);
            opacity: 0.85;
            transition: height 0.25s ease;
        }
        .room-card.is-available::before {
            background: linear-gradient(90deg, #0284c7, #38bdf8);
        }
        .room-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 22px 45px rgba(48, 143, 134, 0.16);
            border-color: var(--teal-main);
        }

        .room-code-box {
            width: 52px;
            height: 52px;
            border-radius: 18px;
            background: linear-gradient(135deg, var(--teal-main), var(--teal-hover));
            color: #ffffff;
            font-weight: 900;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 6px 15px rgba(48, 143, 134, 0.3);
            border: 2px solid #ffffff;
        }
        .room-code-box.is-available {
            background: linear-gradient(135deg, #0284c7, #38bdf8);
            box-shadow: 0 6px 15px rgba(2, 132, 199, 0.28);
        }

        .badge-status-room {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 13px;
            border-radius: 50px;
            font-size: 0.76rem;
            font-weight: 800;
            white-space: nowrap;
        }
        .badge-status-rented {
            background: #dcfce7;
            color: #15803d;
            border: 1.5px solid #86efac;
        }
        .badge-status-empty {
            background: #e0f2fe;
            color: #0369a1;
            border: 1.5px solid #7dd3fc;
        }
        .status-dot-live {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            display: inline-block;
            animation: pulseDot 2s infinite;
        }

        .price-banner {
            background: linear-gradient(135deg, #f0fdfa 0%, #ecfdf5 100%);
            border: 1.5px solid #ccfbf1;
            border-radius: 18px;
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 16px 0 12px 0;
        }
        .price-val {
            font-weight: 900;
            font-size: 1.2rem;
            color: var(--teal-main);
            letter-spacing: -0.3px;
        }

        .desc-box {
            color: #475569;
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 14px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.5em;
        }

        .tenant-box {
            background: linear-gradient(135deg, #f8fafc 0%, #f0fdf4 100%);
            border: 1.5px solid #bbf7d0;
            border-radius: 18px;
            padding: 12px 15px;
            margin-bottom: 14px;
        }
        .empty-room-box {
            background: linear-gradient(135deg, #f8fafc 0%, #f0f9ff 100%);
            border: 1.5px dashed #7dd3fc;
            border-radius: 18px;
            padding: 12px 15px;
            margin-bottom: 14px;
        }

        .code-link-pill {
            font-weight: 800;
            color: var(--teal-main);
            background: #ffffff;
            border: 1.5px solid #99f6e4;
            border-radius: 50px;
            padding: 2px 10px;
            font-size: 0.76rem;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .code-link-pill:hover {
            background: var(--teal-main);
            color: #ffffff;
            border-color: var(--teal-main);
        }

        .quick-link-chip {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 0.85rem;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            color: #475569;
            transition: all 0.2s ease;
        }
        .quick-link-chip:hover {
            background: #f0fdfa;
            color: var(--teal-main);
            border-color: #99f6e4;
            transform: translateY(-2px);
        }

        .btn-edit-card {
            border: 2px solid #cbd5e1;
            border-radius: 50px;
            color: #334155;
            font-weight: 800;
            font-size: 0.84rem;
            padding: 7px 14px;
            background: #ffffff;
            transition: all 0.2s ease;
            flex-grow: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .btn-edit-card:hover {
            border-color: var(--teal-main);
            color: #ffffff;
            background: var(--teal-main);
            box-shadow: 0 6px 14px rgba(48, 143, 134, 0.28);
        }

        .btn-del-card {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 1.5px solid #fecaca;
            color: #dc2626;
            background: #fef2f2;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }
        .btn-del-card:hover {
            background: #dc2626;
            color: #ffffff;
            border-color: #dc2626;
            transform: translateY(-2px);
        }

        /* ================= MODAL CHUẨN STAYMATE ================= */
        .modal-staymate .modal-content {
            border-radius: 30px;
            border: 4px solid #ffffff;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        .modal-staymate .modal-header {
            background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%);
            border-bottom: 2px solid #99f6e4;
            padding: 18px 26px;
        }
        .modal-staymate .form-control,
        .modal-staymate .form-select {
            border-radius: 16px;
            border: 2px solid #e2e8f0;
            padding: 10px 15px;
            font-weight: 700;
            font-size: 0.9rem;
            color: #1e293b;
        }
        .modal-staymate .form-control:focus,
        .modal-staymate .form-select:focus {
            border-color: var(--teal-main);
            box-shadow: 0 0 0 4px rgba(48, 143, 134, 0.15);
        }

        .empty-folder-illus {
            width: 150px;
            height: 120px;
            margin: 0 auto 15px auto;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 150 120"><path d="M20,100 Q75,110 130,100" stroke="%23cbd5e1" stroke-width="4" fill="none" stroke-linecap="round"/><path d="M10,80 Q30,40 50,70" fill="%236ee7b7"/><path d="M130,70 Q110,30 90,60" fill="%2334d399"/><path d="M40,50 L60,50 L70,40 L120,40 L120,90 L40,90 Z" fill="%2399f6e4"/><path d="M30,60 L70,60 L80,50 L130,50 L110,100 L20,100 Z" fill="%235eead4"/><path d="M40,30 L45,15" stroke="%23fbbf24" stroke-width="3" stroke-linecap="round"/><path d="M90,20 L105,10" stroke="%23fbbf24" stroke-width="3" stroke-linecap="round"/></svg>');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        .footer-custom {
            background: #ffffff;
            padding: 20px 0;
            color: #64748b;
            font-size: 0.85rem;
            font-weight: 700;
            margin-top: auto;
            border-top: 1px solid #f1f5f9;
            position: relative;
            z-index: 10;
        }
    </style>
</head>
<body>

    <!-- ================= NAVBAR CHUẨN TRANG TỔNG QUAN ================= -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
        <div class="container-fluid px-3 px-xl-5">
            <a class="brand-logo me-4" href="index.php?route=dashboard">
                <i class="fa-solid fa-house-chimney text-teal-main"></i>
                <div>
                    <div class="brand-text">StayMate</div>
                    <div class="brand-sub">Quản Lý Nhà Trọ</div>
                </div>
            </a>

            <!-- Cụm Chuông + Avatar + Nút menu di động -->
            <div class="d-flex align-items-center gap-3 order-lg-3">
                <a href="index.php?route=invoices&status=unpaid" class="position-relative text-decoration-none" style="color: #64748b;" title="Hóa đơn chưa thu">
                    <i class="fa-solid fa-bell fs-4"></i>
                    <?php if ($unpaidInv > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white">
                            <?= $unpaidInv ?>
                        </span>
                    <?php endif; ?>
                </a>

                <div class="dropdown">
                    <div class="d-flex align-items-center gap-2" style="cursor: pointer;" data-bs-toggle="dropdown">
                        <div class="avatar-circle">AS</div>
                        <div class="text-start d-none d-sm-block lh-sm">
                            <div class="fw-bold text-dark" style="font-size: 0.9rem;">
                                <?= h($adminName) ?> <i class="fa-solid fa-angle-down ms-1 text-muted fs-6"></i>
                            </div>
                            <div class="text-muted" style="font-size: 0.75rem;">Admin</div>
                        </div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 mt-2 p-2">
                        <li>
                            <a class="dropdown-item rounded-3 fw-bold py-2" href="index.php?route=dashboard">
                                <i class="fa-solid fa-city me-2 text-teal-main"></i> Danh sách Tòa nhà
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item rounded-3 fw-bold py-2" href="TrangXemPhong/" target="_blank">
                                <i class="fa-solid fa-up-right-from-square me-2 text-primary"></i> Trang Xem phòng khách
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item rounded-3 text-danger fw-bold py-2" href="index.php?route=auth/logout">
                                <i class="fa-solid fa-right-from-bracket me-2"></i> Đăng xuất
                            </a>
                        </li>
                    </ul>
                </div>

                <button class="btn-hamburger d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#staymateMobileNav" aria-controls="staymateMobileNav" aria-expanded="false" aria-label="Mở menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>

            <!-- Danh mục Menu đồng bộ chuẩn với Trang Tổng quan -->
            <div class="collapse navbar-collapse mobile-nav-collapse order-lg-2" id="staymateMobileNav">
                <ul class="nav nav-pills me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php?route=overview"><i class="fa-solid fa-border-all"></i> Tổng quan</a></li>
                    <li class="nav-item"><a class="nav-link active" href="index.php?route=rooms"><i class="fa-solid fa-bed"></i> Phòng trọ</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?route=tenants"><i class="fa-solid fa-users"></i> Khách thuê</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?route=contracts"><i class="fa-solid fa-file-signature"></i> Hợp đồng</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?route=utilities"><i class="fa-solid fa-bolt"></i> Điện Nước</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php?route=invoices"><i class="fa-solid fa-file-invoice-dollar"></i> Hóa đơn</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ================= NỘI DUNG CHÍNH ================= -->
    <div class="container-fluid px-4 px-xl-5 py-4 position-relative">

        <a href="index.php?route=overview" class="btn-back-content">
            <i class="fa-solid fa-arrow-left me-2"></i> Quay lại Tổng quan
        </a>

        <!-- Header giống Trang Tổng quan -->
        <div class="header-title-wrapper anim-fade-up">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1" style="font-size: 0.85rem; font-weight: 700;">
                        <li class="breadcrumb-item">
                            <a href="index.php?route=dashboard" class="text-teal-main text-decoration-none">
                                <i class="fa-solid fa-house"></i> Hệ thống Quản lý
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="index.php?route=overview" class="text-teal-main text-decoration-none">Tổng quan</a>
                        </li>
                        <li class="breadcrumb-item text-muted" aria-current="page">Quản lý Phòng trọ</li>
                    </ol>
                </nav>

                <div class="main-title">
                    Quản lý Phòng trọ -
                    <span class="bld-highlight">
                        Danh sách &amp; Trạng thái Phòng
                        <span class="title-sparkle ts-1"></span>
                        <span class="title-sparkle ts-2"></span>
                    </span>
                </div>

                <!-- Bảng giá dịch vụ đồng bộ từ bảng services của CSDL chung -->
                <div class="bld-address-txt d-flex flex-wrap align-items-center gap-2 mt-2">
                    <?php foreach ($services as $srv):
                        $sName = mb_strtolower($srv['service_name'], 'UTF-8');
                        $iconClass = 'fa-solid fa-concierge-bell text-teal-main';
                        if (strpos($sName, 'điện') !== false) $iconClass = 'fa-solid fa-bolt text-warning';
                        elseif (strpos($sName, 'nước') !== false) $iconClass = 'fa-solid fa-droplet text-info';
                        elseif (strpos($sName, 'internet') !== false || strpos($sName, 'wifi') !== false) $iconClass = 'fa-solid fa-wifi text-primary';
                        elseif (strpos($sName, 'rác') !== false) $iconClass = 'fa-solid fa-recycle text-success';
                    ?>
                        <span class="rate-chip">
                            <i class="<?= $iconClass ?>"></i>
                            <?= h($srv['service_name']) ?>: <?= money($srv['unit_price']) ?>/<?= h($srv['unit']) ?>
                        </span>
                    <?php endforeach; ?>
                    <a href="index.php?route=utilities" class="rate-chip text-decoration-none text-teal-main" title="Cấu hình đơn giá dịch vụ">
                        <i class="fa-solid fa-sliders"></i> Quản lý điện nước
                    </a>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2 position-relative z-2">
                <div class="date-badge d-none d-xl-block">
                    <i class="fa-regular fa-calendar-days me-2 text-teal-main"></i> Hôm nay, <?= date('d/m/Y') ?>
                </div>
                <a href="TrangXemPhong/" target="_blank" class="btn-pill-white">
                    <i class="fa-solid fa-globe text-teal-main"></i> Trang khách xem phòng
                </a>
                <button type="button" class="btn-teal-custom" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                    <i class="fa-solid fa-plus"></i> Thêm Phòng Mới
                </button>
            </div>

            <div class="header-house-graphic d-none d-lg-block"></div>
        </div>

        <!-- Thông báo Flash -->
        <?php if ($flash_success !== ''): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-pill px-4 py-3 shadow-sm fw-bold border-0 mb-4 position-relative z-2" style="background: #dcfce7; color: #166534;">
                <i class="fa-solid fa-circle-check me-2"></i><?= h($flash_success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($flash_error !== ''): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-pill px-4 py-3 shadow-sm fw-bold border-0 mb-4 position-relative z-2" style="background: #fee2e2; color: #991b1b;">
                <i class="fa-solid fa-triangle-exclamation me-2"></i><?= h($flash_error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- ================= 4 THẺ THỐNG KÊ OVERVIEW CHUẨN TRANG TỔNG QUAN ================= -->
        <div class="row g-4 mb-4 position-relative z-2">
            <!-- 1. Tỷ lệ lấp đầy -->
            <div class="col-12 col-md-6 col-lg-3 anim-fade-up delay-1">
                <div class="overview-card card-green">
                    <div class="card-bg-pattern pattern-house"></div>
                    <div class="overview-icon-bg icon-green"><i class="fa-solid fa-building-circle-check"></i></div>
                    <div class="overview-title text-success"><i class="fa-regular fa-calendar-check fs-6"></i> TỶ LỆ LẤP ĐẦY</div>
                    <div class="d-flex align-items-baseline mb-2 position-relative z-2">
                        <span class="overview-val text-dark me-2"><?= $rentedRooms ?> / <?= $totalRooms ?></span>
                        <span class="fw-bold text-muted small">phòng</span>
                    </div>
                    <div class="progress mb-2 position-relative z-2" style="height: 8px; background-color: rgba(255,255,255,0.75); border-radius: 6px;">
                        <div class="progress-bar bg-success" style="width: <?= $occupancyRate ?>%; border-radius: 6px;"></div>
                    </div>
                    <div class="d-flex justify-content-between text-success position-relative z-2" style="font-size: 0.8rem; font-weight: 800;">
                        <span><i class="fa-solid fa-circle-check"></i> <?= $occupancyRate ?>% đang thuê</span>
                        <span class="text-muted"><?= $availRooms ?> trống</span>
                    </div>
                </div>
            </div>

            <!-- 2. Phòng trống sẵn sàng -->
            <div class="col-12 col-md-6 col-lg-3 anim-fade-up delay-2">
                <div class="overview-card card-blue">
                    <div class="card-bg-pattern pattern-wave"></div>
                    <div class="overview-icon-bg icon-blue"><i class="fa-solid fa-door-open"></i></div>
                    <div class="overview-title text-primary"><i class="fa-solid fa-key fs-6"></i> PHÒNG ĐANG TRỐNG</div>
                    <div class="d-flex align-items-baseline mt-2 position-relative z-2">
                        <span class="overview-val text-dark me-2"><?= $availRooms ?></span>
                        <span class="fw-bold text-muted small">phòng sẵn sàng</span>
                    </div>
                    <div class="mt-3 position-relative z-2" style="font-size: 0.8rem; font-weight: 800;">
                        <a href="index.php?route=rooms&status=available" class="text-primary text-decoration-none">
                            <i class="fa-solid fa-filter me-1"></i> Lọc xem phòng trống →
                        </a>
                    </div>
                </div>
            </div>

            <!-- 3. Tiền phòng đang cho thuê / tháng -->
            <div class="col-12 col-md-6 col-lg-3 anim-fade-up delay-3">
                <div class="overview-card card-yellow">
                    <div class="card-bg-pattern pattern-wave-yellow"></div>
                    <div class="overview-icon-bg icon-yellow"><i class="fa-solid fa-coins"></i></div>
                    <div class="overview-title" style="color: #b45309;"><i class="fa-solid fa-sack-dollar fs-6"></i> TIỀN PHÒNG / THÁNG</div>
                    <div class="overview-val mt-2 position-relative z-2" style="color: #b45309; font-size: 1.85rem;">
                        <?= money($monthlyPot) ?>
                    </div>
                    <div class="mt-3 position-relative z-2" style="font-size: 0.8rem; font-weight: 800; color: #b45309;">
                        <i class="fa-solid fa-tag me-1"></i> Giá TB: <span class="text-dark"><?= money($avgPrice) ?></span>
                    </div>
                </div>
            </div>

            <!-- 4. Hợp đồng & Công nợ liên kết -->
            <div class="col-12 col-md-6 col-lg-3 anim-fade-up delay-4">
                <div class="overview-card card-purple">
                    <div class="card-bg-pattern pattern-people"></div>
                    <div class="overview-icon-bg icon-purple"><i class="fa-solid fa-file-signature"></i></div>
                    <div class="overview-title" style="color: #7e22ce;"><i class="fa-solid fa-users fs-6"></i> HỢP ĐỒNG HIỆU LỰC</div>
                    <div class="d-flex align-items-baseline mt-2 position-relative z-2">
                        <span class="overview-val text-dark me-2"><?= $activeCtr ?></span>
                        <span class="fw-bold text-muted small">hợp đồng active</span>
                    </div>
                    <div class="mt-3 position-relative z-2" style="font-size: 0.8rem; font-weight: 800; color: #7e22ce;">
                        <a href="index.php?route=invoices&status=unpaid" class="text-decoration-none" style="color: #7e22ce;">
                            <i class="fa-solid fa-file-invoice-dollar me-1"></i> <?= $unpaidRooms ?> phòng còn nợ hóa đơn
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= KHUNG BỘ LỌC & LƯỚI PHÒNG TRỌ ================= -->
        <div class="section-box anim-fade-up">
            <!-- Hàng nút Pill lọc nhanh trạng thái -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pb-3 mb-4 border-bottom border-light">
                <div class="d-flex flex-wrap gap-2">
                    <a href="index.php?route=rooms" class="status-pill pill-all <?= $currentStatus === '' ? 'active-filter' : '' ?>">
                        <i class="fa-solid fa-border-all"></i> Tất cả phòng (<?= $totalRooms ?>)
                    </a>
                    <a href="index.php?route=rooms&status=available" class="status-pill pill-empty <?= $currentStatus === 'available' ? 'active-filter' : '' ?>">
                        <i class="fa-solid fa-door-open"></i> Phòng trống (<?= $availRooms ?>)
                    </a>
                    <a href="index.php?route=rooms&status=rented" class="status-pill pill-rented <?= $currentStatus === 'rented' ? 'active-filter' : '' ?>">
                        <i class="fa-solid fa-user-check"></i> Đang thuê (<?= $rentedRooms ?>)
                    </a>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="index.php?route=contracts" class="btn-pill-white">
                        <i class="fa-solid fa-file-circle-plus text-teal-main"></i> Quản lý Hợp đồng
                    </a>
                    <a href="index.php?route=invoices" class="btn-pill-white">
                        <i class="fa-solid fa-receipt text-warning"></i> Hóa đơn &amp; Thu tiền
                    </a>
                </div>
            </div>

            <!-- Thanh tìm kiếm & lọc chi tiết -->
            <form method="GET" action="index.php" class="row g-3 align-items-end mb-4">
                <input type="hidden" name="route" value="rooms">
                <div class="col-12 col-lg-4">
                    <label class="form-label fw-bold text-teal-main text-uppercase mb-1" style="font-size: 0.75rem;">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Từ khóa tìm kiếm
                    </label>
                    <input type="text" name="keyword" class="form-control filter-control"
                           placeholder="Tìm số phòng (P101), mô tả, tên hoặc SĐT khách..."
                           value="<?= h($keyword) ?>">
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label fw-bold text-muted text-uppercase mb-1" style="font-size: 0.75rem;">Khu vực tầng</label>
                    <select name="floor" class="form-select filter-control">
                        <option value="">-- Tất cả tầng --</option>
                        <?php foreach ($floors as $fl): ?>
                            <option value="<?= (int)$fl ?>" <?= $currentFloor === (string)$fl ? 'selected' : '' ?>>
                                Tầng <?= (int)$fl ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label fw-bold text-muted text-uppercase mb-1" style="font-size: 0.75rem;">Mức giá thuê</label>
                    <select name="price_range" class="form-select filter-control">
                        <option value="">-- Mọi mức giá --</option>
                        <option value="under3m" <?= $currentPrice === 'under3m' ? 'selected' : '' ?>>Đến 3.000.000 đ</option>
                        <option value="3m_4m" <?= $currentPrice === '3m_4m' ? 'selected' : '' ?>>3.000.000 - 4.000.000 đ</option>
                        <option value="over4m" <?= $currentPrice === 'over4m' ? 'selected' : '' ?>>Trên 4.000.000 đ</option>
                    </select>
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                    <label class="form-label fw-bold text-muted text-uppercase mb-1" style="font-size: 0.75rem;">Trạng thái</label>
                    <select name="status" class="form-select filter-control">
                        <option value="">-- Tất cả --</option>
                        <option value="available" <?= $currentStatus === 'available' ? 'selected' : '' ?>>Phòng trống</option>
                        <option value="rented" <?= $currentStatus === 'rented' ? 'selected' : '' ?>>Đang thuê</option>
                    </select>
                </div>

                <div class="col-6 col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn-teal-custom flex-grow-1 justify-content-center py-2 px-3">
                        <i class="fa-solid fa-filter"></i> Lọc
                    </button>
                    <?php if ($hasFilter): ?>
                        <a href="index.php?route=rooms" class="btn-pill-white py-2 px-3" title="Xóa bộ lọc">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Lưới Thẻ Phòng Trọ -->
            <div class="row g-4">
                <?php if (!empty($rooms)): ?>
                    <?php foreach ($rooms as $idx => $room):
                        $isAvailable = ($room['status'] ?? 'available') === 'available';
                        $roomId      = (int)$room['id'];
                        $unpaidCnt   = (int)($room['unpaid_count'] ?? 0);
                        $delayStyle  = 'animation-delay: ' . min(0.35, $idx * 0.05) . 's;';
                    ?>
                        <div class="col-xxl-4 col-xl-4 col-lg-6 col-md-6 anim-fade-up" style="<?= $delayStyle ?>">
                            <div class="room-card <?= $isAvailable ? 'is-available' : '' ?>">
                                <div>
                                    <!-- Đầu thẻ: Mã phòng + Tầng + Trạng thái -->
                                    <div class="d-flex align-items-start justify-content-between gap-2">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="room-code-box <?= $isAvailable ? 'is-available' : '' ?>">
                                                <?= h($room['room_number']) ?>
                                            </div>
                                            <div>
                                                <h5 class="fw-black mb-1 text-dark" style="font-weight: 900; font-size: 1.12rem;">
                                                    Phòng <?= h($room['room_number']) ?>
                                                </h5>
                                                <div class="text-muted fw-bold" style="font-size: 0.8rem;">
                                                    <i class="fa-solid fa-layer-group text-teal-main me-1"></i><?= h($room['floor_label']) ?>
                                                    &bull; ID #<?= $roomId ?>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if ($isAvailable): ?>
                                            <span class="badge-status-room badge-status-empty">
                                                <span class="status-dot-live" style="background: #0284c7;"></span> Phòng trống
                                            </span>
                                        <?php else: ?>
                                            <span class="badge-status-room badge-status-rented">
                                                <span class="status-dot-live" style="background: #16a34a;"></span> Đang thuê
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Giá thuê tháng -->
                                    <div class="price-banner">
                                        <span class="fw-bold text-muted small">
                                            <i class="fa-solid fa-tag text-teal-main me-1"></i> Giá thuê niêm yết
                                        </span>
                                        <div>
                                            <span class="price-val"><?= money($room['price']) ?></span>
                                            <span class="text-muted fw-bold small">/tháng</span>
                                        </div>
                                    </div>

                                    <!-- Mô tả phòng từ CSDL -->
                                    <div class="desc-box" title="<?= h($room['description'] ?? '') ?>">
                                        <i class="fa-solid fa-sparkles text-warning me-1"></i>
                                        <?= h(!empty($room['description']) ? $room['description'] : 'Phòng tiêu chuẩn đầy đủ tiện nghi cơ bản') ?>
                                    </div>

                                    <!-- Thông tin Khách thuê & Hợp đồng thực tế từ CSDL -->
                                    <?php if (!$isAvailable): ?>
                                        <div class="tenant-box">
                                            <?php if (!empty($room['contract_id'])): ?>
                                                <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                                                    <a href="index.php?route=tenants&keyword=<?= urlencode($room['tenant_phone'] ?? $room['tenant_name'] ?? '') ?>"
                                                       class="fw-black text-decoration-none text-dark text-truncate"
                                                       style="font-weight: 800; font-size: 0.9rem;">
                                                        <i class="fa-solid fa-user-check text-success me-1"></i><?= h($room['tenant_name']) ?>
                                                    </a>
                                                    <a href="index.php?route=contracts/detail&id=<?= (int)$room['contract_id'] ?>"
                                                       class="code-link-pill" title="Xem chi tiết hợp đồng">
                                                        <i class="fa-solid fa-file-contract"></i> <?= h($room['contract_code']) ?>
                                                    </a>
                                                </div>
                                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-1 text-muted fw-bold" style="font-size: 0.78rem;">
                                                    <span><i class="fa-solid fa-phone me-1 text-teal-main"></i><?= h($room['tenant_phone']) ?></span>
                                                    <span>
                                                        <i class="fa-regular fa-calendar me-1"></i>
                                                        <?= date('d/m/Y', strtotime($room['contract_start'])) ?> → <?= date('d/m/Y', strtotime($room['contract_end'])) ?>
                                                    </span>
                                                </div>
                                                <?php if ($unpaidCnt > 0): ?>
                                                    <div class="mt-2 pt-2 border-top border-success-subtle d-flex justify-content-between align-items-center" style="font-size: 0.78rem;">
                                                        <span class="text-danger fw-bold">
                                                            <i class="fa-solid fa-circle-exclamation me-1"></i>Nợ <?= $unpaidCnt ?> hóa đơn (<?= money($room['unpaid_amount']) ?>)
                                                        </span>
                                                        <a href="index.php?route=invoices&room_id=<?= $roomId ?>&period=all" class="fw-bold text-danger text-decoration-none">
                                                            Thu ngay →
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <div class="fw-bold text-dark" style="font-size: 0.88rem;">
                                                            <i class="fa-solid fa-user-clock text-warning me-1"></i> Đang thuê (Chưa gắn HĐ active)
                                                        </div>
                                                        <div class="text-muted fw-semibold" style="font-size: 0.78rem;">
                                                            Tạo hợp đồng để đồng bộ khách thuê &amp; hóa đơn
                                                        </div>
                                                    </div>
                                                    <a href="index.php?route=contracts" class="code-link-pill">
                                                        <i class="fa-solid fa-plus"></i> Lập HĐ
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="empty-room-box d-flex justify-content-between align-items-center gap-2">
                                            <div>
                                                <div class="fw-bold" style="color: #0369a1; font-size: 0.88rem;">
                                                    <i class="fa-solid fa-circle-check me-1"></i> Phòng trống sẵn sàng đón khách
                                                </div>
                                                <div class="text-muted fw-semibold" style="font-size: 0.78rem;">
                                                    Đang hiển thị công khai trên trang Xem phòng
                                                </div>
                                            </div>
                                            <a href="index.php?route=contracts" class="code-link-pill" title="Lập hợp đồng cho phòng này">
                                                <i class="fa-solid fa-file-signature"></i> Tạo HĐ
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Chân thẻ: Liên kết nhanh Điện nước / Hóa đơn + Sửa + Xóa -->
                                <div class="d-flex align-items-center gap-2 pt-3 border-top border-light">
                                    <a href="index.php?route=utilities&room_id=<?= $roomId ?>&period=all"
                                       class="quick-link-chip" title="Xem chỉ số Điện Nước phòng <?= h($room['room_number']) ?>">
                                        <i class="fa-solid fa-bolt text-warning"></i>
                                    </a>
                                    <a href="index.php?route=invoices&room_id=<?= $roomId ?>&period=all"
                                       class="quick-link-chip" title="Xem Hóa đơn phòng <?= h($room['room_number']) ?>">
                                        <i class="fa-solid fa-file-invoice-dollar text-teal-main"></i>
                                    </a>

                                    <button type="button" class="btn-edit-card" data-bs-toggle="modal" data-bs-target="#editRoomModal<?= $roomId ?>">
                                        <i class="fa-solid fa-pen-to-square"></i> Chỉnh sửa
                                    </button>

                                    <form method="POST" action="index.php?route=rooms" class="m-0"
                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa Phòng <?= h($room['room_number']) ?> khỏi CSDL?');">
                                        <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $roomId ?>">
                                        <button type="submit" class="btn-del-card" title="Xóa phòng <?= h($room['room_number']) ?>">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- ================= MODAL CẬP NHẬT PHÒNG (ĐỒNG BỘ CSDL CHUNG) ================= -->
                        <div class="modal fade modal-staymate" id="editRoomModal<?= $roomId ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <form method="POST" action="index.php?route=rooms" class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-black text-teal-main" style="font-weight: 900;">
                                            <i class="fa-solid fa-pen-to-square me-2"></i>Cập nhật Phòng <?= h($room['room_number']) ?>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="id" value="<?= $roomId ?>">

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold small text-muted">Mã phòng <span class="text-danger">*</span></label>
                                                <input type="text" name="room_number" class="form-control" maxlength="20"
                                                       value="<?= h($room['room_number']) ?>" required>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-bold small text-muted">Trạng thái phòng <span class="text-danger">*</span></label>
                                                <select name="status" class="form-select">
                                                    <option value="available" <?= $isAvailable ? 'selected' : '' ?>>Phòng trống (available)</option>
                                                    <option value="rented" <?= !$isAvailable ? 'selected' : '' ?>>Đang thuê (rented)</option>
                                                </select>
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label fw-bold small text-muted">Giá thuê niêm yết (VNĐ/tháng) <span class="text-danger">*</span></label>
                                                <input type="number" step="1000" min="1000" name="price" class="form-control text-teal-main"
                                                       value="<?= h((string)(float)$room['price']) ?>" required>
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label fw-bold small text-muted">Mô tả &amp; Tiện nghi phòng</label>
                                                <textarea name="description" class="form-control" rows="3"
                                                          placeholder="VD: Phòng tầng 2, ban công thoáng mát, điều hòa, nóng lạnh..."><?= h($room['description'] ?? '') ?></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0 px-4 pb-4 pt-0">
                                        <button type="button" class="btn-pill-white" data-bs-dismiss="modal">Hủy</button>
                                        <button type="submit" class="btn-teal-custom">
                                            <i class="fa-solid fa-floppy-disk"></i> Lưu thay đổi
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <div class="empty-folder-illus"></div>
                        <h5 class="fw-black text-dark mb-1" style="font-weight: 900;">Không tìm thấy phòng trọ phù hợp</h5>
                        <p class="text-muted fw-semibold mb-3">Thử thay đổi điều kiện lọc hoặc thêm phòng trọ mới vào hệ thống.</p>
                        <div class="d-flex justify-content-center gap-2">
                            <?php if ($hasFilter): ?>
                                <a href="index.php?route=rooms" class="btn-pill-white">
                                    <i class="fa-solid fa-rotate-left"></i> Xem tất cả phòng
                                </a>
                            <?php endif; ?>
                            <button type="button" class="btn-teal-custom" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                                <i class="fa-solid fa-plus"></i> Thêm Phòng Mới
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- ================= MODAL THÊM PHÒNG TRỌ MỚI (ĐỒNG BỘ CSDL CHUNG) ================= -->
    <div class="modal fade modal-staymate" id="addRoomModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="index.php?route=rooms" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-black text-teal-main" style="font-weight: 900;">
                        <i class="fa-solid fa-house-medical me-2"></i>Thêm Phòng trọ mới
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
                    <input type="hidden" name="action" value="create">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Mã phòng <span class="text-danger">*</span></label>
                            <input type="text" name="room_number" class="form-control" maxlength="20" placeholder="VD: P103, P203..." required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Trạng thái ban đầu</label>
                            <select name="status" class="form-select">
                                <option value="available" selected>Phòng trống (available)</option>
                                <option value="rented">Đang thuê (rented)</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">Giá thuê niêm yết (VNĐ/tháng) <span class="text-danger">*</span></label>
                            <input type="number" step="1000" min="1000" name="price" class="form-control text-teal-main" value="3000000" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">Mô tả &amp; Tiện nghi phòng</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="VD: Phòng tầng 1, tiêu chuẩn, điều hòa, nóng lạnh, WC riêng..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn-pill-white" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn-teal-custom">
                        <i class="fa-solid fa-check"></i> Lưu phòng mới
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= FOOTER CHUẨN TRANG TỔNG QUAN ================= -->
    <footer class="footer-custom container-fluid px-4 px-xl-5 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div><i class="fa-solid fa-building me-1" style="color:var(--teal-main);"></i> © 2026 StayMate - Quản lý Nhà trọ Chuyên nghiệp</div>
        <div class="d-flex flex-wrap gap-4">
            <span><i class="fa-solid fa-shield-halved me-1" style="color:var(--teal-main);"></i> WAMP MVC Architecture</span>
            <span><i class="fa-solid fa-bolt me-1 text-warning"></i> Tự động hóa Điện, Nước &amp; Hóa đơn</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
