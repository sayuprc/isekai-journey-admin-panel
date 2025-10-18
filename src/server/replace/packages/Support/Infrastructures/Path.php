<?php

declare(strict_types=1);

namespace Support\Infrastructures;

use Tempest\Core\Kernel;

readonly class Path
{
    public function __construct(private Kernel $kernel)
    {
    }

    public function base(string $path): string
    {
        return $this->kernel->root . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
    }
}
