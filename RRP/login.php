<?php
// Start the session
session_start();

// DB connection
$conn = new mysqli("localhost", "root", "", "sign");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get input and sanitize
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$password = $_POST['password'];

// Check if email is valid
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Invalid email format.";
    exit();
}

// Fetch user by email
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Verify password
    if (password_verify($password, $user['password'])) {
        // Store session data
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];

        // Redirect to clickhere.html with name and email as query params
        $nameParam = $user['fullname'];
$emailParam = $user['email'];
header("Location: clickhere.html?name=$nameParam&email=$emailParam");

        exit();
    } else {
        echo "Incorrect password.";
    }
} else {
    echo "User not found.";
}

$stmt->close();
$conn->close();
?>
