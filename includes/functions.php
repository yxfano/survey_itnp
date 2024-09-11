<?php
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function authenticate_admin($username, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        return true;
    }
    return false;
}

function get_survey_count() {
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) FROM surveys");
    return $stmt->fetchColumn();
}

function get_average_ratings() {
    global $pdo;
    try {
        $stmt = $pdo->query("
            SELECT 
                s.layanan,
                AVG(sa.answer) as avg_rating,
                COUNT(DISTINCT s.id) as count
            FROM 
                surveys s
            LEFT JOIN 
                survey_answers sa ON s.id = sa.survey_id
            GROUP BY 
                s.layanan
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error in get_average_ratings: " . $e->getMessage());
        return array();
    }
}
// Add more functions as needed