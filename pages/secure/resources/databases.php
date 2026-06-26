<?php
require_once ROOT_PATH . '/includes/header.php';
include "navbar.php";
?>
<body class="app-layout">
    
    <section class="container mt-4">
        <div class="page-title">
            <h3>🗄️ Online Databases</h3>
            <p>9 specialized research databases available to registered students</p>
        </div>
      <div class="filter-group bg-light p-3 w-100 d-flex gap-2 r mb-3">
            <div class="input-group w-25">
                <span class="input-group-text" id="basic-addon1">🔍</span>
                <input type="text" class="form-control" placeholder="Search databases" aria-label="Username" aria-describedby="basic-addon1">
            </div>
           
        </div>
        <section class="row">
            <div class="col-sm-3">
                <div class="card shadow-sm" style="width: 18rem;">
                    <div class="bg-primary p-3">
                        <h2 class="text-center mt-2">🗄️</h2>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Taylor & Francis Collections</h5>
                       
                        <button  class="btn btn-primary btn-sm">⚡ Access</button>
                    </div>
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