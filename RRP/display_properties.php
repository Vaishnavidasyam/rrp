<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "property";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$name = $_GET['name'] ?? '';
$email = $_GET['email'] ?? '';

if (!$name || !$email) {
    echo "No properties to display. Please provide name and email parameters.";
    exit();
}

$sql = "SELECT * FROM sellers WHERE name = ? AND email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $name, $email);
$stmt->execute();
$result = $stmt->get_result();

$properties = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $properties[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Property Listings</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    .property-card {
      cursor: pointer;
      transition: transform 0.2s ease-in-out;
    }
    .property-card:hover {
      transform: scale(1.02);
    }
  </style>
</head>
<body class="p-5">
  <div class="container">
    <h2 class="mb-4">Properties Submitted by <?= htmlspecialchars($name) ?> </h2>
    <div class="row" id="propertyList"></div>
    <button id="saveButton" class="btn btn-success mt-4">Save Properties</button>
  </div>

  <script>
    const properties = <?= json_encode($properties) ?>;
    const name = '<?= $name ?>';  // Raw name value from query string
    const email = '<?= $email ?>';  // Raw email value from query string

    const propertyList = document.getElementById('propertyList');
    const saveButton = document.getElementById('saveButton');

    properties.forEach(property => {
      const div = document.createElement('div');
      div.className = 'col-md-4';

      // Extract all images
      const images = property.images ? property.images.split(',') : [];
      
      // Display the first image or fallback to a placeholder image if no image exists
      const firstImage = images.length > 0 ? images[0] : 'placeholder.jpg';
      const imageHtml = `<img src="${firstImage}" class="card-img-top" alt="Property Image" style="max-height: 200px;">`;

      // Create the property card dynamically
      div.innerHTML = `
        <div class="card h-100 property-card">
          ${imageHtml}
          <div class="card-body">
            <h5 class="card-title">${property.title}</h5>
            <p class="card-text">${property.city}, ${property.state}</p>
            <a href="view_property.php?id=${property.id}" class="btn btn-outline-info mt-2">View</a>
            <a href="edit_property.php?id=${property.id}" class="btn btn-outline-primary mt-2">Edit</a>
            <a href="delete_property.php?id=${property.id}" class="btn btn-outline-danger mt-2" onclick="return confirm('Are you sure you want to delete this property?')">Delete</a>
          </div>
        </div>
      `;

      propertyList.appendChild(div);
    });

    // Save button click event
    saveButton.addEventListener('click', () => {
      const confirmed = confirm('Are you sure you want to save these properties?');
      if (confirmed) {
        // Perform AJAX to save properties
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'save_properties.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.onload = function () {
          if (xhr.status === 200) {
            alert('Properties have been saved to the display table!');
          } else {
            alert('Failed to save properties.');
          }
        };

        // Send properties data as JSON
        xhr.send(JSON.stringify({ properties: properties, name: name, email: email }));
      }
    });
  </script>
</body>
</html>
