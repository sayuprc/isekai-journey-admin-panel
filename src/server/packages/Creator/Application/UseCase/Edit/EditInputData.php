<?php

declare(strict_types=1);

namespace Creator\Application\UseCase\Edit;

class EditInputData
{
    public function __construct(
        public readonly string $creatorId,
        public readonly string $creatorName,
    ) {
    }
}
