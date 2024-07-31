<?php
session_start(); // Start the session

// Include the database configuration file
require_once 'config.php';

// Define the base URL to replace the file path
$baseURL = "https://cv.aut.ac.nz/";
$basePath = "/var/www/html/moodle/";

// Set the number of books per page
$booksPerPage = 16;

// Get the current page from the URL, default is 1
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Validate current page number
if ($currentPage < 1) {
    $currentPage = 1;
}

// Get the search query and level if available and store in session
$searchQuery = isset($_GET['search']) ? $_GET['search'] : (isset($_SESSION['searchQuery']) ? $_SESSION['searchQuery'] : '');
$selectedLevel = isset($_GET['level']) ? (int)$_GET['level'] : (isset($_SESSION['selectedLevel']) ? (int)$_SESSION['selectedLevel'] : '');

// Store the search query and selected level in the session
$_SESSION['searchQuery'] = $searchQuery;
$_SESSION['selectedLevel'] = $selectedLevel;

// Calculate the offset for the SQL query
$offset = ($currentPage - 1) * $booksPerPage;

// Validate offset to prevent large values
if ($offset < 0) {
    $offset = 0;
}

// Establish a database connection using PDO
try {
    $pdo = new PDO($dsn, $databaseUser, $databasePassword, $options);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Build the SQL query for counting total books
    $sqlCount = "SELECT COUNT(*) FROM books WHERE 1";
    $params = [];

    // Add search condition if present
    if ($searchQuery) {
        $sqlCount .= " AND REPLACE(text, '-', ' ') LIKE :search";
        $params[':search'] = '%' . $searchQuery . '%';
    }

    // Add level condition if present
    if ($selectedLevel) {
        $sqlCount .= " AND text LIKE :level";
        $params[':level'] = '%/' . $selectedLevel . '/%';
    }

    $stmt = $pdo->prepare($sqlCount);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $totalBooks = $stmt->fetchColumn();

    // Fetch the books for the current page matching the search query and level, ordered by text
    $sqlFetch = "SELECT * FROM books WHERE 1";
    if ($searchQuery) {
        $sqlFetch .= " AND REPLACE(text, '-', ' ') LIKE :search";
    }
    if ($selectedLevel) {
        $sqlFetch .= " AND text LIKE :level";
    }
    $sqlFetch .= " ORDER BY text LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sqlFetch);
    if ($searchQuery) {
        $stmt->bindValue(':search', '%' . $searchQuery . '%', PDO::PARAM_STR);
    }
    if ($selectedLevel) {
        $stmt->bindValue(':level', '%/' . $selectedLevel . '/%', PDO::PARAM_STR);
    }
    $stmt->bindValue(':limit', $booksPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
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
    echo json_encode(['totalPages' => $totalPages, 'currentPage' => $currentPage]);
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
        function searchBooks(page = 1) {
            const query = document.getElementById('searchInput').value;
            const level = document.getElementById('levelSelect').value;

            const xhr = new XMLHttpRequest();
            xhr.open('GET', `gallery.php?search=${query}&level=${level}&page=${page}`, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.getElementById('booksContainer').innerHTML = xhr.responseText;

                    // Update pagination info
                    const response = JSON.parse(xhr.responseText);
                    document.getElementById('paginationInfo').innerHTML = `Page <strong>${response.currentPage}</strong> of <strong>${response.totalPages}</strong> | Each page shows up to <strong>16</strong> books`;
                }
            };
            xhr.send();
            //location.reload();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const levelSelect = document.getElementById('levelSelect');
            if (searchInput && levelSelect) {
                searchInput.addEventListener('input', searchBooks);
                levelSelect.addEventListener('change', searchBooks);
            }
        });
        
        // Run searchBooks() once the page is fully loaded
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
        	const page = urlParams.get('page') || 1;
        	searchBooks(page);
        };
        
    </script>
</head>
<body>
<div class="container mt-5">
    <div id="headerContainer" class="d-flex justify-content-between align-items-center mb-4">
        <?php if (!isset($_GET['search'])): ?>
        <h2><a href="gallery.php">Children's Voyage</a></h2>
        
        <select id="levelSelect" class="form-control w-25 ml-3">
            <option value="">All reading levels</option>
            <option value="29181" <?php echo $selectedLevel == 29181 ? 'selected' : ''; ?>>Ready to Read Phonics Plus</option>
            <option value="22576" <?php echo $selectedLevel == 22576 ? 'selected' : ''; ?>>Ready to Read Colour Wheel</option>
            <option value="1" <?php echo $selectedLevel == 1 ? 'selected' : ''; ?>>Reading Year 1</option>
            <option value="2" <?php echo $selectedLevel == 2 ? 'selected' : ''; ?>>Reading Year 2</option>
            <option value="3" <?php echo $selectedLevel == 3 ? 'selected' : ''; ?>>Reading Year 3</option>
            <option value="4" <?php echo $selectedLevel == 4 ? 'selected' : ''; ?>>Reading Year 4</option>
            <option value="5" <?php echo $selectedLevel == 5 ? 'selected' : ''; ?>>Reading Year 5</option>
            <option value="6" <?php echo $selectedLevel == 6 ? 'selected' : ''; ?>>Reading Year 6</option>
            <option value="7" <?php echo $selectedLevel == 7 ? 'selected' : ''; ?>>Reading Year 7</option>
            <option value="8" <?php echo $selectedLevel == 8 ? 'selected' : ''; ?>>Reading Year 8</option>
            
            <option value="22577" <?php echo $selectedLevel == 22577 ? 'selected' : ''; ?>>Junior Journal / Chapters</option>
            <option value="22578" <?php echo $selectedLevel == 22578 ? 'selected' : ''; ?>>School Journal</option>
            <option value="22579" <?php echo $selectedLevel == 22579 ? 'selected' : ''; ?>>School Journal Story Library</option>
            <option value="22580" <?php echo $selectedLevel == 22580 ? 'selected' : ''; ?>>Connected series</option>
        </select>
        
        <input type="text" id="searchInput" class="form-control w-25 ml-3" placeholder="Search by title..." value="<?php echo htmlspecialchars($searchQuery); ?>">
        
        <?php endif; ?>
    </div>
    <?php if (isset($_GET['search'])): ?>
    <div id="paginationInfo" class="pagination-arrows d-flex justify-content-center align-items-center mt-4">
        <?php
        // Calculate the previous and next page numbers
        $prevPage = max(1, $currentPage - 1);
        $nextPage = min($totalPages, $currentPage + 1);
        ?>
        <a href="?page=<?php echo $prevPage; ?>" class="<?php echo ($currentPage == 1) ? 'disabled' : ''; ?>">
            <h1>&larr;</h1>
        </a>
        <span class="mx-3">
            Page <strong><?php echo $currentPage; ?></strong> of <strong><?php echo $totalPages; ?> pages</strong> , there are <strong><?php echo $totalBooks; ?></strong> digital books in total.
        </span>    
        <a href="?page=<?php echo $nextPage; ?>" class="<?php echo ($currentPage == $totalPages) ? 'disabled' : ''; ?>">
            <h1>&rarr;</h1>
        </a>
    </div>
    <?php endif; ?>
	
    <div id="booksContainer">
        <?php include 'book_gallery_partial.php'; ?>
    </div>
</div>
</body>
</html>

