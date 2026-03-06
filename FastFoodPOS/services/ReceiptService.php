<?php
class ReceiptService {
    public static function generateReceipt($orderId, $items, $total) {
        echo "<h2>Receipt #$orderId</h2>";
        foreach ($items as $item) {
            echo $item['name'] . " - $" . $item['price'] . "<br>";
        }
        echo "<strong>Total: $" . $total . "</strong>";
    }
}
?>
