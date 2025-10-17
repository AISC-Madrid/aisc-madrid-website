<?php
// login.php
session_start();
include('../assets/db.php'); // Your $conn mysqli connection
include("/assets/head.php");

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mail = $_POST['mail']."@alumnos.uc3m.es" ?? '';
    $password = $_POST['password'] ?? '';

    // Prepare and execute query
    $stmt = $conn->prepare("SELECT id, mail, password_hash, role FROM members WHERE mail = ?");
    $stmt->bind_param("s", $mail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // Verify password
        if (password_verify($password, $user['password_hash'])) {
            // Start session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['mail'] = $user['mail'];
            $_SESSION['activated'] = true;
            $_SESSION['role'] = $user['role'];
            header("Location: ../dashboard/dashboard.php"); // Redirect after login
            exit();
        } else {
            $error = "Invalid mail or password.";
        }
    } else {
        $error = "Invalid mail or password.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-5 px-3">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-8 col-md-6 col-lg-4 bg-white p-4 rounded shadow">
            
            <div class="d-flex align-items-center justify-content-center mb-4 flex-wrap text-center">
                <img src="/images/logos/PNG/AISC Logo Color.png" alt="Logo AISC Madrid"
                     class="me-2 mb-2"
                     style="width: 14vw; max-width: 60px; min-width: 45px; height: auto; object-fit: contain;">
                <h3 class="m-0 fs-5 fs-md-4">Login Portal AISC Madrid</h3>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger text-center py-2">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label">NIA</label>
                    <input type="text" name="mail" class="form-control form-control-lg" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control form-control-lg" required>
                </div>
                <button type="submit" class="btn btn-primary form-btn w-100">Login</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>
