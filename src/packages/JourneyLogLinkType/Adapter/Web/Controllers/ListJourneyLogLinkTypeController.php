<?php

declare(strict_types=1);

namespace JourneyLogLinkType\Adapter\Web\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use JourneyLogLinkType\Adapter\Web\Presenters\ListViewJourneyLogLinkType;
use JourneyLogLinkType\UseCases\List\ListInteractor;

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
