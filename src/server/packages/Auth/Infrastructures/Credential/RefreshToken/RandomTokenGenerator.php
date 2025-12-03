<?php

declare(strict_types=1);

namespace Auth\Infrastructures\Credential\RefreshToken;

use Auth\Domain\Services\Credential\RefreshToken\RandomTokenGeneratorInterface;

readonly class RandomTokenGenerator implements RandomTokenGeneratorInterface
{
    private const int LENGTH = 64;

    public function generate(): string
    {
        return bin2hex(random_bytes(self::LENGTH));
    }
}
