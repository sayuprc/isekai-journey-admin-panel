<?php

declare(strict_types=1);

namespace AdminUser\Application\UseCase\Create;

use SensitiveParameter;

readonly class CreateInputData
{
    /**
     * @param list<string> $permissions
     */
    public function __construct(
        public string $adminUserName,
        public string $email,
        #[SensitiveParameter]
        public string $plainPassword,
        public int $role,
        public array $permissions,
    ) {
    }
}
