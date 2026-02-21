<?php
// login.php - Centralized login for AISC Madrid
session_start();

// Destroy any existing session to force fresh login every time
if (isset($_SESSION['activated'])) {
    session_unset();
    session_destroy();
    session_start(); // Start a new clean session
}

include('assets/db.php'); // Your $conn mysqli connection

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputUser = trim($_POST['mail'] ?? '');
    $password = $_POST['password'] ?? '';

    $userFound = false;

    // First, try to find in members table (regular users)
    $mail = $inputUser . "@alumnos.uc3m.es";
    $stmt = $conn->prepare("SELECT id, mail, password_hash, role, full_name FROM members WHERE mail = ?");
    $stmt->bind_param("s", $mail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // Verify password for regular user
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = strtok($user['full_name'], ' ');
            $_SESSION['mail'] = $user['mail'];
            $_SESSION['activated'] = true;
            $_SESSION['role'] = $user['role'];
            header("Location: /dashboard/dashboard.php");
            exit();
        } else {
            $error = "Invalid username or password.";
            $userFound = true;
        }
    }
    $stmt->close();

    // If not found in members, try guests table
    if (!$userFound) {
        $stmt = $conn->prepare("SELECT id, username, password_hash, name, is_active FROM guests WHERE username = ?");
        $stmt->bind_param("s", $inputUser);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($guest = $result->fetch_assoc()) {
            $userFound = true;
            if (!$guest['is_active']) {
                $error = "This guest account is inactive.";
            } elseif (password_verify($password, $guest['password_hash'])) {
                $_SESSION['user_id'] = $guest['id'];
                $_SESSION['name'] = $guest['name'] ?: $guest['username'];
                $_SESSION['username'] = $guest['username'];
                $_SESSION['activated'] = true;
                $_SESSION['role'] = 'guest';
                header("Location: /dashboard/guests/guest_dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password.";
            }
        }
        $stmt->close();
    }

    if (!$userFound) {
        $error = "Invalid username or password.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<?php include('assets/head.php'); ?>

<body class="bg-light">

    <div class="container my-5 px-3">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-8 col-md-6 col-lg-4 bg-white p-4 rounded shadow">

                <div class="d-flex align-items-center justify-content-center mb-4 flex-wrap text-center">
                    <img src="/images/logos/PNG/AISC Logo Color.png" alt="Logo AISC Madrid" class="me-2 mb-2"
                        style="width: 14vw; max-width: 60px; min-width: 45px; height: auto; object-fit: contain;">
                    <h3 class="m-0" style="color: black;">Login Portal AISC Madrid</h3>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger text-center py-2">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST">
                    <div class="mb-3">
                        <label class="form-label">NIA / Username</label>
                        <input type="text" name="mail" class="form-control form-control-lg" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control form-control-lg" required>
                    </div>
                    <button type="submit" class="btn form-btn w-100">Login</button>
                </form>
            </div>
        </div>
    </div>

</body>

</html>
