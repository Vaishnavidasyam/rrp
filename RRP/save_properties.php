<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "property";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';

if (!$name || !$email) {
    echo "Invalid name or email.";
    exit();
}

// Step 1: Fetch current properties from sellers table
$sql = "SELECT * FROM sellers WHERE name = ? AND email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $name, $email);
$stmt->execute();
$result = $stmt->get_result();

$properties = [];
while ($row = $result->fetch_assoc()) {
    $properties[] = $row;
}
$stmt->close();

// Step 2: Get all existing display property IDs for this user
$existingDisplayIds = [];
$sql = "SELECT id FROM display WHERE name = ? AND email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $name, $email);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $existingDisplayIds[] = $row['id'];
}
$stmt->close();

$currentIds = [];

foreach ($properties as $property) {
    $currentIds[] = $property['id'];

    // Check if property already exists in display
    $sql = "SELECT id FROM display WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $property['id']);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        // Insert new
        $sql = "INSERT INTO display (id, title, description, city, state, price, type, images, google_maps_url, name, email, owner_name, contact, saved_to_display, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssissssssss", 
            $property['id'], $property['title'], $property['description'], $property['city'], 
            $property['state'], $property['price'], $property['type'], $property['images'], $property['google_maps_url'], 
            $name, $email, $property['owner_name'], $property['contact'], $property['created_at']);
        $stmt->execute();
    } else {
        // Update existing
        $sql = "UPDATE display 
                SET title = ?, description = ?, city = ?, state = ?, price = ?, type = ?, images = ?, google_maps_url = ?, name = ?, email = ?, owner_name = ?, contact = ?, saved_to_display = 1, created_at = ?
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssissssssssi", 
            $property['title'], $property['description'], $property['city'], $property['state'], 
            $property['price'], $property['type'], $property['images'], $property['google_maps_url'], 
            $name, $email, $property['owner_name'], $property['contact'], $property['created_at'], $property['id']);
        $stmt->execute();
    }
    $stmt->close();
}

// Step 3: Delete records from display that are no longer in sellers
$toDelete = array_diff($existingDisplayIds, $currentIds);
if (!empty($toDelete)) {
    $placeholders = implode(',', array_fill(0, count($toDelete), '?'));
    $types = str_repeat('i', count($toDelete));

    $sql = "DELETE FROM display WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$toDelete);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
echo "Display table updated successfully.";
?>
