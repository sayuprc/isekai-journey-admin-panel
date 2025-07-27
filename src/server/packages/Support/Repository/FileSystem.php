<?php

declare(strict_types=1);

namespace Support\Repository;

readonly class FileSystem
{
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    public function put(string $path, string $content): void
    {
        file_put_contents($path, $content);
    }

    public function get(string $path): string
    {
        $data = file_get_contents($path);
        assert(is_string($data));

        return $data;
    }
}
