<?php
// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "property"; // Replace with your actual database name

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the email from the URL parameters
$email = $_GET['email'];

// Query to fetch the user ID based on the email from the display table
$sql = "SELECT id, name FROM display WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$user = $result->fetch_assoc();

// Return the user data as JSON
echo json_encode($user);

// Close the connection
$conn->close();
?>
