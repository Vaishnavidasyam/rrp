<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "property";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get email from query parameter
$email = $_GET['email'] ?? '';

$properties = [];

if (!empty($email)) {
    $stmt = $conn->prepare("SELECT * FROM display WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $properties[] = $row;
    }

    $stmt->close();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($properties);
$conn->close();
?>
