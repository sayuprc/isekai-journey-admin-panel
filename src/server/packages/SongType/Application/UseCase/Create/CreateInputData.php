<?php

declare(strict_types=1);

namespace SongType\Application\UseCase\Create;

readonly class CreateInputData
{
    /**
     * @param positive-int $orderNo
     */
    public function __construct(
        public string $songTypeName,
        public int $orderNo,
    ) {
    }
}
