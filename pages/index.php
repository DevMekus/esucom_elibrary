<?php
require_once ROOT_PATH . '/includes/header.php';
include "navbar.php";
?>
<body class="app-layout">
<section class="hero-carousel">
    <div class="float_div">
            <div class="row">
                <div class="col-sm-6">
                    <div class="content">
                        <h1>
                        Your Gateway to Medical Knowledge & Research Excellence
                        </h1>
                        <p>Access thousands of eBooks, eJournals, research databases, and physical collections — all in one unified library portal. Search, read, and grow.</p>
                        <button class="btn">Explore Resources</button>
                    </div>
                    
                </div>
            </div>
    </div>
    <div id="carouselExampleCaptions" class="carousel  slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
            <img src="https://plus.unsplash.com/premium_photo-1682284353484-4e16001c58eb?q=80&w=870&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="d-block w-100" alt="...">
            <div class="carousel-caption d-none d-md-block">
              
            </div>
            </div>
            <div class="carousel-item">
            <img src="https://plus.unsplash.com/premium_photo-1663047671914-1b0a9ed1e0f4?q=80&w=871&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="d-block w-100" alt="...">
            <div class="carousel-caption d-none d-md-block">
               
            </div>
            </div>
            <div class="carousel-item">
            <img src="https://plus.unsplash.com/premium_photo-1663040105068-fea37b9af2c9?q=80&w=871&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="d-block w-100" alt="...">
            <div class="carousel-caption d-none d-md-block">
               
            </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>    
</section>


<?php require_once ROOT_PATH . '/includes/footer.php'; ?>
</body>
</html>