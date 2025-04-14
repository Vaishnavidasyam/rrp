<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "property";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query properties that are saved for display
$sql = "SELECT * FROM display WHERE saved_to_display = 1";
$result = $conn->query($sql);

$properties = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $properties[] = $row;
    }
}

$conn->close();

// Return properties as JSON
echo json_encode($properties);
?>
