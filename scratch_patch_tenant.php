<?php
$filePath = __DIR__ . '/app/Models/TenantModel.php';
$content = file_get_contents($filePath);
$pos1 = strpos($content, 'public function assignRoomToTenant');
$pos2 = strpos($content, 'public function deleteTenant');

if ($pos1 !== false && $pos2 !== false) {
    $newFunc = "public function assignRoomToTenant(int \$tenantId, int \$roomId, ?string \$roommateName = null, ?string \$roommatePhone = null): void {\r\n"
             . "        // Cập nhật thông tin phòng ưu tiên và bạn cùng phòng trong users\r\n"
             . "        \$stmt = \$this->conn->prepare(\"\r\n"
             . "            UPDATE users SET \r\n"
             . "                preferred_room_id = :room_id,\r\n"
             . "                roommate_name = COALESCE(:roommate_name, roommate_name),\r\n"
             . "                roommate_phone = COALESCE(:roommate_phone, roommate_phone)\r\n"
             . "            WHERE id = :tenant_id\r\n"
             . "        \");\r\n"
             . "        \$stmt->execute([\r\n"
             . "            ':room_id'        => \$roomId,\r\n"
             . "            ':roommate_name'  => \$roommateName,\r\n"
             . "            ':roommate_phone' => \$roommatePhone,\r\n"
             . "            ':tenant_id'      => \$tenantId,\r\n"
             . "        ]);\r\n"
             . "        // Nghiệp vụ: Khi tạo khách kèm phòng thì KHÔNG ĐƯỢC tự động lập hợp đồng!\r\n"
             . "        // Người quản lý sẽ sang trang Hợp đồng để tự lập hợp đồng và chọn khách chưa có HĐ.\r\n"
             . "    }\r\n\r\n    ";

    $content = substr($content, 0, $pos1) . $newFunc . substr($content, $pos2);
    file_put_contents($filePath, $content);
    echo "UPDATED_TENANT_MODEL_OK\n";
} else {
    echo "NOT_FOUND\n";
}
