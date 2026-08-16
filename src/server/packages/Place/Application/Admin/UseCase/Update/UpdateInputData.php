<?php

declare(strict_types=1);

namespace Place\Application\Admin\UseCase\Update;

readonly class UpdateInputData
{
    public function __construct(
        public string $placeId,
        public string $name,
        public int $kindValue,
    ) {
    }
}
