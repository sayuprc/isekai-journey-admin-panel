<?php

declare(strict_types=1);

namespace Support\Contracts;

interface UuidGeneratorInterface
{
    public function generate(): string;
}
