<?php

declare(strict_types=1);

namespace User\Application\UseCase\Login;

readonly class LoginInputData
{
    public function __construct(public string $userId)
    {
    }
}
