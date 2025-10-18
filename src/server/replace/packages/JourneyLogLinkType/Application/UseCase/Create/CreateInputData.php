<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Application\UseCase\Create;

readonly class CreateInputData
{
    /**
     * @param positive-int $orderNo
     */
    public function __construct(
        public string $journeyLogLinkTypeName,
        public int $orderNo,
    ) {
    }
}
