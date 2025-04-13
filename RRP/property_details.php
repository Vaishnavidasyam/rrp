<?php
$conn = new mysqli("localhost", "root", "", "property");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
  echo "Invalid property ID.";
  exit;
}

$sql = "SELECT * FROM display WHERE id = $id";
$result = $conn->query($sql);
$property = $result->fetch_assoc();
$conn->close();

if (!$property) {
  echo "Property not found.";
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Property Details</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
</head>
<body class="p-4">
  <div class="container">
    <a href="buyer.html" class="btn btn-secondary mb-3">← Back to Listings</a>
    <div class="card shadow">
      <?php if ($property['image']): ?>
        <img src="<?= $property['image'] ?>" class="card-img-top" style="height:300px; object-fit:cover;">
      <?php endif; ?>
      <div class="card-body">
        <h3 class="card-title"><?= htmlspecialchars($property['title']) ?></h3>
        <p class="card-text"><strong>Type:</strong> <?= $property['type'] ?></p>
        <p class="card-text"><strong>Purpose:</strong> <?= $property['purpose'] ?></p>
        <p class="card-text"><strong>Price:</strong> <?= $property['price'] ?></p>
        <p class="card-text"><strong>Location:</strong> <?= $property['city'] ?>, <?= $property['state'] ?></p>
        <p class="card-text"><strong>Posted By:</strong> <?= $property['name'] ?></p>
        <p class="card-text"><strong>Description:</strong><br><?= nl2br(htmlspecialchars($property['description'])) ?></p>
      </div>
    </div>
  </div>
</body>
</html>
