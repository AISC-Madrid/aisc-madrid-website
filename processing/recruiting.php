<?php
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$position = trim($_POST['position'] ?? '');
$reason = trim($_POST['reason'] ?? '');
$consent = isset($_POST['consent']) ? 1 : 0;

$errors = [];

// Validación campo por campo
if ($name === '') $errors['error_name'] = 1;
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['error_email'] = 1;
if ($position === '') $errors['error_position'] = 1;
if ($reason === '' || strlen($reason) > 1000) $errors['error_reason'] = 1;
if ($consent !== 1) $errors['error_consent'] = 1;

// Si hay errores, redirigir a join.php con errores y valores previos
if (!empty($errors)) {
    $query = http_build_query(array_merge($errors, [
        'name' => $name,
        'email' => $email,
        'position' => $position,
        'reason' => $reason,
        'consent' => $consent
    ]));
    header("Location: /join.php?$query#get-involved");
    exit;
}

include("../assets/db.php");

// Verificar si el correo ya está en DB
$checkStmt = $conn->prepare("SELECT id FROM recruiting_2025 WHERE email = ?");
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    $checkStmt->close();
    $conn->close();
    header("Location: /join.php?error_duplicate=1&name=$name&email=$email&position=$position&reason=$reason&consent=$consent#recruiting-form");
    exit;
}
$checkStmt->close();

// Insertar en la DB
$stmt = $conn->prepare("INSERT INTO recruiting_2025 (full_name, email, position, interest) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $position, $reason);
$stmt->execute();
$stmt->close();

// Save in form_submission (newsletter) table if not already present
$checkForm = $conn->prepare("SELECT id FROM form_submission WHERE email = ?");
$checkForm->bind_param("s", $email);
$checkForm->execute();
$checkForm->store_result();

if ($checkForm->num_rows === 0) {
    $checkForm->close();

    // Generate unsubscribe token
    $unsubscribe_token = bin2hex(random_bytes(16)); 

    $stmt2 = $conn->prepare("INSERT INTO form_submission (full_name, email, unsubscribe_token) VALUES (?, ?, ?)");
    $stmt2->bind_param("sss", $name, $email, $unsubscribe_token);
    $stmt2->execute();
    $stmt2->close();
} else {
    $checkForm->close();
}

$conn->close();

// Sucess redirect
header("Location: /join.php?success=1#get-involved");
exit;
?>