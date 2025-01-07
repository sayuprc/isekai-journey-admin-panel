<?php

declare(strict_types=1);

namespace App\Http\Controllers\JourneyLogLinkType;

use App\Http\Controllers\Controller;
use App\Http\Presenters\JourneyLogLinkType\JourneyLogLinkTypePresenter;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use JourneyLogLinkType\UseCases\Edit\EditRequest;
use JourneyLogLinkType\UseCases\Edit\EditUseCaseInterface;
use JourneyLogLinkType\UseCases\Get\GetRequest;
use JourneyLogLinkType\UseCases\Get\GetUseCaseInterface;
use Shared\Route\RouteMap;

class EditJourneyLogLinkTypeController extends Controller
{
    public function index(string $journeyLogLinkTypeId, GetUseCaseInterface $interactor): RedirectResponse|View
    {
        try {
            $response = $interactor->handle(new GetRequest($journeyLogLinkTypeId));
        } catch (Exception $e) {
            return redirect()
                ->route(RouteMap::LIST_JOURNEY_LOG_LINK_TYPE)
                ->withErrors([
                    'message' => $e->getMessage(),
                ]);
        }

        $journeyLogLinkType = (new JourneyLogLinkTypePresenter($response->journeyLogLinkType))->present();

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
            ->route(RouteMap::LIST_JOURNEY_LOG_LINK_TYPE)
            ->with([
                'message' => '更新しました',
            ]);
    }
}
