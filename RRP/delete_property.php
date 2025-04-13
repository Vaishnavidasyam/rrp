<?php
$conn = new mysqli("localhost", "root", "", "property");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$property_id = $_POST['id'] ?? '';

if ($property_id) {
    $sql = "DELETE FROM sellers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $property_id);

    if ($stmt->execute()) {
        // Redirect back to the property list page with a success message
        echo "Property deleted successfully!";
    } else {
        echo "Error deleting property: " . $stmt->error;
    }
}

$conn->close();
?>
