<?php

declare(strict_types=1);

namespace App\Http\Presenters\JourneyLog;

use App\Http\ViewModels\JourneyLog\JourneyLogLinkView;
use App\Http\ViewModels\JourneyLog\JourneyLogView;
use JourneyLog\Domain\Entities\JourneyLog;
use JourneyLog\Domain\Entities\JourneyLogLink;

class JourneyLogPresenter
{
    private const string DATE_FORMAT = 'Y-m-d';

    public function __construct(private readonly JourneyLog $journeyLog)
    {
    }

    public function present(): JourneyLogView
    {
        return new JourneyLogView(
            $this->journeyLog->journeyLogId->value,
            $this->journeyLog->story->value,
            $this->journeyLog->period->fromOn->value->format(self::DATE_FORMAT),
            $this->journeyLog->period->toOn->value->format(self::DATE_FORMAT),
            $this->journeyLog->orderNo->value,
            array_map(function (JourneyLogLink $journeyLogLink): JourneyLogLinkView {
                return new JourneyLogLinkView(
                    $journeyLogLink->journeyLogLinkTypeId->value,
                    $journeyLogLink->journeyLogLinkName->value,
                    $journeyLogLink->orderNo->value,
                    $journeyLogLink->journeyLogLinkTypeId->value,
                );
            }, $this->journeyLog->journeyLogLinks)
        );
    }
}
