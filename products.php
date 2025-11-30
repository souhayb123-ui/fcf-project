<?php 
include 'db.php';

$cat_id = $_GET['cat'];  // category id from URL

// Get category info
$cat_sql = "SELECT * FROM categories WHERE id = $cat_id";
$cat_result = $conn->query($cat_sql);
$category = $cat_result->fetch_assoc();

// Get products
$prod_sql = "SELECT * FROM products WHERE category_id = $cat_id";
$products = $conn->query($prod_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $category['name']; ?> - FCF</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; text-align: center; }
        .header { background: white; padding: 20px; box-shadow: 0 0 10px #ccc; }
        .header img { width: 100px; }
        .container { display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; margin-top: 30px; }
        .card { background: white; width: 250px; border-radius: 10px; padding: 10px; box-shadow: 0 0 10px #ccc; }
        .card img { width: 100%; height: 180px; object-fit: cover; border-radius: 10px; }
        .price { font-size: 20px; color: green; }
        .stock { margin-top: 5px; font-weight: bold; }
        .out { color: red; }
        .back { margin-top: 20px; display: inline-block; padding: 10px 20px; background: #555; color: white; border-radius: 5px; text-decoration: none; }
    </style>
</head>

<body>

<div class="header">
    <img src="images/fcf_logo.png">
    <h2><?php echo $category['name']; ?> Products</h2>
</div>

<a class="back" href="index.php">← Back to Home</a>

<div class="container">
    <?php
    if ($products->num_rows > 0) {
        while ($p = $products->fetch_assoc()) {
            echo "
            <div class='card'>
                <img src='images/" . $p['image'] . "'>
                <h3>" . $p['name'] . "</h3>
                <div class='price'>" . $p['price'] . " $</div>
                <div class='stock " . ($p['stock_status'] == 'out' ? 'out' : '') . "'>" 
                . ucfirst($p['stock_status']) . 
                "</div>
            </div>";
        }
    } else {
        echo "<h3>No products yet</h3>";
    }
    ?>
</div>

</body>
</html>
