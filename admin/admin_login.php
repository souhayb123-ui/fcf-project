<?php
session_start();
include '../db.php'; // adjust path if needed

$error = "";

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Fetch admin user
    $sql = "SELECT * FROM users WHERE email='$email' AND role='admin'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Compare plain text password
        if ($password == $user['password']) {
            $_SESSION['admin'] = $user['id'];
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = "Wrong password!";
        }

    } else {
        $error = "Admin not found!";
    }
}
?>

?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - FCF</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .login-box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 15px #ccc; width: 350px; text-align: center; }
        input { width: 90%; padding: 10px; margin: 10px 0; }
        button { padding: 10px 20px; background: green; color: white; border: none; cursor: pointer; border-radius: 5px; }
        .error { color: red; }
    </style>
</head>

<body>

<div class="login-box">
    <h2>Admin Login</h2>
    <?php if($error) { echo "<p class='error'>$error</p>"; } ?>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit" name="login">Login</button>
    </form>
</div>

</body>
</html>