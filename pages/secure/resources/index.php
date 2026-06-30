<?php
require_once ROOT_PATH . '/includes/appHeader.php';
require_once ROOT_PATH . '/includes/header.php';
include "navbar.php";
?>
<body class="app-layout"  data-permission='<?= $user['role']; ?>'>
    
    <section class="container mt-4">
        <div class="page-title">
            <h3>Welcome to <?= BRAND_NAME ?> 👋</h3>
            <p><?= TAG ?> </p>
        </div>
        <section class="mt-2">
            <div class="row">
                <div class="col-sm-3">
                    <div class="card">
                        <div class="bg-light p-3">
                            <h2 class="text-center mt-2">📚</h2>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">37</h5>
                            <p>eBooks</p>
                            <a href="#"  class="btn btn-primary btn-sm">Access Ebooks</a>
                        </div>
                    </div>                    
                </div>
                <div class="col-sm-3">
                    <div class="card">
                        <div class="bg-light p-3">
                            <h2 class="text-center mt-2">📰</h2>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">161</h5>
                            <p>eJournals</p>
                            <a href="#"  class="btn btn-primary btn-sm">Access Journals</a>
                        </div>
                    </div>                    
                </div>
                <div class="col-sm-3">
                    <div class="card">
                        <div class="bg-light p-3">
                            <h2 class="text-center mt-2">🗄️</h2>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">9</h5>
                            <p>Databases</p>
                            <a href="#"  class="btn btn-primary btn-sm">Access Databases</a>
                        </div>
                    </div>                    
                </div>
                <div class="col-sm-3">
                    <div class="card">
                        <div class="bg-light p-3">
                            <h2 class="text-center mt-2">🔖</h2>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">37</h5>
                            <p>OPAC</p>
                            <a href="#"  class="btn btn-primary btn-sm">Access OPAC</a>
                        </div>
                    </div>                    
                </div>
            </div>
        </section>
        <section class="mt-2">
            <div class="row">
                <div class="col-sm-8">
                    <div class="card p-4">
                       <div class="d-flex justify-content-between align-center ">
                         <h5>📚 Recent Titles</h5>
                         <a href="#">View all →</a>
                       </div>
                        <div class="w-100 d-flex justify-content-between align-center bg-light border-bottom p-2">
                            <div class="">
                                <h6>Atlas-Orthopedics and Neurosurgery-The Spine</h6>
                                <p class="small muted">TORSTEN B. MOELLER & EMIL REIF</p>
                                <span class="badge bg-success">eBook</span>
                            </div>
                            <button class="btn btn-sm btn-primary">PDF</button>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card p-4">
                        <div class="d-flex justify-content-between align-center ">
                            <h5>🔔 Notifications</h5>
                            <a href="#">All →</a>
                        </div>
                        <div class="recent_title_card w-100 border-bottom p-2">
                            <h6>New Arrival</h6>
                            <p class="small muted">15 new Histopathology books added today...</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </section>
 <?php require "footer.php" ?>
<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
</body>
</html>