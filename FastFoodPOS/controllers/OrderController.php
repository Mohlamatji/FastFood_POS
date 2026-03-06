<?php

require_once __DIR__ . "/../models/Order.php";
require_once __DIR__ . "/../services/ReceiptService.php";


class OrderController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createOrder($items) {
        $total = array_sum(array_column($items, 'price'));
        $stmt = $this->pdo->prepare("INSERT INTO orders (total) VALUES (?)");
        $stmt->execute([$total]);
        $orderId = $this->pdo->lastInsertId();

        foreach ($items as $item) {
            $stmt = $this->pdo->prepare("INSERT INTO order_items (order_id, product_id, price) VALUES (?, ?, ?)");
            $stmt->execute([$orderId, $item['id'], $item['price']]);
        }

        ReceiptService::generateReceipt($orderId, $items, $total);
    }

    public function getAllOrders() {
    $stmt = $this->pdo->query("SELECT * FROM orders ORDER BY id DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Inside OrderController class

// Fetch all items belonging to a specific order
public function getOrderItems($orderId) {
    // Prepare SQL query: join order_items with products to get product names + prices
    $stmt = $this->pdo->prepare("
        SELECT p.name, oi.price 
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    
    // Execute query with the given order ID
    $stmt->execute([$orderId]);
    
    // Return results as an associative array (each row = product name + price)
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


}
?>
