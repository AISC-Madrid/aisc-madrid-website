<?php
require '../assets/csrf.php';
if ($_SERVER["REQUEST_METHOD"] == "POST" && !validate_csrf_token($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    die("Token CSRF inválido.");
}

require '../vendor/autoload.php';

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
    $stmt = $conn->prepare("INSERT INTO event_registrations (event_id, name, email, qr_email_sent) VALUES (?, ?, ?, 0)");
    if (!$stmt) {
        error_log("DB prepare failed in " . __FILE__ . ": " . $conn->error);
        http_response_code(500);
        die("Error interno del servidor.");
    }
    
    $stmt->bind_param("iss", $event_id, $name, $email);

    if ($stmt->execute()) {
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

        // The confirmation email is sent by the email worker (mails/workers/send_registration_emails.php)
        // within ~30 seconds, so this request never waits on Gmail.

        header("Location: /events/event_registration.php?id=$event_id&success=1");
        exit;
    } else {
        $stmt->close();
        header("Location: /events/event_registration.php?id=$event_id&error_db=1");
        exit;
    }

} else {
    header("Location: /index.php");
    exit;
}
?>