<?php

declare(strict_types=1);

namespace Support\DebugInfrastructures\Repository;

class DebugConfig
{
    public function __construct(public readonly string $path)
    {
    }
}
