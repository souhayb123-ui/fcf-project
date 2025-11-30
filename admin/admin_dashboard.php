<?php
session_start();
include '../db.php';

// Logout
if(isset($_GET['logout'])){
    session_destroy();
    header("Location: admin_login.php");
    exit();
}

// Check admin login
if(!isset($_SESSION['user']) || $_SESSION['role'] != 'admin'){
    header("Location: admin_login.php");
    exit();
}


// Handle delete
if(isset($_GET['delete'])){
    $id = $_GET['delete'];
    $res = $conn->query("SELECT * FROM order_items WHERE product_id=$id");
    if($res->num_rows > 0){
        echo "<script>alert('Cannot delete this product because it exists in an order.'); window.location='admin_dashboard.php';</script>";
    } else {
        $conn->query("DELETE FROM products WHERE id=$id");
        header("Location: admin_dashboard.php");
        exit();
    }
}

// Handle add product
if(isset($_POST['add'])){
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $img_name = $_FILES['image']['name'];
    $tmp_name = $_FILES['image']['tmp_name'];
    // Fetch all drivers
$drivers = $conn->query("SELECT * FROM users WHERE role='driver' ORDER BY name ASC");

    move_uploaded_file($tmp_name, "images/".$img_name);
    $conn->query("INSERT INTO products (name, category_id, price, stock_status, image) VALUES ('$name','$category','$price','$stock','$img_name')");
    header("Location: admin_dashboard.php");
    exit();
}

// Handle edit product
if(isset($_POST['edit'])){
    $id = $_POST['id'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $conn->query("UPDATE products SET price='$price', stock_status='$stock' WHERE id=$id");
    header("Location: admin_dashboard.php");
    exit();
}

// Edit request
$editProduct = null;
if(isset($_GET['edit'])){
    $editId = $_GET['edit'];
    $res = $conn->query("SELECT * FROM products WHERE id=$editId");
    $editProduct = $res->fetch_assoc();
}

// Search & filter
$search = $_GET['search'] ?? '';
$filter_cat = $_GET['category'] ?? '';
$sql = "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id=c.id WHERE 1";
if($search != '') $sql .= " AND p.name LIKE '%$search%'";
if($filter_cat != '') $sql .= " AND c.id='$filter_cat'";
$products = $conn->query($sql);
$categories = $conn->query("SELECT * FROM categories");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - FCF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            overflow-x: hidden;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 260px;
            background: #343a40;
            color: white;
            padding-top: 20px;
            overflow-y: auto;
        }
        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 14px 25px;
            font-weight: 500;
        }
        .sidebar a:hover {
            background: #495057;
            border-radius: 5px;
        }
        .main-content {
            margin-left: 260px;
            padding: 20px;
            min-height: 100vh;
        }
        .header {
            background: white;
            padding: 12px 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 10;
            margin-bottom: 20px;
        }
        .full-width-form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 1px 6px rgba(0,0,0,0.1);
            width: 100%;
            margin-bottom: 30px;
        }
        .full-width-form .mb-3 {
            margin-bottom: 15px;
        }
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 15px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            table-layout: auto;
        }
        thead th {
            position: sticky;
            top: 0;
            background: #343a40;
            color: white;
        }
        img.product-img {
            width: 60px;
        }
        h4.section-title {
            margin-bottom: 15px;
        }
        .collapse-button {
            margin-bottom: 15px;
        }
    </style>
    <script>
        function toggleForm(id) {
            const form = document.getElementById(id);
            if(form.style.display === "none") form.style.display = "block";
            else form.style.display = "none";
        }
    </script>
</head>
<body>
<div class="sidebar">
    <h4 class="text-center mb-4">FCF Admin</h4>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="javascript:void(0)" onclick="toggleForm('addProduct')">Add Products</a>
    <a href="add_driver.php">Add Driver</a>
    <a href="admin_drivers.php">Drivers</a>
    <a href="admin_orders.php">Orders</a>
    <a href="admin_orders_map.php">Orders Map</a>
    <a href="admin_analytics.php">Analytics</a>
    <a href="../logout.php">Logout</a>

    

</div>

<div class="main-content">
    <div class="header">
        <h5>Welcome, <?php echo $_SESSION['user']; ?></h5>
    </div>

    <!-- Search & Filter -->
    <form class="row g-3 mb-4" method="GET">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search by product name" value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-md-4">
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                <?php while($cat = $categories->fetch_assoc()){ ?>
                    <option value="<?php echo $cat['id']; ?>" <?php if($filter_cat==$cat['id']) echo 'selected'; ?>><?php echo $cat['name']; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary w-100">Search / Filter</button>
        </div>
    </form>

    <!-- Add Product Form (Vertical & Collapsible) -->
    <h4 class="section-title">Add Products</h4>
    <button class="btn btn-secondary collapse-button" onclick="toggleForm('addProduct')">Toggle Add Product Form</button>
    <div class="full-width-form" id="addProduct">
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <input type="text" name="name" class="form-control" placeholder="Product Name" required>
            </div>
            <div class="mb-3">
                <select name="category" class="form-select" required>
                    <option value="">Select Category</option>
                    <?php 
                    $categories = $conn->query("SELECT * FROM categories");
                    while($cat = $categories->fetch_assoc()){ ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="mb-3">
                <input type="text" name="price" class="form-control" placeholder="Price" required>
            </div>
            <div class="mb-3">
                <select name="stock" class="form-select" required>
                    <option value="available">Available</option>
                    <option value="out">Out of Stock</option>
                </select>
            </div>
            <div class="mb-3">
                <input type="file" name="image" class="form-control" required>
            </div>
            <button type="submit" name="add" class="btn btn-primary w-100">Add Product</button>
        </form>
    </div>
    

    <!-- Edit Product Form -->
    <?php if($editProduct){ ?>
    <h4 class="section-title">Edit Product: <?php echo $editProduct['name']; ?></h4>
    <div class="full-width-form">
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $editProduct['id']; ?>">
            <div class="mb-3">
                <label>Price</label>
                <input type="text" name="price" class="form-control" value="<?php echo $editProduct['price']; ?>" required>
            </div>
            <div class="mb-3">
                <label>Stock Status</label>
                <select name="stock" class="form-select" required>
                    <option value="available" <?php if($editProduct['stock_status']=='available') echo 'selected'; ?>>Available</option>
                    <option value="out" <?php if($editProduct['stock_status']=='out') echo 'selected'; ?>>Out of Stock</option>
                </select>
            </div>
            <button type="submit" name="edit" class="btn btn-success w-100">Save Changes</button>
            <a href="admin_dashboard.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
        </form>
    </div>
    <?php } ?>

    <!-- Products Table -->
    <h4 class="section-title">Products</h4>
    <div class="table-container">
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Image</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($p = $products->fetch_assoc()){ ?>
                <tr>
                    <td><?php echo $p['id']; ?></td>
                    <td><?php echo $p['name']; ?></td>
                    <td><?php echo $p['cat_name']; ?></td>
                    <td>$<?php echo number_format($p['price'],2); ?></td>
                    <td style="color: <?php echo ($p['stock_status']=='available') ? 'green' : 'red'; ?>; font-weight:bold;"><?php echo ucfirst($p['stock_status']); ?></td>
                    <td><img src="images/<?php echo $p['image']; ?>" class="product-img"></td>
                    <td>
                        <a href="admin_dashboard.php?edit=<?php echo $p['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="admin_dashboard.php?delete=<?php echo $p['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>