<?php

declare(strict_types=1);

namespace Media\Application\UseCase\Update;

readonly class UpdateInputData
{
    public function __construct(
        public string $mediaId,
        public string $title,
        public string $url,
        public int $typeValue,
        public int $formatValue,
        public bool $isDisplay,
    ) {
    }
}
