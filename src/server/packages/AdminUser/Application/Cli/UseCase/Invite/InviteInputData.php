<?php

declare(strict_types=1);

namespace AdminUser\Application\Cli\UseCase\Invite;

readonly class InviteInputData
{
    /**
     * @param list<string> $permissions
     */
    public function __construct(
        public int $role,
        public array $permissions,
        public int $expiresInHours,
    ) {
    }
}
