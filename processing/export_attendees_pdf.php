<?php
session_start();
require('../vendor/autoload.php');
include("../assets/db.php");

$allowed_roles = ['admin', 'events', 'web', 'finance', 'marketing', 'guest'];
if (!isset($_SESSION['activated']) || !in_array($_SESSION['role'], $allowed_roles)) {
    die("Acceso denegado");
}

// Determinar si se deben censurar los correos (solo para guests)
$should_censor_emails = ($_SESSION['role'] === 'guest');

$event_id = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;
if ($event_id <= 0) {
    die("ID de evento inválido");
}

// Si es guest, verificar que tenga acceso al evento
if ($_SESSION['role'] === 'guest') {
    $guest_id = $_SESSION['user_id'];
    $access_stmt = $conn->prepare("SELECT 1 FROM event_guest_access WHERE guest_id = ? AND event_id = ?");
    $access_stmt->bind_param("ii", $guest_id, $event_id);
    $access_stmt->execute();
    $access_result = $access_stmt->get_result();
    if ($access_result->num_rows === 0) {
        die("Acceso denegado a este evento");
    }
    $access_stmt->close();
}

$event_stmt = $conn->prepare("SELECT title_es, start_datetime, location, speaker FROM events WHERE id = ?");
$event_stmt->bind_param("i", $event_id);
if (!$event_stmt->execute()) {
    die("Error al consultar el evento");
}
$event_result = $event_stmt->get_result();
$event = $event_result->fetch_assoc();
$event_stmt->close();

if (!$event) {
    die("Evento no encontrado");
}

$registrations_stmt = $conn->prepare("
    SELECT name, email, attendance_status, registration_date
    FROM event_registrations
    WHERE event_id = ?
    ORDER BY name ASC
");
$registrations_stmt->bind_param("i", $event_id);
$registrations_stmt->execute();
$registrations_result = $registrations_stmt->get_result();
$registrations = [];
while ($row = $registrations_result->fetch_assoc()) {
    $registrations[] = $row;
}
$registrations_stmt->close();
$conn->close();

use Fpdf\Fpdf;

class PDF extends Fpdf
{
    function Header()
    {
        if (file_exists('../images/logos/PNG/AISC Logo Color.png')) {
            $this->Image('../images/logos/PNG/AISC Logo Color.png', 10, 6, 30);
        }
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(80);
        $this->Cell(30, 10, iconv('UTF-8', 'ISO-8859-1', 'Lista de Asistentes'), 0, 0, 'C');
        $this->Ln(35);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Event Info
$pdf->SetFont('Arial', 'B', 12);
$pdf->MultiCell(0, 6, iconv('UTF-8', 'ISO-8859-1', 'Evento: ' . $event['title_es']), 0, 'L');
$pdf->SetFont('Arial', '', 10);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(17, 6, iconv('UTF-8', 'ISO-8859-1', 'Ponente: '), 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, iconv('UTF-8', 'ISO-8859-1', $event['speaker']), 0, 1);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(13, 6, iconv('UTF-8', 'ISO-8859-1', 'Fecha: '), 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, iconv('UTF-8', 'ISO-8859-1', date("d/m/Y H:i", strtotime($event['start_datetime']))), 0, 1);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(20, 6, iconv('UTF-8', 'ISO-8859-1', 'Ubicación: '), 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, iconv('UTF-8', 'ISO-8859-1', $event['location']), 0, 1);
$attended_count = 0;
foreach ($registrations as $reg) {
    if ($reg['attendance_status'] === 'attended') {
        $attended_count++;
    }
}
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(40, 6, iconv('UTF-8', 'ISO-8859-1', 'Personas Registradas: '), 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, count($registrations), 0, 1);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(43, 6, iconv('UTF-8', 'ISO-8859-1', 'Personas que Asistieron: '), 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, $attended_count, 0, 1);
$pdf->Ln(10);

// Table Header
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(141, 218, 235);
$pdf->Cell(10, 7, '#', 1, 0, 'C', true);
$pdf->Cell(70, 7, 'Nombre', 1, 0, 'C', true);
$pdf->Cell(80, 7, 'Email', 1, 0, 'C', true);
$pdf->Cell(30, 7, 'Estado', 1, 1, 'C', true);

// Table Rows
$pdf->SetFont('Arial', '', 9);
$count = 1;
foreach ($registrations as $reg) {
    $status = ($reg['attendance_status'] === 'attended') ? 'Asistió' : 'No asistió';
    $date = date("d/m/y", strtotime($reg['registration_date']));
    
    $pdf->Cell(10, 6, $count++, 1, 0, 'C');
    $pdf->Cell(70, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $reg['name']), 1, 0, 'L');
    
    // Censurar email solo si es guest
    $display_email = $reg['email'];
    if ($should_censor_emails) {
        $parts = explode('@', $reg['email']);
        if (count($parts) === 2) {
            $local = $parts[0];
            $visible = strlen($local) > 3 ? substr($local, 0, 3) : substr($local, 0, 1);
            $display_email = $visible . '*****@' . $parts[1];
        }
    }
    
    $pdf->Cell(80, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $display_email), 1, 0, 'L');
    $pdf->Cell(30, 6, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $status), 1, 1, 'C');
}

// Output
$filename = 'AISC_Madrid-asistentes_evento_' . $event_id . '.pdf';
$pdf->Output('D', $filename);
?>
