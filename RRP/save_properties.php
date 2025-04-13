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

    // Prepare statements
    $stmt_update_sellers = $conn->prepare("UPDATE sellers SET title = ?, description = ?, city = ?, state = ?, price = ?, images = ?, google_maps_url = ?, owner_name = ?, contact = ? WHERE id = ?");
    $stmt_insert_display = $conn->prepare("INSERT INTO display (id, title, description, city, state, price, images, google_maps_url, owner_name, contact) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt_update_display = $conn->prepare("UPDATE display SET title = ?, description = ?, city = ?, state = ?, price = ?, images = ?, google_maps_url = ?, owner_name = ?, contact = ? WHERE id = ?");

    foreach ($properties as $property) {
        // Check if all required fields are present
        if (empty($property['id']) || empty($property['title']) || empty($property['description']) || empty($property['city']) || empty($property['state'])) {
            continue;  // Skip this property if it doesn't have required fields
        }

        // Update the sellers table
        $stmt_update_sellers->bind_param(
            "sssssssssi", 
            $property['title'], 
            $property['description'], 
            $property['city'], 
            $property['state'], 
            $property['price'], 
            $property['images'], 
            $property['google_maps_url'], 
            $property['owner_name'], 
            $property['contact'], 
            $property['id']
        );

        if (!$stmt_update_sellers->execute()) {
            echo json_encode(["status" => "error", "message" => "Failed to update sellers table"]);
            exit();
        }

        // Check if the property exists in the display table
        $check_sql = "SELECT id FROM display WHERE id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $property['id']);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            // If the property exists, update it in the display table
            $stmt_update_display->bind_param(
                "sssssssssi", 
                $property['title'], 
                $property['description'], 
                $property['city'], 
                $property['state'], 
                $property['price'], 
                $property['images'], 
                $property['google_maps_url'], 
                $property['owner_name'], 
                $property['contact'], 
                $property['id']
            );

            if (!$stmt_update_display->execute()) {
                echo json_encode(["status" => "error", "message" => "Failed to update display table"]);
                exit();
            }
        } else {
            // If the property does not exist, insert it into the display table
            $stmt_insert_display->bind_param(
                "isssssssss", 
                $property['id'], 
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

            if (!$stmt_insert_display->execute()) {
                echo json_encode(["status" => "error", "message" => "Failed to insert into display table"]);
                exit();
            }
        }
    }

    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid data received"]);
}

$conn->close();
?>
