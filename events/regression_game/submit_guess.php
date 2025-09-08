<?php
// submit_guess.php
header('Content-Type: application/json');
include('../assets/db.php'); // mysqli connection

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $x1 = floatval($_POST['x1'] ?? null);
    $y1 = floatval($_POST['y1'] ?? null);
    $x2 = floatval($_POST['x2'] ?? null);
    $y2 = floatval($_POST['y2'] ?? null);
    $color = ($_POST['color'] ?? null);
    $error = floatval($_POST['error'] ?? null);

    if (!$user_id || $x1 === null || $y1 === null || $x2 === null || $y2 === null) {
        $response['message'] = "Missing parameters.";
        echo json_encode($response);
        exit;
    }

    



    // Check if user already played
    $stmt = $conn->prepare("SELECT id FROM regression_guesses WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $response['message'] = "You have already submitted a guess.";
        $stmt->close();
        echo json_encode($response);
        exit;
    }
    $stmt->close();

    // Insert guess
    $stmt = $conn->prepare("INSERT INTO regression_guesses (user_id, x1, y1, x2, y2, color, error) VALUES (?,?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iddddsd", $user_id, $x1, $y1, $x2, $y2, $color, $error);

    if ($stmt->execute()) {
        $response['success'] = true;
    } else {
        $response['message'] = "Database error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
echo json_encode($response);
