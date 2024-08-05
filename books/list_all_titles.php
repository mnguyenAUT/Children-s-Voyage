<?php
require_once 'config.php';

// Establish a database connection using PDO
try {
    $pdo = new PDO($dsn, $databaseUser, $databasePassword, $options);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Build the SQL query for fetching the books and counting total books
    $sqlFetch = "SELECT DISTINCT REPLACE(REPLACE(SUBSTRING_INDEX(`text`, '/', -1), '-', ' '), '.txt', '') as title FROM books WHERE 1 ORDER BY title";

    // Prepare and execute the statement
    $stmt = $pdo->prepare($sqlFetch);  
    $stmt->execute();

    // Get the total number of books
    $totalBooks = $stmt->rowCount();

    // Fetch all titles
    $titles = $stmt->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Books</title>
</head>
<body>
    <h1>Total Books: <?php echo $totalBooks; ?></h1>
    <h2>Titles:</h2>
    
        <?php foreach ($titles as $title): ?>
            <?php echo htmlspecialchars($title); ?></br>
        <?php endforeach; ?>
    
</body>
</html>

