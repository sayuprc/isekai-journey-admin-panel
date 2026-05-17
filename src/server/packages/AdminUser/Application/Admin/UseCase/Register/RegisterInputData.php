<?php

declare(strict_types=1);

namespace AdminUser\Application\Admin\UseCase\Register;

use SensitiveParameter;

readonly class RegisterInputData
{
    public function __construct(
        #[SensitiveParameter]
        public string $plainToken,
        public string $email,
        public string $name,
        #[SensitiveParameter]
        public string $plainPassword,
    ) {
    }
}
