<?php

declare(strict_types=1);

namespace AdminUser\Application\UseCase\Create;

readonly class CreateInputData
{
    /**
     * @param list<string> $permissions
     */
    public function __construct(
        public string $email,
        public string $plainPassword,
        public int $role,
        public array $permissions,
    ) {
    }
}
