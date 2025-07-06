<?php

declare(strict_types=1);

namespace Tests\Support;

use FilesystemIterator;
use Mockery;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RuntimeException;
use SplFileInfo;
use Support\Contracts\ConfigInterface;
use Support\Repository\FileStore;

trait FileRepositoryTransaction
{
    private const string FILE_DIR = __DIR__ . '/../../storage/app/tests';

    protected function setUp(): void
    {
        parent::setUp();

        $config = Mockery::mock(ConfigInterface::class);

        $directory = $this->getDirectoryName();

        $config->shouldReceive('getString')
            ->with('debug.file.path')
            ->andReturn($directory);

        $config->shouldReceive('getString')
            ->with('openapi.path')
            ->andReturn(config()->string('openapi.path'));

        $this->app->bind(ConfigInterface::class, fn () => $config);

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
        return self::FILE_DIR . '/' . new ReflectionClass($this)->getName() . '/' . $this->name();
    }

    private function factory(string $repository, int|string $key, mixed $value): void
    {
        /** @var FileStore $store */
        $store = $this->app->make(FileStore::class);

        $store->put($this->getFileName($repository), $key, $value);
    }

    private function getAll(string $repository): array
    {
        /** @var FileStore $store */
        $store = $this->app->make(FileStore::class);

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
