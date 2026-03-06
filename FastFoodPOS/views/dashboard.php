<?php
session_start();
include '../config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch username for greeting
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetchColumn();

// -----------------------------
// Fetch KPIs
// -----------------------------

// Revenue today
$stmt = $pdo->prepare("SELECT SUM(total) FROM orders WHERE DATE(created_at) = CURDATE()");
$stmt->execute();
$revenueToday = $stmt->fetchColumn() ?? 0;

// Revenue this week
$stmt = $pdo->prepare("SELECT SUM(total) FROM orders WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)");
$stmt->execute();
$revenueWeek = $stmt->fetchColumn() ?? 0;

// Units sold today (join order_items with orders to use orders.created_at)
$stmt = $pdo->prepare("
    SELECT p.name, COUNT(*) AS sold
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) = CURDATE()
    GROUP BY p.name
");
$stmt->execute();
$unitsTodayList = $stmt->fetchAll(PDO::FETCH_ASSOC);
$unitsToday = array_sum(array_column($unitsTodayList, 'sold'));

// Pending orders
$stmt = $pdo->prepare("SELECT * FROM orders WHERE status = 'Pending'");
$stmt->execute();
$pendingOrdersList = $stmt->fetchAll(PDO::FETCH_ASSOC);
$pendingOrders = count($pendingOrdersList);

// Top selling products (last 7 days)
$stmt = $pdo->prepare("
    SELECT p.name, COUNT(*) AS sold 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY p.name
    ORDER BY sold DESC
    LIMIT 5
");
$stmt->execute();
$topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Revenue trend (last 7 days)
$stmt = $pdo->prepare("
    SELECT DATE(created_at) AS day, SUM(total) AS revenue 
    FROM orders 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY day
    ORDER BY day ASC
");
$stmt->execute();
$trendData = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">

<!-- Navigation bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-danger">
  <div class="container-fluid">
    <a class="navbar-brand" href="../index.php">🍔 Fast Food POS</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <span class="nav-link">Welcome, <?= htmlspecialchars($currentUser) ?></span>
        </li>
        <!-- Home now points to index.php in the root folder -->
        <li class="nav-item">
          <a class="nav-link" href="../index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="logout.php">Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-5">
  <h2 class="mb-4 text-danger">📊 Fast Food POS Dashboard</h2>

  <!-- KPI Cards -->
  <div class="row mb-4">
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <h5 class="card-title">Revenue Today</h5>
          <p class="card-text fw-bold">R<?= number_format($revenueToday, 2) ?></p>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <h5 class="card-title">Revenue This Week</h5>
          <p class="card-text fw-bold">R<?= number_format($revenueWeek, 2) ?></p>
        </div>
      </div>
    </div>
    <!-- Units Sold Today card with collapsible detail -->
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <h5 class="card-title">
            <a class="text-decoration-none" data-bs-toggle="collapse" href="#unitsTodayDetails">
              Units Sold Today
            </a>
          </h5>
          <p class="card-text fw-bold"><?= $unitsToday ?></p>
        </div>
      </div>
    </div>
    <!-- Pending Orders card with collapsible detail -->
    <div class="col-md-3">
      <div class="card text-center">
        <div class="card-body">
          <h5 class="card-title">
            <a class="text-decoration-none" data-bs-toggle="collapse" href="#pendingOrdersDetails">
              Pending Orders
            </a>
          </h5>
          <p class="card-text fw-bold"><?= $pendingOrders ?></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Collapsible Units Sold Today Detail -->
  <div class="collapse mb-4" id="unitsTodayDetails">
    <div class="card card-body">
      <h5>Units Sold Today (by product)</h5>
      <table class="table table-sm table-striped">
        <thead>
          <tr><th>Product</th><th>Units Sold</th></tr>
        </thead>
        <tbody>
          <?php foreach ($unitsTodayList as $u): ?>
            <tr>
              <td><?= $u['name'] ?></td>
              <td><?= $u['sold'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Collapsible Pending Orders Detail -->
  <div class="collapse mb-4" id="pendingOrdersDetails">
    <div class="card card-body">
      <h5>Pending Orders</h5>
      <table class="table table-sm table-striped">
        <thead>
          <tr><th>ID</th><th>Total (R)</th><th>Date</th></tr>
        </thead>
        <tbody>
          <?php foreach ($pendingOrdersList as $o): ?>
            <tr>
              <td><?= $o['id'] ?></td>
              <td><?= $o['total'] ?></td>
              <td><?= $o['created_at'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Revenue Trend Chart -->
  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title">Revenue (Last 7 Days)</h5>
      <canvas id="revenueChart"></canvas>
    </div>
  </div>

  <!-- Top Products Chart -->
  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title">Top Selling Products (7 Days)</h5>
      <canvas id="topProductsChart"></canvas>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const revenueLabels = <?= json_encode(array_column($trendData, 'day')) ?>;
  const revenueValues = <?= json_encode(array_column($trendData, 'revenue')) ?>;

  new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
      labels: revenueLabels,
      datasets: [{
        label: 'Revenue (R)',
        data: revenueValues,
        borderColor: 'rgba(220,53,69,1)',
        backgroundColor: 'rgba(220,53,69,0.2)',
        fill: true
      }]
    }
  });

  const productLabels = <?= json_encode(array_column($topProducts, 'name')) ?>;
  const productValues = <?= json_encode(array_column($topProducts, 'sold')) ?>;

  new Chart(document.getElementById('topProductsChart'), {
    type: 'bar',
    data: {
      labels: productLabels,
      datasets: [{
        label: 'Units Sold',
        data: productValues,
        backgroundColor: 'rgba(25,135,84,0.7)'
      }]
    }
  });
</script>

</body>
</html>
