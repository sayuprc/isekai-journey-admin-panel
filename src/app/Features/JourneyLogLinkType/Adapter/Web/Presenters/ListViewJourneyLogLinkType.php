<?php

declare(strict_types=1);

namespace App\Features\JourneyLogLinkType\Adapter\Web\Presenters;

use App\Features\JourneyLogLinkType\Domain\Entities\JourneyLogLinkType;

class ListViewJourneyLogLinkType
{
    public function __construct(private readonly JourneyLogLinkType $journeyLogLinkType)
    {
    }

    public function journeyLogLinkTypeId(): string
    {
        return $this->journeyLogLinkType->journeyLogLinkTypeId->value;
    }

    public function journeyLogLinkTypeName(): string
    {
        return $this->journeyLogLinkType->journeyLogLinkTypeName->value;
    }

    public function orderNo(): int
    {
        return $this->journeyLogLinkType->orderNo->value;
    }
}
