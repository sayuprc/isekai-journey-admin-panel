<?php

declare(strict_types=1);

namespace App\Http\Controllers\JourneyLog;

use App\Http\Controllers\Controller;
use App\Http\Presenters\JourneyLog\ListViewJourneyLog;
use Illuminate\Contracts\View\View;
use JourneyLog\UseCases\List\ListUseCaseInterface;

class ListJourneyLogController extends Controller
{
    public function index(ListUseCaseInterface $interactor): View
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
