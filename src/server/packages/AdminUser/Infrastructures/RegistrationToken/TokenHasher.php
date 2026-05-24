<?php

declare(strict_types=1);

namespace AdminUser\Infrastructures\RegistrationToken;

use AdminUser\Domain\Services\RegistrationToken\TokenHasherInterface;
use Illuminate\Support\Facades\Hash;
use Override;
use SensitiveParameter;

readonly class TokenHasher implements TokenHasherInterface
{
    #[Override]
    public function hash(#[SensitiveParameter] string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    #[Override]
    public function verify(#[SensitiveParameter] string $plainToken, string $hashedToken): bool
    {
        if (hash_equals($this->hash($plainToken), $hashedToken)) {
            return true;
        }

        if (password_get_info($hashedToken)['algoName'] === 'unknown') {
            return false;
        }

        return Hash::check($plainToken, $hashedToken);
    }
}
