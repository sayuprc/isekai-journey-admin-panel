<?php

declare(strict_types=1);

namespace AdminUser\Application\Cli\UseCase\Invite;

use AdminUser\Domain\Models\Invitation\ExpiresAt;
use AdminUser\Domain\Models\Invitation\PlainToken;

readonly class InviteOutputData
{
    public function __construct(
        public PlainToken $plainToken,
        public ExpiresAt $expiresAt,
    ) {
    }
}
