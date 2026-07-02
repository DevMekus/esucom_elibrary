<?php
require_once ROOT_PATH . '/includes/appHeader.php';
require_once ROOT_PATH . '/includes/header.php';
include "navbar.php";

?>
<body class="app-layout" data-page='users' data-userid='<?= $user['userid']; ?>'  data-permission='<?= $user['role']; ?>' data-role='<?= $user['role']; ?>'>
    <div 
    id="departments" 
    class="mt-4"
    data-branches='<?= htmlspecialchars(json_encode($departments), ENT_QUOTES, 'UTF-8'); ?>'>
    </div>
    <section class="container mt-4">
        <div class="page-title">
            <h3>🔖 Users — Management</h3>
           
            <p>manage all account</p>
        </div>
      <div class="filter-group">
            <input type="text" id="searchInput" class="form-control" placeholder="Search name, email, phone" aria-label="Username" aria-describedby="basic-addon1">
            <select class="form-select" id="role_id">
                <option value="null">All Roles</option>
                <option value="1">Students</option>
                <option value="2">Admins</option>                
            </select>
            <button class="btn btn-sm btn-primary" id="searchBtn">🔍 Search</button>
            <button type="button" id="addOpacBtn" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal">
                Add User
            </button>
        </div>
        <section class="row">
            <div class="col-sm-12">
                <div class="card shadow-sm p-4">
                   <table class="tables table-striped table-hover">
                        <thead >
                            <tr>
                                <th scope="col">Id</th>
                                <th scope="col">Account</th>
                                <th scope="col">Department</th>
                                <th scope="col">Level</th>
                                <th scope="col">Role</th>
                                <th scope="col">Join Date</th>
                                <th scope="col">Status</th>
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

 <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">📰 <span id="titleDom">Add</span> User</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form class="userModal" id="addUserForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="fullname" class="form-label">Fullname *</label>
                        <input type="text" class="form-control" name="fullname" id="fullname" >
                    </div>
                    <div class="mb-3">
                        <label for="email_address" class="form-label">Email address *</label>
                        <input type="email" class="form-control" name="email_address" id="email_address">                
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <label for="phone" class="form-label">Phone *</label>
                            <input type="text" class="form-control" name="phone" id="phone">                
                        </div>
                        <div class="col-sm-6">
                            <label for="level" class="form-label">Level *</label>
                            <input type="level" class="form-control" name="level" id="level" placeholder="eg: 300L, 200L, 100L">                
                        </div> 
                    </div>
                    

                     <div class="mb-3">
                        <label for="author" class="form-label">Department </label>   
                        <select class="form-selects" id="department" name="department">
                            <option value="null" >--Select Department --</option>
                            <?php foreach($departments as $department):  ?>
                            <option value="<?= strtolower($department['id']) ?>"><?= strtoupper($department['department_name']) ?></option>
                            <?php endforeach; ?>         
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address / Room No </label>
                        <textarea  class="form-control" rows="2" name="address" id="address"> </textarea>              
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <label for="author" class="form-label">Role *</label>                
                            <select class="form-select" aria-label="Default select example" name="role_id" id="uRole_id">              
                            <option value="null">All Roles</option>
                                <option value="1">Students</option>
                            <?php if ($user['role'] == 'super_admin'): ?>
                                <option value="2">Admins</option>    
                            <?php endif; ?>  
                            </select>
                        </div>
                    
                        <div class="col-sm-6">
                            <label for="author" class="form-label">Status *</label>                
                            <select class="form-select" aria-label="Default select example" name="status" id="status">              
                            <option value="active">Active</option>
                                <option value="inactive">Inactive</option>                      
                            </select>
                        </div>
                    </div>
                
                    
                                     
            
                    <div class="mb-3">
                        <label for="author" class="form-label">Upload avatar </label>
                        <input 
                            type="file"  
                            name="profileImage"  
                            accept=".png, .jpg, .jpeg"  
                            class="form-control" 
                            name="author" 
                            id="fileInput" 
                           
                        >
                    </div>
                    <div id="filePreview"  class="mb-3"></div>
                    
                    
                
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