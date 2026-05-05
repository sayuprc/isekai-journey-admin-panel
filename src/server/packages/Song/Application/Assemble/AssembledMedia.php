<?php

declare(strict_types=1);

namespace Song\Application\Assemble;

readonly class AssembledMedia
{
    public function __construct(
        public string $mediaId,
        public string $title,
        public string $url,
        public string $typeName,
        public int $typeValue,
        public bool $isDisplay,
        public int $songMediaTypeValue,
        public int $orderNo,
    ) {
    }
}
