<?php
require_once dirname(__DIR__) . '/siteConfig.php';

$url = isset($_GET['url']) ? trim($_GET['url'], '/') : 'index';
$urlParts = explode('/', $url);

$page = $urlParts[0];
$id = $urlParts[1] ?? null;

$metaTitle = BRAND_NAME;
$metaDescription = "AI powered multivendor website that offers full customization ";
$metaKeywords = "Ai, e-commerce, brand, shop";

function getPagePath($page, $id = null)
{
    global $urlParts;

    // Check for flat file first
    $basePath = ROOT_PATH . '/pages/' . $page . '.php';
    if (file_exists($basePath)) {
        return $basePath;
    }

    // Dynamically build and check nested paths
    $currentPath = ROOT_PATH . '/pages';
    for ($i = 0; $i < count($urlParts); $i++) {
        $currentPath .= '/' . $urlParts[$i];
        $possibleFile = $currentPath . '.php';

        if (file_exists($possibleFile)) {
            return $possibleFile;
        }
    }

    return false;
}


$pagePath = getPagePath($page, $id);

if ($pagePath) {
    if ($id) {
        $_GET['id'] = $id;
    }


    switch ($page) {

        case 'index':
            $metaTitle = 'Welcome to ' . BRAND_NAME;
            $metaDescription = 'We bring the timeless charm of Italian pizza and craftsmanship to Nigeria, while staying true to the classic recipe, style, standards, and taste.';
            $metaKeywords = 'pizza, order pizza, delivery, PizzaSquare';
            break;

       
        case 'auth':
            $authPage = $_GET['id'] ?? '';
            if (strpos($authPage, 'login') !== false) {
                $metaTitle = 'Login to Your Account | ' . BRAND_NAME;
                $metaDescription = 'Access your personalized dashboard to manage your orders, products, and settings. Secure login for customers, vendors, and admins.';
                $metaKeywords = 'login, sign in, dealer access, customer login, admin login, multiuser platform';
            } else {
                $metaTitle = 'Authentication | ' . BRAND_NAME;
                $metaDescription = 'Secure authentication pages for accessing your ' . BRAND_NAME . ' account.';
                $metaKeywords = 'auth, login, register, forgot password';
            }
            break;
        case 'secure':
            $dashPage = $_GET['id'] ?? '';
            $metaTitle = ucfirst($dashPage) . ' | ' . BRAND_NAME;
            $metaDescription = 'Your personalized dashboard';
            $metaKeywords = 'Secure, dashboard';
            break;


        default:
            $metaTitle = BRAND_NAME;
            $metaDescription = '';
            $metaKeywords = '';
            break;
    }

    require_once $pagePath;
} else {
    require_once ROOT_PATH . '/pages/404.php';
}
