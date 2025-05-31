<?php
session_start();

// Validate and sanitize all incoming parameters
$productId = isset($_GET['product_id']) ? htmlspecialchars($_GET['product_id']) : '';
$quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;
$unitPrice = isset($_GET['unit_price']) ? floatval($_GET['unit_price']) : 0.00;
$totalPrice = isset($_GET['total_price']) ? floatval($_GET['total_price']) : 0.00;
$designation = isset($_GET['product_name']) ? htmlspecialchars($_GET['product_name']) : 'Product Name';
$type = isset($_GET['product_type']) ? htmlspecialchars($_GET['product_type']) : 'Not Specified';
$imagePath = isset($_GET['image_path']) ? htmlspecialchars($_GET['image_path']) : 'Medications/default-image.png';
$description = isset($_GET['description']) ? htmlspecialchars($_GET['description']) : 'No description available';


// Calculate prices
$deliveryFee = 7.00;
$totalOrderPrice = $totalPrice + $deliveryFee;


// Verify pharmacist is logged in
if (!isset($_SESSION['pharmacist_id']) || !isset($_SESSION['username'])) {
    header("Location: form.html");
    exit();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Confirmation</title>
    <link rel="stylesheet" href="delivery.css">
    <style>
        .product img {
            max-width: 100px;
            max-height: 100px;
            object-fit: contain;
        }
        .price p {
            font-weight: bold;
            color: #1CAE72;
        }
        .disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <a href="market.html">
                <div class="back">
                    <img src="pics/left.png" alt="Back arrow">
                    <p>Back to market</p>
                </div>
            </a>
            <p id="word1">ALPHA</p>
            <p id="word2">PHARM</p>
        </div>
    </div>

    <aside>
        <div class="right-section">
            <div class="all">
                <div class="all1">
                    <div class="row1">
                        <p>Items</p>
                        <p>Subtotal</p>
                    </div>
                    <hr>

                    <div class="product">
                        <img src="<?= $imagePath ?>" alt="<?= $designation ?>">
                        <div class="discript">
                            <p id="designation"><?= $designation ?></p>
                            <p id="dis"><?= $description ?></p>
                            <p id="type">Type: <?= $type ?></p>
                            <p id="quantity">Quantity: <span id="nbr"><?= $quantity ?></span></p>
                        </div>
                        <div class="price">
                            <p style="white-space:nowrap; position:relative; color:black ; right:8rem" id="price"><?= number_format($totalPrice, 2) ?> DA</p>
                        </div>
                    </div>
                </div>

                <div class="all2">
                    <div class="totalitem">
                        <p id="ti">Total Item</p>
                        <p id="price1"><?= number_format($totalPrice, 2) ?> DA</p>
                    </div>
                    <hr>

                    <div class="deliv">
                        <p id="deliv">Delivery</p>
                        <p id="price2"><?= number_format($deliveryFee, 2) ?> DA</p>
                    </div>
                    <hr>

                    <div class="order">
                        <p id="order">Total order</p>
                        <p id="price3"><?= number_format($totalOrderPrice, 2) ?> DA</p>
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <div class="container">
        <div class="step1">
            <div class="circle-title">
                <span class="number">1</span>
            </div>
            <p id="delivery">PERSONAL INFORMATIONS</p>
        </div>

        <div class="details">
            <p id="usernameAcc">Connected as <span id="user"><?= htmlspecialchars($_SESSION['username']) ?></span></p>
            <p id="diss">Not you? <a href="logout.php" id="logout">Logout</a></p>
        </div>

        <div class="delivery">
            <div class="step2">
                <div class="circle-title">
                    <span class="number">2</span>
                </div>
                <p id="delivery">DELIVERY</p>
            </div>

            <form action="process_delivery.php" method="POST" onsubmit="return validateForm()">
                <!-- Hidden fields for all product/order data -->
                <input type="hidden" name="product_id" value="<?= $productId ?>">
                <input type="hidden" name="product_name" value="<?= htmlspecialchars($designation) ?>">
                <input type="hidden" name="product_type" value="<?= htmlspecialchars($type) ?>">
                <input type="hidden" name="quantity" value="<?= $quantity ?>">
                <input type="hidden" name="unit_price" value="<?= $unitPrice ?>">
                <input type="hidden" name="total_price" value="<?= $totalOrderPrice ?>">
                <input type="hidden" name="image_path" value="<?= $imagePath ?>">
                <input type="hidden" name="description" value="<?= htmlspecialchars($description) ?>">
                <input type="hidden" name="pharmacist_id" value="<?= $_SESSION['pharmacist_id'] ?>">
                <input type="hidden" name="pharmacist_username" value="<?= $_SESSION['username'] ?>">

                <div class="pharm">
                    <div class="first">
                        <label for="PharmacyName">Pharmacy Name <span>*</span></label>
                        <input type="text" id="PharmacyName" name="PharmacyName" required pattern="[A-Za-z0-9\s]{2,}">
                    </div>

                    <div class="second">
                        <label for="PharmacistLastName">Last Name <span>*</span></label>
                        <input type="text" id="PharmacistLastName" name="PharmacistLastName" required pattern="[A-Za-z\s]{2,}">
                    </div>

                    <div class="third">
                        <label for="PharmacistName">Name <span>*</span></label>
                        <input type="text" id="PharmacistName" name="PharmacistName" required pattern="[A-Za-z\s]{2,}">
                    </div>
                </div>

                <div class="phone">
                    <label for="phone">Phone number <span>*</span></label>
                    <input type="tel" id="phone" name="phone" required pattern="[0-9]{10}" title="10 digit phone number">
                </div>

                <div class="pharm2">
                    <div class="city">
                        <label for="city">Postal Address (City, Carter) <span>*</span></label>
                        <input type="text" id="city" name="city" required minlength="5">
                    </div>
                    <div class="PostalCode">
                        <label for="PostalCode">Postal Code <span>*</span></label>
                        <input type="text" id="PostalCode" name="PostalCode" required pattern="[0-9]{5}" title="5 digit postal code">
                    </div>
                </div>

                <div class="btns">
                    <div class="saveBtn">
                        <img src="pics/smalll.png" alt="Save icon">
                        <input type="submit" value="Save">
                    </div>

                    <div class="cancelBtn">
                        <a href="market.html"  style="color: white; " >Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function validateForm() {
            const phone = document.getElementById('phone').value;
            const postalCode = document.getElementById('PostalCode').value;
            
            if (!/^\d{10}$/.test(phone)) {
                alert('Please enter a valid 10-digit phone number');
                return false;
            }
            
            if (!/^\d{5}$/.test(postalCode)) {
                alert('Please enter a valid 5-digit postal code');
                return false;
            }
            
            return true;
        }
    </script>
</body>
</html>






