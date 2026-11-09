<?php
require_once __DIR__ . '/../Models/RoomModel.php';
require_once __DIR__ . '/../Models/ReportModel.php';

class RoomController {
    private RoomModel $model;

    public function __construct() {
        $this->model = new RoomModel();
    }

    public function index(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Xử lý Thêm, Sửa, Xóa (POST) có kiểm tra CSRF token
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!hash_equals($_SESSION['csrf_token'], $token)) {
                $_SESSION['room_flash_error'] = 'Phiên làm việc không hợp lệ (CSRF). Vui lòng thử lại!';
                header("Location: index.php?route=rooms");
                exit;
            }

            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $room_number = strtoupper(trim($_POST['room_number'] ?? ''));
                $priceRaw = str_replace([',', '.', ' '], '', trim($_POST['price'] ?? '0'));
                $price = is_numeric($_POST['price'] ?? '') ? (float)$_POST['price'] : (float)$priceRaw;
                $status = $this->validStatus($_POST['status'] ?? 'available');
                $building = trim($_POST['building'] ?? 'Tòa A - Smart Stay Sunrise');
                $room_type = trim($_POST['room_type'] ?? 'Phòng Tiêu chuẩn (Standard) (2.600.000đ)');
                $floor = trim($_POST['floor'] ?? '1');
                
                // Bỏ ô diện tích, tự động gán diện tích chuẩn theo loại phòng nếu không nhập
                $area = trim($_POST['area'] ?? '');
                if ($area === '') {
                    if (stripos($room_type, 'Studio') !== false) {
                        $area = '35.00';
                    } elseif (stripos($room_type, 'VIP') !== false || stripos($room_type, 'Cao cấp') !== false) {
                        $area = '28.00';
                    } else {
                        $area = '22.00';
                    }
                }

                $max_occupants = trim($_POST['max_occupants'] ?? '2');
                $amenities = trim($_POST['amenities'] ?? '');
                $note = trim($_POST['note'] ?? $_POST['description'] ?? '');

                // Xử lý upload ảnh hoặc chọn ảnh mẫu
                $roomImage = '';
                if (!empty($_FILES['room_image_file']['name']) && $_FILES['room_image_file']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../../public/uploads/rooms/';
                    if (!is_dir($uploadDir)) {
                        @mkdir($uploadDir, 0777, true);
                    }
                    $fileTmp  = $_FILES['room_image_file']['tmp_name'];
                    $fileExt  = strtolower(pathinfo($_FILES['room_image_file']['name'], PATHINFO_EXTENSION));
                    $allowed  = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                    if (in_array($fileExt, $allowed, true)) {
                        $fileName = 'room_' . time() . '_' . mt_rand(1000, 9999) . '.' . $fileExt;
                        $destPath = $uploadDir . $fileName;
                        if (move_uploaded_file($fileTmp, $destPath)) {
                            $roomImage = 'public/uploads/rooms/' . $fileName;
                        }
                    }
                }
                if ($roomImage === '') {
                    $roomImage = trim($_POST['sample_image'] ?? '');
                }
                if ($roomImage === '') {
                    if (stripos($room_type, 'Studio') !== false) {
                        $roomImage = 'https://images.unsplash.com/photo-1536376072261-38c75010e6c9?auto=format&fit=crop&w=900&q=85';
                    } elseif (stripos($room_type, 'VIP') !== false || stripos($room_type, 'Cao cấp') !== false) {
                        $roomImage = 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=900&q=85';
                    } else {
                        $roomImage = 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=900&q=85';
                    }
                }

                // Dịch vụ khác được thêm bằng nút (+)
                $serviceNames  = $_POST['service_names'] ?? [];
                $servicePrices = $_POST['service_prices'] ?? [];
                $serviceUnits  = $_POST['service_units'] ?? [];

                $servicesToSave = [];
                for ($i = 0; $i < count($serviceNames); $i++) {
                    $sName = trim($serviceNames[$i] ?? '');
                    if ($sName === '') continue;
                    $sPrice = (float)($servicePrices[$i] ?? 0);
                    $sUnit = trim($serviceUnits[$i] ?? 'Tháng') ?: 'Tháng';
                    $servicesToSave[] = [
                        'service_name' => $sName,
                        'unit_price'   => $sPrice,
                        'unit'         => $sUnit,
                    ];
                }

                $descParts = [];
                if ($building !== '') $descParts[] = "Tòa: {$building}";
                if ($room_type !== '') $descParts[] = "Loại: {$room_type}";
                if ($floor !== '') $descParts[] = "Tầng {$floor}";
                if ($area !== '') $descParts[] = "Diện tích: {$area} m²";
                if ($max_occupants !== '') $descParts[] = "Tối đa: {$max_occupants} người";
                if ($amenities !== '') $descParts[] = "Tiện nghi: {$amenities}";
                if ($roomImage !== '') $descParts[] = "Ảnh: {$roomImage}";
                if (!empty($servicesToSave)) {
                    $sTexts = array_map(function($s) {
                        return $s['service_name'] . ' (' . number_format($s['unit_price'], 0, ',', '.') . 'đ/' . $s['unit'] . ')';
                    }, $servicesToSave);
                    $descParts[] = "Dịch vụ: " . implode(', ', $sTexts);
                }
                if ($note !== '') $descParts[] = $note;

                $description = !empty($descParts) ? implode(" | ", $descParts) : trim($_POST['description'] ?? '');

                if ($room_number === '' || mb_strlen($room_number) > 20) {
                    $_SESSION['room_flash_error'] = 'Vui lòng nhập mã phòng hợp lệ (tối đa 20 ký tự)!';
                } elseif ($price <= 0) {
                    $_SESSION['room_flash_error'] = 'Giá thuê phòng phải là số lớn hơn 0!';
                } else {
                    try {
                        $bId = 1;
                        if (stripos($building, 'B') !== false) $bId = 2;
                        elseif (stripos($building, 'C') !== false) $bId = 3;

                        $newId = $this->model->createRoom($room_number, $price, $status, $description, $bId);
                        if ($newId > 0 && !empty($servicesToSave)) {
                            $this->model->saveRoomServices($newId, $servicesToSave);
                        }
                        $_SESSION['room_flash_success'] = "Đã thêm phòng {$room_number} vào hệ thống thành công!";
                        ReportModel::recordActivity($_SESSION['room_flash_success'], 'success');
                    } catch (PDOException $e) {
                        $_SESSION['room_flash_error'] = "Không thể thêm: Mã phòng '{$room_number}' đã tồn tại trong CSDL!";
                    }
                }
                header("Location: index.php?route=rooms");
                exit;
            } elseif ($action === 'update') {
                $id = (int)($_POST['id'] ?? 0);
                $room_number = strtoupper(trim($_POST['room_number'] ?? ''));
                $priceRaw = str_replace([',', '.', ' '], '', trim($_POST['price'] ?? '0'));
                $price = is_numeric($_POST['price'] ?? '') ? (float)$_POST['price'] : (float)$priceRaw;
                $status = $this->validStatus($_POST['status'] ?? 'available');
                $building = trim($_POST['building'] ?? 'Tòa A - Smart Stay Sunrise');
                $room_type = trim($_POST['room_type'] ?? 'Phòng Tiêu chuẩn (Standard) (2.600.000đ)');
                $floor = trim($_POST['floor'] ?? '1');
                $area = trim($_POST['area'] ?? '22.00');
                $max_occupants = trim($_POST['max_occupants'] ?? '2');
                $amenities = trim($_POST['amenities'] ?? '');
                $note = trim($_POST['note'] ?? $_POST['description'] ?? '');

                // Dịch vụ khác của phòng
                $serviceNames  = $_POST['service_names'] ?? [];
                $servicePrices = $_POST['service_prices'] ?? [];
                $serviceUnits  = $_POST['service_units'] ?? [];

                $servicesToSave = [];
                for ($i = 0; $i < count($serviceNames); $i++) {
                    $sName = trim($serviceNames[$i] ?? '');
                    if ($sName === '') continue;
                    $sPrice = (float)($servicePrices[$i] ?? 0);
                    $sUnit = trim($serviceUnits[$i] ?? 'Tháng') ?: 'Tháng';
                    $servicesToSave[] = [
                        'service_name' => $sName,
                        'unit_price'   => $sPrice,
                        'unit'         => $sUnit,
                    ];
                }

                $descParts = [];
                if ($building !== '') $descParts[] = "Tòa: {$building}";
                if ($room_type !== '') $descParts[] = "Loại: {$room_type}";
                if ($floor !== '') $descParts[] = "Tầng {$floor}";
                if ($area !== '') $descParts[] = "Diện tích: {$area} m²";
                if ($max_occupants !== '') $descParts[] = "Tối đa: {$max_occupants} người";
                if ($amenities !== '') $descParts[] = "Tiện nghi: {$amenities}";
                if (!empty($servicesToSave)) {
                    $sTexts = array_map(function($s) {
                        return $s['service_name'] . ' (' . number_format($s['unit_price'], 0, ',', '.') . 'đ/' . $s['unit'] . ')';
                    }, $servicesToSave);
                    $descParts[] = "Dịch vụ: " . implode(', ', $sTexts);
                }
                if ($note !== '') $descParts[] = $note;

                $description = !empty($descParts) ? implode(" | ", $descParts) : trim($_POST['description'] ?? '');

                if ($id <= 0 || $room_number === '' || mb_strlen($room_number) > 20) {
                    $_SESSION['room_flash_error'] = 'Thông tin mã phòng không hợp lệ!';
                } elseif ($price <= 0) {
                    $_SESSION['room_flash_error'] = 'Giá thuê phòng phải là số lớn hơn 0!';
                } else {
                    try {
                        $this->model->updateRoom($id, $room_number, $price, $status, $description);
                        $this->model->saveRoomServices($id, $servicesToSave);
                        $_SESSION['room_flash_success'] = "Đã cập nhật thông tin phòng {$room_number} thành công!";
                        ReportModel::recordActivity($_SESSION['room_flash_success'], 'info');
                    } catch (PDOException $e) {
                        $_SESSION['room_flash_error'] = "Không thể cập nhật: Mã phòng '{$room_number}' đã bị trùng với phòng khác!";
                    }
                }
                header("Location: index.php?route=rooms");
                exit;
            } elseif ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $roomInfo = $this->model->getRoomById($id);
                    $roomLabel = $roomInfo['room_number'] ?? ('#' . $id);
                    try {
                        $this->model->deleteRoom($id);
                        $_SESSION['room_flash_success'] = "Đã xóa phòng {$roomLabel} khỏi hệ thống!";
                        ReportModel::recordActivity($_SESSION['room_flash_success'], 'warning');
                    } catch (PDOException $e) {
                        $_SESSION['room_flash_error'] = "Không thể xóa phòng {$roomLabel}: Phòng đang có hợp đồng, chỉ số điện nước hoặc hóa đơn liên quan trong CSDL!";
                    }
                }
                header("Location: index.php?route=rooms");
                exit;
            }
        }

        // Lấy thông báo flash (nếu có)
        $flash_success = $_SESSION['room_flash_success'] ?? '';
        $flash_error = $_SESSION['room_flash_error'] ?? '';
        unset($_SESSION['room_flash_success'], $_SESSION['room_flash_error']);

        // Hỗ trợ tương thích tham số msg cũ nếu có link bên ngoài gọi tới
        if ($flash_success === '' && isset($_GET['msg'])) {
            if ($_GET['msg'] === 'created') $flash_success = 'Đã thêm phòng trọ mới thành công!';
            elseif ($_GET['msg'] === 'updated') $flash_success = 'Cập nhật thông tin phòng thành công!';
            elseif ($_GET['msg'] === 'deleted') $flash_success = 'Đã xóa phòng trọ khỏi hệ thống!';
        }

        // Bộ lọc tìm kiếm
        $filters = [
            'keyword'     => trim($_GET['keyword'] ?? ''),
            'status'      => trim($_GET['status'] ?? ''),
            'floor'       => trim($_GET['floor'] ?? ''),
            'price_range' => trim($_GET['price_range'] ?? ''),
        ];

        $stats    = $this->model->getRoomStats();
        $services = $this->model->getServices();
        $floors   = $this->model->getAvailableFloors();
        $rooms    = $this->model->getRooms(
            $filters['keyword'],
            $filters['status'],
            $filters['floor'],
            $filters['price_range']
        );

        require_once __DIR__ . '/../views/rooms/index.php';
    }

    private function validStatus(string $status): string {
        return in_array($status, ['available', 'rented'], true) ? $status : 'available';
    }
}
?>