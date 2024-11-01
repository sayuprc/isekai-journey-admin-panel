<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Adapter\Web\Controllers;

use App\Features\JourneyLog\Adapter\Web\Presenters\ListViewJourneyLog;
use App\Features\JourneyLog\Port\UseCases\List\ListInteractor;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

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
