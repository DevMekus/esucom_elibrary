<?php
require_once ROOT_PATH . '/includes/header.php';
require_once ROOT_PATH . '/includes/appHeader.php';
include "navbar.php";

?>
<body data-page='ebook' data-permission='<?= $user['role']; ?>'>  
    <div 
    id="categories" 
    class="mt-4"
    data-branches='<?= htmlspecialchars(json_encode($categories), ENT_QUOTES, 'UTF-8'); ?>'>
    </div>
    <section class="container">
        <div class="page-title">
            <h3>📚 eBooks Library</h3>
            <p>Browse and download from 37+ academic eBooks</p>
        </div>
        <div class="filter-group">
            <input type="text" id="searchInput" class="form-control" placeholder="Search ebooks" aria-label="Username" aria-describedby="basic-addon1">
            <select class="form-select" id="category_id">
                <option value="null" >All Categories</option>
                <?php foreach($categories as $category):  ?>
                <option value="<?= strtolower($category['id']) ?>"><?= strtoupper($category['category']) ?></option>
                <?php endforeach; ?>               
            </select>
            <button class="btn btn-sm btn-primary" id="searchBtn">🔍 Search</button>  
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ebookModal">
            Add eBook
            </button>
        </div>
        <div class="row" id="card_row"> 
        </div>
        <section class="w-100 d-flex gap-3 justify-content-center mt-4" id="pagination">
            <button class="page-link btn btn-sm" id="prevBtn">Previous</button>
            <button class="page-link btn btn-sm" id="nextBtn">Next</button>
        </section>
     </section>

    <div class="modal fade" id="ebookModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h1 class="modal-title fs-5" id="exampleModalLabel">📰 Add Ebooks</h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form class="ebookModal" id="addEbookForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Ebook Title *</label>
                        <input type="text" class="form-control" name="title" id="title" placeholder="Eg: SAMDIC Book of Prenatal...">
                    </div>
                    <div class="mb-3">
                        <label for="author" class="form-label">Ebook Author(s) *</label>
                        <input type="text" class="form-control" name="author" id="author" placeholder="Eg: Prof. Ekwochi Uchenna">
                    
                    </div>
                    <div class="mb-3">
                        <label for="author" class="form-label">Category *</label>                
                        <select class="form-select" aria-label="Default select example" name="category_id" id="ebookCat_id">              
                            <?php foreach($categories as $category):  ?>
                                <option value="<?= strtolower($category['id']) ?>"><?= strtoupper($category['category']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <input type="hidden" class="form-control" name="url" id="url">
                    <div class="mb-3">
                        <label for="author" class="form-label">Upload Ebook File *</label>
                        <input 
                            type="file"  
                            name="ebook_file"  
                            accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"  
                            class="form-control" 
                            name="author" 
                            id="fileInput" 
                            placeholder="Eg: ebook-title"
                        >
                    </div>
                    <div id="filePreview"  class="mb-3"></div>
                </div>
                <div class="modal-footer w-100 d-flex justify-content-end">           
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
        </form>
        </div>
    </div>
    </div>

<?php require "footer.php" ?>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
</body>
</html>