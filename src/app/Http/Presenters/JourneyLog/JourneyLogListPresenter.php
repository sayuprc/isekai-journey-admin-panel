<?php

declare(strict_types=1);

namespace App\Http\Presenters\JourneyLog;

use App\Http\ViewModels\JourneyLog\JourneyLogListView;
use JourneyLog\Domain\Entities\JourneyLog;
use JourneyLog\Domain\Entities\Period;
use JourneyLog\UseCases\List\ListResponse;

class JourneyLogListPresenter
{
    private const string DATE_FORMAT = 'Y-m-d';

    /**
     * @return array<JourneyLogListView>
     */
    public function present(ListResponse $response): array
    {
        return array_map(function (JourneyLog $journeyLog): JourneyLogListView {
            return new JourneyLogListView(
                $journeyLog->journeyLogId->value,
                $journeyLog->story->value,
                $this->period($journeyLog->period),
                $journeyLog->orderNo->value,
            );
        }, $response->journeyLogs);
    }

    private function period(Period $period): string
    {
        return $period->isSingleDay()
            ? $period->fromOn->value->format(self::DATE_FORMAT)
            : $period->fromOn->value->format(self::DATE_FORMAT)
            . ' ~ '
            . $period->toOn->value->format(self::DATE_FORMAT);
    }
}
