<?php


// Include config and controller
include '../config.php';
include '../controllers/OrderController.php';

// Create controller instance
$orderController = new OrderController($pdo);

// Handle new order form submission
if (isset($_POST['place_order'])) {
    $productId = $_POST['product_id'];
    $quantity  = (int)$_POST['quantity'];

    // Fetch product info
    $stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // Calculate total
        $total = $product['price'] * $quantity;

        // Insert into orders
        $stmt = $pdo->prepare("INSERT INTO orders (total, created_at) VALUES (?, NOW())");
        $stmt->execute([$total]);
        $orderId = $pdo->lastInsertId();

        // Insert items into order_items
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, price) VALUES (?, ?, ?)");
        for ($i = 0; $i < $quantity; $i++) {
            $stmt->execute([$orderId, $product['id'], $product['price']]);
        }
    }
}

// Handle delete order request
if (isset($_POST['delete_order'])) {
    $orderId = $_POST['order_id'];
    // Delete items first (foreign key constraint)
    $pdo->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$orderId]);
    // Delete order
    $pdo->prepare("DELETE FROM orders WHERE id = ?")->execute([$orderId]);
}

// Fetch all orders from the database
$orders = $orderController->getAllOrders();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Orders</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <h2 class="mb-4 text-danger">🧾 Orders</h2>

  <!-- Orders table -->
  <table class="table table-striped table-hover">
    <thead class="table-dark">
      <tr>
        <th>Order ID</th>
        <th>Total (R)</th>
        <th>Items</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td><?= $o['id'] ?></td>
          <td><?= $o['total'] ?></td>
          <td>
            <ul>
              <?php 
                $items = $orderController->getOrderItems($o['id']); 
                foreach ($items as $item): 
              ?>
                <li><?= $item['name'] ?> (R<?= $item['price'] ?>)</li>
              <?php endforeach; ?>
            </ul>
          </td>
          <td>
            <!-- Delete button -->
            <form method="POST" action="orders.php" style="display:inline;">
              <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
              <button type="submit" name="delete_order" class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Place Order Form -->
<div class="container mt-5">
  <h3 class="mb-4">Place a New Order</h3>
  <form method="POST" action="orders.php">
    <div class="mb-3">
      <label for="product" class="form-label">Select Product</label>
      <select name="product_id" id="product" class="form-select">
        <?php
          $products = $pdo->query("SELECT id, name, price FROM products")->fetchAll(PDO::FETCH_ASSOC);
          foreach ($products as $p) {
            echo "<option value='{$p['id']}'>{$p['name']} (R{$p['price']})</option>";
          }
        ?>
      </select>
    </div>
    <div class="mb-3">
      <label for="quantity" class="form-label">Quantity</label>
      <input type="number" name="quantity" id="quantity" class="form-control" value="1" min="1">
    </div>
    <button type="submit" name="place_order" class="btn btn-danger">Place Order</button>
  </form>
</div>

</body>
</html>
