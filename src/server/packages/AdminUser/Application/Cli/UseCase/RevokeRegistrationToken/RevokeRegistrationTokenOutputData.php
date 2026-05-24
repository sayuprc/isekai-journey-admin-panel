<?php

declare(strict_types=1);

namespace AdminUser\Application\Cli\UseCase\RevokeRegistrationToken;

readonly class RevokeRegistrationTokenOutputData
{
    public function __construct(public int $revokedCount)
    {
    }
}
