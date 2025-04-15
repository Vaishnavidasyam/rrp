<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "property";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the 'category' from the query string, default to 'all'
$category = isset($_GET['category']) ? $_GET['category'] : 'all';

// Query properties that are saved for display
$sql = "SELECT * FROM display WHERE saved_to_display = 1";

// If a category is selected, filter by property type
if ($category != 'all') {
    $sql .= " AND type = '" . $conn->real_escape_string($category) . "'";
}

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
