<?php

declare(strict_types=1);

namespace User\Application\UseCase\Login;

use SensitiveParameter;

readonly class LoginInputData
{
    public function __construct(
        public string $email,
        #[SensitiveParameter] public string $password,
    ) {
    }
}
