<?php

declare(strict_types=1);

namespace Tests\Unit\Support\DebugInfrastructures\Repository;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use stdClass;
use Support\DebugInfrastructures\Repository\FileStore;
use Support\DebugInfrastructures\Repository\FileSystem;
use Tests\Support\TestFile;
use Tests\TestCase;

class FileStoreTest extends TestCase
{
    use TestFile;

    #[Test]
    public function getAllDataFromFile(): void
    {
        $this->runWithTemporaryFile(
            function (string $path, mixed $content) {
                $actual = $this->getFileStore()->getAll($path);

                $this->assertSame(unserialize($content), $actual);
            },
            '/tmp/get-all.dat',
            serialize([
                '1' => [
                    'id' => 1,
                    'name' => 'getAll value',
                ],
            ]),
        );
    }

    #[Test]
    public function getAllDataFromNonexistentFile(): void
    {
        $this->runWithNonexistentFile(
            function (string $path) {
                $actual = $this->getFileStore()->getAll($path);

                $this->assertEmpty($actual);
            },
            '/tmp/get-all-nonexistent.dat',
        );
    }

    #[Test]
    public function getAllDataFromEmptyFile(): void
    {
        $this->runWithTemporaryFile(
            function (string $path) {
                $actual = $this->getFileStore()->getAll($path);

                $this->assertEmpty($actual);
            },
            '/tmp/getAllEmpty.dat',
            serialize([]),
        );
    }

    #[Test]
    public function getDataFromFile(): void
    {
        $this->runWithTemporaryFile(
            function (string $path, mixed $content) {
                $actual = $this->getFileStore()->get($path, '1');

                $this->assertSame(unserialize($content)['1'], $actual);
            },
            '/tmp/get.dat',
            serialize([
                '1' => [
                    'id' => 1,
                    'name' => 'getAll value',
                ],
            ]),
        );
    }

    #[Test]
    public function getDataFromNonexistentFile(): void
    {
        $this->runWithNonexistentFile(
            function (string $path) {
                $actual = $this->getFileStore()->get($path, 'get-nonexistent');

                $this->assertNull($actual);
            },
            '/tmp/get-nonexistent.dat',
        );
    }

    #[Test]
    public function getDataNotHasKey(): void
    {
        $this->runWithTemporaryFile(
            function (string $path) {
                $actual = $this->getFileStore()->get($path, '2');

                $this->assertNull($actual);
            },
            '/tmp/get.dat',
            serialize([
                '1' => [
                    'id' => 1,
                    'name' => 'getAll value',
                ],
            ]),
        );
    }

    #[Test]
    public function getDataFromEmptyFile(): void
    {
        $this->runWithTemporaryFile(
            function (string $path) {
                $actual = $this->getFileStore()->get($path, '2');

                $this->assertNull($actual);
            },
            '/tmp/get.dat',
            serialize([]),
        );
    }

    #[Test]
    #[DataProvider('providePutDataToFile')]
    public function putDataToFile(string $key, mixed $value, bool $isObject = false): void
    {
        $this->runWithTemporaryFile(
            function (string $path) use ($key, $value, $isObject) {
                $this->getFileStore()->put($path, $key, $value);

                $actual = unserialize(file_get_contents($path));

                $this->assertArrayHasKey($key, $actual);

                if (! $isObject) {
                    $this->assertSame($value, $actual[$key]);
                } else {
                    $this->assertEquals($value, $actual[$key]);
                }
            },
            '/tmp/put.dat',
            serialize([]),
        );
    }

    public static function providePutDataToFile(): array
    {
        return [
            [
                'int',
                1,
            ],
            [
                'string',
                'string value',
            ],
            [
                'object',
                new stdClass(),
                true,
            ],
            [
                'object-2',
                (function () {
                    $stdClass = new stdClass();
                    $stdClass->id = 20;
                    $stdClass->name = 'std class';

                    return $stdClass;
                })(),
                true,
            ],
            [
                'array',
                [
                    'id' => 10,
                    'name' => 'array value',
                ],
            ],
        ];
    }

    #[Test]
    public function unsetFromFile(): void
    {
        $this->runWithTemporaryFile(
            function (string $path, mixed $content) {
                $unsetKey = '1';

                $this->getFileStore()->unset($path, $unsetKey);

                $actual = unserialize(file_get_contents($path));

                $this->assertArrayNotHasKey($unsetKey, $actual);

                $this->assertArrayHasKey('2', $actual);
                $this->assertSame(unserialize($content)['2'], $actual['2']);
            },
            '/tmp/unset.dat',
            serialize([
                '1' => [
                    'id' => 1,
                    'name' => 'hoge',
                ],
                '2' => [
                    'id' => 2,
                    'name' => 'fuga',
                ],
            ]),
        );
    }

    private function getFileStore(): FileStore
    {
        return new FileStore(new FileSystem());
    }
}
