<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

$survey_count = get_survey_count();
$avg_ratings = get_average_ratings();

// Hitung rata-rata keseluruhan
$overall_average = array_sum(array_column($avg_ratings, 'avg_rating')) / count($avg_ratings);

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

$respondents = get_survey_respondents();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Survey Layanan IT</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .target-sales {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .target-item {
            text-align: center;
        }
        .target-value {
            font-size: 24px;
            font-weight: bold;
        }
        .target-label {
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
        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pending { background-color: #ffeaa7; color: #fdcb6e; }
        .status-completed { background-color: #55efc4; color: #00b894; }
        .status-progress { background-color: #74b9ff; color: #0984e3; }
        .status-hold { background-color: #fab1a0; color: #e17055; }
        .btn-details {
            background-color: #6c5ce7;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
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
            <div class="dashboard-grid">
                <div class="card">
                    <h2>Rata-rata Nilai per Layanan</h2>
                    <canvas id="ratingChart"></canvas>
                    <div class="target-sales">
                        <?php foreach ($avg_ratings as $rating): ?>
                        <div class="target-item">
                            <div class="target-value"><?php echo number_format($rating['avg_rating'], 2); ?></div>
                            <div class="target-label"><?php echo $rating['layanan']; ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="card">
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
                </div>
            </div>
            
            <div class="card">
                <h2>Responden Survey Terbaru</h2>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Unit Kerja</th>
                            <th>Layanan</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Rata-rata Rating</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($respondents as $index => $respondent): ?>
                        <tr>
                            <td>#<?php echo $respondent['id']; ?></td>
                            <td><?php echo $respondent['unit_kerja']; ?></td>
                            <td><?php echo $respondent['layanan']; ?></td>
                            <td><?php echo $respondent['tanggal']; ?></td>
                            <td>
                                <span class="status status-<?php echo ['pending', 'completed', 'progress', 'hold'][array_rand([0,1,2,3])]; ?>">
                                    <?php echo ['PENDING', 'COMPLETED', 'IN PROGRESS', 'ON HOLD'][array_rand([0,1,2,3])]; ?>
                                </span>
                            </td>
                            <td><?php echo number_format($respondent['avg_rating'], 2); ?></td>
                            <td><button class="btn-details">Details</button></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    var ctx = document.getElementById('ratingChart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($avg_ratings, 'layanan')); ?>,
            datasets: [{
                label: 'Rata-rata Nilai',
                data: <?php echo json_encode(array_column($avg_ratings, 'avg_rating')); ?>,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.6)',
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(255, 206, 86, 0.6)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    max: 10
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += context.parsed.y.toFixed(2);
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
    </script>
</body>
</html>