<?php

declare(strict_types=1);

namespace Auth\Application\UseCase\LoginStart;

readonly class LoginStartOutputData
{
    /**
     * @param array<string, mixed> $publicKey
     */
    public function __construct(
        public string $authCeremonyId,
        public array $publicKey,
    ) {
    }
}
