<?php

namespace App\Tests\Unit\Service\Ticket;

use App\Service\Ticket\TicketPdfService;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

class TicketPdfServiceTest extends TestCase
{
    public function test_generate_pdf_from_snapshot_returns_pdf_payload(): void
    {
        $twig = $this->createMock(Environment::class);
        $projectDir = sys_get_temp_dir() . '/ticket-pdf-test-' . uniqid();
        mkdir($projectDir . '/public/assets/branding', 0777, true);

        file_put_contents(
            $projectDir . '/public/assets/branding/it-pro-logo.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9WnR8L8AAAAASUVORK5CYII=')
        );

        $twig->expects($this->once())
            ->method('render')
            ->with(
                'ticket/ticket.html.twig',
                $this->callback(function (array $context): bool {
                    return isset($context['ticket'], $context['generatedAt'], $context['logoPath']);
                })
            )
            ->willReturn('<html><body>Ticket</body></html>');

        $service = new TicketPdfService($twig, $projectDir);

        $result = $service->generatePdfFromSnapshot([
            'reference' => 'SAV-2026-000001',
        ]);

        $this->assertSame('ticket-SAV-2026-000001.pdf', $result['filename']);
        $this->assertSame('application/pdf', $result['mime']);
        $this->assertNotEmpty($result['content']);

        $this->removeDir($projectDir);
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->removeDir($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($dir);
    }
}