<?php
session_start();
include 'db.php';

$message = '';
$showForgot = false;

if(!isset($_SESSION['login_attempts'])){
    $_SESSION['login_attempts'] = 0;
}

if(isset($_POST['login'])){
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if($res->num_rows == 0){
        $message = "User not found!";
        $_SESSION['login_attempts']++;
    } else {
        $user = $res->fetch_assoc();
        $role = $user['role'];

        // Skip admin for forgot password
        if($role === "admin"){
            if($password !== $user['password']){
                $message = "Wrong password!";
            } else {
                $_SESSION['user'] = $user['id'];
                $_SESSION['role'] = "admin";
                $_SESSION['login_attempts'] = 0;
                header("Location: admin/admin_dashboard.php");
                exit();
            }
        }
        else { // driver or normal user
            if(!password_verify($password, $user['password'])){
                $message = "Wrong password!";
                $_SESSION['login_attempts']++;

                // Show forgot password after 3 attempts
                if($_SESSION['login_attempts'] >= 3){
                    $showForgot = true;
                }

            } else {
                // Reset attempts on successful login
                $_SESSION['login_attempts'] = 0;

                if($role === "driver"){
                    $_SESSION['driver'] = $user['id'];
                    $_SESSION['driver_name'] = $user['username'];
                    header("Location: driver/driver_dashboard.php");
                    exit();
                } else { // normal user
                    $_SESSION['user'] = $user['id'];
                    $_SESSION['role'] = "user";

                    // Merge session cart with DB cart
                    if(empty($_SESSION['cart'])){
                        $conn->query("DELETE FROM cart_items WHERE user_id=".$user['id']);
                    }
                    $db_cart = [];
                    $res_cart = $conn->query("SELECT product_id, quantity FROM cart_items WHERE user_id=".$user['id']);
                    while($row = $res_cart->fetch_assoc()){
                        $db_cart[$row['product_id']] = $row['quantity'];
                    }
                    foreach($_SESSION['cart'] as $pid => $qty){
                        if(isset($db_cart[$pid])){
                            $new_qty = $db_cart[$pid] + $qty;
                            $conn->query("UPDATE cart_items SET quantity=$new_qty WHERE user_id=".$user['id']." AND product_id=$pid");
                            $_SESSION['cart'][$pid] = $new_qty;
                        } else {
                            $conn->query("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (".$user['id'].",$pid,$qty)");
                        }
                    }
                    $_SESSION['cart'] = [];
                    $res_cart = $conn->query("SELECT product_id, quantity FROM cart_items WHERE user_id=".$user['id']);
                    while($row = $res_cart->fetch_assoc()){
                        $_SESSION['cart'][$row['product_id']] = $row['quantity'];
                    }

                    header("Location: index.php");
                    exit();
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - FCF</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body { background-color:#f8f8f8; }
    .login-container {
        max-width: 400px;
        margin: 80px auto;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    .logo { text-align:center; margin-bottom:20px; }
</style>
</head>
<body>

<div class="login-container">
    <div class="logo">
        <img src="images/fcf_logo.png" alt="FCF Logo" style="width:100px;">
        <h4 class="mt-2">FRESH • CHILLED • FROZEN</h4>
    </div>

    <?php if($message): ?>
        <div class="alert alert-danger"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
    </form>

    <?php if($showForgot): ?>
        <div class="mt-3 text-center">
            <a href="forgot_password.php">Forgot Password?</a>
        </div>
    <?php endif; ?>

    <p class="mt-3 text-center">Don't have an account? <a href="signup.php">Sign Up</a></p>
</div>

</body>
</html>
