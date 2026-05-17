<?php

declare(strict_types=1);

namespace AdminUser\Application\Admin\UseCase\Register;

use SensitiveParameter;

readonly class RegisterInputData
{
    public function __construct(
        #[SensitiveParameter]
        public string $token,
        public string $name,
        public string $email,
        #[SensitiveParameter]
        public string $plainPassword,
    ) {
    }
}
