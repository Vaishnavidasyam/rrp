<?php
$conn = new mysqli("localhost", "root", "", "property");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Capture the name and email from the query parameters
$name = $_GET['name'] ?? '';
$email = isset($_GET['email']) ? urldecode($_GET['email']) : '';  // Decode the email

// Modify the SQL query to filter by name and email
$sql = "SELECT * FROM sellers WHERE name = ? AND email = ? ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $name, $email);
$stmt->execute();
$result = $stmt->get_result();

$properties = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $properties[] = $row;
    }
} else {
    echo "No properties found.";
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Manage Properties</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="p-5">
  <div class="container">
    <h2 class="mb-4">Properties Submitted by <?= htmlspecialchars($name) ?> </h2>

    <div id="propertyList" class="row g-4"></div>

    <!-- Hidden fields to pass name and email -->
    <form id="deleteForm" method="POST" style="display: none;">
      <input type="hidden" name="delete_id" id="delete_id" />
    </form>
  </div>

  <script>
    const properties = <?= json_encode($properties) ?>;

    const renderProperties = () => {
      const propertyList = document.getElementById('propertyList');
      properties.forEach(property => {
        const div = document.createElement('div');
        div.className = 'col-md-4';
        div.innerHTML = `
          <div class="card">
            <img src="${property.image ? 'images/' + property.image.split(',')[0] : ''}" class="card-img-top" alt="Property Image" />
            <div class="card-body">
              <h5 class="card-title">${property.title}</h5>
              <p class="card-text">${property.description}</p>
              <a href="edit_property.php?id=${property.id}" class="btn btn-primary">Edit</a>
              <button type="button" class="btn btn-danger" onclick="deleteProperty(${property.id})">Delete</button>
            </div>
          </div>
        `;
        propertyList.appendChild(div);
      });
    };

    const deleteProperty = (id) => {
      if (confirm('Are you sure you want to delete this property?')) {
        document.getElementById('delete_id').value = id;
        document.getElementById('deleteForm').submit();
      }
    };

    renderProperties();
  </script>
</body>
</html>
