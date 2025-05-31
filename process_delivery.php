<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$host = 'localhost';
$dbname = 'alphapharm';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Generate order ID
$order_id = 'ORD' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);

// Validate and prepare order data
try {
    // Verify pharmacist exists and is verified
    $stmt = $conn->prepare("SELECT 1 FROM pharmacist WHERE pharmacist_id = ? AND is_verified = 1");
    $stmt->execute([$_SESSION['pharmacist_id']]);
    if (!$stmt->fetch()) {
        die("Pharmacist not found or not verified");
    }

    // Verify product exists and get details
    $stmt = $conn->prepare("
        SELECT desig, types, sell, stock 
        FROM products 
        WHERE product_id = ?
    ");
    $stmt->execute([$_POST['product_id']]);
    $product = $stmt->fetch();
    
    if (!$product) {
        die("Product not found");
    }

    if ($product['stock'] < $_POST['quantity']) {
        die("Insufficient stock");
    }

    // Calculate prices
    $delivery_fee = 7.00;
    $subtotal = $product['sell'] * $_POST['quantity'];
    $total_price = $subtotal + $delivery_fee;

    // Prepare complete order data
    $order_data = [
        ':order_id' => $order_id,
        ':pharmacist_id' => $_SESSION['pharmacist_id'],
        ':product_id' => $_POST['product_id'],
        ':pharmacist_username' => $_SESSION['username'],
        ':product_designation' => $product['desig'],
        ':product_type' => $product['types'],
        ':product_price' => $product['sell'],
        ':quantity' => $_POST['quantity'],
        ':total_price' => $total_price,
        ':delivery_fee' => $delivery_fee,
        ':pharmacy_name' => $_POST['PharmacyName'],
        ':pharmacist_lastname' => $_POST['PharmacistLastName'],
        ':pharmacist_name' => $_POST['PharmacistName'],
        ':delivery_address' => $_POST['city'],
        ':postal_code' => $_POST['PostalCode'],
        ':phone' => $_POST['phone']
    ];

    // Begin transaction
    $conn->beginTransaction();

    // Insert order - NOW WITH ALL 16 COLUMNS
    $stmt = $conn->prepare("
        INSERT INTO orders (
            order_id, pharmacist_id, product_id, pharmacist_username,
            product_designation, product_type, product_price, quantity,
            total_price, delivery_fee, pharmacy_name, pharmacist_lastname,
            pharmacist_name, delivery_address, postal_code, phone
        ) VALUES (
            :order_id, :pharmacist_id, :product_id, :pharmacist_username,
            :product_designation, :product_type, :product_price, :quantity,
            :total_price, :delivery_fee, :pharmacy_name, :pharmacist_lastname,
            :pharmacist_name, :delivery_address, :postal_code, :phone
        )
    ");
    $stmt->execute($order_data);

    // Update stock
    $stmt = $conn->prepare("
        UPDATE products 
        SET stock = stock - :quantity 
        WHERE product_id = :product_id
    ");
    $stmt->execute([
        ':quantity' => $_POST['quantity'],
        ':product_id' => $_POST['product_id']
    ]);

    $conn->commit();

    header("Location: order_confirmation.php?order_id=$order_id");
    exit();

} catch (PDOException $e) {
    $conn->rollBack();
    die("Order processing failed: " . $e->getMessage());
}
?>


