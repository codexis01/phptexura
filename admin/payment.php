<?php
session_start();
$order_id = $_GET['order_id'];
$total_price = $_SESSION['total_price'];  // Retrieve total price from session
?>

<!DOCTYPE html>
<html>
<head>
    <title>Complete Payment</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>
<script>
    var options = {
        "key": "rzp_test_hwmWxxJ54Eqemg",
        "amount": "<?php echo $total_price * 100; ?>",
        "currency": "INR",
        "order_id": "<?php echo $order_id; ?>",
        "name": "Texura Store",
        "description": "Order Payment",
        "handler": function(response) {
            window.location.href = "payment_success.php?payment_id=" + response.razorpay_payment_id + "&order_id=" + "<?php echo $order_id; ?>";
        },
        "theme": {
            "color": "#2874f0"
        }
    };
    var rzp1 = new Razorpay(options);
    rzp1.open();
</script>
</body>
</html>
