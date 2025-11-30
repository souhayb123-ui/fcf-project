<?php
session_start();
include '../db.php';

// Check admin login
if(!isset($_SESSION['user']) || $_SESSION['role'] != 'admin'){
    header("Location: admin_login.php");
    exit();
}

// Fetch daily profit (last 30 days)
$daily = $conn->query("
    SELECT DATE(created_at) AS day, SUM(total_amount) AS profit
    FROM orders
    WHERE status='delivered'
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at) DESC
    LIMIT 30
");

// Fetch monthly profit
$monthly = $conn->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, SUM(total_amount) AS profit
    FROM orders
    WHERE status='delivered'
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
");

// Fetch yearly profit
$yearly = $conn->query("
    SELECT YEAR(created_at) AS year, SUM(total_amount) AS profit
    FROM orders
    WHERE status='delivered'
    GROUP BY YEAR(created_at)
    ORDER BY year DESC
");

// Prepare data arrays for charts
$daily_labels = [];
$daily_data = [];
while($row = $daily->fetch_assoc()){
    $daily_labels[] = $row['day'];
    $daily_data[] = $row['profit'];
}

$monthly_labels = [];
$monthly_data = [];
while($row = $monthly->fetch_assoc()){
    $monthly_labels[] = $row['month'];
    $monthly_data[] = $row['profit'];
}

$yearly_labels = [];
$yearly_data = [];
while($row = $yearly->fetch_assoc()){
    $yearly_labels[] = $row['year'];
    $yearly_data[] = $row['profit'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Analytics - FCF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin:0; background:#f8f9fa; }
        .sidebar { position: fixed; top:0; left:0; height:100vh; width:260px; background:#343a40; color:white; padding-top:20px; overflow-y:auto; }
        .sidebar a { display:block; color:white; text-decoration:none; padding:14px 25px; font-weight:500; }
        .sidebar a:hover { background:#495057; border-radius:5px; }
        .main-content { margin-left:260px; padding:20px; min-height:100vh; }
        .header { background:white; padding:12px 20px; border-radius:5px; box-shadow:0 2px 4px rgba(0,0,0,0.1); position:sticky; top:0; z-index:10; margin-bottom:20px; }
        .chart-container { background:white; padding:20px; border-radius:10px; margin-bottom:30px; box-shadow:0 1px 6px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="sidebar">
    <h4 class="text-center mb-4">FCF Admin</h4>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_drivers.php">Drivers</a>
    <a href="admin_orders.php">Orders</a>
    <a href="admin_analytics.php">Analytics</a>
    <a href="admin_orders_map.php">Orders Map</a>
    <a href="admin_dashboard.php?logout=1">Logout</a>
</div>

<div class="main-content">
    <div class="header">
        <h5>Welcome, <?php echo $_SESSION['user']; ?></h5>
    </div>

    <h4>Profit Analytics</h4>

    <div class="chart-container">
        <h5>Daily Profit (Last 30 Days)</h5>
        <canvas id="dailyChart"></canvas>
    </div>

    <div class="chart-container">
        <h5>Monthly Profit</h5>
        <canvas id="monthlyChart"></canvas>
    </div>

    <div class="chart-container">
        <h5>Yearly Profit</h5>
        <canvas id="yearlyChart"></canvas>
    </div>
</div>

<script>
    const dailyChart = new Chart(document.getElementById('dailyChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_reverse($daily_labels)); ?>,
            datasets: [{
                label: 'Profit ($)',
                data: <?php echo json_encode(array_reverse($daily_data)); ?>,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                fill: true,
                tension: 0.3
            }]
        },
        options: { responsive:true, scales: { y: { beginAtZero:true } } }
    });

    const monthlyChart = new Chart(document.getElementById('monthlyChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_reverse($monthly_labels)); ?>,
            datasets: [{
                label: 'Profit ($)',
                data: <?php echo json_encode(array_reverse($monthly_data)); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.7)'
            }]
        },
        options: { responsive:true, scales: { y: { beginAtZero:true } } }
    });

    const yearlyChart = new Chart(document.getElementById('yearlyChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_reverse($yearly_labels)); ?>,
            datasets: [{
                label: 'Profit ($)',
                data: <?php echo json_encode(array_reverse($yearly_data)); ?>,
                backgroundColor: 'rgba(255, 159, 64, 0.7)'
            }]
        },
        options: { responsive:true, scales: { y: { beginAtZero:true } } }
    });
</script>
</body>
</html>
