<?php
// Check if the cart cookie exists
if (isset($_COOKIE['cart'])) {
    // Decode the cart data
    $cart = json_decode($_COOKIE['cart'], true);
} else {
    $cart = [];
}

// Handle removal of item from cart
if (isset($_GET['remove_id'])) {
    $remove_id = $_GET['remove_id'];
    
    // Loop through cart and remove the item
    foreach ($cart as $key => $item) {
        if ($item['id'] == $remove_id) {
            unset($cart[$key]);
            break;
        }
    }
    
    // Save the updated cart back to the cookie
    setcookie('cart', json_encode(array_values($cart)), time() + (86400 * 30), "/"); // 30 days expiry
    header("Location: cart.php"); // Refresh the page
    exit();
}

// Handle updating the quantity and size
if (isset($_POST['update_cart'])) {
    foreach ($cart as $key => &$item) {
        if ($item['id'] == $_POST['item_id']) {
            // Update quantity
            $item['quantity'] = (int)$_POST['quantity'];
            // Update size
            $item['size'] = $_POST['size'];
        }
    }
    
    // Save the updated cart back to the cookie
    setcookie('cart', json_encode(array_values($cart)), time() + (86400 * 30), "/"); // 30 days expiry
    header("Location: cart.php"); // Refresh the page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=x, initial-scale=1.0">
    <title>Your Cart</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        h1 {
            color: #333;
        }
        .cart-item {
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
            margin-bottom: 10px;
        }
        .cart-item img {
            width: 100px;
            height: auto;
        }
        .cart-item h3 {
            margin: 0;
            font-size: 18px;
        }
        .cart-item p {
            margin: 5px 0;
        }
        .remove-btn {
            color: red;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
        }
        .remove-btn i {
            font-size: 18px;
        }
        .total-price {
            font-size: 18px;
            margin-top: 20px;
        }
        .checkout-btn {
            background-color: green;
            color: white;
            padding: 10px;
            text-decoration: none;
            font-weight: bold;
            border-radius: 5px;
        }
        .checkout-btn:hover {
            background-color: darkgreen;
        }
        .quantity-btns {
            display: inline-flex;
            align-items: center;
        }
        .quantity-btn {
            font-size: 20px;
            padding: 5px 10px;
            cursor: pointer;
        }
        select {
            padding: 5px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <!-- Header -->
 <div class="banner">
        <nav class="navbar navbar-expand-lg py-3">
            <div class="container">
                <a href="/">
                    <img src="img/logo.png" alt="Logo" />
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link" href="index.php">HOME</a></li>
                        <li class="nav-item"><a class="nav-link" href="newArrival.html">NEW ARRIVAL</a></li>
                        <li class="nav-item"><a class="nav-link" href="shop.html">SHOP</a></li>
                    </ul>
                    
                    <ul class="navbar-nav ms-auto">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <!-- User logged in -->
                            <li class="nav-item">
                                <a class="nav-link" href="cart.php">
                                <svg width="20" height="20" viewBox="0 0 31 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path fill-rule="evenodd" clip-rule="evenodd" d="M12.4878 5.17515C13.2614 4.33124 14.3106 3.85714 15.4047 3.85714C16.4987 3.85714 17.5479 4.33124 18.3215 5.17515C19.0951 6.01907 19.5297 7.16367 19.5297 8.35714V10.0616H11.2797V8.35714C11.2797 7.16367 11.7143 6.01907 12.4878 5.17515ZM7.74395 10.0616V8.35714C7.74395 6.1407 8.55106 4.01503 9.98772 2.44775C11.4244 0.880483 13.3729 0 15.4047 0C17.4364 0 19.3849 0.880483 20.8216 2.44775C22.2583 4.01503 23.0654 6.1407 23.0654 8.35714V10.0616H27.1904C27.7908 10.0616 28.2952 10.5539 28.3617 11.2048L30.3584 30.735C30.3638 30.7877 30.3712 30.8487 30.3792 30.9171C30.4291 31.3354 30.5116 32.0253 30.3841 32.6906C30.1147 34.0964 29.1322 35.2504 27.8551 35.6377C27.2791 35.8123 26.624 35.7927 26.2252 35.7807C26.1417 35.7781 26.0696 35.7758 26.0119 35.7758H4.79752C4.73961 35.7758 4.66748 35.7781 4.58418 35.7807C4.1853 35.7927 3.53037 35.8123 2.95412 35.6377C1.67719 35.2504 0.694517 34.0966 0.425166 32.6906C0.297715 32.0253 0.380097 31.3354 0.430045 30.9171C0.438201 30.8487 0.445508 30.7877 0.450882 30.735L2.44764 11.2048C2.51418 10.5539 3.01859 10.0616 3.61895 10.0616H7.74395Z" fill="black"/>
                      </svg>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="">Welcome, <?php echo $_SESSION['username']; ?></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="logout.php">Logout</a>
                            </li>
                        <?php else: ?>
                            <!-- User not logged in -->
                            <li class="nav-item"><a class="btn btn-dark" href="login.php">Login</a></li>
                            <li class="nav-item"><a class="btn btn-dark" href="admin_login.php">Admin</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </div>

    <h1>Your Cart</h1>

    <?php if (empty($cart)) { ?>
        <p>Your cart is empty.</p>
    <?php } else { ?>
        <ul>
            <?php
            $total_price = 0;
            foreach ($cart as $item) {
                // Ensure price is treated as a float
                $item_price = (float)$item['price'];
                $total_price += $item_price * (int)$item['quantity'];
                ?>
                <li class="cart-item">
                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    <div>
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p>Price: $<?php echo number_format($item_price, 2); ?></p>

                        <form method="POST" action="cart.php">
                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                            
                            <!-- Quantity adjustment -->
                            <div class="quantity-btns">
                                <button type="button" class="quantity-btn" onclick="changeQuantity('<?php echo $item['id']; ?>', -1)">-</button>
                                <input type="number" name="quantity" value="<?php echo (int)$item['quantity']; ?>" min="1" max="99" style="width: 50px; text-align: center;" id="quantity-<?php echo $item['id']; ?>" required>
                                <button type="button" class="quantity-btn" onclick="changeQuantity('<?php echo $item['id']; ?>', 1)">+</button>
                            </div>
                            
                            <!-- Size selection -->
                            <p>Select Size: 
                                <select name="size" required>
                                    <option value="S" <?php echo $item['size'] == 'S' ? 'selected' : ''; ?>>S</option>
                                    <option value="M" <?php echo $item['size'] == 'M' ? 'selected' : ''; ?>>M</option>
                                    <option value="L" <?php echo $item['size'] == 'L' ? 'selected' : ''; ?>>L</option>
                                    <option value="XL" <?php echo $item['size'] == 'XL' ? 'selected' : ''; ?>>XL</option>
                                    <option value="XXL" <?php echo $item['size'] == 'XXL' ? 'selected' : ''; ?>>XXL</option>
                                </select>
                            </p>
                        </form>

                        <!-- Remove icon -->
                        <a href="cart.php?remove_id=<?php echo $item['id']; ?>" class="remove-btn"><i class="fas fa-trash-alt"></i> Remove</a>
                    </div>
                </li>
            <?php } ?>
        </ul>

        <div class="total-price">
            <p>Total Price: $<?php echo number_format((float)$total_price, 2); ?></p>
        </div>

        <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
    <?php } ?>

    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script>
        function changeQuantity(itemId, change) {
            let quantityInput = document.getElementById(`quantity-${itemId}`);
            let currentQuantity = parseInt(quantityInput.value);
            if (currentQuantity + change >= 1) {
                quantityInput.value = currentQuantity + change;
            }
        }
    </script>

</body>
</html>
