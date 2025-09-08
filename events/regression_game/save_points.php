<?php
include('../assets/db.php'); // $conn = mysqli connection

// Read JSON body
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !is_array($data)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit;
}

// Clear old points
$conn->query("TRUNCATE TABLE regression_points");

// Prepare insert
$stmt = $conn->prepare("INSERT INTO regression_points (x, y) VALUES (?, ?)");

foreach ($data as $point) {
    $x = $point['x'];
    $y = $point['y'];
    $stmt->bind_param("dd", $x, $y);
    $stmt->execute();
}

$stmt->close();
$conn->close();

echo json_encode(["success" => true, "message" => "Points saved"]);
