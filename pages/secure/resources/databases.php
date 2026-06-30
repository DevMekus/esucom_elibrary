<?php
require_once ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/includes/appHeader.php';
include "navbar.php";
?>
<body class="app-layout" data-page='database' data-permission='<?= $user['role']; ?>'>
    <div 
    id="departments" 
    class="mt-4"
    data-branches='<?= htmlspecialchars(json_encode($departments), ENT_QUOTES, 'UTF-8'); ?>'>
    </div>
    <section class="container mt-4">
        <div class="page-title">
            <h3>🗄️ Online Databases</h3>
            <p>9 specialized research databases available to registered students</p>
        </div>
      <div class="filter-group">
            <input type="text" class="form-control" id="searchInput" placeholder="Search databases" aria-label="Username" aria-describedby="basic-addon1">
           <button class="btn btn-sm btn-primary" id="searchBtn">🔍 Search</button>
           <button type="button" id="newDatabaseBtn" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#databaseModal">
            Add Database
            </button>
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

<div class="modal fade" id="databaseModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h1 class="modal-title fs-5" id="exampleModalLabel">📰 <span id="titleDom">Add</span> Research Database</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form class="databaseModal" id="addDatabaseForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Database Title *</label>
                        <input type="text" class="form-control" name="name" id="name" placeholder="Eg: HINARI...">
                    </div>                    
                    
                    <div class="mb-3">
                        <label for="url" class="form-label">Access URL *</label>
                        <input type="text" class="form-control" name="url" id="url" placeholder="Eg: https://www.cdc.gov/health-topics.html">
                    
                    </div>
                </div>
                <div class="modal-footer w-100 d-flex justify-content-end">           
                    <button type="submit" class="btn btn-primary" id="submitBtn">Save Database</button>
                </div>
        </form>
        </div>
    </div>
</div>
</body>
</html>