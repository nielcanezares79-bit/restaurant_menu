<?php
session_start();

if (!isset($_SESSION['order_type'])) {
    header('Location: index.php');
    exit;
}

$order_type = $_SESSION['order_type'];
$table_info = $_SESSION['table_number'];

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $meal_type = $_POST['meal_type'] ?? '';
    $selected_meat = $_POST['meat'] ?? '';
    $selected_meat2 = $_POST['meat2'] ?? '';
    $selected_gulay = $_POST['gulay'] ?? '';
    $meal_qty = max(1, intval($_POST['meal_qty'] ?? 1));

    if (!empty($meal_type) && !empty($selected_meat)) {
        $price = 0;
        $item_name = "";

        if ($meal_type === 'budget') {
            $price = 50.00;
            $item_name = "Budget Meal ($selected_meat + $selected_gulay)";
        } elseif ($meal_type === 'double') {
            if (empty($selected_meat2)) {
                $selected_meat2 = $selected_meat;
            }
            $price = 80.00;
            $item_name = "Double Meat Meal ($selected_meat & $selected_meat2 - No Gulay)";
        } elseif ($meal_type === 'meat_only') {
            if ($order_type === 'takeout') {
                // Take-out meat only: customer chooses how much they are buying (min P50)
                $custom_amount = floatval($_POST['custom_amount'] ?? 50.00);
                if ($custom_amount < 50.00) {
                    $custom_amount = 50.00; // Enforce minimum P50
                }
                $price = $custom_amount;
                $item_name = "Take-Out Meat Only ($selected_meat - Worth ₱" . number_format($custom_amount, 2) . ")";
            } else {
                $price = 40.00;
                $item_name = "Meat Only ($selected_meat)";
            }
        }

        $_SESSION['cart'][] = [
            'name' => $item_name,
            'price' => $price,
            'quantity' => ($meal_type === 'meat_only' && $order_type === 'takeout') ? 1 : $meal_qty 
            // If custom amount is used for take-out meat only, quantity is 1 since price acts as total amount.
        ];
    }

    // Rice Add-ons
    $rice_1cup = intval($_POST['rice_1cup'] ?? 0);
    $rice_half = intval($_POST['rice_half'] ?? 0);
    if ($rice_1cup > 0) {
        $_SESSION['cart'][] = ['name' => 'Extra 1 Cup Rice', 'price' => 10.00, 'quantity' => $rice_1cup];
    }
    if ($rice_half > 0) {
        $_SESSION['cart'][] = ['name' => 'Extra Half Cup Rice', 'price' => 5.00, 'quantity' => $rice_half];
    }

    // Drinks Quantities
    $drinks = [
        'coke' => ['name' => 'Coke', 'price' => 15.00],
        'sprite' => ['name' => 'Sprite', 'price' => 15.00],
        'royal' => ['name' => 'Royal', 'price' => 15.00],
        'mountain_dew' => ['name' => 'Mountain Dew', 'price' => 18.00]
    ];

    foreach ($drinks as $key => $info) {
        $qty = intval($_POST['drink_' . $key] ?? 0);
        if ($qty > 0) {
            $_SESSION['cart'][] = [
                'name' => $info['name'],
                'price' => $info['price'],
                'quantity' => $qty
            ];
        }
    }

    header('Location: cart.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Visual Restaurant Menu</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f9; padding: 20px; color: #333; }
        .container { background: white; max-width: 900px; margin: 0 auto; padding: 30px; border-radius: 10px; box-shadow: 0px 4px 15px rgba(0,0,0,0.1); }
        h2 { border-bottom: 2px solid #ff5722; padding-bottom: 10px; color: #ff5722; }
        .badge { background: #ff5722; color: white; padding: 5px 10px; border-radius: 4px; font-size: 14px; }
        
        section { margin-top: 30px; background: #fafafa; padding: 20px; border-radius: 8px; border: 1px solid #e0e0e0; }
        section h3 { margin-top: 0; color: #444; }

        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; margin-top: 15px; }
        .card { background: white; border: 2px solid #ddd; border-radius: 8px; overflow: hidden; text-align: center; transition: 0.2s; cursor: pointer; padding-bottom: 10px; }
        .card img { width: 100%; height: 100px; object-fit: cover; }
        .card h4 { margin: 8px 0 4px 0; font-size: 15px; }
        .card input[type="radio"] { margin-top: 5px; transform: scale(1.2); }
        
        .card.selected { border-color: #ff5722; background: #fff8f6; }

        .form-group { margin-top: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        select, input[type="number"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; font-size: 15px; }

        .drink-row { display: flex; align-items: center; justify-content: space-between; background: white; padding: 10px 15px; margin-bottom: 10px; border-radius: 6px; border: 1px solid #ddd; }
        .drink-row img { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; margin-right: 15px; }
        .drink-info { display: flex; align-items: center; flex-grow: 1; }
        .drink-row input { width: 80px; text-align: center; }

        .btn-submit { display: block; width: 100%; background: #4caf50; color: white; padding: 15px; margin-top: 30px; border: none; border-radius: 6px; font-size: 18px; font-weight: bold; cursor: pointer; text-align: center; }
        .btn-submit:hover { background: #43a047; }
        .cart-link { display: block; text-align: center; margin-top: 15px; color: #007bff; text-decoration: none; font-weight: bold; }
        
        #secondMeatSection, #customAmountSection { display: none; background: #fff3e0; border-color: #ffb74d; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Restaurant Menu Order Form</h2>
        <p>Ordering Type: <span class="badge"><?php echo ucfirst($order_type); ?></span> | Location: <strong><?php echo $table_info; ?></strong></p>
        
        <form method="POST">
            
            <!-- Meal Package Choice -->
            <section>
                <h3>1. Choose Meal Package</h3>
                <div class="form-group">
                    <label for="meal_type">Package Type:</label>
                    <select name="meal_type" id="meal_type" required onchange="checkMealType()">
                        <option value="budget">Budget Meal - ₱50.00 (Includes Meat + Gulay)</option>
                        <option value="double">Double Meat - ₱80.00 (Includes 2 Meats - NO Gulay)</option>
                        <option value="meat_only">
                            <?php if ($order_type === 'takeout'): ?>
                                Take-Out Meat Only (Custom Amount - Min ₱50.00)
                            <?php else: ?>
                                Meat Only - ₱40.00
                            <?php endif; ?>
                        </option>
                    </select>
                </div>
            </section>

            <!-- Custom Amount Section (Only for Take-Out Meat Only) -->
            <section id="customAmountSection">
                <h3>Enter Amount Worth (₱)</h3>
                <label>How much worth of meat are you buying? (Minimum ₱50.00):</label>
                <input type="number" name="custom_amount" id="custom_amount" value="50" min="50" step="1">
            </section>

            <!-- First Meat Picture Grid -->
            <section>
                <h3 id="meat1_title">2. Choose Your Meat</h3>
                <div class="grid">
                    <?php 
                    $meats = ['dinuguan' => 'Dinuguan', 'adobo' => 'Adobo', 'sinam-it' => 'Sinam-it', 'dinakdakan' => 'Dinakdakan', 'sisig' => 'Sisig'];
                    foreach($meats as $key => $name): 
                    ?>
                    <label class="card" onclick="selectCard(this, 'meat')">
                        <img src="uploads/<?php echo $key; ?>.jpg" alt="<?php echo $name; ?>" onerror="this.src='https://via.placeholder.com/150x100?text=<?php echo $name; ?>'">
                        <h4><?php echo $name; ?></h4>
                        <input type="radio" name="meat" value="<?php echo $key; ?>" required>
                    </label>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Second Meat Picture Grid (Only shows for Double Meat) -->
            <section id="secondMeatSection">
                <h3>3. Choose Your Second Meat (Double Meat)</h3>
                <div class="grid">
                    <?php foreach($meats as $key => $name): ?>
                    <label class="card" onclick="selectCard(this, 'meat2')">
                        <img src="uploads/<?php echo $key; ?>.jpg" alt="<?php echo $name; ?>" onerror="this.src='https://via.placeholder.com/150x100?text=<?php echo $name; ?>'">
                        <h4><?php echo $name; ?></h4>
                        <input type="radio" name="meat2" value="<?php echo $key; ?>">
                    </label>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Gulay Picture Grid (Hidden for Double Meat) -->
            <section id="gulaySection">
                <h3>Choose Your Gulay / Vegetable</h3>
                <div class="grid">
                    <?php 
                    $gulays = ['sitaw_kalabasa' => 'Sitaw & Kalabasa', 'talong' => 'Talong', 'upo' => 'Upo'];
                    foreach($gulays as $key => $name): 
                    ?>
                    <label class="card" onclick="selectCard(this, 'gulay')">
                        <img src="uploads/<?php echo $key; ?>.jpg" alt="<?php echo $name; ?>" onerror="this.src='https://via.placeholder.com/150x100?text=<?php echo $name; ?>'">
                        <h4><?php echo $name; ?></h4>
                        <input type="radio" name="gulay" value="<?php echo $key; ?>">
                    </label>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Rice Add-ons -->
            <section>
                <h3>Add Rice</h3>
                <div style="display: flex; gap: 15px;">
                    <div style="flex:1;">
                        <label>Extra 1 Cup (₱10.00):</label>
                        <input type="number" name="rice_1cup" value="0" min="0">
                    </div>
                    <div style="flex:1;">
                        <label>Extra Half Cup (₱5.00):</label>
                        <input type="number" name="rice_half" value="0" min="0">
                    </div>
                </div>
            </section>

            <!-- Drinks Selection with Quantities -->
            <section>
                <h3>Choose Drinks (Mix & Match Quantities)</h3>
                <?php 
                $drinks_list = [
                    'coke' => ['name' => 'Coke', 'price' => 15],
                    'sprite' => ['name' => 'Sprite', 'price' => 15],
                    'royal' => ['name' => 'Royal', 'price' => 15],
                    'mountain_dew' => ['name' => 'Mountain Dew', 'price' => 18]
                ];
                foreach($drinks_list as $key => $d):
                ?>
                <div class="drink-row">
                    <div class="drink-info">
                        <img src="uploads/<?php echo $key; ?>.jpg" alt="<?php echo $d['name']; ?>" onerror="this.src='https://via.placeholder.com/50?text=Drink'">
                        <div>
                            <strong><?php echo $d['name']; ?></strong><br>
                            <small>₱<?php echo number_format($d['price'], 2); ?></small>
                        </div>
                    </div>
                    <div>
                        <input type="number" name="drink_<?php echo $key; ?>" value="0" min="0">
                    </div>
                </div>
                <?php endforeach; ?>
            </section>

            <!-- Meal Quantity (Hidden if Take-Out Meat Only custom amount is chosen) -->
            <section id="qtySection">
                <h3>Meal Quantity</h3>
                <input type="number" name="meal_qty" value="1" min="1" required>
            </section>

            <button type="submit" class="btn-submit">Add Order to Cart</button>
        </form>

        <a href="cart.php" class="cart-link">&rarr; View Cart & Checkout</a>
    </div>

    <script>
        const isTakeout = "<?php echo $order_type; ?>" === "takeout";

        function selectCard(cardElement, groupName) {
            const parentSection = cardElement.closest('section');
            parentSection.querySelectorAll('.card').forEach(c => c.classList.remove('selected'));
            
            cardElement.classList.add('selected');
            const radio = cardElement.querySelector('input[type="radio"]');
            radio.checked = true;
        }

        function checkMealType() {
            const mealType = document.getElementById('meal_type').value;
            const secondMeatSection = document.getElementById('secondMeatSection');
            const gulaySection = document.getElementById('gulaySection');
            const customAmountSection = document.getElementById('customAmountSection');
            const qtySection = document.getElementById('qtySection');
            const meat1Title = document.getElementById('meat1_title');
            
            const gulayRadios = document.querySelectorAll('input[name="gulay"]');
            const meat2Radios = document.querySelectorAll('input[name="meat2"]');

            // Reset visibility
            secondMeatSection.style.display = 'none';
            gulaySection.style.display = 'block';
            customAmountSection.style.display = 'none';
            qtySection.style.display = 'block';
            meat1Title.innerText = "2. Choose Your Meat";

            meat2Radios.forEach(r => { r.required = false; r.checked = false; });
            gulayRadios.forEach(r => r.required = true);
            document.querySelectorAll('#secondMeatSection .card').forEach(c => c.classList.remove('selected'));

            if (mealType === 'double') {
                secondMeatSection.style.display = 'block';
                gulaySection.style.display = 'none';
                meat1Title.innerText = "2. Choose Your First Meat";
                meat2Radios.forEach(r => r.required = true);
                gulayRadios.forEach(r => { r.required = false; r.checked = false; });
                document.querySelectorAll('#gulaySection .card').forEach(c => c.classList.remove('selected'));
            } else if (mealType === 'meat_only' && isTakeout) {
                customAmountSection.style.display = 'block';
                gulaySection.style.display = 'none';
                qtySection.style.display = 'none'; // Quantity isn't needed since price handles the peso amount
                gulayRadios.forEach(r => { r.required = false; r.checked = false; });
                document.querySelectorAll('#gulaySection .card').forEach(c => c.classList.remove('selected'));
            }
        }
    </script>
</body>
</html>