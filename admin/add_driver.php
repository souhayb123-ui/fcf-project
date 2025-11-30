<?php
session_start();
include '../db.php';

// Logout
if(isset($_GET['logout'])){
    session_destroy();
    header("Location: ../logout.php");
    exit();
}

// Check admin login
if(!isset($_SESSION['user']) || $_SESSION['role'] != 'admin'){
    header("Location: ../logout.php");
    exit();
}

// Handle add driver
if(isset($_POST['add_driver'])){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT); // hash password
    $role = 'driver';

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if($res->num_rows > 0){
        $message = "Email already exists!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $role);
        $stmt->execute();
        header("Location: admin_dashboard.php?success=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Driver - FCF Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9fa; }
        .container { max-width: 500px; margin: 50px auto; }
        .form-container {
            background: white; padding: 20px; border-radius: 10px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<div class="container">
    <h3 class="mb-4 text-center">Add Driver</h3>

    <?php if(isset($message)): ?>
        <div class="alert alert-danger"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="form-container">
        <form method="POST">
            <div class="mb-3">
                <input type="text" name="name" class="form-control" placeholder="Driver Name" required>
            </div>
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <button type="submit" name="add_driver" class="btn btn-success w-100">Add Driver</button>
            <a href="admin_dashboard.php" class="btn btn-secondary w-100 mt-2">Back to Dashboard</a>
        </form>
    </div>
</div>
</body>
</html>
