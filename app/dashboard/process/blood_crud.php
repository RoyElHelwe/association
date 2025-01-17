<?php
// Connect to the database
$host = 'db';
$dbname = 'association_management';
$user = 'appuser';
$pass = 'apppassword';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set JSON content type header for API responses
header('Content-Type: application/json');

// Function to validate input
function validateInput($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

// Handle add or edit blood
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['status' => 'error', 'message' => ''];

    // Validate and sanitize input
    $blood_type = validateInput($_POST['blood_type'] ?? '');
    $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 0;
    $expiration_date = validateInput($_POST['expiration_date'] ?? '');

    if (empty($blood_type) || empty($expiration_date)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required!']);
        exit;
    }

    if (isset($_GET['action'])) {
        // Handle Edit Operation
        if ($_GET['action'] === 'edit' && isset($_POST['id'])) {
            $id = (int) $_POST['id'];

            try {
                $stmt = $conn->prepare("UPDATE blood SET blood_type = ?, quantity = ?, expiration_date = ? WHERE id = ?");
                $stmt->bind_param("sisi", $blood_type, $quantity, $expiration_date, $id);

                if ($stmt->execute()) {
                    echo json_encode(['status' => 'success', 'message' => 'Blood record updated successfully!']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to update blood record: ' . $conn->error]);
                }
                $stmt->close();
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
            }
        }

        // Handle Add Operation
        elseif ($_GET['action'] === 'add') {
            try {
                $stmt = $conn->prepare("INSERT INTO blood (blood_type, quantity, expiration_date) VALUES (?, ?, ?)");
                $stmt->bind_param("sis", $blood_type, $quantity, $expiration_date);

                if ($stmt->execute()) {
                    echo json_encode(['status' => 'success', 'message' => 'Blood record added successfully!']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to add blood record: ' . $conn->error]);
                }
                $stmt->close();
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
            }
        }
    }
    exit;
}

// Handle delete blood
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    if ($id > 0) {
        try {
            $stmt = $conn->prepare("DELETE FROM blood WHERE id = ?");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Blood record deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete blood record']);
            }
            $stmt->close();
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid blood record ID']);
    }
    exit;
}

// Get all blood records (for AJAX requests)
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $result = $conn->query("SELECT * FROM blood ORDER BY created_at DESC");

        $blood_records = [];
        while ($row = $result->fetch_assoc()) {
            $blood_records[] = array(
                'id' => $row['id'],
                'blood_type' => $row['blood_type'],
                'quantity' => $row['quantity'],
                'expiration_date' => $row['expiration_date'],
                'created_at' => $row['created_at']
            );
        }

        echo json_encode($blood_records);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
}

$conn->close();
?>