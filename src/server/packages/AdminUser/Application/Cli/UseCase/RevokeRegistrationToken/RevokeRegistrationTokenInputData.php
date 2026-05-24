<?php

declare(strict_types=1);

namespace AdminUser\Application\Cli\UseCase\RevokeRegistrationToken;

readonly class RevokeRegistrationTokenInputData
{
    public function __construct(public string $email)
    {
    }
}
