<?php
require_once __DIR__ . '/../Models/ReportModel.php';
require_once __DIR__ . '/../Models/AccountModel.php';

class DashboardController {
    private ReportModel $reportModel;

    public function __construct() {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $this->reportModel = new ReportModel();
    }

    // Luồng chính: GET/POST ?route=dashboard (Trang Quản lý Tòa nhà)
    public function index(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userRole      = $_SESSION['role'] ?? 'admin';
        $allowedBldIds = $this->reportModel->getAllowedBuildingIds();

        // Xử lý các thao tác POST ngay trên trang Tòa nhà (Thêm/Sửa/Xóa Tòa nhà, Thu tiền nhanh, Đổi mật khẩu)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

                        if ($action === 'add_room_type') {
                if ($userRole !== 'admin') {
                    $_SESSION['flash_error'] = "Chỉ Admin mới có quyền thêm loại phòng!";
                    header("Location: index.php?route=dashboard");
                    exit;
                }
                $rtName      = trim($_POST['rt_name'] ?? '');
                $rtPrice     = (float)str_replace(['.', ',', ' '], '', $_POST['rt_price'] ?? '0');
                $rtArea      = trim($_POST['rt_area'] ?? '');
                $rtAmenities = trim($_POST['rt_amenities'] ?? '');

                if ($rtName !== '') {
                    $this->reportModel->addRoomType($rtName, $rtPrice, $rtArea, $rtAmenities);
                    $this->reportModel->logActivity("Đã thêm loại phòng mới: '{$rtName}' (" . number_format($rtPrice, 0, ',', '.') . " đ)", 'success');
                    $_SESSION['flash_success'] = "Đã thêm loại phòng '{$rtName}' thành công!";
                } else {
                    $_SESSION['flash_error'] = "Vui lòng nhập tên loại phòng.";
                }
                header("Location: index.php?route=dashboard");
                exit;
            }

            if ($action === 'add_building') {
                if ($userRole !== 'admin') {
                    $_SESSION['flash_error'] = "Chỉ Admin mới có quyền thêm tòa nhà mới!";
                    header("Location: index.php?route=dashboard");
                    exit;
                }
                $code       = trim($_POST['code'] ?? '');
                $name       = trim($_POST['name'] ?? '');
                $address      = trim($_POST['address'] ?? '');
                $manager      = trim($_POST['manager'] ?? '');
                $managerPhone = trim($_POST['manager_phone'] ?? '');
                $managerEmail = trim($_POST['manager_email'] ?? '');
                $managerAcc   = trim($_POST['manager_acc'] ?? '');
                $managerPwd   = trim($_POST['manager_pwd'] ?? '123');
                if ($managerPwd === '') $managerPwd = '123';
                $roomPrefix = trim($_POST['room_prefix'] ?? '');
                $theme      = trim($_POST['theme'] ?? 'teal');

                if ($name !== '') {
                    $selectedRoomTypes = $_POST['room_types'] ?? [];
                    if (!is_array($selectedRoomTypes) || empty($selectedRoomTypes)) {
                        $selectedRoomTypes = ['Phòng Tiêu Chuẩn'];
                    }
                    if ($roomPrefix === '') {
                        $roomPrefix = 'P' . (count($this->reportModel->getBuildingsConfig()) + 1);
                    }
                    $this->reportModel->addBuilding($code, $name, $address, $manager, $managerAcc, $roomPrefix, $theme, $selectedRoomTypes, $managerPhone, $managerEmail);

                    // Tự động tạo hoặc cập nhật tài khoản quản lý trong bảng users với mật khẩu mặc định là 123
                    if ($managerAcc !== '') {
                        try {
                            $db = Database::getConnection();
                            $chk = $db->prepare("SELECT id FROM users WHERE username = :u LIMIT 1");
                            $chk->execute([':u' => $managerAcc]);
                            $existingUserId = $chk->fetchColumn();

                            if ($existingUserId) {
                                $effectiveEmail = $managerEmail !== '' ? $managerEmail : ($managerAcc . '@nhatro.com');
                                $up = $db->prepare("UPDATE users SET full_name = :fn, phone = :phone, email = :em, role = 'landlord', password = :pwd WHERE id = :id");
                                $up->execute([
                                    ':fn'    => $manager !== '' ? $manager : 'Quản lý ' . $name,
                                    ':phone' => $managerPhone,
                                    ':em'    => $effectiveEmail,
                                    ':pwd'   => $managerPwd,
                                    ':id'    => $existingUserId
                                ]);
                            } else {
                                $effectiveEmail = $managerEmail !== '' ? $managerEmail : ($managerAcc . '@nhatro.com');
                                $ins = $db->prepare("INSERT INTO users (username, password, full_name, phone, email, role, created_at) VALUES (:u, :pwd, :fn, :phone, :em, 'landlord', NOW())");
                                $ins->execute([
                                    ':u'     => $managerAcc,
                                    ':pwd'   => $managerPwd,
                                    ':fn'    => $manager !== '' ? $manager : 'Quản lý ' . $name,
                                    ':phone' => $managerPhone,
                                    ':em'    => $effectiveEmail
                                ]);
                            }
                        } catch (Exception $e) {}
                    }

                    $this->reportModel->logActivity("Đã thêm mới tòa nhà '{$name}' ({$code}) kèm tài khoản quản lý '{$managerAcc}'", 'success');
                    $_SESSION['flash_success'] = "Đã thêm tòa nhà '{$name}' thành công! Tài khoản '{$managerAcc}' (Mật khẩu: {$managerPwd}) đã sẵn sàng đăng nhập.";
                } else {
                    $_SESSION['flash_error'] = "Vui lòng nhập tên tòa nhà.";
                }
                header("Location: index.php?route=dashboard");
                exit;
            } elseif ($action === 'edit_building') {
                $id         = trim($_POST['building_id'] ?? '');
                $code       = trim($_POST['code'] ?? '');
                $name       = trim($_POST['name'] ?? '');
                $address      = trim($_POST['address'] ?? '');
                $manager      = trim($_POST['manager'] ?? '');
                $managerPhone = trim($_POST['manager_phone'] ?? '');
                $managerEmail = trim($_POST['manager_email'] ?? '');
                $managerAcc   = trim($_POST['manager_acc'] ?? '');
                $roomPrefix   = trim($_POST['room_prefix'] ?? '');
                $selectedRoomTypes = $_POST['room_types'] ?? [];
                if (!is_array($selectedRoomTypes) || empty($selectedRoomTypes)) {
                    $selectedRoomTypes = ['Phòng Tiêu Chuẩn'];
                }

                if ($id !== '' && $name !== '') {
                    $this->reportModel->updateBuilding($id, $code, $name, $address, $manager, $managerAcc, $roomPrefix, $selectedRoomTypes, $managerPhone, $managerEmail);
                    if ($managerAcc !== '') {
                        try {
                            $db = Database::getConnection();
                            $effectiveEmail = $managerEmail !== '' ? $managerEmail : ($managerAcc . '@nhatro.com');
                            $upAcc = $db->prepare("UPDATE users SET full_name = :fn, phone = :phone, email = :em WHERE username = :u");
                            $upAcc->execute([
                                ':fn'    => $manager !== '' ? $manager : 'Quản lý ' . $name,
                                ':phone' => $managerPhone,
                                ':em'    => $effectiveEmail,
                                ':u'     => $managerAcc
                            ]);
                        } catch (Exception $e) {}
                    }
                    $this->reportModel->logActivity("Đã cập nhật thông tin tòa nhà '{$name}'", 'info');
                    $_SESSION['flash_success'] = "Đã cập nhật thông tin '{$name}' thành công!";
                }
                header("Location: index.php?route=dashboard");
                exit;
            } elseif ($action === 'delete_building') {
                $id = trim($_POST['building_id'] ?? '');
                if ($id !== '' && $this->reportModel->deleteBuilding($id)) {
                    $this->reportModel->logActivity("Đã xóa tòa nhà mã '{$id}' khỏi danh sách", 'warning');
                    $_SESSION['flash_success'] = "Đã xóa tòa nhà thành công!";
                } else {
                    $_SESSION['flash_error'] = "Không thể xóa tòa nhà mặc định cuối cùng.";
                }
                header("Location: index.php?route=dashboard");
                exit;
            } elseif ($action === 'edit_user_account') {
                $userId = (int)($_POST['user_id'] ?? 0);
                $fullName = trim($_POST['full_name'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $role = trim($_POST['role'] ?? '');
                $newPwd = trim($_POST['new_password'] ?? '');

                if ($userId > 0 && $fullName !== '' && $email !== '') {
                    try {
                        $accountModel = new AccountModel();
                        $accountModel->updateAccount($userId, $fullName, $phone, $email, $role !== '' ? $role : null);
                        if ($newPwd !== '') {
                            $accountModel->resetPassword($userId, $newPwd);
                        }
                        $_SESSION['flash_success'] = "Đã cập nhật thông tin tài khoản '{$fullName}' thành công!";
                    } catch (Exception $e) {
                        $_SESSION['flash_error'] = $e->getMessage();
                    }
                }
                header("Location: index.php?route=dashboard");
                exit;
            } elseif ($action === 'delete_user_account') {
                $userId = (int)($_POST['user_id'] ?? 0);
                $currentUserId = (int)($_SESSION['user_id'] ?? 0);

                if ($userId > 0) {
                    if ($userId === $currentUserId) {
                        $_SESSION['flash_error'] = "Không thể xóa tài khoản bạn đang đăng nhập!";
                    } else {
                        try {
                            $accountModel = new AccountModel();
                            $accountModel->deleteAccount($userId);
                            $_SESSION['flash_success'] = "Đã xóa tài khoản thành công!";
                        } catch (Exception $e) {
                            $_SESSION['flash_error'] = $e->getMessage();
                        }
                    }
                }
                header("Location: index.php?route=dashboard");
                exit;
            } elseif ($action === 'change_password') {
                $oldPass = trim($_POST['old_password'] ?? '');
                $newPass = trim($_POST['new_password'] ?? '');
                $cfmPass = trim($_POST['confirm_password'] ?? '');
                $userId  = (int)($_SESSION['user_id'] ?? 1);

                if ($newPass !== $cfmPass) {
                    $_SESSION['flash_error'] = "Mật khẩu xác nhận không khớp!";
                } elseif (strlen($newPass) < 4) {
                    $_SESSION['flash_error'] = "Mật khẩu mới phải từ 4 ký tự trở lên!";
                } else {
                    $db = Database::getConnection();
                    $stmt = $db->prepare("SELECT password FROM users WHERE id = :id");
                    $stmt->execute([':id' => $userId]);
                    $currPassInDb = (string)$stmt->fetchColumn();

                    $validOld = ($oldPass === $currPassInDb || password_verify($oldPass, $currPassInDb));
                    if (!$validOld) {
                        $_SESSION['flash_error'] = "Mật khẩu hiện tại không chính xác!";
                    } else {
                        $upStmt = $db->prepare("UPDATE users SET password = :p WHERE id = :id");
                        $upStmt->execute([':p' => $newPass, ':id' => $userId]);
                        $this->reportModel->logActivity("Đã đổi mật khẩu tài khoản thành công", 'success');
                        $_SESSION['flash_success'] = "Đã cập nhật mật khẩu mới thành công!";
                    }
                }
                header("Location: index.php?route=dashboard");
                exit;
            } elseif ($action === 'pay_invoice') {
                $invoiceId = (int)($_POST['invoice_id'] ?? 0);
                if ($invoiceId > 0 && $this->reportModel->markInvoiceStatus($invoiceId, 'paid')) {
                    $this->reportModel->logActivity("Đã xác nhận thu tiền hóa đơn #{$invoiceId}", 'success');
                    $_SESSION['flash_success'] = "Đã xác nhận thu tiền hóa đơn #{$invoiceId} thành công!";
                }
                header("Location: index.php?route=dashboard");
                exit;
            }
        }

        $allowedBldIds = $this->reportModel->getAllowedBuildingIds();
        $userRole      = $_SESSION['role'] ?? 'admin';

        // Chỉ Admin mới được xem trang Quản lý Tòa nhà.
        // Quản lý tòa nào thì chuyển thẳng sang trang Tổng quan của tòa đấy!
        if ($userRole !== 'admin') {
            $onlyBld = reset($allowedBldIds) ?: 'A';
            $_SESSION['selected_building_id'] = $onlyBld;
            header("Location: index.php?route=overview&building_id=" . urlencode($onlyBld));
            exit;
        }

        $filterBuilding = trim($_GET['building_id'] ?? 'all');

        $keyword   = trim($_GET['keyword'] ?? '');
        $status    = trim($_GET['status'] ?? '');

        $buildings = $this->reportModel->getBuildingsWithStats();
        $stats         = $this->reportModel->getSummaryStats($filterBuilding);
        $chartData     = $this->reportModel->getMonthlyRevenue($filterBuilding);
        $debtList      = $this->reportModel->getDebtList($keyword, $status, $filterBuilding);
        $roomsList     = $this->reportModel->getRoomsList($filterBuilding);
        $contractsList = $this->reportModel->getContractsList($filterBuilding);
        $notifications = $this->reportModel->getNotifications($filterBuilding);

        $flashSuccess = $_SESSION['flash_success'] ?? '';
        $flashError   = $_SESSION['flash_error'] ?? '';
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $roomTypesList = $this->reportModel->getRoomTypes();
        $accountModel = new AccountModel();
        $allAccountsList = $accountModel->getAccounts('', '', $_SESSION['role'] ?? 'admin', 'all');
        require_once __DIR__ . '/../views/dashboard/index.php';
    }

    // Luồng mới: GET/POST ?route=overview (Trang Tổng quan Tòa nhà & Hệ thống)
    public function overview(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $buildings = $this->reportModel->getBuildingsWithStats();
        $firstBld  = !empty($buildings) ? (string)array_key_first($buildings) : 'A';

        $allowedBldIds = $this->reportModel->getAllowedBuildingIds();
        $userRole      = $_SESSION['role'] ?? 'admin';

        $reqBld = trim($_GET['building_id'] ?? ($_POST['building_id'] ?? ''));

        if ($userRole !== 'admin') {
            $onlyBld = reset($allowedBldIds) ?: 'A';
            if ($reqBld !== $onlyBld) {
                $_SESSION['flash_error'] = "Tài khoản của bạn chỉ có quyền quản lý " . ($buildings[$onlyBld]['name'] ?? ('Tòa ' . $onlyBld)) . "!";
                header("Location: index.php?route=overview&building_id=" . urlencode($onlyBld));
                exit;
            }
            $building_id = $onlyBld;
            $_SESSION['selected_building_id'] = $building_id;
        } else {
            if ($reqBld !== '' && $reqBld !== 'all' && isset($buildings[$reqBld])) {
                $_SESSION['selected_building_id'] = $reqBld;
                $building_id = $reqBld;
            } elseif (!empty($_SESSION['selected_building_id']) && isset($buildings[$_SESSION['selected_building_id']])) {
                $building_id = $_SESSION['selected_building_id'];
            } else {
                $building_id = $firstBld;
                $_SESSION['selected_building_id'] = $building_id;
            }
        }

        // Xử lý các thao tác POST ngay trên trang Tổng quan (Lập hóa đơn mới, Thu tiền, Hoàn tác, Xóa hóa đơn, Đổi mật khẩu)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $redirectUrl = "index.php?route=overview&building_id=" . urlencode($building_id);

            if ($action === 'change_password') {
                $oldPass = trim($_POST['old_password'] ?? '');
                $newPass = trim($_POST['new_password'] ?? '');
                $cfmPass = trim($_POST['confirm_password'] ?? '');
                $userId  = (int)($_SESSION['user_id'] ?? 1);

                if ($newPass !== $cfmPass) {
                    $_SESSION['flash_error'] = "Mật khẩu xác nhận không khớp!";
                } elseif (strlen($newPass) < 4) {
                    $_SESSION['flash_error'] = "Mật khẩu mới phải từ 4 ký tự trở lên!";
                } else {
                    $db = Database::getConnection();
                    $stmt = $db->prepare("SELECT password FROM users WHERE id = :id");
                    $stmt->execute([':id' => $userId]);
                    $currPassInDb = (string)$stmt->fetchColumn();

                    $validOld = ($oldPass === $currPassInDb || password_verify($oldPass, $currPassInDb));
                    if (!$validOld) {
                        $_SESSION['flash_error'] = "Mật khẩu hiện tại không chính xác!";
                    } else {
                        $upStmt = $db->prepare("UPDATE users SET password = :p WHERE id = :id");
                        $upStmt->execute([':p' => $newPass, ':id' => $userId]);
                        $this->reportModel->logActivity("Đã đổi mật khẩu tài khoản thành công", 'success');
                        $_SESSION['flash_success'] = "Đã cập nhật mật khẩu mới thành công!";
                    }
                }
                header("Location: " . $redirectUrl);
                exit;
            } elseif ($action === 'create_invoice') {
                $roomId    = (int)($_POST['room_id'] ?? 0);
                $monthYear = trim($_POST['month_year'] ?? date('m-Y'));
                $elecOld   = (int)($_POST['electric_old'] ?? 0);
                $elecNew   = (int)($_POST['electric_new'] ?? 0);
                $waterOld  = (int)($_POST['water_old'] ?? 0);
                $waterNew  = (int)($_POST['water_new'] ?? 0);

                $res = $this->reportModel->createNewInvoice($roomId, $monthYear, $elecOld, $elecNew, $waterOld, $waterNew);
                if ($res['ok']) {
                    $this->reportModel->logActivity("Đã lập hóa đơn mới cho phòng ID {$roomId} (Kỳ {$monthYear})", 'success');
                    $_SESSION['flash_success'] = $res['msg'];
                } else {
                    $_SESSION['flash_error'] = $res['msg'];
                }
                header("Location: " . $redirectUrl . "#invoice-section");
                exit;
            } elseif ($action === 'pay_invoice') {
                $invoiceId = (int)($_POST['invoice_id'] ?? 0);
                if ($invoiceId > 0 && $this->reportModel->markInvoiceStatus($invoiceId, 'paid')) {
                    $this->reportModel->logActivity("Đã thu tiền hóa đơn #{$invoiceId}", 'success');
                    $_SESSION['flash_success'] = "Đã thu tiền hóa đơn #{$invoiceId} thành công! Doanh thu thực thu đã được cập nhật.";
                }
                header("Location: " . $redirectUrl . "#invoice-section");
                exit;
            } elseif ($action === 'unpay_invoice') {
                $invoiceId = (int)($_POST['invoice_id'] ?? 0);
                if ($invoiceId > 0 && $this->reportModel->markInvoiceStatus($invoiceId, 'unpaid')) {
                    $this->reportModel->logActivity("Đã hoàn tác hóa đơn #{$invoiceId} về trạng thái Chưa thu", 'warning');
                    $_SESSION['flash_success'] = "Đã chuyển trạng thái hóa đơn #{$invoiceId} về Chưa thu.";
                }
                header("Location: " . $redirectUrl . "#invoice-section");
                exit;
            } elseif ($action === 'delete_invoice') {
                $invoiceId = (int)($_POST['invoice_id'] ?? 0);
                if ($invoiceId > 0 && $this->reportModel->deleteInvoice($invoiceId)) {
                    $this->reportModel->logActivity("Đã xóa hóa đơn #{$invoiceId} khỏi CSDL", 'warning');
                    $_SESSION['flash_success'] = "Đã xóa hóa đơn #{$invoiceId} khỏi CSDL.";
                }
                header("Location: " . $redirectUrl . "#invoice-section");
                exit;
            }
        }

        $keyword   = trim($_GET['keyword'] ?? '');
        $status    = trim($_GET['status'] ?? '');
        $monthYear = trim($_GET['month_year'] ?? '');

        $buildings     = $this->reportModel->getBuildingsWithStats();
        $stats         = $this->reportModel->getSummaryStats($building_id);
        $chartData     = $this->reportModel->getMonthlyRevenue($building_id);
        $debtList      = $this->reportModel->getDebtList($keyword, $status, $building_id, $monthYear);
        $roomsList     = $this->reportModel->getRoomsList($building_id);
        $contractsList = $this->reportModel->getContractsList($building_id);
        $servicesMap   = $this->reportModel->getServicesMap();
        $notifications = $this->reportModel->getNotifications($building_id);

        $flashSuccess = $_SESSION['flash_success'] ?? '';
        $flashError   = $_SESSION['flash_error'] ?? '';
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        require_once __DIR__ . '/../views/dashboard/overview.php';
    }

    // Hiển thị giao diện Đăng nhập: GET ?route=login
    public function loginView(): void {
        $error = $_GET['error'] ?? '';
        require_once __DIR__ . '/../views/auth/login.php';
    }
}
