<?php
session_start();
include 'db.php';

$user_name = '';
if(isset($_SESSION['user'])){
    $user_id = $_SESSION['user'];
    $res = $conn->query("SELECT name FROM users WHERE id='$user_id'");
    if($res->num_rows > 0){
        $user = $res->fetch_assoc();
        $user_name = $user['name'];
    }
}

// Fetch all categories
$categories = $conn->query("SELECT * FROM categories");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>FCF - Fresh Chilled Frozen</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

<style>
/* Body and flex layout */
body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    background-color:#f8f8f8;
    margin: 0;
}
.container {
    flex: 1;
}

/* Header */
.header-container {
    position: relative;
    text-align: center;
    padding: 20px 0;
    background: white;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
.header-buttons {
    position: absolute;
    top: 20px;
    right: 20px;
}

/* Category cards */
.category-card {
    text-decoration: none;
    color: black;
    position: relative;
    display: flex;
    flex-direction: column;
    height: 100%;
}
.category-card .card {
    transition: transform 0.3s, box-shadow 0.3s;
    cursor: pointer;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.category-card .card:hover {
    transform: scale(1.05);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
}
.category-card img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 10px 10px 0 0;
    flex-shrink: 0;
}
.card-body {
    text-align: center;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.category-card h4 { margin: 0; font-size: 18px; }
.category-card p { margin: 5px 0 0 0; font-size: 14px; color: #555; }
.overlay {
    position: absolute;
    top:0; left:0;
    width:100%; height:100%;
    background-color: rgba(0,0,0,0.5);
    color: white;
    font-size:20px;
    font-weight:bold;
    display:flex;
    justify-content:center;
    align-items:center;
    border-radius:10px;
    pointer-events:none;
}
.category-row { gap: 15px; }

/* Floating Cart */
.floating-cart {
    position: fixed;
    bottom: 20px;
    left: 20px;
    background: #ffc107;
    color: black;
    border-radius: 50%;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    text-decoration: none;
}

/* Footer */
footer {
    background-color: #222;
    color: #fff;
    padding: 20px 0;
    text-align: center;
}
.footer-icons {
    margin-top: 10px;
}
.footer-icons a {
    color: #fff;
    margin: 0 10px;
    font-size: 24px;
    transition: color 0.3s, transform 0.3s;
}
.footer-icons a:hover {
    color: #ffc107;
    transform: scale(1.2);
}

/* Chatbot window */
#chatbot-win { display:none; position:fixed; bottom:180px; right:20px; width:300px; height:450px; background:white; border-radius:10px; box-shadow:0 5px 15px rgba(0,0,0,0.3); flex-direction:column; overflow:hidden; z-index:1000; }
#chatbot-msgs { flex:1; padding:10px; overflow-y:auto; }
#chatbot-input { width:100%; padding:10px; border:none; border-top:1px solid #ccc; }
.chat-msg-user { text-align:right; color:blue; margin-bottom:5px; }
.chat-msg-bot { text-align:left; color:green; margin-bottom:5px; }

/* Map */
#chatbot-map { display:none; height:250px; width:90%; margin:10px auto; border-radius:10px; }
#chatbot-map-controls { display:none; text-align:center; margin-top:5px; }

</style>
</head>
<body>

<!-- HEADER -->
<div class="header-container">
    <img src="images/fcf_logo.png" alt="FCF Logo" style="width:120px;">
    <h2 class="mt-2">FRESH • CHILLED • FROZEN</h2>
    <div class="header-buttons">
        <?php if($user_name): ?>
            <span>Hello, <?php echo $user_name; ?>!</span>
            <a href="logout.php" class="btn btn-secondary ms-2">Logout</a>
        <?php else: ?>
            <a href="login.php" class="btn btn-primary">Login</a>
            <a href="signup.php" class="btn btn-success ms-2">Sign Up</a>
        <?php endif; ?>
    </div>
</div>

<!-- CONTENT -->
<div class="container mt-5">
    <h2 class="text-center mb-4">Our Categories</h2>
    <div class="row category-row justify-content-center">
        <?php
        if($categories->num_rows > 0){
            while($cat = $categories->fetch_assoc()){
                $cat_id = $cat['id'];
                $products = $conn->query("SELECT * FROM products WHERE category_id=$cat_id");
                $all_out = true;
                while($p = $products->fetch_assoc()){
                    if($p['stock_status'] == 'available'){ $all_out=false; break; }
                }
                echo '<div class="col-6 col-md-4 col-lg-3 mb-3 d-flex">
                        <a href="category.php?id='.$cat_id.'" class="category-card w-100">
                            <div class="card shadow-sm">
                                <img src="admin/images/'.$cat["image"].'" class="card-img-top" alt="'.$cat["name"].'">
                                <div class="card-body">
                                    <h4>'.$cat["name"].'</h4>
                                    <p>'.$cat["description"].'</p>
                                </div>';
                if($all_out) echo '<div class="overlay">Out of Stock</div>';
                echo '</div></a></div>';
            }
        } else { echo "<p class='text-center'>No categories found.</p>"; }
        ?>
    </div>
</div>

<!-- Floating Cart -->
<a href="cart.php" class="floating-cart">🛒 <?php echo array_sum($_SESSION['cart'] ?? []); ?></a>

<!-- FOOTER -->
<footer>
    <div>Since 2021</div>
    <div>Contact: +961 86716119</div>
    <div class="footer-icons">
        <a href="https://facebook.com" target="_blank"><i class="bi bi-facebook"></i></a>
        <a href="https://instagram.com" target="_blank"><i class="bi bi-instagram"></i></a>
    </div>
    <div>© 2025 FCF - Fresh Chilled Frozen</div>
</footer>

<!-- Chatbot -->
<div id="chatbot-btn" style="position:fixed; bottom:100px; right:20px; background:#ffc107; color:black; border-radius:50%; width:60px; height:60px; display:flex; justify-content:center; align-items:center; cursor:pointer; z-index:1000; font-size:24px;">💬</div>
<div id="chatbot-win">
    <div style="background:#222;color:white;padding:10px;text-align:center;font-weight:bold;">FCF Chatbot</div>
    <div id="chatbot-msgs"></div>
    <input id="chatbot-input" type="text" placeholder="Type your message...">
    <div id="chatbot-map"></div>
    <div id="chatbot-map-controls">
        <button id="hide-map-btn" class="btn btn-sm btn-warning">Hide Map</button>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
const btn = document.getElementById('chatbot-btn');
const win = document.getElementById('chatbot-win');
const msgs = document.getElementById('chatbot-msgs');
const input = document.getElementById('chatbot-input');
const hideMapBtn = document.getElementById('hide-map-btn');
const mapControls = document.getElementById('chatbot-map-controls');

let mapInitialized = false;
let mapInstance;

btn.onclick = () => { win.style.display = win.style.display==='flex'?'none':'flex'; };

input.addEventListener('keypress', function(e){
    if(e.key==='Enter' && input.value.trim()!==''){
        const userMsg = input.value;
        msgs.innerHTML += `<div class="chat-msg-user">You: ${userMsg}</div>`;
        input.value=''; msgs.scrollTop = msgs.scrollHeight;

        $.post('chatbot.php',{message:userMsg}, function(data){
            msgs.innerHTML += `<div class="chat-msg-bot">Bot: ${data.reply}</div>`;
            msgs.scrollTop = msgs.scrollHeight;

            if(data.showMap){
                const mapDiv = document.getElementById('chatbot-map');
                mapDiv.style.display='block';
                mapControls.style.display='block';

                if(!mapInitialized){
                    mapInitialized=true;
                    mapInstance=L.map('chatbot-map').setView([33.8886,35.4955],15);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution:'&copy; <a href="https://openstreetmap.org">OpenStreetMap</a> contributors'
                    }).addTo(mapInstance);
                    L.marker([33.8886,35.4955]).addTo(mapInstance)
                        .bindPopup("<b>FCF Store</b><br>Fresh Chilled Frozen").openPopup();
                    setTimeout(()=>{mapInstance.invalidateSize();},100);
                }
            }
        }, 'json');
    }
});

hideMapBtn.onclick = () => {
    document.getElementById('chatbot-map').style.display='none';
    mapControls.style.display='none';
};
</script>
</body>
</html>
