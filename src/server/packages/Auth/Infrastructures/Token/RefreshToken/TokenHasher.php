<?php

declare(strict_types=1);

namespace Auth\Infrastructures\Token\RefreshToken;

use Auth\Domain\Services\Token\RefreshToken\TokenHasherInterface;
use Illuminate\Support\Facades\Hash;

readonly class TokenHasher implements TokenHasherInterface
{
    public function hash(string $plainToken): string
    {
        return Hash::make($plainToken);
    }

    public function verify(string $plainToken, string $hashedToken): bool
    {
        return Hash::check($plainToken, $hashedToken);
    }
}
