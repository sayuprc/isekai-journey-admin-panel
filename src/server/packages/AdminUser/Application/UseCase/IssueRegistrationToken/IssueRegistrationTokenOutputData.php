<?php

declare(strict_types=1);

namespace AdminUser\Application\UseCase\IssueRegistrationToken;

use AdminUser\Domain\Models\AdminUserRegistrationToken;

readonly class IssueRegistrationTokenOutputData
{
    public function __construct(
        public AdminUserRegistrationToken $token,
        public string $plainToken,
    ) {
    }
}
