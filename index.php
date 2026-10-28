<?php
session_start();

if (isset($_GET['type'])) {
    $_SESSION['order_type'] = $_GET['type'];
    $_SESSION['table_number'] = ($_GET['type'] === 'takeout') ? 'Waiting Area' : ('Table ' . ($_GET['table'] ?? '1'));
    header('Location: menu.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Restaurant Ordering System - Welcome</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f9; text-align: center; padding: 50px; }
        .container { background: white; max-width: 500px; margin: 0 auto; padding: 30px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .btn { display: inline-block; background: #ff5722; color: white; padding: 15px 30px; margin: 10px; text-decoration: none; border-radius: 5px; font-size: 18px; font-weight: bold; }
        .btn:hover { background: #e64a19; }
        .qr-sim { margin-top: 30px; font-size: 14px; color: #666; }
        .qr-links a { display: block; margin: 5px 0; color: #007bff; text-decoration: none; }
        .qr-links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to Our Restaurant</h1>
        <p>Please select your ordering option:</p>
        
        <a href="index.php?type=dinein&table=1" class="btn">Dine-In</a>
        <a href="index.php?type=takeout" class="btn" style="background: #4caf50;">Take-Out (Waiting Area)</a>

        <div class="qr-sim">
            <p><strong>Simulate QR Code Scans:</strong></p>
            <div class="qr-links">
                <a href="index.php?type=dinein&table=1">Scan QR: Table 1</a>
                <a href="index.php?type=dinein&table=2">Scan QR: Table 2</a>
                <a href="index.php?type=dinein&table=3">Scan QR: Table 3</a>
                <a href="index.php?type=dinein&table=4">Scan QR: Table 4</a>
                <a href="index.php?type=takeout" style="color: #4caf50; font-weight: bold;">Scan QR: Take-Out Waiting Area</a>
            </div>
        </div>
    </div>
</body>
</html>