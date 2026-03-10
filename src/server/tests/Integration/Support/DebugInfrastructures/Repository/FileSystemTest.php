<?php

declare(strict_types=1);

namespace Tests\Integration\Support\DebugInfrastructures\Repository;

use PHPUnit\Framework\Attributes\Test;
use Support\DebugInfrastructures\Repository\FileSystem;
use Tests\Support\TestFile;
use Tests\TestCase;

class FileSystemTest extends TestCase
{
    use TestFile;

    #[Test]
    public function isExistsFile(): void
    {
        $this->runWithTemporaryFile(
            function (string $path): void {
                $this->assertTrue(new FileSystem()->exists($path));
            },
            '/tmp/exists.txt',
        );
    }

    #[Test]
    public function isNonexistentFile(): void
    {
        $this->runWithNonexistentFile(
            function (string $path): void {
                $this->assertFalse(new FileSystem()->exists($path));
            },
            '/tmp/nonexistent.txt',
        );
    }

    #[Test]
    public function putDataToFile(): void
    {
        $this->runWithTemporaryFile(
            function (string $path, mixed $content): void {
                new FileSystem()->put($path, $content);

                $this->assertSame($content, file_get_contents($path));
            },
            '/tmp/put.txt',
            'put value',
        );
    }

    #[Test]
    public function getDataFromFile(): void
    {
        $this->runWithTemporaryFile(
            function (string $path, mixed $content): void {
                $this->assertSame($content, new FileSystem()->get($path));
            },
            '/tmp/get.txt',
            'get value',
        );
    }
}
