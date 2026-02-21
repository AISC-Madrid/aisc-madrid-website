<?php
session_start();
include_once '../assets/db.php';

header('Content-Type: application/json');

// Allow any authenticated user
if (!isset($_SESSION['activated'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!isset($data['email']) || !isset($data['event_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing email or event_id']);
    exit();
}

$email = $data['email'];
$event_id = (int) $data['event_id'];

// If user is a guest, verify they have access to this specific event
if ($_SESSION['role'] === 'guest') {
    $guest_id = $_SESSION['user_id'];
    $access_stmt = $conn->prepare("SELECT 1 FROM event_guest_access WHERE guest_id = ? AND event_id = ?");
    $access_stmt->bind_param("ii", $guest_id, $event_id);
    $access_stmt->execute();
    $access_result = $access_stmt->get_result();
    
    if ($access_result->num_rows === 0) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You are not authorized for this event']);
        exit();
    }
    $access_stmt->close();
}

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
        echo json_encode(['success' => true, 'message' => 'Attendance updated successfully.']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error updating attendance.']);
    }
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'User is not registered for this event.']);
}

$stmt->close();
$conn->close();
?>