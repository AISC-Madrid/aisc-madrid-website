<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('../assets/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get and sanitize form data
    $project_id = filter_input(INPUT_POST, 'project_id', FILTER_VALIDATE_INT);
    $name = htmlspecialchars(trim($_POST['name'])); 
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $consent = isset($_POST['consent']);

    // Basic validation
    if (!$project_id || !$name || !$email || !$consent) {
        header("Location: /projects/project_registration.php?id=$project_id&error_validation=1");
        exit;
    }

    // Check for duplicate registration
    $stmt = $conn->prepare("SELECT id FROM project_registrations WHERE project_id = ? AND email = ?");
    $stmt->bind_param("is", $project_id, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        header("Location: /projects/project_registration.php?id=$project_id&error_duplicate=1");
        exit;
    }
    $stmt->close();

    // Insert new registration
    $stmt = $conn->prepare("INSERT INTO project_registrations (project_id, name, email) VALUES (?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("iss", $project_id, $name, $email);

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

        header("Location: /projects/project_registration.php?id=$project_id&success=1");
        exit;
    } else {
        // Handle insertion error
        $stmt->close();
        header("Location: /projects/project_registration.php?id=$project_id&error_db=1");
        exit;
    }

} else {
    // Redirect if not a POST request
    header("Location: /index.php");
    exit;
}
?>