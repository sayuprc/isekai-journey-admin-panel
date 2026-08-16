<?php

declare(strict_types=1);

namespace Place\Application\Admin\UseCase\Create;

readonly class CreateInputData
{
    public function __construct(
        public string $name,
        public int $kindValue,
    ) {
    }
}
