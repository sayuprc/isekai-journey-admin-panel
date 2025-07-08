<?php

declare(strict_types=1);

namespace User\Application\UseCase\Login;

class LoginInputData
{
    public function __construct(public readonly string $userId)
    {
    }
}
