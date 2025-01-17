<?php

error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set JSON header
header('Content-Type: application/json');

// Handle errors
function handleError($message)
{
    echo json_encode([
        'status' => 'error',
        'message' => $message
    ]);
    exit;
}
try {
    include '../../config/db.php';
} catch (Exception $e) {
    handleError('Database connection failed: ' . $e->getMessage());
}

try {
    // Get time range from query parameter
    $timeRange = $_GET['timeRange'] ?? 'month';

    // Build date condition based on time range
    $dateCondition = "";
    switch ($timeRange) {
        case 'today':
            $dateCondition = "DATE(o.created_at) = CURDATE()";
            break;
        case 'week':
            $dateCondition = "YEARWEEK(o.created_at) = YEARWEEK(CURDATE())";
            break;
        case 'month':
            $dateCondition = "YEAR(o.created_at) = YEAR(CURDATE()) AND MONTH(o.created_at) = MONTH(CURDATE())";
            break;
        case 'year':
            $dateCondition = "YEAR(o.created_at) = YEAR(CURDATE())";
            break;
        default:
            $dateCondition = "1=1"; // No date filter
    }

    $sql = "
        SELECT 
            o.id AS order_id, 
            u.clinic_name, 
            o.status AS order_status, 
            o.created_at AS order_date, 
            b.blood_type, 
            b.quantity AS blood_stock_quantity, 
            oi.quantity AS blood_ordered_quantity,
            b.expiration_date
        FROM orders o
        JOIN users u ON o.clinic_id = u.id
        JOIN JSON_TABLE(o.items, '$[*]' COLUMNS (
            item_type VARCHAR(50) PATH '$.type',
            item_id INT PATH '$.id',
            quantity INT PATH '$.quantity'
        )) AS oi
        LEFT JOIN blood b ON oi.item_id = b.id AND oi.item_type = 'blood'
        WHERE oi.item_type = 'blood' 
        AND oi.item_id IS NOT NULL
        AND $dateCondition
        ORDER BY o.created_at DESC
    ";

    $result = $conn->query($sql);

    $bloodOrders = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $bloodOrders[] = $row;
        }
    }

    // Add summary statistics
    $summary = [
        'total_orders' => count($bloodOrders),
        'total_units_ordered' => array_sum(array_column($bloodOrders, 'blood_ordered_quantity')),
        'blood_type_distribution' => array_count_values(array_column($bloodOrders, 'blood_type'))
    ];

    echo json_encode([
        'status' => 'success',
        'data' => $bloodOrders,
        'summary' => $summary
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    $conn->close();
}
?>