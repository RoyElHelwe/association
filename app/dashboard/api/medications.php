<?php
include '../../config/db.php';

header('Content-Type: application/json');

// Query to get medications data
$sql = "SELECT * FROM medications";
$result = $conn->query($sql);

$medications = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $medications[] = $row;
    }
} else {
    echo json_encode(["message" => "No medications data found"]);
    exit();
}

echo json_encode($medications);
$conn->close();
?>
