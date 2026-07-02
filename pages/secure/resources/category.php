<?php
require_once ROOT_PATH . '/includes/appHeader.php';
require_once ROOT_PATH . '/includes/header.php';
include "navbar.php";

?>
<body class="app-layout" data-page='categories' data-userid='<?= $user['userid']; ?>'  data-permission='<?= $user['role']; ?>' data-role='<?= $user['role']; ?>'>
    <div 
    id="categories" 
    class="mt-4"
    data-branches='<?= htmlspecialchars(json_encode($categories), ENT_QUOTES, 'UTF-8'); ?>'>
    </div>
    <section class="container mt-4">
        <div class="page-title">
            <h3>🔖 Categories — Management</h3>
           
            <p>manage all account</p>
        </div>
      <div class="filter-group">
            <input type="text" id="searchInput" class="form-control" placeholder="Search name" aria-label="Username" aria-describedby="basic-addon1">           
            <button class="btn btn-sm btn-primary" id="searchBtn">🔍 Search</button>
            <button type="button" id="addCategoryBtn" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategory">
                Add Category
            </button>
        </div>
        <section class="row">
            <div class="col-sm-12">
                <div class="card shadow-sm p-4">
                   <table class="tables table-striped table-hover">
                        <thead >
                            <tr>
                                <th scope="col">Id</th>
                                <th scope="col">Category</th>                              
                                <th scope="col">. . .</th>   
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

 <div class="modal fade" id="addCategory" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">📰 <span id="titleDom">Add</span> Category</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form class="addCategory" id="addCategoryForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category" class="form-label">Category name *</label>
                        <input type="text" class="form-control" name="category" id="category" >
                    </div>
                </div>
                <div class="modal-footer w-100 d-flex justify-content-end">           
                    <button type="submit" class="btn btn-primary" id="submitBtn">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
</body>
</html>