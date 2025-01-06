<?php

declare(strict_types=1);

namespace Shared\Uuid;

interface UuidGeneratorInterface
{
    public function generate(): string;
}
