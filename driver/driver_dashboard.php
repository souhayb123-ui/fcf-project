<?php
session_start();
include '../db.php';

if(!isset($_SESSION['driver'])){
    header("Location: ../login.php");
    exit();
}

$driver_id = $_SESSION['driver'];

// Fetch driver info (status only)
$driver_res = $conn->query("SELECT name, status FROM users WHERE id=$driver_id");
$driver = $driver_res->fetch_assoc();

// Handle status toggle
if(isset($_POST['toggle_status'])){
    $new_status = ($driver['status'] == 'Active') ? 'Inactive' : 'Active';
    $conn->query("UPDATE users SET status='$new_status' WHERE id=$driver_id");
    header("Location: driver_dashboard.php");
    exit();
}

// Filter by date
$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date'] ?? '';

$sql = "SELECT *, DATE_FORMAT(created_at, '%d/%m/%Y') as order_date FROM orders WHERE driver_id=$driver_id";
if($start_date) $sql .= " AND DATE(created_at) >= '$start_date'";
if($end_date) $sql .= " AND DATE(created_at) <= '$end_date'";
$sql .= " ORDER BY created_at DESC";

$orders = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <title>Driver Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <style>
        body { background-color: #f4f6f9; }
        table { background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        th, td { vertical-align: middle !important; text-align: center; }
        #map { height: 400px; width: 100%; margin-top: 20px; display:none; border-radius: 10px; }
        .status-badge { font-weight:bold; padding:5px 10px; border-radius:5px; }
        .active { background-color:#28a745; color:white; }
        .inactive { background-color:#dc3545; color:white; }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h2><i class="bi bi-truck me-2" style="color:#0d6efd;"></i>Welcome, <?php echo htmlspecialchars($driver['name']); ?></h2>
        <form method="POST" class="d-flex align-items-center gap-3">
            <span class="status-badge <?php echo strtolower($driver['status']); ?>">
                <?php echo $driver['status']; ?>
            </span>
            <button type="submit" name="toggle_status" class="btn btn-outline-primary btn-sm">Toggle Status</button>
            <a href="../logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </form>
    </div>

    <form method="GET" class="row g-3 mb-3">
        <div class="col-auto">
            <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
        </div>
        <div class="col-auto">
            <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="driver_dashboard.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Order ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Location</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while($order = $orders->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $order['id']; ?></td>
                    <td><?php echo htmlspecialchars($order['name']); ?></td>
                    <td><?php echo htmlspecialchars($order['phone']); ?></td>
                    <td><?php echo htmlspecialchars($order['address']); ?></td>
                    <td>$<?php echo number_format($order['total_amount'],2); ?></td>
                    <td>
                        <?php if($order['status']=='pending'): ?>
                            <span class="badge bg-warning text-dark">Pending</span>
                        <?php elseif($order['status']=='accepted'): ?>
                            <span class="badge bg-primary">Accepted</span>
                        <?php else: ?>
                            <span class="badge bg-success">Delivered</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $order['order_date']; ?></td>
                    <td>
                        <?php if($order['latitude'] && $order['longitude']): ?>
                            <button class="btn btn-info btn-sm" onclick="showMap(<?php echo $order['latitude']; ?>, <?php echo $order['longitude']; ?>)">View Map</button>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($order['status'] != 'delivered'): ?>
                            <a href="mark_delivered.php?id=<?php echo $order['id']; ?>" class="btn btn-warning btn-sm">Mark Delivered</a>
                        <?php else: ?>
                            <span class="badge bg-success">Delivered</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div id="map"></div>
</div>

<script>
function showMap(lat, lng){
    var mapDiv = document.getElementById('map');
    mapDiv.style.display = 'block';
    mapDiv.innerHTML = "";

    var map = L.map('map').setView([lat, lng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    L.marker([lat, lng]).addTo(map)
        .bindPopup("Delivery Location")
        .openPopup();

    mapDiv.scrollIntoView({behavior: "smooth"});
}
</script>
</body>
</html>
