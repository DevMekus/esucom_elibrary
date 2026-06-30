<?php
require_once ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/includes/appHeader.php';
include "navbar.php";
?>
<body class="app-layout" data-page='journal' data-permission='<?= $user['role']; ?>'>
    <div 
    id="departments" 
    class="mt-4"
    data-branches='<?= htmlspecialchars(json_encode($departments), ENT_QUOTES, 'UTF-8'); ?>'>
    </div>
    <section class="container">
        <div class="page-title">
            <h3>📰 eJournals</h3>
            <p>161+ peer-reviewed journals from leading academic publishers</p>
        </div>
        <div class="filter-group">
            <input type="text" id="searchInput" class="form-control" placeholder="Search by Journal title" aria-label="Username" aria-describedby="basic-addon1">
            <select class="form-selects" id="category_id">
                <option value="null" >All Departments</option>
                <?php foreach($departments as $department):  ?>
                <option value="<?= strtolower($department['id']) ?>"><?= strtoupper($department['department_name']) ?></option>
                <?php endforeach; ?>         
            </select>
            <button class="btn btn-sm btn-primary" id="searchBtn">🔍 Search</button>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ejournalModal">
            Add eJournal
            </button>
        </div>
        <section class="row" id="card_row"> 
        </section>
       
        <section class="w-100 d-flex gap-3 justify-content-center mt-4" id="pagination">
            <button class="page-link btn btn-sm" id="prevBtn">Previous</button>
            <button class="page-link btn btn-sm" id="nextBtn">Next</button>
        </section>
    </section>

    <div class="modal fade" id="ejournalModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h1 class="modal-title fs-5" id="exampleModalLabel">📰 Add eJournal Subscription</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form class="eJournalModal" id="addJournalForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Journal Title *</label>
                        <input type="text" class="form-control" name="title" id="title" placeholder="Eg: SAMDIC Book of Prenatal...">
                    </div>
                    
                    <div class="mb-3">
                        <label for="author" class="form-label">Subject Area</label>                
                        <select class="form-select" aria-label="Default select example" name="department_id" id="ejDept_id">              
                            <?php foreach($departments as $department):  ?>
                                <option value="<?= strtolower($department['id']) ?>"><?= strtoupper($department['department_name']) ?></option>
                            <?php endforeach; ?>    
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="url" class="form-label">Access URL *</label>
                        <input type="text" class="form-control" name="url" id="url" placeholder="Eg: https://journal.example.com">
                    
                    </div>
                </div>
                <div class="modal-footer w-100 d-flex justify-content-end">           
                    <button type="submit" class="btn btn-primary" id="submitBtn">Save eJournal</button>
                </div>
        </form>
        </div>
    </div>
    </div>
 <?php require "footer.php" ?>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
</body>
</html>