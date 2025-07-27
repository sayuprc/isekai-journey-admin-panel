<?php

declare(strict_types=1);

namespace User\Application\UseCase\Authenticate;

readonly class AuthenticateInputData
{
    public function __construct(public string $accessToken)
    {
    }
}
