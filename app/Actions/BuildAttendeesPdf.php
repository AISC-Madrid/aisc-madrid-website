<?php

namespace App\Actions;

use App\Models\Event;
use Fpdf\Fpdf;
use Illuminate\Support\Collection;

/**
 * Attendee list PDF for an event, same layout as the legacy processing/export_attendees_pdf.php:
 * logo + "Lista de Asistentes" header, event details, counts and a numbered table.
 */
class BuildAttendeesPdf
{
    /**
     * @param  Collection<int, object{name: string, email: string, attendance_status: ?string}>  $registrations
     */
    public function __invoke(Event $event, Collection $registrations): string
    {
        $pdf = new class extends Fpdf
        {
            public function Header(): void
            {
                $logo = public_path('images/logos/aisc-logo-color.png');

                if (is_file($logo)) {
                    $this->Image($logo, 10, 6, 30);
                }

                $this->SetFont('Arial', 'B', 15);
                $this->Cell(80);
                $this->Cell(30, 10, BuildAttendeesPdf::latin1('Lista de Asistentes'), 0, 0, 'C');
                $this->Ln(35);
            }

            public function Footer(): void
            {
                $this->SetY(-15);
                $this->SetFont('Arial', 'I', 8);
                $this->Cell(0, 10, 'Pagina '.$this->PageNo().'/{nb}', 0, 0, 'C');
            }
        };

        $attendedCount = $registrations->where('attendance_status', 'attended')->count();
        $start = $event->start_datetime->setTimezone('Europe/Madrid');

        $pdf->SetTitle(self::latin1('Lista de Asistentes - '.$event->title_es));
        $pdf->AliasNbPages();
        $pdf->AddPage();

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->MultiCell(0, 6, self::latin1('Evento: '.$event->title_es), 0, 'L');

        $this->detailLine($pdf, 'Ponente: ', 17, (string) $event->speaker);
        $this->detailLine($pdf, 'Fecha: ', 13, $start->format('d/m/Y H:i'));
        $this->detailLine($pdf, 'Ubicación: ', 20, (string) $event->location);
        $this->detailLine($pdf, 'Personas Registradas: ', 40, (string) $registrations->count());
        $this->detailLine($pdf, 'Personas que Asistieron: ', 43, (string) $attendedCount);
        $pdf->Ln(10);

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(141, 218, 235);
        $pdf->Cell(10, 7, '#', 1, 0, 'C', true);
        $pdf->Cell(70, 7, 'Nombre', 1, 0, 'C', true);
        $pdf->Cell(80, 7, 'Email', 1, 0, 'C', true);
        $pdf->Cell(30, 7, 'Estado', 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 9);

        foreach ($registrations->values() as $index => $registration) {
            $status = $registration->attendance_status === 'attended' ? 'Asistió' : 'No asistió';

            $pdf->Cell(10, 6, (string) ($index + 1), 1, 0, 'C');
            $pdf->Cell(70, 6, self::latin1($registration->name), 1, 0, 'L');
            $pdf->Cell(80, 6, self::latin1($registration->email), 1, 0, 'L');
            $pdf->Cell(30, 6, self::latin1($status), 1, 1, 'C');
        }

        if ($registrations->isEmpty()) {
            $pdf->Cell(190, 6, self::latin1('No hay personas registradas.'), 1, 1, 'C');
        }

        return $pdf->Output('S');
    }

    /**
     * FPDF's core fonts use Windows-1252; convert so accents (á, ñ, ó) print correctly.
     */
    public static function latin1(string $text): string
    {
        return mb_convert_encoding($text, 'Windows-1252', 'UTF-8');
    }

    private function detailLine(Fpdf $pdf, string $label, int $labelWidth, string $value): void
    {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($labelWidth, 6, self::latin1($label), 0, 0);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 6, self::latin1($value), 0, 1);
    }
}
