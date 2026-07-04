<?php

declare(strict_types=1);

namespace Release\Application\Admin\Storage;

interface JacketArtStorageInterface
{
    public function put(string $key, string $content, string $contentType): string;
}
