<?php
session_start();
include_once '../assets/head.php';
$allowed_roles = ['admin', 'events', 'web', 'finance', 'marketing'];
if (!isset($_SESSION['activated']) || !in_array($_SESSION['role'], $allowed_roles)) {
    header("Location: /");
    exit();
}
?>

<div class="container mt-4 scroll-margin">
    <h2 class="text-center text-dark">Escanear asistencia</h2>
    <p class="text-center text-dark">Utiliza la cámara de tu dispositivo para escanear códigos QR y registrar la
        asistencia.</p>

    <div id="qr-reader" style="width: 500px; margin: 0 auto;"></div>
    <div id="qr-reader-results"></div>
</div>

<?php
include_once '../assets/footer.php';
include("../assets/nav_dashboard.php");
?>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    function onScanSuccess(decodedText, decodedResult) {
        // Handle on success condition with the decoded text and result.
        console.log(`Code matched = ${decodedText}`, decodedResult);
        document.getElementById('qr-reader-results').innerHTML += `<h3 class="text-dark">✅ Escaneado correctamente: ${decodedText}</h3>`;

        // Actualizar la asistencia del evento en la base de datos
        var parts = decodedText.split(';');
        var email = parts[0];
        var event_id = parts[1];

        fetch('../processing/update_attendance.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email: email, event_id: event_id })
        })


        // Stop the scanner after a successful scan
        html5QrcodeScanner.clear();
        // Optionally, you can restart the scanner after a delay
        setTimeout(function () {
            document.getElementById('qr-reader-results').innerHTML = '';
            html5QrcodeScanner.render(onScanSuccess, onScanError);
        }, 2000); // Restart after 2 seconds
    }

    function onScanError(errorMessage) {
        // Handle on error condition with the error message.
        console.error(`QR Code scanning error = ${errorMessage}`);
    }

    var html5QrcodeScanner = new Html5QrcodeScanner(
        "qr-reader", { fps: 10, qrbox: 250 });
    html5QrcodeScanner.render(onScanSuccess, onScanError);
</script>