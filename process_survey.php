<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $unit_kerja = sanitize_input($_POST['unit_kerja']);
    $layanan = sanitize_input($_POST['layanan']);
    $nomor_telepon = sanitize_input($_POST['nomor_telepon']);
    $tanggal = sanitize_input($_POST['tanggal']);
    $feedback = sanitize_input($_POST['feedback']);

    try {
        // Simpan data survey ke database
        $stmt = $pdo->prepare("INSERT INTO surveys (unit_kerja, layanan, nomor_telepon, tanggal, feedback) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$unit_kerja, $layanan, $nomor_telepon, $tanggal, $feedback]);
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
    } catch (PDOException $e) {
        error_log("Error in process_survey: " . $e->getMessage());
        echo "Terjadi kesalahan saat memproses survey. Silakan coba lagi nanti.";
    }
} else {
    // Jika bukan metode POST, redirect ke halaman utama
    header("Location: index.php");
    exit;
}
?>