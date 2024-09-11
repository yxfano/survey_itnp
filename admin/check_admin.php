<?php
require_once('../includes/config.php');
require_once('../includes/db.php');

try {
    $stmt = $pdo->query("SELECT id, username FROM admins");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($admins)) {
        echo "Tidak ada admin dalam database.<br>";
        echo "Menambahkan admin default...<br>";
        
        $username = 'admin';
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $password]);
        
        echo "Admin default ditambahkan. Username: admin, Password: admin123";
    } else {
        echo "Daftar admin:<br>";
        foreach ($admins as $admin) {
            echo "ID: " . $admin['id'] . ", Username: " . $admin['username'] . "<br>";
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>