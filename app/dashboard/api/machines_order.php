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

    // Main query for machine orders
    $sql = "
        SELECT 
            o.id AS order_id,
            o.status AS order_status,
            o.created_at AS order_date,
            u.clinic_name,
            u.id AS clinic_id,
            m.title AS machine_name,
            m.category AS machine_category,
            oi.quantity AS ordered_quantity,
            m.quantity AS stock_quantity,
            m.id AS machine_id,
            m.description AS machine_description
        FROM orders o
        JOIN users u ON o.clinic_id = u.id
        JOIN JSON_TABLE(o.items, '$[*]' COLUMNS (
            item_type VARCHAR(50) PATH '$.type',
            item_id INT PATH '$.id',
            quantity INT PATH '$.quantity'
        )) AS oi
        LEFT JOIN machines m ON oi.item_id = m.id
        WHERE oi.item_type = 'machine'
        AND oi.item_id IS NOT NULL
        AND $dateCondition
        ORDER BY o.created_at DESC
    ";

    // Statistics query for available machines
    $availableSql = "
        SELECT 
            COUNT(*) as total_machines,
            SUM(CASE WHEN quantity > 0 THEN 1 ELSE 0 END) as available_machines
        FROM machines
    ";

    $result = $conn->query($sql);
    $availableResult = $conn->query($availableSql);

    $machineOrders = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $machineOrders[] = $row;
        }
    }

    $availableStats = $availableResult->fetch_assoc();

    // Calculate summary statistics
    $summary = [
        'total_orders' => count($machineOrders),
        'total_machines_ordered' => array_sum(array_column($machineOrders, 'ordered_quantity')),
        'unique_machines' => count(array_unique(array_column($machineOrders, 'machine_id'))),
        'total_machines' => $availableStats['total_machines'],
        'available_machines' => $availableStats['available_machines'],
        'category_distribution' => array_count_values(array_column($machineOrders, 'machine_category')),
        'status_distribution' => array_count_values(array_column($machineOrders, 'order_status'))
    ];

    // Calculate monthly trends
    $monthlyTrends = [];
    foreach ($machineOrders as $order) {
        $month = date('Y-m', strtotime($order['order_date']));
        if (!isset($monthlyTrends[$month])) {
            $monthlyTrends[$month] = 0;
        }
        $monthlyTrends[$month] += $order['ordered_quantity'];
    }

    // Calculate popular categories
    $popularCategories = array_count_values(array_column($machineOrders, 'machine_category'));
    arsort($popularCategories);

    echo json_encode([
        'status' => 'success',
        'data' => $machineOrders,
        'summary' => $summary,
        'trends' => $monthlyTrends,
        'popular_categories' => $popularCategories
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