<?php

declare(strict_types=1);

namespace Auth\Domain\Services\Token\RefreshToken;

interface TokenHasherInterface
{
    /**
     * Hash a plain text token for storage
     */
    public function hash(string $plainToken): string;

    /**
     * Verify a plain text token against a hashed token
     */
    public function verify(string $plainToken, string $hashedToken): bool;
}
