<?php

declare(strict_types=1);

namespace Tests\Unit\Support\DebugInfrastructures\Repository;

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
            function (string $path) {
                $this->assertTrue(new FileSystem()->exists($path));
            },
            '/tmp/exists.txt'
        );
    }

    #[Test]
    public function isNonexistentFile(): void
    {
        $this->runWithNonexistentFile(
            function (string $path) {
                $this->assertFalse(new FileSystem()->exists($path));
            },
            '/tmp/nonexistent.txt'
        );
    }

    #[Test]
    public function putDataToFile(): void
    {
        $this->runWithTemporaryFile(
            function (string $path, mixed $content) {
                new FileSystem()->put($path, $content);

                $this->assertSame($content, file_get_contents($path));
            },
            '/tmp/put.txt',
            'put value'
        );
    }

    #[Test]
    public function getDataFromFile(): void
    {
        $this->runWithTemporaryFile(
            function (string $path, mixed $content) {
                $this->assertSame($content, new FileSystem()->get($path));
            },
            '/tmp/get.txt',
            'get value'
        );
    }
}
