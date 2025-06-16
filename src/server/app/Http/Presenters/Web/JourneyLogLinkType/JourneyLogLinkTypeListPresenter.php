<?php

declare(strict_types=1);

namespace App\Http\Presenters\Web\JourneyLogLinkType;

use App\Http\ViewModels\Web\JourneyLogLink\JourneyLogLinkTypeListView;
use JourneyLogLinkType\Application\UseCase\List\ListOutputData;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkType;

class JourneyLogLinkTypeListPresenter
{
    /**
     * @return array<JourneyLogLinkTypeListView>
     */
    public function present(ListOutputData $outputData): array
    {
        return array_map(function (JourneyLogLinkType $journeyLogLinkType): JourneyLogLinkTypeListView {
            return new JourneyLogLinkTypeListView(
                $journeyLogLinkType->journeyLogLinkTypeId->value,
                $journeyLogLinkType->journeyLogLinkTypeName->value,
                $journeyLogLinkType->orderNo->value,
            );
        }, $outputData->journeyLogLinkTypes);
    }
}
