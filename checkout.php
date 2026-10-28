<?php
session_start();
require_once 'db.php';

if (empty($_SESSION['cart'])) {
    header('Location: menu.php');
    exit;
}

$order_type = $_SESSION['order_type'] ?? 'dinein';
$table_location = $_SESSION['table_number'] ?? 'Not Specified';
$cart = $_SESSION['cart'];

// 1. Calculate Total and format order summary string
$total = 0;
$order_items = [];

foreach ($cart as $item) {
    $subtotal = $item['price'] * $item['quantity'];
    $total += $subtotal;
    $order_items[] = $item['quantity'] . "x " . $item['name'] . " (₱" . number_format($subtotal, 2) . ")";
}

$order_details_str = implode(", ", $order_items);

// 2. Insert into MySQL Database with error checking
$sql = "INSERT INTO orders (order_type, table_number, order_details, total_amount) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("sssd", $order_type, $table_location, $order_details_str, $total);

if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f9; padding: 50px; text-align: center; }
        .modal { background: white; max-width: 450px; margin: 0 auto; padding: 30px; border-radius: 8px; box-shadow: 0px 4px 15px rgba(0,0,0,0.2); }
        h2 { color: #28a745; margin-bottom: 10px; }
        p { font-size: 16px; color: #333; line-height: 1.5; }
        .btn { display: inline-block; background: #007bff; color: white; padding: 12px 25px; margin-top: 20px; text-decoration: none; border-radius: 5px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="modal">
        <h2>Order Placed Successfully!</h2>
        <p>Your order has been recorded. Please wait for your food in your designated <strong><?php echo htmlspecialchars($table_location); ?></strong>.</p>
        <a href="clear.php" class="btn">Start New Order</a>
    </div>
</body>
</html>