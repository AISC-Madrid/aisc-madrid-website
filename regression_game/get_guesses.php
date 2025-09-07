<?php
header('Content-Type: application/json');
include('../assets/db.php'); // Your $conn mysqli connection

try {
    $query = "
        SELECT g.user_id, g.x1, g.y1, g.x2, g.y2, g.color, g.error, u.full_name
        FROM regression_guesses g
        JOIN form_submissions u ON g.user_id = u.id
        ORDER BY g.error ASC
    ";
    $result = $conn->query($query);

    $guesses = [];
    while ($row = $result->fetch_assoc()) {
        $x1 = floatval($row['x1']);
        $y1 = floatval($row['y1']);
        $x2 = floatval($row['x2']);
        $y2 = floatval($row['y2']);

        // Compute slope and intercept
        if ($x2 - $x1 != 0) {
            $slope = ($y2 - $y1) / ($x2 - $x1);
        } else {
            $slope = 0; // vertical line, handle as needed
        }
        $intercept = $y1 - $slope * $x1;

        $guesses[] = [
            'slope' => $slope,
            'intercept' => $intercept,
            'x1' => $x1,
            'x2' => $x2,
            'y1' => $y1,
            'y2' => $y2,
            'color' => $row['color'],
            'full_name' => $row['full_name'],
            'user_id' => $row['user_id'],
            'error' => $row['error']
        ];
    }

    echo json_encode($guesses);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
