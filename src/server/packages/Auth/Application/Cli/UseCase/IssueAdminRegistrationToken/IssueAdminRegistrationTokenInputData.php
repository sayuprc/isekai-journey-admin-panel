<?php

declare(strict_types=1);

namespace Auth\Application\Cli\UseCase\IssueAdminRegistrationToken;

readonly class IssueAdminRegistrationTokenInputData
{
    public function __construct(
        public string $email,
        public int $role,
    )
    {
    }
}
