<?php
session_start();

// Database connection
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "texura_db";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get product ID, size, and quantity from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$size = isset($_GET['size']) ? htmlspecialchars($_GET['size']) : 'M'; 
$quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1; 

// Fetch product details
$stmt = $conn->prepare("SELECT name, description, price, image1 FROM products_table WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if product exists
if ($result->num_rows === 0) {
    die("Product not found!");
}
$product = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy Now - <?php echo htmlspecialchars($product['name']); ?> - Texura</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .checkout-container {
            max-width: 1000px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
        }

        .checkout-left, .checkout-right {
            padding: 20px;
        }

        .product-img {
            width: 100%;
            height: auto;
            border-radius: 8px;
        }

        .summary-box {
            background: #f7f7f7;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }

        .btn-custom {
            background-color: #2874f0;
            color: white;
        }

        .btn-custom:hover {
            background-color: #1f5eb8;
        }
    </style>
</head>
<body>

<!-- Navbar (Same as category.php) -->
<div class="banner">
    <nav class="navbar navbar-expand-lg py-3">
        <div class="container">
            <a href="index.php">
                <img src="img/logo.png" alt="Logo" />
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">HOME</a></li>
                    <li class="nav-item"><a class="nav-link" href="newarrival.php">NEW ARRIVAL</a></li>
                    <li class="nav-item"><a class="nav-link" href="shop.php">SHOP</a></li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="cart.php"><svg width="20" height="20" viewBox="0 0 31 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                                      <path fill-rule="evenodd" clip-rule="evenodd" d="M12.4878 5.17515C13.2614 4.33124 14.3106 3.85714 15.4047 3.85714C16.4987 3.85714 17.5479 4.33124 18.3215 5.17515C19.0951 6.01907 19.5297 7.16367 19.5297 8.35714V10.0616H11.2797V8.35714C11.2797 7.16367 11.7143 6.01907 12.4878 5.17515ZM7.74395 10.0616V8.35714C7.74395 6.1407 8.55106 4.01503 9.98772 2.44775C11.4244 0.880483 13.3729 0 15.4047 0C17.4364 0 19.3849 0.880483 20.8216 2.44775C22.2583 4.01503 23.0654 6.1407 23.0654 8.35714V10.0616H27.1904C27.7908 10.0616 28.2952 10.5539 28.3617 11.2048L30.3584 30.735C30.3638 30.7877 30.3712 30.8487 30.3792 30.9171C30.4291 31.3354 30.5116 32.0253 30.3841 32.6906C30.1147 34.0964 29.1322 35.2504 27.8551 35.6377C27.2791 35.8123 26.624 35.7927 26.2252 35.7807C26.1417 35.7781 26.0696 35.7758 26.0119 35.7758H4.79752C4.73961 35.7758 4.66748 35.7781 4.58418 35.7807C4.1853 35.7927 3.53037 35.8123 2.95412 35.6377C1.67719 35.2504 0.694517 34.0966 0.425166 32.6906C0.297715 32.0253 0.380097 31.3354 0.430045 30.9171C0.438201 30.8487 0.445508 30.7877 0.450882 30.735L2.44764 11.2048C2.51418 10.5539 3.01859 10.0616 3.61895 10.0616H7.74395Z" fill="black"/>
                                    </svg></a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link" href="#">Welcome, <?php echo $_SESSION['username']; ?></a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="btn btn-dark" href="login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</div>

<!-- Product Info Section -->
<div class="container mt-5">
    <div class="row">
        <div class="col-md-6 checkout-left">
            <img src="<?php echo htmlspecialchars($product['image1']); ?>" class="product-img">
            <h4><?php echo htmlspecialchars($product['name']); ?></h4>
            <p><strong>Price:</strong> ₹<?php echo number_format($product['price'], 2); ?></p>
            <p><strong>Size:</strong> <?php echo htmlspecialchars($size); ?></p>
            <p><strong>Quantity:</strong> <?php echo $quantity; ?></p>
            <p><strong>Total Price:</strong> ₹<?php echo number_format($product['price'] * $quantity, 2); ?></p>
        </div>

        <div class="col-md-6 checkout-right">
            <h5>Order Summary</h5>
            <div class="summary-box">
                <p><strong>Product:</strong> <?php echo htmlspecialchars($product['name']); ?></p>
                <p><strong>Quantity:</strong> <?php echo $quantity; ?></p>
                <p><strong>Price per unit:</strong> ₹<?php echo number_format($product['price'], 2); ?></p>
                <hr>
                <p><strong>Delivery Charge:</strong> ₹50</p>
                <p><strong>Platform Fee:</strong> ₹5</p>
                <hr>
                <p><strong>Total:</strong> ₹<?php echo number_format($product['price'] * $quantity + 50 + 5, 2); ?></p>
            </div>
            
            <form action="checkout_process.php" method="POST">
                <h5>Delivery Address</h5>

                <div class="form-group mt-3">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>

                <div class="form-group mt-3">
                    <label for="contact">Contact Number</label>
                    <input type="text" class="form-control" id="contact" name="contact" required>
                </div>

                <div class="form-group mt-3">
                    <label for="pincode">Pincode</label>
                    <input type="text" class="form-control" id="pincode" name="pincode" required>
                </div>

                <div class="form-group mt-3">
                    <label for="locality">Locality (Optional)</label>
                    <input type="text" class="form-control" id="locality" name="locality">
                </div>

                <div class="form-group mt-3">
                    <label for="address">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                </div>

                <div class="form-group mt-3">
                    <label for="city">City</label>
                    <input type="text" class="form-control" id="city" name="city" required>
                </div>

                <div class="form-group mt-3">
                    <label for="state">State</label>
                    <select class="form-select" id="state" name="state" required>
                        <option value="Uttarakhand">Uttarakhand</option>
                        <option value="Delhi">Delhi</option>
                        <option value="Maharashtra">Maharashtra</option>
                        <option value="Karnataka">Karnataka</option>
                    </select>
                </div>

                <div class="form-group mt-3">
                    <label for="landmark">Landmark (Optional)</label>
                    <input type="text" class="form-control" id="landmark" name="landmark">
                </div>

                <div class="form-group mt-3">
                    <label for="alt_phone">Alternate Phone (Optional)</label>
                    <input type="text" class="form-control" id="alt_phone" name="alt_phone">
                </div>

                <div class="form-group mt-3">
                    <label for="payment">Payment Method</label>
                    <select class="form-select" id="payment" name="payment_method" required>
                        <option value="COD">Cash on Delivery</option>
                        <option value="Card">Credit/Debit Card</option>
                        <option value="UPI">UPI Payment</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-custom btn-block mt-4">Place Orders</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php $conn->close(); ?>
