<?php

declare(strict_types=1);

namespace Tests\Integration\Support\DebugInfrastructures\Repository;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Support\DebugInfrastructures\Repository\FileSystem;
use Support\DebugInfrastructures\Repository\JsonFileStore;
use Tests\Support\TestFile;
use Tests\TestCase;

class JsonFileStoreTest extends TestCase
{
    use TestFile;

    #[Test]
    public function loadAllDataFromFile(): void
    {
        $data = [
            [
                'id' => 1,
                'name' => 'getAll value',
            ],
        ];

        $this->runWithTemporaryFile(
            function (string $path) use ($data): void {
                $actual = $this->getFileStore()->load($path);

                $this->assertSame($data, $actual);
            },
            '/tmp/get-all.json',
            $this->jsonEncode($data),
        );
    }

    #[Test]
    public function loadAllDataFromNonexistentFile(): void
    {
        $this->runWithNonexistentFile(
            function (string $path): void {
                $actual = $this->getFileStore()->load($path);

                $this->assertEmpty($actual);
            },
            '/tmp/get-all-nonexistent.json',
        );
    }

    #[Test]
    public function loadAllDataFromEmptyFile(): void
    {
        $this->runWithTemporaryFile(
            function (string $path): void {
                $actual = $this->getFileStore()->load($path);

                $this->assertEmpty($actual);
            },
            '/tmp/getAllEmpty.json',
            '',
        );
    }

    #[Test]
    #[DataProvider('providePutDataToFile')]
    public function putDataToFile(array $value, ?string $key): void
    {
        $this->runWithTemporaryFile(
            function (string $path) use ($key, $value): void {
                $this->getFileStore()->save($path, $value, $key);

                $actual = $this->jsonDecode(file_get_contents($path));

                if (! is_null($key)) {
                    $this->assertArrayHasKey($key, $actual);
                    $this->assertSame($value, $actual[$key]);
                } else {
                    $this->assertContains($value, $actual);
                }
            },
            '/tmp/put.json',
            $this->jsonEncode([]),
        );
    }

    public static function providePutDataToFile(): array
    {
        return [
            'append' => [
                ['id' => 10, 'name' => 'array value'],
                null,
            ],
            'with key' => [
                ['id' => 20, 'name' => 'with key'],
                'my-key',
            ],
        ];
    }

    #[Test]
    public function unsetFromFile(): void
    {
        $data = [
            ['id' => 1, 'name' => 'hoge'],
            ['id' => 2, 'name' => 'fuga'],
        ];

        $this->runWithTemporaryFile(
            function (string $path): void {
                $this->getFileStore()->unset($path, 0);

                $actual = $this->jsonDecode(file_get_contents($path));

                $this->assertCount(1, $actual);
                $this->assertSame(2, $actual[0]['id']);
            },
            '/tmp/unset.json',
            $this->jsonEncode($data),
        );
    }

    private function getFileStore(): JsonFileStore
    {
        return new JsonFileStore(new FileSystem());
    }

    private function jsonEncode(mixed $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    private function jsonDecode(string $json): array
    {
        return json_decode($json, associative: true, flags: JSON_THROW_ON_ERROR);
    }
}
