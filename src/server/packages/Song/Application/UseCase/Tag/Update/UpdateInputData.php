<?php

declare(strict_types=1);

namespace Song\Application\UseCase\Tag\Update;

readonly class UpdateInputData
{
    public function __construct(
        public string $songTagId,
        public string $name,
        public int $orderNo,
    ) {
    }
}
