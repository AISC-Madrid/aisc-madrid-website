<?php
session_start();

$allowed_roles = ['admin', 'web'];
if (!isset($_SESSION['activated']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: /");
    exit();
}
include(__DIR__ . "/../../assets/csrf.php");
include(__DIR__ . "/../../assets/head.php");
include(__DIR__ . "/../../assets/db.php");
include(__DIR__ . "/../../assets/contacts.php");
include(__DIR__ . "/../../assets/nav_dashboard.php");

$category_filter = $_GET['category'] ?? '';
if ($category_filter !== '' && !isset(CONTACT_CATEGORIES[$category_filter])) {
    $category_filter = '';
}

if ($category_filter !== '') {
    $stmt = $conn->prepare("SELECT * FROM contacts WHERE category = ? ORDER BY category, organization, full_name");
    $stmt->bind_param("s", $category_filter);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM contacts ORDER BY category, organization, full_name");
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Contactos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container my-5 scroll-margin">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="m-0">Contactos</h2>
            <a href="dashboard/contacts/create_contact.php" class="btn btn-primary">+ Añadir contacto</a>
        </div>

        <?php if (isset($_GET['saved'])): ?>
            <div class="alert alert-success">Contacto guardado.</div>
        <?php elseif (isset($_GET['deleted'])): ?>
            <div class="alert alert-success">Contacto eliminado.</div>
        <?php endif; ?>

        <form method="get" class="mb-3 d-flex gap-2 align-items-center">
            <label for="category" class="form-label m-0">Categoría:</label>
            <select name="category" id="category" class="form-select w-auto" onchange="this.form.submit()">
                <option value="">Todas</option>
                <?php foreach (CONTACT_CATEGORIES as $key => $label): ?>
                    <option value="<?= $key ?>" <?= $category_filter === $key ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
            </select>
        </form>

        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Categoría</th>
                    <th>Organización / titulación</th>
                    <th>Notas</th>
                    <th>Activo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="<?= $row['active'] ? '' : 'text-muted' ?>">
                            <td><?= htmlspecialchars($row['full_name']) ?><br>
                                <small class="text-muted">Saludo: <?= htmlspecialchars(contact_greeting_name($row)) ?></small></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars(contact_category_label($row['category'])) ?></td>
                            <td><?= htmlspecialchars($row['organization'] ?? '') ?></td>
                            <td><small><?= htmlspecialchars($row['notes'] ?? '') ?></small></td>
                            <td><?= $row['active'] ? 'Sí' : 'No' ?></td>
                            <td>
                                <a class="btn btn-sm btn-success mb-1"
                                    href="dashboard/contacts/create_contact.php?id=<?= $row['id'] ?>">Editar</a>
                                <form method="post" action="dashboard/contacts/delete_contact.php" class="d-inline"
                                    onsubmit="return confirm('¿Seguro que quieres eliminar este contacto?')">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger mb-1">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No se encontraron contactos.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php include(__DIR__ . "/../../assets/footer.php"); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
$conn->close();
?>
