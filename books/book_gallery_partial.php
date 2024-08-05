<!-- Pagination Slider -->
<nav aria-label="Page navigation">
    <div class="d-flex justify-content-center overflow-auto">
        <ul class="pagination" style="white-space: nowrap;">
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
    </div>
</nav>
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
        <div class="col-lg-3 col-md-4 col-xs-12 mb-0">
        <!--
        <div class="col-3 mb-4">
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">-->
    <div class="image-container position-relative" style="overflow: hidden;">
        <a href="https://<?php echo $textLink; ?>&src=<?php echo $imageURL; ?>" target="_blank" style="display: block;">
            <div class="image-wrapper" style="width: 100%; padding-bottom: 150%; position: relative;">
                <img src="<?php echo $imageURL; ?>" alt="Book Thumbnail" class="thumbnail img-fluid" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">
            </div>
        </a>
        <div class="caption-container position-absolute w-100 p-1" style="bottom: 0; background-color: rgba(240, 255, 255, 0.8);">
            <a href="https://<?php echo $textLink; ?>&src=<?php echo $imageURL; ?>" target="_blank">
                <p class="text-center mb-0" style="color: black;"><strong><?php echo htmlentities($caption); ?></strong></p>
            </a>
        </div>
    </div>
</div>

    <?php endforeach; ?>
</div>
<hr/>

<style>
.pagination {
    display: flex;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
}

.page-item {
    flex: 0 0 auto; /* Prevent items from shrinking */
}

.page-item a.page-link {
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
}

.page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}

.page-item.disabled .page-link {
    color: #6c757d;
    pointer-events: none;
    cursor: not-allowed;
}
</style>


