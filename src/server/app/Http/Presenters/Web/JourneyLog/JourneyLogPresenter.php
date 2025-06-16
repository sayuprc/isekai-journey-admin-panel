<?php

declare(strict_types=1);

namespace App\Http\Presenters\Web\JourneyLog;

use App\Http\ViewModels\Web\JourneyLog\JourneyLogLinkView;
use App\Http\ViewModels\Web\JourneyLog\JourneyLogView;
use JourneyLog\Application\UseCase\Get\GetOutputData;
use JourneyLog\Domain\Models\JourneyLogLink\JourneyLogLink;

class JourneyLogPresenter
{
    private const string DATE_FORMAT = 'Y-m-d';

    public function present(GetOutputData $outputData): JourneyLogView
    {
        return new JourneyLogView(
            $outputData->journeyLog->journeyLogId->value,
            $outputData->journeyLog->story->value,
            $outputData->journeyLog->period->fromOn->value->format(self::DATE_FORMAT),
            $outputData->journeyLog->period->toOn->value->format(self::DATE_FORMAT),
            $outputData->journeyLog->orderNo->value,
            array_map(function (JourneyLogLink $journeyLogLink): JourneyLogLinkView {
                return new JourneyLogLinkView(
                    $journeyLogLink->journeyLogLinkTypeId->value,
                    $journeyLogLink->journeyLogLinkName->value,
                    $journeyLogLink->orderNo->value,
                    $journeyLogLink->journeyLogLinkTypeId->value,
                );
            }, $outputData->journeyLog->journeyLogLinks)
        );
    }
}
