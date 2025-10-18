<?php

declare(strict_types=1);

namespace User\Application\UseCase\Create;

readonly class CreateInputData
{
    public function __construct(
        public string $email,
        public string $plainPassword,
    ) {
    }
}
