<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "property";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$propertyId = $_GET['id'] ?? null;

if (!$propertyId) {
    echo "Invalid property ID.";
    exit();
}

$sql = "DELETE FROM sellers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $propertyId);

if ($stmt->execute()) {
    // Redirect to the properties page after successful deletion
    header("Location: display_properties.php?name=" . $_GET['name']);
    exit();
} else {
    echo "Error: " . $stmt->error;
}
