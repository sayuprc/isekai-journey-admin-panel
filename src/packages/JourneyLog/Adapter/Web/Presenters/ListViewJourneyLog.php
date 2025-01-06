<?php

declare(strict_types=1);

namespace JourneyLog\Adapter\Web\Presenters;

use JourneyLog\Domain\Entities\JourneyLog;

class ListViewJourneyLog
{
    private const string DATE_FORMAT = 'Y-m-d';

    public function __construct(private readonly JourneyLog $journeyLog)
    {
    }

    public function journeyLogId(): string
    {
        return $this->journeyLog->journeyLogId->value;
    }

    public function story(): string
    {
        return $this->journeyLog->story->value;
    }

    public function orderNo(): int
    {
        return $this->journeyLog->orderNo->value;
    }

    public function period(): string
    {
        return $this->journeyLog->period->isSingleDay()
            ? $this->journeyLog->period->fromOn->value->format(self::DATE_FORMAT)
            : $this->journeyLog->period->fromOn->value->format(self::DATE_FORMAT)
                . ' ~ '
                . $this->journeyLog->period->toOn->value->format(self::DATE_FORMAT);
    }
}
