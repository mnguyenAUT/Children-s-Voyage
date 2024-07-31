<div class="row">
    <?php foreach ($books as $book): 
        // Replace the base path with the base URL for images
        $imageURL = str_replace($basePath, $baseURL, $book['image']);
        // Convert the image URL to the text URL
        $textURL = str_replace('.jpg', '.txt', $imageURL);
        $textLink = file_get_contents($textURL);

        // Extract the last part of the URL after the last '/'
        $captionPart = basename($textURL, ".txt");

        // Replace '-' with spaces
        $caption = str_replace('-', ' ', $captionPart);
    ?>
        <div class="col-3 mb-4">
    <div class="image-container position-relative" style="overflow: hidden;">
        <a href="https://<?php echo $textLink; ?>&src=<?php echo $imageURL; ?>" target="_blank" style="display: block;">
            <div class="image-wrapper" style="width: 100%; padding-bottom: 137%; position: relative; background-color: #f0f0f0;">
                <img src="<?php echo $imageURL; ?>" alt="Book Thumbnail" class="thumbnail img-fluid" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">
            </div>
        </a>
        <div class="caption-container position-absolute w-100 p-1" style="bottom: 0; background-color: rgba(255, 255, 255, 0.8);">
            <a href="https://<?php echo $textLink; ?>&src=<?php echo $imageURL; ?>" target="_blank">
                <p class="text-center mb-0" style="color: black;"><?php echo htmlentities($caption); ?></p>
            </a>
        </div>
    </div>
</div>

    <?php endforeach; ?>
</div>
<hr/>
<!-- Pagination -->
<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center" style="max-width: 1024px; flex-wrap: wrap;">
        <li class="page-item <?php if($currentPage <= 1) echo 'disabled'; ?>">
            <a class="page-link" href="?page=<?php echo max(1, $currentPage - 1); ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?php if($i == $currentPage) echo 'active'; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?php if($currentPage >= $totalPages) echo 'disabled'; ?>">
            <a class="page-link" href="?page=<?php echo min($totalPages, $currentPage + 1); ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    </ul>
</nav>

<style>
    .image-container {
        position: relative;
        overflow: hidden;
    }

    .image-container .thumbnail {
        transition: transform 0.5s ease;
    }

    .image-container:hover .thumbnail {
        transform: scale(1.05);
    }

    .image-container .shine-effect {
        position: absolute;
        top: 0;
        left: -75%;
        width: 50%;
        height: 100%;
        background: rgba(255, 255, 255, 0.5);
        transform: skewX(-20deg);
        transition: left 0.5s ease;
    }

    .image-container:hover .shine-effect {
        left: 125%;
    }

    .caption-container {
        position: absolute;
        bottom: 0;
        width: 100%;
        padding: 5px;
        background-color: rgba(255, 255, 255, 0.8);
        text-align: center;
    }

    .caption-container p {
        margin-bottom: 0;
        color: black;
    }
</style>

