<?php

declare(strict_types=1);

namespace Tests\Support;

use Override;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RuntimeException;
use SplFileInfo;
use Support\Contracts\MapperInterface;
use Support\DebugInfrastructures\Repository\DebugConfig;
use Support\DebugInfrastructures\Repository\JsonFileStore;

trait FileRepositoryTransaction
{
    private const string FILE_DIR = __DIR__ . '/../../storage/app/tests';

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $config = new DebugConfig($this->getDirectoryName());

        $this->app->bind(DebugConfig::class, fn (): DebugConfig => $config);

        if (! file_exists($config->path)) {
            mkdir($config->path, 0777, true);
        }
    }

    #[Override]
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
                    FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_FILEINFO,
                ),
                RecursiveIteratorIterator::LEAVES_ONLY,
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
        return str_replace(
            '\\',
            '/',
            self::FILE_DIR . '/' . new ReflectionClass($this)->getName() . '/' . $this->name(),
        );
    }

    /**
     * @param array<mixed> $data
     */
    private function factory(string $repository, array $data): void
    {
        $this->getStore()->save($this->getFileName($repository), $data);
    }

    /**
     * @template T
     *
     * @param class-string<T> $class
     *
     * @return array<T>
     */
    private function getAll(string $class, string $repository): array
    {
        return $this->getMapper()->map(
            "array<{$class}>",
            $this->getStore()->load($this->getFileName($repository)),
        );
    }

    private function getFileName(string $repository): string
    {
        $fileName = new ReflectionClass($repository)->getConstant('FILE_NAME');

        if ($fileName === false || $fileName === '') {
            throw new RuntimeException('リポジトリに FILE_NAME 定数が設定されていません。');
        }

        return $this->getDirectoryName() . '/' . $fileName;
    }

    private function getStore(): JsonFileStore
    {
        return app()->make(JsonFileStore::class);
    }

    private function getMapper(): MapperInterface
    {
        return app()->make(MapperInterface::class);
    }
}
