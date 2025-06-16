<?php

declare(strict_types=1);

namespace Support\Application\Uuid;

use Str;
use Support\Contracts\UuidGeneratorInterface;

class UuidGenerator implements UuidGeneratorInterface
{
    public function generate(): string
    {
        return Str::uuid()->toString();
    }
}
