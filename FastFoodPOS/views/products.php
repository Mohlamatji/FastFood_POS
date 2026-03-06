<?php
// Start session for login checks
session_start();

// Include config
include '../config.php';

// -----------------------------
// Check login status
// -----------------------------
$isLoggedIn = isset($_SESSION['user_id']); 
// You’ll set $_SESSION['user_id'] when the user logs in via login.php

// -----------------------------
// Handle Add Product
// -----------------------------
if ($isLoggedIn && isset($_POST['add_product'])) {
    $name  = $_POST['name'];
    $price = (float)$_POST['price'];

    // Insert new product into DB
    $stmt = $pdo->prepare("INSERT INTO products (name, price) VALUES (?, ?)");
    $stmt->execute([$name, $price]);
}

// -----------------------------
// Handle Delete Product
// -----------------------------
if ($isLoggedIn && isset($_POST['delete_product'])) {
    $productId = $_POST['product_id'];

    // First delete any order_items referencing this product (foreign key constraint)
    $pdo->prepare("DELETE FROM order_items WHERE product_id = ?")->execute([$productId]);

    // Then delete the product itself
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$productId]);
}

// -----------------------------
// Handle Edit Product
// -----------------------------
if ($isLoggedIn && isset($_POST['edit_product'])) {
    $productId = $_POST['product_id'];
    $name      = $_POST['name'];
    $price     = (float)$_POST['price'];

    // Update product details
    $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ? WHERE id = ?");
    $stmt->execute([$name, $price, $productId]);
}

// -----------------------------
// Fetch All Products
// -----------------------------
$products = $pdo->query("SELECT * FROM products ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Products</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <h2 class="mb-4 text-danger">🍔 Products</h2>

  <!-- Products table -->
  <table class="table table-striped table-hover">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Price (R)</th>
        <?php if ($isLoggedIn): ?>
          <th>Actions</th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($products as $p): ?>
        <tr>
          <td><?= $p['id'] ?></td>
          <td><?= $p['name'] ?></td>
          <td><?= $p['price'] ?></td>
          <?php if ($isLoggedIn): ?>
          <td>
              <!-- Delete button -->
              <form method="POST" action="products.php" style="display:inline;">
                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                <button type="submit" name="delete_product" class="btn btn-sm btn-outline-danger"
                        onclick="return confirm('Are you sure you want to delete this product?');">
                  Delete
                </button>
              </form>

              <!-- Edit form (inline) -->
              <form method="POST" action="products.php" style="display:inline;">
                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                <input type="text" name="name" value="<?= $p['name'] ?>" 
                       class="form-control form-control-sm d-inline-block" style="width:150px;">
                <input type="number" step="0.01" name="price" value="<?= $p['price'] ?>" 
                       class="form-control form-control-sm d-inline-block" style="width:100px;">
                <button type="submit" name="edit_product" class="btn btn-sm btn-outline-primary">Update</button>
              </form>
          </td>
          <?php endif; ?>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<!-- Add Product Form -->
<?php if ($isLoggedIn): ?>
<div class="container mt-5">
  <h3 class="mb-4">Add a New Product</h3>
  <form method="POST" action="products.php">
    <div class="mb-3">
      <label for="name" class="form-label">Product Name</label>
      <input type="text" name="name" id="name" class="form-control" required>
    </div>
    <div class="mb-3">
      <label for="price" class="form-label">Price (R)</label>
      <input type="number" step="0.01" name="price" id="price" class="form-control" required>
    </div>
    <button type="submit" name="add_product" class="btn btn-danger">Add Product</button>
  </form>
</div>
<?php endif; ?>

</body>
</html>
