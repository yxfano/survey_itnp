<?php
require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=" . 'localhost' . ";dbname=" . 'survey_itnp', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}