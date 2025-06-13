<?php
require_once 'config/database.php';

try {
    // Data admin baru
    $username = 'admin1';
    $password = 'admin12';
    $full_name = 'Admin Satu';
    $email = 'admin1@school.com';
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Cek apakah username atau email sudah ada
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->rowCount() > 0) {
        echo "Username atau email sudah digunakan!";
        exit;
    }
    
    // Insert user baru
    $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, email) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $hashed_password, $full_name, $email]);
    
    echo "Admin berhasil ditambahkan!<br>";
    echo "Username: " . $username . "<br>";
    echo "Password: " . $password;
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 
