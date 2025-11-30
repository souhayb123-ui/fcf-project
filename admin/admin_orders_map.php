<?php
session_start();
include '../db.php'; // adjust path

// Check if admin
if(!isset($_SESSION['user']) || $_SESSION['role'] != 'admin'){
    header("Location: admin_login.php");
    exit();
}

// Fetch all orders with location
$orders = $conn->query("SELECT id, name, phone, total_amount, latitude, longitude FROM orders WHERE latitude IS NOT NULL AND longitude IS NOT NULL");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>All Orders Map - FCF</title>
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<style>
  #map { height: 600px; width: 100%; }
  body { font-family: Arial; }
  h2 { text-align: center; margin-top: 20px; }
</style>
</head>
<body>

<h2>All Orders Map</h2>
<div id="map"></div>

<script>
// Initialize map
var map = L.map('map').setView([33.8938, 35.5018], 10); // Default to Beirut, adjust if needed

// Add OpenStreetMap tiles
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19
}).addTo(map);

// Add markers from PHP
<?php while($order = $orders->fetch_assoc()): ?>
var marker = L.marker([<?php echo $order['latitude']; ?>, <?php echo $order['longitude']; ?>]).addTo(map);
marker.bindPopup("<b>Order #<?php echo $order['id']; ?></b><br>Name: <?php echo addslashes($order['name']); ?><br>Phone: <?php echo $order['phone']; ?><br>Total: $<?php echo $order['total_amount']; ?>");
<?php endwhile; ?>
</script>

</body>
</html>
