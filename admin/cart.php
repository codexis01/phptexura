<?php
session_start();

// Ensure the user is logged in
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

$user_id = $_SESSION['user_id'];

// Handle item removal from the cart
if (isset($_GET['remove_id'])) {
    $remove_id = $_GET['remove_id'];

    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $remove_id, $user_id);
    $stmt->execute();
    
    // Redirect to avoid "Document Expired" issue
    header("Location: cart.php");
    exit();
}

// Handle cart update (quantity & size)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_cart'])) {
    $cart_id = $_POST['cart_id'];
    $new_quantity = (int)$_POST['quantity'];
    $new_size = $_POST['size'] ?? 'M';

    $stmt = $conn->prepare("UPDATE cart SET quantity = ?, size = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("isii", $new_quantity, $new_size, $cart_id, $user_id);
    $stmt->execute();

    // Redirect to avoid form resubmission issue
    header("Location: cart.php");
    exit();
}

// Fetch cart items only for the logged-in user
$sql = "SELECT * FROM cart WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Texura - Your Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const alertBox = document.getElementById("updateMessage");
            if (alertBox) {
                setTimeout(() => {
                    alertBox.style.display = "none";
                }, 3000);
            }
        });
    </script>
</head>
<body>

<!-- Navbar -->
<div class="banner">
    <nav class="navbar navbar-expand-lg py-3">
        <div class="container">
            <a href="index.php"><img src="img/logo.png" alt="Logo" /></a>
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
                    <li class="nav-item"><a class="nav-link" href="">Welcome, <?php echo $_SESSION['username']; ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
</div>

<!-- Cart Section -->
<div class="container my-5">
    <h1>Your Cart</h1>

    <?php if ($result->num_rows === 0) { ?>
        <p>Your cart is empty.</p>
    <?php } else { ?>
        <ul class="list-group">
            <?php $total_price = 0;
            while ($item = $result->fetch_assoc()) {
                $item_price = (float)$item['product_price'];
                $total_price += $item_price * (int)$item['quantity']; ?>
                
                <li class="list-group-item d-flex align-items-center">
                    <img src="<?php echo htmlspecialchars($item['product_image']); ?>" width="100" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                    <div class="ms-3">
                        <h5><?php echo htmlspecialchars($item['product_name']); ?></h5>
                        <p>Price: ₹<?php echo number_format($item_price, 2); ?></p>
                        
                        <!-- Update Quantity & Size -->
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                            <input type="number" name="quantity" value="<?php echo (int)$item['quantity']; ?>" min="1" class="form-control d-inline w-25">
                            <select name="size" class="form-control d-inline w-25">
                                <option value="Select" <?php echo ($item['size'] == 'Select' || $item['size'] == '') ? 'selected' : ''; ?>>Select Size</option>
                                <option value="S" <?php echo ($item['size'] == 'S') ? 'selected' : ''; ?>>S</option>
                                <option value="M" <?php echo ($item['size'] == 'M') ? 'selected' : ''; ?>>M</option>
                                <option value="L" <?php echo ($item['size'] == 'L') ? 'selected' : ''; ?>>L</option>
                                <option value="XL" <?php echo ($item['size'] == 'XL') ? 'selected' : ''; ?>>XL</option>
                                <option value="XXL" <?php echo ($item['size'] == 'XXL') ? 'selected' : ''; ?>>XXL</option>
                            </select>
                            <button type="submit" name="update_cart" class="btn btn-primary">Update</button>
                        </form>

                        <!-- Remove Item -->
                        <a href="cart.php?remove_id=<?php echo $item['id']; ?>" class="btn btn-danger">Remove</a>
                    </div>
                </li>
            <?php } ?>
        </ul>

        <div class="mt-4">
            <h4>Total Price: ₹<?php echo number_format((float)$total_price, 2); ?></h4>
            <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
        </div>
    <?php } ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
