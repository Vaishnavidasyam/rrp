<?php
$host = 'localhost';
$db = 'property';
$user = 'root';
$pass = ''; // Replace with your password

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$type = $_GET['type'] ?? ''; // Get property type from query

$sql = "SELECT * FROM sellers";
if (!empty($type)) {
    $sql .= " WHERE type = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $type);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

$properties = [];
while ($row = $result->fetch_assoc()) {
    $properties[] = $row;
}

header('Content-Type: application/json');
echo json_encode($properties);
?>
