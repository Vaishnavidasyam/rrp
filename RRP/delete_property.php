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
$name = $_GET['name'] ?? '';
$email = $_GET['email'] ?? '';

if (!$propertyId || !$name || !$email) {
    echo "Invalid property ID or missing parameters.";
    exit();
}

// Delete property from sellers table
$sql = "DELETE FROM sellers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $propertyId);

if ($stmt->execute()) {
    // Redirect back to the same page (display_properties.php) with unencoded URL parameters
    header("Location: display_properties.php?name=$name&email=$email");
    exit();
} else {
    echo "Error: " . $stmt->error;
}

$conn->close();
?>
