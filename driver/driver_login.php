<?php
session_start();
include '../db.php';

$message = '';

if(isset($_POST['login'])){
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM drivers WHERE email=? LIMIT 1");
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $res = $stmt->get_result();

    if($res->num_rows == 0){
        $message = "Driver not found!";
    } else {
        $driver = $res->fetch_assoc();
        if(password_verify($password, $driver['password'])){
            $_SESSION['driver'] = $driver['id'];
            $_SESSION['driver_name'] = $driver['name'];
            header("Location: driver_dashboard.php");
            exit();
        } else {
            $message = "Wrong password!";
        }
    }
}
?>
