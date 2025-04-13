<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli("localhost", "root", "", "property");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$property_id = $_GET['id'] ?? '';
$name = $_GET['name'] ?? '';
$email = $_GET['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $type = $_POST['type'];
    $purpose = $_POST['purpose'];
    $price = $_POST['price'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $google_maps_url = $_POST['google_maps_url'];
    $current_images = $_POST['current_images'] ?? '';
    $imagePaths = explode(",", $current_images);

    if (!empty($_FILES['images']['name'][0])) {
        if (!file_exists('images')) {
            mkdir('images', 0777, true);
        }

        foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
            if ($_FILES['images']['error'][$index] === UPLOAD_ERR_OK) {
                $filename = time() . '_' . basename($_FILES['images']['name'][$index]);
                $targetPath = 'images/' . $filename;
                if (move_uploaded_file($tmpName, $targetPath)) {
                    $imagePaths[] = $targetPath;
                }
            }
        }
    }

    $imagePathString = implode(",", $imagePaths);

    // Update sellers table
    $sql = "UPDATE sellers 
            SET name = ?, contact = ?, type = ?, purpose = ?, price = ?, city = ?, state = ?, title = ?, description = ?, image = ?, google_maps_url = ?
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssissssssi", $name, $contact, $type, $purpose, $price, $city, $state, $title, $description, $imagePathString, $google_maps_url, $property_id);
    $stmt->execute();

    // Check if already pushed to display
    $check_sql = "SELECT saved_to_display FROM sellers WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $property_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $row = $check_result->fetch_assoc();
        if ($row['saved_to_display'] == 1) {
            // Update display table
            $update_display_sql = "UPDATE display 
                SET name = ?, contact = ?, type = ?, purpose = ?, price = ?, city = ?, state = ?, title = ?, description = ?, image = ?, google_maps_url = ?
                WHERE id = ?";
            $update_display_stmt = $conn->prepare($update_display_sql);
            $update_display_stmt->bind_param("ssssissssssi", $name, $contact, $type, $purpose, $price, $city, $state, $title, $description, $imagePathString, $google_maps_url, $property_id);
            $update_display_stmt->execute();
        }
    }

    header("Location: sellers1.php?name=" . urlencode($name) . "&email=" . urlencode($email));
    exit();
} else {
    $sql = "SELECT * FROM sellers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $property_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $property = $result->fetch_assoc();
}
?>
