<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php'; // Ensure this path is correct

use Dotenv\Dotenv;
use Razorpay\Api\Api;  // Ensure the Razorpay API class is imported

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Fetch Razorpay keys from environment
$keyId = $_ENV['RZP_KEY_ID'];
$keySecret = $_ENV['RZP_KEY_SECRET'];

// Database Connection
$conn = new mysqli("localhost", "root", "", "texura_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];
$product_name = $_POST['product_name'];
$product_price = $_POST['product_price'];
$quantity = $_POST['quantity'];
$size = $_POST['size'];
$total_price = $_POST['total_price'];
$address = $_POST['address'];
$payment_method = $_POST['payment_method'];

// Set order status based on payment method
$status = ($payment_method == "COD") ? "pending" : "processing";

// Insert order details into database
$stmt = $conn->prepare("INSERT INTO orders (user_id, product_id, product_name, product_price, quantity, total_price, size, delivery_address, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iissdsssss", $user_id, $product_id, $product_name, $product_price, $quantity, $total_price, $size, $address, $payment_method, $status);
$stmt->execute();

// Get the order_id after inserting the order
$order_id = $stmt->insert_id;

if ($payment_method == "COD") {
    // If payment method is COD, show success message and exit
    echo "✅ Order placed successfully! Your order will be delivered soon.";
    exit();
} else {
    // Online Payment: Redirect to Razorpay

    // Ensure orderData is correctly populated
    $orderData = [
        'receipt'         => strval($order_id),  // Ensure this is a string
        'amount'          => $total_price * 100, // Convert to paise
        'currency'        => 'INR',
        'payment_capture' => 1
    ];

    // Create Razorpay order
    $api = new Api($keyId, $keySecret);
    $razorpayOrder = $api->order->create($orderData);

    // Save Razorpay order ID to session
    $_SESSION['razorpay_order_id'] = $razorpayOrder['id'];

    // Redirect to the payment page
    header("Location: payment.php?order_id=" . $_SESSION['razorpay_order_id']);
    exit();
}
?>
