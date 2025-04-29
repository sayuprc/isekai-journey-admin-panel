<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\JourneyLogLinkType;

use App\Http\Controllers\Controller;
use App\Http\Presenters\Web\JourneyLogLinkType\JourneyLogLinkTypePresenter;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use JourneyLogLinkType\Route\JourneyLogLinkTypeRouteMap;
use JourneyLogLinkType\UseCases\Edit\EditRequest;
use JourneyLogLinkType\UseCases\Edit\EditUseCaseInterface;
use JourneyLogLinkType\UseCases\Get\GetRequest;
use JourneyLogLinkType\UseCases\Get\GetUseCaseInterface;

class EditJourneyLogLinkTypeController extends Controller
{
    public function index(string $journeyLogLinkTypeId, GetUseCaseInterface $interactor, JourneyLogLinkTypePresenter $presenter): RedirectResponse|View
    {
        $result = $interactor->handle(new GetRequest($journeyLogLinkTypeId));

        if ($result->isErr()) {
            return redirect()
                ->route(JourneyLogLinkTypeRouteMap::List)
                ->withErrors([
                    'message' => $result->unwrapErr(),
                ]);
        }

        $journeyLogLinkType = $presenter->present($result->unwrap());

        return view('journeyLogLinkTypes.edit.index', compact('journeyLogLinkType'));
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
            ->route(JourneyLogLinkTypeRouteMap::List)
            ->with([
                'message' => '更新しました',
            ]);
    }
}
