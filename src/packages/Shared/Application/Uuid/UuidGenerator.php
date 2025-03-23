<?php

declare(strict_types=1);

namespace Shared\Application\Uuid;

use Shared\Uuid\UuidGeneratorInterface;
use Str;

class UuidGenerator implements UuidGeneratorInterface
{
    public function generate(): string
    {
        return Str::uuid()->toString();
    }
}
