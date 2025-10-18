<?php

declare(strict_types=1);

namespace Support\DebugInfrastructures\Repository;

readonly class FileRepositoryonfig
{
    public function __construct(public string $filePath)
    {
    }
}
