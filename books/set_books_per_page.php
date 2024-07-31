<?php
// Start session to store booksPerPage value
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the booksPerPage value from the AJAX request
    $booksPerPage = isset($_POST['booksPerPage']) ? (int)$_POST['booksPerPage'] : 9;

    // Store booksPerPage in session or another appropriate place
    $_SESSION['booksPerPage'] = $booksPerPage;

    // Respond with success
    echo json_encode(['status' => 'success']);
}
?>

