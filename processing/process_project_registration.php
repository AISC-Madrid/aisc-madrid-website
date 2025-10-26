<?php
// Cargar las clases de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; 

include_once '../assets/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger y limpiar los datos del formulario
    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $project_id = filter_input(INPUT_POST, 'project_id', FILTER_VALIDATE_INT);

    // Validación de datos
    if (empty($name) || !$email || !$project_id) {
        header("Location: /projects/project_registration.php?id=$project_id&error_validation=1&name=".urlencode($name)."&email=".urlencode($_POST['email']));
        exit;
    }

    // Comprobar si ya está inscrito en ESTE proyecto (evitar duplicados)
    $sql_check_project = "SELECT id FROM project_registrations WHERE project_id = ? AND email = ?";
    $stmt_check_project = $conn->prepare($sql_check_project);
    $stmt_check_project->bind_param("is", $project_id, $email);
    $stmt_check_project->execute();
    $stmt_check_project->store_result();

    if ($stmt_check_project->num_rows > 0) {
        $stmt_check_project->close();
        header("Location: /projects/project_registration.php?id=$project_id&error_duplicate=1");
        exit;
    }
    $stmt_check_project->close();

    // Intentar añadirlo a la tabla de newsletter (si no está ya)
    $sql_check_newsletter = "SELECT id FROM form_submissions WHERE email = ?";
    $stmt_check_newsletter = $conn->prepare($sql_check_newsletter);
    $stmt_check_newsletter->bind_param("s", $email);
    $stmt_check_newsletter->execute();
    $stmt_check_newsletter->store_result();

    if ($stmt_check_newsletter->num_rows <= 0) {
        $token = bin2hex(random_bytes(16));
        $stmtInsert = $conn->prepare("INSERT INTO form_submissions (full_name, email, newsletter, unsubscribe_token) VALUES (?, ?, 'yes', ?)");
        $stmtInsert->bind_param("sss", $full_name, $email , $token);
        if ($stmtInsert->execute()) {
            $user_id = $stmtInsert->insert_id;
            $name = $full_name;
            
        }
    }

        
    // Inscribir al usuario en el proyecto
    $sql_insert_registration = "INSERT INTO project_registrations (project_id, name, email) VALUES (?, ?, ?)";
    $stmt_insert_registration = $conn->prepare($sql_insert_registration);
    if ($stmt_insert_registration) {
        $stmt_insert_registration->bind_param("iss", $project_id, $name, $email);
        
        if ($stmt_insert_registration->execute()) {
            // Inserción exitosa, redirigir a la página de éxito
            header("Location: /projects/project_registration.php?id=$project_id&success=1");
        } else {
            // Error en la inserción en el proyecto
            header("Location: /projects/project_registration.php?id=$project_id&error_db=1");
        }
        $stmt_insert_registration->close();
    } else {
        // Error general de la base de datos
        header("Location: /projects/project_registration.php?id=$project_id&error_db=1");
    }

    $conn->close();
    exit;

} else {
    // Si no es una solicitud POST, redirigir al inicio
    header("Location: /index.php");
    exit;
}
?>