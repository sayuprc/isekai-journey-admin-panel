<?php

declare(strict_types=1);

namespace AdminUser\Application\Admin\UseCase\RegisterFinish;

readonly class RegisterFinishInputData
{
    /**
     * @param array<string, mixed> $credential
     */
    public function __construct(
        public string $authCeremonyId,
        public array $credential,
    ) {
    }
}
