<?php
// Connect to the database
$host = 'db';
$dbname = 'association_management';
$user = 'appuser';
$pass = 'apppassword';

$conn = new mysqli($host, $user, $pass, $dbname);

// Set JSON content type header
header('Content-Type: application/json');

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Function to create notification
function createNotification($conn, $userId, $orderId, $message)
{
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, order_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $userId, $orderId, $message);
    return $stmt->execute();
}

// Handle GET request - Fetch all orders
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $query = "SELECT o.*, u.clinic_name, u.name as user_name 
                 FROM orders o 
                 LEFT JOIN users u ON o.clinic_id = u.id 
                 ORDER BY o.created_at DESC";

        $result = $conn->query($query);
        if (!$result) {
            throw new Exception($conn->error);
        }

        $orders = [];
        while ($row = $result->fetch_assoc()) {
            // Decode the JSON items
            $row['items'] = json_decode($row['items'], true);
            $orders[] = $row;
        }

        echo json_encode($orders);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

// Handle POST request - Update order status
elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';

    if (empty($id) || empty($status)) {
        echo json_encode(['status' => 'error', 'message' => 'Order ID and status are required!']);
        exit;
    }

    // Validate status
    $validStatuses = ['pending', 'accepted', 'declined', 'modified'];
    if (!in_array($status, $validStatuses)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid status value']);
        exit;
    }

    try {
        // Start transaction
        $conn->begin_transaction();

        // Get order details first
        $orderStmt = $conn->prepare("SELECT clinic_id FROM orders WHERE id = ?");
        $orderStmt->bind_param("i", $id);
        $orderStmt->execute();
        $orderResult = $orderStmt->get_result();
        $order = $orderResult->fetch_assoc();

        if (!$order) {
            throw new Exception('Order not found');
        }

        // Update order status
        $updateStmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $updateStmt->bind_param("si", $status, $id);

        if ($updateStmt->execute()) {
            // Create notification based on status
            $notificationMessage = "";
            switch ($status) {
                case 'accepted':
                    $notificationMessage = "Your order #$id has been accepted and will be processed soon.";
                    break;
                case 'declined':
                    $notificationMessage = "Your order #$id has been declined. Please contact support for more information.";
                    break;
                case 'modified':
                    $notificationMessage = "Your order #$id has been modified. Please check the updated details.";
                    break;
            }

            // Create notification if message exists
            if ($notificationMessage && !createNotification($conn, $order['clinic_id'], $id, $notificationMessage)) {
                throw new Exception('Failed to create notification');
            }

            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Order status updated successfully!']);
        } else {
            throw new Exception('Failed to update order status');
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

$conn->close();
?>