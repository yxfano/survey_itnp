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

// Mendapatkan bulan dan tahun saat ini
$current_month = date('n');
$current_year = date('Y');

// Mendapatkan data real-time dari database
$sql = "SELECT COUNT(*) as total_respondents FROM surveys WHERE MONTH(created_at) = $current_month AND YEAR(created_at) = $current_year";
$result = $conn->query($sql);
$total_respondents = $result->num_rows > 0 ? intval($result->fetch_assoc()['total_respondents']) : 0;

$sql = "SELECT AVG(answer) as average_satisfaction FROM survey_answers 
        JOIN surveys ON survey_answers.survey_id = surveys.id 
        WHERE MONTH(surveys.created_at) = $current_month AND YEAR(surveys.created_at) = $current_year";
$result = $conn->query($sql);
$average_satisfaction = $result->num_rows > 0 ? round($result->fetch_assoc()['average_satisfaction'] * 10, 1) : 0;

$sql = "SELECT COUNT(*) as respondents, layanan, AVG(answer) as average_satisfaction 
        FROM survey_answers 
        JOIN surveys ON survey_answers.survey_id = surveys.id 
        WHERE MONTH(surveys.created_at) = $current_month AND YEAR(surveys.created_at) = $current_year 
        GROUP BY layanan";
$result = $conn->query($sql);
$sub_units = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['average_satisfaction'] = round($row['average_satisfaction'] * 10, 1);
        $row['respondents'] = intval($row['respondents']); // Pastikan jumlah responden adalah integer
        $sub_units[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Kepuasan Layanan ITNP</title>
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
        .content {
            background-color: #fff;
            padding: 20px;
            border-radius: 25px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .copy-button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2em;
            color: #333;
            float:right;
        }
        .copy-button:hover {
            color: #007bff;
        }
        .copy-button:focus {
            outline: none;
        }
        pre {
            font-size:14px;
        }
    </style>
    <script>
        function copyText() {
            const text = document.querySelector('.content pre').innerText;
            navigator.clipboard.writeText(text).then(() => {
                alert('Text copied to clipboard');
            }).catch(err => {
                console.error('Failed to copy text: ', err);
            });
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
            <li><a href="presentase_kuesioner.php">Presentase Kuesioner</a></li>
            <li><a href="rekapitulasi_bulanan.php" class="active">Rekapitulasi Bulanan</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="container">
            <div class="content">
                <h1>Rekapitulasi Kepuasan Layanan ITNP</h1>
            </div>
            <br>
            <div class="content">
                <button class="copy-button" onclick="copyText()"><i class="fas fa-copy"></i></button>
                <pre>
*REKAPTULASI KUMULATIF*
*HASIL TINGKAT KEPUASAN LAYANAN ITNP*
*BULAN <?php echo strtoupper(date('F Y')); ?>*
*=====================*
*Data Update:* <?php echo date('d F Y'); ?>
*Jumlah Responden:* <?php echo $total_respondents; ?>

Skala tingkat kepuasan [metode likert]
9-10 (Sangat Baik/Sangat Puas)
7-8 (Baik/Puas)
5-6 (Cukup Baik/Cukup Puas)
3-4 (Tidak Baik/Tidak Puas)
1-2 (Sangat Tidak Baik/Sangat Tidak Puas)

*=====================*
*HASIL NILAI SEMENTARA*

GENERAL NILAI ITNP
PRESENTASE: <?php echo $average_satisfaction; ?> %	
TINGKAT KEPUASAN: <?php echo $average_satisfaction >= 90 ? 'Sangat Baik/Sangat Puas' : ($average_satisfaction >= 70 ? 'Baik/Puas' : ($average_satisfaction >= 50 ? 'Cukup Baik/Cukup Puas' : ($average_satisfaction >= 30 ? 'Tidak Baik/Tidak Puas' : 'Sangat Tidak Baik/Sangat Tidak Puas'))); ?>

*=====================*
*SUB UNIT*
<?php foreach ($sub_units as $unit): ?>
[OM <?php echo strtoupper($unit['layanan']); ?>]
RESPONDEN: <?php echo $unit['respondents']; ?>

PRESENTASE: <?php echo $unit['average_satisfaction']; ?> %
TINGKAT KEPUASAN: <?php echo $unit['average_satisfaction'] >= 90 ? 'Sangat Baik/Sangat Puas' : ($unit['average_satisfaction'] >= 70 ? 'Baik/Puas' : ($unit['average_satisfaction'] >= 50 ? 'Cukup Baik/Cukup Puas' : ($unit['average_satisfaction'] >= 30 ? 'Tidak Baik/Tidak Puas' : 'Sangat Tidak Baik/Sangat Tidak Puas'))); ?>
<?php endforeach; ?>
                </pre>
            </div>
        </div>
    </div>
</body>
</html>
