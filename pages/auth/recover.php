<?php
require_once ROOT_PATH . '/includes/header.php';
include "navbar.php";
?>
<body class="app-layout" data-page="auth" data-auth="recover">    
    <section class="auth-container">
        <div class="auth-form-card">
            <h3><strong>Recover Your Password!</strong></h3>
            <p>Enter your email to receive a reset link.</p>
            <div id="a-info"></div>

            <form id="recoverForm" novalidate>
                <div class="mb-3">
                    <label for="email_address" class="form-label">Email address</label>
                    <input type="email" class="form-control" name="email_address" id="email_address" aria-describedby="emailHelp">
                    <div id="emailHelp" class="form-text">We'll never share your email with anyone else.</div>
                </div>
               
                
                <button type="submit" class="btn btn-primary">Reset Link</button>
            </form>
           <div class="w-100 d-flex justify-content-center mt-2"><small class="muted"> <?= AUTH_INTRO; ?></small></div>
        </div>
    </section>
    <?php require_once ROOT_PATH . '/includes/footer.php'; ?>
</body>
</html>