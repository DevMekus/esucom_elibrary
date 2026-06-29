<?php
require_once ROOT_PATH . '/includes/header.php';
include "navbar.php";
?>
<body class="app-layout" data-page="auth" data-auth="login">    
    <section class="auth-container">
        <div class="auth-form-card">
            <h3><strong>Welcome back!</strong></h3>
            <p>Don't have an account? <a href="#"><strong>Contact admin →</strong></a></p>
            <div id="a-info"></div>

            <form id="loginForm">
                <div class="mb-3">
                    <label for="email_address" class="form-label">Email address</label>
                    <input type="email" class="form-control" name="email_address" id="email_address" aria-describedby="emailHelp">
                    <div id="emailHelp" class="form-text">We'll never share your email with anyone else.</div>
                </div>
                <div class="mb-3">
                    <label for="user_password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="user_password" name="user_password">
                    <div id="pwError" class="muted" style="display:none;color:var(--danger);font-size:13px"></div>
                    <div class="d-flex w-100 justify-content-end"><a href="recover" class="muted">Forgot password?</a></div>
                </div>
               
                
                <button type="submit" class="btn btn-primary">Sign In to Esucom Library</button>
            </form>
            <div class="w-100 d-flex justify-content-center mt-2"><small class="muted"> <?= AUTH_INTRO; ?></small></div>
        </div>
    </section>
     <script src="https://cdn.jsdelivr.net/npm/jwt-decode/build/jwt-decode.min.js"></script>
    <?php require_once ROOT_PATH . '/includes/footer.php'; ?>
</body>
</html>