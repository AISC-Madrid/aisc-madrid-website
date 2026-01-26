<?php
session_start();

// Check if user is logged in as a guest
if (!isset($_SESSION['activated']) || $_SESSION['role'] !== 'guest') {
    header("Location: /login.php");
    exit();
}

include("../../assets/db.php");

$guest_id = $_SESSION['user_id'];

// Validate event ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: guest_dashboard.php");
    exit();
}
$event_id = (int) $_GET['id'];

// Check if guest has access to this event
$stmt = $conn->prepare("SELECT 1 FROM event_guest_access WHERE guest_id = ? AND event_id = ?");
$stmt->bind_param("ii", $guest_id, $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: guest_dashboard.php?error=unauthorized");
    exit();
}
$stmt->close();

// Get event details
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();
$stmt->close();

if (!$event) {
    header("Location: guest_dashboard.php?error=notfound");
    exit();
}

// Get registration count and list for this event
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM event_registrations WHERE event_id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$registration_count = $result->fetch_assoc()['count'];
$stmt->close();

// Get all registrations for this event
$stmt = $conn->prepare("
    SELECT * 
    FROM event_registrations er
    WHERE er.event_id = ?
    ORDER BY er.registration_date DESC
");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$registrations = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$gallery = [];
if (!empty($event['gallery_paths'])) {
    $decoded = json_decode($event['gallery_paths'], true);
    if (is_array($decoded) && count($decoded) > 0) {
        $gallery = $decoded;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<?php include_once '../../assets/head.php'; ?>

<body class="bg-light text-dark">
    <?php include('../../assets/nav_dashboard.php'); ?>

    <div class="container scroll-margin">
        <div class="mb-4">
            <a href="/dashboard/guests/guest_dashboard.php" class="btn btn-custom">
                <i class="bi bi-arrow-left"></i> Volver al Dashboard
            </a>
        </div>

        <!-- Foto e información del evento -->
        <div class="row g-4 mb-4">
            <!-- Foto del evento (cuadrada) -->
            <div class="col-md-4">
                <?php if (!empty($event['image_path'])): ?>
                    <img src="/<?= htmlspecialchars($event['image_path']) ?>" 
                         class="img-fluid rounded" 
                         alt="Event image"
                         style="width: 100%; aspect-ratio: 1/1; object-fit: cover;">
                <?php endif; ?>
            </div>

            <!-- Información del evento -->
            <div class="col-md-8">                
                <h1 class="fw-bold mb-1">
                    <?= htmlspecialchars($event['title_es']) ?>
                </h1>
                
                <p class="text-muted mb-3">
                    <?= htmlspecialchars($event['title_en']) ?>
                </p>

                <p class="mb-2">
                    <i class="bi bi-calendar3 me-2" style="color: var(--primary);"></i>
                    <?= date("d/m/Y", strtotime($event['start_datetime'])) ?>
                    - <?= date("H:i", strtotime($event['start_datetime'])) ?> a <?= date("H:i", strtotime($event['end_datetime'])) ?>
                </p>

                <p class="mb-2">
                    <i class="bi bi-geo-alt me-2" style="color: var(--primary);"></i>
                    <?= htmlspecialchars($event['location']) ?>
                </p>

                <?php if (!empty($event['speaker'])): ?>
                    <p class="mb-2">
                        <i class="bi bi-person me-2" style="color: var(--primary);"></i>
                        <?= htmlspecialchars($event['speaker']) ?>
                    </p>
                <?php endif; ?>

                <p class="mb-0">
                    <i class="bi bi-people-fill me-2" style="color: var(--primary);"></i>
                    <?= $registration_count ?> personas registradas
                </p>
            </div>
        </div>

        <!-- Lista de registrados -->
        <div class="row">
            <div class="col-12">
                <h5 class="fw-bold mb-3">
                    <i class="bi bi-people-fill me-2" style="color: var(--primary);"></i>Registrados (<?= $registration_count ?>)
                </h5>
                
                <?php if (empty($registrations)): ?>
                    <div class="p-5 text-center text-muted bg-white rounded">
                        <i class="bi bi-inbox display-1 d-block mb-3"></i>
                        <p class="mb-0">No hay registrados aún</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive bg-white rounded">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Registro</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($registrations as $reg): ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($reg['name']) ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($reg['email']) ?>
                                        </td>
                                        <td>
                                            <?= date("d/m/Y H:i", strtotime($reg['registration_date'])) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include('../../assets/footer.php'); ?>
</body>

</html>

<?php $conn->close(); ?>