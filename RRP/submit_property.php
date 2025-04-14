<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "property";

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
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
    $name = $_POST['name'];
    $email = $_POST['email'];

    // Upload new images (if any)
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

    // Combine images into one string
    $images = implode(',', $newImages);

    // Prepare SQL query for inserting property data
    $sql = "INSERT INTO sellers (
                owner_name, contact, title, type, purpose, price, 
                city, state, description, google_maps_url, images, name, email
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssssssssss",
        $ownerName, $contact, $title, $type, $purpose, $price,
        $city, $state, $description, $googleMapsUrl, $images,
        $name, $email
    );

    // Execute and handle success or failure
    if ($stmt->execute()) {
        $msg = "Property submitted successfully!";
    } else {
        $msg = "Error: " . $stmt->error;
    }

    // Close the statement and the connection
    $stmt->close();
    $conn->close();

    // Redirect to display properties page
    header("Location: display_properties.php?name=$name&email=$email");
    exit();
}
?>
