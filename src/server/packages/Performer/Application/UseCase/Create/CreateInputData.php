<?php

declare(strict_types=1);

namespace Performer\Application\UseCase\Create;

readonly class CreateInputData
{
    /**
     * @param positive-int $orderNo
     */
    public function __construct(
        public string $performerName,
        public int $orderNo,
    ) {
    }
}
