<?php
include '../../config/db.php';

header('Content-Type: application/json');

// Query to get orders data
$sql = "SELECT * FROM orders";
$result = $conn->query($sql);

$orders = array();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // For orders, you might want to decode the 'items' JSON column if necessary
        $row['items'] = json_decode($row['items']);
        $orders[] = $row;
    }
} else {
    echo json_encode(["message" => "No orders data found"]);
    exit();
}

echo json_encode($orders);
$conn->close();
?>
