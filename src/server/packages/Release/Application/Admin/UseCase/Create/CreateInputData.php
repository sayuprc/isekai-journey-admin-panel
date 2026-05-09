<?php

declare(strict_types=1);

namespace Release\Application\Admin\UseCase\Create;

readonly class CreateInputData
{
    public function __construct(
        public string $title,
        public int $typeValue,
        public int $distributionTypeValue,
        public string $releasedOn,
        public string $description,
        public bool $isDisplay,
    ) {
    }
}
