<?php
session_start();
include_once '../assets/head.php';
include_once 'dashboard_nav.php';

if (!isset($_SESSION['activated']) || $_SESSION['role'] !== 'admin') {
    header("Location: events/login.php");
    exit();
}
?>

<div class="container mt-4 scroll-margin">
    <h2 class="text-center text-dark">Escanear asistencia</h2>
    <p class="text-center text-dark">Utiliza la cámara de tu dispositivo para escanear códigos QR y registrar la asistencia.</p>

    <div id="qr-reader" style="width: 500px"></div>
    <div id="qr-reader-results"></div>
</div>

<?php
include_once '../assets/footer.php';
?>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
    function onScanSuccess(decodedText, decodedResult) {
        // Handle on success condition with the decoded text and result.
        console.log(`Code matched = ${decodedText}`, decodedResult);
        document.getElementById('qr-reader-results').innerHTML += `<div>Scanned: ${decodedText}</div>`;
        // Optionally, stop the scanner after a successful scan
        // html5QrcodeScanner.clear();
    }

    function onScanError(errorMessage) {
        // Handle on error condition with the error message.
        console.error(`QR Code scanning error = ${errorMessage}`);
    }

    var html5QrcodeScanner = new Html5QrcodeScanner(
        "qr-reader", { fps: 10, qrbox: 250 });
    html5QrcodeScanner.render(onScanSuccess, onScanError);
</script>
