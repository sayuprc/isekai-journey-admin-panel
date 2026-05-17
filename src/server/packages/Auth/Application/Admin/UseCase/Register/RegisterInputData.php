<?php

declare(strict_types=1);

namespace Auth\Application\Admin\UseCase\Register;

readonly class RegisterInputData
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $registrationToken,
    ) {
    }
}
