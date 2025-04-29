<?php

declare(strict_types=1);

namespace App\Http\Presenters\Web\JourneyLogLinkType;

use App\Http\ViewModels\Web\JourneyLogLink\JourneyLogLinkTypeView;
use JourneyLogLinkType\UseCases\Get\GetOutputData;

class JourneyLogLinkTypePresenter
{
    public function present(GetOutputData $outputData): JourneyLogLinkTypeView
    {
        return new JourneyLogLinkTypeView(
            $outputData->journeyLogLinkType->journeyLogLinkTypeId->value,
            $outputData->journeyLogLinkType->journeyLogLinkTypeName->value,
            $outputData->journeyLogLinkType->orderNo->value,
        );
    }
}
