<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../assets/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get and sanitize form data
    $event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
    $name = htmlspecialchars(trim($_POST['name'])); 
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $consent = isset($_POST['consent']);

    // Basic validation
    if (!$event_id || !$name || !$email || !$consent) {
        header("Location: /events/event_registration.php?id=$event_id&error_validation=1");
        exit;
    }

    // Check for duplicate registration
    $stmt = $conn->prepare("SELECT id FROM event_registrations WHERE event_id = ? AND email = ?");
    $stmt->bind_param("is", $event_id, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        header("Location: /events/event_registration.php?id=$event_id&error_duplicate=1");
        exit;
    }
    $stmt->close();

    // Insert new registration
    $stmt = $conn->prepare("INSERT INTO event_registrations (event_id, name, email) VALUES (?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("iss", $event_id, $name, $email);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: /events/event_registration.php?id=$event_id&success=1");
        exit;
    } else {
        // Handle insertion error
        $stmt->close();
        header("Location: /events/event_registration.php?id=$event_id&error_db=1");
        exit;
    }

} else {
    // Redirect if not a POST request
    header("Location: /index.php");
    exit;
}
?>