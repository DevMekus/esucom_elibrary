<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define("BRAND_NAME", "Esucom Library");
define("BRAND_PHONE", "+234...");
define("BRAND_EMAIL", "support@esucomlib.com");
define("ADMIN_EMAIL", "support@esucomlib.com");
define("COMPANY_ADDRESS", "Parklane Hospital, Enugu.");
define("AUTH_INTRO", "Esut College of Medicine Library Portal");
define("TAG", "Esut College of Medicine Library Portal");

//Date and Timezone Settings
date_default_timezone_set('Africa/Lagos');   
$dateAndTime =  date('Y-m-d H:i:s');
$date =  date('Y-m-d');
$time =  date('H:i:s');

define("CURRENT_DATE", $date);
define("CURRENT_TIME", $time);


// ===========================
// ROOT AND URL DEFINITIONS
// ===========================

// Physical rooth path (for require/ include)
define('ROOT_PATH', dirname(__FILE__));

// Public folder
define('PUBLIC_PATH', ROOT_PATH . '/public');

//Site base URL
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
    (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

define('BASE_URL', ($https ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/esucomlib/');
define('BASE_DIR', $_SERVER['DOCUMENT_ROOT'] . '/esucomlib/');



//API
define("API_URL", BASE_URL . "api/");

// ===========================
// AUTOLOAD CORE FILES
// ===========================

if (file_exists(ROOT_PATH . '/apps/Utils/Utility.php')) {
    require_once ROOT_PATH . '/apps/Utils/Utility.php';
}

if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    require_once ROOT_PATH . '/vendor/autoload.php';
}

// ===========================
// ENVIRONMENT DETECTION
// ===========================

if ($_SERVER['HTTP_HOST'] == 'localhost') {
    define("ENVIRONMENT", 'development');
} else {
    define('ENVIRONMENT', 'production');
}

if (ENVIRONMENT == 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}
