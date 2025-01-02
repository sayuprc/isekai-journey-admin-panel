<?php

declare(strict_types=1);

namespace JourneyLog\Adapter\Web\Presenters;

use Exception;
use JourneyLog\Domain\Entities\JourneyLog;

/**
 * @property string $journeyLogId
 * @property string $story
 * @property string $period
 * @property int    $orderNo
 */
class ListViewJourneyLog
{
    private const string DATE_FORMAT = 'Y-m-d';

    public function __construct(private readonly JourneyLog $journeyLog)
    {
    }

    public function __get(string $name): int|string
    {
        if (in_array($name, ['journeyLogId', 'story', 'orderNo'], true)) {
            return $this->journeyLog->{$name}->value;
        } elseif ($name === 'period') {
            return $this->period();
        }

        throw new Exception('Invalid property: ' . $name);
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
