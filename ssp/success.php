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

$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';

if (empty($user_id)) {
    die("Invalid request.");
}

$stmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    die("User not found.");
}

$stmt->bind_result($fetched_user_id);
$stmt->fetch();
$stmt->close();

if (isset($_POST['update_password'])) {
    $new_password = $_POST['new-password'];
    $confirm_password = $_POST['confirm-password'];

    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif (!preg_match("/[A-Z]/", $new_password) || !preg_match("/[a-z]/", $new_password) || !preg_match("/[0-9]/", $new_password) || !preg_match("/[\W_]/", $new_password)) {
        $error = "Password must contain at least one uppercase letter, one lowercase letter, one digit, and one special character.";
    } else {
        $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
        $stmt->bind_param("ss", $password_hash, $user_id);

        if ($stmt->execute()) {
            header("Location: login.html");
            exit();
        } else {
            $error = "Error updating password. Please try again.";
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Password</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="header">
        <div class="logo-container">
            <img src="https://www.freshersnow.com/wp-content/uploads/2021/10/Prime-Ministers-Scholarship-Scheme.png" alt="Scholarship Logo" class="logo">
        </div>
        <div class="header-content">
            <h1>STUDENT SCHOLARSHIP PORTAL</h1>
        </div>
    </div>
    <div class="container">
        <h2>Update Password</h2>
        <p>Your User ID: <strong><?php echo htmlspecialchars($fetched_user_id); ?></strong></p>
        <form id="update-password-form" method="POST" action="success.php?user_id=<?php echo urlencode($user_id); ?>">
            <label for="new-password">New Password *</label>
            <input type="password" name="new-password" id="new-password" placeholder="Enter new password" required>

            <label for="confirm-password">Confirm Password *</label>
            <input type="password" name="confirm-password" id="confirm-password" placeholder="Re-enter new password" required>

            <div class="error">
                <?php if (isset($error)) echo htmlspecialchars($error); ?>
            </div>

            <button type="submit" name="update_password">Save</button>
        </form>
    </div>
</body>
</html>
