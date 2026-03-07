<?php

declare(strict_types=1);

namespace Support\Infrastructures\Uuid;

use Ramsey\Uuid\Uuid;
use Support\Contracts\Uuid\UuidConverterInterface;

class UuidConverter implements UuidConverterInterface
{
    public function toBin(string $uuid): string
    {
        return Uuid::fromString($uuid)->getBytes();
    }

    public function toUuid(string $bin): string
    {
        return Uuid::fromBytes($bin)->toString();
    }
}
