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

// Function to generate pharmacist_id
function generatePharmacistId($conn) {
    $prefix = "PH"; // Prefix for the ID
    $sql = "SELECT COUNT(*) as count FROM pharmacist"; // Count existing pharmacists
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = $result['count'] + 1; // Increment count for the new pharmacist
    return $prefix . $count; // Format: PH + count (e.g., PH123)
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $license_number = $_POST['license_number'];

    // Handle file upload (license certificate)
    $license_certificate = '';
    if (isset($_FILES['license_certificate']) && $_FILES['license_certificate']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "Certifications/"; // Directory to store uploaded files
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true); // Create the directory if it doesn't exist
        }
        $target_file = $target_dir . basename($_FILES['license_certificate']['name']);
        if (move_uploaded_file($_FILES['license_certificate']['tmp_name'], $target_file)) {
            $license_certificate = $target_file; // Save the file path
        } else {
            die("Error uploading file.");
        }
    }
     else {
        die("License certificate is required.");
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Generate pharmacist_id
    $pharmacist_id = generatePharmacistId($conn);

    // Insert data into the pharmacist table
    try {
        $sql = "INSERT INTO pharmacist (pharmacist_id, username, email, password, license_number, license_certificate, is_verified)
                VALUES (:pharmacist_id, :username, :email, :password, :license_number, :license_certificate, 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':pharmacist_id', $pharmacist_id);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':license_number', $license_number);
        $stmt->bindParam(':license_certificate', $license_certificate);
        $stmt->execute();

        // Load PHPMailer
        require 'PHPMailer-master/src/Exception.php';
        require 'PHPMailer-master/src/PHPMailer.php';
        require 'PHPMailer-master/src/SMTP.php';

        // Create a new PHPMailer instance
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        // PHPMailer : Class 
        //mail : object 
        //host : property 
        try {
            // Server settings
            $mail->isSMTP(); // Use SMTP
            $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
            $mail->SMTPAuth = true; // Enable SMTP authentication
            $mail->Username = 'idkbro6711@gmail.com'; // Your Gmail address    
            $mail->Password = 'btvw ehlk rngz cqsi'; // Your Gmail app password
            $mail->SMTPSecure = 'tls'; // Enable TLS encryption
            $mail->Port = 587; // TCP port to connect to

            // Disable SSL certificate verification (for testing only)
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ];

            // Recipients
            $mail->setFrom('localbussiness173@gmail.com', 'AlphaPharm Team'); // Sender email and name
            $mail->addAddress($email); // Add pharmacist's email as recipient

            // Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = 'Registration Successful - AlphaPharm';
            $mail->Body = "
                <p>Dear $username,</p>
                <p>Thank you for registering with AlphaPharm! Your account is pending verification. You will be notified once your account is approved.</p>
                <p>Best regards,<br>The AlphaPharm Team</p>
            ";
            $mail->AltBody = "Dear $username,\n\nThank you for registering with AlphaPharm! Your account is pending verification. You will be notified once your account is approved.\n\nBest regards,\nThe AlphaPharm Team";

            // Send the email
            $mail->send();
            echo "Registration successful! Your account is pending verification. A confirmation email has been sent to $email.";
        } catch (Exception $e) {
            die("Failed to send email. Error: " . $mail->ErrorInfo);
        }
    } 
    catch (PDOException $e) {
        die("Error inserting data: " . $e->getMessage());
    }
} else {
    die("Invalid request method.");
}
?>