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

$host = 'localhost';
$port = 3306;
$db   = $config['db_name'];
$user = $config['db_user'];
$pass = $config['db_pass'];

// Conexión
@$conn = new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) {
    header("Location: /?error=connection#get-involved");
    exit;
}
?>
