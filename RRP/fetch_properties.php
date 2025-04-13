<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "property");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

$sql = "SELECT * FROM display ORDER BY id DESC";
$result = $conn->query($sql);

$properties = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $properties[] = $row;
    }
}

echo json_encode($properties);
$conn->close();
?>
