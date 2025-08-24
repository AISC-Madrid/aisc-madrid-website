<?php
require '../assets/db.php';

$token = $_POST['token'] ?? '';

if ($token) {
    $stmt = $conn->prepare("UPDATE form_submissions SET newsletter = 'no' WHERE unsubscribe_token = ? AND newsletter = 'yes'");
    $stmt->bind_param("s", $token);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $message = "Tu suscripción ha sido cancelada correctamente ✅";
    } else {
        $message = "El enlace no es válido o ya estabas dado de baja.";
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Suscripción cancelada</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center vh-100 bg-light">
  <div class="card shadow-lg p-4 text-center" style="max-width:500px;">
    <h3><?php echo $message; ?></h3>
    <a href="/" class="btn btn-primary mt-3">Volver al inicio</a>
  </div>
</body>
</html>
