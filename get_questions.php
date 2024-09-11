<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/db.php';

header('Content-Type: application/json');

if (isset($_GET['layanan'])) {
    $layanan = sanitize_input($_GET['layanan']);
    
    try {
        $stmt = $pdo->prepare("SELECT id, text FROM questions WHERE layanan = ?");
        $stmt->execute([$layanan]);
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($questions);
    } catch (PDOException $e) {
        error_log("Error in get_questions: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Terjadi kesalahan saat mengambil pertanyaan.']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Parameter layanan tidak ditemukan.']);
}
?>