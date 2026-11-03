<?php
require_once __DIR__ . '/../app/Config/Database.php';
$db = Database::getConnection();
$st = $db->query("DESCRIBE contracts");
foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $col) {
    echo $col['Field'] . " - " . $col['Type'] . "\n";
}
echo "\n--- SAMPLE CONTRACTS ---\n";
$cSt = $db->query("SELECT id, room_id, tenant_id, deposit_amount, deposit_paid_percent, deposit_status FROM contracts LIMIT 5");
print_r($cSt->fetchAll(PDO::FETCH_ASSOC));
