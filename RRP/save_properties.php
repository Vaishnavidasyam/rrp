<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "property";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Getting data from the AJAX request
$data = json_decode(file_get_contents('php://input'), true);

$properties = $data['properties'];
$name = $data['name'];
$email = $data['email'];

foreach ($properties as $property) {
    $ownerName = $property['owner_name'];
    $contact = $property['contact'];
    $title = $property['title'];
    $type = $property['type'];
    $purpose = $property['purpose'];
    $price = $property['price'];
    $city = $property['city'];
    $state = $property['state'];
    $description = $property['description'];
    $googleMapsUrl = $property['google_maps_url'];
    $images = $property['images'];

    // Insert property into the display table
    $sql = "INSERT INTO display (owner_name, contact, title, type, purpose, price, city, state, description, google_maps_url, images, name, email)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssssssssss", // Adjusted for all placeholders
        $ownerName,
        $contact,
        $title,
        $type,
        $purpose,
        $price,
        $city,
        $state,
        $description,
        $googleMapsUrl,
        $images,
        $name,  // Seller's name
        $email  // Seller's email
    );

    if (!$stmt->execute()) {
        echo "Error saving property: " . $stmt->error;
    }
}

$conn->close();
echo "Properties saved successfully!";
?>
