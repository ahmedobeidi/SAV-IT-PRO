<?php

namespace App\Service\Ticket;

use App\Entity\RepairOrder;
use Dompdf\Dompdf;
use Twig\Environment;

class TicketPdfService
{
    public function __construct(private Environment $twig) {}

    /** @return array{content:string, filename:string, mime:string} */
    public function generatePdf(RepairOrder $repairOrder): array
    {
        $html = $this->twig->render('ticket/ticket.html.twig', [
            'repair' => $repairOrder,
        ]);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfContent = $dompdf->output();
        $filename = sprintf('ticket-repair-%d.pdf', $repairOrder->getId());

        return [
            'content' => $pdfContent,
            'filename' => $filename,
            'mime' => 'application/pdf',
        ];
    }
}