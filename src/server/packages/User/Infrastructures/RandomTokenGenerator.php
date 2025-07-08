<?php

declare(strict_types=1);

namespace User\Infrastructures;

use User\Domain\Services\RandomTokenGeneratorInterface;

class RandomTokenGenerator implements RandomTokenGeneratorInterface
{
    private const int LENGTH = 64;

    public function generate(): string
    {
        return bin2hex(random_bytes(self::LENGTH));
    }
}
