<?php

declare(strict_types=1);

namespace Support\Uuid;

interface UuidGeneratorInterface
{
    public function generate(): string;
}
