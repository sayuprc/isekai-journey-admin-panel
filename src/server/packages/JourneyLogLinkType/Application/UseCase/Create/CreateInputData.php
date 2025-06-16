<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Application\UseCase\Create;

class CreateInputData
{
    /**
     * @param positive-int $orderNo
     */
    public function __construct(
        public readonly string $journeyLogLinkTypeName,
        public readonly int $orderNo,
    ) {
    }
}
