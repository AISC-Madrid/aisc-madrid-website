<?php
// Get POST data
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$event_name = trim($_POST['event_name'] ?? '');
$description = trim($_POST['description'] ?? '');
$language = trim($_POST['language'] ?? '');
$consent = isset($_POST['consent']) ? 1 : 0;

$errors = [];

// Validation for each field
if ($name === '') $errors['error_name'] = 1;
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['error_email'] = 1;
if ($event_name === '' || strlen($event_name) > 100) $errors['error_event_name'] = 1;
if ($description === '' || strlen($description) > 1000) $errors['error_description'] = 1;
if (!in_array($language, ['spanish','english'])) $errors['error_language'] = 1;
if ($consent !== 1) $errors['error_consent'] = 1;

// Redirect if errors
if (!empty($errors)) {
    $query = http_build_query(array_merge($errors, [
        'name' => $name,
        'email' => $email,
        'event_name' => $event_name,
        'description' => $description,
        'language' => $language,
        'consent' => $consent
    ]));
    header("Location: /join.php?$query#collaboration-form");
    exit;
}

include("../assets/db.php"); // DB connection

// Verify if mail already in DB
$checkStmt = $conn->prepare("SELECT id FROM collaborations WHERE email = ?");
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    $checkStmt->close();
    $conn->close();
    $query = http_build_query([
        'error_duplicate' => 1,
        'name' => $name,
        'email' => $email,
        'event_name' => $event_name,
        'description' => $description,
        'language' => $language
    ]);
    header("Location: /join.php?$query#collaboration-form");
    exit;
}
$checkStmt->close();

// Inserta into DB
$stmt = $conn->prepare("INSERT INTO collaborations (name, email, event_name, description, language) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $name, $email, $event_name, $description, $language);
$stmt->execute();
$stmt->close();

// Fall into the newsletter dwell if user wasn't already
$checkForm = $conn->prepare("SELECT id FROM form_submissions WHERE email = ?");
$checkForm->bind_param("s", $email);
$checkForm->execute();
$checkForm->store_result();

if ($checkForm->num_rows === 0) {
    $checkForm->close();

    $unsubscribe_token = bin2hex(random_bytes(16)); 

    $stmt2 = $conn->prepare("INSERT INTO form_submissions (full_name, email, unsubscribe_token) VALUES (?, ?, ?)");
    $stmt2->bind_param("sss", $name, $email, $unsubscribe_token);
    $stmt2->execute();
    $stmt2->close();
} else {
    $checkForm->close();
}

$conn->close();

// Redirect if sucess
header("Location: /join.php?success=1#collaboration-form");
exit;
?>
