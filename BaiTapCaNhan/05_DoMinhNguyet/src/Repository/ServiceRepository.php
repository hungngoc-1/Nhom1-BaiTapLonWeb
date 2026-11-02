<?php
class ServiceRepository {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAll() {
        $stmt = $this->pdo->query("SELECT * FROM services ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function create($name, $price, $unit) {
        $stmt = $this->pdo->prepare("INSERT INTO services (service_name, unit_price, unit) VALUES (?, ?, ?)");
        return $stmt->execute([$name, $price, $unit]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM services WHERE id = ?");
        return $stmt->execute([$id]);
    }
    public function update($id, $name, $price, $unit) {
        $stmt = $this->pdo->prepare("UPDATE services SET service_name = ?, unit_price = ?, unit = ? WHERE id = ?");
        return $stmt->execute([$name, $price, $unit, $id]);
    }
}
?>