<?php

declare(strict_types=1);

namespace App\Features\JourneyLog\Adapter\Web\Controllers;

use App\Features\JourneyLog\Port\UseCases\Create\CreateInteractor;
use App\Features\JourneyLog\Port\UseCases\Create\CreateRequest;
use App\Features\JourneyLogLinkType\Adapter\Web\Presenters\ListViewJourneyLogLinkType;
use App\Features\JourneyLogLinkType\Port\UseCases\List\ListInteractor;
use App\Http\Controllers\Controller;
use App\Shared\Route\RouteMap;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CreateJourneyLogController extends Controller
{
    public function index(ListInteractor $interactor): View
    {
        $response = $interactor->handle();

        $journeyLogLinkTypes = [];

        foreach ($response->journeyLogLinkTypes as $journeyLogLinkType) {
            $journeyLogLinkTypes[] = new ListViewJourneyLogLinkType($journeyLogLinkType);
        }

        return view('journeyLogs.create.index', compact('journeyLogLinkTypes'));
    }

    public function handle(CreateRequest $request, CreateInteractor $interactor): RedirectResponse
    {
        try {
            $interactor->handle($request);
        } catch (Exception $e) {
            return back()
                ->withErrors([
                    'message' => $e->getMessage(),
                ])
                ->withInput();
        }

        return redirect()
            ->route(RouteMap::LIST_JOURNEY_LOGS)
            ->with(['message' => '登録完了しました']);
    }
}
