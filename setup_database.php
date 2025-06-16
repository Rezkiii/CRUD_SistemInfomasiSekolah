<?php
// Database credentials
$host = 'localhost';
$user = 'your_user';
$pass = 'your_password';

require_once 'config/database.php';

try {
    // Create connection without database
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read SQL file
    $sql = file_get_contents('database/school_db.sql');
    
    // Execute SQL commands
    $pdo->exec($sql);
    
    // Buat tabel users jika belum ada
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $conn->exec($sql);
    
    echo "Database and tables created successfully!";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 
