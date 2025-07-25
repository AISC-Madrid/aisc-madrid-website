<?php
// Datos de conexión
$host = 'localhost';
$db   = 'u803318305_aisc';
$user = 'u803318305_aisc';
$pass = 'Aisc_2025?';

// Conexión
@$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    header("Location: /?error=connection#get-involved");
    exit;
}
?>