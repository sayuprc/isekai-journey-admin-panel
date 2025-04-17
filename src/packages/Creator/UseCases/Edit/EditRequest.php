<?php

declare(strict_types=1);

namespace Creator\UseCases\Edit;

class EditRequest
{
    public function __construct(
        public readonly string $creatorId,
        public readonly string $creatorName,
    ) {
    }
}
