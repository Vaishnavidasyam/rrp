<?php
// Database connection
$servername = "localhost";
$username = "root"; // Change as needed
$password = ""; // Change as needed
$dbname = "property"; // Change as needed

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve form data
$name = $_POST['name'];
$email = $_POST['email'];
$owner_name = $_POST['owner_name'];
$contact = $_POST['contact'];
$title = $_POST['title'];
$type = $_POST['type'];
$purpose = $_POST['purpose'];
$price = $_POST['price'];
$city = $_POST['city'];
$state = $_POST['state'];
$description = $_POST['description'];
$google_maps_url = $_POST['google_maps_url'];

// Handle multiple image uploads
$imagePaths = [];
if (isset($_FILES['images'])) {
    foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
        $imageName = $_FILES['images']['name'][$index];
        $imageTmp = $_FILES['images']['tmp_name'][$index];
        $uploadDir = 'uploads/';
        $imagePath = $uploadDir . basename($imageName);
        if (move_uploaded_file($imageTmp, $imagePath)) {
            $imagePaths[] = $imagePath; // Store image path
        }
    }
}
$imagePaths = implode(',', $imagePaths); // Store image paths as comma-separated string

// Insert property data into the database
$sql = "INSERT INTO sellers (name, email, owner_name, contact, title, type, purpose, price, city, state, description, google_maps_url, images)
        VALUES ('$name', '$email', '$owner_name', '$contact', '$title', '$type', '$purpose', '$price', '$city', '$state', '$description', '$google_maps_url', '$imagePaths')";

if ($conn->query($sql) === TRUE) {
    // Redirect to another page to display the property details
    header("Location: display_properties.php?name=$name&email=$email");
    exit();
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
