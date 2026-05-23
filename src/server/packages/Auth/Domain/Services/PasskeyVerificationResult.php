<?php

declare(strict_types=1);

namespace Auth\Domain\Services;

readonly class PasskeyVerificationResult
{
    public function __construct(
        public string $credentialId,
        public string $publicKey,
        public int $signCount,
    ) {
    }
}
