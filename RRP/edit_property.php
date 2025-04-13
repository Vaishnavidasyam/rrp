<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "property";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$propertyId = $_GET['id'] ?? null;

if (!$propertyId) {
    echo "Invalid property ID.";
    exit();
}

// Retrieve property details
$sql = "SELECT * FROM sellers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $propertyId);
$stmt->execute();
$result = $stmt->get_result();
$property = $result->fetch_assoc();

if (!$property) {
    echo "Property not found.";
    exit();
}

$updatedImages = $property['images'] ? explode(',', $property['images']) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $owner_name = $_POST['owner_name'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $title = $_POST['title'] ?? '';
    $type = $_POST['type'] ?? '';
    $purpose = $_POST['purpose'] ?? '';
    $price = $_POST['price'] ?? '';
    $city = $_POST['city'] ?? '';
    $state = $_POST['state'] ?? '';
    $description = $_POST['description'] ?? '';
    $google_maps_url = $_POST['google_maps_url'] ?? '';

    // Image deletion
    $deleteImages = $_POST['delete_images'] ?? [];
    $updatedImages = array_diff($updatedImages, $deleteImages);
    foreach ($deleteImages as $img) {
        if (file_exists($img)) {
            unlink($img);
        }
    }

    // Image uploads
    $newImages = [];
    foreach ($_FILES['images']['name'] as $index => $imageName) {
        if ($_FILES['images']['error'][$index] === 0) {
            $tmpName = $_FILES['images']['tmp_name'][$index];
            $targetPath = "uploads/" . time() . "_" . basename($imageName);
            if (move_uploaded_file($tmpName, $targetPath)) {
                $newImages[] = $targetPath;
            }
        }
    }

    $allImages = array_merge($updatedImages, $newImages);
    $imageString = implode(',', $allImages);

    // Update DB
    $updateSql = "UPDATE sellers SET owner_name=?, contact=?, title=?, type=?, purpose=?, price=?, city=?, state=?, description=?, google_maps_url=?, images=? WHERE id=?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("sssssssssssi", $owner_name, $contact, $title, $type, $purpose, $price, $city, $state, $description, $google_maps_url, $imageString, $propertyId);
    $stmt->execute();

    header("Location: display_properties.php?name=" . urlencode($property['name']));
    exit();
}
?>

<!-- HTML FORM -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Property</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-5">
  <div class="container">
    <h2>Edit Property</h2>
    <form action="edit_property.php?id=<?= $property['id'] ?>" method="POST" enctype="multipart/form-data">
      <div class="mb-3"><label class="form-label">Owner Name</label><input type="text" name="owner_name" class="form-control" value="<?= htmlspecialchars($property['owner_name']) ?>" required /></div>
      <div class="mb-3"><label class="form-label">Contact</label><input type="text" name="contact" class="form-control" value="<?= htmlspecialchars($property['contact']) ?>" required /></div>
      <div class="mb-3"><label class="form-label">Title</label><input type="text" name="title" class="form-control" value="<?= htmlspecialchars($property['title']) ?>" /></div>
      <div class="mb-3">
        <label class="form-label">Type</label>
        <select name="type" class="form-select" required>
          <option <?= $property['type'] === 'apartment' ? 'selected' : '' ?>>apartment</option>
          <option <?= $property['type'] === 'plot' ? 'selected' : '' ?>>plot</option>
          <option <?= $property['type'] === 'house' ? 'selected' : '' ?>>house</option>
          <option <?= $property['type'] === 'penthouse' ? 'selected' : '' ?>>penthouse</option>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Purpose</label>
        <select name="purpose" class="form-select" required>
          <option <?= $property['purpose'] === 'For Sale' ? 'selected' : '' ?>>For Sale</option>
          <option <?= $property['purpose'] === 'For Rent' ? 'selected' : '' ?>>For Rent</option>
        </select>
      </div>
      <div class="mb-3"><label class="form-label">Price</label><input type="text" name="price" class="form-control" value="<?= htmlspecialchars($property['price']) ?>" required /></div>
      <div class="mb-3"><label class="form-label">City</label><input type="text" name="city" class="form-control" value="<?= htmlspecialchars($property['city']) ?>" required /></div>
      <div class="mb-3"><label class="form-label">State</label><input type="text" name="state" class="form-control" value="<?= htmlspecialchars($property['state']) ?>" required /></div>
      <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($property['description']) ?></textarea></div>
      <div class="mb-3"><label class="form-label">Google Maps URL</label><input type="url" name="google_maps_url" class="form-control" value="<?= htmlspecialchars($property['google_maps_url']) ?>" />
      </div>

      <!-- Existing Images -->
      <div class="mb-3">
        <label class="form-label">Existing Images</label><br>
        <?php foreach ($updatedImages as $image): ?>
          <div class="mb-2">
            <img src="<?= $image ?>" style="max-height: 100px; margin-right: 10px;" />
            <input type="checkbox" name="delete_images[]" value="<?= $image ?>"> Delete
          </div>
        <?php endforeach; ?>
      </div>

      <div class="mb-3" id="image-inputs">
        <label class="form-label">Upload New Images</label>
        <input type="file" name="images[]" class="form-control mb-2" />
      </div>
      <button type="button" onclick="addImageInput()" class="btn btn-secondary btn-sm">+ Add More</button>

      <button type="submit" class="btn btn-primary mt-3">Submit</button>
      <a href="display_properties.php?name=<?= urlencode($property['name']) ?>" class="btn btn-danger mt-3">Cancel</a>
    </form>
  </div>
  <script>
    function addImageInput() {
      const container = document.getElementById("image-inputs");
      const input = document.createElement("input");
      input.type = "file";
      input.name = "images[]";
      input.className = "form-control mb-2";
      container.appendChild(input);
    }
  </script>
</body>
</html>
