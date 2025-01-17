<?php

// Check if the user is logged in and if their role is admin
if ($_SESSION['role'] !== 'admin') {
    // Redirect to login page if not logged in or not an admin
    header("Location: /login.php");
    exit;
}
?>
