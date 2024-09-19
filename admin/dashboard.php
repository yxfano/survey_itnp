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

// Fungsi untuk mendapatkan data survei berdasarkan layanan dan rentang tanggal
function getSurveyData($layanan_filter, $start_date, $end_date) {
    global $conn;

    $where_clause = "WHERE 1=1";
    if ($layanan_filter && $layanan_filter != 'all') {
        $where_clause .= " AND surveys.layanan = '$layanan_filter'";
    }
    if ($start_date && $end_date) {
        $where_clause .= " AND surveys.created_at BETWEEN '$start_date' AND '$end_date'";
    }

    // Mengambil data dari tabel surveys
    $sql = "SELECT COUNT(*) as total_surveys FROM surveys $where_clause";
    $result = $conn->query($sql);
    $total_surveys = $result->num_rows > 0 ? $result->fetch_assoc()['total_surveys'] : 0;

    // Mengambil data dari tabel survey_answers
    $sql = "SELECT COUNT(*) as positive_feedback FROM survey_answers 
            JOIN surveys ON survey_answers.survey_id = surveys.id 
            $where_clause AND survey_answers.answer >= 7";
    $result = $conn->query($sql);
    $positive_feedback = $result->num_rows > 0 ? $result->fetch_assoc()['positive_feedback'] : 0;

    $sql = "SELECT COUNT(*) as negative_feedback FROM survey_answers 
            JOIN surveys ON survey_answers.survey_id = surveys.id 
            $where_clause AND survey_answers.answer < 7";
    $result = $conn->query($sql);
    $negative_feedback = $result->num_rows > 0 ? $result->fetch_assoc()['negative_feedback'] : 0;

    // Mengambil rata-rata kepuasan dari tabel survey_answers
    $sql = "SELECT AVG(survey_answers.answer) as average_satisfaction FROM survey_answers 
            JOIN surveys ON survey_answers.survey_id = surveys.id 
            $where_clause";
    $result = $conn->query($sql);
    $average_satisfaction = $result->num_rows > 0 ? $result->fetch_assoc()['average_satisfaction'] : 0;

    // Mengambil data untuk pie chart
    $sql = "SELECT survey_answers.answer, COUNT(*) as count FROM survey_answers 
            JOIN surveys ON survey_answers.survey_id = surveys.id 
            $where_clause GROUP BY survey_answers.answer";
    $result = $conn->query($sql);
    $ratings = [0, 0, 0, 0, 0];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $answer = $row['answer'];
            if ($answer >= 9) {
                $ratings[0] += $row['count'];
            } elseif ($answer >= 7) {
                $ratings[1] += $row['count'];
            } elseif ($answer >= 5) {
                $ratings[2] += $row['count'];
            } elseif ($answer >= 3) {
                $ratings[3] += $row['count'];
            } else {
                $ratings[4] += $row['count'];
            }
        }
    }

    return [
        'total_surveys' => $total_surveys,
        'positive_feedback' => $positive_feedback,
        'negative_feedback' => $negative_feedback,
        'average_satisfaction' => $average_satisfaction,
        'ratings' => $ratings
    ];
}

// Mendapatkan filter layanan dan rentang tanggal dari query string
$layanan_filter = isset($_GET['layanan']) ? $_GET['layanan'] : 'all';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$data = getSurveyData($layanan_filter, $start_date, $end_date);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            font-size: 14px;
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
        .card h3 {
            margin-top: 0;
        }
        .metrics {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .metric {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            flex: 1;
            margin-right: 20px;
            text-align: center;
        }
        .metric:last-child {
            margin-right: 0;
        }
        .metric h3 {
            margin-top: 0;
            font-size: 24px;
        }
        .metric p {
            font-size: 18px;
            margin: 10px 0 0;
        }
        .chart {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
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
        .date-filter {
            margin-bottom: 20px;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(function() {
            $("#start_date, #end_date").datepicker({
                dateFormat: 'yy-mm-dd'
            });

            // Event listener untuk filter tanggal
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();
                const layanan = '<?php echo $layanan_filter; ?>';
                window.location.href = `dashboard.php?layanan=${layanan}&start_date=${startDate}&end_date=${endDate}`;
            });
        });

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

        function renderPieChart(data) {
            const ctx = document.getElementById('satisfactionPieChart').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Sangat Baik/Sangat Puas (9-10)', 'Baik/Puas (7-8)', 'Cukup Baik/Cukup Puas (5-6)', 'Tidak Baik/Tidak Puas (3-4)', 'Sangat Tidak Baik/Sangat Tidak Puas (1-2)'],
                    datasets: [{
                        data: data,
                        backgroundColor: ['#2ecc71', '#3498db', '#f1c40f', '#e67e22', '#e74c3c'],
                        borderColor: ['#27ae60', '#2980b9', '#f39c12', '#d35400', '#c0392b'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    return tooltipItem.label + ': ' + tooltipItem.raw + ' responses';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Data untuk pie chart
        const pieData = <?php echo json_encode($data['ratings']); ?>;
        renderPieChart(pieData);
    </script>
</head>
<body>
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="dashboard.php?layanan=all" class="<?php echo $layanan_filter == 'all' ? 'active' : ''; ?>">All</a></li>
            <li><a href="dashboard.php?layanan=Laptop" class="<?php echo $layanan_filter == 'Laptop' ? 'active' : ''; ?>">Laptop</a></li>
            <li><a href="dashboard.php?layanan=Printer" class="<?php echo $layanan_filter == 'Printer' ? 'active' : ''; ?>">Printer</a></li>
            <li><a href="dashboard.php?layanan=Penyedia_Layanan_IT" class="<?php echo $layanan_filter == 'Penyedia_Layanan_IT' ? 'active' : ''; ?>">Penyedia Layanan IT</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="container">
            <div class="header">
                <h1>Dashboard</h1>
            </div>
            <div class="date-filter">
                <form id="filterForm">
                    <label for="start_date">Start Date:</label>
                    <input type="text" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                    <label for="end_date">End Date:</label>
                    <input type="text" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                    <button type="submit">Filter</button>
                </form>
            </div>
            <div class="metrics">
                <div class="metric">
                    <h3><?php echo $data['total_surveys']; ?></h3>
                    <p>Total Surveys</p>
                </div>
                <div class="metric">
                    <h3><?php echo round($data['average_satisfaction'], 2); ?></h3>
                    <p>Average Satisfaction</p>
                </div>
                <div class="metric">
                    <h3><?php echo $data['positive_feedback']; ?></h3>
                    <p>Positive Feedback</p>
                </div>
                <div class="metric">
                    <h3><?php echo $data['negative_feedback']; ?></h3>
                    <p>Negative Feedback</p>
                </div>
            </div>
            <div class="chart">
                <canvas id="satisfactionChart"></canvas>
            </div>
            <div class="chart">
                <canvas id="satisfactionPieChart"></canvas>
            </div>
        </div>
    </div>
</body>
</html>
