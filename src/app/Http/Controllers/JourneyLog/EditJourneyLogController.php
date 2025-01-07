<?php

declare(strict_types=1);

namespace App\Http\Controllers\JourneyLog;

use App\Http\Controllers\Controller;
use App\Http\Presenters\JourneyLog\JourneyLogPresenter;
use App\Http\Presenters\JourneyLogLinkType\JourneyLogLinkTypeListPresenter;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use JourneyLog\UseCases\Edit\EditRequest;
use JourneyLog\UseCases\Edit\EditUseCaseInterface;
use JourneyLog\UseCases\Get\GetRequest;
use JourneyLog\UseCases\Get\GetUseCaseInterface;
use JourneyLogLinkType\UseCases\List\ListUseCaseInterface;
use Shared\Route\RouteMap;

class EditJourneyLogController extends Controller
{
    public function index(string $journeyLogId, GetUseCaseInterface $getInteractor, ListUseCaseInterface $listInteractor, JourneyLogPresenter $presenter): RedirectResponse|View
    {
        try {
            $getResponse = $getInteractor->handle(new GetRequest($journeyLogId));
            $listResponse = $listInteractor->handle();
        } catch (Exception $e) {
            return redirect()
                ->route(RouteMap::LIST_JOURNEY_LOGS)
                ->withErrors([
                    'message' => $e->getMessage(),
                ]);
        }

        $journeyLog = $presenter->present($getResponse);

        $journeyLogLinkTypes = [];

        foreach ($listResponse->journeyLogLinkTypes as $journeyLogLinkType) {
            $journeyLogLinkTypes[] = (new JourneyLogLinkTypeListPresenter($journeyLogLinkType))->present();
        }

        return view('journeyLogs.edit.index', compact('journeyLog', 'journeyLogLinkTypes'));
    }

    public function handle(EditRequest $request, EditUseCaseInterface $interactor): RedirectResponse
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
            ->with([
                'message' => '更新しました',
            ]);
    }
}
