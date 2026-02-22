<?php

declare(strict_types=1);

namespace Creator\Application\UseCase\Update;

readonly class UpdateInputData
{
    public function __construct(
        public string $creatorId,
        public string $name,
    ) {
    }
}
