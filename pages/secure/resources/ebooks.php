<?php
require_once ROOT_PATH . '/includes/header.php';
include "navbar.php";
?>
<body class="app-layout">
    
    <section class="container mt-4">
        <div class="page-title">
            <h3>📚 eBooks Library</h3>
            <p>Browse and download from 37+ academic eBooks</p>
        </div>
        <div class="filter-group">
            <div class="row">
                <div class="col-sm-4">
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon1"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search ebooks" aria-label="Username" aria-describedby="basic-addon1">
                    </div>                    
                </div>
                <div class="col-sm-3">
                    <select class="form-select" aria-label="Default select example">
                        <option selected>All Categories</option>
                        <option value="1">One</option>
                        <option value="2">Two</option>
                        <option value="3">Three</option>
                    </select>
                </div>
                
            </div>
        </div>
    </section>

<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
</body>
</html>