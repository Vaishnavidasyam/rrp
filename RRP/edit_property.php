<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "property";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$property = null;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "SELECT * FROM sellers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $property = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $type = $_POST['type']; // Property type
    $purpose = $_POST['purpose']; // Property purpose
    $price = $_POST['price'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $description = $_POST['description'];
    $map_url = $_POST['google_maps_url'];

    // For handling new images
    $newImages = [];
    if (!empty($_FILES['images']['name'][0])) {
        $uploadDir = 'uploads/';
        foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
            $fileName = basename($_FILES['images']['name'][$index]);
            $targetPath = $uploadDir . time() . '_' . $fileName;
            if (move_uploaded_file($tmpName, $targetPath)) {
                $newImages[] = $targetPath;
            }
        }
    }

    // Fetch the current images for the property
    $sql = "SELECT images FROM sellers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $currentImages = $row['images'];

    // Delete selected images
    if (isset($_POST['delete_images'])) {
        $deleteImages = $_POST['delete_images']; // array of images to delete
        $currentImagesArray = explode(',', $currentImages);

        // Remove the deleted images from the current images array
        $updatedImagesArray = array_diff($currentImagesArray, $deleteImages);

        // Delete the physical files from the server
        foreach ($deleteImages as $img) {
            if (in_array($img, $currentImagesArray) && file_exists($img)) {
                unlink($img); // delete the file
            }
        }

        // Update the images array for the property
        $currentImages = implode(',', $updatedImagesArray);
    }

    // Combine current images with new ones
    $updatedImages = $currentImages;
    if (!empty($newImages)) {
        $updatedImages = $currentImages ? $currentImages . ',' . implode(',', $newImages) : implode(',', $newImages);
    }

    // Update the property in the sellers table
    $sql = "UPDATE sellers SET title = ?, type = ?, purpose = ?, price = ?, city = ?, state = ?, description = ?, google_maps_url = ?, images = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssi", $title, $type, $purpose, $price, $city, $state, $description, $map_url, $updatedImages, $id);
    $stmt->execute();

 
    // Redirect back to the properties display page
    $name = $_POST['name'];
    $email = $_POST['email'];
    header("Location: display_properties.php?name=$name&email=$email");
  exit();

  
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Property</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container py-5">
    <h2>Edit Property</h2>

    <?php if ($property): ?>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $property['id'] ?>">
        <input type="hidden" name="name" value="<?= htmlspecialchars($_GET['name'] ?? '') ?>">
        <input type="hidden" name="email" value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">

        <div class="mb-3">
            <label>Title</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($property['title']) ?>" required>
        </div>

        <div class="mb-3">
            <label>Type</label>
            <select name="type" class="form-control" required>
                <option value="apartment" <?= $property['type'] == 'apartment' ? 'selected' : '' ?>>Apartment</option>
                <option value="plot" <?= $property['type'] == 'plot' ? 'selected' : '' ?>>Plot</option>
                <option value="house" <?= $property['type'] == 'house' ? 'selected' : '' ?>>House</option>
                <option value="penthouse" <?= $property['type'] == 'penthouse' ? 'selected' : '' ?>>Penthouse</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Purpose</label>
            <select name="purpose" class="form-control" required>
                <option value="For Sale" <?= $property['purpose'] == 'For Sale' ? 'selected' : '' ?>>For Sale</option>
                <option value="For Rent" <?= $property['purpose'] == 'For Rent' ? 'selected' : '' ?>>For Rent</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Price</label>
            <input type="text" name="price" class="form-control" value="<?= htmlspecialchars($property['price']) ?>" required>
        </div>

        <div class="mb-3">
            <label>City</label>
            <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($property['city']) ?>" required>
        </div>

        <div class="mb-3">
            <label>State</label>
            <input type="text" name="state" class="form-control" value="<?= htmlspecialchars($property['state']) ?>" required>
        </div>

        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($property['description']) ?></textarea>
        </div>

        <div class="mb-3">
            <label>Google Maps URL</label>
            <input type="url" name="google_maps_url" class="form-control" value="<?= htmlspecialchars($property['google_maps_url']) ?>">
        </div>

        <div class="mb-3">
            <label>Current Images:</label>
            <div class="d-flex flex-wrap gap-2">
                <?php
                $images = explode(',', $property['images']);
                foreach ($images as $img) {
                    echo "<div class='position-relative'>
                            <img src='$img' alt='Image' style='width:100px; height:auto;'>
                            <input type='checkbox' name='delete_images[]' value='$img' class='position-absolute top-0 start-100 translate-middle'>
                          </div>";
                }
                ?>
            </div>
        </div>

        <div class="mb-3">
            <label>Upload New Images (optional):</label>
            <div id="imageInputs">
                <div class="input-group mb-2">
                    <input type="file" name="images[]" class="form-control" accept="image/*">
                </div>
            </div>
            <button type="button" id="addImageInput" class="btn btn-sm btn-outline-primary">+ Add More Images</button>
        </div>

        <button type="submit" class="btn btn-primary">Update Property</button>
        <a href="display_properties.php?name=<?= urlencode($_GET['name'] ?? '') ?>&email=<?= urlencode($_GET['email'] ?? '') ?>" class="btn btn-secondary">Back</a>
    </form>
    <?php else: ?>
        <p>Property not found.</p>
    <?php endif; ?>
    
    <script>
        document.getElementById('addImageInput').addEventListener('click', function () {
            const inputGroup = document.createElement('div');
            inputGroup.className = 'input-group mb-2';
            inputGroup.innerHTML = `
                <input type="file" name="images[]" class="form-control" accept="image/*">
            `;
            document.getElementById('imageInputs').appendChild(inputGroup);
        });
    </script>

</body>
</html>
