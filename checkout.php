<?php
session_start();
include 'db.php';

// Check if user is logged in
if(!isset($_SESSION['user'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user'];
$message = '';

// Get logged in user info
$res = $conn->query("SELECT name, email FROM users WHERE id=$user_id");
$user = $res->fetch_assoc();

// Get cart items
$cart = $_SESSION['cart'] ?? [];
if(empty($cart)){
    header("Location: index.php");
    exit();
}

// Handle form submission
if(isset($_POST['checkout'])){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $latitude = trim($_POST['latitude']);
    $longitude = trim($_POST['longitude']);

    // Calculate total
    $total = 0;
    foreach($cart as $prod_id => $qty){
        $prod_res = $conn->query("SELECT price FROM products WHERE id=$prod_id");
        $prod = $prod_res->fetch_assoc();
        $total += $prod['price'] * $qty;
    }

    // Insert into orders table
    $stmt = $conn->prepare("INSERT INTO orders (user_id, name, email, phone, address, latitude, longitude, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssdd", $user_id, $name, $email, $phone, $address, $latitude, $longitude, $total);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    // Insert each product into order_items
    foreach($cart as $prod_id => $qty){
        $prod_res = $conn->query("SELECT price FROM products WHERE id=$prod_id");
        $prod = $prod_res->fetch_assoc();
        $price = $prod['price'];
        $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt2->bind_param("iiid", $order_id, $prod_id, $qty, $price);
        $stmt2->execute();
    }

    // Clear cart
    $_SESSION['cart'] = [];

    $message = "Order placed successfully! Your Order ID is #$order_id";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Checkout - FCF</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<style>
body { background-color:#f8f8f8; }
.checkout-container {
    max-width: 600px;
    margin: 50px auto;
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow:0 5px 15px rgba(0,0,0,0.2);
}
#map { height: 300px; margin-bottom: 15px; }
</style>
</head>
<body>

<div class="checkout-container">
    <div class="text-center mb-4">
        <img src="images/fcf_logo.png" alt="FCF Logo" style="width:100px;">
        <h4 class="mt-2">FRESH • CHILLED • FROZEN</h4>
    </div>

    <?php if($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Delivery Address</label>
            <textarea name="address" class="form-control" required></textarea>
        </div>

        <!-- Leaflet Map -->
        <div id="map"></div>
        <button type="button" id="locateBtn" class="btn btn-info mb-3">Use My Current Location</button>

        <!-- Hidden inputs for coordinates -->
        <input type="hidden" name="latitude" id="latitude">
        <input type="hidden" name="longitude" id="longitude">

        <h5>Total Amount: $<?php
            $total_display = 0;
            foreach($cart as $prod_id => $qty){
                $prod_res = $conn->query("SELECT price FROM products WHERE id=$prod_id");
                $prod = $prod_res->fetch_assoc();
                $total_display += $prod['price'] * $qty;
            }
            echo number_format($total_display,2);
        ?></h5>

        <button type="submit" name="checkout" class="btn btn-success w-100 mt-3">Place Order</button>
    </form>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
var map = L.map('map').setView([33.8886, 35.4955], 12); // Default Beirut
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

var marker = L.marker([33.8886, 35.4955], {draggable:true}).addTo(map);

function updateLatLng(lat,lng){
    document.getElementById('latitude').value = lat;
    document.getElementById('longitude').value = lng;
}
updateLatLng(marker.getLatLng().lat, marker.getLatLng().lng);

// Marker drag
marker.on('dragend', function(e){
    var pos = e.target.getLatLng();
    updateLatLng(pos.lat, pos.lng);
});

// Map click
map.on('click', function(e){
    marker.setLatLng(e.latlng);
    updateLatLng(e.latlng.lat, e.latlng.lng);
});

// Geolocation button
document.getElementById('locateBtn').addEventListener('click', function(){
    if(navigator.geolocation){
        navigator.geolocation.getCurrentPosition(function(position){
            var lat = position.coords.latitude;
            var lng = position.coords.longitude;
            map.setView([lat, lng], 15);
            marker.setLatLng([lat, lng]);
            updateLatLng(lat, lng);
        }, function(err){
            alert('Could not get your location. Make sure location services are enabled.');
        });
    } else {
        alert('Geolocation is not supported by your browser.');
    }
});
</script>

</body>
</html>
