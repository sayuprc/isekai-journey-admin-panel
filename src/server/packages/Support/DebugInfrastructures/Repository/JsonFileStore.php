<?php

declare(strict_types=1);

namespace Support\DebugInfrastructures\Repository;

readonly class JsonFileStore
{
    public function __construct(private FileSystem $file)
    {
    }

    /**
     * @return array<mixed>
     */
    public function load(string $path): array
    {
        $path = $this->withExtension($path);

        if (! $this->file->exists($path)) {
            return [];
        }

        $data = $this->file->get($path);

        if ($data === '') {
            return [];
        }

        /** @var array<mixed> */
        return json_decode($data, associative: true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * @param array<mixed> $data
     */
    public function save(string $path, array $data, null|int|string $index = null): void
    {
        $path = $this->withExtension($path);

        $storedData = $this->file->exists($path)
            ? $this->load($path)
            : [];

        if (! is_null($index)) {
            $storedData[$index] = $data;
        } else {
            $storedData[] = $data;
        }

        $this->file->put($path, $this->jsonEncode($storedData));
    }

    public function unset(string $path, int|string $index): void
    {
        $path = $this->withExtension($path);

        $storedData = $this->file->exists($path)
            ? $this->load($path)
            : [];

        unset($storedData[$index]);

        $this->file->put($path, $this->jsonEncode(array_values($storedData)));
    }

    private function withExtension(string $path): string
    {
        return str_ends_with($path, '.json')
            ? $path
            : $path . '.json';
    }

    /**
     * @param array<mixed> $data
     */
    private function jsonEncode(array $data): string
    {
        return json_encode($data, flags: JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }
}
