<?php

declare(strict_types=1);

namespace Tests\Integration\Support\DebugInfrastructures\Repository;

use PHPUnit\Framework\Attributes\Test;
use Support\Contracts\MapperInterface;
use Support\DebugInfrastructures\Repository\FileStore;
use Support\DebugInfrastructures\Repository\FileSystem;
use Support\Infrastructures\Serializer;
use Tests\Support\TestFile;
use Tests\TestCase;

class TestEntity
{
    public function __construct(
        public int $id,
        public string $name,
    ) {
    }
}

class FileStoreTest extends TestCase
{
    use TestFile;

    #[Test]
    public function getAllDataFromFile(): void
    {
        $data = [
            '1' => [
                'id' => 1,
                'name' => 'getAll value',
            ],
        ];
        $json = json_encode($data);

        $this->runWithTemporaryFile(
            function (string $path) {
                $actual = $this->getFileStore()->getAll($path, TestEntity::class);

                $expected = [
                    '1' => new TestEntity(1, 'getAll value'),
                ];
                $this->assertEquals($expected, $actual);
            },
            '/tmp/get-all.json',
            $json,
        );
    }

    #[Test]
    public function getAllDataFromNonexistentFile(): void
    {
        $this->runWithNonexistentFile(
            function (string $path) {
                $actual = $this->getFileStore()->getAll($path, TestEntity::class);

                $this->assertEmpty($actual);
            },
            '/tmp/get-all-nonexistent.json',
        );
    }

    #[Test]
    public function getAllDataFromEmptyJSONFile(): void
    {
        $this->runWithTemporaryFile(
            function (string $path) {
                $actual = $this->getFileStore()->getAll($path, TestEntity::class);

                $this->assertEmpty($actual);
            },
            '/tmp/getAllEmpty.json',
            '{}',
        );
    }

    #[Test]
    public function getDataFromFile(): void
    {
        $data = [
            '1' => [
                'id' => 1,
                'name' => 'getAll value',
            ],
        ];

        $this->runWithTemporaryFile(
            function (string $path) {
                $actual = $this->getFileStore()->get($path, '1', TestEntity::class);

                $this->assertEquals(new TestEntity(1, 'getAll value'), $actual);
            },
            '/tmp/get.json',
            json_encode($data),
        );
    }

    #[Test]
    public function getDataFromNonexistentFile(): void
    {
        $this->runWithNonexistentFile(
            function (string $path) {
                $actual = $this->getFileStore()->get($path, 'get-nonexistent', TestEntity::class);

                $this->assertNull($actual);
            },
            '/tmp/get-nonexistent.json',
        );
    }

    #[Test]
    public function getDataNotHasKey(): void
    {
        $data = [
            '1' => [
                'id' => 1,
                'name' => 'getAll value',
            ],
        ];

        $this->runWithTemporaryFile(
            function (string $path) {
                $actual = $this->getFileStore()->get($path, '2', TestEntity::class);

                $this->assertNull($actual);
            },
            '/tmp/get.json',
            json_encode($data),
        );
    }

    #[Test]
    public function getDataFromEmptyJSONFile(): void
    {
        $this->runWithTemporaryFile(
            function (string $path) {
                $actual = $this->getFileStore()->get($path, '2', TestEntity::class);

                $this->assertNull($actual);
            },
            '/tmp/get.json',
            '{}',
        );
    }

    #[Test]
    public function putDataToFile(): void
    {
        $key = 'new';
        $value = new TestEntity(2, 'new value');

        $this->runWithTemporaryFile(
            function (string $path) use ($key, $value) {
                $this->getFileStore()->put($path, $key, $value, TestEntity::class);

                $json = file_get_contents($path);
                $actual = json_decode($json, true);

                $this->assertArrayHasKey($key, $actual);
                $this->assertSame(['id' => 2, 'name' => 'new value'], $actual[$key]);
            },
            '/tmp/put.json',
            '{}',
        );
    }

    #[Test]
    public function unsetFromFile(): void
    {
        $data = [
            '1' => ['id' => 1, 'name' => 'hoge'],
            '2' => ['id' => 2, 'name' => 'fuga'],
        ];

        $this->runWithTemporaryFile(
            function (string $path) use ($data) {
                $unsetKey = '1';

                $this->getFileStore()->unset($path, $unsetKey, TestEntity::class);

                $json = file_get_contents($path);
                $actual = json_decode($json, true);

                $this->assertArrayNotHasKey($unsetKey, $actual);
                $this->assertArrayHasKey('2', $actual);
                $this->assertSame($data['2'], $actual['2']);
            },
            '/tmp/unset.json',
            json_encode($data),
        );
    }

    private function getFileStore(): FileStore
    {
        return new FileStore(
            new FileSystem(),
            new Serializer(),
            $this->app->make(MapperInterface::class),
        );
    }
}
