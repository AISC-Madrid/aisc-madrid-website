<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['activated']) || $_SESSION['role'] !== 'admin') {
    header("Location: events/login.php");
    exit();
}


include(__DIR__ . "/../../assets/db.php");

// Initialize variables for the form
$full_name = $mail = $position_es = $position_en = $password = '';
$phone = $dni = $socials = $active = $board = $image_path = '';

// Check if an ID is passed
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $member = $result->fetch_assoc();

    if ($member) {
        $full_name = $member['full_name'];
        $mail = $member['mail'];
        $password = $member['password_hash'];
        $position_es = $member['position_es'];
        $position_en = $member['position_en'];
        $phone = $member['phone'];
        $dni = $member['dni'];
        $socials = $member['socials'];
        $board = $member['board'];
        $active = $member['active'];
        $image_path = $member['image_path'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear/Editar miembro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container my-5">
    <div class="row wrap">
        <h1 class="mb-4"><?= isset($id) ? 'Editar Miembro' : 'Crear Nuevo Miembro' ?></h1>

        <form action="<?= isset($id) ? 'update_team_member.php' : 'add_team_member.php' ?>" method="POST">
    <?php if(isset($id)): ?>
        <input type="hidden" name="id" value="<?= $id ?>">
    <?php endif; ?>

    <div class="row justify-content-center">
        <!-- Name -->
        <div class="mb-3 col-6">
            <label class="form-label">Nombre Completo</label>
            <input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($full_name) ?>">
        </div>

        <!-- Mail -->
        <div class="mb-3 col-6">
            <label class="form-label">Mail</label>
            <input type="text" name="mail" class="form-control" required value="<?= htmlspecialchars($mail) ?>">
        </div>
        <!-- Password -->
        <div>
            <label class="form-label">Password</label>
            <input type="text" name="password" class="form-control">
        </div>

        <!-- position_es -->
        <div class="mb-3 col-6">
            <label class="form-label">Posición (Español)</label>
            <input type="text" name="position_es" class="form-control" value="<?= htmlspecialchars($position_es) ?>">
        </div>

        <!-- position_en -->
        <div class="mb-3 col-6">
            <label class="form-label">Position (English)</label>
            <input type="text" name="position_en" class="form-control" value="<?= htmlspecialchars($position_en) ?>">
        </div>

        <!-- phone -->
        <div class="mb-3 col-6">
            <label class="form-label">phone</label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($phone) ?>">
        </div>

        <!-- dni -->
        <div class="mb-3 col-6">
            <label class="form-label">DNI</label>
            <input type="text" name="dni" class="form-control" value="<?= htmlspecialchars($dni) ?>">
        </div>

        <!-- socials -->
        <div class="mb-3 col-6">
            <label class="form-label">Redes Sociales</label>
            <textarea name="socials" class="form-control" rows="4"><?= htmlspecialchars($socials) ?></textarea>
        </div>

        <!-- board -->
        <div class="mb-3 col-6">
            <label class="form-label">Board</label>
            <select name="board" class="form-select">
                <option value="yes" <?= ($board === 'yes') ? 'selected' : '' ?>>Yes</option>
                <option value="no" <?= ($board === 'no') ? 'selected' : '' ?>>No</option>
            </select>
        </div>

        <!-- active -->
        <div class="mb-3 col-6">
            <label class="form-label">Activo</label>
            <select name="active" class="form-select">
                <option value="yes" <?= ($active === 'yes') ? 'selected' : '' ?>>Yes</option>
                <option value="no" <?= ($active === 'no') ? 'selected' : '' ?>>No</option>
            </select>
        </div>

        <!-- Image path -->
        <div class="mb-3">
            <label class="form-label">Ruta de imagen</label>
            <input type="text" name="image_path" class="form-control" value="<?= htmlspecialchars($image_path) ?>">
        </div>

        <!-- Submit -->
        <button type="submit" class="btn btn-primary col-6">
            <?= isset($id) ? 'Actualizar Miembro' : 'Guardar Miembro' ?>
        </button>
    </div>
</form>

    </div>
</div>
</body>
</html>
