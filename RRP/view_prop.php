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

    // Fetch property details based on the property ID
    $sql = "SELECT * FROM sellers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $property = $result->fetch_assoc();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .property-img {
            max-width: 100%;
            height: auto;
        }
        .property-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .property-gallery img {
            max-width: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body class="container py-5">
    <h2 class="text-center mb-4">Property Details</h2>

    <?php if ($property): ?>
        <div class="row">
            <div class="col-md-6">
                <h3><?= htmlspecialchars($property['title']) ?></h3>
                <p><strong>Type:</strong> <?= htmlspecialchars($property['type']) ?></p>
                <p><strong>Purpose:</strong> <?= htmlspecialchars($property['purpose']) ?></p>
                <p><strong>Price:</strong> ₹<?= htmlspecialchars($property['price']) ?></p>
                <p><strong>City:</strong> <?= htmlspecialchars($property['city']) ?></p>
                <p><strong>State:</strong> <?= htmlspecialchars($property['state']) ?></p>
                <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($property['description'])) ?></p>
                <p><strong>Owner:</strong> <?= htmlspecialchars($property['owner_name']) ?></p>
                <p><strong>Contact:</strong> <?= htmlspecialchars($property['contact']) ?></p>
                <p><strong>Google Maps Location:</strong> <a href="<?= htmlspecialchars($property['google_maps_url']) ?>" target="_blank">View on Map</a></p>
                <p><small class="text-muted">Posted on <?= htmlspecialchars($property['created_at']) ?></small></p>
            </div>

            <div class="col-md-6">
                <h4>Property Images</h4>
                <div class="property-gallery">
                    <?php
                    $images = explode(',', $property['images']);
                    foreach ($images as $img) {
                        echo "<div><img src='$img' class='property-img' alt='Property Image'></div>";
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <a href="buyer.html?name=<?= urlencode($_GET['name'] ?? '') ?>&email=<?= urlencode($_GET['email'] ?? '') ?>" class="btn btn-secondary">Back to Listings</a>
        </div>

    <?php else: ?>
        <p class="text-center">Property not found.</p>
    <?php endif; ?>
</body>
</html>
