<?php

declare(strict_types=1);

namespace App\Http\Controllers\JourneyLog;

use App\Http\Controllers\Controller;
use App\Http\Presenters\JourneyLog\JourneyLogListPresenter;
use Illuminate\Contracts\View\View;
use JourneyLog\UseCases\List\ListUseCaseInterface;

class ListJourneyLogController extends Controller
{
    public function index(ListUseCaseInterface $interactor, JourneyLogListPresenter $presenter): View
    {
        $heads = [
            '期間',
            '内容',
            '表示順',
            '',
        ];

        $config = [
            'order' => [[0, 'asc']],
        ];

        $journeyLogs = $presenter->present($interactor->handle());

        return view('journeyLogs.list.index', compact('heads', 'config', 'journeyLogs'));
    }
}
