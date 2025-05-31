<?php
session_start();

// Database connection details
$host = 'localhost';
$dbname = 'alphapharm';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $input_username = trim($_POST['username']);
    $input_password = trim($_POST['password']);

    // Validate inputs
    if (empty($input_username) || empty($input_password)) {
        die("Both username and password are required.");
    }

    // Password verification function
    function verifyPassword($input_password, $hashed_password) {
        return password_verify($input_password, $hashed_password);
    }

    // Check admin table first
    $admin_query = "SELECT * FROM admin WHERE username = :username";
    $admin_stmt = $conn->prepare($admin_query);
    $admin_stmt->bindParam(':username', $input_username);
    $admin_stmt->execute();
    $admin = $admin_stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        if (verifyPassword($input_password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['username'] = $admin['username'];
            header("Location: admin.html");
            exit();
        } else {
            die("Invalid username or password.");
        }
    } else {
        // Check pharmacist table
        $pharmacist_query = "SELECT * FROM pharmacist WHERE username = :username";
        $pharmacist_stmt = $conn->prepare($pharmacist_query);
        $pharmacist_stmt->bindParam(':username', $input_username);
        $pharmacist_stmt->execute();
        $pharmacist = $pharmacist_stmt->fetch(PDO::FETCH_ASSOC);

        if ($pharmacist) {
            if (verifyPassword($input_password, $pharmacist['password'])) {
                if ($pharmacist['is_verified'] == 1) {
                    // Store ALL required pharmacist data in session
                    $_SESSION['pharmacist_id'] = $pharmacist['pharmacist_id'];
                    $_SESSION['username'] = $pharmacist['username'];
                    $_SESSION['email'] = $pharmacist['email'];
                    $_SESSION['license_number'] = $pharmacist['license_number'];
                    
                    header("Location: index.html");
                    exit();
                } else {
                    die("Your account is not yet verified. Please contact the administrator.");
                }
            } else {
                die("Invalid username or password.");
            }
        } else {
            die("Invalid username or password.");
        }
    }
    /// We save the admin and pharmacist data in the SESSION for the Direct acess to the system for the next time 
} else {
    header("Location: form.html");
    exit();
}

$conn = null;
?>