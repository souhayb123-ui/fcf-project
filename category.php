<?php
session_start();
include 'db.php';
// Initialize cart if not exists
if(!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// Handle add to cart via PHP (fallback)
if(isset($_POST['add_to_cart'])){
    $prod_id = $_POST['product_id'];
    $qty = (int)$_POST['quantity'];
    if(isset($_SESSION['cart'][$prod_id])){
        $_SESSION['cart'][$prod_id] += $qty;
    } else {
        $_SESSION['cart'][$prod_id] = $qty;
    }
}

// Get category ID from URL
$category_id = $_GET['id'];

// Get category info
$cat_sql = "SELECT * FROM categories WHERE id = $category_id";
$cat_result = $conn->query($cat_sql);
$category = $cat_result->fetch_assoc();

// Get products for this category
$prod_sql = "SELECT * FROM products WHERE category_id = $category_id";
$prod_result = $conn->query($prod_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <meta charset="UTF-8">
    <title><?php echo $category['name']; ?> - FCF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color:#f8f8f8; }

        /* Product card styling */
        .product-card {
            width: 100%;
            max-width: 250px;
            margin: 0 auto;
            position: relative;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .product-card:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .product-card img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 10px;
        }
        .out-of-stock {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 220px;
            background: rgba(0,0,0,0.6);
            color: white;
            font-weight: bold;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            text-transform: uppercase;
            pointer-events: none;
        }
        .product-card h4 { margin-top: 10px; }
        .product-card p { margin-bottom: 0; }
        .back-home { text-decoration: none; }
        .quantity-input { width: 70px; display:inline-block; }
        .message { text-align:center; color:green; font-weight:bold; margin-bottom:10px; }

        /* Floating cart button */
        .floating-cart {
            position: fixed;
            bottom: 20px;
            left: 20px;
            width: 60px;
            height: 60px;
            background-color: #ffc107;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: black;
            font-size: 24px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            z-index: 1000;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .floating-cart:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 20px rgba(0,0,0,0.4);
        }
        .floating-cart .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: red;
            color: white;
            font-size: 14px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>

<!-- HEADER -->
<div class="text-center p-4 bg-white shadow-sm">
    <div>
        <img src="images/fcf_logo.png" alt="FCF Logo" style="width:120px;">
        <h2 class="mt-2">FRESH • CHILLED • FROZEN</h2>
    </div>
    <div>
        <a href="index.php" class="btn btn-secondary me-2">Home</a>
    </div>
</div>

<div class="container mt-5">
    <h2 class="text-center mb-4"><?php echo $category['name']; ?> Products</h2>
    <?php if(isset($message)) echo "<div class='message'>{$message}</div>"; ?>

    <div class="row">
        <?php
        if ($prod_result->num_rows > 0) {
            while($row = $prod_result->fetch_assoc()) {
                $badge = ($row['stock_status'] == 'available') 
                    ? "<span class='badge bg-success'>Available</span>" 
                    : "<span class='badge bg-danger'>Out of Stock</span>";
                ?>
                <div class="col-md-4 mb-4 d-flex justify-content-center">
                    <div class="card mb-4 shadow-sm product-card">
                        <img src="images/<?php echo $row["image"]; ?>" class="card-img-top" alt="<?php echo $row["name"]; ?>" data-bs-toggle="modal" data-bs-target="#productModal<?php echo $row["id"]; ?>">
                        <?php if($row['stock_status'] == 'out'): ?>
                            <div class="out-of-stock">Out of Stock</div>
                        <?php endif; ?>
                        <div class="card-body text-center">
                            <h4><?php echo $row["name"]; ?></h4>
                            <?php echo $badge; ?>
                            <p class="mt-2">Price: <strong>$<?php echo $row["price"]; ?></strong></p>
                        </div>
                    </div>
                </div>

                <!-- Product Modal -->
                <div class="modal fade" id="productModal<?php echo $row["id"]; ?>" tabindex="-1" aria-labelledby="modalLabel<?php echo $row["id"]; ?>" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="modalLabel<?php echo $row["id"]; ?>"><?php echo $row["name"]; ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body text-center">
                        <img src="images/<?php echo $row["image"]; ?>" class="img-fluid mb-3" alt="<?php echo $row["name"]; ?>">
                        <p><?php echo $row["description"] ?? "No description"; ?></p>
                        <h5>Unit Price: $<span id="price<?php echo $row["id"]; ?>"><?php echo $row["price"]; ?></span></h5>
                        <p>Status: <?php echo ucfirst($row["stock_status"]); ?></p>
                        <?php if($row["stock_status"]=="available"): ?>
                        <div>
                            Quantity: <input type="number" name="quantity" value="1" min="1" class="quantity-input" id="qty<?php echo $row["id"]; ?>" onchange="updateTotal<?php echo $row["id"]; ?>()">
                        </div>
                        <p>Total Price: $<span id="total<?php echo $row["id"]; ?>"><?php echo $row["price"]; ?></span></p>
                        <button class="btn btn-success add-to-cart" data-id="<?php echo $row['id']; ?>">Add to Cart</button>
                        <?php endif; ?>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                      </div>
                    </div>
                  </div>
                </div>

                <script>
                    function updateTotal<?php echo $row["id"]; ?>(){
                        var qty = document.getElementById("qty<?php echo $row["id"]; ?>").value;
                        var unitPrice = <?php echo $row["price"]; ?>;
                        document.getElementById("total<?php echo $row["id"]; ?>").innerText = (unitPrice * qty).toFixed(2);
                    }
                </script>
            <?php
            }
        } else {
            echo "<p class='text-center'>No products found in this category.</p>";
        }
        ?>
    </div>
</div>

<!-- Floating Cart Button -->
<a href="cart.php" class="floating-cart">
    🛒 <span class="cart-count"><?php echo array_sum($_SESSION['cart']); ?></span>
</a>

<!-- FOOTER -->
<footer class="footer">
    <div>© 2025 FCF - Fresh Chilled Frozen</div>
    <div>Contact: +961 7 157 1508</div>
    <div class="footer-icons">
        <a href="https://facebook.com" target="_blank"><i class="bi bi-facebook"></i></a>
        <a href="https://instagram.com" target="_blank"><i class="bi bi-instagram"></i></a>
    <div>Since 2021</div>
    </div>
</footer>

<style>
/* Footer fixed at bottom with flex layout */
body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    background-color: #f8f8f8;
    margin: 0;
}
.container {
    flex: 1;
}
.footer {
    background-color: #222;
    color: #fff;
    text-align: center;
    padding: 20px 0;
}

/* Social icons */
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
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    $('.add-to-cart').click(function(){
        var product_id = $(this).data('id');
        var quantity = parseInt($('#qty'+product_id).val()) || 1;

        $.post('add_to_cart.php', {product_id: product_id, quantity: quantity}, function(data){
            var result = JSON.parse(data);
            $('.floating-cart .cart-count').text(result.total_items); // Update floating cart count
            alert("Added to cart!");
        });
    });
});
</script>

</body>
</html>
