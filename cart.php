<?php
session_start();
include 'db.php';

$cart = $_SESSION['cart'] ?? [];
$total = 0;

// Handle quantity update
if(isset($_POST['update_cart'])){
    foreach($_POST['quantities'] as $prod_id => $qty){
        if($qty > 0){
            $_SESSION['cart'][$prod_id] = $qty;
        } else {
            unset($_SESSION['cart'][$prod_id]);
        }
    }
    header("Location: cart.php");
    exit();
}

// Handle remove single item
if(isset($_GET['remove'])){
    $prod_id = $_GET['remove'];
    unset($_SESSION['cart'][$prod_id]);
    header("Location: cart.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopping Cart - FCF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color:#f8f8f8; }
        .cart-img { width:80px; height:80px; object-fit:cover; border-radius:5px; }
        .back-home { margin-bottom:20px; }
    </style>
</head>
<body>
<div class="container mt-4">
    <a href="index.php" class="btn btn-secondary back-home">← Back to Home</a>
    <h2 class="mb-4 text-center">Your Cart</h2>

    <?php if(empty($cart)) { ?>
        <p class="text-center">Your cart is empty.</p>
    <?php } else { ?>
        <form method="POST">
        <table class="table table-bordered bg-white shadow-sm">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Name</th>
                    <th>Unit Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            foreach($cart as $prod_id => $qty){
                $prod_sql = "SELECT * FROM products WHERE id=$prod_id";
                $prod_result = $conn->query($prod_sql);
                if($prod_result->num_rows > 0){
                    $product = $prod_result->fetch_assoc();
                    $line_total = $product['price'] * $qty;
                    $total += $line_total;
                    echo '
                    <tr>
                        <td><img src="images/'.$product["image"].'" class="cart-img" alt="'.$product["name"].'"></td>
                        <td>'.$product["name"].'</td>
                        <td>$'.$product["price"].'</td>
                        <td><input type="number" name="quantities['.$prod_id.']" value="'.$qty.'" min="0" class="form-control" style="width:70px;"></td>
                        <td>$'.number_format($line_total,2).'</td>
                        <td><a href="cart.php?remove='.$prod_id.'" class="btn btn-danger btn-sm">Remove</a></td>
                    </tr>';
                }
            }
            ?>
            </tbody>
        </table>

   
        <div class="text-end mb-4">
    <h4>Total: <span id="grandTotal">$<?php echo number_format($total,2); ?></span></h4>
</div>


        <div class="d-flex justify-content-between mb-5">
            <button type="submit" name="update_cart" class="btn btn-primary">Update Cart</button>
            <!-- Checkout button -->
            <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
        </div>
        </form>
    <?php } ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
<script>
const quantityInputs = document.querySelectorAll('input[name^="quantities"]');

quantityInputs.forEach(input => {
    input.addEventListener('input', () => {
        let row = input.closest('tr');
        let price = parseFloat(row.querySelector('td:nth-child(3)').innerText.replace('$',''));
        let qty = parseInt(input.value);
        row.querySelector('td:nth-child(5)').innerText = '$' + (price * qty).toFixed(2);

        // Update total
        let total = 0;
        document.querySelectorAll('td:nth-child(5)').forEach(td => {
            total += parseFloat(td.innerText.replace('$',''));
        });
        document.querySelector('#grandTotal').innerText = '$' + total.toFixed(2);
    });
});
</script>

</html>
