<?php
$conn = new mysqli("localhost", "root", "", "property");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'] ?? null;
$name = $_GET['name'] ?? '';
$email = $_GET['email'] ?? '';

if (!$id) {
    echo "Property ID missing.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Update property details
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

    // Handle images
    $existingImages = $_POST['existing_images'] ?? ''; // Keep existing images
    $newImages = [];

    if (isset($_FILES['images']) && $_FILES['images']['error'][0] != 4) { // Check if new images were uploaded
        foreach ($_FILES['images']['tmp_name'] as $index => $tmpName) {
            $imageName = $_FILES['images']['name'][$index];
            $imageTmp = $_FILES['images']['tmp_name'][$index];
            $uploadDir = 'uploads/';
            $imagePath = $uploadDir . basename($imageName);
            if (move_uploaded_file($imageTmp, $imagePath)) {
                $newImages[] = $imagePath; // Add new image path to the array
            }
        }
    }

    // Combine existing images with new ones (if any)
    if ($newImages) {
        $existingImages = $existingImages ? $existingImages . ',' . implode(',', $newImages) : implode(',', $newImages);
    }

    // Update query to include the combined images
    $sql = "UPDATE sellers SET owner_name=?, contact=?, title=?, type=?, purpose=?, price=?, city=?, state=?, description=?, google_maps_url=?, images=? 
            WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssssi", $owner_name, $contact, $title, $type, $purpose, $price, $city, $state, $description, $google_maps_url, $existingImages, $id);

    if ($stmt->execute()) {
        header("Location: display_properties.php?name=" . urlencode($name) . "&email=" . urlencode($email));
        exit();
    } else {
        echo "Update failed!";
    }
} else {
    // Fetch property for form display
    $stmt = $conn->prepare("SELECT * FROM sellers WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $property = $result->fetch_assoc();

    if (!$property) {
        echo "Property not found.";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit Property</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
</head>
<body class="p-5">
  <div class="container">
    <h2>Edit Property: <?= htmlspecialchars($property['title']) ?></h2>

    <form method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label>Owner Name</label>
        <input type="text" name="owner_name" class="form-control" value="<?= htmlspecialchars($property['owner_name']) ?>" required>
      </div>
      <div class="mb-3">
        <label>Contact</label>
        <input type="text" name="contact" class="form-control" value="<?= htmlspecialchars($property['contact']) ?>" required>
      </div>
      <div class="mb-3">
        <label>Title</label>
        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($property['title']) ?>" required>
      </div>
      <div class="mb-3">
        <label>Type</label>
        <input type="text" name="type" class="form-control" value="<?= htmlspecialchars($property['type']) ?>" required>
      </div>
      <div class="mb-3">
        <label>Purpose</label>
        <input type="text" name="purpose" class="form-control" value="<?= htmlspecialchars($property['purpose']) ?>" required>
      </div>
      <div class="mb-3">
        <label>Price</label>
        <input type="number" name="price" class="form-control" value="<?= htmlspecialchars($property['price']) ?>" required>
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
        <textarea name="description" class="form-control" required><?= htmlspecialchars($property['description']) ?></textarea>
      </div>
      <div class="mb-3">
        <label>Google Maps URL</label>
        <input type="url" name="google_maps_url" class="form-control" value="<?= htmlspecialchars($property['google_maps_url']) ?>">
      </div>

      <!-- Existing images -->
      <div class="mb-3">
        <label>Current Images:</label><br>
        <?php
          $images = explode(',', $property['images']);
          foreach ($images as $image) {
              echo "<div class='mb-2'>
                        <img src='$image' alt='Property Image' style='max-height: 150px;' />
                    </div>";
          }
        ?>
        <input type="hidden" name="existing_images" value="<?= htmlspecialchars($property['images']) ?>">
      </div>

      <!-- Upload new images -->
      <div class="mb-3">
        <label>Upload New Images:</label>
        <input type="file" name="images[]" class="form-control" multiple>
      </div>

      <button type="submit" class="btn btn-success">Update Property</button>
      <a href="display_properties.php?name=<?= urlencode($name) ?>&email=<?= urlencode($email) ?>" class="btn btn-secondary">Cancel</a>
    </form>
  </div>
</body>
</html>
