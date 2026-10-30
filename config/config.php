<?php

define('DEBUG', false);

if (DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}


define('DB_HOST', getenv('APP_DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('APP_DB_PORT') ?: '3306');
define('DB_NAME', getenv('APP_DB_NAME') ?: 'student_registration_system');
define('DB_USER', getenv('APP_DB_USER') ?: 'root');
define('DB_PASS', getenv('APP_DB_PASS') ?: '');   // XAMPP default root password is empty


define('SITE_NAME', 'Student Registration System');
define('STUDENTS_PER_PAGE', 10);   // pagination size required by the brief

/* Absolute filesystem path of the project root (…/student-registration-system) */
define('BASE_PATH', dirname(__DIR__));

/* Web path of the project, e.g. "/student-registration-system".
   It is detected automatically so the project works from any folder. */
if (!defined('BASE_URL')) {
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    // When the current script lives in a sub-folder (e.g. /ajax) go one level up.
    if (preg_match('#/(ajax|api)$#', $scriptDir)) {
        $scriptDir = dirname($scriptDir);
    }
    $scriptDir = rtrim($scriptDir, '/');
    define('BASE_URL', $scriptDir === '' ? '' : $scriptDir);
}

/* Upload directory for profile pictures */
define('UPLOAD_DIR', BASE_PATH . '/uploads');
define('UPLOAD_URL', BASE_URL . '/uploads');
define('MAX_UPLOAD_BYTES', 2 * 1024 * 1024);   // 2 MB
define('ALLOWED_MIME', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_EXT',  ['jpg', 'jpeg', 'png', 'gif', 'webp']);


date_default_timezone_set('Asia/Damascus');


function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,   // real prepared statements
            PDO::ATTR_STRINGIFY_FETCHES  => false,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        $message = DEBUG
            ? 'Database connection failed: ' . $e->getMessage()
            : 'Database connection failed. Please check config/config.php';
        exit('<div style="font-family:sans-serif;padding:2rem;color:#b02a37">'
            . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</div>');
    }

    return $pdo;
}
