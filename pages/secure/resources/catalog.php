<?php
require_once ROOT_PATH . '/includes/appHeader.php';
require_once ROOT_PATH . '/includes/header.php';
include "navbar.php";
?>
<body class="app-layout" data-page='opac'  data-permission='<?= $user['role']; ?>'>
    <div 
    id="categories" 
    class="mt-4"
    data-branches='<?= htmlspecialchars(json_encode($categories), ENT_QUOTES, 'UTF-8'); ?>'>
    </div>
    <section class="container mt-4">
        <div class="page-title">
            <h3>🔖 OPAC — Physical Catalog</h3>
            <p>Search all 103 physical items · real-time availability & shelf locations</p>
        </div>
      <div class="filter-group">
            <input type="text" id="searchInput" class="form-control" placeholder="Search databases" aria-label="Username" aria-describedby="basic-addon1">
            <select class="form-select w-25" id="category_id">
                <option value="null">All Categories</option>
                <?php foreach($categories as $category):  ?>
                <option value="<?= strtolower($category['id']) ?>"><?= strtoupper($category['category']) ?></option>
                <?php endforeach; ?>       
            </select>
           <button class="btn btn-sm btn-primary" id="searchBtn">🔍 Search</button>
            <button type="button" id="addOpacBtn" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#opacModal">
            Add Catalog
            </button>
        </div>
        <section class="row">
            <div class="col-sm-12">
                <div class="card shadow-sm p-4">
                   <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col">S/N</th>
                                <th scope="col">Title</th>
                                <th scope="col">Author</th>
                                <th scope="col">Accession No</th>
                                <th scope="col">Category</th>
                                <th scope="col">Publisher</th>
                                <th scope="col">ISBN</th>
                                <?= $user['role'] !== 'student' ? '<th scope="col">. . .</th>' : '' ?>                               
                            </tr>
                        </thead>
                        <tbody id="table_row">
                        </tbody>
                    </table>
                </div>
            </div>
            
        </section>
        <section class="w-100 d-flex gap-3 justify-content-center mt-4" id="pagination">
            <button class="page-link btn btn-sm" id="prevBtn">Previous</button>
            <button class="page-link btn btn-sm" id="nextBtn">Next</button>
        </section>
    </section>
 <?php require "footer.php" ?>

 <div class="modal fade" id="opacModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        <div class="modal-header">
            <h1 class="modal-title fs-5" id="exampleModalLabel">📰 <span id="titleDom">Add</span> Catalog</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form class="opacModal" id="addOpacForm">
            <div class="modal-body">
                <div class="mb-3">
                    <label for="title" class="form-label">Book Title *</label>
                    <input type="text" class="form-control" name="title" id="title" >
                </div>
                <div class="mb-3">
                    <label for="author" class="form-label">Author(s) *</label>
                    <input type="text" class="form-control" name="author" id="author">                
                </div>
                <div class="mb-3">
                    <label for="accession_no" class="form-label">Accession Number</label>
                    <input type="text" class="form-control" name="accession_no" id="accession_no" >                
                </div>
                <div class="mb-3">
                    <label for="publisher" class="form-label">Publisher</label>
                    <input type="text" class="form-control" name="publisher" id="publisher" >                
                </div>
                <div class="mb-3">
                    <label for="author" class="form-label">Category *</label>                
                    <select class="form-select" aria-label="Default select example" name="category_id" id="catelogCart_id">              
                        <?php foreach($categories as $category):  ?>
                            <option value="<?= strtolower($category['id']) ?>"><?= strtoupper($category['category']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-6">
                        <label for="publication_place" class="form-label">Place of Publication</label>
                        <input type="text" class="form-control" name="publication_place" id="publication_place" >                
                    </div>
                    <div class="col-sm-6">
                        <label for="date_of_publication" class="form-label">Year of Publication</label>
                        <input type="text" class="form-control" name="date_of_publication" id="date_of_publication" >                
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3">
                        <label for="call_number" class="form-label">Call Number</label>
                        <input type="text" class="form-control" name="call_number" id="call_number" >                
                    </div>
                    <div class="col-sm-3">
                        <label for="serial_number" class="form-label">Serial Number</label>
                        <input type="text" class="form-control" name="serial_number" id="serial_number" >                
                    </div>
                    <div class="col-sm-3">
                        <label for="shelve_number" class="form-label">Shelve Number</label>
                        <input type="text" class="form-control" name="shelve_number" id="shelve_number" >                
                    </div>
                    <div class="col-sm-3">
                        <label for="copies" class="form-label">Copies</label>
                        <input type="text" class="form-control" name="copies" id="copies" >                
                    </div>
                </div>
                
                
                
               
            </div>
            <div class="modal-footer w-100 d-flex justify-content-end">           
                <button type="submit" class="btn btn-primary" id="submitBtn">Save Catalog</button>
            </div>
        </form>
        </div>
    </div>
</div>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
</body>
</html>