<?php
include '../db.php';

if(!isset($_GET['order_id'])){
    die("No order ID provided.");
}

$order_id = (int)$_GET['order_id'];
$res = $conn->query("SELECT * FROM orders WHERE id=$order_id");
if($res->num_rows == 0){
    die("Order not found.");
}

$order = $res->fetch_assoc();
$lat = $order['latitude'];
$lng = $order['longitude'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Order #<?php echo $order_id; ?> Location</title>
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<style>
  #map { height: 500px; }
</style>
</head>
<body>

<h3>Order #<?php echo $order_id; ?> Location</h3>
<div id="map"></div>

<script>
    var map = L.map('map').setView([<?php echo $lat; ?>, <?php echo $lng; ?>], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    L.marker([<?php echo $lat; ?>, <?php echo $lng; ?>]).addTo(map)
        .bindPopup("Delivery Location for Order #<?php echo $order_id; ?>")
        .openPopup();
</script>

</body>
</html>
