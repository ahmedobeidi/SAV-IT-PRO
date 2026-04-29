<?php

namespace App\Tests\Unit\Service\Ticket;

use App\Service\Ticket\TicketStorageService;
use PHPUnit\Framework\TestCase;

class TicketStorageServiceTest extends TestCase
{
    private string $projectDir;

    protected function setUp(): void
    {
        $this->projectDir = sys_get_temp_dir() . '/ticket-storage-test-' . uniqid();
        mkdir($this->projectDir, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->projectDir);
    }

    public function test_save_creates_file_and_returns_metadata(): void
    {
        $service = new TicketStorageService($this->projectDir);

        $result = $service->save('ticket-test.pdf', 'PDF-CONTENT');

        $this->assertArrayHasKey('storagePath', $result);
        $this->assertArrayHasKey('size', $result);
        $this->assertGreaterThan(0, $result['size']);

        $absolutePath = $service->absolutePath($result['storagePath']);
        $this->assertFileExists($absolutePath);
        $this->assertSame('PDF-CONTENT', file_get_contents($absolutePath));
    }

    public function test_delete_if_exists_removes_file(): void
    {
        $service = new TicketStorageService($this->projectDir);

        $result = $service->save('ticket-test.pdf', 'PDF-CONTENT');
        $absolutePath = $service->absolutePath($result['storagePath']);

        $this->assertFileExists($absolutePath);

        $service->deleteIfExists($result['storagePath']);

        $this->assertFileDoesNotExist($absolutePath);
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