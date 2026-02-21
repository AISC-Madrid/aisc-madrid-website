<?php
session_start();

// Check if the user is logged in with admin or events role
$allowed_roles = ['admin', 'events'];
if (!isset($_SESSION['activated']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: /");
    exit();
}

include("../../assets/db.php");

// Handle delete action BEFORE any HTML output
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $guest_id = (int) $_GET['delete'];
    // Also delete related event_guest_access entries
    $conn->query("DELETE FROM event_guest_access WHERE guest_id = $guest_id");
    $conn->query("DELETE FROM guests WHERE id = $guest_id");
    header("Location: /dashboard/guests/guests_list.php?deleted=1");
    exit();
}

// Handle toggle active status BEFORE any HTML output
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $guest_id = (int) $_GET['toggle'];
    $conn->query("UPDATE guests SET is_active = NOT is_active WHERE id = $guest_id");
    header("Location: /dashboard/guests/guests_list.php");
    exit();
}

// Now include files that output HTML
include("../../assets/head.php");
include("../../assets/nav_dashboard.php");

// Get all guests with their associated events
$result = $conn->query("
    SELECT 
        g.*,
        GROUP_CONCAT(e.title_es SEPARATOR ', ') AS event_titles,
        GROUP_CONCAT(e.id SEPARATOR ',') AS event_ids,
        COUNT(ega.event_id) AS event_count
    FROM guests g
    LEFT JOIN event_guest_access ega ON g.id = ega.guest_id
    LEFT JOIN events e ON ega.event_id = e.id
    GROUP BY g.id
    ORDER BY g.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Guests - AISC Madrid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body class="bg-light">
    <div class="container my-5 scroll-margin">
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>Guest eliminado correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['created'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>Guest creado correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>Guest actualizado correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="m-0">
                    <i class="bi bi-person-badge me-2"></i>Gestión de Guests
                </h2>
                <p class="text-muted mb-0">Administra las cuentas de invitados para eventos</p>
            </div>
            <a href="/dashboard/guests/create_guest.php" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Crear Nuevo Guest
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Nombre</th>
                            <th>Estado</th>
                            <th>Eventos Asociados</th>
                            <th>Creado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><code><?= $row['id'] ?></code></td>
                                    <td>
                                        <strong>
                                            <?= htmlspecialchars($row['username']) ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($row['name'] ?: '-') ?>
                                    </td>
                                    <td>
                                        <?php if ($row['is_active']): ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i>Activo
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-x-circle me-1"></i>Inactivo
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($row['event_count'] > 0): ?>
                                            <span class="badge bg-primary me-1">
                                                <?= $row['event_count'] ?> evento(s)
                                            </span>
                                            <small class="text-muted d-block"
                                                style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                <?= htmlspecialchars($row['event_titles']) ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">Sin eventos</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date("d/m/Y H:i", strtotime($row['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="/dashboard/guests/edit_guest.php?id=<?= $row['id'] ?>"
                                                class="btn btn-outline-primary" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="/dashboard/guests/guests_list.php?delete=<?= $row['id'] ?>" class="btn btn-outline-danger"
                                                title="Eliminar"
                                                onclick="return confirm('¿Seguro que quieres eliminar este guest?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox display-6 d-block mb-2"></i>
                                        No hay guests registrados.
                                        <br>
                                        <a href="/dashboard/guests/create_guest.php" class="btn btn-primary btn-sm mt-2">
                                            <i class="bi bi-plus-lg me-1"></i> Crear primer guest
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include('../../assets/footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php $conn->close(); ?>