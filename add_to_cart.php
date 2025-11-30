<?php
session_start();
include 'db.php';

$product_id = $_POST['product_id'];
$quantity = (int)$_POST['quantity'];

// If user logged in, save to DB
if(isset($_SESSION['user'])){
    $user_id = $_SESSION['user'];
    
    // Check if product already in DB
    $check = $conn->query("SELECT * FROM cart_items WHERE user_id=$user_id AND product_id=$product_id");
    if($check->num_rows > 0){
        $conn->query("UPDATE cart_items SET quantity = quantity + $quantity WHERE user_id=$user_id AND product_id=$product_id");
    } else {
        $conn->query("INSERT INTO cart_items (user_id, product_id, quantity) VALUES ($user_id, $product_id, $quantity)");
    }
}

// Also save/update session cart for guests or current session
if(!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
if(isset($_SESSION['cart'][$product_id])){
    $_SESSION['cart'][$product_id] += $quantity;
}else{
    $_SESSION['cart'][$product_id] = $quantity;
}

// Return total items
$total_items = array_sum($_SESSION['cart']);
echo json_encode(['total_items' => $total_items]);
