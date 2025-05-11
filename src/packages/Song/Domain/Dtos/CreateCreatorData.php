<?php

declare(strict_types=1);

namespace Song\Domain\Dtos;

class CreateCreatorData
{
    /**
     * @param positive-int $orderNo
     */
    public function __construct(
        public readonly string $creatorId,
        public readonly int $orderNo,
    ) {
    }
}
