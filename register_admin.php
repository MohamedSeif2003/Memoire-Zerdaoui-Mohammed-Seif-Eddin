<?php
// معلومات الاتصال بقاعدة البيانات
$host = 'localhost'; // اسم السيرفر
$dbname = 'alphapharm'; // اسم قاعدة البيانات
$username = 'root'; // اسم مستخدم قاعدة البيانات
$password = ''; // كلمة مرور قاعدة البيانات

// الاتصال بقاعدة البيانات
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password); //PDO is a a safe class for inhance security that creates an object    

    //هذا هو سلسلة الاتصال (DSN - Data Source Name)
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
/// php stops the try and goes to catch put the informations in new entity calls PDOExeption then save it in the $e
 catch (PDOException $e) {
    die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// بيانات المدير
$inputUsername = 'zerdaoui2003'; // اسم المستخدم
$inputPassword = 'helloworld'; // كلمة المرور

// تهشير كلمة المرور
$hashedPassword = password_hash($inputPassword, PASSWORD_DEFAULT);

// إدخال البيانات في قاعدة البيانات
$query = "INSERT INTO admin (username, password) VALUES (:username, :password)";
$stmt = $conn->prepare($query);
$stmt->bindParam(':username', $inputUsername);
$stmt->bindParam(':password', $hashedPassword);

if ($stmt->execute()) {
    echo "تم إضافة المدير بنجاح!";
} else {
    echo "فشل إضافة المدير.";
}
?>