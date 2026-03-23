<?php

namespace App\Service\Ticket;

use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

class TicketPdfService
{
    public function __construct(
        private Environment $twig,
        private string $projectDir,
    ) {}

    /** @return array{content:string, filename:string, mime:string} */
    public function generatePdfFromSnapshot(array $snapshot): array
    {
        $logoDataUri = $this->buildLogoDataUri();

        $html = $this->twig->render('ticket/ticket.html.twig', [
            'ticket' => $snapshot,
            'generatedAt' => new \DateTimeImmutable(),
            'logoPath' => $logoDataUri,
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->setChroot($this->projectDir . '/public');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfContent = $dompdf->output();
        $filename = sprintf('ticket-%s.pdf', $snapshot['reference']);

        return [
            'content' => $pdfContent,
            'filename' => $filename,
            'mime' => 'application/pdf',
        ];
    }

    private function buildLogoDataUri(): ?string
    {
        $path = $this->projectDir . '/public/assets/branding/it-pro-logo.png';

        if (!is_file($path)) {
            return null;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return null;
        }

        return 'data:image/png;base64,' . base64_encode($content);
    }
}