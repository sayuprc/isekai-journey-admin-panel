<?php

declare(strict_types=1);

namespace Auth\Infrastructures\Token\RefreshToken;

use Auth\Domain\Services\Token\RefreshToken\TokenHasherInterface;
use Illuminate\Support\Facades\Hash;
use SensitiveParameter;

readonly class TokenHasher implements TokenHasherInterface
{
    public function hash(#[SensitiveParameter] string $plainToken): string
    {
        return Hash::make($plainToken);
    }

    public function verify(#[SensitiveParameter] string $plainToken, string $hashedToken): bool
    {
        return Hash::check($plainToken, $hashedToken);
    }
}
