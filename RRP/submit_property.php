<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "property";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $propertyId = $_POST['id'];
    $ownerName = $_POST['owner_name'];
    $contact = $_POST['contact'];
    $title = $_POST['title'];
    $type = $_POST['type'];
    $purpose = $_POST['purpose'];
    $price = $_POST['price'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $description = $_POST['description'];
    $googleMapsUrl = $_POST['google_maps_url'];

    // Process existing images (to delete them if needed)
    $existingImages = $conn->real_escape_string(implode(',', $_POST['delete_images'] ?? []));
    
    // Upload new images
    $newImages = [];
    if (isset($_FILES['images']) && $_FILES['images']['error'][0] != UPLOAD_ERR_NO_FILE) {
        $targetDir = "uploads/";
        foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
            if ($_FILES['images']['error'][$key] == UPLOAD_ERR_OK) {
                $fileName = basename($_FILES['images']['name'][$key]);
                $targetFile = $targetDir . uniqid() . "_" . $fileName;
                if (move_uploaded_file($tmpName, $targetFile)) {
                    $newImages[] = $targetFile;
                }
            }
        }
    }

    // Combine existing and new images
    $allImages = array_merge(explode(',', $existingImages), $newImages);
    $images = implode(',', $allImages);

    // Update or insert property into the database
    if ($propertyId) {
        $sql = "UPDATE sellers SET owner_name = ?, contact = ?, title = ?, type = ?, purpose = ?, price = ?, city = ?, state = ?, description = ?, google_maps_url = ?, images = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssssi", $ownerName, $contact, $title, $type, $purpose, $price, $city, $state, $description, $googleMapsUrl, $images, $propertyId);

        if ($stmt->execute()) {
            $msg = "Property updated successfully!";
        } else {
            $msg = "Error updating property: " . $stmt->error;
        }

        $stmt->close();
    } else {
        // Insert new property if no ID is provided
        $sql = "INSERT INTO sellers (owner_name, contact, title, type, purpose, price, city, state, description, google_maps_url, images) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssss", $ownerName, $contact, $title, $type, $purpose, $price, $city, $state, $description, $googleMapsUrl, $images);

        if ($stmt->execute()) {
            $msg = "Property submitted successfully!";
        } else {
            $msg = "Error submitting property: " . $stmt->error;
        }

        $stmt->close();
    }

    $conn->close();

    // Redirect with success message and query parameters
    $name = urlencode($_POST['name']);
    $email = urlencode($_POST['email']);
    header("Location: display_properties.php?name=$name&email=$email&msg=" . urlencode($msg));
    exit();
}
?>
