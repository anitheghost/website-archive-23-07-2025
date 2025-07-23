<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit("Unauthorized access.");
}

// Load configuration
$config = require __DIR__ . '/../includes/config.php'; // Adjust the path as needed

// Database connection parameters
$host = $config['db']['host'];
$dbname = $config['db']['dbname'];
$username = $config['db']['username'];
$password = $config['db']['password'];

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare and execute the delete statement
    $stmt = $pdo->prepare("DELETE FROM login_logs");
    $stmt->execute();
    
    // Return the number of deleted rows
    $deletedCount = $stmt->rowCount();
    echo $deletedCount . " login logs deleted successfully."; // Response message
    
    // Reset auto-increment to 1
    $resetAutoIncrementStmt = $pdo->prepare("ALTER TABLE login_logs AUTO_INCREMENT = 1");
    $resetAutoIncrementStmt->execute();
    
    echo " Auto-increment reset to 1."; // Additional message after resetting auto-increment
    
    http_response_code(200); // Success
} catch (PDOException $e) {
    http_response_code(500); // Server error
    exit("Query failed: " . $e->getMessage());
}
?>
