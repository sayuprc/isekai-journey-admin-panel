<?php

declare(strict_types=1);

namespace AdminUser\Domain\Services;

use SensitiveParameter;

interface RegistrationTokenHasherInterface
{
    public function hash(#[SensitiveParameter] string $plainToken): string;

    public function verify(#[SensitiveParameter] string $plainToken, string $hashedToken): bool;
}
