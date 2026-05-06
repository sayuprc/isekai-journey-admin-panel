<?php

declare(strict_types=1);

namespace AdminUser\Application\UseCase\IssueRegistrationToken;

readonly class IssueRegistrationTokenInputData
{
    public function __construct(
        public string $name,
        public string $email,
        public int $role,
        public int $expiresInMinutes,
    ) {
    }
}
