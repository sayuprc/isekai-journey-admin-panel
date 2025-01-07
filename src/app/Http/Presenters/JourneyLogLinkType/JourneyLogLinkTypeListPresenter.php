<?php

declare(strict_types=1);

namespace App\Http\Presenters\JourneyLogLinkType;

use App\Http\ViewModels\JourneyLogLink\JourneyLogLinkTypeListView;
use JourneyLogLinkType\Domain\Entities\JourneyLogLinkType;

class JourneyLogLinkTypeListPresenter
{
    public function __construct(private readonly JourneyLogLinkType $journeyLogLinkType)
    {
    }

    public function present(): JourneyLogLinkTypeListView
    {
        return new JourneyLogLinkTypeListView(
            $this->journeyLogLinkType->journeyLogLinkTypeId->value,
            $this->journeyLogLinkType->journeyLogLinkTypeName->value,
            $this->journeyLogLinkType->orderNo->value,
        );
    }
}
