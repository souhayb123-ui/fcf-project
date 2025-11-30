<?php
include '../db.php';

// Fetch all drivers from users table
$drivers = $conn->query("SELECT id, name FROM users WHERE role='driver' ORDER BY name ASC");

// Handle order assignment
if(isset($_POST['assign_driver'])){
    $order_id = intval($_POST['order_id']);
    $driver_id = intval($_POST['driver_id']);

    // Update order: assign driver and set status to accepted
    $conn->query("UPDATE orders SET status='accepted', driver_id=$driver_id WHERE id=$order_id");
    header("Location: admin_orders.php");
    exit();
}

// Handle filter and search
$statusFilter = $_GET['status'] ?? '';
$searchQuery  = $_GET['search'] ?? '';

// Build SQL query dynamically
$sql = "SELECT o.*, u.name AS driver_name 
        FROM orders o 
        LEFT JOIN users u ON o.driver_id=u.id 
        WHERE 1";

if ($statusFilter && in_array($statusFilter, ['pending', 'accepted', 'delivered'])) {
    $sql .= " AND o.status='$statusFilter'";
}

if ($searchQuery) {
    $searchEscaped = $conn->real_escape_string($searchQuery);
    $sql .= " AND (o.name LIKE '%$searchEscaped%' OR o.email LIKE '%$searchEscaped%')";
}

$sql .= " ORDER BY o.id DESC";
$orders = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Orders - FCF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; font-family: Arial, sans-serif; padding: 20px; }
        h2 { text-align: center; margin-bottom: 20px; }
        .table-container { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .btn-space { margin-right: 5px; }
        th, td { text-align: center; vertical-align: middle; }
        .filter-form { display: flex; gap: 10px; margin-bottom: 15px; justify-content: center; flex-wrap: wrap; }
        .filter-form input, .filter-form select { width: auto; }
    </style>
</head>
<body>

<h2>All Orders</h2>

<div class="table-container">
    <!-- Filter & Search Form -->
    <form method="GET" class="filter-form">
        <select name="status" class="form-select">
            <option value="">All Status</option>
            <option value="pending" <?php if($statusFilter=='pending') echo 'selected'; ?>>Pending</option>
            <option value="accepted" <?php if($statusFilter=='accepted') echo 'selected'; ?>>Accepted</option>
            <option value="delivered" <?php if($statusFilter=='delivered') echo 'selected'; ?>>Delivered</option>
        </select>

        <input type="text" name="search" class="form-control" placeholder="Search by name/email" value="<?php echo htmlspecialchars($searchQuery); ?>">

        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="admin_orders.php" class="btn btn-secondary">Reset</a>
    </form>

    <!-- Orders Table -->
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Order ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Total</th>
                <th>Status</th>
                <th>Driver</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if($orders->num_rows > 0): ?>
            <?php while($order = $orders->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $order['id']; ?></td>
                    <td><?php echo htmlspecialchars($order['name']); ?></td>
                    <td><?php echo htmlspecialchars($order['email']); ?></td>
                    <td><?php echo htmlspecialchars($order['phone']); ?></td>
                    <td><?php echo htmlspecialchars($order['address']); ?></td>
                    <td>$<?php echo number_format($order['total_amount'],2); ?></td>
                    <td>
                        <?php
                            if($order['status'] == 'pending') echo '<span class="badge bg-warning text-dark">Pending</span>';
                            elseif($order['status'] == 'accepted') echo '<span class="badge bg-primary">Accepted</span>';
                            elseif($order['status'] == 'delivered') echo '<span class="badge bg-success">Delivered</span>';
                        ?>
                    </td>
                    <td><?php echo $order['driver_name'] ?? 'Not assigned'; ?></td>
                    <td>
                        <a href="view_order_map.php?order_id=<?php echo $order['id']; ?>" target="_blank" class="btn btn-info btn-sm btn-space">View on Map</a>

                        <?php if($order['status'] == 'pending'): ?>
                        <!-- Assign Driver Form -->
                        <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <select name="driver_id" class="form-select form-select-sm mb-1" required>
                                <option value="">Select Driver</option>
                                <?php while($driver = $drivers->fetch_assoc()): ?>
                                    <option value="<?php echo $driver['id']; ?>"><?php echo htmlspecialchars($driver['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                            <button type="submit" name="assign_driver" class="btn btn-success btn-sm w-100">Accept</button>
                        </form>
                        <?php else: ?>
                            <span class="text-muted">No action</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="9">No orders found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
