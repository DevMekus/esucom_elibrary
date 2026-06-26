<?php
  use App\Utils\Utility;
  $current = Utility::currentRoute();
  $parts = explode("/", trim($current, "/"));

  $route = $parts[3] ?? null;
?>

<nav class="navbar navbar-expand-lg nav-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="#"><?= BRAND_NAME ?></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse w-100 d-flex justify-content-between" id="navbarNav">
      <ul class="navbar-nav">
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
       
      </ul>
      <a href="<?= BASE_URL ?>" class="btn btn-secondary btn-sm">Go Home</a>
    </div>
  </div>
</nav>