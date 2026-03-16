<?php

namespace App\Service\Ticket;

class TicketStorageService
{
    public function __construct(
        private string $projectDir,
    ) {}

    public function save(string $filename, string $content): array
    {
        $relativeDir = 'var/storage/tickets/' . date('Y/m');
        $absoluteDir = $this->projectDir . '/' . $relativeDir;

        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
            throw new \RuntimeException('Impossible de créer le dossier de stockage des tickets.');
        }

        $safeName = uniqid('', true) . '-' . preg_replace('/[^A-Za-z0-9._-]/', '_', $filename);
        $relativePath = $relativeDir . '/' . $safeName;
        $absolutePath = $this->projectDir . '/' . $relativePath;

        file_put_contents($absolutePath, $content);

        return [
            'storagePath' => $relativePath,
            'size' => filesize($absolutePath),
        ];
    }

    public function absolutePath(string $storagePath): string
    {
        return $this->projectDir . '/' . ltrim($storagePath, '/');
    }
}