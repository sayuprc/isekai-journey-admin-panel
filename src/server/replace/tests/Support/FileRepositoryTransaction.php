<?php

declare(strict_types=1);

namespace Tests\Support;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RuntimeException;
use SplFileInfo;
use Support\DebugInfrastructures\Repository\FileRepositoryConfig;
use Support\DebugInfrastructures\Repository\FileStore;

trait FileRepositoryTransaction
{
    private const string FILE_DIR = __DIR__ . '/../../storage/app/tests';

    protected function setUp(): void
    {
        parent::setUp();

        $directory = $this->getDirectoryName();

        $this->container->config(new FileRepositoryConfig(filePath: $directory));

        if (! file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->deleteRecursive($this->getDirectoryName());
    }

    private function deleteRecursive(string $directory): void
    {
        /** @var SplFileInfo $file */
        foreach (
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $directory,
                    FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_FILEINFO
                ),
                RecursiveIteratorIterator::LEAVES_ONLY
            ) as $file
        ) {
            unlink($file->getRealPath());
        }

        if (file_exists($directory)) {
            rmdir($directory);
        }
    }

    private function getDirectoryName(): string
    {
        return str_replace('\\', '/', self::FILE_DIR . '/' . new ReflectionClass($this)->getName() . '/' . $this->name());
    }

    private function factory(string $repository, int|string $key, mixed $value): void
    {
        /** @var FileStore $store */
        $store = $this->container->get(FileStore::class);

        $store->put($this->getFileName($repository), $key, $value);
    }

    private function getAll(string $repository): array
    {
        /** @var FileStore $store */
        $store = $this->container->get(FileStore::class);

        return $store->getAll($this->getFileName($repository));
    }

    private function getFileName(string $repository): string
    {
        $fileName = new ReflectionClass($repository)->getConstant('FILE_NAME');

        if ($fileName === false || $fileName === '') {
            throw new RuntimeException('リポジトリに FILE_NAME 定数が設定されていません。');
        }

        return $this->getDirectoryName() . '/' . $fileName;
    }
}
