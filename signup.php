<?php
session_start();
include 'db.php';

$message = '';

if(isset($_POST['signup'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    // Validate
    if($password !== $confirm){
        $message = "Passwords do not match!";
    } else {
        // Check if email exists
        $check = $conn->query("SELECT * FROM users WHERE email='$email'");
        if($check->num_rows > 0){
            $message = "Email already registered!";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $conn->query("INSERT INTO users (name, email, password, role) VALUES ('$name','$email','$hash','user')");
            $message = "Sign up successful! You can now log in.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sign Up - FCF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center mb-4">Sign Up</h2>
    <?php if($message) echo "<div class='alert alert-info'>$message</div>"; ?>
    <form method="POST" class="mx-auto" style="max-width:400px;">
        <input type="text" name="name" placeholder="Full Name" class="form-control mb-2" required>
        <input type="email" name="email" placeholder="Email" class="form-control mb-2" required>
        <input type="password" name="password" placeholder="Password" class="form-control mb-2" required>
        <input type="password" name="confirm" placeholder="Confirm Password" class="form-control mb-2" required>
        <button type="submit" name="signup" class="btn btn-primary w-100">Sign Up</button>
        <p class="mt-2 text-center">Already have an account? <a href="login.php">Login</a></p>
    </form>
</div>
</body>
</html>
