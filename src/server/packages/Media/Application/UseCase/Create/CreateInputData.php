<?php

declare(strict_types=1);

namespace Media\Application\UseCase\Create;

readonly class CreateInputData
{
    public function __construct(
        public string $title,
        public string $url,
        public string $publishedAt,
        public int $typeValue,
        public int $formatValue,
        public bool $isDisplay,
    ) {
    }
}
