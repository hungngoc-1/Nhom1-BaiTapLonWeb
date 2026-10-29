<?php
// CaNhan_Kimhue/controllers/RoomController.php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/RoomModel.php';

class RoomController {
    private $roomModel;

    public function __construct() {
        global $pdo; 
        $this->roomModel = new RoomModel($pdo);
    }

    public function index() {
        $keyword = $_GET['keyword'] ?? '';
        if (!empty($keyword)) {
            $rooms = $this->roomModel->searchRooms($keyword);
        } else {
            $rooms = $this->roomModel->getAllRooms();
        }
        require_once __DIR__ . '/../views/rooms/index.php';
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $room_number = $_POST['room_number'];
            $price = $_POST['price'];
            $status = $_POST['status'];
            $description = $_POST['description'] ?? '';
            
            $this->roomModel->addRoom($room_number, $price, $status, $description);
            header("Location: index.php?route=rooms");
            exit();
        }
        require_once __DIR__ . '/../views/rooms/create.php';
    }

    public function edit() {
        $id = $_GET['id'] ?? 0;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $room_number = $_POST['room_number'];
            $price = $_POST['price'];
            $status = $_POST['status'];
            $description = $_POST['description'] ?? '';
            
            $this->roomModel->updateRoom($id, $room_number, $price, $status, $description);
            header("Location: index.php?route=rooms");
            exit();
        }
        
        $room = $this->roomModel->getRoomById($id);
        require_once __DIR__ . '/../views/rooms/edit.php';
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $this->roomModel->deleteRoom($id);
        }
        header("Location: index.php?route=rooms");
        exit();
    }
}
?>