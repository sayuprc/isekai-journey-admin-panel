<?php

declare(strict_types=1);

namespace SongType\UseCases\Create;

class CreateRequest
{
    /**
     * @param positive-int $orderNo
     */
    public function __construct(
        public readonly string $songTypeName,
        public readonly int $orderNo,
    ) {
    }
}
