<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Layanan IT</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #3498db;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .tanggal {
            background-color: 	#D3D3D3;
        }
        input[type="text"], input[type="tel"], input[type="date"], select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        button {
            background-color: #3498db;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        button:hover {
            background-color: #2980b9;
        }
        .error {
            color: #e74c3c;
            margin-top: 5px;
        }
        .admin-login {
            text-align: right;
            margin-bottom: 20px;
        }
        .admin-login a {
            background-color: #2ecc71;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }
        .admin-login a:hover {
            background-color: #27ae60;
        }
        .indikator {
            font-size: 12px;
            line-height: 0.7;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="admin-login">
            <a href="login.php">Login Admin</a>
        </div>
        <h1>Survei Kepuasan Pelayanan Tim IT Non Public Service</h1>
        <form id="survey-form" action="process_survey.php" method="POST">
            <div class="form-group">
                <label for="unit_kerja">Unit Kerja:</label>
                <input type="text" id="unit_kerja" name="unit_kerja" required>
            </div>
            <div class="form-group">
                <label for="layanan">Layanan:</label>
                <select id="layanan" name="layanan" required>
                    <option value="">Pilih Layanan</option>
                    <option value="Printer">Printer</option>
                    <option value="Laptop">Laptop</option>
                    <option value="Penyedia_Layanan_IT">Penyedia Layanan IT</option>
                </select>
            </div>
            <div class="form-group">
                <label for="tanggal">Tanggal:</label>
                <input class="tanggal" type="date" id="surveyDate" name="tanggal" readonly>
            </div>
            <div class="form-group indikator">
                <label for="indikator">Indikator Penilaian:</label>
                <p><i>1-2 Sangat Buruk &nbsp 3-4 Buruk &nbsp 5-6 Netral &nbsp 7-8 Baik &nbsp 9-10 Sangat Baik</i></p>
            </div>
            <div id="survey-questions"></div>
            
            <h2>Kritik dan Saran</h2>
            <div class="form-group">
                <label for="feedback">Kritik dan Saran:</label>
                <textarea id="feedback" name="feedback" placeholder="Tuliskan kritik dan saran Anda di sini..."></textarea>
            </div>
            
            <button type="submit">Kirim Survey</button>
        </form>
    </div>
    <script src="assets/js/survey.js"></script>
</body>
</html>