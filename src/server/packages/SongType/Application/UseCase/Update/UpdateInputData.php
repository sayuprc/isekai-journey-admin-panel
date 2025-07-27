<?php

declare(strict_types=1);

namespace SongType\Application\UseCase\Update;

readonly class UpdateInputData
{
    /**
     * @param positive-int $orderNo
     */
    public function __construct(
        public string $songTypeId,
        public string $songTypeName,
        public int $orderNo,
    ) {
    }
}
