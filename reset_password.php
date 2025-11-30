<?php
session_start();
include 'db.php';

$message = '';
$token = $_GET['token'] ?? '';

if(isset($_POST['reset'])){
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $token = $_POST['token'];

    $res = $conn->query("SELECT * FROM users WHERE reset_token='$token' LIMIT 1");
    if($res->num_rows == 0){
        $message = "Invalid token!";
    } else {
        $user = $res->fetch_assoc();
        $conn->query("UPDATE users SET password='$password', reset_token=NULL WHERE id=".$user['id']);
        $message = "Password reset successfully! <a href='login.php'>Login</a>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reset Password - FCF</title>
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
        <h5 class="mt-3">Reset Password</h5>
    </div>

    <?php if($message): ?>
        <div class="alert alert-info mt-3"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST" class="mt-3">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <div class="mb-3 text-start">
            <label>New Password</label>
            <input type="password" name="password" class="form-control" placeholder="Enter new password" required>
        </div>
        <button type="submit" name="reset" class="btn btn-primary w-100">Reset Password</button>
    </form>

    <p class="mt-3"><a href="login.php">Back to Login</a></p>
</div>

</body>
</html>
