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

// Fetch all pharmacists from the database
$sql = "SELECT * FROM pharmacist";
$stmt = $conn->prepare($sql);
$stmt->execute();
$pharmacists = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Material+Icons+Sharp" rel="stylesheet">   
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=search" />
    <link rel="stylesheet" href="admin_manage_ph.css">
    <title>Admin</title>
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
                <a href="admin_manage_ph.php" class="active">
                    <span class="material-icons-sharp">person_outline</span>
                    <h3>Customers</h3>
                </a>
                <a href="admin_manage_order.php">
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
            <div class="recent-users">
                <h2  style = "font-size : 2rem ; margin-bottom : 2rem;"  >PHARMACIST MANAGEMENT</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>License Number</th>
                            <th>License Certificate</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pharmacists as $pharmacist): ?>
                            <tr>
                                <td><?php echo $pharmacist['pharmacist_id']; ?></td>
                                <td><?php echo $pharmacist['username']; ?></td>
                                <td><?php echo $pharmacist['email']; ?></td>
                                <td><?php echo $pharmacist['license_number']; ?></td>
                                <td>
                                    <?php if ($pharmacist['license_certificate']): ?>
                                        <img src="<?php echo $pharmacist['license_certificate']; ?>" alt="License Certificate" width="100">
                                    <?php else: ?>
                                        No certificate uploaded.
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $pharmacist['is_verified'] ? 'Verified' : 'Pending'; ?></td>
                                <td>
                                    <?php if (!$pharmacist['is_verified']): ?>
                                        <!-- Accept Button -->
                                        <a href="verify_pharmacist.php?id=<?php echo $pharmacist['pharmacist_id']; ?>&action=accept" class="btn accept">Accept</a>
                                        <!-- Reject Button -->
                                        <a href="verify_pharmacist.php?id=<?php echo $pharmacist['pharmacist_id']; ?>&action=reject" class="btn reject">Reject</a>
                                    <?php else: ?>
                                        <!-- Cancel Button -->
                                        <a href="verify_pharmacist.php?id=<?php echo $pharmacist['pharmacist_id']; ?>&action=cancel" class="btn cancel">Cancel</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>

        <div class="Profilee"  style=" background-color : transparent;   ">

            <div class="info">
                <p>hey, <b id="adminUsername">Zerdaoui</b></p>
                <small class="text-muted">Admin</small>
            </div>

            <div class="profile-photo">
                <img src="pics/pharmacist.png" alt="">
            </div>
            
        </div>
    </div>
</body>
</html>