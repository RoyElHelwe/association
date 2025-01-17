<?php
include '../../config/db.php';

header('Content-Type: application/json');

// Query to get machines data
$sql = "SELECT * FROM machines";
$result = $conn->query($sql);

$machines = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $machines[] = $row;
    }
} else {
    echo json_encode(["message" => "No machines data found"]);
    exit();
}

echo json_encode($machines);
$conn->close();
?>
