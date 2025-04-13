<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "property";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$data = json_decode(file_get_contents('php://input'), true);
$properties = $data['properties'] ?? [];
$name = $data['name'] ?? '';
$email = $data['email'] ?? '';

if (empty($properties) || !$name || !$email) {
    http_response_code(400);
    echo "Invalid input.";
    exit();
}

foreach ($properties as $property) {
    $id = $property['id'];
    $images = isset($property['images']) ? implode(',', explode(',', $property['images'])) : '';

    // Check if property exists in display table
    $sql_check = "SELECT id FROM display WHERE id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows === 0) {
        // INSERT
        $sql_insert = "INSERT INTO display (id, title, description, city, state, price, images, google_maps_url, name, email, created_at)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
        $stmt = $conn->prepare($sql_insert);
        $stmt->bind_param("isssssssss",
            $property['id'],
            $property['title'],
            $property['description'],
            $property['city'],
            $property['state'],
            $property['price'],
            $images,
            $property['google_maps_url'],
            $name,
            $email
        );
    } else {
        // UPDATE
        $sql_update = "UPDATE display SET title = ?, description = ?, city = ?, state = ?, price = ?, images = ?, google_maps_url = ?, name = ?, email = ? WHERE id = ?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("sssssssssi",
            $property['title'],
            $property['description'],
            $property['city'],
            $property['state'],
            $property['price'],
            $images,
            $property['google_maps_url'],
            $name,
            $email,
            $property['id']
        );
    }

    $stmt->execute();
}

$conn->close();
echo "Properties have been successfully saved/updated in the display table!";
?>
