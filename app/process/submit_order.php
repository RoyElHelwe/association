<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in and is a clinic
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'clinic') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

include '../config/db.php';

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['items']) || empty($input['items'])) {
        throw new Exception('No items provided');
    }

    // Start transaction
    mysqli_begin_transaction($conn);

    // Insert order
    $clinic_id = $_SESSION['user_id'];
    $items_json = json_encode($input['items']);

    $stmt = mysqli_prepare($conn, "INSERT INTO orders (clinic_id, items, status) VALUES (?, ?, 'pending')");
    mysqli_stmt_bind_param($stmt, "is", $clinic_id, $items_json);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Failed to create order');
    }

    // Update medication quantities
    foreach ($input['items'] as $item) {
        if ($item['type'] === 'medication') {
            // First check if enough quantity is available
            $checkStmt = mysqli_prepare($conn, "SELECT quantity FROM medications WHERE id = ?");
            mysqli_stmt_bind_param($checkStmt, "i", $item['id']);
            mysqli_stmt_execute($checkStmt);
            $result = mysqli_stmt_get_result($checkStmt);
            $currentStock = mysqli_fetch_assoc($result)['quantity'];

            if ($currentStock < $item['quantity']) {
                throw new Exception('Not enough stock available for one or more items');
            }

            // Update the quantity
            $updateStmt = mysqli_prepare(
                $conn,
                "UPDATE medications SET quantity = quantity - ? WHERE id = ? AND quantity >= ?"
            );
            mysqli_stmt_bind_param($updateStmt, "iii", $item['quantity'], $item['id'], $item['quantity']);

            if (!mysqli_stmt_execute($updateStmt)) {
                throw new Exception('Failed to update medication quantity');
            }

            mysqli_stmt_close($updateStmt);
        }
    }

    mysqli_stmt_close($stmt);

    // If everything is successful, commit the transaction
    mysqli_commit($conn);

    echo json_encode([
        'status' => 'success',
        'message' => 'Order submitted successfully'
    ]);

} catch (Exception $e) {
    // If there's an error, rollback the transaction
    mysqli_rollback($conn);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    mysqli_close($conn);
}
?>