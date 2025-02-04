<?php
require 'vendor/autoload.php';
use Razorpay\Api\Api;

session_start();
$conn = new mysqli("localhost", "root", "", "texura_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$keyId = "rzp_test_hwmWxxJ54Eqemg";
$keySecret = "your_key_secret";

$api = new Api($keyId, $keySecret);

if (!isset($_GET['payment_id'])) {
    die("❌ Payment failed. Please try again.");
}

$payment_id = $_GET['payment_id'];
$order_id = $_GET['order_id'];

$payment = $api->payment->fetch($payment_id);

if ($payment->status == "captured") {
    // Update Order Status in Database
    $stmt = $conn->prepare("UPDATE orders SET status = 'paid' WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();

    echo "✅ Payment successful! Your order is confirmed.";
} else {
    echo "❌ Payment failed. Please try again.";
}
?>
