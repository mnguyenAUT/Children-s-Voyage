<?php
// Include the database configuration file
require_once 'config.php';

// Define the base URL to replace the file path
$baseURL = "https://cv.aut.ac.nz/";
$basePath = "/var/www/html/moodle/";

// Set the number of books per page
$booksPerPage = 16;

// Get the current page from the URL, default is 1
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the offset for the SQL query
$offset = ($currentPage - 1) * $booksPerPage;

// Get the search query if available
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

try {
    // Establish a database connection using PDO
    $pdo = new PDO($dsn, $databaseUser, $databasePassword, $options);

    // Fetch the total number of books matching the search query
    if ($searchQuery) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM books WHERE REPLACE(text, '-', ' ') LIKE :search");
        $stmt->execute([':search' => '%' . $searchQuery . '%']);
    } else {
        $stmt = $pdo->query("SELECT COUNT(*) FROM books");
    }
    $totalBooks = $stmt->fetchColumn();

    // Fetch the books for the current page matching the search query, ordered randomly
    if ($searchQuery) {
        $stmt = $pdo->prepare("SELECT * FROM books WHERE REPLACE(text, '-', ' ') LIKE :search ORDER BY RAND() LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':search', '%' . $searchQuery . '%', PDO::PARAM_STR);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM books ORDER BY RAND() LIMIT :limit OFFSET :offset");
    }
    $stmt->bindValue(':limit', $booksPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $books = $stmt->fetchAll();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

// Calculate the total number of pages
$totalPages = ceil($totalBooks / $booksPerPage);

// Determine if this is an AJAX request
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($isAjax) {
    // Only output the books and pagination for AJAX requests
    include 'book_gallery_partial.php';
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Children's Voyage</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <script>
        function searchBooks() {
            const query = document.getElementById('searchInput').value;

            const xhr = new XMLHttpRequest();
            xhr.open('GET', `gallery.php?search=${query}`, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.getElementById('booksContainer').innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('input', searchBooks);
            }
        });
    </script>
    
</head>
<body>
<div class="container mt-5">
    
    <div id="headerContainer" class="d-flex justify-content-between align-items-center mb-4">
        <?php if (!isset($_GET['search'])): ?>
            <h1><a href="gallery.php">Children's Voyage</a></h1>

            <input type="text" id="searchInput" class="form-control w-50 ml-3" placeholder="Search by name...">
        <?php endif; ?>
    </div>
    
    <?php if (!isset($_GET['search'])): ?>
    <div class="pagination-arrows d-flex justify-content-center align-items-center mt-4">
    <a href="?page=<?php echo max(1, $currentPage - 1); ?>" class="<?php echo ($currentPage == 1) ? 'disabled' : ''; ?>"><h1>&larr;</h1></a>
    <span class="mx-3">
        Page <strong><?php echo $currentPage; ?></strong> of <strong><?php echo $totalPages; ?></strong> | Each page shows up to <strong>16</strong> books
    </span>    
    <a href="?page=<?php echo min($totalPages, $currentPage + 1); ?>" class="<?php echo ($currentPage == $totalPages) ? 'disabled' : ''; ?>"><h1>&rarr;</h1></a>
</div>
    <?php endif; ?>
    
    <div id="booksContainer">
        <?php include 'book_gallery_partial.php'; ?>
    </div>
    
</div>
</body>
</html>

