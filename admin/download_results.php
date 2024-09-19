<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

// Fungsi untuk mendapatkan semua hasil survey
function get_all_survey_results() {
    global $pdo;
    $query = "
        SELECT s.id, s.unit_kerja, s.layanan, s.nomor_telepon, s.tanggal, s.feedback,
               q.text as question, sa.answer
        FROM surveys s
        JOIN survey_answers sa ON s.id = sa.survey_id
        JOIN questions q ON sa.question_id = q.id
        ORDER BY s.id, q.id
    ";
    $stmt = $pdo->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Jika form disubmit, generate dan download CSV
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['download'])) {
    $results = get_all_survey_results();
    
    // Set header untuk download file
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="hasil_survey_' . date('Y-m-d') . '.csv"');
    
    // Buka output stream
    $output = fopen('php://output', 'w');
    
    // Tambahkan BOM (Byte Order Mark) untuk UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Tulis header CSV
    fputcsv($output, array('ID Survey', 'Unit Kerja', 'Layanan', 'Nomor Telepon', 'Tanggal', 'Pertanyaan', 'Jawaban', 'Feedback'));
    
    // Tulis data ke CSV
    foreach ($results as $row) {
        fputcsv($output, array(
            $row['id'],
            $row['unit_kerja'],
            $row['layanan'],
            $row['nomor_telepon'],
            $row['tanggal'],
            $row['question'],
            $row['answer'],
            $row['feedback']
        ));
    }
    
    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Hasil Survey - Survey Layanan IT</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            font-size:12px;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
        }
        .sidebar {
            width: 15%;
            background-color: #2c3e50;
            color: #ecf0f1;
            padding: 20px;
            height: 100vh;
            position: fixed;
        }
        .sidebar h2 {
            color: #ecf0f1;
            margin-bottom: 20px;
        }
        .sidebar ul {
            list-style-type: none;
            padding: 0;
        }
        .sidebar ul li {
            margin-bottom: 10px;
        }
        .sidebar ul li a {
            color: #ecf0f1;
            text-decoration: none;
            display: block;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .sidebar ul li a:hover, .sidebar ul li a.active {
            background-color: #34495e;
        }
        .main-content {
            flex: 1;
            padding: 20px;
            margin-left: 250px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1, h2 {
            color: #333;
            margin-top: 0;
        }
        .card {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .btn {
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="data_kuesioner.php">Data Kuesioner</a></li>
            <li><a href="manage_questions.php">Kelola Pertanyaan</a></li>
            <li><a href="presentase_kuesioner.php">Presentase Kuesioner</a></li>
            <li><a href="download_results.php" class="active">Download Hasil Survey</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="container">
            <div class="header">
                <h1>Download Hasil Survey</h1>
            </div>
            <div class="card">
                <h2>Unduh Hasil Survey</h2>
                <p>Klik tombol di bawah ini untuk mengunduh hasil survey dalam format CSV.</p>
                <form method="POST">
                    <button type="submit" name="download" class="btn">Download Hasil Survey</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>