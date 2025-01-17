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
            $dateCondition = "1=1";
    }

    // Main query for medication orders
    $sql = "
        SELECT 
            o.id AS order_id,
            o.status AS order_status,
            o.created_at AS order_date,
            u.clinic_name,
            u.id AS clinic_id,
            m.title AS medication_name,
            m.category AS medication_category,
            oi.quantity AS ordered_quantity,
            m.quantity AS stock_quantity,
            m.id AS medication_id
        FROM orders o
        JOIN users u ON o.clinic_id = u.id
        JOIN JSON_TABLE(o.items, '$[*]' COLUMNS (
            item_type VARCHAR(50) PATH '$.type',
            item_id INT PATH '$.id',
            quantity INT PATH '$.quantity'
        )) AS oi
        LEFT JOIN medications m ON oi.item_id = m.id
        WHERE oi.item_type = 'medication' 
        AND oi.item_id IS NOT NULL
        AND $dateCondition
        ORDER BY o.created_at DESC
    ";

    // Statistics query for low stock medications
    $lowStockSql = "
        SELECT 
            COUNT(*) as low_stock_count
        FROM medications
        WHERE quantity <= 10
    ";

    $result = $conn->query($sql);
    $lowStockResult = $conn->query($lowStockSql);

    $medicationOrders = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $medicationOrders[] = $row;
        }
    }

    // Calculate summary statistics
    $summary = [
        'total_orders' => count($medicationOrders),
        'total_items_ordered' => array_sum(array_column($medicationOrders, 'ordered_quantity')),
        'unique_medications' => count(array_unique(array_column($medicationOrders, 'medication_id'))),
        'low_stock_count' => ($lowStockResult->fetch_assoc())['low_stock_count'],
        'category_distribution' => array_count_values(array_column($medicationOrders, 'medication_category')),
        'status_distribution' => array_count_values(array_column($medicationOrders, 'order_status'))
    ];

    // Calculate monthly trends
    $monthlyTrends = [];
    foreach ($medicationOrders as $order) {
        $month = date('Y-m', strtotime($order['order_date']));
        if (!isset($monthlyTrends[$month])) {
            $monthlyTrends[$month] = 0;
        }
        $monthlyTrends[$month] += $order['ordered_quantity'];
    }

    echo json_encode([
        'status' => 'success',
        'data' => $medicationOrders,
        'summary' => $summary,
        'trends' => $monthlyTrends
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