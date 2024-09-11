<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

$new_password = 'admin123'; // Ganti dengan password yang Anda inginkan
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username = 'admin'");
    $stmt->execute([$hashed_password]);
    echo "Password admin berhasil direset. New password: $new_password";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>