<?php

use Dotenv\Dotenv;

require_once ROOT_PATH . "/vendor/autoload.php";

$dotenv = Dotenv::createImmutable(ROOT_PATH);
$dotenv->load();
