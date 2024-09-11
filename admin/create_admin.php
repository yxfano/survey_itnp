<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

$username = 'admin'; // Ganti dengan username yang Anda inginkan
$password = 'admin123'; // Ganti dengan password yang kuat

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $hashed_password]);
    echo "Admin berhasil ditambahkan!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>