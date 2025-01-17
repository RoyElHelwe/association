<?php
// category_api.php
// This API returns all available categories

// Database connection
$host = 'db';
$dbname = 'association_management';
$user = 'appuser';
$pass = 'apppassword';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SELECT id, name FROM categories"); // Assuming categories table has 'id' and 'name'

$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

echo json_encode($categories); // Return categories as JSON

$conn->close();
?>
