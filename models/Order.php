<?php
require_once 'Database.php';

class Order {
    private $conn;
    private $table = "orders";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (customer_name, menu_id, quantity, total_price, order_date) 
                  VALUES (:customer_name, :menu_id, :quantity, :total_price, NOW())";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':customer_name', $data['customer_name']);
        $stmt->bindParam(':menu_id', $data['menu_id']);
        $stmt->bindParam(':quantity', $data['quantity']);
        $stmt->bindParam(':total_price', $data['total_price']);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    public function getAllOrders() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY order_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>