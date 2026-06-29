<?php
require_once ROOT_PATH . '/includes/appHeader.php';
require_once ROOT_PATH . '/includes/header.php';
include "navbar.php";
?>
<body class="app-layout" data-page='database' data-permission='<?= $user['role']; ?>'>
    
    <section class="container mt-4">
        <div class="page-title">
            <h3>🗄️ Online Databases</h3>
            <p>9 specialized research databases available to registered students</p>
        </div>
      <div class="filter-group bg-light p-3 w-100 d-flex gap-2 r mb-3">
            <div class="input-group w-25">
                <span class="input-group-text" id="basic-addon1">🔍</span>
                <input type="text" class="form-control" id="searchInput" placeholder="Search databases" aria-label="Username" aria-describedby="basic-addon1">
            </div>
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