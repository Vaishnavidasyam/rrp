<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "property";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$id = $_GET['id'] ?? '';
if (!$id) {
    echo "Invalid property ID.";
    exit();
}

$sql = "SELECT * FROM sellers WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$property = $result->fetch_assoc();

if (!$property) {
    echo "Property not found.";
    exit();
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
  <title>View Property</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-5">
  <div class="container">
    <h2><?= htmlspecialchars($property['title']) ?></h2>
    <p><strong>Owner:</strong> <?= htmlspecialchars($property['owner_name']) ?></p>
    <p><strong>Contact:</strong> <?= htmlspecialchars($property['contact']) ?></p>
    <p><strong>Type:</strong> <?= htmlspecialchars($property['type']) ?> | <strong>Purpose:</strong> <?= htmlspecialchars($property['purpose']) ?></p>
    <p><strong>Price:</strong> <?= htmlspecialchars($property['price']) ?></p>
    <p><strong>Location:</strong> <?= htmlspecialchars($property['city']) ?>, <?= htmlspecialchars($property['state']) ?></p>
    <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($property['description'])) ?></p>
    <?php if ($property['google_maps_url']): ?>
      <p><a href="<?= $property['google_maps_url'] ?>" target="_blank">View on Google Maps</a></p>
    <?php endif; ?>

    <h4>Images</h4>
    <div class="row">
      <?php foreach (explode(',', $property['images']) as $img): ?>
        <div class="col-md-4 mb-3"><img src="<?= $img ?>" style="width: 100%; max-height: 200px;" /></div>
      <?php endforeach; ?>
    </div>

    <a href="javascript:history.back()" class="btn btn-secondary">Back</a>
  </div>
</body>
</html>
