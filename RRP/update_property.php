<?php
$conn = new mysqli("localhost", "root", "", "property");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$id = $_POST['id'];
$title = $_POST['title'];
$price = $_POST['price'];
$city = $_POST['city'];
$state = $_POST['state'];
$name = $_POST['name'];
$description = $_POST['description'];

// Get existing images if new ones aren't uploaded
$sql = "SELECT image FROM sellers WHERE id = $id";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$existingImages = explode(",", $row['image']);  // Existing images as an array

// Handle the uploaded images
$newImagePaths = [];

if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0) {
    $uploadDir = "uploads/";
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Loop through uploaded files
    foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
        if ($_FILES['images']['error'][$index] == UPLOAD_ERR_OK) {
            $fileName = time() . "_" . basename($_FILES['images']['name'][$index]);
            $newImagePath = $uploadDir . $fileName;
            if (move_uploaded_file($tmpName, $newImagePath)) {
                $newImagePaths[] = $newImagePath;
            }
        }
    }
}

// Merge new images with the existing ones
$allImages = array_merge($existingImages, $newImagePaths);
$imagePathString = implode(",", $allImages);

// Update the property in the database
$stmt = $conn->prepare("UPDATE sellers SET title=?, price=?, city=?, state=?, name=?, description=?, image=? WHERE id=?");
$stmt->bind_param("sdsssssi", $title, $price, $city, $state, $name, $description, $imagePathString, $id);

if ($stmt->execute()) {
    echo "<script>alert('Property updated successfully!'); window.location.href='sellers1.php';</script>";
} else {
    echo "<script>alert('Failed to update property.'); window.history.back();</script>";
}

$stmt->close();
$conn->close();
?>
