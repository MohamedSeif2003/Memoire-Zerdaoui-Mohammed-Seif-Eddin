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
    die("<script>alert('Database connection failed: " . addslashes($e->getMessage()) . "');</script>");
}

// Handle order actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if required parameters exist
    if (!isset($_POST['order_id']) || !isset($_POST['pharmacist_email'])) {
        echo "<script>alert('Error: Missing required parameters!');</script>";
        exit;
    }

    $order_id = $_POST['order_id'];
    $pharmacist_email = $_POST['pharmacist_email'];
    
    // Fetch order details
    $stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo "<script>alert('Error: Order not found!');</script>";
        exit;
    }

    // Load PHPMailer
    $phpmailer_files = [
        'PHPMailer-master/src/Exception.php',
        'PHPMailer-master/src/PHPMailer.php',
        'PHPMailer-master/src/SMTP.php'
    ];
    
    foreach ($phpmailer_files as $file) {
        if (!file_exists($file)) {
            echo "<script>alert('Error: Required PHPMailer file missing - $file');</script>";
            exit;
        }
        require_once $file;
    }
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // SMTP Configuration with error handling
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'idkbro6711@gmail.com';
        $mail->Password = 'btvw ehlk rngz cqsi';
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->Timeout = 10; // Reduced timeout
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];
        
        $mail->setFrom('idkbro6711@gmail.com', 'Alpha Pharm');
        $mail->addAddress($pharmacist_email);
        $mail->isHTML(true);
        
        // Determine action
        if (isset($_POST['accept_order'])) {
            $mail->Subject = 'Order #' . $order_id . ' Approved - Alpha Pharm';
            $mail->Body = '
                <h2>Order Approved</h2>
                <p>Dear ' . htmlspecialchars($order['pharmacist_name']) . ',</p>
                <p>Your order <strong>#' . $order_id . '</strong> has been approved.</p>
                <h3>Order Details:</h3>
                <ul>
                    <li>Product: ' . htmlspecialchars($order['product_designation']) . '</li>
                    <li>Type: ' . htmlspecialchars($order['product_type']) . '</li>
                    <li>Quantity: ' . htmlspecialchars($order['quantity']) . '</li>
                    <li>Total Price: ' . number_format($order['total_price'], 2) . ' DA</li>
                    <li>Delivery Address: ' . htmlspecialchars($order['delivery_address']) . '</li>
                </ul>
                <p>Thank you for choosing Alpha Pharm!</p>
                <p>Best regards,<br>Alpha Pharm Team</p>
            ';
            $status_message = 'Order approved and notification sent!';
        }
        elseif (isset($_POST['reject_order'])) {
            $mail->Subject = 'Order #' . $order_id . ' Declined - Alpha Pharm';
            $mail->Body = '
                <h2>Order Declined</h2>
                <p>Dear ' . htmlspecialchars($order['pharmacist_name']) . ',</p>
                <p>We regret to inform you that your order <strong>#' . $order_id . '</strong> could not be processed.</p>
                <h3>Order Details:</h3>
                <ul>
                    <li>Product: ' . htmlspecialchars($order['product_designation']) . '</li>
                    <li>Type: ' . htmlspecialchars($order['product_type']) . '</li>
                    <li>Quantity: ' . htmlspecialchars($order['quantity']) . '</li>
                    <li>Total Price: ' . number_format($order['total_price'], 2) . ' DA</li>
                </ul>
                <p>Please contact support for more information.</p>
                <p>Best regards,<br>Alpha Pharm Team</p>
            ';
            $status_message = 'Order rejected and notification sent!';
        }
        else {
            echo "<script>alert('Error: No valid action specified!');</script>";
            exit;
        }

        $mail->send();
        echo "<script>alert('$status_message'); window.location.href='admin_manage_order.php';</script>";
        
    } catch (Exception $e) {
        $error_message = "Failed to send email. Error: " . $e->getMessage();
        echo "<script>alert('" . addslashes($error_message) . "');</script>";
    }
}

// Fetch all orders from the database with pharmacist info including email
$stmt = $conn->prepare("
    SELECT 
        o.order_id, 
        o.pharmacist_id, 
        o.product_id, 
        o.product_designation, 
        o.product_type, 
        o.quantity, 
        o.total_price, 
        o.delivery_address,
        o.pharmacist_username,
        o.pharmacist_name,
        o.pharmacist_lastname,
        o.pharmacy_name,
        o.postal_code,
        o.phone,
        p.email AS pharmacist_email
    FROM orders o
    LEFT JOIN pharmacist p ON o.pharmacist_id = p.pharmacist_id
    ORDER BY o.order_id DESC
");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons+Sharp" rel="stylesheet">   
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=search" />
    <link rel="stylesheet" href="admin_manage_order.css">
    <title>Admin - Manage Orders</title>
    <style>
        /* Ensure buttons maintain original styling */
        .btn {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            border: none;
            color: white;
            font-size: 14px;
        }
        .accept { background-color: #4CAF50; }
        .reject { background-color: #f44336; }
        .cancel { background-color: #ff9800; }
        .details { background-color: #2196F3; }
        
        .action-toggle {
            position: relative;
            display: inline-block;
        }
        .action-toggle input[type="checkbox"] {
            display: none;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
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
                    <span class="material-icons-sharp">settings</span>
                    <h3>Settings</h3>
                </a>
                <a href="index.html">
                    <span class="material-icons-sharp">logout</span>
                    <h3>Logout</h3>
                </a>
            </div>
        </aside>

        <main>
            <div class="recent-users"  >
                <h2 style = "font-size : 2rem ; margin-bottom : 2rem;" >ORDERS MANAGEMENT</h2>
                <table  >
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Pharmacist</th>
                            <th>Product</th>
                            <th>Designation</th>
                            <th>Type</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Actions</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['order_id']) ?></td>
                            <td><?= htmlspecialchars($order['pharmacist_name'].' '.$order['pharmacist_lastname']) ?></td>
                            <td><?= htmlspecialchars($order['product_id']) ?></td>
                            <td><?= htmlspecialchars($order['product_designation']) ?></td>
                            <td><?= htmlspecialchars($order['product_type']) ?></td>
                            <td><?= htmlspecialchars($order['quantity']) ?></td>
                            <td><?= number_format($order['total_price'], 2) ?> DA</td>
                            <td><?= htmlspecialchars($order['delivery_address']) ?></td>
                            <td>Pending</td>
                            <td>
                                <div class="action-toggle">
                                    <input type="checkbox" id="toggle-<?= $order['order_id'] ?>">
                                    <div class="action-buttons">
                                        <!-- Accept Form -->
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to accept this order?');">
                                            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                            <input type="hidden" name="pharmacist_email" value="<?= $order['pharmacist_email'] ?>">
                                            <button type="submit" name="accept_order" class="btn accept" style="all: unset; cursor: pointer; color:green; padding : 10px">Accept</button>
                                        </form>
                                        <!-- Reject Form -->
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to reject this order?');">
                                            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                            <input type="hidden" name="pharmacist_email" value="<?= $order['pharmacist_email'] ?>">
                                            <button type="submit" name="reject_order" class="btn reject" style="all: unset; cursor: pointer; color:red;padding : 10px " >Reject</button>
                                        </form>
                                        <span class="btn cancel" onclick="document.getElementById('toggle-<?= $order['order_id'] ?>').checked = false">Cancel</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="order_details_ph.php?order_id=<?= $order['order_id'] ?>" class="btn details">View Details</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>

        <div class="Profilee">
            <div class="info">
                <p>Hey, <b id="adminUsername">Zerdaoui</b></p>
                <small class="text-muted">Admin</small>
            </div>
            <div class="profile-photo">
                <img src="pics/pharmacist.png" alt="Admin Profile Photo">
            </div>
        </div>
    </div>
</body>
</html>