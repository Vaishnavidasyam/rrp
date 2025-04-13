<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "property";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'] ?? null;

if (!$id) {
    echo "No property selected.";
    exit();
}

$sql = "SELECT * FROM sellers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

$property = $result->fetch_assoc();
$conn->close();

if (!$property) {
    echo "Property not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Property Details</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
</head>
<body class="p-5">
  <div class="container">
    <h2 class="mb-4"><?= htmlspecialchars($property['title']) ?></h2>

    <div class="row">
      <div class="col-md-6">
        <?php
          $images = explode(',', $property['images']);
          foreach ($images as $img) {
              echo "<img src='" . htmlspecialchars($img) . "' class='img-fluid mb-3' style='max-height: 300px;' alt='Property Image'><br>";
          }
        ?>
      </div>
      <div class="col-md-6">
        <p><strong>Owner:</strong> <?= htmlspecialchars($property['owner_name']) ?></p>
        <p><strong>Contact:</strong> <?= htmlspecialchars($property['contact']) ?></p>
        <p><strong>Type:</strong> <?= htmlspecialchars($property['type']) ?></p>
        <p><strong>Purpose:</strong> <?= htmlspecialchars($property['purpose']) ?></p>
        <p><strong>Price:</strong> ₹<?= htmlspecialchars($property['price']) ?></p>
        <p><strong>City:</strong> <?= htmlspecialchars($property['city']) ?></p>
        <p><strong>State:</strong> <?= htmlspecialchars($property['state']) ?></p>
        <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($property['description'])) ?></p>
        <p><strong>Location:</strong> 
          <a href="<?= htmlspecialchars($property['google_maps_url']) ?>" target="_blank">View on Google Maps</a>
        </p>
      </div>
    </div>

    <a href="javascript:history.back()" class="btn btn-secondary mt-3">Back</a>
  </div>
</body>
</html>
