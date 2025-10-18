<?php

declare(strict_types=1);

namespace Support\Infrastructures;

use Ramsey\Uuid\Uuid;
use Support\Contracts\UuidGeneratorInterface;

readonly class UuidGenerator implements UuidGeneratorInterface
{
    public function generate(): string
    {
        return Uuid::uuid7()->toString();
    }
}
