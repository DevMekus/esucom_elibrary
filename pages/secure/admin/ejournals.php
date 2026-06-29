<?php
require_once ROOT_PATH . '/includes/header.php';
include "navbar.php";
?>
<body class="app-layout" data-page='journal' data-permission='admin'>
    
    <section class="container mt-4">
        <div class="page-title">
            <h3>📰 eJournals</h3>
            <p>161+ peer-reviewed journals from leading academic publishers</p>
        </div>
        <div class="filter-group bg-light p-3 w-100 d-flex gap-2 r mb-3">
            <div class="input-group w-25">
                <span class="input-group-text" id="basic-addon1">🔍</span>
                <input type="text" id="searchInput" class="form-control" placeholder="Search by Journal title" aria-label="Username" aria-describedby="basic-addon1">
            </div>
            <select class="form-selects w-25" id="category_id">
                <option value="null">All Categories</option>
                <option value="1">One</option>
                <option value="2">Two</option>
                <option value="3">Three</option>
            </select>
            <button class="btn btn-sm btn-primary" id="searchBtn">🔍 Search</button>
        </div>
        <section class="row" id="card_row"> 
        </section>
       
        <section class="w-100 d-flex gap-3 justify-content-center mt-4" id="pagination">
            <button class="page-link btn btn-sm" id="prevBtn">Previous</button>
            <button class="page-link btn btn-sm" id="nextBtn">Next</button>
        </section>
    </section>
 <?php require "footer.php" ?>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
</body>
</html>