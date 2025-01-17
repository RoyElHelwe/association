<?php
// Include the database connection file
include '../config/db.php';

// Start session
session_start();

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user input
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Query the database for the user with the provided email
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        // Fetch user data
        $user = mysqli_fetch_assoc($result);

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Successful login, set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on the role
            if ($user['role'] == 'admin') {
                // Admin redirect to dashboard/index.php
                header("Location: ../dashboard/index.php");
            } elseif ($user['role'] == 'clinic') {
                // Clinic redirect to index.php
                header("Location: ../index.php");
            }
            exit;
        } else {
            // Password is incorrect
            $_SESSION['error'] = "Incorrect password.";
            header("Location: ../login.php");
            exit;
        }
    } else {
        // No user found with the provided email
        $_SESSION['error'] = "No user found with this email.";
        header("Location: ../login.php");
        exit;
    }
    
    // Close the database connection
    mysqli_close($conn);
}
?>
