<?php


// Get the requested filename from query param
$filename = $_GET['file'] ?? '';



// Sanitize: allow only alphanumeric, dots, dashes, underscores
$filename = basename(preg_replace('/[^a-zA-Z0-9._\-]/', '', $filename));

if (empty($filename)) {
    http_response_code(400);
    exit('No file specified.');
}

// Resolve the absolute path to the file
$filePath = realpath(__DIR__ . '/UPLOADS/ebooks/' . $filename);

// Security: make sure the resolved path is still within the allowed directory
$allowedDir = realpath(__DIR__ . '/UPLOADS/ebooks/');


if (!$filePath || strpos($filePath, $allowedDir) !== 0) {
    http_response_code(403);
    exit('Access denied.');
}

if (!file_exists($filePath)) {
    http_response_code(404);
    exit('File not found.');
}



// Stream the PDF to the browser (inline = open, not download)
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

readfile($filePath);
exit;