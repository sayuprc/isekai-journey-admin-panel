<?php

declare(strict_types=1);

namespace Auth\Application\UseCase\RegisterStart;

readonly class RegisterStartInputData
{
    public function __construct(
        public string $email,
        public string $registrationToken,
    ) {
    }
}
