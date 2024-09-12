<?php
session_start();
$host = "localhost";  
$db_username = "root"; 
$db_password = ""; 
$db_name = "scholarship_portal";

$conn = new mysqli($host, $db_username, $db_password, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";
function generateUserId($conn) {
    $year = date('Y');
    $month = date('m');

    do {
        $randomDigits = mt_rand(1000, 9999);
        $userId = $year . $month . $randomDigits;
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $stmt->store_result();
    } while ($stmt->num_rows > 0);

    return $userId;
}

if (isset($_POST['register'])) {
    $first_name = $_POST['register-first-name'];
    $last_name = $_POST['register-last-name'];
    $email = $_POST['register-email'];
    $aadhaar_number = $_POST['register-aadhaar'];
    $phone_number = $_POST['register-phone'];
    $gender = $_POST['register-gender'];
    $password = 'ssp@123';

    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? OR aadhaar_number = ?");
    $stmt->bind_param("ss", $email, $aadhaar_number);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $error = "User with this email or Aadhaar number already exists.";
    } else {
        $user_id = generateUserId($conn);
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("INSERT INTO users (user_id, first_name, last_name, email, aadhaar_number, phone_number, gender, password_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $user_id, $first_name, $last_name, $email, $aadhaar_number, $phone_number, $gender, $password_hash);
        
        if ($stmt->execute()) {
            header("Location: success.php?user_id=" . urlencode($user_id));
            exit();
        } else {
            $error = "Error during registration. Please try again.";
        }
    }
    $stmt->close();
}

$conn->close();
?>
