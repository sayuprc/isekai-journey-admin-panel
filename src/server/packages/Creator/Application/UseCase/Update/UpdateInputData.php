<?php

declare(strict_types=1);

namespace Creator\Application\UseCase\Update;

class UpdateInputData
{
    public function __construct(
        public readonly string $creatorId,
        public readonly string $creatorName,
    ) {
    }
}
