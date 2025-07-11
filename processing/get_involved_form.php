<?php
// Replace with your actual database credentials
$host = 'localhost';
$db   = 'u803318305_aisc';
$user = 'u803318305_aisc';
$pass = 'Aisc_2025?';

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Get POST data
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');

// Basic validation
if ($name === '' || $email === '') {
  echo "Please fill in all required fields and give consent.";
  exit;
}

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO form_submissions (full_name, email, consent) VALUES (?, ?)");
$stmt->bind_param("ssi", $name, $email);

if ($stmt->execute()) {
  echo "Thanks for joining us!";
} else {
  if ($conn->errno === 1062) {
    echo "This email is already registered.";
  } else {
    echo "Error: " . $conn->error;
  }
}

$stmt->close();
$conn->close();
?>
