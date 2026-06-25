<?php

namespace App\Utils;


class Utility
{
    public static $API_ROUTE = "/esucomlib/api";
    public static $siteName = '';

    public static $student_tbl = 'students';
    public static $accounts_tbl = 'accounts';
    public static $admin_tbl = 'admins';
    public static $research_databases_tbl = 'research_databases';
    public static $ejournals_tbl = 'ejournal_main';
    public static $opac_catalog_tbl = 'opac';
    public static $ebooks_tbl = 'ebooks';
    public static $subjects_tbl = 'subjects';
    public static $notifications_tbl = 'notifications';
    public static $departments_tbl = 'departments';
    public static $category_tbl = 'category';
    public static $logs = '';

    public static $recordLimit = 10;


    public static function setRecordLimit(int $limit){
        Utility::$recordLimit = $limit;
    }

    public static function dateTime(){
        return date('Y-m-d H:i:s');
    }

    public static function returnDate(){
        return date('Y-m-d');
    }
     

    public static function debugger()
    {
        $logFile = __DIR__ . "/debug.log";
        $message = "[" . date("Y-m-d H:i:s") . "] Code reached here\n";
        file_put_contents($logFile, $message, FILE_APPEND);
    }

    

    public static function generate_uniqueId()
    {
        try {

            $number = random_int(1000000, 9999999);
            return "ps-" . $number;
        } catch (\Exception $e) {
            return false;
        }
    }


   

    static function log(
        string $message,
        string $level = 'info',
        string $context = 'general',
        array $extra = [],
        ?\Throwable $exception = null
    ) {
        $logDir = __DIR__ . "/../../logs";

        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $logFile = $logDir . '/' . date('Y-m-d') . '.log';
        $logEntry = [
            'timestamp' => date('c'),
            'level' => strtolower($level),
            'context' => $context,
            'message' => $message,
            'extra' => $extra
        ];

        if ($exception) {
            $logEntry['exception'] = [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'code' => $exception->getCode(),
                'trace' => $exception->getTraceAsString()
            ];
        }

        file_put_contents($logFile, json_encode($logEntry) . PHP_EOL, FILE_APPEND);
    }


    

    public static function uploadDocuments(string $inputName, string $targetDir): array
    {
        if (!isset($_FILES[$inputName])) {
            throw new \RuntimeException("No files found for input: {$inputName}");
        }

        $fileInput = $_FILES[$inputName];

        // Normalize single file into array format
        $files = is_array($fileInput['name'])
            ? self::normalizeMultipleFiles($fileInput)
            : self::normalizeSingleFile($fileInput);

        $absoluteDir = rtrim(BASE_DIR, '/') . '/' . trim($targetDir, '/') . '/';

        if (!is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0755, true);
        }

        $uploaded = [];

        foreach ($files as $file) {

            self::validateFile($file);

            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $uniqueName = uniqid('upload_', true) . '.' . $extension;

            $destination = $absoluteDir . $uniqueName;

            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                throw new \RuntimeException("Failed to move file: {$file['name']}");
            }

            $uploaded[] = rtrim(BASE_URL, '/') . '/' . trim($targetDir, '/') . '/' . $uniqueName;
        }

        return [
            'success' => true,
            'files' => $uploaded
        ];
    }

    private static function normalizeSingleFile(array $file): array
    {
        return [
            [
                'name' => $file['name'],
                'type' => $file['type'],
                'tmp_name' => $file['tmp_name'],
                'error' => $file['error'],
                'size' => $file['size']
            ]
        ];
    }

    private static function normalizeMultipleFiles(array $files): array
    {
        $normalized = [];

        foreach ($files['name'] as $i => $name) {
            $normalized[] = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];
        }

        return $normalized;
    }


    private static function validateFile(array $file): void
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException("Upload error for {$file['name']}");
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        $allowedExtensions = [
            'jpg', 'jpeg', 'png', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx'
        ];

        if (!in_array($extension, $allowedExtensions)) {
            throw new \RuntimeException("Invalid file extension: {$extension}");
        }

        if ($file['size'] > 10 * 1024 * 1024) {
            throw new \RuntimeException("File too large: {$file['name']}");
        }

        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $allowedMimeTypes = [
                'image/jpeg',
                'image/png',
                'image/webp',
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ];

            if (!in_array($mime, $allowedMimeTypes)) {
                throw new \RuntimeException("Invalid MIME type for {$file['name']}");
            }
        }
    }




    public static function verifySession()
    {
        if (!isset($_SESSION['user_profile'], $_SESSION['token'])) {
        
            header("Location: " . BASE_URL . "auth/login?f-bk=UNAUTHORIZED");
            exit;
        }
        
        if (self::isJwtExpired($_SESSION['token'])){
            header("Location: " . BASE_URL . "auth/login?f-bk=UNAUTHORIZED");
        }
            
    }

    static function isJwtExpired($token)
    {
        
        $parts = explode(".", $token);
        
        if (count($parts) !== 3) return true;
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        if (!isset($payload['exp'])) return true;
        
        return $payload['exp'] < time();
    }

    /**
     * Get the current route relative to the base folder.
     *
     * @return string
     */
        public static function currentRoute(): string
        {
            $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
            $baseFolder = '/';



            if (strpos($requestUri, $baseFolder) === 0) {
                $requestUri = substr($requestUri, strlen($baseFolder));
            }



            return trim(parse_url($requestUri, PHP_URL_PATH) ?? '', '/');
        }
    /**
     * Get the current route relative to the base folder.
     *
     * @return string
     */


    public static function truncateText(string $text, int $limit = 100): string
    {
        if (mb_strlen($text) <= $limit) {
            return $text;
        }
        return mb_substr($text, 0, $limit) . '...';
    }

    /**
     * Get the client's IP address.
     *
     * @return string
     */
    public static function getUserIP(): string
    {
        return $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Get the client's device user agent string.
     *
     * @return string
     */
    public static function getUserDevice(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }

    public static function getDateTime(){
        return  date('y-m-d H:m:s', time());
    }
   
}
