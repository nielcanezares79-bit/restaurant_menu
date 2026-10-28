<?php
session_start();

// Handle item removal or updates
if (isset($_GET['remove'])) {
    $index = intval($_GET['remove']);
    unset($_SESSION['cart'][$index]);
    $_SESSION['cart'] = array_values($_SESSION['cart']); // re-index array
    header('Location: cart.php');
    exit;
}

$cart = $_SESSION['cart'] ?? [];
$total = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f9; padding: 20px; }
        .container { background: white; max-width: 600px; margin: 0 auto; padding: 25px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); }
        h2 { color: #333; border-bottom: 2px solid #ff5722; padding-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background: #f8f9fa; }
        .total-row { font-weight: bold; font-size: 18px; }
        .actions { margin-top: 20px; display: flex; justify-content: space-between; }
        .btn { padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; color: white; }
        .btn-menu { background: #6c757d; }
        .btn-checkout { background: #28a745; }
        .btn-danger { background: #dc3545; padding: 5px 10px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Review Your Order</h2>
        <p>Location: <strong><?php echo $_SESSION['table_number'] ?? 'Not Specified'; ?></strong></p>

        <?php if (empty($cart)): ?>
            <p>Your cart is empty.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($cart as $index => $item): 
                    $subtotal = $item['price'] * $item['quantity'];
                    $total += $subtotal;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td>₱<?php echo number_format($item['price'], 2); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>₱<?php echo number_format($subtotal, 2); ?></td>
                    <td><a href="cart.php?remove=<?php echo $index; ?>" class="btn btn-danger">Remove</a></td>
                </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="3" align="right">Total:</td>
                    <td colspan="2">₱<?php echo number_format($total, 2); ?></td>
                </tr>
            </table>
        <?php endif; ?>

        <div class="actions">
            <a href="menu.php" class="btn btn-menu">&larr; Add More Items</a>
            <?php if (!empty($cart)): ?>
                <a href="checkout.php" class="btn btn-checkout">Proceed to Checkout &rarr;</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>