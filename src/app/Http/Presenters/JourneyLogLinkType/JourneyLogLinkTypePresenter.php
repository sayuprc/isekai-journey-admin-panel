<?php

declare(strict_types=1);

namespace App\Http\Presenters\JourneyLogLinkType;

use App\Http\ViewModels\JourneyLogLink\JourneyLogLinkTypeView;
use JourneyLogLinkType\Domain\Entities\JourneyLogLinkType;

class JourneyLogLinkTypePresenter
{
    public function __construct(private readonly JourneyLogLinkType $journeyLogLinkType)
    {
    }

    public function present(): JourneyLogLinkTypeView
    {
        return new JourneyLogLinkTypeView(
            $this->journeyLogLinkType->journeyLogLinkTypeId->value,
            $this->journeyLogLinkType->journeyLogLinkTypeName->value,
            $this->journeyLogLinkType->orderNo->value,
        );
    }
}
