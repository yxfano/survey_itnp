<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

// Pastikan Anda sudah menghubungkan ke database
$conn = mysqli_connect("localhost", "root", "", "survey_itnp");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

function displaySurveyResults($layanan_filter) {
    global $conn;

    // Query untuk mendapatkan total jawaban per pertanyaan
    $sql = "SELECT question_id, COUNT(*) as total_responses, SUM(answer) as total_score FROM survey_answers GROUP BY question_id";
    $result = $conn->query($sql);

    $questions = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $questions[$row['question_id']] = [
                'total_responses' => $row['total_responses'],
                'total_score' => $row['total_score']
            ];
        }
    }

    // Query untuk mendapatkan teks pertanyaan
    $sql = "SELECT id, layanan, text FROM questions WHERE layanan = '$layanan_filter'";
    $result = $conn->query($sql);

    echo "<table>";
    echo "<thead><tr><th>Pertanyaan</th><th>Persentase Jawaban</th></tr></thead>";
    echo "<tbody>";

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $question_id = $row['id'];
            if (isset($questions[$question_id])) {
                $total_responses = $questions[$question_id]['total_responses'];
                $total_score = $questions[$question_id]['total_score'];
                $average_score = $total_score / $total_responses;
                $percentage = ($average_score / 10) * 100; // Asumsi skala jawaban 1-10

                echo "<tr>";
                echo "<td>" . $row['text'] . "</td>";
                echo "<td>" . round($percentage, 2) . "%</td>";
                echo "</tr>";
            }
        }
    } else {
        echo "<tr><td colspan='2'>Tidak ada pertanyaan yang ditemukan.</td></tr>";
    }

    echo "</tbody>";
    echo "</table>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Results</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #6ebad4, #ffffff);
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
        .content {
            background-color: #fff;
            padding: 20px;
            border-radius: 25px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .tabs {
            display: flex;
            cursor: pointer;
            margin-bottom: 20px;
            border-bottom: 1px solid #ccc;
        }
        .tab {
            padding: 10px 20px;
            border: 1px solid #ccc;
            border-bottom: none;
            background-color: #f2f2f2;
            margin-right: 5px;
            border-radius: 5px 5px 0 0;
        }
        .tab.active {
            background-color: #fff;
            border-bottom: 1px solid #fff;
        }
        .tab-content {
            display: none;
            border: 1px solid #ccc;
            padding: 20px;
            background-color: #fff;
            border-radius: 0 0 5px 5px;
        }
        .tab-content.active {
            display: block;
        }
    </style>
    <script>
        function showTab(tabId) {
            const tabs = document.querySelectorAll('.tab');
            const contents = document.querySelectorAll('.tab-content');
            tabs.forEach(tab => tab.classList.remove('active'));
            contents.forEach(content => content.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            document.querySelector(`[data-tab="${tabId}"]`).classList.add('active');
        }

        window.onload = function() {
            showTab('tab1'); // Default tab
        }
    </script>
</head>
<body>
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="data_kuesioner.php">Data Kuesioner</a></li>
            <li><a href="manage_questions.php">Kelola Pertanyaan</a></li>
            <li><a href="presentase_kuesioner.php" class="active">Presentase Kuesioner</a></li>
            <li><a href="rekapitulasi_bulanan.php">Rekapitulasi Bulanan</a></li>            
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="container">
            <div class="header">
                <h1>Presentase Kuesioner</h1>
            </div>
            <div class="card">
                <div class="tabs">
                    <div class="tab" data-tab="tab1" onclick="showTab('tab1')">Laptop</div>
                    <div class="tab" data-tab="tab2" onclick="showTab('tab2')">Printer</div>
                    <div class="tab" data-tab="tab3" onclick="showTab('tab3')">Penyedia Layanan IT</div>
                </div>
                <div id="tab1" class="tab-content">
                    <?php displaySurveyResults('Laptop'); ?>
                </div>
                <div id="tab2" class="tab-content">
                    <?php displaySurveyResults('Printer'); ?>
                </div>
                <div id="tab3" class="tab-content">
                    <?php displaySurveyResults('Penyedia_Layanan_IT'); ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
