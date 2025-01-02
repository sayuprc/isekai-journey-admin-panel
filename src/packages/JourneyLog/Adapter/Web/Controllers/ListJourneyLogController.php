<?php

declare(strict_types=1);

namespace JourneyLog\Adapter\Web\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use JourneyLog\Adapter\Web\Presenters\ListViewJourneyLog;
use JourneyLog\UseCases\List\ListInteractor;

class ListJourneyLogController extends Controller
{
    public function index(ListInteractor $interactor): View
    {
        $response = $interactor->handle();

        $heads = [
            '内容',
            '期間',
            '表示順',
            '',
        ];

        $journeyLogs = [];

        foreach ($response->journeyLogs as $journeyLog) {
            $journeyLogs[] = new ListViewJourneyLog($journeyLog);
        }

        return view('journeyLogs.list.index', compact('heads', 'journeyLogs'));
    }
}
