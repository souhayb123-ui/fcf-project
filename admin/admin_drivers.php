<?php
session_start();
include '../db.php';

// Logout
if(isset($_GET['logout'])){
    session_destroy();
    header("Location: admin_login.php");
    exit();
}

// Check admin login
if(!isset($_SESSION['user']) || $_SESSION['role'] != 'admin'){
    header("Location: admin_login.php");
    exit();
}

// Fetch all drivers from users table
$drivers = $conn->query("SELECT * FROM users WHERE role='driver' ORDER BY name ASC");

// Function to get assigned orders count
function assignedOrders($conn, $driver_id){
    $res = $conn->query("SELECT COUNT(*) as total FROM orders WHERE driver_id=$driver_id");
    $row = $res->fetch_assoc();
    return $row['total'];
}

// Function to get delivered orders count
function deliveredOrders($conn, $driver_id){
    $res = $conn->query("SELECT COUNT(*) as total FROM orders WHERE driver_id=$driver_id AND status='Delivered'");
    $row = $res->fetch_assoc();
    return $row['total'];
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Drivers - FCF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { margin:0; font-family:Arial, sans-serif; background:#f8f9fa; }
        .sidebar { position: fixed; top:0; left:0; height:100vh; width:260px; background:#343a40; color:white; padding-top:20px; overflow-y:auto; }
        .sidebar a { display:block; color:white; text-decoration:none; padding:14px 25px; font-weight:500; }
        .sidebar a:hover { background:#495057; border-radius:5px; }
        .main-content { margin-left:260px; padding:20px; min-height:100vh; }
        .header { background:white; padding:12px 20px; border-radius:5px; box-shadow:0 2px 4px rgba(0,0,0,0.1); position:sticky; top:0; z-index:10; margin-bottom:20px; }
        table { width:100%; background:white; border-radius:10px; overflow-x:auto; }
        thead th { position:sticky; top:0; background:#343a40; color:white; }
    </style>
</head>
<body>

<div class="sidebar">
    <h4 class="text-center mb-4">FCF Admin</h4>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_drivers.php">Drivers</a>
    <a href="admin_orders.php">Orders</a>
    <a href="admin_orders_map.php">Orders Map</a>
    <a href="admin_dashboard.php?logout=1">Logout</a>
</div>

<div class="main-content">
    <div class="header">
        <h5>Welcome, <?php echo $_SESSION['user']; ?></h5>
    </div>

    <h4>Drivers Status</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>Driver Name</th>
                    <th>Status</th>
                    <th>Assigned Orders</th>
                    <th>Delivered Orders</th>
                </tr>
            </thead>
            <tbody>
    <?php while($d = $drivers->fetch_assoc()){ ?>
    <tr>
        <td><?php echo $d['name']; ?></td>
        <td style="color: <?php echo ($d['status']=='Active') ? 'green' : 'red'; ?>; font-weight:bold;">
            <?php echo $d['status']; ?>
        </td>
        <td><?php echo assignedOrders($conn, $d['id']); ?></td>
        <td><?php echo deliveredOrders($conn, $d['id']); ?></td>
    </tr>
    <?php } ?>
</tbody>

        </table>
    </div>

</div>
</body>
</html>
