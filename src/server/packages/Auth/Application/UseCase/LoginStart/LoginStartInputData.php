<?php

declare(strict_types=1);

namespace Auth\Application\UseCase\LoginStart;

readonly class LoginStartInputData
{
    public function __construct(public string $email)
    {
    }
}
