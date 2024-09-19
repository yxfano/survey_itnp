<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/db.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize_input($_POST['username']);
    $password = $_POST['password'];

    error_log("Login attempt - Username: $username");

    try {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            error_log("Admin found in database. Stored password hash: " . $admin['password']);
            if (password_verify($password, $admin['password'])) {
                error_log("Password verified successfully");
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                header('Location: admin/dashboard.php');
                exit;
            } else {
                error_log("Password verification failed. Input password: $password");
                $error = "Username atau password salah.";
            }
        } else {
            error_log("Admin not found in database");
            $error = "Username atau password salah.";
        }
    } catch (PDOException $e) {
        error_log("Login Error: " . $e->getMessage());
        $error = "Terjadi kesalahan. Silakan coba lagi nanti.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Survey Layanan IT</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 25px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            width: 300px;
            text-align: center;
        }
        h1 {
            color: #3498db;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 25px;
            font-size: 16px;
        }
        .btn {
            background-color: #3498db;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .error {
            color: #e74c3c;
            margin-bottom: 15px;
        }
        .back-link {
            margin-top: 20px;
        }
        .back-link a {
            color: #3498db;
            text-decoration: none;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Login Admin</h1>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form action="" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        <div class="back-link">
            <a href="index.php">Kembali ke Halaman Utama</a>
        </div>
    </div>
</body>
</html>
