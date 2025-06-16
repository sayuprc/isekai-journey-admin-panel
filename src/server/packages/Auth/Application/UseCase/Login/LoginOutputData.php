<?php

declare(strict_types=1);

namespace Auth\Application\UseCase\Login;

class LoginOutputData
{
    public function __construct(public readonly bool $isSucceeded)
    {
    }
}
