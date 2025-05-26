<?php

declare(strict_types=1);

namespace Auth\UseCases\Login;

class LoginOutputData
{
    public function __construct(public readonly bool $isSucceeded)
    {
    }
}
