<?php
// Include the database connection file
include '../config/db.php';
session_start();  // Start the session to store messages

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user input
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $clinic_name = mysqli_real_escape_string($conn, $_POST['clinic_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $role = "clinic";
    $clinic_id = mysqli_real_escape_string($conn, $_POST['clinic_id']);

    // Check if password and confirm password match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: ../register.php");  // Redirect back to the registration page
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if email already exists OR clinic ID already exists
    $sql = "SELECT * FROM users WHERE email = '$email' OR clinic_id = '$clinic_id'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        $_SESSION['error'] = "Email or Clinic ID already registered.";
        header("Location: ../register.php");  // Redirect back to the registration page
        exit();
    }

    // Insert new user into the database
    $sql = "INSERT INTO users (role, name, clinic_name, clinic_id, email, phone, password, address) 
            VALUES ('$role', '$name', '$clinic_name', '$clinic_id', '$email', '$phone', '$hashed_password', '$address')";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Registration successful. You can now log in.";
        header("Location: ../register.php");  // Redirect to the register page with a success message
        exit();
    } else {
        $_SESSION['error'] = "Error: " . mysqli_error($conn);
        header("Location: ../register.php");  // Redirect back to the registration page
        exit();
    }

    // Close the connection
    mysqli_close($conn);
}
?>
