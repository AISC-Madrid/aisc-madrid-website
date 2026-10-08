<?php
session_start();

$allowed_roles = ['admin', 'web'];
if (!isset($_SESSION['activated']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: /");
    exit();
}
include(__DIR__ . "/../../assets/csrf.php");
include(__DIR__ . "/../../assets/db.php");
include(__DIR__ . "/../../assets/contacts.php");
require_once __DIR__ . "/../../assets/cloudinary.php";

$contact = [
    'full_name' => '',
    'greeting_name' => '',
    'email' => '',
    'category' => 'degree_director',
    'organization' => '',
    'notes' => '',
    'active' => 1,
];

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM contacts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) {
        die("<p style='color:red;'>❌ Contacto no encontrado.</p>");
    }
    $contact = $row;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Crear/Editar contacto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="<?= cdn('images/logos/AISC Logo Square.ico') ?>" type="image/x-icon">
</head>

<body class="bg-light">
    <div class="container my-5">
        <h1 class="mb-4"><?= isset($id) ? 'Editar contacto' : 'Nuevo contacto' ?></h1>

        <form action="save_contact.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
            <?php if (isset($id)): ?>
                <input type="hidden" name="id" value="<?= $id ?>">
            <?php endif; ?>

            <div class="row">
                <div class="mb-3 col-md-6">
                    <label class="form-label">Nombre completo</label>
                    <input type="text" name="full_name" class="form-control" required maxlength="150"
                        value="<?= htmlspecialchars($contact['full_name']) ?>">
                </div>

                <div class="mb-3 col-md-6">
                    <label class="form-label">Nombre para el saludo ("Buenos días ___"). Vacío = primer nombre</label>
                    <input type="text" name="greeting_name" class="form-control" maxlength="100" placeholder="María Carmen"
                        value="<?= htmlspecialchars($contact['greeting_name'] ?? '') ?>">
                </div>

                <div class="mb-3 col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required maxlength="255"
                        value="<?= htmlspecialchars($contact['email']) ?>">
                </div>

                <div class="mb-3 col-md-6">
                    <label class="form-label">Categoría</label>
                    <select name="category" class="form-select" required>
                        <?php foreach (CONTACT_CATEGORIES as $key => $label): ?>
                            <option value="<?= $key ?>" <?= $contact['category'] === $key ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3 col-md-6">
                    <label class="form-label">Activo</label>
                    <select name="active" class="form-select">
                        <option value="1" <?= $contact['active'] ? 'selected' : '' ?>>Sí</option>
                        <option value="0" <?= !$contact['active'] ? 'selected' : '' ?>>No (no aparece al enviar)</option>
                    </select>
                </div>

                <div class="mb-3 col-12">
                    <label class="form-label">Organización / titulación (completa "...para los alumnos del ___")</label>
                    <input type="text" name="organization" class="form-control" maxlength="300"
                        placeholder="Grado en Ingeniería Informática"
                        value="<?= htmlspecialchars($contact['organization'] ?? '') ?>">
                </div>

                <div class="mb-3 col-12">
                    <label class="form-label">Notas</label>
                    <textarea name="notes" class="form-control" rows="2"><?= htmlspecialchars($contact['notes'] ?? '') ?></textarea>
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><?= isset($id) ? 'Actualizar contacto' : 'Guardar contacto' ?></button>
            <a href="contacts_list.php" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div>
</body>

</html>
