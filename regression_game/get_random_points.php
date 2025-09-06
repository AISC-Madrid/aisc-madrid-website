<?php
header('Content-Type: application/json');
include('../assets/db.php'); // your mysqli connection

$result = $conn->query("SELECT x, y FROM regression_points ORDER BY id ASC");
$points = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $points[] = [
            'x' => floatval($row['x']),
            'y' => floatval($row['y'])
        ];
    }
}

echo json_encode($points);
$conn->close();
