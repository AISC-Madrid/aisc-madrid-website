<?php
session_start();
include_once '../assets/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['activated']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit();
}

$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!isset($data['email']) || !isset($data['event_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing email or event_id']);
    exit();
}

$email = $data['email'];
$event_id = $data['event_id'];

// Check if the user is registered for the event
$registration_query = "SELECT * FROM event_registrations WHERE email = ? AND event_id = ?";
$stmt = $conn->prepare($registration_query);
$stmt->bind_param("si", $email, $event_id);
$stmt->execute();
$registration_result = $stmt->get_result();

if ($registration_result->num_rows > 0) {
    // Update attendance status
    $update_query = "UPDATE event_registrations SET attendance_status = 'attended' WHERE email = ? AND event_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $email, $event_id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Attendance updated successfully.']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Error updating attendance.']);
    }
} else {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'User is not registered for this event.']);
}

$stmt->close();
$conn->close();
?>