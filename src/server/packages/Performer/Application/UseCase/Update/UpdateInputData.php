<?php

declare(strict_types=1);

namespace Performer\Application\UseCase\Update;

readonly class UpdateInputData
{
    /**
     * @param positive-int $orderNo
     */
    public function __construct(
        public string $performerId,
        public string $name,
        public int $orderNo,
    ) {
    }
}
