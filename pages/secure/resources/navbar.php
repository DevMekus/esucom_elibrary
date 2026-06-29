<?php
  use App\Utils\Utility;
  $current = Utility::currentRoute();
  $parts = explode("/", trim($current, "/"));
  $route = $parts[3] ?? null;
?>

<nav class="navbar navbar-expand-lg nav-primary">
  <div class="container">
    <a class="navbar-brand" href="index"><?= BRAND_NAME; ?></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
         <a class="nav-link <?= $route == "index" ? 'active' : '' ?>" aria-current="page" href="index">Overview</a>
        </li>
       <li class="nav-item">
          <a class="nav-link <?= $route == "ebooks" ? 'active' : '' ?>" href="ebooks">eBooks</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $route == "ejournals" ? 'active' : '' ?>" href="ejournals">eJournals</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $route == "databases" ? 'active' : '' ?>" href="databases">Resource Database</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $route == "catalog" ? 'active' : '' ?>" href="catalog">OPAC Catalog</a>
        </li>
        <?php if ($user['role'] == 'super_admin'): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Dropdown
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#">Resources</a></li>
            <li><a class="dropdown-item" href="#">Another action</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="#">Something else here</a></li>
          </ul>
        </li>
        <?php endif; ?>
       
      </ul>
       <div class="d-flex gap-2">
        <a class="btn btn-sm btn-default <?= $route == "notifications" ? 'active' : '' ?>" href="notifications"><i class="fas fa-bell"></i> </a>
        <button class="btn btn-error btn-xs logout" data-id="<?= $user['userid']; ?>"><i class="fas fa-power-off"></i>Logout</button>
      </div>
     
    </div>
  </div>
</nav>