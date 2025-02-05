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

// Get the selected category from URL
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Fetch products for the selected category
$stmt = $conn->prepare("SELECT id, name, price, image1 FROM products_table WHERE category = ?");
$stmt->bind_param("s", $category);
$stmt->execute();
$result = $stmt->get_result();

$message = ""; // To store add-to-cart messages

// Handle Add to Cart
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_to_cart'])) {
    // Ensure user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo "<script>alert('Please log in to add items to the cart.'); window.location='login.php';</script>";
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    $product_name = htmlspecialchars($_POST['product_name']);
    $product_price = $_POST['product_price'];
    $product_image = htmlspecialchars($_POST['product_image']);
    $quantity = 1;
    $size = $_POST['size'] ?? 'M'; // Default size M

    // Check if the product already exists in the user's cart
    $stmt = $conn->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $message = "Product already in cart!";
    } else {
        // Insert new product into the cart
        $insert_stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, product_name, product_price, product_image, quantity, size) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("iisdssi", $user_id, $product_id, $product_name, $product_price, $product_image, $quantity, $size);
        $insert_stmt->execute();
        $message = "Product added to cart!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category); ?> - Texura</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <style>
        /* Product Card Styling */
        .product-card {
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.2s ease-in-out;
            background: #fff;
            padding: 15px;
            text-align: center;
        }

        .product-card:hover {
            transform: scale(1.05);
        }

        .product-img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 12px;
        }

        .product-title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
        }

        .product-price {
            font-size: 16px;
            color: #d9534f;
            font-weight: bold;
        }

        .add-to-cart-btn {
            background-color:rgb(0, 0, 0);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }

        .add-to-cart-btn:hover {
            background-color: #218838;
        }

        @media (max-width: 768px) {
            .product-img {
                height: 250px;
            }
        }

        @media (max-width: 576px) {
            .product-img {
                height: 200px;
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
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
                    <li class="nav-item"><a class="nav-link" href="#about_html">ABOUT US</a></li>
                <li class="nav-item"><a class="nav-link" href="#contact__section">CONTACT</a></li>
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

<!-- Display Popup Message -->
<?php if (!empty($message)): ?>
    <script>
        alert("<?php echo $message; ?>");
        window.location.href = "category.php?category=<?php echo urlencode($category); ?>";
    </script>
<?php endif; ?>

<!-- Category Heading -->
<div class="container mt-4">
    <h2 class="text-center"><?php echo htmlspecialchars($category); ?></h2>
</div>

<!-- Product Display -->
<div class="container mt-4">
    <div class="row">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) { ?>
                <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
                    <div class="product-card">
                    <a href="product.php?id=<?php echo $row['id']; ?>">
    <img src="<?php echo htmlspecialchars($row['image1']); ?>" class="product-img" alt="<?php echo htmlspecialchars($row['name']); ?>">
</a>

                        <p class="product-title"><?php echo htmlspecialchars($row['name']); ?></p>
                        <p class="product-price">₹<?php echo number_format($row['price'], 2); ?></p>
                        
                        <!-- Add to Cart Form -->
                        <form method="POST" action="">
                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($row['name']); ?>">
                            <input type="hidden" name="product_price" value="<?php echo $row['price']; ?>">
                            <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($row['image1']); ?>">
                            <button type="submit" name="add_to_cart" class="add-to-cart-btn">Add to Cart</button>
                        </form>
                    </div>
                </div>
            <?php }
        } else { ?>
            <p class="text-center">No products found in this category.</p>
        <?php } ?>
    </div>
</div>
<!-- footer -->
<div class="container mt-5" id="contact__section">
    <div class="row">
        <div class="col-12 col-md-4">
            <img src="img/logo.png">
            <p>Complete your style with awesome clothes from us.</p>
            <p>Phone : +91 9720123467</p>
            <div class="row">
                <div class="col-3 col-md-2 social">
                  <a href="https://www.facebook.com/texura01" target="_blank">
                  <svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="52" height="52" rx="15" fill="#E9EFC3"/>
                    <path d="M31.5122 27.0625L32.2065 22.5381H27.8652V19.6021C27.8652 18.3643 28.4717 17.1577 30.416 17.1577H32.3896V13.3057C32.3896 13.3057 30.5986 13 28.8862 13C25.311 13 22.9741 15.167 22.9741 19.0898V22.5381H19V27.0625H22.9741V38H27.8652V27.0625H31.5122Z" fill="black"/>
                    </svg>
                    </a>
                </div>
                <div class="col-3 col-md-2 social">
                  <a href="https://www.instagram.com/_texura/" target="_blank">
                  <svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="52" height="52" rx="15" fill="#E9EFC3"/>
                    <path d="M25.946 20.3308C22.8406 20.3308 20.3357 22.8357 20.3357 25.9412C20.3357 29.0466 22.8406 31.5515 25.946 31.5515C29.0515 31.5515 31.5564 29.0466 31.5564 25.9412C31.5564 22.8357 29.0515 20.3308 25.946 20.3308ZM25.946 29.5886C23.9392 29.5886 22.2986 27.9529 22.2986 25.9412C22.2986 23.9294 23.9343 22.2937 25.946 22.2937C27.9578 22.2937 29.5935 23.9294 29.5935 25.9412C29.5935 27.9529 27.9529 29.5886 25.946 29.5886ZM33.0945 20.1013C33.0945 20.8289 32.5085 21.4099 31.7859 21.4099C31.0584 21.4099 30.4773 20.824 30.4773 20.1013C30.4773 19.3787 31.0632 18.7927 31.7859 18.7927C32.5085 18.7927 33.0945 19.3787 33.0945 20.1013ZM36.8103 21.4294C36.7273 19.6765 36.3269 18.1238 35.0427 16.8445C33.7634 15.5652 32.2107 15.1648 30.4578 15.0769C28.6511 14.9744 23.2361 14.9744 21.4294 15.0769C19.6814 15.1599 18.1287 15.5603 16.8445 16.8396C15.5603 18.1189 15.1648 19.6716 15.0769 21.4246C14.9744 23.2312 14.9744 28.6462 15.0769 30.4529C15.1599 32.2058 15.5603 33.7585 16.8445 35.0378C18.1287 36.3171 19.6765 36.7175 21.4294 36.8054C23.2361 36.908 28.6511 36.908 30.4578 36.8054C32.2107 36.7224 33.7634 36.322 35.0427 35.0378C36.322 33.7585 36.7224 32.2058 36.8103 30.4529C36.9128 28.6462 36.9128 23.2361 36.8103 21.4294ZM34.4763 32.3914C34.0955 33.3484 33.3582 34.0857 32.3962 34.4714C30.9558 35.0427 27.5378 34.9109 25.946 34.9109C24.3542 34.9109 20.9314 35.0378 19.4958 34.4714C18.5388 34.0906 17.8015 33.3533 17.4158 32.3914C16.8445 30.9509 16.9763 27.533 16.9763 25.9412C16.9763 24.3494 16.8494 20.9265 17.4158 19.491C17.7966 18.5339 18.5339 17.7966 19.4958 17.4109C20.9363 16.8396 24.3542 16.9714 25.946 16.9714C27.5378 16.9714 30.9607 16.8445 32.3962 17.4109C33.3533 17.7917 34.0906 18.5291 34.4763 19.491C35.0476 20.9314 34.9158 24.3494 34.9158 25.9412C34.9158 27.533 35.0476 30.9558 34.4763 32.3914Z" fill="black"/>
                    </svg>
                    </a>
                </div>
                <div class="col-3 col-md-2 social">
                  <a href="https://www.google.com/maps/place/World+Trade+Tower,+Makarba,+Ahmedabad,+Gujarat+380051/@22.9893467,72.4944617,709m/data=!3m1!1e3!4m15!1m8!3m7!1s0x395e9ac1e08efc69:0xfab7c177ee1d4fcb!2sWorld+Trade+Tower,+Makarba,+Ahmedabad,+Gujarat+380051!3b1!8m2!3d22.9894657!4d72.4970037!16s%2Fg%2F11qs97n7tb!3m5!1s0x395e9ac1e08efc69:0xfab7c177ee1d4fcb!8m2!3d22.9894657!4d72.4970037!16s%2Fg%2F11qs97n7tb?entry=ttu&g_ep=EgoyMDI1MDIwMi4wIKXMDSoASAFQAw%3D%3D" target="_blank">
                  <svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="52" height="52" rx="15" fill="#E9EFC3"/>
                    <path d="M26 36.75C25.7083 36.75 25.4583 36.675 25.25 36.525C25.0417 36.375 24.8854 36.1781 24.7813 35.9344C24.3854 34.8844 23.8854 33.9 23.2813 32.9812C22.6979 32.0625 21.875 30.9844 20.8125 29.7469C19.75 28.5094 18.8854 27.3281 18.2188 26.2031C17.5729 25.0781 17.25 23.7187 17.25 22.125C17.25 19.9312 18.0938 18.075 19.7813 16.5562C21.4896 15.0187 23.5625 14.25 26 14.25C28.4375 14.25 30.5 15.0187 32.1875 16.5562C33.8958 18.075 34.75 19.9312 34.75 22.125C34.75 23.8312 34.3854 25.2562 33.6562 26.4C32.9479 27.525 32.125 28.6406 31.1875 29.7469C30.0625 31.0969 29.2083 32.2219 28.625 33.1219C28.0625 34.0031 27.5938 34.9406 27.2188 35.9344C27.1146 36.1969 26.9479 36.4031 26.7188 36.5531C26.5104 36.6844 26.2708 36.75 26 36.75ZM26 32.7281C26.3542 32.0906 26.75 31.4625 27.1875 30.8438C27.6458 30.225 28.3125 29.4 29.1875 28.3687C30.0833 27.3187 30.8125 26.3531 31.375 25.4719C31.9583 24.5719 32.25 23.4562 32.25 22.125C32.25 20.5687 31.6354 19.2469 30.4063 18.1594C29.1979 17.0531 27.7292 16.5 26 16.5C24.2708 16.5 22.7917 17.0531 21.5625 18.1594C20.3542 19.2469 19.75 20.5687 19.75 22.125C19.75 23.4562 20.0313 24.5719 20.5938 25.4719C21.1771 26.3531 21.9167 27.3187 22.8125 28.3687C23.6875 29.4 24.3438 30.225 24.7813 30.8438C25.2396 31.4625 25.6458 32.0906 26 32.7281ZM26 24.9375C26.875 24.9375 27.6146 24.6656 28.2187 24.1219C28.8229 23.5781 29.125 22.9125 29.125 22.125C29.125 21.3375 28.8229 20.6719 28.2187 20.1281C27.6146 19.5844 26.875 19.3125 26 19.3125C25.125 19.3125 24.3854 19.5844 23.7813 20.1281C23.1771 20.6719 22.875 21.3375 22.875 22.125C22.875 22.9125 23.1771 23.5781 23.7813 24.1219C24.3854 24.6656 25.125 24.9375 26 24.9375Z" fill="#1D1B20"/>
                    </svg>   
                    </a>                 
                </div>
                <div class="col-3 col-md-2 social">
                  <a href="https://www.linkedin.com/company/104142694/admin/dashboard/" target="_blank">
                  <svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="52" height="52" rx="15" fill="#E9EFC3"/>
                    <path d="M20.875 21.3203V34H17.4883V21.3203H20.875ZM17.2773 18.0156C17.2773 17.5234 17.4492 17.1172 17.793 16.7969C18.1367 16.4766 18.5977 16.3164 19.1758 16.3164C19.7461 16.3164 20.2031 16.4766 20.5469 16.7969C20.8984 17.1172 21.0742 17.5234 21.0742 18.0156C21.0742 18.5078 20.8984 18.9141 20.5469 19.2344C20.2031 19.5547 19.7461 19.7148 19.1758 19.7148C18.5977 19.7148 18.1367 19.5547 17.793 19.2344C17.4492 18.9141 17.2773 18.5078 17.2773 18.0156ZM27.4488 24.0273V34H24.0738V21.3203H27.2378L27.4488 24.0273ZM26.9566 27.2148H26.0425C26.0425 26.2773 26.1636 25.4336 26.4058 24.6836C26.648 23.9258 26.9878 23.2812 27.4253 22.75C27.8628 22.2109 28.3823 21.8008 28.9839 21.5195C29.5933 21.2305 30.273 21.0859 31.023 21.0859C31.6167 21.0859 32.1597 21.1719 32.6519 21.3438C33.1441 21.5156 33.5659 21.7891 33.9175 22.1641C34.2769 22.5391 34.5503 23.0352 34.7378 23.6523C34.9331 24.2695 35.0308 25.0234 35.0308 25.9141V34H31.6323V25.9023C31.6323 25.3398 31.5542 24.9023 31.398 24.5898C31.2417 24.2773 31.0113 24.0586 30.7066 23.9336C30.4097 23.8008 30.0425 23.7344 29.605 23.7344C29.1519 23.7344 28.7573 23.8242 28.4214 24.0039C28.0933 24.1836 27.8198 24.4336 27.6011 24.7539C27.3902 25.0664 27.23 25.4336 27.1206 25.8555C27.0113 26.2773 26.9566 26.7305 26.9566 27.2148Z" fill="black"/>
                    </svg>
                    </a>
                </div>
            </div>

        </div>
        <div class="col-12 col-md-8">
            <div class="row justify-content-end">
                <div class="col-4 col-md-3 end">
                    <h5>Company</h5>
                    <a href="#" class="text-decoration-none">
                      <p>Organisation</p></a>
                    <a href="" class="text-decoration-none">
                      <p>Partners</p></a>
                </div>
                <div class="col-4 col-md-3 end">
                    <h5>Quick Link</h5>
                    <a href="" class="text-decoration-none">
                    <p>Photo Gallery</p>
                  </a>
                  <a href="" class="text-decoration-none">
                    <p>Our Team</p>
                    </a>
                </div>
                <div class="col-4 col-md-3 end">
                    <h5>Legal</h5>
                    <a href="" class="text-decoration-none">
                    <p>Winning Awards</p>
                    </a>
                    <a href="" class="text-decoration-none">
                    <p>Press</p>
                    </a>
                </div>                 
                  </div>
            </div>
        </div>
        <hr class="line">
        <p class="text-end">The VastrAcharya HQ,

World Trade Tower, SG Highway, 

Ahmedabad, Gujarat, 380051


        </p>
    </div>
 
  </div>
  <script src="js/sccript.js"></script>

</body>
</html>

<?php
$conn->close();
?>
