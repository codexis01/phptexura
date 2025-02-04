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

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch product details
$stmt = $conn->prepare("SELECT name, description, price, image1, image2, image3 FROM products_table WHERE id = ?");
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
    <title><?php echo htmlspecialchars($product['name']); ?> - Texura</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
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

<!-- Product Section -->
<div class="container mt-5">
    <div class="row">
        <div class="col-12 col-md-2">
            <img src="<?php echo htmlspecialchars($product['image1']); ?>" class="img-fluid mb-2 small-img" onclick="changeImage(this)">
            <img src="<?php echo htmlspecialchars($product['image2']); ?>" class="img-fluid mb-2 small-img" onclick="changeImage(this)">
            <img src="<?php echo htmlspecialchars($product['image3']); ?>" class="img-fluid mb-2 small-img" onclick="changeImage(this)">
        </div>

        <div class="col-12 col-md-4">
            <img id="mainImage" src="<?php echo htmlspecialchars($product['image1']); ?>" class="img-fluid">
        </div>

        <div class="col-12 col-md-6">
            <h4><?php echo htmlspecialchars($product['name']); ?></h4>
            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            <p>₹<?php echo number_format($product['price'], 2); ?></p>
            
            <div class="row">
                <div class="col-3"><h5>Size:</h5></div>
                <div class="col-9">
                    <select class="form-select w-50" id="size" name="size">
                        <option value="S">S</option>
                        <option value="M" selected>M</option>
                        <option value="L">L</option>
                        <option value="XL">XL</option>
                        <option value="XXL">XXL</option>
                    </select>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-3"><h5>Quantity:</h5></div>
                <div class="col-9">
                    <div class="input-group w-50">
                        <button class="btn btn-outline-dark" type="button" onclick="decreaseQuantity()">-</button>
                        <input type="text" id="quantity" name="quantity" class="form-control text-center" value="1" readonly>
                        <button class="btn btn-outline-dark" type="button" onclick="increaseQuantity()">+</button>
                    </div>
                </div>
            </div>

            <!-- Add to Cart Form -->
            <form method="POST" action="shop.php">
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['name']); ?>">
                <input type="hidden" name="product_price" value="<?php echo $product['price']; ?>">
                <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($product['image1']); ?>">
                <input type="hidden" name="size" id="selectedSize" value="M">
                <input type="hidden" name="quantity" id="selectedQuantity" value="1">
                <button type="submit" name="add_to_cart" class="btn btn-dark mt-3">Add to Cart</button>
            </form>
            
            <a id="buyNowBtn" class="btn btn-warning ms-2 mt-3">Buy Now</a>
        </div>
    </div>
</div>

<script>
    function changeImage(element) {
        document.getElementById("mainImage").src = element.src;
    }

    function increaseQuantity() {
        let qty = document.getElementById("quantity");
        qty.value = parseInt(qty.value) + 1;
        updateFormValues();
        updateBuyNowLink();
    }

    function decreaseQuantity() {
        let qty = document.getElementById("quantity");
        if (parseInt(qty.value) > 1) {
            qty.value = parseInt(qty.value) - 1;
            updateFormValues();
            updateBuyNowLink();
        }
    }

    function updateFormValues() {
        document.getElementById("selectedSize").value = document.getElementById("size").value;
        document.getElementById("selectedQuantity").value = document.getElementById("quantity").value;
    }

    function updateBuyNowLink() {
        let size = document.getElementById("size").value;
        let quantity = document.getElementById("quantity").value;
        document.getElementById("buyNowBtn").href = `buynow.php?id=<?php echo $product_id; ?>&size=${encodeURIComponent(size)}&quantity=${encodeURIComponent(quantity)}`;
    }

    document.getElementById("size").addEventListener("change", updateFormValues);
    document.getElementById("size").addEventListener("change", updateBuyNowLink);
    updateBuyNowLink();
</script>

</body>
</html>

<?php $conn->close(); ?>
