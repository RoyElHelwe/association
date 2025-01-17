<?php
$host = 'db';
$dbname = 'association_management';
$user = 'appuser';
$pass = 'apppassword';

// Connect to MySQL database
$conn = new mysqli($host, $user, $pass, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
