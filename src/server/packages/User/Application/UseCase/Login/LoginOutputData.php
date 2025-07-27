<?php

declare(strict_types=1);

namespace User\Application\UseCase\Login;

use User\Domain\Models\Credential\Credential;

readonly class LoginOutputData
{
    public function __construct(public Credential $credential)
    {
    }
}
