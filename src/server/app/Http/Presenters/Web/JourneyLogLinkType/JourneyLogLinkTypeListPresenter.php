<?php

declare(strict_types=1);

namespace App\Http\Presenters\Web\JourneyLogLinkType;

use App\Http\ViewModels\Web\JourneyLogLink\JourneyLogLinkTypeListView;
use JourneyLogLinkType\Domain\Models\JourneyLogLinkType;
use JourneyLogLinkType\UseCases\List\ListOutputData;

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
