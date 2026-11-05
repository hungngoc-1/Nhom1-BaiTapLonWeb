<?php
require_once __DIR__ . '/../Models/TenantModel.php';
require_once __DIR__ . '/../Models/RoomModel.php';
require_once __DIR__ . '/../Models/TenantPortalModel.php';

class TenantController {
    private TenantModel $model;
    private RoomModel $roomModel;
    private TenantPortalModel $portalModel;

    public function __construct() {
        $this->model = new TenantModel();
        $this->roomModel = new RoomModel();
        $this->portalModel = new TenantPortalModel();
    }

    public function index(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!hash_equals($_SESSION['csrf_token'], $token)) {
                $_SESSION['tenant_flash_error'] = 'Phiên làm việc không hợp lệ (CSRF). Vui lòng thử lại!';
                header("Location: index.php?route=tenants");
                exit;
            }

            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $fullName         = trim($_POST['full_name'] ?? '');
                $cccd             = trim($_POST['cccd'] ?? '');
                $phone            = trim($_POST['phone'] ?? '');
                $birthday         = trim($_POST['birthday'] ?? '');
                $gender           = trim($_POST['gender'] ?? 'Nam');
                $email            = trim($_POST['email'] ?? '');
                $occupation       = trim($_POST['occupation'] ?? '');
                $hometown         = trim($_POST['hometown'] ?? '');
                $emergencyContact = trim($_POST['emergency_contact'] ?? '');
                $status           = trim($_POST['status'] ?? 'Đang thuê');
                $roomId           = (int)($_POST['room_id'] ?? 0);
                $roommateName     = trim($_POST['roommate_name'] ?? '');
                $roommatePhone    = trim($_POST['roommate_phone'] ?? '');

                if ($fullName === '' || $phone === '') {
                    $_SESSION['tenant_flash_error'] = 'Vui lòng nhập đầy đủ Họ và tên và Số điện thoại khách thuê!';
                } elseif (!preg_match('/^0[0-9]{9}$/', $phone)) {
                    $_SESSION['tenant_flash_error'] = 'Số điện thoại không hợp lệ! Vui lòng nhập đúng 10 chữ số (bắt đầu bằng số 0).';
                } elseif ($cccd !== '' && !preg_match('/^[0-9]{9,12}$/', $cccd)) {
                    $_SESSION['tenant_flash_error'] = 'Số CCCD/CMND không hợp lệ! Phải là dãy gồm 9 đến 12 chữ số.';
                } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $_SESSION['tenant_flash_error'] = 'Địa chỉ email không đúng định dạng (VD: ten@gmail.com)!';
                } elseif ($emergencyContact !== '' && !preg_match('/^0[0-9]{9}$/', $emergencyContact)) {
                    $_SESSION['tenant_flash_error'] = 'Số liên hệ khẩn cấp không hợp lệ! Phải đúng 10 chữ số (bắt đầu bằng 0).';
                } elseif ($roommatePhone !== '' && !preg_match('/^0[0-9]{9}$/', $roommatePhone)) {
                    $_SESSION['tenant_flash_error'] = 'Số điện thoại bạn cùng phòng không hợp lệ! Phải đúng 10 chữ số (bắt đầu bằng 0).';
                } else {
                    try {
                        $newTenantId = $this->model->createTenantFull(
                            $fullName,
                            $cccd,
                            $phone,
                            $birthday ?: null,
                            $gender,
                            $email,
                            $occupation,
                            $hometown,
                            $emergencyContact,
                            $status,
                            $roommateName ?: null,
                            $roommatePhone ?: null
                        );

                        if ($roomId > 0) {
                            $this->model->assignRoomToTenant($newTenantId, $roomId, $roommateName, $roommatePhone);
                            $_SESSION['tenant_flash_success'] = "Đã thêm khách thuê '{$fullName}' thành công! Hồ sơ đã lưu phòng dự kiến (chưa tạo hợp đồng). Vui lòng vào trang Hợp đồng để thao tác lập hợp đồng khi bàn giao phòng.";
                        } else {
                            $_SESSION['tenant_flash_success'] = "Đã thêm khách thuê '{$fullName}' thành công! Tài khoản đăng nhập: {$phone} (Mật khẩu: 123)";
                        }
                    } catch (PDOException $e) {
                        $_SESSION['tenant_flash_error'] = 'Lỗi lưu thông tin: ' . $e->getMessage();
                    }
                }
                header("Location: index.php?route=tenants");
                exit;
            } elseif ($action === 'update') {
                $id               = (int)($_POST['id'] ?? 0);
                $fullName         = trim($_POST['full_name'] ?? '');
                $cccd             = trim($_POST['cccd'] ?? '');
                $phone            = trim($_POST['phone'] ?? '');
                $birthday         = trim($_POST['birthday'] ?? '');
                $gender           = trim($_POST['gender'] ?? 'Nam');
                $email            = trim($_POST['email'] ?? '');
                $occupation       = trim($_POST['occupation'] ?? '');
                $hometown         = trim($_POST['hometown'] ?? '');
                $emergencyContact = trim($_POST['emergency_contact'] ?? '');
                $status           = trim($_POST['status'] ?? 'Đang thuê');
                $roomId           = (int)($_POST['room_id'] ?? 0);
                $roommateName     = trim($_POST['roommate_name'] ?? '');
                $roommatePhone    = trim($_POST['roommate_phone'] ?? '');

                if ($id <= 0 || $fullName === '' || $phone === '') {
                    $_SESSION['tenant_flash_error'] = 'Vui lòng nhập đầy đủ thông tin khách thuê!';
                } elseif (!preg_match('/^0[0-9]{9}$/', $phone)) {
                    $_SESSION['tenant_flash_error'] = 'Số điện thoại không hợp lệ! Vui lòng nhập đúng 10 chữ số (bắt đầu bằng số 0).';
                } elseif ($cccd !== '' && !preg_match('/^[0-9]{9,12}$/', $cccd)) {
                    $_SESSION['tenant_flash_error'] = 'Số CCCD/CMND không hợp lệ! Phải là dãy gồm 9 đến 12 chữ số.';
                } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $_SESSION['tenant_flash_error'] = 'Địa chỉ email không đúng định dạng (VD: ten@gmail.com)!';
                } elseif ($emergencyContact !== '' && !preg_match('/^0[0-9]{9}$/', $emergencyContact)) {
                    $_SESSION['tenant_flash_error'] = 'Số liên hệ khẩn cấp không hợp lệ! Phải đúng 10 chữ số (bắt đầu bằng 0).';
                } elseif ($roommatePhone !== '' && !preg_match('/^0[0-9]{9}$/', $roommatePhone)) {
                    $_SESSION['tenant_flash_error'] = 'Số điện thoại bạn cùng phòng không hợp lệ! Phải đúng 10 chữ số (bắt đầu bằng 0).';
                } else {
                    try {
                        $this->model->updateTenantFull(
                            $id,
                            $fullName,
                            $cccd,
                            $phone,
                            $birthday ?: null,
                            $gender,
                            $email,
                            $occupation,
                            $hometown,
                            $emergencyContact,
                            $status,
                            $roommateName ?: null,
                            $roommatePhone ?: null
                        );

                        // Cập nhật thông tin bạn cùng phòng vào hợp đồng đang hoạt động
                        if ($roommateName !== '') {
                            $this->model->updateContractRoommate($id, $roommateName, $roommatePhone);
                        }

                        $_SESSION['tenant_flash_success'] = "Đã cập nhật thông tin khách thuê '{$fullName}' thành công!";
                    } catch (PDOException $e) {
                        $_SESSION['tenant_flash_error'] = 'Không thể cập nhật: ' . $e->getMessage();
                    }
                }
                header("Location: index.php?route=tenants");
                exit;
            } elseif ($action === 'delete') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    try {
                        $this->model->deleteTenant($id);
                        $_SESSION['tenant_flash_success'] = 'Đã xóa khách thuê khỏi hệ thống!';
                    } catch (PDOException $e) {
                        $_SESSION['tenant_flash_error'] = 'Không thể xóa: Khách thuê này đang có hợp đồng thuê phòng trong CSDL!';
                    }
                }
                header("Location: index.php?route=tenants");
                exit;
            } elseif ($action === 'approve_request') {
                $reqId = (int)($_POST['request_id'] ?? 0);
                $note = trim($_POST['manager_note'] ?? 'Đã xác nhận và cập nhật thông tin.');
                if ($reqId > 0 && $this->portalModel->approveRequest($reqId, $note)) {
                    $_SESSION['tenant_flash_success'] = 'Đã phê duyệt yêu cầu và tự động cập nhật thông tin khách thuê!';
                } else {
                    $_SESSION['tenant_flash_error'] = 'Không thể phê duyệt yêu cầu.';
                }
                header("Location: index.php?route=tenants");
                exit;
            } elseif ($action === 'reject_request') {
                $reqId = (int)($_POST['request_id'] ?? 0);
                $note = trim($_POST['manager_note'] ?? 'Yêu cầu không được phê duyệt.');
                if ($reqId > 0 && $this->portalModel->rejectRequest($reqId, $note)) {
                    $_SESSION['tenant_flash_success'] = 'Đã từ chối yêu cầu điều chỉnh.';
                } else {
                    $_SESSION['tenant_flash_error'] = 'Không thể từ chối yêu cầu.';
                }
                header("Location: index.php?route=tenants");
                exit;
            } elseif ($action === 'mark_lead_contacted') {
                $leadId = (int)($_POST['lead_id'] ?? 0);
                $custName = trim($_POST['customer_name'] ?? 'khách');
                if ($leadId > 0 && $this->model->markLeadContacted($leadId)) {
                    $_SESSION['tenant_flash_success'] = "Đã cập nhật: Đã liên hệ với {$custName} thành công! Khách đã chuyển sang bảng Khách Đã Liên Hệ.";
                } else {
                    $_SESSION['tenant_flash_error'] = 'Không thể cập nhật trạng thái liên hệ.';
                }
                header("Location: index.php?route=tenants#sec_leads");
                exit;
            } elseif ($action === 'delete_lead') {
                $leadId = (int)($_POST['lead_id'] ?? 0);
                if ($leadId > 0 && $this->model->deleteContactLead($leadId)) {
                    $_SESSION['tenant_flash_success'] = 'Đã xóa yêu cầu liên hệ khỏi hệ thống.';
                }
                header("Location: index.php?route=tenants#sec_leads");
                exit;
            }
        }

        $flash_success = $_SESSION['tenant_flash_success'] ?? '';
        $flash_error   = $_SESSION['tenant_flash_error'] ?? '';
        unset($_SESSION['tenant_flash_success'], $_SESSION['tenant_flash_error']);

        $keyword    = trim($_GET['keyword'] ?? '');
        $stayStatus = trim($_GET['stay_status'] ?? '');
        $selectedRoomId = (int)($_GET['room_id'] ?? 0);

        $stats    = $this->model->getTenantStats();
        $tenants  = $this->model->getTenants($keyword, $stayStatus);
        $allRooms = $this->roomModel->getRooms();
        $pendingLeads = $this->model->getContactLeads('pending');
        $contactedLeads = $this->model->getContactLeads('contacted');

        require_once __DIR__ . '/../views/tenants/index.php';
    }
}
