<?php
// Database configuration
$db_host = "localhost";
$db_user = "root";
$db_pass = "ceo@2005";
$db_name = "student_results_db";

// Create connection and select database
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Now the database is selected, you can run queries safely
$sql = "SELECT * FROM users WHERE username = 'admin'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    // Create default admin user (password: admin123)
    $default_password = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password, role) VALUES ('admin', '$default_password', 'admin')";
    
    if ($conn->query($sql) !== TRUE) {
        die("Error creating default admin user: " . $conn->error);
    }
}
?>
