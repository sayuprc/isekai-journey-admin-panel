<?php

declare(strict_types=1);

namespace User\Application\UseCase\Authenticate;

class AuthenticateInputData
{
    public function __construct(public readonly string $accessToken)
    {
    }
}
