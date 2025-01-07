<?php

declare(strict_types=1);

namespace App\Http\Controllers\JourneyLog;

use App\Http\Controllers\Controller;
use App\Http\Presenters\JourneyLogLinkType\JourneyLogLinkTypeListPresenter;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use JourneyLog\UseCases\Create\CreateRequest;
use JourneyLog\UseCases\Create\CreateUseCaseInterface;
use JourneyLogLinkType\UseCases\List\ListUseCaseInterface;
use Shared\Route\RouteMap;

class CreateJourneyLogController extends Controller
{
    public function index(ListUseCaseInterface $interactor, JourneyLogLinkTypeListPresenter $presenter): View
    {
        $journeyLogLinkTypes = $presenter->present($interactor->handle());

        return view('journeyLogs.create.index', compact('journeyLogLinkTypes'));
    }

    public function handle(CreateRequest $request, CreateUseCaseInterface $interactor): RedirectResponse
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
