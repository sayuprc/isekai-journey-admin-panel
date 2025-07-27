<?php

declare(strict_types=1);

namespace Support\Infrastructures;

use Illuminate\Support\Str;
use Support\Contracts\UuidGeneratorInterface;

readonly class UuidGenerator implements UuidGeneratorInterface
{
    public function generate(): string
    {
        return Str::uuid()->toString();
    }
}
