<?php

declare(strict_types=1);

namespace Support\Repository;

/**
 * @template T
 */
readonly class FileStore
{
    public function __construct(private FileSystem $file)
    {
    }

    /**
     * @return array<string, T>
     */
    public function getAll(string $path): array
    {
        if (! $this->file->exists($path)) {
            return [];
        }

        /** @var array<string, T> */
        return unserialize($this->file->get($path));
    }

    /**
     * @return T|null
     */
    public function get(string $path, string $key): mixed
    {
        if (! $this->file->exists($path)) {
            return null;
        }

        return $this->getAll($path)[$key] ?? null;
    }

    /**
     * @param T $data
     */
    public function put(string $path, string $key, mixed $data): void
    {
        $storedData = $this->file->exists($path)
            ? $this->getAll($path)
            : [];

        $storedData[$key] = $data;

        $this->file->put($path, serialize($storedData));
    }

    public function unset(string $path, string $key): void
    {
        $storedData = $this->file->exists($path)
            ? $this->getAll($path)
            : [];

        unset($storedData[$key]);

        $this->file->put($path, serialize($storedData));
    }
}
