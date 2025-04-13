<?php
$conn = new mysqli("localhost", "root", "", "property");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the name and email from POST data
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';

// Fetch the unsaved properties for this user
// Get the unsaved properties
$sql = "SELECT * FROM sellers WHERE name = ? AND email = ? AND saved_to_display = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $name, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Insert unsaved properties into the display table
    while ($property = $result->fetch_assoc()) {
        // Insert into the display table
        $insertSql = "INSERT INTO display (user, email, contact, type, purpose, price, city, state, title, description, image, google_maps_url, saved_to_display)
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtInsert = $conn->prepare($insertSql);
        $stmtInsert->bind_param(
            "sssssssssssss",
            $name,
            $email,
            $property['contact'],
            $property['type'],
            $property['purpose'],
            $property['price'],
            $property['city'],
            $property['state'],
            $property['title'],
            $property['description'],
            $property['image'],
            $property['google_maps_url'],
            $property['saved_to_display']
        );

        if (!$stmtInsert->execute()) {
            echo json_encode(['success' => false, 'message' => 'Error saving property: ' . $stmtInsert->error]);
            exit();
        }

        // Update the sellers table to mark this property as saved
        $updateSql = "UPDATE sellers SET saved_to_display = 1 WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("i", $property['id']);
        $updateStmt->execute();
    }

    echo json_encode(['success' => true, 'message' => 'Properties successfully saved to display table!']);
} else {
    echo json_encode(['success' => false, 'message' => 'No unsaved properties found.']);
}

$conn->close();
?>
