<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey Layanan IT</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #6ebad4, #ffffff);
            margin: 0;
            padding: 20px;
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
            background-color: #D3D3D3;
        }
        input[type="text"], input[type="tel"], input[type="date"], select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 25px;
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
            border-radius: 25px;
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
        .indikator {
            font-size: 12px;
            line-height: 0.7;
            text-align: center;
        }
        .card {
            background-color: #fff;
            border-radius: 25px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
    </style>
</head>
<body>
        <div class="card">
            <h1>Survei Kepuasan Pelayanan<br>Tim IT Non Public Service</h1>
            <p style="text-align: justify;">Survei ini dirancang untuk membantu kami sebagai tim ITNPS memahami pengalaman Anda saat menggunakan layanan kami. Feedback Anda sangat berharga bagi kami untuk terus meningkatkan kualitas layanan. Survei ini akan memakan waktu sekitar 2-3 menit untuk diselesaikan. Jawaban Anda akan digunakan untuk keperluan evaluasi dan pengembangan layanan ITNPS.</p>
            <p style="text-align: justify;">Kami mengucapkan terima kasih atas partisipasi Anda dalam survei ini. Umpan balik Anda akan membantu kami memberikan layanan yang lebih baik di masa mendatang.</p>
        </div>
        <form id="survey-form" action="process_survey.php" method="POST">
            <div class="card">
                <div class="form-group">
                    <label for="unit_kerja">Unit Kerja:</label>
                    <input type="text" id="unit_kerja" name="unit_kerja" placeholder="Masukkan Unit Kerja Anda" required>
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
            </div>
            <div class="card">
                <div class="form-group indikator">
                    <label for="indikator">Indikator Penilaian:</label>
                    <p><i>1-2 Sangat Buruk   3-4 Buruk   5-6 Netral   7-8 Baik   9-10 Sangat Baik</i></p>
                </div>
                <div id="survey-questions"></div>
            </div>
            <div class="card">
                <h2>Kritik dan Saran</h2>
                <div class="form-group">
                    <textarea id="feedback" name="feedback" placeholder="Tuliskan kritik dan saran Anda di sini..."></textarea>
                </div>
                <button type="submit">Kirim Survey</button>
            </div>
        </form>
    <script src="assets/js/survey.js"></script>
</body>
</html>
