<?php
include '../../config/db.php';

header('Content-Type: application/json');

// Query to get blood data
$sql = "SELECT * FROM blood";
$result = $conn->query($sql);

$blood = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $blood[] = $row;
    }
} else {
    echo json_encode(["message" => "No blood data found"]);
    exit();
}

echo json_encode($blood);
$conn->close();
?>
