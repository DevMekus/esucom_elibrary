<?php
require_once ROOT_PATH . '/includes/header.php';

$token = $_GET['token'] ?? null;

if (!$token) header('location: ' . BASE_URL . 'auth/login');

include "navbar.php";
?>
<body class="app-layout" data-page="auth" data-auth="reset">    
    <section class="auth-container">
        <div class="auth-form-card">
            <h3><strong>Reset Your Password!</strong></h3>
            <p>Please enter your new password below.</p>
            <div id="a-info"></div>

            <form id="resetForm" novalidate>
                <div class="mb-3">
                    <label for="new_password" class="form-label">Password</label>
                    <input type="password" class="form-control" name="new_password" id="email_address" placeholder="Enter your new password" minlength="6">
                    <div id="pwError" class="muted" style="display:none;color:var(--danger);font-size:13px"></div>
                </div>

                <div id="message"></div>
                <input type="hidden" name="token" value="<?= $token; ?>" />
               
                
                <button type="submit" class="btn btn-primary">Reset Password</button>
            </form>
           <div class="w-100 d-flex justify-content-center mt-2"><small class="muted"> <?= AUTH_INTRO; ?></small></div>
        </div>
    </section>
    <?php require_once ROOT_PATH . '/includes/footer.php'; ?>
</body>
</html>