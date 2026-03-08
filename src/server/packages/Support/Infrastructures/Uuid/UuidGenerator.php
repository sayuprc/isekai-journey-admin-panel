<?php

declare(strict_types=1);

namespace Support\Infrastructures\Uuid;

use Illuminate\Support\Str;
use Support\Contracts\Uuid\UuidGeneratorInterface;

readonly class UuidGenerator implements UuidGeneratorInterface
{
    public function generate(): string
    {
        return Str::uuid()->toString();
    }
}
