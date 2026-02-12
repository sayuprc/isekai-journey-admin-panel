<?php

declare(strict_types=1);

namespace Support\DebugInfrastructures\Repository;

use Support\Contracts\MapperInterface;
use Support\Infrastructures\Serializer;

/**
 * @template T
 */
readonly class FileStore
{
    public function __construct(
        private FileSystem $file,
        private Serializer $serializer,
        private MapperInterface $mapper,
    ) {
    }

    /**
     * @param class-string<T> $className
     *
     * @return array<string, T>
     */
    public function getAll(string $path, string $className): array
    {
        if (! $this->file->exists($path)) {
            return [];
        }

        $json = $this->file->get($path);
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($data)) {
            return [];
        }

        /** @var array<string, T> */
        return $this->mapper->map("array<string, {$className}>", $data);
    }

    /**
     * @param class-string<T> $className
     *
     * @return T|null
     */
    public function get(string $path, string $key, string $className): mixed
    {
        if (! $this->file->exists($path)) {
            return null;
        }

        return $this->getAll($path, $className)[$key] ?? null;
    }

    /**
     * @param T               $data
     * @param class-string<T> $className
     */
    public function put(string $path, string $key, mixed $data, string $className): void
    {
        $storedData = $this->file->exists($path)
            ? $this->getAll($path, $className)
            : [];

        $storedData[$key] = $data;

        $serializedData = $this->serializer->serialize($storedData);

        $this->file->put(
            $path,
            json_encode($serializedData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR),
        );
    }

    /**
     * @param class-string<T> $className
     */
    public function unset(string $path, string $key, string $className): void
    {
        $storedData = $this->file->exists($path)
            ? $this->getAll($path, $className)
            : [];

        unset($storedData[$key]);

        $serializedData = $this->serializer->serialize($storedData);

        $this->file->put(
            $path,
            json_encode($serializedData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR),
        );
    }
}
