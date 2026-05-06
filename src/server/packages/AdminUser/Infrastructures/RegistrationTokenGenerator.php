<?php

declare(strict_types=1);

namespace AdminUser\Infrastructures;

use AdminUser\Domain\Services\RegistrationTokenGeneratorInterface;
use Override;

readonly class RegistrationTokenGenerator implements RegistrationTokenGeneratorInterface
{
    private const int LENGTH = 64;

    #[Override]
    public function generate(): string
    {
        return bin2hex(random_bytes(self::LENGTH));
    }
}
