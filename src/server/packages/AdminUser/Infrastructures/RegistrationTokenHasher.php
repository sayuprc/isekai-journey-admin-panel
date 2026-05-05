<?php

declare(strict_types=1);

namespace AdminUser\Infrastructures;

use AdminUser\Domain\Services\RegistrationTokenHasherInterface;
use Illuminate\Support\Facades\Hash;
use Override;
use SensitiveParameter;

readonly class RegistrationTokenHasher implements RegistrationTokenHasherInterface
{
    #[Override]
    public function hash(#[SensitiveParameter] string $plainToken): string
    {
        return Hash::make($plainToken);
    }

    #[Override]
    public function verify(#[SensitiveParameter] string $plainToken, string $hashedToken): bool
    {
        return Hash::check($plainToken, $hashedToken);
    }
}
