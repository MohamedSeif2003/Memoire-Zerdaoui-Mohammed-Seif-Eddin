<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__.'/PHPMailer-master/src/Exception.php';
require __DIR__.'/PHPMailer-master/src/PHPMailer.php';
require __DIR__.'/PHPMailer-master/src/SMTP.php';

$test_recipient = 'your_email@example.com'; 
$mail = new PHPMailer\PHPMailer\PHPMailer(true);

try {
    // Server settings
    $mail->SMTPDebug = 4;
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'idkbro6711@gmail.com';
    $mail->Password = 'btvw ehlk rngz cqsi';
    
    // Try both SSL and TLS options
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS; // TLS
    $mail->Port = 587;
    
    // SSL Bypass (for testing only)
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    
    // Recipients
    $mail->setFrom('idkbro6711@gmail.com', 'Alpha Pharm');
    $mail->addAddress($test_recipient);
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'SMTP Test';
    $mail->Body = 'This is a test email from PHPMailer.';
    
    if ($mail->send()) {
        echo "Email sent successfully!";
    } else {
        throw new Exception("Send failed: " . $mail->ErrorInfo);
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    
    // Additional debugging
    if (strpos($e->getMessage(), 'certificate verify failed') !== false) {
        echo "<h3>SSL Certificate Solution:</h3>";
        echo "<p>1. Download <a href='https://curl.se/docs/caextract.html' target='_blank'>cacert.pem</a></p>";
        echo "<p>2. Save to: C:\\xampp\\php\\extras\\ssl\\cacert.pem</p>";
        echo "<p>3. Edit php.ini and add:</p>";
        echo "<pre>curl.cainfo = \"C:\\xampp\\php\\extras\\ssl\\cacert.pem\"\n";
        echo "openssl.cafile = \"C:\\xampp\\php\\extras\\ssl\\cacert.pem\"</pre>";
    }
}