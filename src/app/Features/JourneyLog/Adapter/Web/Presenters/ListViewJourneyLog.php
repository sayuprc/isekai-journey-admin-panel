<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Adapter\Web\Presenters;

use App\Features\JourneyLog\Domain\Entities\JourneyLog;
use Exception;

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
        if (in_array($name, ['journeyLogId', 'story', 'orderNo'])) {
            return $this->journeyLog->{$name}->value;
        } elseif ($name === 'period') {
            return $this->period();
        }

        throw new Exception('Invalid property: ' . $name);
    }

    private function period(): string
    {
        return $this->journeyLog->eventDate->isSingleDay()
            ? $this->journeyLog->eventDate->fromOn->format(self::DATE_FORMAT)
            : $this->journeyLog->eventDate->fromOn->format(self::DATE_FORMAT)
                . ' ~ '
                . $this->journeyLog->eventDate->toOn->format(self::DATE_FORMAT);
    }
}
