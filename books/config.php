<?php
// config.php

// Database connection parameters
$databaseHost = 'localhost';
$databaseName = 'ECMS_TA'; // Replace with your database name
$databaseUser = 'ecms'; // Replace with your database username
$databasePassword = 'Ecms-1234@'; // Replace with your database password
$databaseCharset = 'utf8mb4';
$tableBooks = 'books'; // Table name

$dsn = "mysql:host=$databaseHost;dbname=$databaseName;charset=$databaseCharset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];


try {
    $pdo = new PDO($dsn, $databaseUser, $databasePassword, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Function to get the real IP address of the visitor
function getRealIpAddr() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

// Function to get the duration of stay on the page
function getDuration($startTime) {
    return time() - $startTime;
}

// Start the session to track session id and visit duration
session_start();
if (!isset($_SESSION['start_time'])) {
    $_SESSION['start_time'] = time();
}

$ip_address = getRealIpAddr();
$page_visited = $_SERVER['REQUEST_URI'];
$stay_duration = getDuration($_SESSION['start_time']);
$user_agent = $_SERVER['HTTP_USER_AGENT'];
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
$session_id = session_id();

// Insert visitor data into the database
$sql = "INSERT INTO visitor_statistics (ip_address, page_visited, stay_duration, user_agent, referer, session_id)
        VALUES (:ip_address, :page_visited, :stay_duration, :user_agent, :referer, :session_id)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':ip_address' => $ip_address,
    ':page_visited' => $page_visited,
    ':stay_duration' => $stay_duration,
    ':user_agent' => $user_agent,
    ':referer' => $referer,
    ':session_id' => $session_id
]);

// Optional: To debug, you can print the confirmation message
//echo "Visitor data recorded successfully.";

?>

