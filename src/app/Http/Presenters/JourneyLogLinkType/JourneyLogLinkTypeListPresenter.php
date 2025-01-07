<?php

declare(strict_types=1);

namespace App\Http\Presenters\JourneyLogLinkType;

use App\Http\ViewModels\JourneyLogLink\JourneyLogLinkTypeListView;
use JourneyLogLinkType\Domain\Entities\JourneyLogLinkType;
use JourneyLogLinkType\UseCases\List\ListResponse;

class JourneyLogLinkTypeListPresenter
{
    /**
     * @return array<JourneyLogLinkTypeListView>
     */
    public function present(ListResponse $response): array
    {
        return array_map(function (JourneyLogLinkType $journeyLogLinkType): JourneyLogLinkTypeListView {
            return new JourneyLogLinkTypeListView(
                $journeyLogLinkType->journeyLogLinkTypeId->value,
                $journeyLogLinkType->journeyLogLinkTypeName->value,
                $journeyLogLinkType->orderNo->value,
            );
        }, $response->journeyLogLinkTypes);
    }
}
