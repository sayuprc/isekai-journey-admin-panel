<?php

declare(strict_types=1);

namespace AdminUser\Application\Cli\UseCase\Create;

readonly class CreateInputData
{
    /**
     * @param list<string> $permissions
     */
    public function __construct(
        public string $name,
        public string $email,
        public int $role,
        public array $permissions,
    ) {
    }
}
