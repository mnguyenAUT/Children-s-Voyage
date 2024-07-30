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

?>

