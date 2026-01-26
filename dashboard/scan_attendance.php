<?php
session_start();
// Allow any authenticated user
if (!isset($_SESSION['activated'])) {
    header("Location: /login.php");
    exit();
}
include_once '../assets/db.php';
include_once '../assets/head.php';


// If guest, get their allowed events for client-side validation
$allowedEvents = [];
$isGuest = ($_SESSION['role'] === 'guest');

if ($isGuest) {
    $guest_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT event_id FROM event_guest_access WHERE guest_id = ?");
    $stmt->bind_param("i", $guest_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $allowedEvents[] = $row['event_id'];
    }
    $stmt->close();
}
?>

<div class="container mt-4 scroll-margin">
    <h2 class="text-center text-dark">Escanear asistencia</h2>
    <p class="text-center text-dark">Utiliza la cámara de tu dispositivo para escanear códigos QR y registrar la
        asistencia.</p>

    <?php if ($isGuest && empty($allowedEvents)): ?>
        <div class="alert alert-warning text-center">
            No tienes eventos asignados para escanear. Contacta con el administrador.
        </div>
    <?php else: ?>
        <div id="qr-reader" style="width: 100%; max-width: 500px; margin: 0 auto;"></div>
        <div id="qr-reader-results" class="mt-3 text-center"></div>
    <?php endif; ?>
</div>

<?php
include_once '../assets/footer.php';
include("../assets/nav_dashboard.php");
?>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    async function onScanSuccess(decodedText, decodedResult) {
        // Handle on success condition with the decoded text and result.
        console.log(`Code matched = ${decodedText}`, decodedResult);

        // Stop the scanner immediately to prevent multiple scans
        html5QrcodeScanner.clear();

        // Actualizar la asistencia del evento en la base de datos
        var parts = decodedText.split(';');
        
        if (parts.length < 2) {
             console.error("Invalid QR format");
             document.getElementById('qr-reader-results').innerHTML = `<h3 class="text-danger">❌ Formato QR inválido</h3>`;
             restartScanner();
             return;
        }

        var email = parts[0];
        var event_id = parts[1];

        try {
            const response = await fetch('../processing/update_attendance.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email: email, event_id: event_id })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                 document.getElementById('qr-reader-results').innerHTML += `<h3 class="text-success">✅ ${data.message || 'Escaneado correctamente'}</h3>`;
            } else {
                 document.getElementById('qr-reader-results').innerHTML += `<h3 class="text-danger">❌ ${data.message || 'Error al registrar asistencia'}</h3>`;
            }

        } catch (error) {
            console.error('Error:', error);
            document.getElementById('qr-reader-results').innerHTML += `<h3 class="text-danger">❌ Error de conexión</h3>`;
        }

        // Restart the scanner after a delay
        restartScanner();
    }

    function restartScanner() {
        setTimeout(function () {
            document.getElementById('qr-reader-results').innerHTML = '';
            html5QrcodeScanner.render(onScanSuccess, onScanError);
        }, 3000); // Restart after 3 seconds to give time to read the message
    }

    function onScanError(errorMessage) {
        // Handle on error condition with the error message.
        console.error(`QR Code scanning error = ${errorMessage}`);
    }

    var html5QrcodeScanner = new Html5QrcodeScanner(
        "qr-reader", { fps: 10, qrbox: 250 });
    html5QrcodeScanner.render(onScanSuccess, onScanError);
</script>