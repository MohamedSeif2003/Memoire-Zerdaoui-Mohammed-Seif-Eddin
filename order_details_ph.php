<?php
// Database connection details
$host = 'localhost';
$dbname = 'alphapharm';
$username = 'root';
$password = '';

// Create a connection to the database
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    die("Order ID not specified");
}

$order_id = $_GET['order_id'];

// Fetch order details with pharmacist email
try {
    $stmt = $conn->prepare("
        SELECT 
            o.*,
            p.email AS pharmacist_email
        FROM orders o
        LEFT JOIN pharmacist p ON o.pharmacist_id = p.pharmacist_id
        WHERE o.order_id = :order_id
    ");
    $stmt->execute([':order_id' => $order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        die("Order not found");
    }
} catch (PDOException $e) {
    die("Error fetching order details: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons+Sharp" rel="stylesheet">   
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=search" />
    <link rel="stylesheet" href="admin_manage_ph.css">
    <title>Order Details - <?= htmlspecialchars($order['order_id']) ?></title>
    <style>
        /* General Styles */
        .container {
            display: flex;
            min-height: 100vh;
        }

        label, span {
            font-size: 1.2rem;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            margin: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .main-content header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .main-content header h1 {
            font-size: 28px;
            color: #2c3e50;
            margin: 0;
        }

        .main-content header .btn.back {
            background-color: #3498db;
            color: #fff;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
            transition: opacity 0.3s ease;
        }

        .main-content header .btn.back:hover {
            opacity: 0.9;
        }

        /* Order Details Section */
        .order-details {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 3em
        }

        .order-details .detail-item {
            margin-bottom: 15px;
        }

        .order-details .detail-item label {
            font-weight: bold;
            color: #2c3e50;
            display: inline-block;
            width: 150px;
        }

        .order-details .detail-item span {
            color: #333;
        }

        /* Profile Section */
        .Profile {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            background-color: #fff;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .Profile .info {
            text-align: right;
        }

        .Profile .info p {
            margin: 0;
            font-size: 14px;
        }

        .Profile .info small {
            color: #777;
        }

        .Profile .profile-photo img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <div class="container">
        <aside>
            <div class="top">
                <div class="logo">
                    <h2>ALPHA <span class="danger">PHARM</span></h2>
                </div>
                <div class="close" id="close-btn">
                    <span class="material-icons-sharp">close</span>
                </div>
            </div>

            <div class="sidebar">
                <a href="admin.html">
                    <span class="material-icons-sharp">grid_view</span>
                    <h3>Dashboard</h3>
                </a>
                <a href="admin_manage_ph.php">
                    <span class="material-icons-sharp">person_outline</span>
                    <h3>Customers</h3>
                </a>
                <a href="admin_manage_order.php" class="active">
                    <span class="material-icons-sharp">receipt_long</span>
                    <h3>Orders</h3>
                </a>
                <a href="#">
                    <span class="material-icons-sharp">inventory</span>
                    <h3>Products</h3>
                </a>
                <a href="#">
                    <span class="material-icons-sharp">settings</span>
                    <h3>Settings</h3>
                </a>
                <a href="index.html">
                    <span class="material-icons-sharp">logout</span>
                    <h3>Logout</h3>
                </a>
            </div>
        </aside>

        <div class="main-content">
            <header>
                <h1>Order Details - <?= htmlspecialchars($order['order_id']) ?></h1>
                <a href="admin_manage_order.php" class="btn back">Back to Orders</a>
            </header>

            <!-- Order Information Section -->
            <div class="order-details">
                <div class="detail-item">
                    <label>Order ID:</label>
                    <span><?= htmlspecialchars($order['order_id']) ?></span>
                </div>
                <div class="detail-item">
                    <label>Order Date:</label>
                    <span><?= date('Y-m-d H:i', strtotime($order['order_date'] ?? 'now')) ?></span>
                </div>
                <div class="detail-item">
                    <label>Pharmacist ID:</label>
                    <span><?= htmlspecialchars($order['pharmacist_id']) ?></span>
                </div>
                <div class="detail-item">
                    <label>Pharmacist Name:</label>
                    <span><?= htmlspecialchars($order['pharmacist_name'] . ' ' . $order['pharmacist_lastname']) ?></span>
                </div>
                <div class="detail-item">
                    <label>Email:</label>
                    <span><?= htmlspecialchars($order['pharmacist_email']) ?></span>
                </div>
                <div class="detail-item">
                    <label>Username:</label>
                    <span><?= htmlspecialchars($order['pharmacist_username']) ?></span>
                </div>
                <div class="detail-item">
                    <label>Pharmacy Name:</label>
                    <span><?= htmlspecialchars($order['pharmacy_name']) ?></span>
                </div>
                <div class="detail-item">
                    <label>Postal Code:</label>
                    <span><?= htmlspecialchars($order['postal_code']) ?></span>
                </div>
                <div class="detail-item">
                    <label>Phone Number:</label>
                    <span><?= htmlspecialchars($order['phone']) ?></span>
                </div>
            </div>

            <!-- Product Information Section -->
            <div class="product-info">
                <div class="order-details">
                    <div class="detail-item">
                        <label>Product ID:</label>
                        <span><?= htmlspecialchars($order['product_id']) ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Unit Price:</label>
                        <span><?= number_format($order['product_price'], 2) ?> DA</span>
                    </div>
                    <div class="detail-item">
                        <label>Delivery Fee:</label>
                        <span><?= number_format($order['delivery_fee'], 2) ?> DA</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="Profile">
            <div class="info">
                <p>Hey, <b id="adminUsername">Admin</b></p>
                <small class="text-muted">Administrator</small>
            </div>
            <div class="profile-photo">
                <img src="pics/pharmacist.png" alt="Admin Profile Photo">
            </div>
        </div>
    </div>
</body>
</html>