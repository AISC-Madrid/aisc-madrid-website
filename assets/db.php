<?php
// Datos de conexión
$config = include(__DIR__ . '/../config.php');

$_is_admin_page = str_contains($_SERVER['SCRIPT_FILENAME'] ?? '', '/dashboard/') ||
                  str_contains($_SERVER['SCRIPT_FILENAME'] ?? '', '/events/insert') ||
                  str_contains($_SERVER['SCRIPT_FILENAME'] ?? '', '/events/update') ||
                  str_contains($_SERVER['SCRIPT_FILENAME'] ?? '', '/events/create') ||
                  str_contains($_SERVER['SCRIPT_FILENAME'] ?? '', '/projects/insert') ||
                  str_contains($_SERVER['SCRIPT_FILENAME'] ?? '', '/projects/update') ||
                  str_contains($_SERVER['SCRIPT_FILENAME'] ?? '', '/projects/create');

if ($_is_admin_page || !empty($config['dev_mode'])) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}
error_reporting(E_ALL);
ini_set('log_errors', 1);

$host = $config['db_host'];
$port = $config['db_port'];
$db   = $config['db_name'];
$user = $config['db_user'];
$pass = $config['db_pass'];

// Conexión
// mysqli throws on connection failure since PHP 8.1, so catch it instead of crashing with a 500
try {
    $conn = new mysqli($host, $user, $pass, $db, $port);
} catch (mysqli_sql_exception $e) {
    error_log('DB connection failed (' . $host . ':' . $port . '): ' . $e->getMessage());
    http_response_code(503);
    echo 'Database unavailable. Please try again later.';
    exit;
}
?>
