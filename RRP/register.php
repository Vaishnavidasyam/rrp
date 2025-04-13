<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DEBUG: You can comment this out later
echo "<h3>✅ register.php is running</h3>";

// DB connection setup
$host = "localhost";
$username = "root";
$password = "";
$database = "sign";

// Connect to DB
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

// Check for POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm-password'] ?? '';

    // Password match check
    if ($password !== $confirm_password) {
        die("❌ Passwords do not match.");
    }

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        die("❌ Email already registered.");
    }
    $check->close();

    // Password hashing
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into users table
    $stmt = $conn->prepare("INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $fullname, $email, $hashed_password);

    if ($stmt->execute()) {
        // ✅ Successful - redirect
        $nameParam = urlencode($fullname);
$emailParam = urlencode($email);
header("Location: clickhere.html?name=$nameParam&email=$emailParam");

        exit();
    } else {
        die("❌ Insert failed: " . $stmt->error);
    }

    $stmt->close();
} else {
    echo "⚠️ No POST data received.";
}

$conn->close();
?>
