<?php
session_start();
header('Content-Type: application/json');

include '../config/db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['notification_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

try {
    $stmt = $conn->prepare("
        UPDATE notifications 
        SET status = 'read' 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $_POST['notification_id'], $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        throw new Exception('Failed to update notification status');
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>