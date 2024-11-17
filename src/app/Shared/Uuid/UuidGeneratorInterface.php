<?php

declare(strict_types=1);

namespace App\Shared\Uuid;

interface UuidGeneratorInterface
{
    public function generate(): string;
}
