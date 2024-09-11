<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $unit_kerja = sanitize_input($_POST['unit_kerja']);
    $layanan = sanitize_input($_POST['layanan']);
    $nomor_telepon = sanitize_input($_POST['nomor_telepon']);
    $tanggal = sanitize_input($_POST['tanggal']);

    // Simpan data survey ke database
    $stmt = $pdo->prepare("INSERT INTO surveys (unit_kerja, layanan, nomor_telepon, tanggal) VALUES (?, ?, ?, ?)");
    $stmt->execute([$unit_kerja, $layanan, $nomor_telepon, $tanggal]);
    $survey_id = $pdo->lastInsertId();

    // Simpan jawaban survey
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'q') === 0) {
            $question_id = substr($key, 1);
            $answer = intval($value);
            $stmt = $pdo->prepare("INSERT INTO survey_answers (survey_id, question_id, answer) VALUES (?, ?, ?)");
            $stmt->execute([$survey_id, $question_id, $answer]);
        }
    }

    // Redirect ke halaman terima kasih
    header("Location: thank_you.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proses Survey - Survey Layanan IT</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>Memproses Survey</h1>
            <p>Mohon tunggu sebentar, survey Anda sedang diproses...</p>
        </div>
    </div>
</body>
</html>