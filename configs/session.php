<?php
header('Content-Type: application/json');

// Prevent direct access
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

session_start();
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action'])) {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

switch ($input['action']) {
    case 'set':
        if (isset($input['token'], $input['role'], $input['userid'])) {
            $_SESSION['token'] = $input['token'];
            $_SESSION['role'] = $input['role'];
            $_SESSION['userid'] = $input['userid'];
            $_SESSION['last_refresh'] = time(); // Save refresh time
            echo json_encode(["success" => true, "message" => "Session set"]);
            exit;
        }
        break;

    case 'refresh':
        if (isset($_SESSION['token'])) {
            $_SESSION['last_refresh'] = time(); // Just update refresh time
            echo json_encode(["success" => true, "message" => "Session refreshed"]);
            exit;
        }
        break;
    case 'unset-p':
        if (isset($_SESSION['user_profile'])) {
            unset($_SESSION['user_profile']);
        }

        break;
    case 'set-p':
        $_SESSION['user_profile'] = $input['user'];
        $_SESSION['profile_cached_at'] = time();
        $_SESSION['userid'] = $input['user']['userid'];
        break;

    case 'set-branch':
        $_SESSION['branches'] = $input['branches'];       
        break;
        
    case 'config':
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 200,
            'message' => 'Application data',
            'ENCRYPTION_KEY' => 'd10b86de4e86d5f6636b96f041f10ded5346a6c760d8d981a6690fbef7c87132',
            'PAYSTACK_PK' => 'pk_test_5a69848631eaa83428c8a28cad37e3227c6f17e7',
            'success' => true,
        ]);
        exit;
        break;

    case 'clear':
        session_unset();
        session_destroy();
        echo json_encode(["success" => true, "message" => "Session cleared"]);
        exit;
}

// Default response if nothing else matched
echo json_encode(["success" => false, "message" => "Unhandled request"]);
exit;
