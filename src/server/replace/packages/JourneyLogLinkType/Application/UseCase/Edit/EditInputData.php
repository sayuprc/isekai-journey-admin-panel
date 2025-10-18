<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Application\UseCase\Edit;

readonly class EditInputData
{
    /**
     * @param positive-int $orderNo
     */
    public function __construct(
        public string $journeyLogLinkTypeId,
        public string $journeyLogLinkTypeName,
        public int $orderNo,
    ) {
    }
}
