<?php

declare(strict_types=1);

namespace Auth\Application\Admin\UseCase\Login;

readonly class LoginInputData
{
    public function __construct(public string $adminUserId)
    {
    }
}
