<?php
session_start();
require('razorpay-php/Razorpay.php');
use Razorpay\Api\Api;

$RAZORPAY_KEY_ID = 'your_key_id';
$RAZORPAY_KEY_SECRET = 'your_key_secret';

// Check if Razorpay POST data is set
if (!isset($_POST['razorpay_payment_id']) || !isset($_POST['razorpay_order_id']) || !isset($_POST['razorpay_signature'])) {
    die('Payment details missing. Please try again.');
}

$payment_id = $_POST['razorpay_payment_id'];
$order_id = $_POST['razorpay_order_id'];
$signature = $_POST['razorpay_signature'];
$final_total = $_POST['final_total'];

try {
    // Initialize Razorpay API
    $api = new Api($RAZORPAY_KEY_ID, $RAZORPAY_KEY_SECRET);

    // Verify the payment signature
    $attributes = [
        'razorpay_payment_id' => $payment_id,
        'razorpay_order_id' => $order_id,
        'razorpay_signature' => $signature,
    ];
    $api->utility->verifyPaymentSignature($attributes);

    // Fetch payment details (optional)
    $payment = $api->payment->fetch($payment_id);

    // Check payment status
    if ($payment->status === 'captured') {
        // Payment is successful. Update the database and display success message.
        // Save the order details to the database
        $statement = $pdo->prepare("INSERT INTO tbl_orders (
            customer_id,
            order_total,
            payment_status,
            payment_method,
            razorpay_payment_id,
            razorpay_order_id
        ) VALUES (?, ?, ?, ?, ?, ?)");
        $statement->execute([
            $_SESSION['customer']['cust_id'], 
            $final_total, 
            'Paid', 
            'Razorpay', 
            $payment_id, 
            $order_id
        ]);

        // Clear the cart session
        unset($_SESSION['cart_p_id']);
        unset($_SESSION['cart_p_qty']);
        unset($_SESSION['cart_p_current_price']);
        unset($_SESSION['cart_p_name']);
        unset($_SESSION['cart_size_name']);
        unset($_SESSION['cart_color_name']);
        unset($_SESSION['cart_p_featured_photo']);

        // Redirect to success page
        header('Location: payment_success.php');
        exit;
    } else {
        // Payment failed
        header('Location: payment-failure.php');
        exit;
    }
} catch (Exception $e) {
    // Log the error for debugging
    error_log($e->getMessage());

    // Redirect to payment failure page
    header('Location: payment-failure.php');
    exit;
}
