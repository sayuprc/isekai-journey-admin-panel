<?php

declare(strict_types=1);

namespace SongType\Application\UseCase\Edit;

class EditInputData
{
    /**
     * @param positive-int $orderNo
     */
    public function __construct(
        public readonly string $songTypeId,
        public readonly string $songTypeName,
        public readonly int $orderNo,
    ) {
    }
}
