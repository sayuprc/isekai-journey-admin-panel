<?php

declare(strict_types=1);

namespace App\Http\Presenters\JourneyLog;

use App\Http\ViewModels\JourneyLog\JourneyLogLinkView;
use App\Http\ViewModels\JourneyLog\JourneyLogView;
use JourneyLog\Domain\Entities\JourneyLogLink;
use JourneyLog\UseCases\Get\GetResponse;

class JourneyLogPresenter
{
    private const string DATE_FORMAT = 'Y-m-d';

    public function present(GetResponse $response): JourneyLogView
    {
        return new JourneyLogView(
            $response->journeyLog->journeyLogId->value,
            $response->journeyLog->story->value,
            $response->journeyLog->period->fromOn->value->format(self::DATE_FORMAT),
            $response->journeyLog->period->toOn->value->format(self::DATE_FORMAT),
            $response->journeyLog->orderNo->value,
            array_map(function (JourneyLogLink $journeyLogLink): JourneyLogLinkView {
                return new JourneyLogLinkView(
                    $journeyLogLink->journeyLogLinkTypeId->value,
                    $journeyLogLink->journeyLogLinkName->value,
                    $journeyLogLink->orderNo->value,
                    $journeyLogLink->journeyLogLinkTypeId->value,
                );
            }, $response->journeyLog->journeyLogLinks)
        );
    }
}
