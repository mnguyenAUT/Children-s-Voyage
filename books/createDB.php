<?php
// Include the database configuration file
require_once 'config.php';

try {
    // Establish a database connection using PDO
    $pdo = new PDO($dsn, $databaseUser, $databasePassword, $options);

    // Function to recursively find all .jpg and .txt files in the directory
    function findFiles($dir) {
        $results = [];

        // Get all files and directories in the current directory
        $files = scandir($dir);
        foreach ($files as $file) {
            // Skip special directories '.' and '..'
            if ($file == '.' || $file == '..') continue;

            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                // Recursively search in subdirectories
                $results = array_merge($results, findFiles($path));
            } else {
                // Only consider .jpg and .txt files
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                if ($extension === 'jpg' || $extension === 'txt') {
                    $results[] = $path;
                }
            }
        }

        return $results;
    }

    // Get the current directory and find all .jpg and .txt files
    $currentDir = __DIR__;
    $allFiles = findFiles($currentDir);

    // Process found files to insert into the database
    $processedFolders = [];
    foreach ($allFiles as $filePath) {
    	echo $filePath."<br/>";
    	$path = $filePath;
        $folderPath = dirname($filePath);
        //if (!in_array($folderPath, $processedFolders)) 
        
        {
            // Initialize variables to store the URLs
            $imageURL = '';
            $textURL = '';

            //foreach ($allFiles as $path) 
            {
                //if (dirname($path) === $folderPath) 
                {
                    if (pathinfo($path, PATHINFO_EXTENSION) === 'jpg') {
                        $imageURL = $path;
			// Replace the ".jpg" extension with ".txt" in the image URL to get the text URL
			$textURL = str_replace('.jpg', '.txt', $imageURL);

                    }
                }
            }

            // Check if the imageURL already exists in the database
            if ($imageURL) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM books WHERE image = :image");
                $stmt->execute([':image' => $imageURL]);
                $count = $stmt->fetchColumn();

                if ($count == 0) {
                    // Insert into the database if the URL is not a duplicate
                    $stmt = $pdo->prepare("INSERT INTO books (image, text) VALUES (:image, :text)");
                    $stmt->execute([':image' => $imageURL, ':text' => $textURL]);
                }
            }

            // Mark this folder as processed
            $processedFolders[] = $folderPath;
        }
    }

    echo "Database populated with unique image and text URLs successfully.";
} catch (Exception $e) {
    // Handle any exceptions or errors
    echo "Error: " . $e->getMessage();
}
?>

