<?php
session_start();
include 'db.php';

$message = '';

if(isset($_POST['reset'])){
    $email = trim($_POST['email']);
    $res = $conn->query("SELECT * FROM users WHERE email='$email' AND role IN ('user','driver') LIMIT 1");
    if($res->num_rows == 0){
        $message = "Email not found!";
    } else {
        $user = $res->fetch_assoc();
        $token = bin2hex(random_bytes(16));
        $conn->query("UPDATE users SET reset_token='$token' WHERE id=".$user['id']);
        // Normally send email here
        $message = "Password reset link: <a href='reset_password.php?token=$token'>Reset Password</a>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Forgot Password - FCF</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body { background-color:#f8f8f8; }
    .container-box {
        max-width: 400px;
        margin: 80px auto;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        text-align: center;
    }
    .logo img { width:100px; margin-bottom:10px; }
</style>
</head>
<body>

<div class="container-box">
    <div class="logo">
        <img src="images/fcf_logo.png" alt="FCF Logo">
        <h4>FRESH • CHILLED • FROZEN</h4>
        <h5 class="mt-3">Forgot Password</h5>
    </div>

    <?php if($message): ?>
        <div class="alert alert-info mt-3"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST" class="mt-3">
        <div class="mb-3 text-start">
            <label>Email</label>
            <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
        </div>
        <button type="submit" name="reset" class="btn btn-primary w-100">Send Reset Link</button>
    </form>

    <p class="mt-3"><a href="login.php">Back to Login</a></p>
</div>

</body>
</html>
