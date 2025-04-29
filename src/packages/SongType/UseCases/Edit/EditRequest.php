<?php

declare(strict_types=1);

namespace SongType\UseCases\Edit;

class EditRequest
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
