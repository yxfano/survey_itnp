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

// Mendapatkan filter dari request
$reportType = isset($_GET['report_type']) ? $_GET['report_type'] : 'all';
$dateRange = isset($_GET['date_range']) ? $_GET['date_range'] : date('Y-m-01') . ' - ' . date('Y-m-t');
list($startDate, $endDate) = explode(' - ', $dateRange);

// Query dasar
$query = "SELECT * FROM surveys WHERE created_at BETWEEN '$startDate' AND '$endDate'";

// Menambahkan filter berdasarkan jenis laporan
if ($reportType != 'all') {
    $query .= " AND layanan = '$reportType'";
}

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$respondents = [];
while ($row = mysqli_fetch_assoc($result)) {
    $survey_id = $row['id'];
    $query_answers = "SELECT AVG(answer) as avg_rating FROM survey_answers WHERE survey_id = $survey_id";
    $result_answers = mysqli_query($conn, $query_answers);
    $avg_rating = mysqli_fetch_assoc($result_answers)['avg_rating'];
    
    $respondents[] = [
        'id' => $row['id'],
        'unit_kerja' => $row['unit_kerja'],
        'layanan' => $row['layanan'],
        'tanggal' => $row['tanggal'],
        'avg_rating' => $avg_rating,
        'feedback' => $row['feedback']
    ];
}

// Hitung total survey dan rata-rata keseluruhan berdasarkan filter
$survey_count = count($respondents);
$overall_average = array_sum(array_column($respondents, 'avg_rating')) / $survey_count;

// Fungsi untuk mendapatkan daftar responden survey
function get_survey_respondents($limit = 5) {
    global $pdo;
    $query = "
        SELECT s.id, s.unit_kerja, s.layanan, s.tanggal, 
               AVG(sa.answer) as avg_rating
        FROM surveys s
        JOIN survey_answers sa ON s.id = sa.survey_id
        GROUP BY s.id
        ORDER BY s.tanggal DESC
        LIMIT :limit
    ";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$avg_ratings = get_survey_respondents();

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Survey Layanan IT</title>
    <!-- <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet"> -->
    <!-- <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" /> -->
    <!-- <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script> -->
    <!-- <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script> -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->

    <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />


    <style>
        body {
            font-family: 'Roboto', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
        }
        .sidebar {
            width: 250px;
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
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        .card {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        .stat-item {
            text-align: center;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #3498db;
        }
        .stat-label {
            font-size: 14px;
            color: #777;
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
        .btn-details {
            background-color: #6c5ce7;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        input[type="text"], input[type="tel"], input[type="date"], select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .daterangepicker {
            z-index: 9999 !important;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="dashboard.php" class="active">Dashboard</a></li>
            <li><a href="manage_questions.php">Kelola Pertanyaan</a></li>
            <li><a href="download_results.php">Download Hasil Survey</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="container">
            <div class="header">
                <h1>Dashboard Admin</h1>
            </div>
            <div class="card">
                <form method="GET" action="">
                    <table>
                        <tr>
                            <td>
                                <select name="report_type" id="report_type" placeholder="Semua Laporan">
                                    <option value="all" <?= $reportType == 'all' ? 'selected' : '' ?>>Semua Laporan</option>
                                    <option value="Laptop" <?= $reportType == 'Laptop' ? 'selected' : '' ?>>Laporan Laptop</option>
                                    <option value="Printer" <?= $reportType == 'Printer' ? 'selected' : '' ?>>Laporan Printer</option>
                                    <option value="Penyedia_Layanan_IT" <?= $reportType == 'Penyedia_Layanan_IT' ? 'selected' : '' ?>>Laporan Penyedia Layanan IT</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="date_range" id="date_range" class="form-control">
                            </td>
                            <td>
                                <button type="submit">Filter</button>
                            </td>
                        </tr>
                    </table>
                </form>
                <h2>Responden Survey Terbaru</h2>

                <input type="text" name="daterange" value="" />


                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $survey_count; ?></div>
                        <div class="stat-label">Total Survey</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo number_format($overall_average, 2); ?></div>
                        <div class="stat-label">Rata-rata Keseluruhan</div>
                    </div>
                </div>
                <br>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Unit Kerja</th>
                            <th>Layanan</th>
                            <th>Tanggal</th>   
                            <th>Rata-rata Rating</th>
                            <th>Feedback</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($respondents as $respondent): ?>
                            <tr>
                                <td><?php echo $respondent['id']; ?></td>
                                <td><?php echo $respondent['unit_kerja']; ?></td>
                                <td><?php echo $respondent['layanan']; ?></td>
                                <td><?php echo $respondent['tanggal']; ?></td>
                                <td><?php echo number_format($respondent['avg_rating'], 2); ?></td>
                                <td>
                                    <?php
                                    $feedback = $respondent['feedback'];
                                    if (strlen($feedback) > 27) {
                                        echo substr($feedback, 0, 27) . '... ';
                                        echo '<a href="#" class="more-link" data-full-text="' . htmlspecialchars($feedback) . '">More</a>';
                                    } else {
                                        echo $feedback;
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var moreLinks = document.querySelectorAll('.more-link');
        moreLinks.forEach(function(link) {
            link.addEventListener('click', function(event) {
                event.preventDefault();
                var fullText = this.getAttribute('data-full-text');
                this.parentElement.innerHTML = fullText;
            });
        });
    });
    $(function() {
        $('#date_range').daterangepicker({
            locale: {
                format: 'YYYY-MM-DD'
            },
            startDate: '<?= $startDate ?>',
            endDate: '<?= $endDate ?>',
            singleDatePicker: false,
            showDropdowns: true,
            autoApply: true,
            opens: 'right',
            drops: 'down',
            linkedCalendars: false,
            showCustomRangeLabel: false,
            alwaysShowCalendars: true,
            singleCalendar: true
        });
    });
    </script>

<script>
$(function() {
  $('input[name="daterange"]').daterangepicker({
    opens: 'left'
  }, function(start, end, label) {
    console.log("A new date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));
  });
});
</script>


</body>
</html>
