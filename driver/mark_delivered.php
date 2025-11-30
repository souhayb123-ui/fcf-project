<?php
session_start();
include '../db.php';

if(!isset($_SESSION['driver'])){
    header("Location: driver_login.php");
    exit();
}

if(isset($_GET['id'])){
    $order_id = intval($_GET['id']);
    $driver_id = $_SESSION['driver'];

    // Update order status to delivered
    $conn->query("UPDATE orders SET status='delivered' WHERE id=$order_id AND driver_id=$driver_id");
}

header("Location: driver_dashboard.php");
exit();
?>
