<?php
require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=" . '172.17.8.110' . ";dbname=" . 'survey_itnp', 'root', 'Adm1nAdm1n');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}