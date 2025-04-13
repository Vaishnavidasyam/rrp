<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "property";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$inputData = json_decode(file_get_contents('php://input'), true);

if (isset($inputData['properties']) && is_array($inputData['properties'])) {
    $properties = $inputData['properties'];

    // Loop through properties and insert them into the display table
    $stmt = $conn->prepare("INSERT INTO display (title, description, city, state, price, images, google_maps_url, owner_name, contact) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($properties as $property) {
        // Bind values to prepared statement
        $stmt->bind_param(
            "sssssssss", 
            $property['title'], 
            $property['description'], 
            $property['city'], 
            $property['state'], 
            $property['price'], 
            $property['images'], 
            $property['google_maps_url'], 
            $property['owner_name'], 
            $property['contact']
        );

        // Execute statement for each property
        $stmt->execute();
    }

    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid data received"]);
}

$conn->close();
?>
