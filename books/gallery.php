<?php

session_start();

// Default books per page
$booksPerPage = isset($_SESSION['booksPerPage']) ? $_SESSION['booksPerPage'] : 9;

// Include the database configuration file
require_once 'config.php';

// Define the base URL to replace the file path
$baseURL = "https://cv.aut.ac.nz/";
$basePath = "/var/www/html/moodle/";

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
    
    // Fetch the visitor count
    $sqlFetch = "SELECT COUNT(*) AS visitor_count FROM visitor_statistics";
    $stmt = $pdo->prepare($sqlFetch);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $visitorCount = $result['visitor_count'];

    // Split the search query into individual terms
    $searchTerms = explode('|', $searchQuery);
    $searchConditions = [];
    $params = [];

    // Build the SQL query for fetching books
    $sqlFetch = "SELECT 
        SUBSTRING_INDEX(`text`, '/', -1) AS last_text,
        MAX(ID) AS ID,
        MAX(image) AS image,
        MAX(`text`) AS `text`,
        MAX(comments) AS comments
        FROM books WHERE 1";

    foreach ($searchTerms as $index => $term) {
        $param = ":search{$index}";
        $searchConditions[] = "REPLACE(text, '-', ' ') LIKE {$param}";
        $params[$param] = '%' . trim($term) . '%';
    }

    if ($searchConditions) {
        $sqlFetch .= " AND (" . implode(' OR ', $searchConditions) . ")";
    }

    if ($selectedLevel) {
        $sqlFetch .= " AND text LIKE :level";
        $params[':level'] = '%/' . $selectedLevel . '/%';
    }

    $sqlFetch .= " GROUP BY last_text";

    $stmt = $pdo->prepare($sqlFetch);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $totalBooks = $stmt->rowCount();

    // Fetch the books for the current page matching the search query and level, ordered by text
    $sqlFetch .= " ORDER BY text LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($sqlFetch);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
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
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    
    
    <link rel="stylesheet" href="https://cv.aut.ac.nz/books/style.css">
    
    <style>
        .image-tile {
            width: 105px;
            height: 105px;
            overflow: hidden;
            display: inline-block;
            margin: 5px;
            border: 2px solid #ccc;
            transition: border-color 0.3s, transform 0.3s;
        }
        .image-tile img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .image-tile:hover {
            border-color: #007bff;
            transform: scale(1.05);
        }
    </style>

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
                    //const response = JSON.parse(xhr.responseText);
                    //document.getElementById('paginationInfo').innerHTML = `Page <strong>${response.currentPage}</strong> of <strong>${response.totalPages}</strong> | Each page shows up to <strong>16</strong> books`;
                }
            };
            xhr.send();
            //location.reload();
        }
        
        function detectScreenSize() {
            var screenWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
            
            // Set booksPerPage based on screen width
            var booksPerPage;
            if (screenWidth < 1024) {
                booksPerPage = 9; // Mobile size            
            } else {
                booksPerPage = 8; // Desktop size
            }

            // Send screen size data to server using AJAX
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "set_books_per_page.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send("booksPerPage=" + booksPerPage);
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
            detectScreenSize();
            const urlParams = new URLSearchParams(window.location.search);
        	const page = urlParams.get('page') || 1;
        	searchBooks(page);
        };
        
        function clearSearch() {
		// Send request to clear sessions and reload the page
		window.location.href = 'clear_session.php';
	    }
            
        function injectName(name) {
            document.getElementById('searchInput').value = name;
            searchBooks();
            window.scrollTo(0,0);
        }
    </script>
</head>
<body>
<div class="container mt-1">
    <div id="headerContainer" class="d-flex justify-content-between align-items-center mb-0">
        <?php if (!isset($_GET['search'])): ?>
        <h2 class="d-none d-sm-block"><a href="gallery.php"><strong>C</strong>hildren's <strong>V</strong>oyage</a></h2>
        
        <select id="levelSelect" class="form-control w-10 ml-3">
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
        
        <input type="text" id="searchInput" class="form-control w-10 ml-3" placeholder="Search by title..." value="<?php echo htmlspecialchars($searchQuery); ?>">
        <button class="btn btn-secondary ml-2" style="white-space: nowrap;" onclick="clearSearch()">Clear Search</button>
        
        <?php endif; ?>
    </div>
    <?php if (isset($_GET['search'])): ?>
    <div id="paginationInfo" class="pagination-arrows d-flex justify-content-center align-items-center mt-0">
        <?php
        // Calculate the previous and next page numbers
        $prevPage = max(1, $currentPage - 1);
        $nextPage = min($totalPages, $currentPage + 1);
        ?>
        <a href="?page=<?php echo $prevPage; ?>" class="<?php echo ($currentPage == 1) ? 'disabled' : ''; ?>">
            <h1>&larr;</h1>
        </a>
        <span class="mx-3">
            Page <strong><?php echo $currentPage; ?></strong> of <strong><?php echo $totalPages; ?> pages</strong>, there are <strong><?php echo $totalBooks; ?></strong> digital books in total.
        </span>    
        <a href="?page=<?php echo $nextPage; ?>" class="<?php echo ($currentPage == $totalPages) ? 'disabled' : ''; ?>">
            <h1>&rarr;</h1>
        </a>
    </div>
    <?php endif; ?>
	
    <div id="booksContainer">
        <?php include 'book_gallery_partial.php'; ?>
    </div>
    
    
    
    <?php if (!isset($_GET['search'])): ?>
    
    <h2>Search by Image Icons</h2>

<div class="d-flex flex-wrap">
    <?php
        $keywords = [
            'family' => ['family', 'aunty', 'father', 'mother', 'son', 'daughter', 'cousin', 'uncle', 'niece', 'nephew'],
            'animal' => ['animal', 'dog', 'cat', 'bird', 'fish', 'insect', 'lion', 'tiger', 'elephant', 'bear', 'panda', 'kangaroo', 'koala', 'dolphin', 'eagle'],
            'school' => ['school', 'teacher', 'classroom', 'homework', 'library', 'recess', 'student', 'principal', 'tutor', 'mentor'],
            'nature' => ['nature', 'forest', 'ocean', 'river', 'mountain', 'tree', 'flower', 'plant', 'garden', 'beach', 'desert', 'jungle', 'swamp', 'canyon'],
            'adventure' => ['adventure', 'journey', 'quest', 'treasure', 'explorer', 'voyage', 'mission', 'odyssey', 'expedition'],
            'friendship' => ['friendship', 'friend', 'companion', 'buddy', 'pal', 'mate', 'chum', 'partner', 'confidant', 'ally'],
            'holiday' => ['holiday', 'Christmas', 'New Year', 'Easter', 'Halloween', 'birthday', 'vacation', 'picnic', 'camping', 'beach', 'skiing', 'surfing', 'road trip', 'cruise', 'safari', 'hiking'],
            'food' => ['food', 'breakfast', 'lunch', 'dinner', 'snack', 'dessert', 'meal', 'feast', 'cuisine', 'dish'],
            'weather' => ['weather', 'rain', 'sun', 'storm', 'wind', 'snow', 'cloudy', 'thunder', 'lightning', 'hail'],
            'sports' => ['sports', 'soccer', 'rugby', 'basketball', 'swimming', 'running', 'football', 'cricket', 'tennis', 'golf'],
            'emotion' => ['emotion', 'happy', 'sad', 'angry', 'scared', 'excited', 'joy', 'sorrow', 'surprise', 'fear'],
            'space' => ['space', 'planet', 'star', 'astronaut', 'rocket', 'alien', 'galaxy', 'universe', 'comet', 'spacecraft'],
            'fantasy' => ['fantasy', 'dragon', 'wizard', 'fairy', 'magic', 'spell', 'knight', 'princess', 'castle', 'enchantment'],
            'transportation' => ['transportation', 'car', 'bike', 'boat', 'train', 'plane', 'subway', 'tram', 'motorcycle', 'helicopter', 'yacht'],
            'history' => ['history', 'war', 'ancient', 'medieval', 'revolution', 'colony', 'renaissance', 'modern', 'contemporary', 'antique'],
            'science' => ['science', 'experiment', 'robot', 'lab', 'discovery', 'invention', 'biology', 'chemistry', 'physics', 'astronomy', 'geology'],
            'health' => ['health', 'doctor', 'hospital', 'medicine', 'exercise', 'diet', 'nurse', 'clinic', 'therapy', 'fitness', 'nutrition'],
            'home' => ['home', 'house', 'kitchen', 'garden', 'bedroom', 'yard', 'living room', 'bathroom', 'garage', 'attic', 'basement'],
            'technology' => ['technology', 'computer', 'internet', 'smartphone', 'software', 'gadget', 'AI', 'VR', 'drone', 'blockchain', 'cybersecurity'],
            'art' => ['art', 'painting', 'drawing', 'sculpture', 'gallery', 'museum', 'artist', 'canvas', 'brush', 'easel', 'palette'],
            'music' => ['music', 'song', 'instrument', 'band', 'concert', 'melody', 'composer', 'orchestra', 'symphony', 'opera', 'genre'],
            'mythology' => ['mythology', 'god', 'hero', 'legend', 'myth', 'deity', 'monster', 'titan', 'epic', 'saga', 'folklore'],
            'community' => ['community', 'village', 'city', 'neighborhood', 'town', 'society', 'leader', 'council', 'committee', 'volunteer', 'charity'],
            'job' => ['job', 'farmer', 'teacher', 'doctor', 'engineer', 'artist', 'scientist', 'writer', 'actor', 'pilot', 'chef'],
            'season' => ['season', 'spring', 'summer', 'autumn', 'winter', 'rainy', 'sunny', 'windy', 'cloudy', 'snowy'],

            'colors' => ['colors', 'red', 'blue', 'green', 'yellow', 'purple', 'orange', 'pink', 'brown', 'black', 'white'],
'fruits' => ['fruits', 'apple', 'banana', 'cherry', 'grape', 'orange', 'strawberry', 'watermelon', 'pineapple', 'mango', 'peach'],
'vehicles' => ['vehicles', 'car', 'bike', 'boat', 'train', 'plane', 'bus', 'truck', 'motorcycle', 'scooter', 'helicopter'],
'countries' => ['countries', 'USA', 'Canada', 'Australia', 'New Zealand', 'England', 'France', 'Germany', 'Italy', 'Spain', 'Japan'],
'beverages' => ['beverages', 'water', 'coffee', 'tea', 'juice', 'soda', 'milk', 'wine', 'beer', 'smoothie', 'lemonade'],
            
        ];

        foreach ($keywords as $keyword => $subKeywords) {
            $image = $keyword . '.png';
            $subKeywordsString = implode('|', $subKeywords);
            echo '
            <div class="image-tile" onclick="injectName(\'' . $subKeywordsString . '\')">
                <img src="images/' . $image . '" alt="' . $keyword . '">
            </div>';
        }
    ?>
</div>
    
    
    
    <footer class="bg-light text-center text-lg-start mt-0">
  <div class="text-center p-3">
        Made by <a href="https://academics.aut.ac.nz/minh.nguyen" target="_blank">Minh Nguyen</a> for his sons and all the children in the world. May you always be curious and kind. The site is visited <?php echo $visitorCount; ?> times so far.
    </div>
</footer>
<?php endif; ?>

</div>
</body>
</html>

