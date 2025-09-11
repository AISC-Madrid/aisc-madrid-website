<?php
// save_data.php
include('../../assets/db.php'); // your mysqli $conn connection

$full_name      = trim($_POST['full_name'] ?? '');
$email          = trim($_POST['email'] ?? '');
$degree         = $_POST['degree'] ?? '';
$course_year    = $_POST['course_year'] ?? '';
$interests      = isset($_POST['ai_topics']) ? implode(', ', $_POST['ai_topics']) : '';
$expectations   = $_POST['expectations'] ?? '';
$knowledge_level = $_POST['knowledge_level'] ?? '';
$consent        = isset($_POST['consent']) ? 1 : 0;

$errors = [];

// Simple validation
if (!$full_name) $errors[] = "Full name is required";
if (!$email) $errors[] = "Email is required";
if (!$degree) $errors[] = "Degree is required";
if (!$course_year) $errors[] = "Course year is required";
if (!$consent) $errors[] = "Consent is required";

if ($errors) {
    $error_str = urlencode(implode(', ', $errors));
    header("Location: index.php?error=$error_str");
    exit;
}

// Generate a unique unsubscribe token (32 characters)
$unsubscribe_token = bin2hex(random_bytes(16));

// Insert or update if email already exists
$sql = "INSERT INTO form_submissions
        (full_name, email, degree, course_year, interests, expectations, knowledge_level, unsubscribe_token)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        full_name=VALUES(full_name),
        degree=VALUES(degree),
        course_year=VALUES(course_year),
        interests=VALUES(interests),
        expectations=VALUES(expectations),
        knowledge_level=VALUES(knowledge_level),
        unsubscribe_token=VALUES(unsubscribe_token)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    $error_str = urlencode('Database error: ' . $conn->error);
    header("Location: ?error=$error_str");
    exit;
}

$stmt->bind_param(
    "ssssssss",
    $full_name,
    $email,
    $degree,
    $course_year,
    $interests,
    $expectations,
    $knowledge_level,
    $unsubscribe_token
);

if ($stmt->execute()) {
    header("Location: index.php?success=Your information has been saved successfully!");
} else {
    $error_str = urlencode('Database error: ' . $stmt->error);
    header("Location: results.php?error=$error_str");
}

$stmt->close();
$conn->close();
