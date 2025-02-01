<?php
session_start();

// Check if the user is logged in, else redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "texura_db";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all products from the database
$sql = "SELECT * FROM products_table";
$result = $conn->query($sql);

if (isset($_POST['add_to_cart'])) {
    // Check if the cart cookie exists
    if (isset($_COOKIE['cart'])) {
        // Decode the existing cart data
        $cart = json_decode($_COOKIE['cart'], true);
    } else {
        // Initialize an empty cart if the cookie doesn't exist
        $cart = [];
    }

    // Get the product details from the form submission
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_image = $_POST['product_image'];

    // Create a product array
    $product = [
        'id' => $product_id,
        'name' => $product_name,
        'price' => $product_price,
        'image' => $product_image,
        'quantity' => 1
    ];

    // Check if the product already exists in the cart
    $found = false;
    foreach ($cart as $key => $item) {
        if ($item['id'] == $product_id) {
            // If the product is found, update the quantity
            $cart[$key]['quantity'] += 1;
            $found = true;
            break;
        }
    }

    // If the product is not found, add it to the cart
    if (!$found) {
        $cart[] = $product;
    }

    // Set the updated cart in the cookie
    setcookie('cart', json_encode($cart), time() + (86400 * 30), "/"); // 30 days expiry
    echo "<script>alert('Product added to cart!');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Products</title>
    <style>
        .product {
            border: 1px solid #ddd;
            padding: 20px;
            margin: 10px;
            display: inline-block;
            width: 250px;
            text-align: center;
        }
        .product img {
            width: 100%;
            height: auto;
        }
        .navbar {
            background-color: #333;
            overflow: hidden;
        }
        .navbar a {
            color: white;
            padding: 14px 20px;
            text-align: center;
            text-decoration: none;
            float: left;
        }
        .navbar a:hover {
            background-color: #ddd;
            color: black;
        }
    </style>
</head>
<body>

    <div class="navbar">
        <a href="welcome.php">Welcome, <?php echo $_SESSION['username']; ?></a>
        <a href="logout.php">Logout</a>
        <a href="cart.php" class="cart-button">View Cart</a> <!-- View Cart Button -->
    </div>

    <h1>Our Products</h1>

    <?php
    // Check if there are any products
    if ($result->num_rows > 0) {
        // Output data of each product
        while ($row = $result->fetch_assoc()) {
            echo "<div class='product'>";
            echo "<h3>" . $row['name'] . "</h3>";
            echo "<p>" . $row['description'] . "</p>";
            echo "<p>Price: $" . number_format($row['price'], 2) . "</p>";
            echo "<img src='" . $row['image1'] . "' alt='" . $row['name'] . "' />";
            echo "<form method='POST' action=''>";
            echo "<input type='hidden' name='product_id' value='" . $row['id'] . "'>";
            echo "<input type='hidden' name='product_name' value='" . $row['name'] . "'>";
            echo "<input type='hidden' name='product_price' value='" . $row['price'] . "'>";
            echo "<input type='hidden' name='product_image' value='" . $row['image1'] . "'>";
            echo "<button type='submit' name='add_to_cart'>Add to Cart</button>";
            echo "</form>";
            echo "</div>";
        }
    } else {
        echo "<p>No products found.</p>";
    }

    // Close the database connection
    $conn->close();
    ?>

</body>
</html>
