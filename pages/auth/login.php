<?php
require_once ROOT_PATH . '/includes/header.php';
include "navbar.php";
?>
<body class="app-layout">    
    <section class="auth-container">
        <div class="auth-form-card">
            <h3><strong>Welcome back!</strong></h3>
            <p>Don't have an account? <a href="#"><strong>Create one →</strong></a></p>

            <form>
                <div class="mb-3">
                    <label for="exampleInputEmail1" class="form-label">Email address</label>
                    <input type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp">
                    <div id="emailHelp" class="form-text">We'll never share your email with anyone else.</div>
                </div>
                <div class="mb-3">
                    <label for="exampleInputPassword1" class="form-label">Password</label>
                    <input type="password" class="form-control" id="exampleInputPassword1">
                    <div class="d-flex w-100 justify-content-end"><a href="#" class="muted">Forgot password?</a></div>
                </div>
               
                
                <button type="submit" class="btn btn-primary">Sign In to Esucom Library</button>
            </form>
            <div class="w-100 d-flex justify-content-center mt-2"><small class="muted">Esucom Library</small></div>
        </div>
    </section>
</body>
</html>