<?php

declare(strict_types=1);

namespace Auth\Infrastructures\Token\RefreshToken;

use Auth\Domain\Services\Token\RefreshToken\TokenHasherInterface;

readonly class TokenHasher implements TokenHasherInterface
{
    public function hash(string $plainToken): string
    {
        return password_hash($plainToken, PASSWORD_DEFAULT);
    }

    public function verify(string $plainToken, string $hashedToken): bool
    {
        return password_verify($plainToken, $hashedToken);
    }
}
