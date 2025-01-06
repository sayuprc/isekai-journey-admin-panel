<?php

declare(strict_types=1);

namespace App\Http\Controllers\JourneyLogLinkType;

use App\Http\Controllers\Controller;
use App\Http\Presenters\JourneyLogLinkType\ListViewJourneyLogLinkType;
use Illuminate\Contracts\View\View;
use JourneyLogLinkType\UseCases\List\ListUseCaseInterface;

class ListJourneyLogLinkTypeController extends Controller
{
    public function index(ListUseCaseInterface $interactor): View
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
