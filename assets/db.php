<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Datos de conexión
$config = include(__DIR__ . '/../config.php');

$host = 'localhost';
$db   = $config['db_name'];
$user = $config['db_user'];
$pass = $config['db_pass'];

// Conexión
@$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    header("Location: /?error=connection#get-involved");
    exit;
}
?>
