<?php

declare(strict_types=1);

namespace Auth\Infrastructures\RecoveryCode;

use Auth\Domain\Services\RecoveryCode\RecoveryCodeHasherInterface;
use Override;
use SensitiveParameter;

readonly class RecoveryCodeHasher implements RecoveryCodeHasherInterface
{
    #[Override]
    public function hash(#[SensitiveParameter] string $plainCode): string
    {
        return hash('sha256', $plainCode);
    }

    #[Override]
    public function verify(#[SensitiveParameter] string $plainCode, string $hashedCode): bool
    {
        return hash_equals($this->hash($plainCode), $hashedCode);
    }
}
