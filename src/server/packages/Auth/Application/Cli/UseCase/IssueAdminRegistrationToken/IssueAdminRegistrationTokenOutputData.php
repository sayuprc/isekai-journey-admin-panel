<?php

declare(strict_types=1);

namespace Auth\Application\Cli\UseCase\IssueAdminRegistrationToken;

readonly class IssueAdminRegistrationTokenOutputData
{
    public function __construct(public string $plainToken)
    {
    }
}
