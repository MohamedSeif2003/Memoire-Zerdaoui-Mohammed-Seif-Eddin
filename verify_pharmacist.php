<?php
// Database connection details
$host = 'localhost'; // Replace with your database host
$dbname = 'alphapharm'; // Replace with your database name
$username = 'root'; // Replace with your database username
$password = ''; // Replace with your database password

// Create a connection to the database
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if the pharmacist ID and action are provided in the URL
if (isset($_GET['id']) && isset($_GET['action'])) {
    $pharmacist_id = $_GET['id']; // Get the pharmacist ID
    $action = $_GET['action']; // Get the action (accept, reject, cancel)

    // Validate the action
    if (!in_array($action, ['accept', 'reject', 'cancel'])) {
        die("Invalid action.");
    }

    // Fetch the pharmacist's details
    $sql = "SELECT * FROM pharmacist WHERE pharmacist_id = :pharmacist_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':pharmacist_id', $pharmacist_id);
    $stmt->execute();
    $pharmacist = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pharmacist) {
        die("Pharmacist not found.");
    }

    // Perform the action
    switch ($action) {
        case 'accept':
            // Mark the pharmacist as verified
            $sql = "UPDATE pharmacist SET is_verified = 1 WHERE pharmacist_id = :pharmacist_id";
            $message = "Pharmacist account has been verified successfully.";
            break;

        case 'reject':
            // Delete the pharmacist's record
            $sql = "DELETE FROM pharmacist WHERE pharmacist_id = :pharmacist_id";
            $message = "Pharmacist account has been rejected and deleted.";
            break;

        case 'cancel':
            // Mark the pharmacist as unverified
            $sql = "UPDATE pharmacist SET is_verified = 0 WHERE pharmacist_id = :pharmacist_id";
            $message = "Pharmacist account verification has been canceled.";
            break;

        default:
            die("Invalid action.");
    }

    // Execute the query
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':pharmacist_id', $pharmacist_id);
        $stmt->execute();

        // Send an email notification to the pharmacist
        if ($action === 'accept' || $action === 'cancel') {
            // Load PHPMailer
            require 'PHPMailer-master/src/Exception.php';
            require 'PHPMailer-master/src/PHPMailer.php';
            require 'PHPMailer-master/src/SMTP.php';

            // Create a new PHPMailer instance
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);

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
                $mail->addAddress($pharmacist['email']); // Add pharmacist's email as recipient

                // Content
                $mail->isHTML(true); // Set email format to HTML
                $mail->Subject = 'Account Verification Update - AlphaPharm';
                $mail->Body = "
                    <p>Dear {$pharmacist['username']},</p>
                    <p>Your account verification has been successfully approved . You can now access all available features.</p>
                    <p>Best regards,<br>The AlphaPharm Team</p>
                ";
                $mail->AltBody = "Dear {$pharmacist['username']},\n\nYour account verification has been successfully approved . You can now access all available features.\n\nBest regards,\nThe AlphaPharm Team";

                // Send the email
                $mail->send();
            } catch (Exception $e) {
                die("Failed to send email. Error: " . $mail->ErrorInfo);
            }
        }

        // Redirect back to the admin manage page with a success message
        header("Location: admin_manage_ph.php?message=" . urlencode($message));
        exit();
    } catch (PDOException $e) {
        die("Error performing action: " . $e->getMessage());
    }
} else {
    die("Pharmacist ID and action are required.");
}
?>