<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\JourneyLog;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Web\JourneyLog\JourneyLogPresenter;
use App\Http\Presenters\Web\JourneyLogLinkType\JourneyLogLinkTypeListPresenter;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use JourneyLog\Route\JourneyLogRouteMap;
use JourneyLog\UseCases\Edit\EditRequest;
use JourneyLog\UseCases\Edit\EditUseCaseInterface;
use JourneyLog\UseCases\Get\GetRequest;
use JourneyLog\UseCases\Get\GetUseCaseInterface;
use JourneyLogLinkType\UseCases\List\ListUseCaseInterface;

class EditJourneyLogController extends Controller
{
    public function index(
        string $journeyLogId,
        GetUseCaseInterface $getInteractor,
        ListUseCaseInterface $listInteractor,
        JourneyLogPresenter $presenter,
        JourneyLogLinkTypeListPresenter $journeyLogLinkTypeListPresenter,
    ): RedirectResponse|View {
        $result = $getInteractor->handle(new GetRequest($journeyLogId));

        if ($result->isErr()) {
            return redirect()
                ->route(JourneyLogRouteMap::List)
                ->withErrors([
                    'message' => $result->unwrapErr(),
                ]);
        }

        $journeyLog = $presenter->present($result->unwrap());
        $journeyLogLinkTypes = $journeyLogLinkTypeListPresenter->present($listInteractor->handle());

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
            ->route(JourneyLogRouteMap::List)
            ->with([
                'message' => '更新しました',
            ]);
    }
}
