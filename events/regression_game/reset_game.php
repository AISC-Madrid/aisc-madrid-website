<?php
// reset_game.php
include('../assets/db.php'); // your $conn mysqli connection

header('Content-Type: application/json');

if ($conn->query("TRUNCATE TABLE regression_guesses")) {
    echo json_encode([
        'success' => true,
        'message' => 'All guesses have been reset.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error resetting guesses: ' . $conn->error
    ]);
}

$conn->close();
