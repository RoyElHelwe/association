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

// Function to check if category is in use
function isCategoryInUse($conn, $categoryId)
{
    // Check machines table
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM machines WHERE category = ?");
    $stmt->bind_param("s", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if ($row['count'] > 0)
        return true;

    // Check medications table
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM medications WHERE category = ?");
    $stmt->bind_param("s", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if ($row['count'] > 0)
        return true;

    return false;
}

// Handle add or edit category
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['status' => 'error', 'message' => ''];

    // Validate and sanitize input
    $name = validateInput($_POST['name'] ?? '');

    if (empty($name)) {
        echo json_encode(['status' => 'error', 'message' => 'Category name is required!']);
        exit;
    }

    if (isset($_GET['action'])) {
        // Handle Edit Operation
        if ($_GET['action'] === 'edit' && isset($_POST['id'])) {
            $id = (int) $_POST['id'];

            try {
                $conn->begin_transaction();

                // Get old category name
                $stmt = $conn->prepare("SELECT name FROM categories WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $old_category = $result->fetch_assoc();
                $stmt->close();

                // Update category name
                $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
                $stmt->bind_param("si", $name, $id);
                $stmt->execute();
                $stmt->close();

                // Update machines table
                $stmt = $conn->prepare("UPDATE machines SET category = ? WHERE category = ?");
                $stmt->bind_param("ss", $name, $old_category['name']);
                $stmt->execute();
                $stmt->close();

                // Update medications table
                $stmt = $conn->prepare("UPDATE medications SET category = ? WHERE category = ?");
                $stmt->bind_param("ss", $name, $old_category['name']);
                $stmt->execute();
                $stmt->close();

                $conn->commit();
                echo json_encode(['status' => 'success', 'message' => 'Category and related items updated successfully!']);
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
            }
        }

        // Handle Add Operation
        elseif ($_GET['action'] === 'add') {
            try {
                $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
                $stmt->bind_param("s", $name);

                if ($stmt->execute()) {
                    echo json_encode(['status' => 'success', 'message' => 'Category added successfully!']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to add category: ' . $conn->error]);
                }
                $stmt->close();
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
            }
        }
    }
    exit;
}

// Handle delete category
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    if ($id > 0) {
        try {
            // Check if category is in use
            $stmt = $conn->prepare("SELECT name FROM categories WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $category = $result->fetch_assoc();
            $stmt->close();

            if (isCategoryInUse($conn, $category['name'])) {
                echo json_encode(['status' => 'error', 'message' => 'Cannot delete category: It is being used by machines or medications']);
                exit;
            }

            // If not in use, proceed with deletion
            $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Category deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete category']);
            }
            $stmt->close();
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid category ID']);
    }
    exit;
}

// Get all categories with usage count
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $query = "SELECT c.*, 
                  (SELECT COUNT(*) FROM machines WHERE category = c.name) +
                  (SELECT COUNT(*) FROM medications WHERE category = c.name) as usage_count
                  FROM categories c
                  ORDER BY c.created_at DESC";

        $result = $conn->query($query);

        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = array(
                'id' => $row['id'],
                'name' => htmlspecialchars_decode($row['name']),
                'created_at' => $row['created_at'],
                'usage_count' => $row['usage_count']
            );
        }

        echo json_encode($categories);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
}

$conn->close();
?>