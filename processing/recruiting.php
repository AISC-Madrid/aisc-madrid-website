<?php

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$position = trim($_POST['position'] ?? '');
$reason = trim($_POST['reason'] ?? '');
$consent = isset($_POST['consent']) ? 1 : 0;

// Validación básica
if ($name === '' || $email === '' || $position === '' || $reason === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: /join.php?error=validation#get-involved");
    exit;
}

include("../assets/db.php");

// Verificar duplicado
$checkStmt = $conn->prepare("SELECT id FROM recruiting_2025 WHERE email = ?");
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    $checkStmt->close();
    $conn->close();
    header("Location: /join.php?error=duplicate#get-involved");
    exit;
}
$checkStmt->close();

// Insertar en DB
$stmt = $conn->prepare("INSERT INTO recruiting_2025 (full_name, email, position, interest) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $position, $reason);
$stmt->execute();
$stmt->close();
$conn->close();

// Redirigir con parámetro de éxito
header("Location: /join.php?success=1#get-involved");
exit;
?>
