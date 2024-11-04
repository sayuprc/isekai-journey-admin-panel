<?php

declare(strict_types=1);

namespace App\Features\JourneyLogLinkType\Adapter\Web\Controllers;

use App\Features\JourneyLogLinkType\Adapter\Web\Presenters\ListViewJourneyLogLinkType;
use App\Features\JourneyLogLinkType\Port\UseCases\List\ListInteractor;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class ListJourneyLogLinkTypeController extends Controller
{
    public function index(ListInteractor $interactor): View
    {
        $response = $interactor->handle();

        $heads = [
            '名前',
            '表示順',
            '',
        ];

        $journeyLogLinkTypes = [];

        foreach ($response->journeyLogLinkTypes as $journeyLogLinkType) {
            $journeyLogLinkTypes[] = new ListViewJourneyLogLinkType($journeyLogLinkType);
        }

        return view('journeyLogLinkTypes.list.index', compact('heads', 'journeyLogLinkTypes'));
    }
}
