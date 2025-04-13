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

// Retrieve query parameters
$name = $_GET['name'] ?? '';
$email = $_GET['email'] ?? '';

if (!$name || !$email) {
    echo "No properties to display. Please provide name and email parameters.";
    exit();
}

// Query to fetch properties based on name and email
$sql = "SELECT * FROM sellers WHERE name = '$name' AND email = '$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Display properties
    while ($row = $result->fetch_assoc()) {
        echo "<div>";
        echo "<h3>" . htmlspecialchars($row['title']) . "</h3>";
        echo "<p>Owner: " . htmlspecialchars($row['owner_name']) . "</p>";
        echo "<p>Price: " . htmlspecialchars($row['price']) . "</p>";
        echo "<p>Type: " . htmlspecialchars($row['type']) . "</p>";
        echo "<p>Description: " . nl2br(htmlspecialchars($row['description'])) . "</p>";
        echo "<p>Location: <a href='" . htmlspecialchars($row['google_maps_url']) . "'>View on Google Maps</a></p>";

        // Display images
        $images = explode(',', $row['images']);
        foreach ($images as $image) {
            echo "<img src='" . htmlspecialchars($image) . "' alt='Property Image' style='width: 200px; height: auto; margin-top: 10px;' />";
        }
        
        echo "</div><hr>";
    }
} else {
    echo "No properties found.";
}

$conn->close();
?>
