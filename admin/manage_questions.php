<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

// Fungsi untuk mendapatkan semua pertanyaan
function get_all_questions() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM questions ORDER BY layanan, id");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $layanan = sanitize_input($_POST['layanan']);
                $text = sanitize_input($_POST['text']);
                $stmt = $pdo->prepare("INSERT INTO questions (layanan, text) VALUES (?, ?)");
                $stmt->execute([$layanan, $text]);
                break;
            case 'edit':
                $id = intval($_POST['id']);
                $layanan = sanitize_input($_POST['layanan']);
                $text = sanitize_input($_POST['text']);
                $stmt = $pdo->prepare("UPDATE questions SET layanan = ?, text = ? WHERE id = ?");
                $stmt->execute([$layanan, $text, $id]);
                break;
            case 'delete':
                $id = intval($_POST['id']);
                $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
                $stmt->execute([$id]);
                break;
        }
    }
}

$questions = get_all_questions();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pertanyaan - Survey Layanan IT</title>
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
        .btn {
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .btn-edit {
            background-color: #2ecc71;
        }
        .btn-delete {
            background-color: #e74c3c;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        input[type="text"], select, textarea {
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="data_kuesioner.php">Data Kuesioner</a></li>
            <li><a href="manage_questions.php" class="active">Kelola Pertanyaan</a></li>
            <li><a href="presentase_kuesioner.php">Presentase Kuesioner</a></li>
            <li><a href="rekapitulasi_bulanan.php">Rekapitulasi Bulanan</a></li>            
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="container">
            <div class="header">
                <h1>Kelola Pertanyaan Survey</h1>
            </div>
            <div class="card">
                <h2>Tambah Pertanyaan Baru</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <select name="layanan" required>
                        <option value="">Pilih Layanan</option>
                        <option value="Printer">Printer</option>
                        <option value="Laptop">Laptop</option>
                        <option value="Penyedia_Layanan_IT">Penyedia Layanan IT</option>
                    </select>
                    <textarea name="text" placeholder="Masukkan pertanyaan" required></textarea>
                    <button type="submit" class="btn">Tambah Pertanyaan</button>
                </form>
            </div>
            <div class="card">
                <h2>Daftar Pertanyaan</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Layanan</th>
                            <th>Pertanyaan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($questions as $question): ?>
                        <tr>
                            <td><?= htmlspecialchars($question['id']) ?></td>
                            <td><?= htmlspecialchars($question['layanan']) ?></td>
                            <td><?= htmlspecialchars($question['text']) ?></td>
                            <td>
                                <button onclick="editQuestion(<?= $question['id'] ?>)" class="btn btn-edit">Edit</button>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $question['id'] ?>">
                                    <button type="submit" onclick="return confirm('Anda yakin ingin menghapus pertanyaan ini?')" class="btn btn-delete">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    function editQuestion(id) {
        // Implementasi fungsi edit
        console.log('Edit question with id:', id);
    }
    </script>
</body>
</html>