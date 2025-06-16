<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\JourneyLog;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Web\JourneyLogLinkType\JourneyLogLinkTypeListPresenter;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use JourneyLog\Application\UseCase\Create\CreateInputData;
use JourneyLog\Application\UseCase\Create\CreateUseCaseInterface;
use JourneyLog\Route\JourneyLogRouteMap;
use JourneyLogLinkType\Application\UseCase\List\ListUseCaseInterface;

class CreateJourneyLogController extends Controller
{
    public function index(ListUseCaseInterface $interactor, JourneyLogLinkTypeListPresenter $presenter): View
    {
        $journeyLogLinkTypes = $presenter->present($interactor->handle());

        return view('journeyLogs.create.index', compact('journeyLogLinkTypes'));
    }

    public function handle(CreateInputData $inputData, CreateUseCaseInterface $interactor): RedirectResponse
    {
        try {
            $interactor->handle($inputData);
        } catch (Exception $e) {
            return back()
                ->withErrors([
                    'message' => $e->getMessage(),
                ])
                ->withInput();
        }

        return redirect()
            ->route(JourneyLogRouteMap::List)
            ->with(['message' => '登録完了しました']);
    }
}
