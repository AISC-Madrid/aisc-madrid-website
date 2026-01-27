<?php
session_start();
header("Location: /dashboard/scan_attendance.php");
exit();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Scan QR - Guest Portal</title>
</head>

<body class="bg-light">
    <?php include('../../assets/nav_dashboard.php'); ?>

    <div class="container mt-4" style="margin-top: 100px !important;">
        <h2 class="text-center text-dark">
            <i class="bi bi-qr-code-scan me-2"></i>
            Scan Attendance QR
        </h2>
        <p class="text-center text-muted">
            Use your device camera to scan QR codes and register attendance.
        </p>
        <div id="qr-reader" style="width: 100%; max-width: 500px; margin: 0 auto;"></div>
        <div id="qr-reader-results" class="mt-3 text-center"></div>
    </div>

    <?php include('../../assets/footer.php'); ?>

    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        // Events this guest is allowed to scan for
        const allowedEvents = <?= json_encode($allowed_events) ?>;

        function onScanSuccess(decodedText, decodedResult) {
            console.log(`Code matched = ${decodedText}`, decodedResult);

            // Parse the QR code (format: email;event_id)
            var parts = decodedText.split(';');
            var email = parts[0];
            var event_id = parseInt(parts[1]);

            // Check if guest is authorized for this event
            if (!allowedEvents.includes(event_id)) {
                document.getElementById('qr-reader-results').innerHTML =
                    `<div class="alert alert-danger">
                        <i class="bi bi-x-circle me-2"></i>
                        You are not authorized to scan for this event
                    </div>`;

                // Restart scanner after delay
                setTimeout(function () {
                    document.getElementById('qr-reader-results').innerHTML = '';
                    html5QrcodeScanner.render(onScanSuccess, onScanError);
                }, 3000);
                return;
            }

            document.getElementById('qr-reader-results').innerHTML =
                `<div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    Scanned: ${email} for event ${event_id}
                </div>`;

            // Update attendance in the database
            fetch('/processing/update_attendance.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email: email, event_id: event_id })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('qr-reader-results').innerHTML =
                            `<div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>
                            ✅ Attendance registered for: ${email}
                        </div>`;
                    } else {
                        document.getElementById('qr-reader-results').innerHTML =
                            `<div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            ${data.message || 'Error registering attendance'}
                        </div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });

            // Stop the scanner after a successful scan
            html5QrcodeScanner.clear();

            // Restart after delay
            setTimeout(function () {
                document.getElementById('qr-reader-results').innerHTML = '';
                html5QrcodeScanner.render(onScanSuccess, onScanError);
            }, 2000);
        }

        function onScanError(errorMessage) {
            // Handle on error condition with the error message.
            console.error(`QR Code scanning error = ${errorMessage}`);
        }

        var html5QrcodeScanner = new Html5QrcodeScanner(
            "qr-reader", { fps: 10, qrbox: 250 });
        html5QrcodeScanner.render(onScanSuccess, onScanError);
    </script>
</body>

</html>

<?php $conn->close(); ?>