<?php
session_start();

// Check if user is logged in as a guest
if (!isset($_SESSION['activated']) || $_SESSION['role'] !== 'guest') {
    header("Location: /login.php");
    exit();
}

include_once '../../assets/head.php';
include("../../assets/db.php");

$guest_id = $_SESSION['user_id'];

// Get all events this guest has access to with registration count
$stmt = $conn->prepare("
    SELECT e.*, 
           COUNT(DISTINCT er.id) as registration_count
    FROM events e
    INNER JOIN event_guest_access ega ON e.id = ega.event_id
    LEFT JOIN event_registrations er ON e.id = er.event_id
    WHERE ega.guest_id = ?
    GROUP BY e.id
    ORDER BY e.start_datetime DESC
");
$stmt->bind_param("i", $guest_id);
$stmt->execute();
$result = $stmt->get_result();
$events = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <link rel="stylesheet" href="/css/styles3.css">
</head>

<body class="bg-light">
    <?php include('../../assets/nav_dashboard.php'); ?>
    
    <div class="container scroll-margin">
        <h2 class="mb-4 fw-bold">
            <i class="bi bi-calendar-event me-2"></i>Mis Eventos
        </h2>

        <?php if (empty($events)): ?>
            <div class="card p-5 text-center">
                <i class="bi bi-inbox display-1 d-block mb-3 text-muted"></i>
                <h5 class="mb-2">No tienes eventos asignados</h5>
                <p class="text-muted mb-0">Contacta con el administrador si crees que esto es un error</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($events as $event): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 no-hover">
                            <?php if (!empty($event['image_path'])): ?>
                                <img src="/<?= htmlspecialchars($event['image_path']) ?>" 
                                     class="card-img-top" 
                                     alt="Event image"
                                     style="height: 200px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="card-body">
                                <span class="badge bg-dark mb-2">
                                    <?= htmlspecialchars($event['type_es']) ?>
                                </span>
                                <h5 class="card-title fw-bold">
                                    <?= htmlspecialchars($event['title_es']) ?>
                                </h5>
                                <p class="card-text text-muted small mb-1">
                                    <i class="bi bi-calendar3 me-1"></i>
                                    <?= date("d/m/Y H:i", strtotime($event['start_datetime'])) ?>
                                </p>
                                <p class="card-text text-muted small mb-1">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    <?= htmlspecialchars($event['location']) ?>
                                </p>
                                <?php if (!empty($event['speaker'])): ?>
                                    <p class="card-text text-muted small mb-1">
                                        <i class="bi bi-person me-1"></i>
                                        <?= htmlspecialchars($event['speaker']) ?>
                                    </p>
                                <?php endif; ?>
                                <p class="card-text small fw-bold mt-2" style="color: var(--primary);">
                                    <i class="bi bi-people-fill me-1"></i>
                                    <?= $event['registration_count'] ?> registrados
                                </p>
                            </div>
                            <div class="card-footer bg-white border-0">
                                <a href="/dashboard/guests/guest_event_view.php?id=<?= $event['id'] ?>"
                                   class="btn btn-custom w-100">
                                    Ver detalles <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include('../../assets/footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php $conn->close(); ?>