<?php
session_start();
header('Content-Type: application/json');
include 'db.php';

$message = $_POST['message'] ?? '';
$message = strtolower(trim($message));

$reply = "Sorry, I didn't understand. Try asking about products, prices, categories, or contact info.";
$keyword_map = [
    'meat' => 'Meat',
    'chicken' => 'Chicken',
    'fish' => 'Fish',
     
];


// --- Greeting ---
if(strpos($message,'hello')!==false || strpos($message,'hi')!==false){
    $reply = "Hello! Welcome to FCF - Fresh Chilled Frozen!";
}
// --- Contact ---
elseif(strpos($message,'contact')!==false){
    $reply = "You can contact us at +961 86716119.";
}
// --- Categories ---
elseif(strpos($message,'categories')!==false){
    $res = $conn->query("SELECT name FROM categories");
    if($res->num_rows > 0){
        $cats = [];
        while($row = $res->fetch_assoc()) $cats[] = $row['name'];
        $reply = "We have the following categories: ".implode(", ", $cats);
    } else {
        $reply = "No categories available right now.";
    }
}
// --- Out of stock ---
elseif(strpos($message,'out of stock')!==false){
    $res = $conn->query("SELECT name FROM products WHERE stock_status='out'");
    if($res->num_rows > 0){
        $out = [];
        while($row = $res->fetch_assoc()) $out[] = $row['name'];
        $reply = "Currently out of stock: ".implode(", ", $out);
    } else {
        $reply = "All products are available!";
    }
}
elseif(strpos($message,'location') !== false || strpos($message,'where') !== false){
    $reply = "Here is our store location:";
    // Send a flag to JS so it knows to show the map
    echo json_encode(['reply'=>$reply, 'showMap'=>true]);
    exit;
}

// --- Specific product query ---
elseif(strpos($message,'do you have')!==false || strpos($message,'price of')!==false){
    // Remove common phrases to extract product name
    $prod_name = str_replace(['do you have','price of','?'], '', $message);
    $prod_name = trim($prod_name);

    if(!empty($prod_name)){
        $prod_name_wild = "%".$prod_name."%";
        $stmt = $conn->prepare("SELECT name, price, stock_status FROM products WHERE name LIKE ?");
        $stmt->bind_param("s",$prod_name_wild);
        $stmt->execute();
        $res = $stmt->get_result();

        if($res->num_rows > 0){
            $p = $res->fetch_assoc();
            if($p['stock_status']=='available'){
                $reply = "Yes, we have '{$p['name']}'. Price: $".$p['price'];
            } else {
                $reply = "Sorry, '{$p['name']}' is currently out of stock.";
            }
        } else {
            $reply = "Sorry, we don’t have '{$prod_name}' in our store.";
        }
    } else {
        $reply = "Please specify the product name.";
    }
}
// --- Check for keyword-based category queries ---
foreach($keyword_map as $key => $category_name){
    if(strpos($message, $key) !== false){
        // Fetch products in this category that are available
        $stmt = $conn->prepare("SELECT name, price FROM products WHERE category_id = (SELECT id FROM categories WHERE name = ?) AND stock_status='available'");
        $stmt->bind_param("s", $category_name);
        $stmt->execute();
        $res = $stmt->get_result();

        if($res->num_rows > 0){
            $products = [];
            while($p = $res->fetch_assoc()){
                $products[] = "{$p['name']} ($".$p['price'].")";
            }
            $reply = "Available {$category_name} products: ".implode(", ", $products);
        } else {
            $reply = "Sorry, no {$category_name} products are available right now.";
        }
        break; // stop checking other keywords
    }
}

echo json_encode(['reply'=>$reply]);
