<?php
require '../assets/db.php';

$token = $_GET['token'] ?? '';

if (!$token) {
    echo "<h2>Enlace no válido</h2>";
    exit;
}

// Verificar que el token existe en la base de datos
$stmt = $conn->prepare("SELECT email FROM form_submissions WHERE unsubscribe_token = ? AND newsletter = 'yes' LIMIT 1");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<h2>El enlace no es válido o ya has cancelado la suscripción.</h2>";
    exit;
}

$email = $result->fetch_assoc()['email'];
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Cancelar suscripción</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center vh-100 bg-light">
  <div class="card shadow-lg p-4" style="max-width:500px;">
    <h3 class="mb-3">¿Cancelar suscripción?</h3>
    <p>El correo <strong><?php echo htmlspecialchars($email); ?></strong> dejará de recibir nuestras newsletters.</p>
    <p>¿Estás seguro de que quieres continuar?</p>
    <div class="d-flex justify-content-between mt-4">
      <!-- Formulario para confirmar -->
      <form action="unsubscribe_confirm.php" method="POST">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <button type="submit" class="btn btn-danger">Sí, cancelar</button>
      </form>
      <!-- Botón de cancelar -->
      <a href="/" class="btn btn-secondary">No, volver</a>
    </div>
  </div>
</body>
</html>
