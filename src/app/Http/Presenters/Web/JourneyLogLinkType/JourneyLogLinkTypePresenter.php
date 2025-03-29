<?php

declare(strict_types=1);

namespace App\Http\Presenters\Web\JourneyLogLinkType;

use App\Http\ViewModels\Web\JourneyLogLink\JourneyLogLinkTypeView;
use JourneyLogLinkType\UseCases\Get\GetResponse;

class JourneyLogLinkTypePresenter
{
    public function present(GetResponse $response): JourneyLogLinkTypeView
    {
        return new JourneyLogLinkTypeView(
            $response->journeyLogLinkType->journeyLogLinkTypeId->value,
            $response->journeyLogLinkType->journeyLogLinkTypeName->value,
            $response->journeyLogLinkType->orderNo->value,
        );
    }
}
