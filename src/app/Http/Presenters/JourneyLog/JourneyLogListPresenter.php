<?php

declare(strict_types=1);

namespace App\Http\Presenters\JourneyLog;

use App\Http\ViewModels\JourneyLog\JourneyLogListView;
use JourneyLog\Domain\Entities\JourneyLog;

class JourneyLogListPresenter
{
    private const string DATE_FORMAT = 'Y-m-d';

    public function __construct(private readonly JourneyLog $journeyLog)
    {
    }

    public function present(): JourneyLogListView
    {
        return new JourneyLogListView(
            $this->journeyLog->journeyLogId->value,
            $this->journeyLog->story->value,
            $this->period(),
            $this->journeyLog->orderNo->value,
        );
    }

    private function period(): string
    {
        return $this->journeyLog->period->isSingleDay()
            ? $this->journeyLog->period->fromOn->value->format(self::DATE_FORMAT)
            : $this->journeyLog->period->fromOn->value->format(self::DATE_FORMAT)
            . ' ~ '
            . $this->journeyLog->period->toOn->value->format(self::DATE_FORMAT);
    }
}
