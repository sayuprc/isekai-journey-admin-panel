<?php

declare(strict_types=1);

namespace Auth\Application\UseCase\RegisterStart;

readonly class RegisterStartOutputData
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
