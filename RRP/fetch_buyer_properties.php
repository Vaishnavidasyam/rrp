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

// Fetch all properties from the display table
$properties = [];

$sql = "SELECT * FROM display ORDER BY created_at DESC"; // Optional: order by most recent
$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $properties[] = $row;
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($properties);

$conn->close();
?>
