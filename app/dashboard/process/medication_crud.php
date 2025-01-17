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

// Ensure the uploads directory exists and is writable
$target_dir = "uploads/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// Function to validate input
function validateInput($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

// Handle add or edit medication
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['status' => 'error', 'message' => ''];

    // Validate and sanitize input
    $title = validateInput($_POST['title'] ?? '');
    $description = validateInput($_POST['description'] ?? '');
    $category = validateInput($_POST['category'] ?? '');
    $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 0;

    if (isset($_GET['action'])) {
        // Handle Edit Operation
        if ($_GET['action'] === 'edit' && isset($_POST['id'])) {
            $id = (int) $_POST['id'];

            // Handle image upload if provided
            $image = null;
            if (isset($_FILES['fileToUpload']) && $_FILES['fileToUpload']['error'] === UPLOAD_ERR_OK) {
                $image = basename($_FILES['fileToUpload']['name']);
                $target_file = $target_dir . $image;

                if (!move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $target_file)) {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to upload image.']);
                    exit;
                }
            }

            try {
                if ($image) {
                    // Update with new image
                    $stmt = $conn->prepare("UPDATE medications SET title = ?, description = ?, category = ?, quantity = ?, image = ? WHERE id = ?");
                    $stmt->bind_param("sssisi", $title, $description, $category, $quantity, $image, $id);
                } else {
                    // Update without changing image
                    $stmt = $conn->prepare("UPDATE medications SET title = ?, description = ?, category = ?, quantity = ? WHERE id = ?");
                    $stmt->bind_param("sssii", $title, $description, $category, $quantity, $id);
                }

                if ($stmt->execute()) {
                    echo json_encode(['status' => 'success', 'message' => 'Medication updated successfully!']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to update medication: ' . $conn->error]);
                }
                $stmt->close();
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
            }
        }

        // Handle Add Operation
        elseif ($_GET['action'] === 'add') {
            $image = null;

            // Handle image upload
            if (isset($_FILES['fileToUpload']) && $_FILES['fileToUpload']['error'] === UPLOAD_ERR_OK) {
                $image = basename($_FILES['fileToUpload']['name']);
                $target_file = $target_dir . $image;

                if (!move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $target_file)) {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to upload image.']);
                    exit;
                }
            }

            try {
                $stmt = $conn->prepare("INSERT INTO medications (title, description, category, quantity, image) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssis", $title, $description, $category, $quantity, $image);

                if ($stmt->execute()) {
                    echo json_encode(['status' => 'success', 'message' => 'Medication added successfully!']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to add medication: ' . $conn->error]);
                }
                $stmt->close();
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
            }
        }
    }
    exit;
}

// Handle delete medication
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    if ($id > 0) {
        try {
            // First get the image filename if it exists
            $stmt = $conn->prepare("SELECT image FROM medications WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                // If there's an image, delete it
                if (!empty($row['image'])) {
                    $image_path = $target_dir . $row['image'];
                    if (file_exists($image_path)) {
                        unlink($image_path);
                    }
                }
            }
            $stmt->close();

            // Then delete the database record
            $stmt = $conn->prepare("DELETE FROM medications WHERE id = ?");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Medication deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete medication']);
            }
            $stmt->close();
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid medication ID']);
    }
    exit;
}

// Get all medications (for AJAX requests)
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $result = $conn->query("SELECT * FROM medications ORDER BY created_at DESC");

        $medications = [];
        while ($row = $result->fetch_assoc()) {
            $medications[] = array(
                'id' => $row['id'],
                'title' => htmlspecialchars_decode($row['title']),
                'description' => htmlspecialchars_decode($row['description']),
                'image' => $row['image'],
                'category' => $row['category'],
                'quantity' => $row['quantity'],
                'created_at' => $row['created_at']
            );
        }

        echo json_encode($medications);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
}

$conn->close();
?>