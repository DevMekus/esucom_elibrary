<?php

use App\Utils\Utility;
use App\Utils\HttpClient;

Utility::verifySession();

$user = $_SESSION['user_profile'] ?? null;

if (!$user || !isset($user['userid'])) {
    header('location: ' . BASE_URL . 'auth/login?f-bk=UNAUTHORIZED');
    exit;
}

$role   = $user['role'];
$userid = $user['userid'];

// Refresh user profile if not cached or expired
$cacheDuration = 86400;
$shouldRefresh = !isset($_SESSION['user_profile'])
    || !isset($_SESSION['profile_cached_at'])
    || (time() - $_SESSION['profile_cached_at']) > $cacheDuration;

if ($shouldRefresh) {
    //refresh profile from db
    session_destroy();
    header('location: ' . BASE_URL . 'auth/login?f-bk=Expr');
    exit;
} else {
    $user = $_SESSION['user_profile'];
}

//Get school info
$url =  "api/v1/school";
$token = $_SESSION['token'] ?? null;

 $http = new HttpClient(
        BASE_URL,
        [
            "Authorization" => "Bearer $token",
            "Accept" => "application/json",
            'Origin'=> BASE_URL
        ]
    );
$response = $http->get($url);

$categories = $response['data']['data']['categories'] ?? null;
$departments = $response['data']['data']['departments'] ?? null;

