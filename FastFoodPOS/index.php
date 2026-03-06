<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Fast Food POS - Home</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Navigation bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-danger">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">🍔 Fast Food POS</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <?php if (isset($_SESSION['user_id'])): ?>
          <li class="nav-item">
            <a class="nav-link" href="views/dashboard.php">Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="views/logout.php">Logout</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="views/login.php">Login</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container text-center mt-5">
  <h2 class="mb-4 text-danger">Welcome to Fast Food POS</h2>
  <p class="lead mb-5">Manage products, track orders, and keep your fast food business running smoothly.</p>

  <div class="row">
    <!-- Products -->
    <div class="col-md-4 text-center">
      <i class="bi bi-bag-fill" style="font-size: 3rem;"></i>
      <h4>Products</h4>
      <a href="views/products.php" class="btn btn-danger mt-2">Manage Products</a>
    </div>

    <!-- Orders -->
    <div class="col-md-4 text-center">
      <i class="bi bi-receipt-cutoff" style="font-size: 3rem;"></i>
      <h4>Orders</h4>
      <a href="views/orders.php" class="btn btn-danger mt-2">View Orders</a>
    </div>

    <!-- Dashboard -->
    <div class="col-md-4 text-center">
      <i class="bi bi-speedometer2" style="font-size: 3rem;"></i>
      <h4>Dashboard</h4>
      <a href="views/dashboard.php" class="btn btn-danger mt-2">Go to Dashboard</a>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
