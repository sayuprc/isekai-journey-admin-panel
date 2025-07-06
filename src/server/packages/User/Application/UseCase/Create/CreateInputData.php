<?php

declare(strict_types=1);

namespace User\Application\UseCase\Create;

class CreateInputData
{
    public function __construct(
        public readonly string $email,
        public readonly string $plainPassword,
    ) {
    }
}
