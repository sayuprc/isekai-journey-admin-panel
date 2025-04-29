<?php

declare(strict_types=1);

namespace App\Http\Presenters\Web\JourneyLog;

use App\Http\ViewModels\Web\JourneyLog\JourneyLogListView;
use JourneyLog\Domain\Models\JourneyLog;
use JourneyLog\Domain\Models\Period;
use JourneyLog\UseCases\List\ListOutputData;

class JourneyLogListPresenter
{
    private const string DATE_FORMAT = 'Y-m-d';

    /**
     * @return array<JourneyLogListView>
     */
    public function present(ListOutputData $outputData): array
    {
        return array_map(function (JourneyLog $journeyLog): JourneyLogListView {
            return new JourneyLogListView(
                $journeyLog->journeyLogId->value,
                $journeyLog->story->value,
                $this->period($journeyLog->period),
                $journeyLog->orderNo->value,
            );
        }, $outputData->journeyLogs);
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
