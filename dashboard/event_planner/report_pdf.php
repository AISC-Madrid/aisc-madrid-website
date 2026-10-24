<?php
// Informe PDF del plan: datos del evento y personas, todas las tareas (asignado, fecha
// límite, completado por/cuándo, a tiempo o con retraso, notas), checklist y actividad.
// Generación bajo demanda (no se guarda). Cualquier miembro activo puede descargarlo.

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/_partials/helpers.php';
require __DIR__ . '/../../vendor/autoload.php';

use Fpdf\Fpdf;

function ep_pdf_txt(?string $s): string
{
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $s ?? '');
}

$planId = (int) ($_GET['id'] ?? 0);
$plan = ep_fetch_plan($conn, $planId);
if (!$plan) {
    http_response_code(404);
    exit('Evento no encontrado.');
}

$stmt = $conn->prepare("SELECT epm.slot, m.full_name FROM event_plan_members epm JOIN members m ON m.id = epm.member_id WHERE epm.event_plan_id = ? ORDER BY epm.slot");
$stmt->bind_param('i', $planId);
$stmt->execute();
$planMembers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $conn->prepare(
    "SELECT et.*, a.full_name AS assignee_name, cb.full_name AS completed_by_name
     FROM event_tasks et
     JOIN members a ON a.id = et.assignee_id
     LEFT JOIN members cb ON cb.id = et.completed_by
     WHERE et.event_plan_id = ? ORDER BY et.team, et.sort_order, et.due_date"
);
$stmt->bind_param('i', $planId);
$stmt->execute();
$tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $conn->prepare("SELECT eci.*, m.full_name AS done_by_name FROM event_checklist_items eci LEFT JOIN members m ON m.id = eci.done_by WHERE eci.event_plan_id = ? ORDER BY eci.team, eci.sort_order");
$stmt->bind_param('i', $planId);
$stmt->execute();
$checklist = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $conn->prepare("SELECT eal.*, m.full_name AS actor_name FROM event_activity_log eal LEFT JOIN members m ON m.id = eal.actor_id WHERE eal.event_plan_id = ? ORDER BY eal.created_at ASC, eal.id ASC");
$stmt->bind_param('i', $planId);
$stmt->execute();
$activity = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

class EventPlannerPDF extends Fpdf
{
    public string $planTitle = '';

    function Header()
    {
        if (file_exists(__DIR__ . '/../../images/logos/PNG/AISC Logo Color.png')) {
            $this->Image(__DIR__ . '/../../images/logos/PNG/AISC Logo Color.png', 10, 6, 25);
        }
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, ep_pdf_txt('Informe: ' . $this->planTitle), 0, 1, 'C');
        $this->Ln(2);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    function SectionTitle(string $title)
    {
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor(235, 23, 142);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, 8, ep_pdf_txt($title), 0, 1, 'L', true);
        $this->SetTextColor(0, 0, 0);
        $this->Ln(2);
    }
}

$pdf = new EventPlannerPDF();
$pdf->planTitle = $plan['title'];
$pdf->AliasNbPages();
$pdf->SetAutoPageBreak(true, 18);
$pdf->AddPage();

// --- Datos del evento ---
$pdf->SectionTitle('Datos del evento');
$pdf->SetFont('Arial', '', 10);
$rows = [
    ['Ponente', $plan['speaker_name'] . ($plan['speaker_company'] ? ' (' . $plan['speaker_company'] . ')' : '')],
    ['Fecha', ep_fmt_datetime_madrid($plan['starts_at']) . ' (' . (int) $plan['duration_minutes'] . ' min)'],
    ['Lugar', $plan['location'] ?: '-'],
    ['Estado', ep_plan_status_label($plan['status'])],
];
foreach ($rows as [$label, $value]) {
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(35, 6, ep_pdf_txt($label . ':'), 0, 0);
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 6, ep_pdf_txt((string) $value), 0, 1);
}
$pdf->Ln(2);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 6, ep_pdf_txt('Personas asignadas:'), 0, 1);
$pdf->SetFont('Arial', '', 10);
foreach ($planMembers as $pm) {
    $pdf->Cell(0, 6, ep_pdf_txt('- ' . $pm['slot'] . ': ' . $pm['full_name']), 0, 1);
}
$pdf->Ln(4);

// --- Tareas ---
$pdf->SectionTitle('Tareas');
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(230, 230, 230);
$pdf->Cell(55, 7, ep_pdf_txt('Tarea'), 1, 0, 'L', true);
$pdf->Cell(30, 7, ep_pdf_txt('Asignado'), 1, 0, 'L', true);
$pdf->Cell(20, 7, ep_pdf_txt('Limite'), 1, 0, 'C', true);
$pdf->Cell(25, 7, ep_pdf_txt('Completada'), 1, 0, 'C', true);
$pdf->Cell(22, 7, ep_pdf_txt('A tiempo'), 1, 0, 'C', true);
$pdf->Cell(38, 7, ep_pdf_txt('Notas'), 1, 1, 'L', true);

$pdf->SetFont('Arial', '', 8);
foreach ($tasks as $t) {
    $onTime = '-';
    $completedStr = '-';
    if ($t['status'] === 'done') {
        $completedDate = substr($t['completed_at'], 0, 10);
        $onTime = $completedDate <= $t['due_date'] ? 'Si' : 'No';
        $completedStr = ep_fmt_date($completedDate) . ' (' . $t['completed_by_name'] . ')';
    } elseif ($t['status'] === 'skipped') {
        $completedStr = 'No aplica';
    }

    $y0 = $pdf->GetY();
    $pdf->MultiCell(55, 5, ep_pdf_txt($t['title']), 1, 'L');
    $y1 = $pdf->GetY();
    $h = $y1 - $y0;
    $pdf->SetXY($pdf->GetX() + 55, $y0);
    $pdf->Cell(30, $h, ep_pdf_txt($t['assignee_name']), 1, 0, 'L');
    $pdf->Cell(20, $h, ep_pdf_txt(ep_fmt_date($t['due_date'])), 1, 0, 'C');
    $pdf->Cell(25, $h, ep_pdf_txt($completedStr), 1, 0, 'C');
    $pdf->Cell(22, $h, ep_pdf_txt($onTime), 1, 0, 'C');
    $pdf->MultiCell(38, 5, ep_pdf_txt($t['completion_notes'] ?? ''), 1, 'L');
    $pdf->SetXY(10, max($y1, $pdf->GetY()));
}
$pdf->Ln(4);

// --- Checklist ---
$pdf->SectionTitle('Checklist del dia del evento');
$pdf->SetFont('Arial', '', 9);
foreach ($checklist as $ci) {
    $mark = $ci['done'] ? '[X]' : '[ ]';
    $line = $mark . ' ' . $ci['team'] . ': ' . $ci['title'];
    if ($ci['done']) {
        $line .= ' (' . $ci['done_by_name'] . ')';
    }
    $pdf->MultiCell(0, 5, ep_pdf_txt($line), 0, 'L');
}
$pdf->Ln(4);

// --- Actividad ---
$pdf->SectionTitle('Log de actividad');
$pdf->SetFont('Arial', '', 8);
foreach ($activity as $log) {
    $when = (new DateTimeImmutable($log['created_at'], new DateTimeZone('UTC')))->setTimezone(event_planner_timezone())->format('d/m/Y H:i');
    $who = $log['actor_name'] ?? 'Sistema';
    $pdf->MultiCell(0, 5, ep_pdf_txt("{$when} - {$who} - {$log['action']}"), 0, 'L');
}

$filename = 'AISC_Madrid-informe_evento_' . $planId . '.pdf';
$pdf->Output('I', $filename);
