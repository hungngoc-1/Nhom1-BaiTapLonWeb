<?php
// CaNhan_Kimhue/index.php
require_once 'controllers/RoomController.php';

$controller = new RoomController();
$action = $_GET['action'] ?? 'index';

if ($action === 'create') {
    $controller->create();
} elseif ($action === 'edit') {
    $controller->edit();
} elseif ($action === 'delete') {
    $controller->delete();
} else {
    $controller->index();
}
?>