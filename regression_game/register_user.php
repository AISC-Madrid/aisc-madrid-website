<?php
session_start();
include("../assets/db.php");

$email     = trim($_POST['email'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email']);
    exit;
}

// 1. Check if user already registered
$stmt = $conn->prepare("SELECT id, full_name FROM form_submissions WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    $user_id = $user['id'];
    $name    = $user['full_name'];
} else {
    // Not registered → create
        echo json_encode(['success' => false, 'message' => 'Not registered']);
        exit;
    
}

// 2. Check if user already submitted a guess
$stmt = $conn->prepare("SELECT COUNT(*) as c FROM regression_guesses WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$already_played = ($row['c'] > 0);

$_SESSION['player_id']   = $user_id;
$_SESSION['player_name'] = $name;
$_SESSION['player_email'] = $email;

echo json_encode([
    'success' => true,
    'user_id' => $user_id,
    'full_name' => $name,
    'email' => $email,
    'already_played' => $already_played
]);

$conn->close();
