<?php
// Show errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../assets/db.php');
include("../assets/head.php");


// Validate and get the event ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("❌ Invalid event ID");
}
$event_id = (int) $_GET['id'];

// Prepare SQL
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
if (!$stmt) {
    die("❌ Prepare failed: " . $mysqli->error);
}
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();
$stmt->close();

$youtubeUrl = $event['youtube_url'] ?? '';
$google_calendar_url = $event['google_calendar_url'] ?? '';

$youtubeId = null;
if (!empty($youtubeUrl)) {
    preg_match("/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&]+)/", $youtubeUrl, $matches);
    $youtubeId = $matches[1] ?? null;
}

if (!$event) {
    die("❌ Event not found");
}

    // --- DYNAMIC GOOGLE CALENDAR URL GENERATION ---

$calendar_base_url = 'http://www.google.com/calendar/event?action=TEMPLATE';
$title_es = $event['title_es'] ?? '';
$speaker = $event['speaker'] ?? '';
$description_es = $event['description_es'] ?? '';
$location = $event['location'] ?? '';
$start_datetime = $event['start_datetime'] ?? '';
$end_datetime = $event['end_datetime'] ?? '';

$dates_param = '';
try {
    $start_dt = new DateTime($start_datetime, new DateTimeZone('Europe/Madrid')); // Adjust timezone as needed
    $end_dt = new DateTime($end_datetime, new DateTimeZone('Europe/Madrid'));     // Adjust timezone as needed
    
    // Format to YYYYMMDDTHHMMSS
    // Note: Google Calendar typically prefers UTC format (ending in Z) or timezone-aware format. 
    // Using YYYYMMDDTHHMMSS for local time without Z often works for simple events.
    $start_format = $start_dt->format('Ymd') . 'T' . $start_dt->format('His');
    $end_format = $end_dt->format('Ymd') . 'T' . $end_dt->format('His');
    
    $dates_param = $start_format . '/' . $end_format;
    
} catch (Exception $e) {
    error_log("Date parsing failed: " . $e->getMessage());
}

$description = $description_es;
if (!empty($speaker)) {
    // Append speaker info to the description
    $description .= "\n\nPonente: " . $speaker;
}

$params = array(
    'text' => $title_es,
    'dates' => $dates_param,
    'details' => $description, // Use the updated description
    'location' => $location,
);

$clean_params = array_filter($params);
$query_string = http_build_query($clean_params);

$google_calendar_url = $calendar_base_url . '&' . $query_string;

?>

<!DOCTYPE html>
<html lang="en">

<body style="color:black;">
    <?php include("../assets/nav.php"); ?>

    <!-- Main Content -->
    <div class="container-fluid scroll-margin bg-dark">
        <div class="row px-3">
            <div class="col-lg-4 d-flex align-items-end justify-content-center p-0 h-100">
                <img src="<?= htmlspecialchars($event['image_path']) ?>"
                    class="card-img-top"
                    alt="Event Image"
                    style="width: 300px; height:300px; object-fit: cover; position:relative; top:32px;">
            </div>
            <div class="col-lg-8 pt-5 container py-lg-2 d-flex flex-column align-items-start justify-content-center">
                <div class="mb-3">
                    <a class="badge bg-aisc-event text-decoration-none"
                        data-en="<?= htmlspecialchars($event['type_en']) ?>"
                        data-es="<?= htmlspecialchars($event['type_es']) ?>">
                        <?= htmlspecialchars($event['type_es']) ?>
                    </a>
                    <h1 id="article-title" class="text-light display-5 fw-bold mt-2"
                        data-en="<?= htmlspecialchars($event['title_en']) ?>"
                        data-es="<?= htmlspecialchars($event['title_es']) ?>">
                        <?= nl2br(htmlspecialchars($event['title_es'])) ?>
                    </h1>
                    <p class="text-decoration-none mt-2" style="color: white;"
                        data-en="Speaker: <?= htmlspecialchars($event['speaker']) ?>"
                        data-es="Ponente: <?= htmlspecialchars($event['speaker']) ?>">
                        Ponente: <?= htmlspecialchars($event['speaker']) ?>

                        <?php if ($event['requires_registration']): ?>
                        <div class="my-3">
                            <a href="events/event_registration.php?id=<?= $event_id ?>" class="btn btn-custom text-light px-4 fw-semibold"
                               data-en="Register for event"
                               data-es="Inscribirse al evento">
                                Inscribirse al evento
                            </a>
                        </div>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="bg-muted row px-3 bg-light">
            <?php
            $gallery = [];
            if (!empty($event['gallery_paths'])) {
                $decoded = json_decode($event['gallery_paths'], true);
                if (is_array($decoded) && count($decoded) > 0) {
                    $gallery = $decoded;
                }
            }
            ?>

            

            <!-- Sidebar -->
            <div class="col-lg-4 pt-5 bg-white d-flex justify-content-center align-items-start">
                <div style="width:70%;">
                    <?php
                    $start = new DateTime($event['start_datetime']);
                    $end   = new DateTime($event['end_datetime']);
                    ?>
                    <div id="article-date" class="mb-3">
                        <i class="fas fa-calendar me-2"></i>
                        <?php
                        if ($start->format('d/m/Y') === $end->format('d/m/Y')) {
                            // Same day: date bold, times normal
                        ?>
                            <strong><?= $start->format('d/m/Y'); ?></strong> <?= $start->format('H:i'); ?> - <?= $end->format('H:i'); ?>
                        <?php
                        } else {
                            // Different days: both dates bold, times normal
                        ?>
                            <strong><?= $start->format('d/m/Y'); ?></strong> <?= $start->format('H:i'); ?> - <strong><?= $end->format('d/m/Y'); ?></strong> <?= $end->format('H:i'); ?>
                        <?php
                        }
                        ?>
                    </div>



                    <div class="mb-3">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        <span><?= htmlspecialchars($event['location']) ?></span>
                    </div>

                    <div class="my-2">
    <span class="me-2" data-en="Add to calendar:" data-es="Añadir al calendario:">Añadir al calendario:</span>
    <?php if (!empty($google_calendar_url)): ?>
        <a href="<?= htmlspecialchars($google_calendar_url) ?>"
            class="btn btn-sm btn-outline-secondary me-1"
            title="Google Calendar" target="_blank">
            <i class="bi bi-google"></i>
        </a>
    <?php endif; ?>
</div>

                    <div class="mt-3 mb-5">
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                Share <i class="fas fa-share-alt"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item"
                                        href="https://api.whatsapp.com/send?text=<?= urlencode('¡Mira este evento! https://aiscmadrid.com/events/evento.php?id=' . $event_id) ?>"
                                        target="_blank">
                                        <i class="fab fa-whatsapp me-2"></i>WhatsApp
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item"
                                        href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode('https://aiscmadrid.com/events/evento.php?id=' . $event_id) ?>"
                                        target="_blank">
                                        <i class="fab fa-linkedin-in me-2"></i>LinkedIn
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item"
                                        href="https://x.com/intent/tweet/?url=<?= urlencode('https://aiscmadrid.com/events/evento.php?id=' . $event_id) ?>"
                                        target="_blank">
                                        <i class="fab fa-x-twitter me-2"></i>X
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            

            <!-- Main Content -->
            <div class="col-lg-8">
                <?php if (!empty($gallery)): ?>
                <div class="pt-2 d-flex align-items-center justify-content-center" style="width:100%;">
                    <div id="eventGalleryCarousel" class="carousel slide mt-4" data-bs-ride="carousel" style="width:80%;">
                        <!-- Indicators -->
                        <div class="carousel-indicators">
                            <?php foreach ($gallery as $index => $path): ?>
                                <button type="button" data-bs-target="#eventGalleryCarousel" data-bs-slide-to="<?= $index ?>"
                                    class="<?= $index === 0 ? 'active' : '' ?>"
                                    aria-current="<?= $index === 0 ? 'true' : 'false' ?>"
                                    aria-label="Slide <?= $index + 1 ?>"></button>
                            <?php endforeach; ?>
                        </div>

                        <!-- Slides -->
                        <div class="carousel-inner">
                            <?php foreach ($gallery as $index => $path): ?>
                                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                    <img src="<?= htmlspecialchars($path) ?>"
                                        class="d-block w-100"
                                        alt="Gallery image <?= $index + 1 ?>"
                                        style="max-height:400px; object-fit:cover;">
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Controls -->
                        <button class="carousel-control-prev" type="button" data-bs-target="#eventGalleryCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#eventGalleryCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
            <?php endif; ?>

                <section id="article-body" style="padding: 2rem; white-space: pre-line;"
                    data-en="<?= htmlspecialchars_decode($event['description_en']) ?>"
                    data-es="<?= htmlspecialchars_decode($event['description_es']) ?>">
                    <?= nl2br(htmlspecialchars_decode($event['description_es'])) ?>

                </section>

            <?php if (!empty($youtubeId)): ?>
                <div class="my-4 d-flex justify-content-center align-items-center" style="height: 320px;">
                    <iframe width="420" height="315"
                            src="https://www.youtube.com/embed/<?= htmlspecialchars($youtubeId) ?>?autoplay=0"
                            title="Video del evento"
                            frameborder="0"
                            allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                    </iframe>
                </div>
            <?php endif; ?>

            </div>
        </div>
    </div>

    <?php include('../assets/footer.php'); ?>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/navbar.js"></script>
    <script src="../js/language.js"></script>
</body>

</html>
