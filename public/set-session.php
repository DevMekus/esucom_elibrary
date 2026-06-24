<?php
header('Content-Type: application/json');

// Optional: Add security checks (e.g., validate request method, origin, or token)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Include session.php (adjust path as needed)
require_once __DIR__ . '/../configs/session.php';
