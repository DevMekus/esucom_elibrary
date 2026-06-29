<?php
require_once ROOT_PATH . '/includes/header.php';
include "navbar.php";
?>
<body class="app-layout" data-page='opac' data-permission='admin'>
    
    <section class="container mt-4">
        <div class="page-title">
            <h3>🔖 Users & Accounts</h3>
            <p>Search all 103 physical items · real-time availability & shelf locations</p>
        </div>
      <div class="filter-group bg-light p-3 w-100 d-flex gap-2 r mb-3">
            <div class="input-group w-25">
                <span class="input-group-text" id="basic-addon1">🔍</span>
                <input type="text" id="searchInput" class="form-control" placeholder="Search databases" aria-label="Username" aria-describedby="basic-addon1">
            </div>
            <select class="form-select w-25" id="category_id">
                <option value="null">All Categories</option>
                <option value="1">Students</option>
                <option value="2">Admins</option>
               
            </select>
           <button class="btn btn-sm btn-primary" id="searchBtn">🔍 Search</button>
        </div>
        <section class="row">
            <div class="col-sm-12">
                <div class="card shadow-sm p-4 table-responsive">
                   <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th scope="col">S/N</th>
                                <th scope="col">Title</th>
                                <th scope="col">Author</th>
                                <th scope="col">Accession No</th>
                                <th scope="col">Category</th>
                                <th scope="col">Publisher</th>
                                <th scope="col">ISBN</th>
                                <th scope="col">. . .</th>
                            </tr>
                        </thead>
                        <tbody id="table_row">
                        </tbody>
                    </table>
                </div>
            </div>
            <section class="w-100 d-flex gap-3 justify-content-center mt-4" id="pagination">
                <button class="page-link btn btn-sm" id="prevBtn">Previous</button>
                <button class="page-link btn btn-sm" id="nextBtn">Next</button>
            </section>
            
        </section>
       
    </section>
 <?php require "footer.php" ?>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
</body>
</html>