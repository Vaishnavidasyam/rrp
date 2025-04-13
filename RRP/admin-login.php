<?php
// Start session (optional but recommended for login systems)
session_start();

// Database credentials
$servername = "localhost";
$username = "root"; // or your DB username
$password = "";     // or your DB password
$dbname = "sign";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect form data
$name = $_POST['name'];
$email = $_POST['email'];
$passwordInput = $_POST['password'];

// Prepare and execute query
$sql = "SELECT * FROM users WHERE fullname=? AND email=? AND password=SHA2(?, 256)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $name, $email, $passwordInput);
$stmt->execute();
$result = $stmt->get_result();

// Validate login
if ($result->num_rows === 1) {
    // Optional: Store user info in session
    $_SESSION['admin'] = $email;

    // Redirect to dashboard
    $nameParam = urlencode($name);
$emailParam = urlencode($email);
header("Location: clickhere.html?name=$nameParam&email=$emailParam");
exit();
} else {
    echo "<script>
            alert('Invalid credentials! Please try again.');
            window.location.href='admin-login.html';
          </script>";
}

$stmt->close();
$conn->close();
?>
