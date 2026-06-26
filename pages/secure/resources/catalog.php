<?php
require_once ROOT_PATH . '/includes/header.php';
include "navbar.php";
?>
<body class="app-layout">
    
    <section class="container mt-4">
        <div class="page-title">
            <h3>🔖 OPAC — Physical Catalog</h3>
            <p>Search all 103 physical items · real-time availability & shelf locations</p>
        </div>
      <div class="filter-group bg-light p-3 w-100 d-flex gap-2 r mb-3">
            <div class="input-group w-25">
                <span class="input-group-text" id="basic-addon1">🔍</span>
                <input type="text" class="form-control" placeholder="Search databases" aria-label="Username" aria-describedby="basic-addon1">
            </div>
            <select class="form-select w-25" aria-label="Default select example">
                <option selected>All Categories</option>
                <option value="1">One</option>
                <option value="2">Two</option>
                <option value="3">Three</option>
            </select>
           
        </div>
        <section class="row">
            <div class="col-sm-12">
                <div class="card shadow-sm p-4">
                   <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th scope="col">Title</th>
                                <th scope="col">Author</th>
                                <th scope="col">Accession No</th>
                                <th scope="col">Category</th>
                                <th scope="col">Publisher</th>
                                <th scope="col">ISBN</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                               
                                <td>
                                    <p class="small bold">
                                        Anatomy of the Nervous System: Human & Comparative 2nd ed<br/>
                                        CALL NUMBER: QL 923 .A597 2013
PUBLICATION: Nigeria, 2013
                                    </p>
                                </td>
                                <td>Mark</td>
                                <td>Otto</td>
                                <td>@mdo</td>
                                <td>@mdo</td>
                                <td>@mdo</td>
                            </tr>
                            
                            
                        </tbody>
                    </table>
                </div>
            </div>
            
        </section>
        <section class="w-100 d-flex gap-3 justify-content-center mt-4">
            <button class="page-link btn btn-sm">Previous</button>
            <button class="page-link btn btn-sm btn-primary">Next</button>
        </section>
    </section>
 <?php require "footer.php" ?>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
</body>
</html>