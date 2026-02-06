<?php

declare(strict_types=1);

namespace Song\Application\Assemble;

readonly class AssembledCreator
{
    public function __construct(
        public string $creatorId,
        public string $creatorName,
        public int $orderNo,
    ) {
    }
}
